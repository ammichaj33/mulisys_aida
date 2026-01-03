<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoanRepayment;
use App\Services\LoanCalculationService;
use Dompdf\Dompdf;
use Dompdf\Options;

class RepaymentReceiptController extends Controller
{
    /**
     * Generate PDF receipt for repayment
     */
    public function generateReceipt($id)
    {
        $repayment = LoanRepayment::with(['loanDoc.member', 'repaymentType', 'user'])->findOrFail($id);

        // Calculer le reste dû
        $calculationService = new LoanCalculationService();
        $interestCalculation = $calculationService->calculateMonthlyDegressiveInterest(
            $repayment->loanDoc->requestAmount,
            $repayment->loanDoc->interestRate,
            $repayment->loanDoc->loanMonths,
            $repayment->loanDoc->submitDate->format('Y-m-d')
        );

        $totalCancelledInterest = $calculationService->getCancelledInterest($repayment->loanDoc->loanDocId);
        $totalAmountDue = $repayment->loanDoc->requestAmount + $interestCalculation['total_interest'] - $totalCancelledInterest;
        $totalRepaid = $repayment->loanDoc->loanRepayments->sum('amount');
        $remainingAmount = max(0, $totalAmountDue - $totalRepaid);

        // Vérifier s'il y a des pénalités payées récemment
        $recentPenalties = \App\Models\Penalty::where('loanDocIdFk', $repayment->loanDoc->loanDocId)
            ->where('status', 'paid')
            ->where('paidAt', '>=', ($repayment->createdAt ?? now())->subMinutes(5)) // Pénalités payées dans les 5 dernières minutes
            ->get();

        $penaltyAmount = $recentPenalties->sum('amount');

        // Données pour le PDF
        $data = [
            'repayment' => $repayment,
            'company_name' => 'AIDA_MICROCREDITS',
            'receipt_title' => 'Reçu de remboursement',
            'generated_at' => now()->format('d/m/Y H:i'),
            'totalAmountDue' => $totalAmountDue,
            'totalRepaid' => $totalRepaid,
            'remainingAmount' => $remainingAmount,
            'penaltyAmount' => $penaltyAmount,
            'recentPenalties' => $recentPenalties,
        ];

        // Générer le HTML
        $html = view('pdf.repayment-receipt', $data)->render();

        // Configuration pour impression thermique (80mm)
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'Courier New');
        $options->set('dpi', 150);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A5', 'portrait'); // Format A5 (moitié de A4)
        $dompdf->render();

        $filename = 'Receipt_' . $repayment->loanRepaymentId . '_' . $repayment->loanDoc->refNumber . '.pdf';
        return $dompdf->stream($filename);
    }

    /**
     * Download PDF receipt for repayment
     */
    public function downloadReceipt($id)
    {
        $repayment = LoanRepayment::with(['loanDoc.member', 'repaymentType', 'user'])->findOrFail($id);

        // Calculer le reste dû
        $calculationService = new LoanCalculationService();
        $interestCalculation = $calculationService->calculateMonthlyDegressiveInterest(
            $repayment->loanDoc->requestAmount,
            $repayment->loanDoc->interestRate,
            $repayment->loanDoc->loanMonths,
            $repayment->loanDoc->submitDate->format('Y-m-d')
        );

        $totalCancelledInterest = $calculationService->getCancelledInterest($repayment->loanDoc->loanDocId);
        $totalAmountDue = $repayment->loanDoc->requestAmount + $interestCalculation['total_interest'] - $totalCancelledInterest;
        $totalRepaid = $repayment->loanDoc->loanRepayments->sum('amount');
        $remainingAmount = max(0, $totalAmountDue - $totalRepaid);

        // Vérifier s'il y a des pénalités payées récemment
        $recentPenalties = \App\Models\Penalty::where('loanDocIdFk', $repayment->loanDoc->loanDocId)
            ->where('status', 'paid')
            ->where('paidAt', '>=', ($repayment->createdAt ?? now())->subMinutes(5)) // Pénalités payées dans les 5 dernières minutes
            ->get();

        $penaltyAmount = $recentPenalties->sum('amount');

        // Données pour le PDF
        $data = [
            'repayment' => $repayment,
            'company_name' => 'MICROCREDITS',
            'receipt_title' => 'Reçu de remboursement',
            'generated_at' => now()->format('d/m/Y H:i'),
            'totalAmountDue' => $totalAmountDue,
            'totalRepaid' => $totalRepaid,
            'remainingAmount' => $remainingAmount,
            'penaltyAmount' => $penaltyAmount,
            'recentPenalties' => $recentPenalties,
        ];

        // Générer le HTML
        $html = view('pdf.repayment-receipt', $data)->render();

        // Configuration pour impression thermique (80mm)
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'Courier New');
        $options->set('dpi', 150);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A5', 'portrait'); // Format A5 (moitié de A4)
        $dompdf->render();

        $filename = 'Receipt_' . $repayment->loanRepaymentId . '_' . $repayment->loanDoc->refNumber . '.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }
}
