<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\LoanDoc;
use App\Models\LoanRepayment;
use App\Models\RepaymentType;
use App\Models\Penalty;
use App\Models\Historique;
use App\Models\InterestCancellation;
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
        $loansQuery = LoanDoc::with('member')
            ->withSum('loanRepayments', 'amount');

        $overdueFrom = null;
        $overdueTo = null;
        if ($request->boolean('overdue')) {
            // Filtre optionnel "insolvables du ... au ..." basé sur la date prévue (expectedEndDate)
            try {
                if ($request->filled('overdue_from')) {
                    $overdueFrom = Carbon::parse($request->input('overdue_from'))->startOfDay();
                }
                if ($request->filled('overdue_to')) {
                    $overdueTo = Carbon::parse($request->input('overdue_to'))->endOfDay();
                }
                if ($overdueFrom && $overdueTo && $overdueFrom->gt($overdueTo)) {
                    [$overdueFrom, $overdueTo] = [$overdueTo->copy()->startOfDay(), $overdueFrom->copy()->endOfDay()];
                }
            } catch (\Exception $e) {
                $overdueFrom = null;
                $overdueTo = null;
            }
        }

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

        // Enrichir et filtrer
        $calculationService = new LoanCalculationService();
        if ($request->boolean('overdue')) {
            // Charger l'ensemble des dossiers correspondant aux filtres, puis filtrer l'insolvabilité
            $allLoans = $loansQuery->orderBy('createdAt', 'desc')->get();
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
                    $loan->expectedEndDate = Carbon::parse($last['date']);
                    reset($schedule);
                } else {
                    $loan->expectedEndDate = $loan->submitDate->copy()->addMonths((int)($loan->loanMonths ?? 0));
                }
                $loan->expectedToDate = 0.0;
                $loan->actualToDate = (float) ($loan->loan_repayments_sum_amount ?? 0);
                if (!empty($schedule)) {
                    $repayments = $loan->loanRepayments()->orderBy('created_at', 'asc')->get(['amount', 'created_at']);
                    $cumulativeBefore = 0.0;
                    foreach ($schedule as $payment) {
                        $dueDate = Carbon::parse($payment['date']);
                        $expected = (float) ($payment['montant_total'] ?? 0);
                        if ($expected <= 0) {
                            continue;
                        }
                        if ($dueDate->lte(now())) {
                            $loan->expectedToDate += $expected;
                        }
                        foreach ($repayments as $rep) {
                            if (Carbon::parse($rep->created_at)->lt($dueDate)) {
                                $cumulativeBefore += (float) $rep->amount;
                            } else {
                                break;
                            }
                        }
                    }
                }
                return $loan;
            });
            $filtered = $enriched->filter(function($loan) {
                $shortfallToday = ($loan->actualToDate + 1e-6) < ($loan->expectedToDate ?? 0);
                return ($loan->remainingAmount ?? 0) > 0
                    && in_array($loan->status, ['validated', 'done'], true)
                    && ($shortfallToday || $loan->expectedEndDate->isPast());
            })->values();

            if ($overdueFrom || $overdueTo) {
                $filtered = $filtered->filter(function ($loan) use ($overdueFrom, $overdueTo) {
                    if (empty($loan->expectedEndDate)) {
                        return false;
                    }
                    $d = $loan->expectedEndDate instanceof Carbon ? $loan->expectedEndDate : Carbon::parse($loan->expectedEndDate);
                    if ($overdueFrom && $d->lt($overdueFrom)) {
                        return false;
                    }
                    if ($overdueTo && $d->gt($overdueTo)) {
                        return false;
                    }
                    return true;
                })->values();
            }

            $perPage = 50;
            $currentPage = max(1, (int) request()->input('page', 1));
            $total = $filtered->count();
            $items = $filtered->forPage($currentPage, $perPage)->values();
            $query = request()->query();
            unset($query['page']);
            $recentRequests = new LengthAwarePaginator($items, $total, $perPage, $currentPage, [
                'path' => request()->url(),
                'query' => $query,
            ]);
        } else {
            $recentRequests = $loansQuery->orderBy('createdAt', 'desc')->paginate(50);
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
                $schedule = $interestCalculation['schedule'] ?? [];
                if (!empty($schedule)) {
                    $last = end($schedule);
                    $loan->expectedEndDate = Carbon::parse($last['date']);
                    reset($schedule);
                } else {
                    $loan->expectedEndDate = $loan->submitDate->copy()->addMonths((int)($loan->loanMonths ?? 0));
                }
                // Dû cumulé à aujourd'hui
                $loan->expectedToDate = 0.0;
                if (!empty($schedule)) {
                    foreach ($schedule as $payment) {
                        $dueDate = Carbon::parse($payment['date']);
                        $expected = (float) ($payment['montant_total'] ?? 0);
                        if ($expected > 0 && $dueDate->lte(now())) {
                            $loan->expectedToDate += $expected;
                        }
                    }
                }
                $loan->actualToDate = (float) ($loan->loan_repayments_sum_amount ?? 0);
                return $loan;
            });
        }

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

        // Remboursements de la semaine (fenêtre: max(début du mois, aujourd'hui-6j) → aujourd'hui)
        $weekStartWindow = now()->subDays(6)->startOfDay();
        $monthStart = now()->startOfMonth();
        $weekStart = $weekStartWindow->lt($monthStart) ? $monthStart : $weekStartWindow;
        $weekEnd = now()->endOfDay();
        $weekRepayments = LoanRepayment::whereBetween('repaymentDate', [$weekStart, $weekEnd])
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

    public function printOverduePdf(Request $request)
    {
        if (!$request->boolean('overdue')) {
            return redirect()->route('caissiere.dashboard');
        }

        // Base query + filtres (mêmes que le dashboard)
        $loansQuery = LoanDoc::with('member')->withSum('loanRepayments', 'amount');

        if ($request->filled('status')) {
            $loansQuery->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $loansQuery->where(function ($q) use ($search) {
                $q->where('refNumber', 'like', "%{$search}%")
                    ->orWhereHas('member', function ($memberQuery) use ($search) {
                        $memberQuery->where('firstName', 'like', "%{$search}%")
                            ->orWhere('lastName', 'like', "%{$search}%")
                            ->orWhere('phoneNumber', 'like', "%{$search}%");
                    });
            });
        }

        // Période optionnelle (même logique que le dashboard)
        $overdueFrom = null;
        $overdueTo = null;
        try {
            if ($request->filled('overdue_from')) {
                $overdueFrom = Carbon::parse($request->input('overdue_from'))->startOfDay();
            }
            if ($request->filled('overdue_to')) {
                $overdueTo = Carbon::parse($request->input('overdue_to'))->endOfDay();
            }
            if ($overdueFrom && $overdueTo && $overdueFrom->gt($overdueTo)) {
                [$overdueFrom, $overdueTo] = [$overdueTo->copy()->startOfDay(), $overdueFrom->copy()->endOfDay()];
            }
        } catch (\Exception $e) {
            $overdueFrom = null;
            $overdueTo = null;
        }

        $calculationService = new LoanCalculationService();
        $allLoans = $loansQuery->orderBy('createdAt', 'desc')->get();

        $enriched = $allLoans->map(function ($loan) use ($calculationService) {
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
                $loan->expectedEndDate = Carbon::parse($last['date']);
                reset($schedule);
            } else {
                $loan->expectedEndDate = $loan->submitDate->copy()->addMonths((int)($loan->loanMonths ?? 0));
            }
            $loan->expectedToDate = 0.0;
            $loan->actualToDate = (float)($loan->loan_repayments_sum_amount ?? 0);
            if (!empty($schedule)) {
                $repayments = $loan->loanRepayments()->orderBy('created_at', 'asc')->get(['amount', 'created_at']);
                $cumulativeBefore = 0.0;
                foreach ($schedule as $payment) {
                    $dueDate = Carbon::parse($payment['date']);
                    $expected = (float)($payment['montant_total'] ?? 0);
                    if ($expected <= 0) {
                        continue;
                    }
                    if ($dueDate->lte(now())) {
                        $loan->expectedToDate += $expected;
                    }
                    foreach ($repayments as $rep) {
                        if (Carbon::parse($rep->created_at)->lt($dueDate)) {
                            $cumulativeBefore += (float)$rep->amount;
                        } else {
                            break;
                        }
                    }
                }
            }
            return $loan;
        });

        $loans = $enriched->filter(function ($loan) use ($overdueFrom, $overdueTo) {
            $shortfallToday = ($loan->actualToDate + 1e-6) < ($loan->expectedToDate ?? 0);
            $isOverdue = ($loan->remainingAmount ?? 0) > 0
                && in_array($loan->status, ['validated', 'done'], true)
                && ($shortfallToday || $loan->expectedEndDate->isPast());

            if (!$isOverdue) {
                return false;
            }

            if ($overdueFrom || $overdueTo) {
                if (empty($loan->expectedEndDate)) {
                    return false;
                }
                $d = $loan->expectedEndDate instanceof Carbon ? $loan->expectedEndDate : Carbon::parse($loan->expectedEndDate);
                if ($overdueFrom && $d->lt($overdueFrom)) {
                    return false;
                }
                if ($overdueTo && $d->gt($overdueTo)) {
                    return false;
                }
            }

            return true;
        })->values();

        $loans = $loans->sortBy(function ($loan) {
            return $loan->expectedEndDate ? $loan->expectedEndDate->timestamp : PHP_INT_MAX;
        })->values();

        $totals = [
            'count' => $loans->count(),
            'requestAmount' => round((float)$loans->sum('requestAmount'), 2),
            'totalAmountDue' => round((float)$loans->sum('totalAmountDue'), 2),
            'totalRepaid' => round((float)$loans->sum('totalRepaid'), 2),
            'remainingAmount' => round((float)$loans->sum('remainingAmount'), 2),
        ];

        $pdf = \PDF::loadView('caissiere.insolvables-pdf', [
            'loans' => $loans,
            'overdueFrom' => $overdueFrom,
            'overdueTo' => $overdueTo,
            'filters' => [
                'status' => $request->input('status'),
                'search' => $request->input('search'),
            ],
            'totals' => $totals,
        ]);

        $filename = 'insolvables_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->stream($filename);
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
     * Record a repayment with automatic allocation: Interest → Penalties → Capital
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

        // Description simple du remboursement (sans retrait automatique des pénalités)
        // Toujours une chaîne non nulle pour éviter les erreurs SQL
        $description = $request->description ?: '';

        $repayment = LoanRepayment::create([
            'amount' => $request->amount,
            'loanDocIdFk' => $loan->loanDocId,
            'repaymentDate' => $request->repaymentDate,
            'repaymentTypeIdFk' => $request->repaymentTypeIdFk,
            'userIdFk' => Auth::id(),
            'description' => $description
        ]);

        // Enregistrer automatiquement dans le cashflow
        $cashflowService = new CashflowService();
        $cashflowService->recordRepayment($repayment, $loan);

        // Log the operation (sans détail de répartition pénalités/intérêts/capital)
        $logDescription = 'Remboursement enregistré: ' . number_format($request->amount, 2) . ' USD';

        Historique::create([
            'recordIdFk' => $loan->loanDocId,
            'recordStatus' => 'repayment_recorded',
            'operDescription' => $logDescription,
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
        
        try {
            // Vérifier que le montant ne dépasse pas le reste dû (hors pénalités)
            $tolerance = 0.001; // 0.1 centime de tolérance
            if (($request->amount - $remainingAmount) > $tolerance) {
                return back()->withErrors([
                    'amount' => 'Le montant du remboursement (' . round($request->amount, 2) . ' USD) ne peut pas dépasser le montant total dû (' . round($remainingAmount, 2) . ' USD).'
                ])->withInput();
            }
            
            // Limiter la description utilisateur à 100 caractères max
            $userDescription = $request->description ? mb_substr($request->description, 0, 100) : '';
            // Description finale (max 255 caractères au total), sans mention de pénalités automatiques
            // Toujours une chaîne non nulle pour éviter les erreurs SQL
            $description = $userDescription !== '' ? mb_substr($userDescription, 0, 255) : '';
            
            // Créer le remboursement
            $repayment = LoanRepayment::create([
                'amount' => $request->amount,
                'loanDocIdFk' => $request->loanDocIdFk,
                'repaymentDate' => $request->repaymentDate,
                'repaymentTypeIdFk' => $request->repaymentTypeIdFk,
                'userIdFk' => Auth::id(),
                'description' => $description
            ]);

            // Enregistrer automatiquement dans le cashflow
            $cashflowService = new CashflowService();
            $cashflowService->recordRepayment($repayment, $loan);
            
            // Log the operation (sans détail de répartition pénalités/intérêts/capital)
            $logDescription = 'Remboursement enregistré: ' . number_format($request->amount, 2) . ' USD';
            
            Historique::create([
                'recordIdFk' => $request->loanDocIdFk,
                'recordStatus' => 'repayment_recorded',
                'operDescription' => $logDescription,
                'userIdFk' => Auth::id()
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Erreur lors de l'enregistrement du remboursement", [
                'loanId' => $request->loanDocIdFk,
                'amount' => $request->amount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors([
                'error' => 'Une erreur est survenue lors de l\'enregistrement: ' . $e->getMessage()
            ])->withInput();
        }

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

    /**
     * Afficher le formulaire de remboursement anticipé
     */
    public function earlyRepaymentForm($id)
    {
        $loan = LoanDoc::with([
            'member',
            'loanRepayments.repaymentType',
            'loanRepayments.user',
            'penalties'
        ])->findOrFail($id);

        // Vérifier que le crédit est validé
        if ($loan->status !== 'validated') {
            return redirect()->route('caissiere.loans.show', $loan->loanDocId)
                ->with('error', 'Seuls les crédits validés peuvent être remboursés de manière anticipée.');
        }

        $calculationService = new LoanCalculationService();
        
        // Calculer les intérêts et montants
        $interestCalculation = $calculationService->calculateMonthlyDegressiveInterest(
            $loan->requestAmount,
            $loan->interestRate,
            $loan->loanMonths,
            $loan->submitDate->format('Y-m-d')
        );
        
        $totalCancelledInterest = $calculationService->getCancelledInterest($loan->loanDocId);
        $totalAmountDue = $loan->requestAmount + $interestCalculation['total_interest'] - $totalCancelledInterest;
        $totalRepaid = $loan->loanRepayments->sum('amount');
        $remainingAmount = round($totalAmountDue - $totalRepaid, 2);

        // Si déjà entièrement remboursé
        if ($remainingAmount <= 0) {
            return redirect()->route('caissiere.loans.show', $loan->loanDocId)
                ->with('error', 'Ce crédit est déjà entièrement remboursé.');
        }

        // Calculer les détails du remboursement anticipé (par défaut avec la date d'aujourd'hui)
        $earlyRepaymentDetails = $calculationService->calculateEarlyRepaymentInterest(
            $loan,
            now()->format('Y-m-d')
        );

        // Récupérer le type de remboursement "Remboursement anticipé"
        $earlyRepaymentType = RepaymentType::where('isActive', true)
            ->where(function($query) {
                $query->where('repaymentName', 'like', '%Remboursement anticipé%')
                      ->orWhere('repaymentName', 'like', '%anticipé%')
                      ->orWhere('repaymentName', 'like', '%Anticipé%')
                      ->orWhere('repaymentName', 'like', '%Anticipe%')
                      ->orWhere('repaymentName', 'like', '%anticip%');
            })
            ->first();
        
        // Si le type n'existe pas, chercher par défaut ou créer un message d'erreur
        if (!$earlyRepaymentType) {
            // Essayer de trouver n'importe quel type actif comme fallback
            $earlyRepaymentType = RepaymentType::where('isActive', true)->first();
        }

        return view('caissiere.early-repayment', compact(
            'loan',
            'calculationService',
            'interestCalculation',
            'totalCancelledInterest',
            'totalAmountDue',
            'totalRepaid',
            'remainingAmount',
            'earlyRepaymentDetails',
            'earlyRepaymentType'
        ));
    }

    /**
     * Traiter le remboursement anticipé
     */
    public function processEarlyRepayment(Request $request, $id)
    {
        $loan = LoanDoc::with('loanRepayments')->findOrFail($id);

        // Vérifier que le crédit est validé
        if ($loan->status !== 'validated') {
            return back()->withErrors([
                'status' => 'Seuls les crédits validés peuvent être remboursés de manière anticipée.'
            ])->withInput();
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'repaymentDate' => 'required|date',
            'repaymentTypeIdFk' => 'required|exists:repaymenttype,repaymentTypeID',
            'description' => 'nullable|string|max:500',
            'confirm_early_repayment' => 'required|accepted'
        ]);

        $repaymentDate = Carbon::parse($request->repaymentDate);
        
        // Vérifier que la date n'est pas antérieure à la date d'octroi
        if ($repaymentDate->lt($loan->submitDate)) {
            return back()->withErrors([
                'repaymentDate' => 'La date de remboursement ne peut pas être antérieure à la date d\'octroi du crédit (' . $loan->submitDate->format('d/m/Y') . ').'
            ])->withInput();
        }

        $calculationService = new LoanCalculationService();
        
        // Calculer les détails du remboursement anticipé
        $earlyRepaymentDetails = $calculationService->calculateEarlyRepaymentInterest(
            $loan,
            $request->repaymentDate
        );

        // Vérifier que c'est bien un remboursement anticipé
        if (!$earlyRepaymentDetails['isEarlyRepayment']) {
            return back()->withErrors([
                'amount' => 'Ce n\'est pas un remboursement anticipé. Utilisez le formulaire de remboursement normal.'
            ])->withInput();
        }

        // Calculer le montant total dû avec intérêts annulés
        $interestCalculation = $calculationService->calculateMonthlyDegressiveInterest(
            $loan->requestAmount,
            $loan->interestRate,
            $loan->loanMonths,
            $loan->submitDate->format('Y-m-d')
        );
        
        $totalCancelledInterest = $calculationService->getCancelledInterest($loan->loanDocId);
        $totalAmountDue = $loan->requestAmount + $interestCalculation['total_interest'] - $totalCancelledInterest;
        $totalRepaid = $loan->loanRepayments->sum('amount');
        
        // Le montant total à payer pour un remboursement anticipé complet
        $expectedAmount = $earlyRepaymentDetails['montantTotal'];
        
        // Vérifier que le montant correspond (avec tolérance)
        $tolerance = 0.01; // 1 centime de tolérance
        if (abs($request->amount - $expectedAmount) > $tolerance) {
            return back()->withErrors([
                'amount' => 'Le montant doit être exactement ' . number_format($expectedAmount, 2, ',', ' ') . ' USD pour un remboursement anticipé complet.'
            ])->withInput();
        }

        // Utiliser une transaction pour garantir la cohérence
        \DB::beginTransaction();
        try {
            // Créer le remboursement
            $repayment = LoanRepayment::create([
                'amount' => $request->amount,
                'loanDocIdFk' => $loan->loanDocId,
                'repaymentDate' => $request->repaymentDate,
                'repaymentTypeIdFk' => $request->repaymentTypeIdFk,
                'userIdFk' => Auth::id(),
                'description' => $request->description ?? 'Remboursement anticipé'
            ]);

            // Créer les enregistrements d'annulation d'intérêts
            foreach ($earlyRepaymentDetails['monthsToCancel'] as $monthData) {
                InterestCancellation::create([
                    'loanDocIdFk' => $loan->loanDocId,
                    'repaymentIdFk' => $repayment->loanRepaymentId,
                    'cancelledMonth' => $monthData['month'],
                    'cancelledInterestAmount' => $monthData['interest']
                ]);
            }

            // Enregistrer dans le cashflow (catégorie spécifique pour remboursement anticipé)
            $cashflowService = new CashflowService();
            $cashflowService->recordEarlyRepayment($repayment, $loan);

            // Marquer le crédit comme terminé
            $loan->update([
                'status' => 'done',
                'endedDate' => $repaymentDate
            ]);

            // Log dans l'historique
            $totalInterestCancelled = $earlyRepaymentDetails['interetsACanceler'];
            Historique::create([
                'recordIdFk' => $loan->loanDocId,
                'recordStatus' => 'early_repayment',
                'operDescription' => 'Remboursement anticipé: ' . number_format($request->amount, 2) . ' USD - Intérêts annulés: ' . number_format($totalInterestCancelled, 2) . ' USD',
                'userIdFk' => Auth::id()
            ]);

            \DB::commit();

            return redirect()->route('caissiere.loans.show', $loan->loanDocId)
                ->with('success', 'Remboursement anticipé enregistré avec succès. ' . count($earlyRepaymentDetails['monthsToCancel']) . ' mois d\'intérêts ont été annulés.');

        } catch (\Exception $e) {
            \DB::rollBack();
            
            return back()->withErrors([
                'error' => 'Une erreur est survenue lors de l\'enregistrement: ' . $e->getMessage()
            ])->withInput();
        }
    }
}
