<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'Administrateur', 'slug' => 'admin'],
            ['name' => 'Gestionnaire',   'slug' => 'gestionnaire'],
            ['name' => 'Ressources Humaines', 'slug' => 'rh'],
            ['name' => 'Utilisateur',    'slug' => 'user'],
        ];

        foreach ($roles as $role) {
            \App\Models\Role::firstOrCreate(
                ['slug' => $role['slug']],
                ['name' => $role['name']]
            );
        }
    }
}