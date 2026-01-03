<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Assigner les rôles aux utilisateurs existants
        $users = User::all();
        
        foreach ($users as $user) {
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
                    // Rôle par défaut si non reconnu
                    $user->assignRole('receptionniste');
                    break;
            }
        }
    }
}