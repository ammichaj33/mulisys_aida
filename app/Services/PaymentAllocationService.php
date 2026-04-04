<?php

namespace App\Services;

use App\Models\LoanDoc;
use App\Models\LoanRepayment;
use App\Models\Penalty;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PaymentAllocationService
{
    /**
     * Allocate payment amount: Penalties → Interest → Capital (in chronological order)
     * Les pénalités sont toujours payées en priorité avant tout autre paiement
     * 
     * @param LoanDoc $loan
     * @param float $paymentAmount
     * @param Carbon $paymentDate
     * @return array ['interest' => float, 'penalties' => float, 'capital' => float, 'total' => float]
     */
    public function allocatePayment(LoanDoc $loan, $paymentAmount, Carbon $paymentDate)
    {
        $remainingAmount = $paymentAmount;

        // Step 1: Pay penalties FIRST (en priorité absolue)
        $penaltiesDue = $this->calculatePenaltiesDue($loan->loanDocId);
        $penaltiesPaid = min($remainingAmount, $penaltiesDue);
        $remainingAmount -= $penaltiesPaid;

        // Step 2: Pay interest second
        $interestDue = $this->calculateInterestDue($loan, $paymentDate);
        $interestPaid = min($remainingAmount, $interestDue);
        $remainingAmount -= $interestPaid;

        // Step 3: Allocate remaining to capital (chronological order)
        $capitalAllocated = $this->allocateToCapital($loan, $remainingAmount, $paymentDate);

        // Step 4: Mark penalties as paid if applicable
        if ($penaltiesPaid > 0) {
            $this->markPenaltiesAsPaid($loan->loanDocId, $penaltiesPaid);
        }

        $result = [
            'interest' => round($interestPaid, 2),
            'penalties' => round($penaltiesPaid, 2),
            'capital' => round($capitalAllocated, 2),
            'total' => round($paymentAmount, 2)
        ];

        // Verify consistency
        $totalAllocated = $result['interest'] + $result['penalties'] + $result['capital'];
        if (abs($totalAllocated - $paymentAmount) > 0.01) {
            Log::warning("Payment allocation mismatch for loan {$loan->loanDocId}: Expected {$paymentAmount}, got {$totalAllocated}");
        }

        return $result;
    }

    /**
     * Calculate interest due up to a specific date
     */
    public function calculateInterestDue(LoanDoc $loan, Carbon $date)
    {
        $calculationService = new LoanCalculationService();
        $interestCalculation = $calculationService->calculateMonthlyDegressiveInterest(
            $loan->requestAmount,
            $loan->interestRate,
            $loan->loanMonths,
            $loan->submitDate->format('Y-m-d')
        );

        // Récupérer les remboursements existants (exclure le nouveau remboursement en cours de création)
        $repayments = LoanRepayment::where('loanDocIdFk', $loan->loanDocId)
            ->orderBy('created_at', 'asc')
            ->get();
        
        $interestDue = 0;

        foreach ($interestCalculation['schedule'] as $payment) {
            $dueDate = Carbon::parse($payment['date']);

            // Only consider installments due before or on the payment date
            if ($dueDate->lte($date)) {
                // Check if this installment is fully paid
                $amountPaid = $this->calculateActualRepaidBeforeDate($repayments, $dueDate);
                $expectedAmount = $payment['montant_total'];

                if ($amountPaid < $expectedAmount) {
                    // Installment not fully paid, interest is due
                    $interestDue += $payment['interet'];
                }
            }
        }

        return round($interestDue, 2);
    }

    /**
     * Calculate penalties due (unpaid penalties)
     */
    private function calculatePenaltiesDue($loanId)
    {
        $penalties = Penalty::where('loanDocIdFk', $loanId)
            ->where('status', 'notPaid')
            ->get();

        $total = 0;
        foreach ($penalties as $penalty) {
            $total += $penalty->amount;
        }

        return round($total, 2);
    }

    /**
     * Allocate payment to capital in chronological order
     */
    private function allocateToCapital(LoanDoc $loan, $amount, Carbon $date)
    {
        if ($amount <= 0) {
            return 0;
        }

        $calculationService = new LoanCalculationService();
        $interestCalculation = $calculationService->calculateMonthlyDegressiveInterest(
            $loan->requestAmount,
            $loan->interestRate,
            $loan->loanMonths,
            $loan->submitDate->format('Y-m-d')
        );

        // Récupérer les remboursements existants (exclure le nouveau remboursement en cours de création)
        $repayments = LoanRepayment::where('loanDocIdFk', $loan->loanDocId)
            ->orderBy('created_at', 'asc')
            ->get();
        
        $remainingAmount = $amount;
        $capitalAllocated = 0;

        // Process installments in chronological order
        foreach ($interestCalculation['schedule'] as $payment) {
            if ($remainingAmount <= 0) {
                break;
            }

            $dueDate = Carbon::parse($payment['date']);

            // Only consider installments due before or on the payment date
            if ($dueDate->lte($date)) {
                // Check if this installment is fully paid
                $amountPaid = $this->calculateActualRepaidBeforeDate($repayments, $dueDate);
                $expectedAmount = $payment['montant_total'];

                if ($amountPaid < $expectedAmount && $expectedAmount > 0) {
                    // Installment not fully paid
                    $remainingToPay = $expectedAmount - $amountPaid;

                    // Calculate capital portion of remaining amount
                    // Capital ratio = capital payment / total payment
                    $capitalRatio = $payment['remboursement_fixe'] / $expectedAmount;
                    $capitalDue = $remainingToPay * $capitalRatio;

                    // Allocate available amount
                    $allocation = min($remainingAmount, $capitalDue);
                    $capitalAllocated += $allocation;
                    $remainingAmount -= $allocation;
                }
            }
        }

        return round($capitalAllocated, 2);
    }

    /**
     * Mark penalties as paid and record in cashflow
     */
    private function markPenaltiesAsPaid($loanId, $amountToPay)
    {
        $penalties = Penalty::where('loanDocIdFk', $loanId)
            ->where('status', 'notPaid')
            ->orderBy('penaltyMonth', 'asc') // Pay oldest penalties first
            ->get();

        if ($penalties->isEmpty() || $amountToPay <= 0) {
            return;
        }

        $remainingToPay = $amountToPay;
        $loan = \App\Models\LoanDoc::find($loanId);
        
        if (!$loan) {
            Log::error("Loan not found when marking penalties as paid", ['loanId' => $loanId]);
            return;
        }

        $cashflowService = new \App\Services\CashflowService();

        foreach ($penalties as $penalty) {
            if ($remainingToPay <= 0) {
                break;
            }

            $amountToDeduct = min($remainingToPay, $penalty->amount);
            $remainingToPay -= $amountToDeduct;

            if ($amountToDeduct >= $penalty->amount) {
                // Full penalty paid - update status first
                $penalty->update([
                    'status' => 'paid',
                    'paidAt' => now(),
                    'paidAmount' => $penalty->amount
                ]);

                // Refresh the penalty model to get updated data
                $penalty->refresh();

                // Record penalty payment in cashflow
                try {
                    $cashflowService->recordPenaltyPayment($penalty, $loan);
                } catch (\Exception $e) {
                    Log::error("Error recording penalty payment in cashflow", [
                        'penaltyId' => $penalty->penalityId,
                        'loanId' => $loanId,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                // Partial payment - create a partial payment record
                // For now, we only mark as paid if full amount is covered
                // This ensures consistency
                Log::warning("Partial penalty payment detected but not fully implemented", [
                    'penaltyId' => $penalty->penalityId,
                    'amountToDeduct' => $amountToDeduct,
                    'penaltyAmount' => $penalty->amount
                ]);
            }
        }
    }

    /**
     * Calculate actual repaid amount before a specific date
     */
    private function calculateActualRepaidBeforeDate($repayments, Carbon $date)
    {
        $actualAmount = 0;
        foreach ($repayments as $repayment) {
            $repaymentDate = Carbon::parse($repayment->created_at);
            if ($repaymentDate->lt($date)) {
                $actualAmount += $repayment->amount;
            }
        }
        return $actualAmount;
    }

    /**
     * Generate description for repayment allocation (shortened version to fit database constraints)
     * Ordre d'affichage : Pénalités → Intérêts → Capital (ordre de priorité de paiement)
     */
    public function generateAllocationDescription($allocation)
    {
        $parts = [];
        
        // Afficher les pénalités en premier (payées en priorité)
        if ($allocation['penalties'] > 0) {
            $parts[] = 'Pen:' . number_format($allocation['penalties'], 2);
        }
        
        if ($allocation['interest'] > 0) {
            $parts[] = 'Int:' . number_format($allocation['interest'], 2);
        }
        
        if ($allocation['capital'] > 0) {
            $parts[] = 'Cap:' . number_format($allocation['capital'], 2);
        }

        return implode('|', $parts);
    }
}

