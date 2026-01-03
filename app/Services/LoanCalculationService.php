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
     * Formater l'argent
     */
    public function formatMoney($amount)
    {
        return number_format($amount, 2, ',', ' ') . ' USD';
    }
}



