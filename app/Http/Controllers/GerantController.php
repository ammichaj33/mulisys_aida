<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\LoanDoc;
use App\Models\Member;
use App\Models\Historique;
use App\Models\LoanRepayment;
use App\Models\Penalty;
use App\Models\RepaymentType;
use App\Services\LoanCalculationService;
use App\Services\CashflowService;
use Carbon\Carbon;

class GerantController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view-dashboard-gerant');
    }

    /**
     * Display the dashboard.
     */
    public function dashboard(Request $request)
    {
        $query = LoanDoc::with('member')
            ->withSum('loanRepayments', 'amount');

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('refNumber', 'like', "%{$search}%")
                  ->orWhereHas('member', function($memberQuery) use ($search) {
                      $memberQuery->where('firstName', 'like', "%{$search}%")
                                 ->orWhere('lastName', 'like', "%{$search}%")
                                 ->orWhere('phoneNumber', 'like', "%{$search}%");
                  });
            });
        }

        $recentRequests = $query->orderBy('createdAt', 'desc')->paginate(50);

        // Enrichir avec remboursé et reste dû
        $calculationService = new LoanCalculationService();
        $recentRequests->getCollection()->transform(function($loan) use ($calculationService) {
            $interestCalculation = $calculationService->calculateMonthlyDegressiveInterest(
                $loan->requestAmount,
                $loan->interestRate,
                $loan->loanMonths,
                $loan->submitDate->format('Y-m-d')
            );
            $totalCancelledInterest = $calculationService->getCancelledInterest($loan->loanDocId);
            $loan->interestAmount = round($interestCalculation['total_interest'] - $totalCancelledInterest, 2);
            $loan->totalAmountDue = round($loan->requestAmount + $interestCalculation['total_interest'] - $totalCancelledInterest, 2);
            $loan->totalRepaid = round($loan->loan_repayments_sum_amount ?? 0, 2);
            $loan->remainingAmount = max(0, round($loan->totalAmountDue - $loan->totalRepaid, 2));
            // Date de fin prévue basée sur la dernière échéance
            $schedule = $interestCalculation['schedule'] ?? [];
            if (!empty($schedule)) {
                $last = end($schedule);
                $loan->expectedEndDate = \Carbon\Carbon::parse($last['date']);
                reset($schedule);
            } else {
                $loan->expectedEndDate = $loan->submitDate->copy()->addMonths((int)($loan->loanMonths ?? 0));
            }
            // Dû cumulé et payé cumulé à aujourd'hui
            $loan->expectedToDate = 0.0;
            $loan->actualToDate = (float) ($loan->loan_repayments_sum_amount ?? 0);
            // Prochaine échéance impayée (informatif)
            $loan->nextUnpaidDueDate = null;
            if (!empty($schedule)) {
                $repayments = $loan->loanRepayments()->orderBy('created_at', 'asc')->get(['amount', 'created_at']);
                $cumulativeBefore = 0.0;
                foreach ($schedule as $payment) {
                    $dueDate = \Carbon\Carbon::parse($payment['date']);
                    $expected = (float) ($payment['montant_total'] ?? 0);
                    if ($expected <= 0) {
                        continue;
                    }
                    if ($dueDate->lte(now())) {
                        $loan->expectedToDate += $expected;
                    }
                    if ($loan->nextUnpaidDueDate === null) {
                        foreach ($repayments as $rep) {
                            if (\Carbon\Carbon::parse($rep->created_at)->lt($dueDate)) {
                                $cumulativeBefore += (float) $rep->amount;
                            } else {
                                break;
                            }
                        }
                        if ($dueDate->lte(now()) && ($cumulativeBefore + 1e-6) < $expected) {
                            $loan->nextUnpaidDueDate = $dueDate;
                        }
                    }
                }
            }
            return $loan;
        });

        // Filtre "insolvables"
        if ($request->boolean('overdue')) {
            // Recharger tous les dossiers correspondant aux filtres (toutes années) puis appliquer le filtre
            $allLoans = $query->orderBy('createdAt', 'desc')->get();
            $enriched = $allLoans->map(function($loan) use ($calculationService) {
                $interestCalculation = $calculationService->calculateMonthlyDegressiveInterest(
                    $loan->requestAmount,
                    $loan->interestRate,
                    $loan->loanMonths,
                    $loan->submitDate->format('Y-m-d')
                );
                $totalCancelledInterest = $calculationService->getCancelledInterest($loan->loanDocId);
                $loan->interestAmount = round($interestCalculation['total_interest'] - $totalCancelledInterest, 2);
                $loan->totalAmountDue = round($loan->requestAmount + $interestCalculation['total_interest'] - $totalCancelledInterest, 2);
                $loan->totalRepaid = round($loan->loan_repayments_sum_amount ?? 0, 2);
                $loan->remainingAmount = max(0, round($loan->totalAmountDue - $loan->totalRepaid, 2));
                $schedule = $interestCalculation['schedule'] ?? [];
                if (!empty($schedule)) {
                    $last = end($schedule);
                    $loan->expectedEndDate = \Carbon\Carbon::parse($last['date']);
                    reset($schedule);
                } else {
                    $loan->expectedEndDate = $loan->submitDate->copy()->addMonths((int)($loan->loanMonths ?? 0));
                }
                // Dû cumulé à aujourd'hui
                $loan->expectedToDate = 0.0;
                if (!empty($schedule)) {
                    foreach ($schedule as $payment) {
                        $dueDate = \Carbon\Carbon::parse($payment['date']);
                        $expected = (float) ($payment['montant_total'] ?? 0);
                        if ($expected > 0 && $dueDate->lte(now())) {
                            $loan->expectedToDate += $expected;
                        }
                    }
                }
                $loan->actualToDate = (float) ($loan->loan_repayments_sum_amount ?? 0);
                return $loan;
            });

            $filtered = $enriched->filter(function($loan) {
                $shortfallToday = ($loan->actualToDate + 1e-6) < ($loan->expectedToDate ?? 0);
                return ($loan->remainingAmount ?? 0) > 0
                    && in_array($loan->status, ['validated', 'done'], true)
                    && ($shortfallToday || $loan->expectedEndDate->isPast());
            })->values();

            // Re-paginate AFTER filtering to keep consistent page size
            $perPage = 50;
            $currentPage = max(1, (int) request()->input('page', 1));
            $total = $filtered->count();
            $items = $filtered->forPage($currentPage, $perPage)->values();
            // Nettoyer la query des paramètres de pagination
            $query = request()->query();
            unset($query['page']);
            $recentRequests = new LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $currentPage,
                ['path' => request()->url(), 'query' => $query]
            );
        }

        // Statistiques
        try {
            $stats = [
                'accepted' => LoanDoc::where('status', 'accepted')->count(),
                'validated' => LoanDoc::where('status', 'validated')->count(),
                'rejected' => LoanDoc::where('status', 'rejected')->count(),
                'done' => LoanDoc::where('status', 'done')->count(),
            ];
        } catch (\Exception $e) {
            $stats = [
                'accepted' => 0,
                'validated' => 0,
                'rejected' => 0,
                'done' => 0,
            ];
        }

        // Remboursements du jour
        try {
            $todayRepayments = LoanRepayment::whereDate('repaymentDate', today())
                ->selectRaw('COUNT(*) as count, COALESCE(SUM(amount), 0) as total')
                ->first();
        } catch (\Exception $e) {
            $todayRepayments = (object)['count' => 0, 'total' => 0];
        }

        // Pénalités en attente
        try {
            $pendingPenalties = Penalty::where('status', 'notPaid')
                ->selectRaw('COUNT(*) as count, COALESCE(SUM(amount), 0) as total')
                ->first();
        } catch (\Exception $e) {
            $pendingPenalties = (object)['count' => 0, 'total' => 0];
        }

        // Remboursements de la semaine (fenêtre: max(début du mois, aujourd'hui-6j) → aujourd'hui)
        try {
            $weekStartWindow = now()->subDays(6)->startOfDay();
            $monthStart = now()->startOfMonth();
            $weekStart = $weekStartWindow->lt($monthStart) ? $monthStart : $weekStartWindow;
            $weekEnd = now()->endOfDay();
            $weekRepayments = LoanRepayment::whereBetween('repaymentDate', [$weekStart, $weekEnd])
                ->selectRaw('COUNT(*) as count, COALESCE(SUM(amount), 0) as total')
                ->first();
        } catch (\Exception $e) {
            $weekRepayments = (object)['count' => 0, 'total' => 0];
        }

        // Remboursements du mois
        try {
            $monthRepayments = LoanRepayment::whereMonth('repaymentDate', now()->month)
                ->whereYear('repaymentDate', now()->year)
                ->selectRaw('COUNT(*) as count, COALESCE(SUM(amount), 0) as total')
                ->first();
        } catch (\Exception $e) {
            $monthRepayments = (object)['count' => 0, 'total' => 0];
        }

        return view('gerant.dashboard', compact('recentRequests', 'stats', 'todayRepayments', 'pendingPenalties', 'weekRepayments', 'monthRepayments'));
    }

    /**
     * Display final validation page for accepted loans.
     */
    public function finalValidation(Request $request)
    {
        // Traitement de la validation finale
        if ($request->isMethod('post')) {
            $request->validate([
                'loanDocId' => 'required|exists:loandocs,loanDocId',
                'decision' => 'required|in:validated,rejected',
                'comments' => 'required|string|max:500'
            ]);

            $loan = LoanDoc::findOrFail($request->loanDocId);
            $oldStatus = $loan->status;
            $loan->update(['status' => $request->decision]);

            // Enregistrer automatiquement dans le cashflow si le crédit est validé
            if ($request->decision === 'validated' && $oldStatus !== 'validated') {
                $cashflowService = new CashflowService();
                $cashflowService->recordLoanGrant($loan);
            }

            // Log the operation
            Historique::create([
                'recordIdFk' => $loan->loanDocId,
                'recordStatus' => $request->decision,
                'operDescription' => 'Validation finale par le gérant' . ($request->comments ? ': ' . $request->comments : ''),
                'userIdFk' => Auth::id()
            ]);

            $message = $request->decision === 'validated' ? 'Demande validée avec succès' : 'Demande rejetée';
            return redirect()->route('gerant.final-validation')->with('success', $message);
        }

        // Récupérer les demandes acceptées en attente de validation finale
        $pendingLoans = LoanDoc::with('member')
            ->where('status', 'accepted')
            ->orderBy('createdAt', 'asc')
            ->get();

        return view('gerant.final-validation', compact('pendingLoans'));
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
     * Display a listing of loans for the gerant.
     */
    public function index(Request $request)
    {
        $query = LoanDoc::with('member');
        
        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('refNumber', 'like', "%{$search}%")
                  ->orWhereHas('member', function($memberQuery) use ($search) {
                      $memberQuery->where('firstName', 'like', "%{$search}%")
                                 ->orWhere('lastName', 'like', "%{$search}%")
                                 ->orWhere('phoneNumber', 'like', "%{$search}%");
                  });
            });
        }
        
        $loans = $query->orderBy('createdAt', 'desc')->paginate(20);
        
        // Ajouter les paramètres de requête aux liens de pagination
        $loans->appends($request->query());
        
        // Statistiques
        $stats = [
            'total' => LoanDoc::count(),
            'draft' => LoanDoc::where('status', 'draft')->count(),
            'accepted' => LoanDoc::where('status', 'accepted')->count(),
            'rejected' => LoanDoc::where('status', 'rejected')->count(),
            'validated' => LoanDoc::where('status', 'validated')->count(),
            'done' => LoanDoc::where('status', 'done')->count(),
        ];
        
        return view('gerant.index', compact('loans', 'stats'));
    }

    /**
     * Show the form for editing the specified loan.
     */
    public function edit($id)
    {
        $loan = LoanDoc::with('member')->findOrFail($id);
        
        // Vérifier que le prêt peut être modifié (statut accepted)
        if ($loan->status !== 'accepted') {
            return redirect()->route('gerant.loans.show', $id)
                ->with('error', 'Ce dossier ne peut pas être modifié.');
        }
        
        return view('gerant.edit', compact('loan'));
    }

    /**
     * Update the specified loan.
     */
    public function update(Request $request, $id)
    {
        $loan = LoanDoc::findOrFail($id);
        
        // Vérifier que le prêt peut être modifié (statut accepted)
        if ($loan->status !== 'accepted') {
            return redirect()->route('gerant.loans.show', $id)
                ->with('error', 'Ce dossier ne peut pas être modifié.');
        }
        
        $request->validate([
            'requestAmount' => 'required|numeric|min:1',
            'loanMonths' => 'required|integer|min:1|max:36',
            'interestRate' => 'required|numeric|min:0|max:100',
        ]);
        
        $oldAmount = $loan->requestAmount;
        $oldMonths = $loan->loanMonths;
        $oldRate = $loan->interestRate;
        
        $loan->update([
            'requestAmount' => $request->requestAmount,
            'loanMonths' => $request->loanMonths,
            'interestRate' => $request->interestRate,
        ]);
        
        // Log the operation
        Historique::create([
            'recordIdFk' => $loan->loanDocId,
            'recordStatus' => 'modified_by_gerant',
            'operDescription' => "Dossier modifié par le gérant - Montant: {$oldAmount} → {$request->requestAmount} USD, Durée: {$oldMonths} → {$request->loanMonths} mois, Taux: {$oldRate}% → {$request->interestRate}%",
            'userIdFk' => Auth::id()
        ]);
        
        return redirect()->route('gerant.loans.show', $id)
            ->with('success', 'Dossier modifié avec succès.');
    }

    /**
     * Display a listing of validated loans for repayment.
     */
    public function repaymentsIndex(Request $request)
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
            });

        return view('gerant.repayments.index', compact('loans'));
    }

    /**
     * Display the specified repayment.
     */
    public function showRepayment($id)
    {
        $repayment = LoanRepayment::with(['loanDoc.member', 'repaymentType', 'user'])->findOrFail($id);
        return view('gerant.repayments.show', compact('repayment'));
    }

    /**
     * Show the form for editing the specified repayment.
     */
    public function editRepayment($id)
    {
        $repayment = LoanRepayment::with(['loanDoc.member', 'loanDoc.loanRepayments', 'repaymentType'])->findOrFail($id);
        $loan = $repayment->loanDoc;
        
        // Vérifier que le crédit a le statut "validated"
        if ($loan->status != 'validated') {
            return redirect()->route('gerant.repayments.show', $repayment->loanRepaymentId)
                ->withErrors(['status' => 'Les remboursements ne peuvent être modifiés que lorsque le crédit a le statut "Validé".']);
        }
        
        $repaymentTypes = RepaymentType::all();
        
        // Calculer les informations du crédit
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
        $loan->totalAmountDue = $loan->requestAmount + $interestCalculation['total_interest'] - $totalCancelledInterest;
        
        // Montant déjà remboursé (sans le remboursement en cours d'édition)
        $loan->totalRepaid = $loan->loanRepayments->where('loanRepaymentId', '!=', $repayment->loanRepaymentId)->sum('amount');
        
        // Reste dû (en ajoutant le montant actuel du remboursement)
        $loan->remainingAmount = max(0, $loan->totalAmountDue - $loan->totalRepaid);
        
        return view('gerant.repayments.edit', compact('repayment', 'repaymentTypes', 'loan'));
    }

    /**
     * Update the specified repayment.
     */
    public function updateRepayment(Request $request, $id)
    {
        $repayment = LoanRepayment::with('loanDoc.loanRepayments')->findOrFail($id);
        
        $loan = $repayment->loanDoc;
        
        // Vérifier que le crédit a le statut "validated"
        if ($loan->status != 'validated') {
            return back()->withErrors([
                'status' => 'Les remboursements ne peuvent être modifiés que lorsque le crédit a le statut "Validé".'
            ]);
        }
        
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'repaymentDate' => 'required|date',
            'repaymentTypeIdFk' => 'required|exists:repaymenttype,repaymentTypeID',
            'description' => 'nullable|string|max:500'
        ]);
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

        $oldAmount = $repayment->amount;
        $oldDate = $repayment->repaymentDate;
        
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
            'operDescription' => 'Remboursement modifié par le gérant: ' . number_format($request->amount, 2) . ' USD',
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
            
            return redirect(route('gerant.loans.show', $loan->loanDocId) . '#repayments')
                ->with('success', 'Remboursement modifié avec succès. Le crédit a été marqué comme terminé.');
        }

        return redirect(route('gerant.loans.show', $loan->loanDocId) . '#repayments')
            ->with('success', 'Remboursement modifié avec succès.');
    }

    /**
     * Remove the specified repayment.
     */
    public function destroyRepayment($id)
    {
        $repayment = LoanRepayment::with('loanDoc')->findOrFail($id);
        $loan = $repayment->loanDoc;
        
        // Vérifier que le crédit a le statut "validated"
        if ($loan->status != 'validated') {
            return back()->withErrors([
                'status' => 'Les remboursements ne peuvent être supprimés que lorsque le crédit a le statut "Validé".'
            ]);
        }
        
        // Annuler la transaction cashflow avant suppression
        $cashflowService = new CashflowService();
        $cashflowService->cancelRepaymentTransaction($repayment->loanRepaymentId, $loan->loanDocId);
        
        // Log the operation before deletion
        Historique::create([
            'recordIdFk' => $repayment->loanDocIdFk,
            'recordStatus' => 'repayment_deleted',
            'operDescription' => 'Remboursement supprimé par le gérant: ' . number_format($repayment->amount, 2) . ' USD',
            'userIdFk' => Auth::id()
        ]);
        
        $repayment->delete();
        
        // Vérifier si le crédit doit être remis en statut validated si ce n'est plus done
        $calculationService = new LoanCalculationService();
        $interestCalculation = $calculationService->calculateMonthlyDegressiveInterest(
            $loan->requestAmount,
            $loan->interestRate,
            $loan->loanMonths,
            $loan->submitDate->format('Y-m-d')
        );
        $totalCancelledInterest = $calculationService->getCancelledInterest($loan->loanDocId);
        $totalAmountDue = $loan->requestAmount + $interestCalculation['total_interest'] - $totalCancelledInterest;
        $totalRepaid = $loan->loanRepayments->sum('amount');
        
        if ($loan->status == 'done' && $totalRepaid < $totalAmountDue) {
            $loan->update([
                'status' => 'validated',
                'endedDate' => null
            ]);
        }
        
        return redirect(route('gerant.loans.show', $loan->loanDocId) . '#repayments')
            ->with('success', 'Remboursement supprimé avec succès.');
    }

}
