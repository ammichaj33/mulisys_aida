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
        $summary = $this->recalculateAllPenalties(false);

        return $summary['loans'];
    }

    /**
     * Recalculate penalties for all eligible loans (create or update notPaid records).
     */
    public function recalculateAllPenalties(bool $updateExisting = true): array
    {
        $loans = LoanDoc::whereIn('status', ['validated', 'done'])
            ->with(['loanRepayments', 'member'])
            ->get();

        $summary = [
            'loans' => 0,
            'created' => 0,
            'updated' => 0,
            'total_amount' => 0,
        ];

        foreach ($loans as $loan) {
            $result = $this->processLoanPenalties($loan, $updateExisting);

            if ($result['created'] > 0 || $result['updated'] > 0) {
                $summary['loans']++;
            }

            $summary['created'] += $result['created'];
            $summary['updated'] += $result['updated'];
            $summary['total_amount'] += $result['total_amount'];
        }

        $summary['total_amount'] = round($summary['total_amount'], 2);

        return $summary;
    }

    /**
     * Recalculate penalties for a single loan (create or update notPaid records).
     */
    public function recalculateLoanPenalties(LoanDoc $loan): array
    {
        return $this->processLoanPenalties($loan, true);
    }

    /**
     * Calculate penalty for a specific loan with rules:
     * - M-1 : (Capital M-1 + Intérêt M-1) × 10%
     * - M-2 : (Reste M-1 + Capital M-2 + Intérêt M-2) × 10%
     * - M-3 : (Reste M-1 + Reste M-2 + Capital M-3 + Intérêt M-3) × 10%
     * - etc.
     *   Reste M-x = échéance M-x impayée + pénalité M-x impayée
     *   Seuls les remboursements effectués pendant la tolérance de M-n (échéance + 30 jours) réduisent le Reste M-x.
     */
    public function calculateLoanPenalty(LoanDoc $loan)
    {
        $result = $this->processLoanPenalties($loan, false);

        return $result['total_amount'];
    }

    /**
     * Process penalties for a loan.
     *
     * @param  bool  $updateExisting  When true, update existing notPaid penalties with recalculated amounts.
     */
    private function processLoanPenalties(LoanDoc $loan, bool $updateExisting): array
    {
        $summary = [
            'created' => 0,
            'updated' => 0,
            'total_amount' => 0,
        ];

        if ($loan->status !== 'validated') {
            return $summary;
        }

        $toleranceDays = config('penalties.tolerance_days', 30);
        $penaltyRate = config('penalties.penalty_rate', 10.0);

        $calculationService = new LoanCalculationService();
        $interestCalculation = $calculationService->calculateMonthlyDegressiveInterest(
            $loan->requestAmount,
            $loan->interestRate,
            $loan->loanMonths,
            $loan->submitDate->format('Y-m-d')
        );

        $repayments = $loan->loanRepayments()->orderBy('repaymentDate', 'asc')->orderBy('created_at', 'asc')->get();
        $currentDate = Carbon::now();

        foreach ($interestCalculation['schedule'] as $index => $payment) {
            $dueDate = LoanCalculationService::normalizeYear(Carbon::parse($payment['date']));

            if ($dueDate->isFuture()) {
                continue;
            }

            if ($this->isInstallmentUnpaid($index, $interestCalculation['schedule'], $repayments)) {
                $monthsOverdue = $this->calculateMonthsOverdue($dueDate, $currentDate, $toleranceDays);

                if ($monthsOverdue > 0) {
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
                        $penaltyMonth = $dueDate->format('Y-m');
                        $description = $this->generatePenaltyDescription(
                            $index,
                            $dueDate,
                            $penaltyAmount,
                            $loan,
                            $interestCalculation['schedule'],
                            $repayments
                        );

                        $existingPenalty = Penalty::where('loanDocIdFk', $loan->loanDocId)
                            ->where('penaltyMonth', $penaltyMonth)
                            ->where('status', 'notPaid')
                            ->first();

                        if ($existingPenalty) {
                            if ($updateExisting) {
                                $existingPenalty->update([
                                    'amount' => round($penaltyAmount, 2),
                                    'description' => $description,
                                ]);

                                Historique::create([
                                    'recordIdFk' => $loan->loanDocId,
                                    'recordStatus' => 'penalty_recalculated',
                                    'operDescription' => 'Pénalité recalculée: ' . number_format($penaltyAmount, 2) . ' USD pour échéance ' . ($index + 1),
                                    'userIdFk' => Auth::id() ?? 1,
                                ]);

                                $summary['updated']++;
                                $summary['total_amount'] += $penaltyAmount;
                            }
                        } else {
                            Penalty::create([
                                'loanDocIdFk' => $loan->loanDocId,
                                'memberIdFk' => $loan->memberIdFk,
                                'penaltyMonth' => $penaltyMonth,
                                'amount' => round($penaltyAmount, 2),
                                'reason' => 'retard',
                                'description' => $description,
                                'status' => 'notPaid',
                                'created_at' => now(),
                                'createdBy' => Auth::id() ?? 1,
                            ]);

                            Historique::create([
                                'recordIdFk' => $loan->loanDocId,
                                'recordStatus' => 'penalty_created',
                                'operDescription' => 'Pénalité créée: ' . number_format($penaltyAmount, 2) . ' USD pour échéance ' . ($index + 1),
                                'userIdFk' => Auth::id() ?? 1,
                            ]);

                            $summary['created']++;
                            $summary['total_amount'] += $penaltyAmount;
                        }
                    }
                }
            }
        }

        $summary['total_amount'] = round($summary['total_amount'], 2);

        return $summary;
    }

    /**
     * Calculate penalty for a specific month.
     */
    private function calculatePenaltyForMonth($index, $currentPayment, $dueDate, $loan, $schedule, $repayments, $penaltyRate)
    {
        $details = $this->buildPenaltyCalculationDetails(
            $loan->loanDocId,
            $index,
            $currentPayment,
            $dueDate,
            $schedule,
            $repayments,
            $penaltyRate
        );

        $currentMonth = $dueDate->format('Y-m');
        $logType = $details['is_consecutive'] ? 'cumulée (reste M-1)' : 'simple';
        \Log::info("Pénalité {$logType} pour {$currentMonth}: {$details['formula']} = {$details['calculated_penalty']}");

        return $details['calculated_penalty'];
    }

    /**
     * Part capital de l'échéance (amortissement mensuel, pas le capital restant global).
     */
    private function getInstallmentCapitalAmount(array $payment): float
    {
        return (float) ($payment['remboursement_fixe'] ?? $payment['rembfixe'] ?? 0);
    }

    /**
     * Build penalty calculation details for a given installment.
     */
    private function buildPenaltyCalculationDetails($loanId, $index, $currentPayment, $dueDate, $schedule, $repayments, $penaltyRate)
    {
        $currentMonthCapital = $this->getInstallmentCapitalAmount($currentPayment);
        $currentMonthInterest = $currentPayment['interet'];
        $currentMonthBase = $currentMonthCapital + $currentMonthInterest;

        $installmentNumber = $index + 1;
        $toleranceDays = (int) config('penalties.tolerance_days', 30);
        $previousRemaining = $this->calculatePreviousMonthsRemaining(
            $loanId,
            $index,
            $schedule,
            $repayments,
            $dueDate,
            $toleranceDays
        );
        $isConsecutive = $previousRemaining['total'] > 0;

        if ($isConsecutive) {
            $baseAmount = $previousRemaining['total'] + $currentMonthBase;
        } else {
            $baseAmount = $currentMonthBase;
        }

        $formulas = $this->buildPenaltyFormulaStrings(
            $installmentNumber,
            $currentMonthCapital,
            $currentMonthInterest,
            $previousRemaining,
            $penaltyRate
        );

        $calculatedPenalty = round($baseAmount * ($penaltyRate / 100), 2);

        return [
            'current_month_capital' => round($currentMonthCapital, 2),
            'current_month_interest' => round($currentMonthInterest, 2),
            'previous_remaining' => $previousRemaining,
            'is_consecutive' => $isConsecutive,
            'penalty_rate' => $penaltyRate,
            'calculated_penalty' => $calculatedPenalty,
            'formula' => $formulas['numeric'],
            'formula_labelled' => $formulas['labelled'],
        ];
    }

    /**
     * Build numeric and labelled formula strings.
     */
    private function buildPenaltyFormulaStrings($installmentNumber, $capital, $interest, array $previousRemaining, $penaltyRate): array
    {
        if ($installmentNumber === 1 || $previousRemaining['total'] <= 0) {
            $numeric = '(' . number_format($capital, 2, '.', '')
                . ' + ' . number_format($interest, 2, '.', '') . ') × ' . $penaltyRate . '%';
            $labelled = '(Capital M-1 + Intérêt M-1) × ' . $penaltyRate . '%';

            return ['numeric' => $numeric, 'labelled' => $labelled];
        }

        $numericParts = [];
        $labelledParts = [];

        foreach ($previousRemaining['breakdown'] as $month) {
            $numericParts[] = number_format($month['subtotal'], 2, '.', '');
            $labelledParts[] = 'Reste M-' . $month['installment_number'];
        }

        $numericParts[] = number_format($capital, 2, '.', '');
        $numericParts[] = number_format($interest, 2, '.', '');
        $labelledParts[] = 'Capital M-' . $installmentNumber;
        $labelledParts[] = 'Intérêt M-' . $installmentNumber;

        return [
            'numeric' => '(' . implode(' + ', $numericParts) . ') × ' . $penaltyRate . '%',
            'labelled' => '(' . implode(' + ', $labelledParts) . ') × ' . $penaltyRate . '%',
        ];
    }

    /**
     * Date effective d'un remboursement (date de paiement saisie par la caissière).
     */
    private function getRepaymentEffectiveDate($repayment): Carbon
    {
        if (!empty($repayment->repaymentDate)) {
            return LoanCalculationService::normalizeYear(Carbon::parse($repayment->repaymentDate))->startOfDay();
        }

        return LoanCalculationService::normalizeYear(Carbon::parse($repayment->created_at))->startOfDay();
    }

    /**
     * Fin de la période de tolérance pour une échéance.
     */
    private function getToleranceEndDate(Carbon $dueDate, int $toleranceDays): Carbon
    {
        return $dueDate->copy()->addDays($toleranceDays);
    }

    /**
     * Total remboursé pendant la tolérance de M-n (du jour de l'échéance jusqu'à échéance + tolérance).
     */
    private function getTotalRepaidUpToToleranceEnd($repayments, Carbon $dueDate, int $toleranceDays): float
    {
        $cutoff = $this->getToleranceEndDate($dueDate, $toleranceDays)->endOfDay();
        $total = 0;

        foreach ($repayments as $repayment) {
            if ($this->getRepaymentEffectiveDate($repayment)->lte($cutoff)) {
                $total += (float) $repayment->amount;
            }
        }

        return round($total, 2);
    }

    /**
     * Reste M-x for all previous months (M-1 through M-(n-1)):
     * échéance impayée + pénalité impayée, comptés à la fin de la tolérance de M-n.
     */
    private function calculatePreviousMonthsRemaining($loanId, $index, $schedule, $repayments, Carbon $dueDate, int $toleranceDays)
    {
        $toleranceEndDate = $this->getToleranceEndDate($dueDate, $toleranceDays);

        if ($index <= 0) {
            return [
                'total' => 0,
                'installment_remaining' => 0,
                'penalty_remaining' => 0,
                'breakdown' => [],
                'repaid_before_due' => 0,
                'due_date' => $dueDate,
                'tolerance_end_date' => $toleranceEndDate,
            ];
        }

        $totalRepaid = $this->getTotalRepaidUpToToleranceEnd($repayments, $dueDate, $toleranceDays);
        $remainingPayment = $totalRepaid;
        $installmentRemaining = 0;
        $penaltyRemaining = 0;
        $breakdown = [];

        for ($i = 0; $i < $index; $i++) {
            $expected = $schedule[$i]['montant_total'];
            $applied = min($remainingPayment, $expected);
            $monthInstallmentRemaining = max(0, round($expected - $applied, 2));
            $remainingPayment -= $applied;

            $dueDate = LoanCalculationService::normalizeYear(Carbon::parse($schedule[$i]['date']));
            $monthKey = $dueDate->format('Y-m');
            $monthPenalty = $this->getUnpaidPenaltyForMonth($loanId, $monthKey);
            $monthPenaltyRemaining = $monthPenalty ? round((float) $monthPenalty->amount, 2) : 0;

            $subtotal = round($monthInstallmentRemaining + $monthPenaltyRemaining, 2);
            $installmentRemaining += $monthInstallmentRemaining;
            $penaltyRemaining += $monthPenaltyRemaining;

            $breakdown[] = [
                'installment_number' => $i + 1,
                'label' => 'Reste M-' . ($i + 1),
                'month' => $monthKey,
                'due_date' => $dueDate,
                'installment_remaining' => $monthInstallmentRemaining,
                'penalty_remaining' => $monthPenaltyRemaining,
                'subtotal' => $subtotal,
            ];
        }

        return [
            'total' => round($installmentRemaining + $penaltyRemaining, 2),
            'installment_remaining' => round($installmentRemaining, 2),
            'penalty_remaining' => round($penaltyRemaining, 2),
            'breakdown' => $breakdown,
            'repaid_before_due' => $totalRepaid,
            'due_date' => $dueDate,
            'tolerance_end_date' => $toleranceEndDate,
        ];
    }

    /**
     * Get penalty calculation details for display (PenaltyController / views).
     */
    public function getPenaltyDetailsForDisplay(Penalty $penalty): ?array
    {
        $loan = $penalty->loanDoc;
        if (!$loan) {
            return null;
        }

        $calculationService = new LoanCalculationService();
        $interestCalculation = $calculationService->calculateMonthlyDegressiveInterest(
            $loan->requestAmount,
            $loan->interestRate,
            $loan->loanMonths,
            $loan->submitDate->format('Y-m-d')
        );

        $schedule = $interestCalculation['schedule'];
        $penaltyMonth = $penalty->penaltyMonth;
        $index = null;
        $correspondingPayment = null;

        foreach ($schedule as $idx => $payment) {
            $paymentDate = LoanCalculationService::normalizeYear(Carbon::parse($payment['date']));
            if ($paymentDate->format('Y-m') === $penaltyMonth || $this->normalizePenaltyMonth($penaltyMonth) === $paymentDate->format('Y-m')) {
                $index = $idx;
                $correspondingPayment = $payment;
                break;
            }
        }

        if ($index === null || !$correspondingPayment) {
            return null;
        }

        $dueDate = LoanCalculationService::normalizeYear(Carbon::parse($correspondingPayment['date']));
        $repayments = $loan->loanRepayments()->orderBy('repaymentDate', 'asc')->orderBy('created_at', 'asc')->get();
        $penaltyRate = config('penalties.penalty_rate', 10.0);
        $toleranceDays = config('penalties.tolerance_days', 30);

        $toleranceEndDate = $this->getToleranceEndDate($dueDate, $toleranceDays);
        $totalRepaidBeforeDue = $this->getTotalRepaidUpToToleranceEnd($repayments, $dueDate, $toleranceDays);
        $monthsOverdue = $this->calculateMonthsOverdue($dueDate, Carbon::now(), $toleranceDays);

        $calculation = $this->buildPenaltyCalculationDetails(
            $loan->loanDocId,
            $index,
            $correspondingPayment,
            $dueDate,
            $schedule,
            $repayments,
            $penaltyRate
        );

        return array_merge($calculation, [
            'installment_number' => $index + 1,
            'due_date' => $dueDate,
            'tolerance_end_date' => $toleranceEndDate,
            'expected_amount' => round($correspondingPayment['montant_total'], 2),
            'total_repaid_before_due' => round($totalRepaidBeforeDue, 2),
            'total_repaid' => round($repayments->sum('amount'), 2),
            'remaining_capital' => $calculation['current_month_capital'],
            'months_overdue' => $monthsOverdue,
            'corresponding_payment' => $correspondingPayment,
            'previous_breakdown' => $calculation['previous_remaining']['breakdown'] ?? [],
        ]);
    }

    /**
     * Normalize stored penalty month (ex: 0026-09 -> 2026-09).
     */
    private function normalizePenaltyMonth(?string $month): ?string
    {
        if (!$month || !preg_match('/^(\d{2,4})-(\d{2})$/', $month, $matches)) {
            return $month;
        }

        $year = (int) $matches[1];
        if ($year < 100) {
            $year += 2000;
        }

        return $year . '-' . $matches[2];
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
    private function generatePenaltyDescription($index, $dueDate, $penaltyAmount, $loan, $schedule, $repayments)
    {
        $toleranceDays = (int) config('penalties.tolerance_days', 30);
        $previousRemaining = $this->calculatePreviousMonthsRemaining(
            $loan->loanDocId,
            $index,
            $schedule,
            $repayments,
            $dueDate,
            $toleranceDays
        );
        $installmentNumber = $index + 1;
        $dueDateLabel = $dueDate->format('d/m/Y');
        $toleranceEndLabel = $previousRemaining['tolerance_end_date']->format('d/m/Y');
        $amountLabel = number_format($penaltyAmount, 2, '.', '') . ' USD';

        if ($previousRemaining['total'] > 0) {
            $capital = $this->getInstallmentCapitalAmount($schedule[$index]);
            $interest = $schedule[$index]['interet'];
            $formulas = $this->buildPenaltyFormulaStrings(
                $installmentNumber,
                $capital,
                $interest,
                $previousRemaining,
                config('penalties.penalty_rate', 10.0)
            );

            $lines = [
                "Pénalité M-{$installmentNumber} — échéance du {$dueDateLabel}.",
                'Remboursements pris en compte : pendant la tolérance (jusqu\'au ' . $toleranceEndLabel . ')'
                    . ' = ' . number_format($previousRemaining['repaid_before_due'], 2) . ' USD.',
                'Formule : ' . $formulas['labelled'],
            ];

            foreach ($previousRemaining['breakdown'] as $month) {
                if ($month['subtotal'] <= 0) {
                    continue;
                }
                $lines[] = '  • ' . $month['label'] . ' : '
                    . number_format($month['installment_remaining'], 2) . ' USD (échéance)'
                    . ' + ' . number_format($month['penalty_remaining'], 2) . ' USD (pénalité)'
                    . ' = ' . number_format($month['subtotal'], 2) . ' USD';
            }

            $lines[] = '  • Capital M-' . $installmentNumber . ' : ' . number_format($capital, 2) . ' USD';
            $lines[] = '  • Intérêt M-' . $installmentNumber . ' : ' . number_format($interest, 2) . ' USD';
            $lines[] = "Pénalité M-{$installmentNumber} : {$amountLabel}.";

            return implode("\n", $lines);
        }

        return "Pénalité M-1 — échéance du {$dueDateLabel}."
            . "\nFormule : (Capital M-1 + Intérêt M-1) × 10%"
            . "\nPénalité M-1 : {$amountLabel}.";
    }

    /**
     * Check if an installment is still unpaid using chronological allocation.
     */
    private function isInstallmentUnpaid($index, $schedule, $repayments): bool
    {
        $totalRepaid = 0;
        foreach ($repayments as $repayment) {
            $totalRepaid += $repayment->amount;
        }

        $remainingPayment = $totalRepaid;

        for ($i = 0; $i <= $index; $i++) {
            $expected = $schedule[$i]['montant_total'];
            $applied = min($remainingPayment, $expected);

            if ($i === $index) {
                return $applied < $expected;
            }

            $remainingPayment -= $applied;
        }

        return false;
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
        $toleranceDate = $dueDate->copy()->addDays($toleranceDays);

        if ($currentDate->lte($toleranceDate)) {
            return 0;
        }

        // Dès le lendemain de la tolérance, l'échéance est pénalisable (une pénalité par mois d'échéance).
        return max(1, $toleranceDate->diffInMonths($currentDate));
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
    public function payPenalty($penaltyId, $amount = null, $paymentDate = null)
    {
        $penalty = Penalty::findOrFail($penaltyId);

        if ($penalty->status === 'paid') {
            return ['success' => false];
        }

        $paymentAmount = round((float) ($amount ?? $penalty->amount), 2);
        $remainingDue = round((float) $penalty->amount, 2);

        if ($paymentAmount <= 0) {
            throw new \InvalidArgumentException('Le montant doit être supérieur à 0.');
        }

        if ($paymentAmount - $remainingDue > 0.01) {
            throw new \InvalidArgumentException(
                'Le montant payé (' . number_format($paymentAmount, 2) . ' USD) ne peut pas dépasser le reste dû (' . number_format($remainingDue, 2) . ' USD).'
            );
        }

        $paidAt = $paymentDate
            ? LoanCalculationService::normalizeYear(Carbon::parse($paymentDate))->setTimeFrom(now())
            : now();

        $totalPaidSoFar = round((float) ($penalty->paidAmount ?? 0) + $paymentAmount, 2);
        $newRemaining = round($remainingDue - $paymentAmount, 2);
        $isFullyPaid = $newRemaining <= 0.01;

        $loan = LoanDoc::findOrFail($penalty->loanDocIdFk);
        $cashflowService = new \App\Services\CashflowService();
        $cashflowService->recordPenaltyPayment($penalty, $loan, $paymentAmount, $paidAt);

        if ($isFullyPaid) {
            $penalty->update([
                'status' => 'paid',
                'amount' => $totalPaidSoFar,
                'paidAt' => $paidAt,
                'paidAmount' => $totalPaidSoFar,
            ]);

            Historique::create([
                'recordIdFk' => $penalty->loanDocIdFk,
                'recordStatus' => 'penalty_paid',
                'operDescription' => 'Pénalité soldée: ' . number_format($totalPaidSoFar, 2) . ' USD',
                'userIdFk' => Auth::id(),
            ]);
        } else {
            $penalty->update([
                'status' => 'notPaid',
                'amount' => $newRemaining,
                'paidAmount' => $totalPaidSoFar,
                'paidAt' => null,
            ]);

            Historique::create([
                'recordIdFk' => $penalty->loanDocIdFk,
                'recordStatus' => 'penalty_partial_paid',
                'operDescription' => 'Paiement partiel pénalité: ' . number_format($paymentAmount, 2)
                    . ' USD (reste ' . number_format($newRemaining, 2) . ' USD)',
                'userIdFk' => Auth::id(),
            ]);
        }

        return [
            'success' => true,
            'fully_paid' => $isFullyPaid,
            'payment_amount' => $paymentAmount,
            'remaining' => $isFullyPaid ? 0 : $newRemaining,
        ];
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
