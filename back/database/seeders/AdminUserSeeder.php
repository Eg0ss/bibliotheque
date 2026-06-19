<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer le rôle admin créé par RoleSeeder
        $adminRole = Role::where('slug', 'admin')->first();

        // Créer le compte admin si inexistant
        User::firstOrCreate(
            ['email' => 'admin@bibliotheque.bj'],
            [
                'name'      => 'Administrateur',
                'password'  => bcrypt('password123'), // à changer en production !
                'role_id'   => $adminRole?->id,
                'is_active' => true,
            ]
        );
    }
}