<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CompleteRolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nettoyer les tables
        \DB::table('model_has_permissions')->delete();
        \DB::table('model_has_roles')->delete();
        \DB::table('role_has_permissions')->delete();
        \DB::table('permissions')->delete();
        \DB::table('roles')->delete();

        // Créer toutes les permissions
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
            
            // Demandes de crédit
            'view-loan-requests',
            'create-loan-requests',
            'edit-loan-requests',
            'delete-loan-requests',
            
            // Validation des crédits
            'view-credit-validation',
            'validate-credits',
            'reject-credits',
            
            // Validation finale
            'view-final-validation',
            'final-validate-credits',
            'final-reject-credits',
            
            // Remboursements
            'view-repayments',
            'create-repayments',
            'edit-repayments',
            'delete-repayments',
            'view-repayment-schedule',
            'generate-repayment-pdf',
            
            // Pénalités
            'view-penalties',
            'create-penalties',
            'edit-penalties',
            'pay-penalties',
            
            // Rapports
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
            
            // Gestion des utilisateurs
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'assign-roles',
            'manage-users', // Gestion complète des utilisateurs, rôles et permissions
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Créer les rôles et assigner les permissions
        $this->createRoles();
        
        // Créer des utilisateurs de test si nécessaire
        $this->createTestUsers();
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

        // Rôle Gérant - Toutes les permissions de visualisation + édition + rapports cashflow
        $gerant = Role::firstOrCreate(['name' => 'gerant']);
        $gerant->givePermissionTo([
            'view-dashboard',
            'view-dashboard-gerant',
            'view-members',
            'view-loan-requests',
            'edit-loan-requests',
            'view-credit-validation',
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

        // Rôle Caissière - Toutes les permissions de visualisation + gestion des remboursements + cashflow
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

        // Rôle Directeur - Toutes les permissions
        $directeur = Role::firstOrCreate(['name' => 'directeur']);
        $directeur->givePermissionTo(Permission::all());
        
        // S'assurer que le directeur a aussi les permissions dashboard spécifiques
        $directeur->givePermissionTo([
            'view-dashboard-directeur',
        ]);
        
        // Rôle Admin - Gestion des utilisateurs, rôles et permissions
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

    private function createTestUsers()
    {
        // Vérifier si des utilisateurs existent déjà
        if (User::count() > 0) {
            $this->command->info('Des utilisateurs existent déjà. Attribution des rôles...');
            
            // Assigner les rôles aux utilisateurs existants
            $users = User::all();
            foreach ($users as $user) {
                // Supprimer tous les rôles existants
                $user->syncRoles([]);
                
                // Assigner le rôle basé sur le champ 'role' existant
                switch ($user->role) {
                    case 'receptionniste':
                        $user->assignRole('receptionniste');
                        break;
                    case 'charge_credits':
                        $user->assignRole('charge_credits');
                        break;
                    case 'gerant':
                        $user->assignRole('gerant');
                        break;
                    case 'caissiere':
                        $user->assignRole('caissiere');
                        break;
                    case 'directeur':
                        $user->assignRole('directeur');
                        break;
                    default:
                        $user->assignRole('receptionniste');
                        break;
                }
            }
        } else {
            // Créer des utilisateurs de test
            $this->command->info('Création des utilisateurs de test...');
            
            $users = [
                [
                    'firstName' => 'Admin',
                    'lastName' => 'System',
                    'email' => 'admin@loans.com',
                    'password' => Hash::make('password'),
                    'role' => 'directeur',
                    'phoneNumber' => '+1234567890',
                ],
                [
                    'firstName' => 'Jean',
                    'lastName' => 'Réceptionniste',
                    'email' => 'receptionniste@loans.com',
                    'password' => Hash::make('password'),
                    'role' => 'receptionniste',
                    'phoneNumber' => '+1234567891',
                ],
                [
                    'firstName' => 'Marie',
                    'lastName' => 'Chargée Crédits',
                    'email' => 'charge.credits@loans.com',
                    'password' => Hash::make('password'),
                    'role' => 'charge_credits',
                    'phoneNumber' => '+1234567892',
                ],
                [
                    'firstName' => 'Pierre',
                    'lastName' => 'Gérant',
                    'email' => 'gerant@loans.com',
                    'password' => Hash::make('password'),
                    'role' => 'gerant',
                    'phoneNumber' => '+1234567893',
                ],
                [
                    'firstName' => 'Sophie',
                    'lastName' => 'Caissière',
                    'email' => 'caissiere@loans.com',
                    'password' => Hash::make('password'),
                    'role' => 'caissiere',
                    'phoneNumber' => '+1234567894',
                ],
            ];

            foreach ($users as $userData) {
                $user = User::create($userData);
                $user->assignRole($userData['role']);
            }
        }
    }
}
