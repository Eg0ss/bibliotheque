<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'user' => [
                'role_name' => 'Utilisateur',
                'users' => [
                    ['name' => 'Utilisateur 1', 'email' => 'user1@example.com'],
                    ['name' => 'Utilisateur 2', 'email' => 'user2@example.com'],
                    ['name' => 'Utilisateur 3', 'email' => 'user3@example.com'],
                ],
            ],

            'gestionnaire' => [
                'role_name' => 'Gestionnaire',
                'users' => [
                    ['name' => 'Gestionnaire 1', 'email' => 'gestionnaire1@example.com'],
                    ['name' => 'Gestionnaire 2', 'email' => 'gestionnaire2@example.com'],
                    ['name' => 'Gestionnaire 3', 'email' => 'gestionnaire3@example.com'],
                ],
            ],

            'rh' => [
                'role_name' => 'Ressources Humaines',
                'users' => [
                    ['name' => 'RH 1', 'email' => 'rh1@example.com'],
                    ['name' => 'RH 2', 'email' => 'rh2@example.com'],
                    ['name' => 'RH 3', 'email' => 'rh3@example.com'],
                ],
            ],

            'admin' => [
                'role_name' => 'Administrateur',
                'users' => [
                    ['name' => 'Admin 1', 'email' => 'admin1@example.com'],
                    ['name' => 'Admin 2', 'email' => 'admin2@example.com'],
                    ['name' => 'Admin 3', 'email' => 'admin3@example.com'],
                ],
            ],
        ];

        foreach ($roles as $slug => $data) {

            $role = Role::where('slug', $slug)->first();

            if (! $role) {
                $this->command->warn("Le rôle {$slug} n'existe pas.");
                continue;
            }

            foreach ($data['users'] as $userData) {

                User::updateOrCreate(
                    ['email' => $userData['email']],
                    [
                        'name'       => $userData['name'],
                        'password'   => Hash::make('password'),
                        'role_id'    => $role->id,
                        'is_active'  => true,
                    ]
                );
            }
        }
    }
}