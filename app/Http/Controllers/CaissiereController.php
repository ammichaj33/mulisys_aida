<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LoanDoc;
use App\Models\LoanRepayment;
use App\Models\RepaymentType;
use App\Models\Penalty;
use App\Models\Historique;
use App\Services\LoanCalculationService;
use App\Services\CashflowService;
use Carbon\Carbon;

class CaissiereController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        // Le rôle "caissiere" est requis pour toutes les actions SAUF la génération de l'échéancier PDF,
        // qui est protégée uniquement par la permission "generate-repayment-pdf" sur la route.
        $this->middleware('permission:view-dashboard-caissiere')->except('generateSchedulePDF');
    }

    /**
     * Display the dashboard.
     */
    public function dashboard(Request $request)
    {
        // Tous les dossiers avec filtres
        $loansQuery = LoanDoc::with('member');

        // Filtres
        if ($request->filled('status')) {
            $loansQuery->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $loansQuery->where(function($q) use ($search) {
                $q->where('refNumber', 'like', "%{$search}%")
                  ->orWhereHas('member', function($memberQuery) use ($search) {
                      $memberQuery->where('firstName', 'like', "%{$search}%")
                                 ->orWhere('lastName', 'like', "%{$search}%")
                                 ->orWhere('phoneNumber', 'like', "%{$search}%");
                  });
            });
        }

        $recentRequests = $loansQuery->orderBy('createdAt', 'desc')->paginate(20);

        // Derniers remboursements
        $repaymentsQuery = LoanRepayment::with(['loanDoc.member', 'repaymentType', 'user']);

        // Filtres pour les remboursements
        if ($request->filled('search')) {
            $search = $request->search;
            $repaymentsQuery->where(function($q) use ($search) {
                $q->where('amount', 'like', "%{$search}%")
                  ->orWhereHas('loanDoc', function($loanQuery) use ($search) {
                      $loanQuery->where('refNumber', 'like', "%{$search}%")
                               ->orWhereHas('member', function($memberQuery) use ($search) {
                                   $memberQuery->where('firstName', 'like', "%{$search}%")
                                              ->orWhere('lastName', 'like', "%{$search}%")
                                              ->orWhere('phoneNumber', 'like', "%{$search}%");
                               });
                  });
            });
        }

        $recentRepayments = $repaymentsQuery->orderBy('createdAt', 'desc')->limit(10)->get();

        // Statistiques pour tous les statuts
        try {
            $stats = [
                'draft' => LoanDoc::where('status', 'draft')->count(),
                'accepted' => LoanDoc::where('status', 'accepted')->count(),
                'validated' => LoanDoc::where('status', 'validated')->count(),
                'rejected' => LoanDoc::where('status', 'rejected')->count(),
                'done' => LoanDoc::where('status', 'done')->count(),
            ];
        } catch (\Exception $e) {
            $stats = [
                'draft' => 0,
                'accepted' => 0,
                'validated' => 0,
                'rejected' => 0,
                'done' => 0,
            ];
        }

        // Remboursements du jour
        $todayRepayments = LoanRepayment::whereDate('repaymentDate', today())
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(amount), 0) as total')
            ->first();

        // Pénalités en attente
        $pendingPenalties = Penalty::where('status', 'notPaid')
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(amount), 0) as total')
            ->first();

        // Remboursements de la semaine
        $weekRepayments = LoanRepayment::where('repaymentDate', '>=', now()->subDays(7))
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(amount), 0) as total')
            ->first();

        // Remboursements du mois
        $monthRepayments = LoanRepayment::whereMonth('repaymentDate', now()->month)
            ->whereYear('repaymentDate', now()->year)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(amount), 0) as total')
            ->first();

        // Crédits en retard (plus de 30 jours)
        $overdueLoans = LoanDoc::where('status', 'validated')
            ->where('submitDate', '<', now()->subDays(30))
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(requestAmount), 0) as total')
            ->first();

        // Total des crédits actifs
        $totalActiveLoans = LoanDoc::where('status', 'validated')
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(requestAmount), 0) as total')
            ->first();

        return view('caissiere.dashboard', compact('recentRequests', 'recentRepayments', 'stats', 'todayRepayments', 'pendingPenalties', 'weekRepayments', 'monthRepayments', 'overdueLoans', 'totalActiveLoans'));
    }

    /**
     * Display the specified loan.
     */
    public function show($id)
    {
        $loan = LoanDoc::with([
            'member',
            'loanRepayments.repaymentType',
            'loanRepayments.user',
            'penalties',
            'validationHistory.user'
        ])->findOrFail($id);

        // Calculs financiers
        $calculationService = new LoanCalculationService();
        $interestCalculation = $calculationService->calculateMonthlyDegressiveInterest(
            $loan->requestAmount,
            $loan->interestRate,
            $loan->loanMonths,
            $loan->submitDate->format('Y-m-d')
        );
        
        $totalCancelledInterest = $calculationService->getCancelledInterest($loan->loanDocId);
        $totalAmountDue = $loan->requestAmount + $interestCalculation['total_interest'] - $totalCancelledInterest;
        
        // Récupérer les intérêts annulés détaillés
        $cancelledInterests = \App\Models\InterestCancellation::where('loanDocIdFk', $loan->loanDocId)->get();
        
        // Calculer le total remboursé et le montant restant
        $totalRepaid = $loan->loanRepayments->sum('amount');
        $remainingAmount = $totalAmountDue - $totalRepaid;

        return view('loans.show', compact('loan', 'interestCalculation', 'totalCancelledInterest', 'cancelledInterests', 'totalAmountDue', 'totalRepaid', 'remainingAmount', 'calculationService'));
    }

    /**
     * Display the repayment schedule for a loan.
     */
    public function schedule($id)
    {
        $loan = LoanDoc::with(['member', 'loanRepayments'])->findOrFail($id);
        
        // Calculs financiers
        $calculationService = new LoanCalculationService();
        $interestCalculation = $calculationService->calculateMonthlyDegressiveInterest(
            $loan->requestAmount,
            $loan->interestRate,
            $loan->loanMonths,
            $loan->submitDate->format('Y-m-d')
        );
        
        $totalCancelledInterest = $calculationService->getCancelledInterest($loan->loanDocId);
        $totalAmountDue = $loan->requestAmount + $interestCalculation['total_interest'] - $totalCancelledInterest;
        
        // Récupérer les intérêts annulés détaillés
        $cancelledInterests = \App\Models\InterestCancellation::where('loanDocIdFk', $loan->loanDocId)->get();
        
        // Calculer le total remboursé et le montant restant
        $totalRepaid = $loan->loanRepayments->sum('amount');
        $remainingAmount = $totalAmountDue - $totalRepaid;
        
        return view('caissiere.schedule', compact('loan', 'interestCalculation', 'totalCancelledInterest', 'cancelledInterests', 'totalAmountDue', 'totalRepaid', 'remainingAmount', 'calculationService'));
    }

    /**
     * Generate PDF of the repayment schedule.
     */
    public function generateSchedulePDF($id)
    {
        $loan = LoanDoc::with(['member', 'loanRepayments'])->findOrFail($id);
        
        // Calculs financiers
        $calculationService = new LoanCalculationService();
        $interestCalculation = $calculationService->calculateMonthlyDegressiveInterest(
            $loan->requestAmount,
            $loan->interestRate,
            $loan->loanMonths,
            $loan->submitDate->format('Y-m-d')
        );
        
        $totalCancelledInterest = $calculationService->getCancelledInterest($loan->loanDocId);
        $totalAmountDue = $loan->requestAmount + $interestCalculation['total_interest'] - $totalCancelledInterest;
        
        // Récupérer les intérêts annulés détaillés
        $cancelledInterests = \App\Models\InterestCancellation::where('loanDocIdFk', $loan->loanDocId)->get();
        
        // Calculer le total remboursé et le montant restant
        $totalRepaid = $loan->loanRepayments->sum('amount');
        $remainingAmount = $totalAmountDue - $totalRepaid;

        // Générer le PDF
        $pdf = \PDF::loadView('caissiere.schedule-pdf', compact('loan', 'interestCalculation', 'totalCancelledInterest', 'cancelledInterests', 'totalAmountDue', 'totalRepaid', 'remainingAmount', 'calculationService'));
        
        // Télécharger le PDF
        return $pdf->download('calendrier_remboursement_' . $loan->refNumber . '_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Record a repayment.
     */
    public function recordRepayment(Request $request, $id)
    {
        $loan = LoanDoc::findOrFail($id);
        
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'repaymentDate' => 'required|date',
            'repaymentTypeIdFk' => 'required|exists:repaymenttype,repaymentTypeID',
            'description' => 'nullable|string|max:500'
        ]);

        $repaymentDate = Carbon::parse($request->repaymentDate);
        if ($repaymentDate->lt($loan->submitDate)) {
            return back()
                ->withErrors([
                    'repaymentDate' => 'La date de remboursement ne peut pas être antérieure à la date d\'octroi du crédit (' . $loan->submitDate->format('d/m/Y') . ').'
                ])
                ->withInput();
        }

        $repayment = LoanRepayment::create([
            'amount' => $request->amount,
            'loanDocIdFk' => $loan->loanDocId,
            'repaymentDate' => $request->repaymentDate,
            'repaymentTypeIdFk' => $request->repaymentTypeIdFk,
            'userIdFk' => Auth::id()
        ]);

        // Enregistrer automatiquement dans le cashflow
        $cashflowService = new CashflowService();
        $cashflowService->recordRepayment($repayment, $loan);

        // Log the operation
        Historique::create([
            'recordIdFk' => $loan->loanDocId,
            'recordStatus' => 'repayment_recorded',
            'operDescription' => 'Remboursement enregistré: ' . number_format($request->amount, 2) . ' USD',
            'userIdFk' => Auth::id()
        ]);

        return redirect()->route('caissiere.loans.show', $loan->loanDocId)
            ->with('success', 'Remboursement enregistré avec succès.');
    }

    /**
     * Manage penalties.
     */
    public function managePenalties(Request $request, $id)
    {
        $loan = LoanDoc::findOrFail($id);
        
        $request->validate([
            'penalty_amount' => 'required|numeric|min:0.01',
            'reason' => 'required|in:retard,montant_inferieur',
            'description' => 'nullable|string|max:500'
        ]);

        $penalty = Penalty::create([
            'loanDocIdFk' => $loan->loanDocId,
            'amount' => $request->penalty_amount,
            'reason' => $request->reason,
            'description' => $request->description,
            'status' => 'notPaid'
        ]);

        // Log the operation
        Historique::create([
            'recordIdFk' => $loan->loanDocId,
            'recordStatus' => 'penalty_added',
            'operDescription' => 'Pénalité ajoutée: ' . number_format($request->penalty_amount, 2) . ' USD - ' . $request->reason,
            'userIdFk' => Auth::id()
        ]);

        return redirect()->route('caissiere.loans.show', $loan->loanDocId)
            ->with('success', 'Pénalité ajoutée avec succès.');
    }

    /**
     * Display a listing of validated loans for repayment.
     */
    public function index(Request $request)
    {
        $calculationService = new LoanCalculationService();
        
        // Construire la requête de base
        $loansQuery = LoanDoc::whereIn('status', ['validated', 'done'])
            ->with('member')
            ->withSum('loanRepayments', 'amount');

        // Appliquer le filtre de recherche si fourni
        if ($request->filled('search')) {
            $search = $request->search;
            $loansQuery->where(function($query) use ($search) {
                $query->where('refNumber', 'like', "%{$search}%")
                      ->orWhereHas('member', function($memberQuery) use ($search) {
                          $memberQuery->where('firstName', 'like', "%{$search}%")
                                     ->orWhere('lastName', 'like', "%{$search}%")
                                     ->orWhere('phoneNumber', 'like', "%{$search}%");
                      });
            });
        }

        // Récupérer les crédits validés avec les montants remboursés et calculer le reste dû
        $loans = $loansQuery->get()
            ->map(function($loan) use ($calculationService) {
                // Calculer les intérêts
                $interestCalculation = $calculationService->calculateMonthlyDegressiveInterest(
                    $loan->requestAmount,
                    $loan->interestRate,
                    $loan->loanMonths,
                    $loan->submitDate->format('Y-m-d')
                );
                
                // Calculer les intérêts annulés
                $totalCancelledInterest = $calculationService->getCancelledInterest($loan->loanDocId);
                
                // Montant total dû (capital + intérêts - intérêts annulés)
                $loan->totalAmountDue = round($loan->requestAmount + $interestCalculation['total_interest'] - $totalCancelledInterest, 2);
                
                // Montant déjà remboursé
                $loan->totalRepaid = round($loan->loan_repayments_sum_amount ?? 0, 2);
                
                // Reste dû (ne peut pas être négatif)
                $loan->remainingAmount = max(0, round($loan->totalAmountDue - $loan->totalRepaid, 2));
                
                return $loan;
            })
            ->filter(function($loan) {
                // Ne garder que les crédits avec un reste dû > 0
                return $loan->remainingAmount > 0;
            });

        return view('caissiere.repayments.index', compact('loans'));
    }

    /**
     * Show the form for creating a new repayment.
     */
    public function create(Request $request)
    {
        $calculationService = new LoanCalculationService();
        
        // Récupérer les crédits validés avec les montants remboursés et calculer le reste dû
        $loans = LoanDoc::whereIn('status', ['validated', 'done'])
            ->with('member')
            ->withSum('loanRepayments', 'amount')
            ->get()
            ->map(function($loan) use ($calculationService) {
                // Calculer les intérêts
                $interestCalculation = $calculationService->calculateMonthlyDegressiveInterest(
                    $loan->requestAmount,
                    $loan->interestRate,
                    $loan->loanMonths,
                    $loan->submitDate->format('Y-m-d')
                );
                
                // Calculer les intérêts annulés
                $totalCancelledInterest = $calculationService->getCancelledInterest($loan->loanDocId);
                
                // Montant total dû (capital + intérêts - intérêts annulés)
                $loan->totalAmountDue = round($loan->requestAmount + $interestCalculation['total_interest'] - $totalCancelledInterest, 2);
                
                // Montant déjà remboursé
                $loan->totalRepaid = round($loan->loan_repayments_sum_amount ?? 0, 2);
                
                // Reste dû (ne peut pas être négatif)
                $loan->remainingAmount = max(0, round($loan->totalAmountDue - $loan->totalRepaid, 2));
                
                return $loan;
            })
            ->filter(function($loan) {
                // Ne garder que les crédits avec un reste dû > 0
                return $loan->remainingAmount > 0;
            });
        
        $repaymentTypes = RepaymentType::all();
        $selectedLoanId = $request->get('loan_id');
        
        // Récupérer le crédit sélectionné avec ses détails
        $selectedLoan = null;
        $unpaidPenalties = 0;
        if ($selectedLoanId) {
            $selectedLoan = $loans->firstWhere('loanDocId', $selectedLoanId);
            
            // Recalculer avec la même logique que dans store() pour garantir la cohérence
            if ($selectedLoan) {
                $interestCalculation = $calculationService->calculateMonthlyDegressiveInterest(
                    $selectedLoan->requestAmount,
                    $selectedLoan->interestRate,
                    $selectedLoan->loanMonths,
                    $selectedLoan->submitDate->format('Y-m-d')
                );
                
                $totalCancelledInterest = $calculationService->getCancelledInterest($selectedLoan->loanDocId);
                $totalAmountDue = $selectedLoan->requestAmount + $interestCalculation['total_interest'] - $totalCancelledInterest;
                $totalRepaid = $selectedLoan->loanRepayments->sum('amount');
                $remainingAmount = $totalAmountDue - $totalRepaid;
                
                // Calculer les pénalités non payées
                $penaltyService = new \App\Services\PenaltyCalculationService();
                $penaltyService->calculateLoanPenalty($selectedLoan); // Calculer les pénalités si nécessaire
                
                $unpaidPenalties = \App\Models\Penalty::where('loanDocIdFk', $selectedLoan->loanDocId)
                    ->where('status', 'notPaid')
                    ->sum('amount');
                
                // Mettre à jour les valeurs en arrondissant à 2 décimales
                $selectedLoan->totalAmountDue = round($totalAmountDue, 2);
                $selectedLoan->totalRepaid = round($totalRepaid, 2);
                $selectedLoan->remainingAmount = round($remainingAmount, 2);
                $selectedLoan->unpaidPenalties = round($unpaidPenalties, 2);
                $selectedLoan->totalAmountWithPenalties = round($remainingAmount + $unpaidPenalties, 2);
            }
        }
        
        return view('caissiere.repayments.create', compact('loans', 'repaymentTypes', 'selectedLoanId', 'selectedLoan'));
    }

    /**
     * Store a newly created repayment.
     */
    public function store(Request $request)
    {
        $request->validate([
            'loanDocIdFk' => 'required|exists:loandocs,loanDocId',
            'amount' => 'required|numeric|min:0.01',
            'repaymentDate' => 'required|date',
            'repaymentTypeIdFk' => 'required|exists:repaymenttype,repaymentTypeID',
            'description' => 'nullable|string|max:500'
        ]);

        // Récupérer le crédit avec les calculs
        $loan = LoanDoc::findOrFail($request->loanDocIdFk);
        $repaymentDate = Carbon::parse($request->repaymentDate);

        if ($repaymentDate->lt($loan->submitDate)) {
            return back()->withErrors([
                'repaymentDate' => 'La date de remboursement ne peut pas être antérieure à la date d\'octroi du crédit (' . $loan->submitDate->format('d/m/Y') . ').'
            ])->withInput();
        }
        $calculationService = new LoanCalculationService();
        
        // Calculer les intérêts
        $interestCalculation = $calculationService->calculateMonthlyDegressiveInterest(
            $loan->requestAmount,
            $loan->interestRate,
            $loan->loanMonths,
            $loan->submitDate->format('Y-m-d')
        );
        
        // Calculer les intérêts annulés
        $totalCancelledInterest = $calculationService->getCancelledInterest($loan->loanDocId);
        
        // Montant total dû
        $totalAmountDue = $loan->requestAmount + $interestCalculation['total_interest'] - $totalCancelledInterest;
        
        // Montant déjà remboursé
        $totalRepaid = $loan->loanRepayments->sum('amount');
        
        // Reste dû (arrondi à 2 décimales)
        $remainingAmount = round($totalAmountDue - $totalRepaid, 2);
        
        // ============================================
        // PARTIE COMMENTÉE : CALCUL ET GESTION DES PÉNALITÉS
        // ============================================
        // Cette section était responsable de :
        // 1. Calculer automatiquement les pénalités pour le crédit (retards, montants inférieurs)
        // 2. Récupérer les pénalités non payées existantes
        // 3. Vérifier que le montant de remboursement ne dépasse pas le reste dû + pénalités
        // 4. Payer automatiquement les pénalités en priorité avant d'appliquer le reste au crédit
        // 5. Marquer les pénalités comme payées dans la base de données
        //
        // DÉSACTIVÉ : Les pénalités ne sont plus calculées ni gérées automatiquement lors de l'enregistrement
        // d'un remboursement. Elles doivent être gérées manuellement séparément.
        // ============================================
        
        // // Calculer les pénalités non payées
        // $penaltyService = new \App\Services\PenaltyCalculationService();
        // $penaltyService->calculateLoanPenalty($loan); // Calculer les pénalités si nécessaire
        // 
        // $unpaidPenalties = \App\Models\Penalty::where('loanDocIdFk', $loan->loanDocId)
        //     ->where('status', 'notPaid')
        //     ->sum('amount');
        // 
        // $unpaidPenalties = round($unpaidPenalties, 2);
        // $totalAmountWithPenalties = round($remainingAmount + $unpaidPenalties, 2);
        // 
        // // Vérifier que le montant ne dépasse pas le reste dû + pénalités (≤)
        // // Utiliser une tolérance pour les comparaisons de nombres flottants
        // $tolerance = 0.001; // 0.1 centime de tolérance
        // if (($request->amount - $totalAmountWithPenalties) > $tolerance) {
        //     return back()->withErrors([
        //         'amount' => 'Le montant du remboursement (' . round($request->amount, 2) . ' USD) ne peut pas dépasser le montant total dû (' . round($totalAmountWithPenalties, 2) . ' USD)'
        //     ])->withInput();
        // }
        //
        // // Calculer la répartition du paiement
        // $repaymentAmount = $request->amount;
        // $penaltyPayment = 0;
        // $loanPayment = 0;
        // 
        // // Si il y a des pénalités non payées, les payer en priorité
        // if ($unpaidPenalties > 0 && $repaymentAmount > 0) {
        //     $penaltyPayment = min($repaymentAmount, $unpaidPenalties);
        //     $repaymentAmount -= $penaltyPayment;
        //     
        //     // Marquer les pénalités comme payées
        //     \App\Models\Penalty::where('loanDocIdFk', $loan->loanDocId)
        //         ->where('status', 'notPaid')
        //         ->update([
        //             'status' => 'paid',
        //             'paidAt' => now(),
        //             'paidAmount' => \DB::raw('amount')
        //         ]);
        // }
        // 
        // // Le reste va au remboursement du crédit
        // $loanPayment = $repaymentAmount;
        
        // ============================================
        // NOUVELLE LOGIQUE : Remboursement direct sans gestion des pénalités
        // ============================================
        // Le montant saisi est directement appliqué au remboursement du crédit
        // Les pénalités doivent être gérées séparément via le module de gestion des pénalités
        // ============================================
        
        // Vérifier que le montant ne dépasse pas le reste dû
        $tolerance = 0.001; // 0.1 centime de tolérance
        if (($request->amount - $remainingAmount) > $tolerance) {
            return back()->withErrors([
                'amount' => 'Le montant du remboursement (' . round($request->amount, 2) . ' USD) ne peut pas dépasser le reste dû (' . round($remainingAmount, 2) . ' USD)'
            ])->withInput();
        }
        
        // Le montant saisi va directement au remboursement du crédit
        $loanPayment = $request->amount;
        
        // Créer le remboursement
        $repayment = LoanRepayment::create([
            'amount' => $loanPayment,
            'loanDocIdFk' => $request->loanDocIdFk,
            'repaymentDate' => $request->repaymentDate,
            'repaymentTypeIdFk' => $request->repaymentTypeIdFk,
            'userIdFk' => Auth::id(),
            'description' => $request->description
        ]);

        // Log the operation
        $logDescription = 'Remboursement enregistré: ' . number_format($loanPayment, 2) . ' USD';
        // Note: Les pénalités ne sont plus gérées automatiquement lors de l'enregistrement d'un remboursement
        // if ($penaltyPayment > 0) {
        //     $logDescription .= ' + Pénalités payées: ' . number_format($penaltyPayment, 2) . ' USD';
        // }
        
        Historique::create([
            'recordIdFk' => $request->loanDocIdFk,
            'recordStatus' => 'repayment_recorded',
            'operDescription' => $logDescription,
            'userIdFk' => Auth::id()
        ]);

        // Vérifier si le crédit est entièrement remboursé
        $newTotalRepaid = $totalRepaid + $request->amount;
        
        if ($newTotalRepaid >= $totalAmountDue) {
            // Marquer le crédit comme terminé
            $loan->update([
                'status' => 'done',
                'endedDate' => now()
            ]);
            
            // Log l'opération
            Historique::create([
                'recordIdFk' => $loan->loanDocId,
                'recordStatus' => 'completed',
                'operDescription' => 'Crédit entièrement remboursé',
                'userIdFk' => Auth::id()
            ]);
            
            return redirect()->route('caissiere.repayments.show', $repayment->loanRepaymentId)
                ->with('success', 'Remboursement enregistré avec succès. Le crédit a été marqué comme terminé.');
        }

        return redirect()->route('caissiere.repayments.show', $repayment->loanRepaymentId)
            ->with('success', 'Remboursement enregistré avec succès.');
    }

    /**
     * Display the specified repayment.
     */
    public function showRepayment($id)
    {
        $repayment = LoanRepayment::with(['loanDoc.member', 'repaymentType', 'user'])->findOrFail($id);
        return view('caissiere.repayments.show', compact('repayment'));
    }

    /**
     * Show the form for editing the specified repayment.
     */
    public function edit($id)
    {
        $repayment = LoanRepayment::with(['loanDoc.member', 'loanDoc.loanRepayments', 'repaymentType'])->findOrFail($id);
        $repaymentTypes = RepaymentType::all();
        
        // Calculer les informations du crédit
        $calculationService = new LoanCalculationService();
        $loan = $repayment->loanDoc;
        
        // Calculer les intérêts
        $interestCalculation = $calculationService->calculateMonthlyDegressiveInterest(
            $loan->requestAmount,
            $loan->interestRate,
            $loan->loanMonths,
            $loan->submitDate->format('Y-m-d')
        );
        
        // Calculer les intérêts annulés
        $totalCancelledInterest = $calculationService->getCancelledInterest($loan->loanDocId);
        
        // Montant total dû
        $loan->totalAmountDue = $loan->requestAmount + $interestCalculation['total_interest'] - $totalCancelledInterest;
        
        // Montant déjà remboursé (sans le remboursement en cours d'édition)
        $loan->totalRepaid = $loan->loanRepayments->where('loanRepaymentId', '!=', $repayment->loanRepaymentId)->sum('amount');
        
        // Reste dû (en ajoutant le montant actuel du remboursement)
        $loan->remainingAmount = max(0, $loan->totalAmountDue - $loan->totalRepaid);
        
        return view('caissiere.repayments.edit', compact('repayment', 'repaymentTypes', 'loan'));
    }

    /**
     * Update the specified repayment.
     */
    public function update(Request $request, $id)
    {
        $repayment = LoanRepayment::with('loanDoc.loanRepayments')->findOrFail($id);
        
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'repaymentDate' => 'required|date',
            'repaymentTypeIdFk' => 'required|exists:repaymenttype,repaymentTypeID',
            'description' => 'nullable|string|max:500'
        ]);

        $loan = $repayment->loanDoc;
        
        $repaymentDate = Carbon::parse($request->repaymentDate);
        if ($repaymentDate->lt($loan->submitDate)) {
            return back()->withErrors([
                'repaymentDate' => 'La date de remboursement ne peut pas être antérieure à la date d\'octroi du crédit (' . $loan->submitDate->format('d/m/Y') . ').'
            ])->withInput();
        }

        // Calculer le reste dû pour validation
        $calculationService = new LoanCalculationService();
        
        // Calculer les intérêts
        $interestCalculation = $calculationService->calculateMonthlyDegressiveInterest(
            $loan->requestAmount,
            $loan->interestRate,
            $loan->loanMonths,
            $loan->submitDate->format('Y-m-d')
        );
        
        // Calculer les intérêts annulés
        $totalCancelledInterest = $calculationService->getCancelledInterest($loan->loanDocId);
        
        // Montant total dû
        $totalAmountDue = $loan->requestAmount + $interestCalculation['total_interest'] - $totalCancelledInterest;
        
        // Montant déjà remboursé (sans le remboursement en cours d'édition)
        $totalRepaid = $loan->loanRepayments->where('loanRepaymentId', '!=', $repayment->loanRepaymentId)->sum('amount');
        
        // Reste dû
        $remainingAmount = max(0, $totalAmountDue - $totalRepaid);
        
        // Vérifier que le montant ne dépasse pas le reste dû (≤)
        if ($request->amount > $remainingAmount) {
            return back()->withErrors([
                'amount' => 'Le montant du remboursement (' . number_format($request->amount, 2, ',', ' ') . ' USD) doit être inférieur ou égal au reste dû (' . number_format($remainingAmount, 2, ',', ' ') . ' USD)'
            ])->withInput();
        }

        $repayment->update([
            'amount' => $request->amount,
            'repaymentDate' => $request->repaymentDate,
            'repaymentTypeIdFk' => $request->repaymentTypeIdFk,
            'description' => $request->description
        ]);

        // Mettre à jour la transaction cashflow
        $cashflowService = new CashflowService();
        // Annuler l'ancienne transaction
        $cashflowService->cancelRepaymentTransaction($repayment->loanRepaymentId, $loan->loanDocId);
        // Créer une nouvelle transaction avec les nouvelles données
        $cashflowService->recordRepayment($repayment, $loan);

        // Log the operation
        Historique::create([
            'recordIdFk' => $repayment->loanDocIdFk,
            'recordStatus' => 'repayment_updated',
            'operDescription' => 'Remboursement modifié: ' . number_format($request->amount, 2) . ' USD',
            'userIdFk' => Auth::id()
        ]);

        // Vérifier si le crédit est maintenant entièrement remboursé
        $newTotalRepaid = $totalRepaid + $request->amount;
        
        if ($newTotalRepaid >= $totalAmountDue && $loan->status != 'done') {
            // Marquer le crédit comme terminé
            $loan->update([
                'status' => 'done',
                'endedDate' => now()
            ]);
            
            // Log l'opération
            Historique::create([
                'recordIdFk' => $loan->loanDocId,
                'recordStatus' => 'completed',
                'operDescription' => 'Crédit entièrement remboursé',
                'userIdFk' => Auth::id()
            ]);
            
            return redirect()->route('caissiere.repayments.index')
                ->with('success', 'Remboursement modifié avec succès. Le crédit a été marqué comme terminé.');
        }

        return redirect()->route('caissiere.repayments.index')
            ->with('success', 'Remboursement modifié avec succès.');
    }

    /**
     * Display penalties management.
     */
    public function penalties(Request $request)
    {
        $query = Penalty::with(['loanDoc.member']);

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('amount', 'like', "%{$search}%")
                  ->orWhereHas('loanDoc', function($loanQuery) use ($search) {
                      $loanQuery->where('refNumber', 'like', "%{$search}%")
                               ->orWhereHas('member', function($memberQuery) use ($search) {
                                   $memberQuery->where('firstName', 'like', "%{$search}%")
                                              ->orWhere('lastName', 'like', "%{$search}%")
                                              ->orWhere('phoneNumber', 'like', "%{$search}%");
                               });
                  });
            });
        }

        $penalties = $query->orderBy('createdAt', 'desc')->paginate(20);

        return view('caissiere.penalties.index', compact('penalties'));
    }

    /**
     * Pay a penalty.
     */
    public function payPenalty(Request $request, $id)
    {
        $penalty = Penalty::findOrFail($id);
        
        $request->validate([
            'payment_amount' => 'required|numeric|min:0.01|max:' . $penalty->amount,
            'payment_date' => 'required|date',
            'description' => 'nullable|string|max:500'
        ]);

        $penalty->update([
            'status' => 'paid',
            'paidAt' => $request->payment_date,
            'paidBy' => Auth::id(),
            'paidAmount' => $request->payment_amount,
            'description' => $request->description
        ]);

        // Enregistrer automatiquement dans le cashflow
        $loan = LoanDoc::findOrFail($penalty->loanDocIdFk);
        $cashflowService = new CashflowService();
        $cashflowService->recordPenaltyPayment($penalty, $loan);

        // Log the operation
        Historique::create([
            'recordIdFk' => $penalty->loanDocIdFk,
            'recordStatus' => 'penalty_paid',
            'operDescription' => 'Pénalité payée: ' . number_format($request->payment_amount, 2) . ' USD',
            'userIdFk' => Auth::id()
        ]);

        return redirect()->route('caissiere.penalties.index')
            ->with('success', 'Pénalité payée avec succès.');
    }
}
