<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penalty;
use App\Models\LoanDoc;
use App\Services\PenaltyCalculationService;
use Illuminate\Support\Facades\Auth;

class PenaltyController extends Controller
{
    protected $penaltyService;

    public function __construct()
    {
        $this->penaltyService = new PenaltyCalculationService();
    }

    /**
     * Display a listing of penalties
     */
    public function index(Request $request)
    {
        $query = Penalty::with(['loanDoc.member']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by loan
        if ($request->filled('loan_id')) {
            $query->where('loanDocIdFk', $request->loan_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhereHas('loanDoc', function($loanQuery) use ($search) {
                      $loanQuery->where('refNumber', 'like', "%{$search}%")
                               ->orWhereHas('member', function($memberQuery) use ($search) {
                                   $memberQuery->where('firstName', 'like', "%{$search}%")
                                              ->orWhere('lastName', 'like', "%{$search}%");
                               });
                  });
            });
        }

        $penalties = $query->orderBy('created_at', 'desc')->paginate(20);

        // Statistics
        $stats = [
            'total' => Penalty::count(),
            'pending' => Penalty::where('status', 'notPaid')->count(),
            'paid' => Penalty::where('status', 'paid')->count(),
            'total_amount' => Penalty::where('status', 'notPaid')->sum('amount'),
            'paid_amount' => Penalty::where('status', 'paid')->sum('amount'),
        ];

        return view('penalties.index', compact('penalties', 'stats'));
    }

    /**
     * Show penalty details
     */
    public function show($id)
    {
        $penalty = Penalty::with(['loanDoc.member', 'loanDoc.loanRepayments'])->findOrFail($id);
        
        // Calculer les détails de la pénalité pour affichage
        $penaltyDetails = $this->calculatePenaltyDetails($penalty);
        
        return view('penalties.show', compact('penalty', 'penaltyDetails'));
    }
    
    /**
     * Calculate penalty details for display
     */
    private function calculatePenaltyDetails($penalty)
    {
        $loan = $penalty->loanDoc;
        $calculationService = new \App\Services\LoanCalculationService();
        
        // Calculer le calendrier de remboursement
        $interestCalculation = $calculationService->calculateMonthlyDegressiveInterest(
            $loan->requestAmount,
            $loan->interestRate,
            $loan->loanMonths,
            $loan->submitDate->format('Y-m-d')
        );
        
        // Trouver l'échéance correspondant à cette pénalité
        $penaltyMonth = $penalty->penaltyMonth;
        $correspondingPayment = null;
        
        foreach ($interestCalculation['schedule'] as $payment) {
            $paymentDate = \Carbon\Carbon::parse($payment['date']);
            if ($paymentDate->format('Y-m') === $penaltyMonth) {
                $correspondingPayment = $payment;
                break;
            }
        }
        
        if (!$correspondingPayment) {
            return null;
        }
        
        // Calculer le capital restant dû à cette échéance
        $dueDate = \Carbon\Carbon::parse($correspondingPayment['date']);
        $repayments = $loan->loanRepayments()->orderBy('created_at', 'asc')->get();
        
        $totalRepaidBeforeDue = 0;
        foreach ($repayments as $repayment) {
            $repaymentDate = \Carbon\Carbon::parse($repayment->created_at);
            if ($repaymentDate->lt($dueDate)) {
                $totalRepaidBeforeDue += $repayment->amount;
            }
        }
        
        $expectedAmountAtDueDate = $correspondingPayment['montant_total'];
        $remainingCapitalAtDueDate = $correspondingPayment['capital_restant'];
        
        // Si des paiements partiels ont été effectués
        if ($totalRepaidBeforeDue > 0) {
            $totalExpectedBeforeDue = 0;
            $totalCapitalExpectedBeforeDue = 0;
            
            foreach ($interestCalculation['schedule'] as $schedulePayment) {
                $scheduleDate = \Carbon\Carbon::parse($schedulePayment['date']);
                if ($scheduleDate->lt($dueDate)) {
                    $totalExpectedBeforeDue += $schedulePayment['montant_total'];
                    $totalCapitalExpectedBeforeDue += $schedulePayment['remboursement_fixe'];
                }
            }
            
            if ($totalExpectedBeforeDue > 0) {
                $capitalRepaidProportion = $totalRepaidBeforeDue / $totalExpectedBeforeDue;
                $capitalRepaid = $capitalRepaidProportion * $totalCapitalExpectedBeforeDue;
                $remainingCapitalAtDueDate = max(0, $correspondingPayment['capital_restant'] - $capitalRepaid);
            }
        }
        
        // Calculer les mois de retard
        $toleranceDays = config('penalties.tolerance_days', 30);
        $toleranceDate = $dueDate->copy()->addDays($toleranceDays);
        $currentDate = \Carbon\Carbon::now();
        
        $monthsOverdue = 0;
        if ($currentDate->gt($toleranceDate)) {
            $monthsOverdue = $toleranceDate->diffInMonths($currentDate);
            if ($monthsOverdue == 0) {
                $daysOverdue = $toleranceDate->diffInDays($currentDate);
                if ($daysOverdue >= 15) {
                    $monthsOverdue = 1;
                }
            }
        }

        // Vérifier si une pénalité notPaid existe pour le mois précédent
        $previousMonth = $dueDate->copy()->subMonth()->format('Y-m');
        $previousPenalty = \App\Models\Penalty::where('loanDocIdFk', $loan->loanDocId)
            ->where('penaltyMonth', $previousMonth)
            ->where('status', 'notPaid')
            ->first();

        // Calculer la pénalité avec la nouvelle formule
        $penaltyRate = config('penalties.penalty_rate', 10.0);
        $currentMonthCapital = $remainingCapitalAtDueDate;
        $currentMonthInterest = $correspondingPayment['interet'];

        $isConsecutive = $previousPenalty !== null;
        $calculatedPenalty = 0;
        $formula = '';

        if ($isConsecutive && $monthsOverdue > 0) {
            // Formule cumulée
            $previousMonthIndex = null;
            foreach ($interestCalculation['schedule'] as $idx => $schedulePayment) {
                $scheduleDate = \Carbon\Carbon::parse($schedulePayment['date']);
                if ($scheduleDate->format('Y-m') === $previousMonth) {
                    $previousMonthIndex = $idx;
                    break;
                }
            }

            if ($previousMonthIndex !== null) {
                $previousMonthPayment = $interestCalculation['schedule'][$previousMonthIndex];
                $previousMonthCapital = $previousMonthPayment['capital_restant'];
                $previousMonthInterest = $previousMonthPayment['interet'];
                $previousPenaltyAmount = $previousPenalty->amount;

                $calculatedPenalty = (($previousMonthCapital + $previousMonthInterest + $previousPenaltyAmount) 
                                     + ($currentMonthCapital + $currentMonthInterest)) * ($penaltyRate / 100);
                $formula = "(({$previousMonthCapital} + {$previousMonthInterest} + {$previousPenaltyAmount}) + ({$currentMonthCapital} + {$currentMonthInterest})) × {$penaltyRate}%";
            } else {
                // Si on ne trouve pas le mois précédent, utiliser la formule simple
                $calculatedPenalty = ($currentMonthCapital + $currentMonthInterest) * ($penaltyRate / 100);
                $formula = "({$currentMonthCapital} + {$currentMonthInterest}) × {$penaltyRate}%";
                $isConsecutive = false;
            }
        }

        if (!$isConsecutive || $calculatedPenalty == 0) {
            // Formule simple (premier retard)
            $calculatedPenalty = ($currentMonthCapital + $currentMonthInterest) * ($penaltyRate / 100);
            $formula = "({$currentMonthCapital} + {$currentMonthInterest}) × {$penaltyRate}%";
        }
        
        return [
            'due_date' => $dueDate,
            'expected_amount' => $expectedAmountAtDueDate,
            'total_repaid_before_due' => $totalRepaidBeforeDue,
            'remaining_capital' => $remainingCapitalAtDueDate,
            'months_overdue' => $monthsOverdue,
            'penalty_rate' => $penaltyRate,
            'calculated_penalty' => round($calculatedPenalty, 2),
            'formula' => $formula,
            'is_consecutive' => $isConsecutive,
            'previous_penalty' => $previousPenalty,
            'corresponding_payment' => $correspondingPayment
        ];
    }

    /**
     * Pay a penalty
     */
    public function pay(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
        ]);

        $penalty = Penalty::findOrFail($id);

        if ($penalty->status === 'paid') {
            return back()->with('error', 'Cette pénalité a déjà été payée.');
        }

        $success = $this->penaltyService->payPenalty($id, $request->amount);

        if ($success) {
            return back()->with('success', 'Pénalité payée avec succès.');
        }

        return back()->with('error', 'Erreur lors du paiement de la pénalité.');
    }

    /**
     * Calculate penalties for a specific loan
     */
    public function calculateForLoan($loanId)
    {
        $loan = LoanDoc::with(['member', 'loanRepayments'])->findOrFail($loanId);
        
        $penaltyAmount = $this->penaltyService->calculateLoanPenalty($loan);

        if ($penaltyAmount > 0) {
            return back()->with('success', 'Pénalités calculées: ' . number_format($penaltyAmount, 2) . ' USD');
        }

        return back()->with('info', 'Aucune pénalité à calculer pour ce crédit.');
    }

    /**
     * Calculate all penalties
     */
    public function calculateAll()
    {
        $totalPenalties = $this->penaltyService->calculateAllPenalties();
        
        return back()->with('success', "Pénalités calculées pour {$totalPenalties} crédits.");
    }
}
