<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LoanDoc;
use App\Models\LoanRepayment;
use App\Models\Penalty;
use App\Services\LoanCalculationService;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    protected $calculationService;

    public function __construct()
    {
        $this->middleware('auth');
        $this->calculationService = new LoanCalculationService();
    }

    /**
     * Rapport des crédits octroyés par périodes
     */
    public function creditsOctroyes(Request $request)
    {
        $startDate = $request->input('startDate', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('endDate', Carbon::now()->format('Y-m-d'));

        $loans = LoanDoc::with('member')
            ->whereBetween('submitDate', [$startDate, $endDate])
            ->orderBy('submitDate', 'desc')
            ->get()
            ->map(function($loan) {
                // Calculer les intérêts
                $interestCalculation = $this->calculationService->calculateMonthlyDegressiveInterest(
                    $loan->requestAmount,
                    $loan->interestRate,
                    $loan->loanMonths,
                    $loan->submitDate->format('Y-m-d')
                );
                
                // Calculer les intérêts annulés
                $totalCancelledInterest = $this->calculationService->getCancelledInterest($loan->loanDocId);
                
                // Intérêt total
                $loan->totalInterest = round($interestCalculation['total_interest'] - $totalCancelledInterest, 2);
                
                // Date d'échéance
                $loan->dueDate = Carbon::parse($loan->submitDate)->addMonths($loan->loanMonths)->format('d/m/Y');
                
                return $loan;
            });

        // Totaux
        $totalAmount = $loans->sum('requestAmount');
        $totalInterest = $loans->sum('totalInterest');

        if ($request->input('pdf')) {
            return $this->generateCreditsOctroyesPDF($loans, $startDate, $endDate, $totalAmount, $totalInterest);
        }

        return view('reports.credits-octroyes', compact('loans', 'startDate', 'endDate', 'totalAmount', 'totalInterest'));
    }

    /**
     * Rapport des crédits échus par périodes
     */
    public function creditsEchus(Request $request)
    {
        $startDate = $request->input('startDate', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('endDate', Carbon::now()->format('Y-m-d'));

        $loans = LoanDoc::with(['member', 'loanRepayments', 'penalties'])
            ->whereIn('status', ['validated', 'done'])
            ->get()
            ->map(function($loan) use ($startDate, $endDate) {
                // Calculer les intérêts
                $interestCalculation = $this->calculationService->calculateMonthlyDegressiveInterest(
                    $loan->requestAmount,
                    $loan->interestRate,
                    $loan->loanMonths,
                    $loan->submitDate->format('Y-m-d')
                );
                
                // Calculer les intérêts annulés
                $totalCancelledInterest = $this->calculationService->getCancelledInterest($loan->loanDocId);
                
                // Montant total dû
                $loan->totalAmountDue = round($loan->requestAmount + $interestCalculation['total_interest'] - $totalCancelledInterest, 2);
                
                // Montant remboursé
                $loan->totalRepaid = round($loan->loanRepayments->sum('amount'), 2);
                
                // Reste à rembourser
                $loan->remainingAmount = max(0, round($loan->totalAmountDue - $loan->totalRepaid, 2));
                
                // Date d'échéance
                $loan->dueDate = Carbon::parse($loan->submitDate)->addMonths($loan->loanMonths);
                
                // Vérifier si le crédit est échu (date d'échéance passée)
                if ($loan->dueDate->lt(Carbon::parse($endDate))) {
                    // Total des pénalités
                    $loan->totalPenalty = round($loan->penalties->sum('amount'), 2);
                    
                    return $loan;
                }
                
                return null;
            })
            ->filter()
            ->sortByDesc('submitDate');

        // Totaux
        $totalAmount = $loans->sum('requestAmount');
        $totalInterest = $loans->sum(function($loan) {
            return round($loan->totalAmountDue - $loan->requestAmount, 2);
        });
        $totalRepaid = $loans->sum('totalRepaid');
        $totalRemaining = $loans->sum('remainingAmount');
        $totalPenalty = $loans->sum('totalPenalty');

        if ($request->input('pdf')) {
            return $this->generateCreditsEchusPDF($loans, $startDate, $endDate, $totalAmount, $totalInterest, $totalRepaid, $totalRemaining, $totalPenalty);
        }

        return view('reports.credits-echus', compact('loans', 'startDate', 'endDate', 'totalAmount', 'totalInterest', 'totalRepaid', 'totalRemaining', 'totalPenalty'));
    }

    /**
     * Rapport global des crédits par période
     */
    public function rapportGlobal(Request $request)
    {
        $startDate = $request->input('startDate', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('endDate', Carbon::now()->format('Y-m-d'));

        $loans = LoanDoc::with(['loanRepayments', 'penalties'])
            ->whereBetween('submitDate', [$startDate, $endDate])
            ->get();

        // Calculer les totaux
        $totalCredit = 0;
        $totalInterest = 0;
        $totalRepaid = 0;
        $totalRemaining = 0;
        $totalPenalty = 0;

        foreach ($loans as $loan) {
            // Calculer les intérêts
            $interestCalculation = $this->calculationService->calculateMonthlyDegressiveInterest(
                $loan->requestAmount,
                $loan->interestRate,
                $loan->loanMonths,
                $loan->submitDate->format('Y-m-d')
            );
            
            // Calculer les intérêts annulés
            $totalCancelledInterest = $this->calculationService->getCancelledInterest($loan->loanDocId);
            
            // Montant total dû
            $totalAmountDue = round($loan->requestAmount + $interestCalculation['total_interest'] - $totalCancelledInterest, 2);
            
            // Montant remboursé
            $totalRepaidForLoan = round($loan->loanRepayments->sum('amount'), 2);
            
            // Reste à rembourser
            $remainingForLoan = max(0, round($totalAmountDue - $totalRepaidForLoan, 2));
            
            // Total des pénalités
            $penaltyForLoan = round($loan->penalties->sum('amount'), 2);
            
            $totalCredit += $loan->requestAmount;
            $totalInterest += round($interestCalculation['total_interest'] - $totalCancelledInterest, 2);
            $totalRepaid += $totalRepaidForLoan;
            $totalRemaining += $remainingForLoan;
            $totalPenalty += $penaltyForLoan;
        }

        if ($request->input('pdf')) {
            return $this->generateRapportGlobalPDF($startDate, $endDate, $totalCredit, $totalInterest, $totalRepaid, $totalRemaining, $totalPenalty);
        }

        return view('reports.rapport-global', compact('startDate', 'endDate', 'totalCredit', 'totalInterest', 'totalRepaid', 'totalRemaining', 'totalPenalty'));
    }

    /**
     * Bilan par période
     * 
     * Ce rapport calcule :
     * - Capital injecté : somme des montants des crédits octroyés dans la période
     * - Intérêt attendu : somme des intérêts calculés pour tous les crédits
     * - Intérêt collecté : partie des remboursements qui correspond aux intérêts
     * - Reste des intérêts à collecter : Intérêt attendu - Intérêt collecté
     * - Total pénalité collecter : somme de toutes les pénalités
     * - Pénalité collecter : somme des pénalités payées
     * - Reste pénalité à collecter : Total pénalité - Pénalité collectée
     * - Frais d'étude collectés : (à définir selon votre logique métier)
     * - Situation mutuelle : solde global
     */
    public function bilanParPeriode(Request $request)
    {
        $startDate = $request->input('startDate', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('endDate', Carbon::now()->format('Y-m-d'));

        // Récupérer uniquement les crédits octroyés dans la période
        $loans = LoanDoc::with(['loanRepayments', 'penalties'])
            ->whereIn('status', ['validated', 'done'])
            ->whereBetween('submitDate', [$startDate, $endDate])
            ->get();

        // Capital injecté : somme des montants des crédits octroyés dans la période
        $capitalInjecte = $loans->sum('requestAmount');

        // Calculer les intérêts attendus pour les crédits de la période
        $interetAttendu = 0;
        $interetCollecte = 0;
        $totalPenaliteCollecter = 0;
        $penaliteCollecter = 0;
        $capitalRembourse = 0;
        
        foreach ($loans as $loan) {
            // Calculer les intérêts pour ce crédit
            $interestCalculation = $this->calculationService->calculateMonthlyDegressiveInterest(
                $loan->requestAmount,
                $loan->interestRate,
                $loan->loanMonths,
                $loan->submitDate->format('Y-m-d')
            );
            
            // Calculer les intérêts annulés
            $totalCancelledInterest = $this->calculationService->getCancelledInterest($loan->loanDocId);
            
            // Intérêt attendu pour ce crédit
            $loanInterest = round($interestCalculation['total_interest'] - $totalCancelledInterest, 2);
            $interetAttendu += $loanInterest;
            
            // Calculer l'intérêt collecté pour ce crédit
            // Approche : répartir les remboursements proportionnellement entre capital et intérêts
            $totalAmountDue = $loan->requestAmount + $loanInterest;
            $totalRepaid = round($loan->loanRepayments->sum('amount'), 2);
            
            if ($totalAmountDue > 0) {
                // Proportion d'intérêts dans le total
                $interestRatio = $loanInterest / $totalAmountDue;
                // Intérêt collecté = partie des remboursements qui correspond aux intérêts
                $interetCollecte += round($totalRepaid * $interestRatio, 2);
                
                // Capital remboursé
                $capitalRatio = $loan->requestAmount / $totalAmountDue;
                $capitalRembourse += round($totalRepaid * $capitalRatio, 2);
            }
            
            // Total des pénalités (payées + non payées) pour les crédits de la période
            $totalPenaliteCollecter += round($loan->penalties->sum('amount'), 2);
            
            // Pénalités payées
            $penaliteCollecter += round($loan->penalties->where('status', 'paid')->sum('paidAmount'), 2);
        }
        
        // Reste des intérêts à collecter
        $resteInteretACollecter = max(0, round($interetAttendu - $interetCollecte, 2));
        
        // Reste pénalité à collecter
        $restePenaliteACollecter = max(0, round($totalPenaliteCollecter - $penaliteCollecter, 2));
        
        // Frais d'étude collectés (à adapter selon votre logique métier)
        // Pour l'instant, on peut utiliser un pourcentage du capital ou un montant fixe par crédit
        // Exemple : 2% du capital injecté
        $fraisEtudeCollectes = round(0, 2); // À adapter selon vos besoins
        
        // Situation mutuelle : solde pour la période
        // Capital injecté + Intérêts collectés + Pénalités collectées + Frais d'étude - Capital remboursé
        $situationMutuelle = round($capitalInjecte + $interetCollecte + $penaliteCollecter + $fraisEtudeCollectes - $capitalRembourse, 2);

        if ($request->input('pdf')) {
            return $this->generateBilanParPeriodePDF(
                $startDate, 
                $endDate, 
                $capitalInjecte, 
                $interetAttendu, 
                $interetCollecte, 
                $resteInteretACollecter,
                $totalPenaliteCollecter,
                $penaliteCollecter,
                $restePenaliteACollecter,
                $fraisEtudeCollectes,
                $situationMutuelle
            );
        }

        return view('reports.bilan-par-periode', compact(
            'startDate', 
            'endDate', 
            'capitalInjecte', 
            'interetAttendu', 
            'interetCollecte', 
            'resteInteretACollecter',
            'totalPenaliteCollecter',
            'penaliteCollecter',
            'restePenaliteACollecter',
            'fraisEtudeCollectes',
            'situationMutuelle'
        ));
    }

    /**
     * Générer le PDF pour les crédits octroyés
     */
    private function generateCreditsOctroyesPDF($loans, $startDate, $endDate, $totalAmount, $totalInterest)
    {
        $pdf = Pdf::loadView('reports.pdf.credits-octroyes', compact('loans', 'startDate', 'endDate', 'totalAmount', 'totalInterest'));
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('rapport-credits-octroyes-' . $startDate . '-' . $endDate . '.pdf');
    }

    /**
     * Générer le PDF pour les crédits échus
     */
    private function generateCreditsEchusPDF($loans, $startDate, $endDate, $totalAmount, $totalInterest, $totalRepaid, $totalRemaining, $totalPenalty)
    {
        $pdf = Pdf::loadView('reports.pdf.credits-echus', compact('loans', 'startDate', 'endDate', 'totalAmount', 'totalInterest', 'totalRepaid', 'totalRemaining', 'totalPenalty'));
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('rapport-credits-echus-' . $startDate . '-' . $endDate . '.pdf');
    }

    /**
     * Générer le PDF pour le rapport global
     */
    private function generateRapportGlobalPDF($startDate, $endDate, $totalCredit, $totalInterest, $totalRepaid, $totalRemaining, $totalPenalty)
    {
        $pdf = Pdf::loadView('reports.pdf.rapport-global', compact('startDate', 'endDate', 'totalCredit', 'totalInterest', 'totalRepaid', 'totalRemaining', 'totalPenalty'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('rapport-global-' . $startDate . '-' . $endDate . '.pdf');
    }

    /**
     * Générer le PDF pour le bilan par période
     */
    private function generateBilanParPeriodePDF($startDate, $endDate, $capitalInjecte, $interetAttendu, $interetCollecte, $resteInteretACollecter, $totalPenaliteCollecter, $penaliteCollecter, $restePenaliteACollecter, $fraisEtudeCollectes, $situationMutuelle)
    {
        $pdf = Pdf::loadView('reports.pdf.bilan-par-periode', compact(
            'startDate', 
            'endDate', 
            'capitalInjecte', 
            'interetAttendu', 
            'interetCollecte', 
            'resteInteretACollecter',
            'totalPenaliteCollecter',
            'penaliteCollecter',
            'restePenaliteACollecter',
            'fraisEtudeCollectes',
            'situationMutuelle'
        ));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('bilan-par-periode-' . $startDate . '-' . $endDate . '.pdf');
    }
}
