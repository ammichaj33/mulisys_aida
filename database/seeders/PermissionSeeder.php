<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer les permissions
        $permissions = [
            // Dashboard
            'view-dashboard',
            'view-dashboard-receptionniste',
            'view-dashboard-charge-credits',
            'view-dashboard-gerant',
            'view-dashboard-caissiere',
            'view-dashboard-directeur',
            
            // Membres
            'view-members',
            'create-members',
            'edit-members',
            'delete-members',
            
            // Demandes de crédit (Réceptionniste)
            'view-loan-requests',
            'create-loan-requests',
            'edit-loan-requests',
            'delete-loan-requests',
            
            // Validation des crédits (Chargé des crédits)
            'view-credit-validation',
            'validate-credits',
            'reject-credits',
            
            // Validation finale (Gérant)
            'view-final-validation',
            'final-validate-credits',
            'final-reject-credits',
            
            // Remboursements (Caissière)
            'view-repayments',
            'create-repayments',
            'edit-repayments',
            'delete-repayments',
            'view-repayment-schedule',
            'generate-repayment-pdf',
            
            // Pénalités (Caissière)
            'view-penalties',
            'create-penalties',
            'edit-penalties',
            'pay-penalties',
            
            // Rapports (Directeur)
            'view-loan-reports',
            'view-interest-reports',
            'view-financial-reports',
            'export-reports',
            'view-report-credits-octroyes',
            'view-report-credits-echus',
            'view-report-global',
            'view-report-bilan',
            
            // Cashflow (Trésorerie)
            'view-cashflow',
            'create-cashflow',
            'edit-cashflow',
            'delete-cashflow',
            'confirm-cashflow',
            'manage-cashflow-categories',
            'manage-cashflow-accounts',
            'view-cashflow-reports',
            
            // Gestion des utilisateurs (Directeur)
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'assign-roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Créer les rôles et assigner les permissions
        $this->createRoles();
    }

    private function createRoles()
    {
        // Rôle Réceptionniste
        $receptionniste = Role::firstOrCreate(['name' => 'receptionniste']);
        $receptionniste->givePermissionTo([
            'view-dashboard',
            'view-dashboard-receptionniste',
            'view-members',
            'create-members',
            'edit-members',
            'view-loan-requests',
            'create-loan-requests',
            'edit-loan-requests',
            'view-repayment-schedule',
            'generate-repayment-pdf',
        ]);

        // Rôle Chargé des crédits
        $chargeCredits = Role::firstOrCreate(['name' => 'charge_credits']);
        $chargeCredits->givePermissionTo([
            'view-dashboard',
            'view-dashboard-charge-credits',
            'view-members',
            'view-loan-requests',
            'view-credit-validation',
            'validate-credits',
            'reject-credits',
            'view-repayment-schedule',
            'generate-repayment-pdf',
        ]);

        // Rôle Gérant
        $gerant = Role::firstOrCreate(['name' => 'gerant']);
        $gerant->givePermissionTo([
            'view-dashboard',
            'view-dashboard-gerant',
            'view-members',
            'view-loan-requests',
            'edit-loan-requests',
            'view-final-validation',
            'final-validate-credits',
            'final-reject-credits',
            'view-repayments',
            'edit-repayments',
            'delete-repayments',
            'view-repayment-schedule',
            'generate-repayment-pdf',
            'view-penalties',
            'view-loan-reports',
            'view-interest-reports',
            'view-financial-reports',
            'view-report-credits-octroyes',
            'view-report-credits-echus',
            'view-report-global',
            'view-report-bilan',
            'view-cashflow',
            'view-cashflow-reports',
        ]);

        // Rôle Caissière
        $caissiere = Role::firstOrCreate(['name' => 'caissiere']);
        $caissiere->givePermissionTo([
            'view-dashboard',
            'view-dashboard-caissiere',
            'view-members',
            'view-loan-requests',
            'view-credit-validation',
            'view-final-validation',
            'view-repayments',
            'create-repayments',
            'edit-repayments',
            'view-repayment-schedule',
            'generate-repayment-pdf',
            'view-penalties',
            'create-penalties',
            'edit-penalties',
            'pay-penalties',
            'view-loan-reports',
            'view-interest-reports',
            'view-financial-reports',
            // Toutes les permissions cashflow
            'view-cashflow',
            'create-cashflow',
            'edit-cashflow',
            'delete-cashflow',
            'confirm-cashflow',
            'manage-cashflow-categories',
            'manage-cashflow-accounts',
            'view-cashflow-reports',
        ]);

        // Rôle Directeur
        $directeur = Role::firstOrCreate(['name' => 'directeur']);
        $directeur->givePermissionTo([
            'view-dashboard',
            'view-dashboard-directeur',
            'view-members',
            'create-members',
            'edit-members',
            'delete-members',
            'view-loan-requests',
            'view-credit-validation',
            'validate-credits',
            'reject-credits',
            'view-final-validation',
            'final-validate-credits',
            'final-reject-credits',
            'view-repayments',
            'create-repayments',
            'edit-repayments',
            'view-repayment-schedule',
            'generate-repayment-pdf',
            'view-penalties',
            'create-penalties',
            'edit-penalties',
            'pay-penalties',
            'view-loan-reports',
            'view-interest-reports',
            'view-financial-reports',
            'export-reports',
            'view-report-credits-octroyes',
            'view-report-credits-echus',
            'view-report-global',
            'view-report-bilan',
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'assign-roles',
            'manage-users',
        ]);
        
        // Rôle Admin
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo([
            'view-dashboard',
            'manage-users',
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'assign-roles',
        ]);
    }
}