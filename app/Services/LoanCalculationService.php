<?php

namespace App\Services;

use App\Models\LoanDoc;
use App\Models\InterestCancellation;
use Carbon\Carbon;

class LoanCalculationService
{
    /**
     * Calculer les intérêts mensuels dégressifs avec capital restant
     */
    public function calculateMonthlyDegressiveInterest($montant, $taux, $duree, $dateDebut = null)
    {
        if ($dateDebut === null) {
            $dateDebut = now()->format('Y-m-d');
        }
        
        $capRestant = $montant;
        $rembFixe = round($montant / $duree, 2);
        $sommInteret = 0;
        $somDecressif = 0;
        
        // Utiliser le jour de la date de soumission pour chaque échéance
        $dateBase = Carbon::parse($dateDebut);
        $jourSoumission = $dateBase->day;
        $annee = $dateBase->year;
        $mois = $dateBase->month;
        
        $schedule = [];
        
        for ($i = 0; $i < $duree; $i++) {
            $rembFixe = round($montant / $duree, 2);
            
            // Intérêts sur le capital restant (dégressif)
            $interet = round(($capRestant * $taux) / 100, 2);
            $sommInteret = $sommInteret + round(($capRestant * $taux) / 100, 2);
            
            $monantDecressif = $interet + $rembFixe;
            $somDecressif = $somDecressif + $monantDecressif;
            
            // Incrémenter le mois (succession simple)
            $mois++;
            if ($mois > 12) {
                $mois = 1;
                $annee++;
            }
            
            // Créer la date avec le même jour que la date de soumission
            // Vérifier que le jour existe dans le mois (ex: 31 janvier -> 31, mais 31 avril -> 30)
            $dateEcheance = Carbon::create($annee, $mois, 1);
            $dernierJourDuMois = $dateEcheance->lastOfMonth()->day;
            
            // Ajuster le jour si nécessaire (ex: 31 n'existe pas en avril, juin, septembre, novembre, février)
            $jour = min($jourSoumission, $dernierJourDuMois);
            
            // Cas spécial pour février : si le jour de soumission est 29, 30 ou 31, utiliser le dernier jour de février
            if ($mois == 2 && $jourSoumission >= 29) {
                // Utiliser le dernier jour de février (gère les années bissextiles : 28 ou 29)
                $dateEcheance = $dateEcheance->lastOfMonth();
            } else {
                //  Créer la date avec le jour (ajusté si nécessaire)
                $dateEcheance = Carbon::create($annee, $mois, $jour);
            }
            
            $dateEcheanceStr = $dateEcheance->format('Y-m-d');
            
            $schedule[] = [
                'id' => $i,
                'mois' => $dateEcheanceStr,
                'capital' => round($capRestant, 2),
                'montantDecressif' => round($monantDecressif, 2),
                'interet' => round($interet, 2),
                'rembfixe' => round($rembFixe, 2),
                'date' => $dateEcheanceStr,
                'capital_restant' => round($capRestant, 2),
                'remboursement_fixe' => round($rembFixe, 2),
                'montant_total' => round($monantDecressif, 2),
                'somme_interets' => round($sommInteret, 2),
                'somme_totale' => round($somDecressif, 2)
            ];
            
            // Diminuer le capital restant APRÈS l'affichage
            $capRestant = $capRestant - $rembFixe;
        }
        
        return [
            'schedule' => $schedule,
            'total_interest' => round($sommInteret, 2),
            'total_amount' => round($somDecressif, 2)
        ];
    }
    
    /**
     * Calculer les intérêts annulés pour un crédit
     */
    public function getCancelledInterest($loanId)
    {
        return InterestCancellation::where('loanDocIdFk', $loanId)
            ->sum('cancelledInterestAmount');
    }
    
    /**
     * Calculer les détails d'un remboursement anticipé
     * 
     * @param LoanDoc $loan Le crédit concerné
     * @param string $repaymentDate La date de remboursement anticipé (format Y-m-d)
     * @return array Contient: capitalRestant, interetsDus, interetsACanceler, montantTotal, monthsToCancel
     */
    public function calculateEarlyRepaymentInterest($loan, $repaymentDate)
    {
        // Calculer l'échéancier complet
        $interestCalculation = $this->calculateMonthlyDegressiveInterest(
            $loan->requestAmount,
            $loan->interestRate,
            $loan->loanMonths,
            $loan->submitDate->format('Y-m-d')
        );
        
        $schedule = $interestCalculation['schedule'];
        
        // Calculer le capital déjà remboursé
        $totalRepaid = $loan->loanRepayments->sum('amount');
        
        // Calculer le capital restant (approximation : montant initial - remboursements)
        // Note: Cette approche est simplifiée. En réalité, il faudrait calculer
        // combien de capital a été remboursé vs intérêts dans chaque paiement
        $capitalRepaid = 0;
        $capitalRestant = $loan->requestAmount;
        
        // Approche simplifiée : si le total remboursé est inférieur au capital, 
        // on considère que tout est du capital
        if ($totalRepaid <= $loan->requestAmount) {
            $capitalRepaid = $totalRepaid;
            $capitalRestant = $loan->requestAmount - $capitalRepaid;
        } else {
            // Si plus que le capital a été remboursé, le capital est entièrement remboursé
            $capitalRepaid = $loan->requestAmount;
            $capitalRestant = 0;
        }
        
        // Date de remboursement
        $repaymentDateCarbon = Carbon::parse($repaymentDate);
        $loanStartDate = Carbon::parse($loan->submitDate);
        
        // Calculer les mois écoulés depuis le début du crédit
        $monthsElapsed = $loanStartDate->diffInMonths($repaymentDateCarbon);
        
        // Intérêts dus jusqu'à la date de remboursement
        $interetsDus = 0;
        $interetsACanceler = 0;
        $monthsToCancel = [];
        
        foreach ($schedule as $index => $installment) {
            $installmentDate = Carbon::parse($installment['date']);
            
            // Si l'échéance est avant ou égale à la date de remboursement, les intérêts sont dus
            if ($installmentDate->lte($repaymentDateCarbon)) {
                $interetsDus += $installment['interet'];
            } else {
                // Si l'échéance est après la date de remboursement, annuler les intérêts
                $interetsACanceler += $installment['interet'];
                $monthsToCancel[] = [
                    'month' => $installmentDate->format('Y-m'),
                    'date' => $installmentDate->format('Y-m-d'),
                    'interest' => round($installment['interet'], 2),
                    'installment_index' => $index
                ];
            }
        }
        
        // Intérêts déjà annulés (s'il y en a)
        $totalCancelledInterest = $this->getCancelledInterest($loan->loanDocId);
        
        // Montant total à payer = Capital restant + Intérêts dus - Intérêts déjà annulés
        $montantTotal = $capitalRestant + $interetsDus - $totalCancelledInterest;
        
        return [
            'capitalRestant' => round($capitalRestant, 2),
            'capitalRepaid' => round($capitalRepaid, 2),
            'interetsDus' => round($interetsDus, 2),
            'interetsACanceler' => round($interetsACanceler, 2),
            'montantTotal' => round($montantTotal, 2),
            'monthsToCancel' => $monthsToCancel,
            'monthsElapsed' => $monthsElapsed,
            'totalMonths' => $loan->loanMonths,
            'isEarlyRepayment' => $monthsElapsed < $loan->loanMonths && $capitalRestant > 0
        ];
    }
    
    /**
     * Formater l'argent
     */
    public function formatMoney($amount)
    {
        return number_format($amount, 2, ',', ' ') . ' USD';
    }
}



