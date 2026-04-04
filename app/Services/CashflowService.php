<?php

namespace App\Services;

use App\Models\CashflowTransaction;
use App\Models\CashflowCategory;
use App\Models\CashflowAccount;
use Illuminate\Support\Facades\Log;

class CashflowService
{
    /**
     * Enregistrer automatiquement une transaction cashflow
     */
    public function recordTransaction(array $data)
    {
        try {
            // Vérifier que la catégorie existe
            $category = CashflowCategory::where('categoryId', $data['categoryIdFk'])->first();
            if (!$category) {
                Log::warning('Catégorie cashflow introuvable', ['categoryId' => $data['categoryIdFk']]);
                return null;
            }

            // Créer la transaction
            $transaction = CashflowTransaction::create([
                'transactionDate' => $data['transactionDate'] ?? now()->toDateString(),
                'transactionType' => $data['transactionType'],
                'categoryIdFk' => $data['categoryIdFk'],
                'accountIdFk' => $data['accountIdFk'] ?? null,
                'amount' => $data['amount'],
                'description' => $data['description'] ?? '',
                'paymentMethod' => $data['paymentMethod'] ?? 'cash',
                'referenceNumber' => $data['referenceNumber'] ?? null,
                'loanDocIdFk' => $data['loanDocIdFk'] ?? null,
                'memberIdFk' => $data['memberIdFk'] ?? null,
                'userIdFk' => $data['userIdFk'] ?? auth()->id(),
                'status' => 'confirmed',
                'confirmedAt' => now(),
                'confirmedBy' => $data['userIdFk'] ?? auth()->id(),
            ]);

            // Mettre à jour le solde du compte si fourni
            if ($transaction->accountIdFk) {
                $account = CashflowAccount::find($transaction->accountIdFk);
                if ($account) {
                    $account->updateBalance();
                }
            }

            return $transaction;
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement automatique cashflow', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            return null;
        }
    }

    /**
     * Enregistrer un remboursement comme entrée
     */
    public function recordRepayment($repayment, $loanDoc)
    {
        // Charger le type de remboursement si pas déjà chargé
        if (!$repayment->relationLoaded('repaymentType')) {
            $repayment->load('repaymentType');
        }

        // Récupérer le nom du type de remboursement
        $categoryName = 'Remboursements de crédits'; // Valeur par défaut
        $categoryDescription = "Remboursements automatiques des crédits";
        
        if ($repayment->repaymentType) {
            // Utiliser repaymentName si disponible, sinon repaymentType
            $repaymentTypeName = $repayment->repaymentType->repaymentName 
                ?? $repayment->repaymentType->repaymentType 
                ?? null;
            
            if ($repaymentTypeName) {
                $categoryName = $repaymentTypeName;
                $categoryDescription = $repayment->repaymentType->description 
                    ?? "Remboursements de type: {$categoryName}";
            }
        }

        // Trouver ou créer la catégorie avec le nom du type de remboursement
        $category = CashflowCategory::firstOrCreate(
            ['categoryName' => $categoryName, 'categoryType' => 'income'],
            ['description' => $categoryDescription, 'isActive' => true]
        );

        // S'assurer que la catégorie est active et mettre à jour la description si nécessaire
        if (!$category->isActive) {
            $category->isActive = true;
        }
        if ($category->description !== $categoryDescription) {
            $category->description = $categoryDescription;
        }
        $category->save();

        return $this->recordTransaction([
            'transactionType' => 'income',
            'categoryIdFk' => $category->categoryId,
            'amount' => $repayment->amount,
            'description' => "Remboursement pour le crédit {$loanDoc->refNumber}",
            'paymentMethod' => 'cash', // Par défaut, peut être modifié selon le type de remboursement
            'loanDocIdFk' => $loanDoc->loanDocId,
            'memberIdFk' => $loanDoc->memberIdFk,
            'transactionDate' => $repayment->repaymentDate->format('Y-m-d'),
            'userIdFk' => $repayment->userIdFk,
        ]);
    }

    /**
     * Enregistrer un remboursement anticipé comme entrée (catégorie dédiée)
     */
    public function recordEarlyRepayment($repayment, $loanDoc)
    {
        // Charger le type de remboursement si pas déjà chargé
        if (!$repayment->relationLoaded('repaymentType')) {
            $repayment->load('repaymentType');
        }

        // Récupérer le nom du type de remboursement
        $categoryName = 'Remboursements anticipés'; // Valeur par défaut
        $categoryDescription = "Remboursements anticipés des crédits";
        
        if ($repayment->repaymentType) {
            // Utiliser repaymentName si disponible, sinon repaymentType
            $repaymentTypeName = $repayment->repaymentType->repaymentName 
                ?? $repayment->repaymentType->repaymentType 
                ?? null;
            
            if ($repaymentTypeName) {
                $categoryName = $repaymentTypeName;
                $categoryDescription = $repayment->repaymentType->description 
                    ?? "Remboursements anticipés de type: {$categoryName}";
            }
        }

        // Trouver ou créer la catégorie avec le nom du type de remboursement
        $category = CashflowCategory::firstOrCreate(
            ['categoryName' => $categoryName, 'categoryType' => 'income'],
            ['description' => $categoryDescription, 'isActive' => true]
        );

        // S'assurer que la catégorie est active et mettre à jour la description si nécessaire
        if (!$category->isActive) {
            $category->isActive = true;
        }
        if ($category->description !== $categoryDescription) {
            $category->description = $categoryDescription;
        }
        $category->save();

        return $this->recordTransaction([
            'transactionType' => 'income',
            'categoryIdFk' => $category->categoryId,
            'amount' => $repayment->amount,
            'description' => "Remboursement anticipé pour le crédit {$loanDoc->refNumber}",
            'paymentMethod' => 'cash',
            'loanDocIdFk' => $loanDoc->loanDocId,
            'memberIdFk' => $loanDoc->memberIdFk,
            'transactionDate' => $repayment->repaymentDate->format('Y-m-d'),
            'userIdFk' => $repayment->userIdFk,
        ]);
    }

    /**
     * Enregistrer un octroi de crédit comme sortie
     */
    public function recordLoanGrant($loanDoc)
    {
        // Trouver ou créer la catégorie "Octroi de crédits"
        $category = CashflowCategory::firstOrCreate(
            ['categoryName' => 'Octroi de crédits', 'categoryType' => 'expense'],
            ['description' => 'Octroi automatique des crédits validés', 'isActive' => true]
        );

        // S'assurer que la catégorie est active
        if (!$category->isActive) {
            $category->isActive = true;
            $category->save();
        }

        return $this->recordTransaction([
            'transactionType' => 'expense',
            'categoryIdFk' => $category->categoryId,
            'amount' => $loanDoc->requestAmount,
            'description' => "Octroi du crédit {$loanDoc->refNumber}",
            'paymentMethod' => 'cash',
            'loanDocIdFk' => $loanDoc->loanDocId,
            'memberIdFk' => $loanDoc->memberIdFk,
            'transactionDate' => $loanDoc->submitDate->format('Y-m-d'),
        ]);
    }

    /**
     * Enregistrer une pénalité payée comme entrée
     */
    public function recordPenaltyPayment($penalty, $loanDoc)
    {
        // Trouver ou créer la catégorie "Pénalités collectées"
        $category = CashflowCategory::firstOrCreate(
            ['categoryName' => 'Pénalités collectées', 'categoryType' => 'income'],
            ['description' => 'Pénalités payées par les membres', 'isActive' => true]
        );

        // S'assurer que la catégorie est active
        if (!$category->isActive) {
            $category->isActive = true;
            $category->save();
        }

        // Utiliser la date de paiement de la pénalité si disponible, sinon la date du jour
        $transactionDate = $penalty->paidAt
            ? $penalty->paidAt->format('Y-m-d')
            : now()->toDateString();

        return $this->recordTransaction([
            'transactionType' => 'income',
            'categoryIdFk' => $category->categoryId,
            'amount' => $penalty->paidAmount ?? $penalty->amount,
            'description' => "Pénalité payée pour le crédit {$loanDoc->refNumber}",
            'paymentMethod' => 'cash',
            'loanDocIdFk' => $loanDoc->loanDocId,
            'memberIdFk' => $loanDoc->memberIdFk,
            'transactionDate' => $transactionDate,
        ]);
    }

    /**
     * Annuler une transaction liée à un remboursement
     */
    public function cancelRepaymentTransaction($repaymentId, $loanDocId)
    {
        // Trouver le remboursement pour obtenir sa date et montant
        $repayment = \App\Models\LoanRepayment::find($repaymentId);
        if (!$repayment) {
            return;
        }

        // Trouver la transaction correspondante (la plus récente correspondant aux critères)
        $transaction = CashflowTransaction::where('loanDocIdFk', $loanDocId)
            ->where('transactionType', 'income')
            ->where('description', 'like', "%Remboursement%")
            ->where('transactionDate', $repayment->repaymentDate->format('Y-m-d'))
            ->where('amount', $repayment->amount)
            ->where('status', 'confirmed')
            ->orderBy('createdAt', 'desc')
            ->first();

        if ($transaction) {
            $accountId = $transaction->accountIdFk;
            $transaction->update(['status' => 'cancelled']);
            
            // Mettre à jour le solde du compte
            if ($accountId) {
                $account = CashflowAccount::find($accountId);
                if ($account) {
                    $account->updateBalance();
                }
            }
        }
    }
}

