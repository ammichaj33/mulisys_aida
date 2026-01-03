<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CashflowCategory;

class CashflowCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            // Entrées (Income)
            [
                'categoryName' => 'Remboursements de crédits',
                'categoryType' => 'income',
                'description' => 'Remboursements automatiques des crédits',
            ],
            [
                'categoryName' => 'Intérêts collectés',
                'categoryType' => 'income',
                'description' => 'Intérêts collectés sur les crédits',
            ],
            [
                'categoryName' => 'Pénalités collectées',
                'categoryType' => 'income',
                'description' => 'Pénalités payées par les membres',
            ],
            [
                'categoryName' => 'Frais d\'étude',
                'categoryType' => 'income',
                'description' => 'Frais d\'étude collectés',
            ],
            [
                'categoryName' => 'Autres revenus',
                'categoryType' => 'income',
                'description' => 'Autres revenus divers',
            ],
            
            // Sorties (Expense)
            [
                'categoryName' => 'Octroi de crédits',
                'categoryType' => 'expense',
                'description' => 'Octroi automatique des crédits validés',
            ],
            [
                'categoryName' => 'Frais opérationnels',
                'categoryType' => 'expense',
                'description' => 'Frais de fonctionnement',
            ],
            [
                'categoryName' => 'Salaires',
                'categoryType' => 'expense',
                'description' => 'Salaires du personnel',
            ],
            [
                'categoryName' => 'Loyer/Bureaux',
                'categoryType' => 'expense',
                'description' => 'Frais de location des bureaux',
            ],
            [
                'categoryName' => 'Services publics',
                'categoryType' => 'expense',
                'description' => 'Électricité, eau, internet',
            ],
            [
                'categoryName' => 'Marketing/Publicité',
                'categoryType' => 'expense',
                'description' => 'Frais de marketing et publicité',
            ],
            [
                'categoryName' => 'Fournitures de bureau',
                'categoryType' => 'expense',
                'description' => 'Fournitures et matériel de bureau',
            ],
            [
                'categoryName' => 'Maintenance',
                'categoryType' => 'expense',
                'description' => 'Frais de maintenance',
            ],
            [
                'categoryName' => 'Autres dépenses',
                'categoryType' => 'expense',
                'description' => 'Autres dépenses diverses',
            ],
        ];

        foreach ($categories as $category) {
            CashflowCategory::firstOrCreate(
                ['categoryName' => $category['categoryName'], 'categoryType' => $category['categoryType']],
                $category
            );
        }
    }
}

