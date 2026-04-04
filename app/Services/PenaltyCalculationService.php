<?php

namespace App\Services;

use App\Models\LoanDoc;
use App\Models\Penalty;
use App\Models\Historique;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PenaltyCalculationService
{
    /**
     * Calculate penalties for all loans with overdue repayments
     */
    public function calculateAllPenalties()
    {
        $loans = LoanDoc::whereIn('status', ['validated', 'done'])
            ->with(['loanRepayments', 'member'])
            ->get();

        $totalPenalties = 0;

        foreach ($loans as $loan) {
            $penalty = $this->calculateLoanPenalty($loan);
            if ($penalty > 0) {
                $totalPenalties++;
            }
        }

        return $totalPenalties;
    }

    /**
     * Calculate penalty for a specific loan with new rules:
     * - First delay: (Capital + Interest) × 10%
     * - Consecutive delay: ((Capital M-1 + Interest M-1 + Penalty M-1) + (Capital M + Interest M)) × 10%
     */
    public function calculateLoanPenalty(LoanDoc $loan)
    {
        $toleranceDays = config('penalties.tolerance_days', 30);
        $penaltyRate = config('penalties.penalty_rate', 10.0); // Taux fixe de 10%

        // Si le crédit n'est pas validé, pas de pénalité
        if ($loan->status !== 'validated') {
            return 0;
        }

        // Calculer les échéances mensuelles
        $calculationService = new LoanCalculationService();
        $interestCalculation = $calculationService->calculateMonthlyDegressiveInterest(
            $loan->requestAmount,
            $loan->interestRate,
            $loan->loanMonths,
            $loan->submitDate->format('Y-m-d')
        );

        // Récupérer tous les remboursements triés par date
        $repayments = $loan->loanRepayments()->orderBy('created_at', 'asc')->get();

        $totalPenalty = 0;
        $currentDate = Carbon::now();

        // Vérifier chaque échéance individuellement
        foreach ($interestCalculation['schedule'] as $index => $payment) {
            $dueDate = Carbon::parse($payment['date']);

            // Si l'échéance n'est pas encore arrivée, pas de pénalité
            if ($dueDate->isFuture()) {
                continue;
            }

            // Calculer le montant dû à cette échéance spécifique
            $expectedAmountAtDueDate = $payment['montant_total'];

            // Calculer le montant réellement remboursé AVANT cette échéance
            $actualRepaidBeforeDueDate = $this->calculateActualRepaidBeforeDate($repayments, $dueDate);

            // Si le remboursement est insuffisant pour cette échéance
            if ($actualRepaidBeforeDueDate < $expectedAmountAtDueDate) {
                // Calculer les mois de retard (au-delà de la tolérance)
                $monthsOverdue = $this->calculateMonthsOverdue($dueDate, $currentDate, $toleranceDays);

                if ($monthsOverdue > 0) {
                    // Calculer la pénalité avec la nouvelle formule
                    $penaltyAmount = $this->calculatePenaltyForMonth(
                        $index,
                        $payment,
                        $dueDate,
                        $loan,
                        $interestCalculation['schedule'],
                        $repayments,
                        $penaltyRate
                    );

                    if ($penaltyAmount > 0) {
                        // Vérifier si une pénalité existe déjà pour cette échéance
                        $existingPenalty = Penalty::where('loanDocIdFk', $loan->loanDocId)
                            ->where('penaltyMonth', $dueDate->format('Y-m'))
                            ->where('status', 'notPaid')
                            ->first();

                        if (!$existingPenalty) {
                            // Créer la pénalité
                            $penalty = Penalty::create([
                                'loanDocIdFk' => $loan->loanDocId,
                                'memberIdFk' => $loan->memberIdFk,
                                'penaltyMonth' => $dueDate->format('Y-m'),
                                'amount' => round($penaltyAmount, 2),
                                'reason' => 'retard',
                                'description' => $this->generatePenaltyDescription($index, $dueDate, $penaltyAmount, $loan, $interestCalculation['schedule']),
                                'status' => 'notPaid',
                                'created_at' => now(),
                                'createdBy' => Auth::id() ?? 1
                            ]);

                            // Logger l'opération
                            Historique::create([
                                'recordIdFk' => $loan->loanDocId,
                                'recordStatus' => 'penalty_created',
                                'operDescription' => 'Pénalité créée: ' . number_format($penaltyAmount, 2) . ' USD pour échéance ' . ($index + 1),
                                'userIdFk' => Auth::id() ?? 1
                            ]);

                            $totalPenalty += $penaltyAmount;
                        }
                    }
                }
            }
        }

        return round($totalPenalty, 2);
    }

    /**
     * Calculate penalty for a specific month with new rules
     */
    private function calculatePenaltyForMonth($index, $currentPayment, $dueDate, $loan, $schedule, $repayments, $penaltyRate)
    {
        // Récupérer les données du mois actuel
        $currentMonthCapital = $currentPayment['capital_restant'];
        $currentMonthInterest = $currentPayment['interet'];
        $currentMonth = $dueDate->format('Y-m');

        // Vérifier si une pénalité notPaid existe pour le mois précédent
        $previousMonth = Carbon::parse($dueDate)->subMonth()->format('Y-m');
        $previousPenalty = $this->getUnpaidPenaltyForMonth($loan->loanDocId, $previousMonth);

        if ($previousPenalty) {
            // Formule cumulée : ((Capital M-1 + Intérêt M-1 + Pénalité M-1) + (Capital M + Intérêt M)) × 10%
            $previousMonthPayment = null;
            if ($index > 0) {
                $previousMonthPayment = $schedule[$index - 1];
            }

            if ($previousMonthPayment) {
                $previousMonthCapital = $previousMonthPayment['capital_restant'];
                $previousMonthInterest = $previousMonthPayment['interet'];
                $previousPenaltyAmount = $previousPenalty->amount;

                $penaltyAmount = (($previousMonthCapital + $previousMonthInterest + $previousPenaltyAmount) 
                                 + ($currentMonthCapital + $currentMonthInterest)) * ($penaltyRate / 100);

                \Log::info("Pénalité cumulée pour {$currentMonth}: (({$previousMonthCapital} + {$previousMonthInterest} + {$previousPenaltyAmount}) + ({$currentMonthCapital} + {$currentMonthInterest})) × {$penaltyRate}% = {$penaltyAmount}");

                return round($penaltyAmount, 2);
            }
        }

        // Formule simple (premier retard) : (Capital M + Intérêt M) × 10%
        $penaltyAmount = ($currentMonthCapital + $currentMonthInterest) * ($penaltyRate / 100);

        \Log::info("Pénalité simple pour {$currentMonth}: ({$currentMonthCapital} + {$currentMonthInterest}) × {$penaltyRate}% = {$penaltyAmount}");

        return round($penaltyAmount, 2);
    }

    /**
     * Get unpaid penalty for a specific month
     */
    private function getUnpaidPenaltyForMonth($loanId, $month)
    {
        return Penalty::where('loanDocIdFk', $loanId)
            ->where('penaltyMonth', $month)
            ->where('status', 'notPaid')
            ->first();
    }

    /**
     * Generate penalty description
     */
    private function generatePenaltyDescription($index, $dueDate, $penaltyAmount, $loan, $schedule)
    {
        $previousMonth = Carbon::parse($dueDate)->subMonth()->format('Y-m');
        $previousPenalty = $this->getUnpaidPenaltyForMonth($loan->loanDocId, $previousMonth);

        if ($previousPenalty) {
            return "Retard consécutif - Échéance " . ($index + 1) . " ({$dueDate->format('d/m/Y')}) - Formule cumulée - Montant: " . number_format($penaltyAmount, 2) . " USD";
        } else {
            return "Premier retard - Échéance " . ($index + 1) . " ({$dueDate->format('d/m/Y')}) - Formule simple - Montant: " . number_format($penaltyAmount, 2) . " USD";
        }
    }

    /**
     * Calculate actual repaid amount before a specific date
     */
    private function calculateActualRepaidBeforeDate($repayments, $date)
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
     * Calculate remaining capital at due date considering partial payments
     */
    private function calculateRemainingCapitalAtDueDate($payment, $repayments, $dueDate, $schedule)
    {
        // Capital initial pour cette échéance
        $initialCapital = $payment['capital_restant'];

        // Calculer le total des paiements effectués avant cette échéance
        $totalRepaidBeforeDue = 0;
        foreach ($repayments as $repayment) {
            $repaymentDate = Carbon::parse($repayment->created_at);
            if ($repaymentDate->lt($dueDate)) {
                $totalRepaidBeforeDue += $repayment->amount;
            }
        }

        // Calculer le total attendu avant cette échéance
        $totalExpectedBeforeDue = 0;
        foreach ($schedule as $schedulePayment) {
            $scheduleDate = Carbon::parse($schedulePayment['date']);
            if ($scheduleDate->lt($dueDate)) {
                $totalExpectedBeforeDue += $schedulePayment['montant_total'];
            }
        }

        // Si aucun paiement n'a été effectué, le capital restant est le capital initial
        if ($totalRepaidBeforeDue == 0) {
            return $initialCapital;
        }

        // Si le paiement est suffisant pour couvrir les échéances précédentes
        if ($totalRepaidBeforeDue >= $totalExpectedBeforeDue) {
            // Le capital restant est le capital initial moins l'amortissement de cette échéance
            return $initialCapital - $payment['remboursement_fixe'];
        }

        // Calculer la proportion de capital remboursé
        $capitalRepaidProportion = $totalRepaidBeforeDue / $totalExpectedBeforeDue;
        $totalCapitalExpectedBeforeDue = 0;

        foreach ($schedule as $schedulePayment) {
            $scheduleDate = Carbon::parse($schedulePayment['date']);
            if ($scheduleDate->lt($dueDate)) {
                $totalCapitalExpectedBeforeDue += $schedulePayment['remboursement_fixe'];
            }
        }

        $capitalRepaid = $capitalRepaidProportion * $totalCapitalExpectedBeforeDue;
        $remainingCapital = $initialCapital - $capitalRepaid;

        return max(0, $remainingCapital);
    }

    /**
     * Calculate months overdue
     */
    private function calculateMonthsOverdue($dueDate, $currentDate, $toleranceDays)
    {
        // Ajouter la tolérance à la date d'échéance
        $toleranceDate = $dueDate->copy()->addDays($toleranceDays);

        // Si on est encore dans la période de tolérance, pas de retard
        if ($currentDate->lte($toleranceDate)) {
            return 0;
        }

        // Calculer la différence en mois
        $monthsOverdue = $toleranceDate->diffInMonths($currentDate);

        // Si c'est exactement un mois, vérifier les jours
        if ($monthsOverdue == 0) {
            $daysOverdue = $toleranceDate->diffInDays($currentDate);
            if ($daysOverdue >= 15) { // Si plus de 15 jours, compter comme 1 mois
                return 1;
            }
        }

        return $monthsOverdue;
    }

    /**
     * Calculate penalty with monthly rate
     */
    private function calculatePenaltyWithMonthlyRate($capitalAmount, $monthlyRate, $monthsOverdue)
    {
        // Formule : Capital restant × Taux mensuel × Mois de retard
        return $capitalAmount * ($monthlyRate / 100) * $monthsOverdue;
    }

    /**
     * Calculate expected amount at a specific date according to schedule
     */
    private function calculateExpectedAmountAtDate($schedule, $date)
    {
        $expectedAmount = 0;
        foreach ($schedule as $payment) {
            $paymentDate = Carbon::parse($payment['date']);
            if ($paymentDate->lte($date)) {
                $expectedAmount += $payment['montant_total'];
            }
        }
        return $expectedAmount;
    }

    /**
     * Calculate actual repaid amount at a specific date
     */
    private function calculateActualRepaidAtDate($repayments, $date)
    {
        $actualAmount = 0;
        foreach ($repayments as $repayment) {
            $repaymentDate = Carbon::parse($repayment->created_at);
            if ($repaymentDate->lte($date)) {
                $actualAmount += $repayment->amount;
            }
        }
        return $actualAmount;
    }

    /**
     * Calculate penalty for a specific month
     */
    private function calculateMonthlyPenalty($baseAmount, $penaltyPercentage, $daysOverdue, $toleranceDays, $maxPenaltyPercentage)
    {
        $effectiveDays = $daysOverdue - $toleranceDays;
        $effectivePenaltyPercentage = min($penaltyPercentage * $effectiveDays, $maxPenaltyPercentage);

        return ($baseAmount * $effectivePenaltyPercentage) / 100;
    }

    /**
     * Get penalties for a specific loan
     */
    public function getLoanPenalties($loanId)
    {
        return Penalty::where('loanDocIdFk', $loanId)
            ->where('status', 'notPaid')
            ->orderBy('penaltyMonth', 'desc')
            ->get();
    }

    /**
     * Pay a penalty
     */
    public function payPenalty($penaltyId, $amount = null)
    {
        $penalty = Penalty::findOrFail($penaltyId);

        if ($penalty->status === 'paid') {
            return false;
        }

        $penalty->update([
            'status' => 'paid',
            'paidAt' => now(),
            'paidAmount' => $amount ?? $penalty->amount
        ]);

        // Enregistrer automatiquement dans le cashflow
        $loan = LoanDoc::findOrFail($penalty->loanDocIdFk);
        $cashflowService = new \App\Services\CashflowService();
        $cashflowService->recordPenaltyPayment($penalty, $loan);

        // Log the operation
        Historique::create([
            'recordIdFk' => $penalty->loanDocIdFk,
            'recordStatus' => 'penalty_paid',
            'operDescription' => 'Pénalité payée: ' . number_format($penalty->amount, 2) . ' USD',
            'userIdFk' => Auth::id()
        ]);

        return true;
    }

    /**
     * Get total pending penalties for a loan
     */
    public function getTotalPendingPenalties($loanId)
    {
        return Penalty::where('loanDocIdFk', $loanId)
            ->where('status', 'notPaid')
            ->sum('amount');
    }
}
