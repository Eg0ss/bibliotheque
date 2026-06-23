<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Thèse',     'description' => 'Travail de recherche doctoral'],
            ['name' => 'Mémoire',   'description' => 'Travail de fin d\'études (Master, Licence)'],
            ['name' => 'Rapport',   'description' => 'Rapport de stage ou d\'activité'],
            ['name' => 'Article',   'description' => 'Article scientifique ou de presse'],
            ['name' => 'Ouvrage',   'description' => 'Livre ou manuel'],
        ];

        foreach ($types as $type) {
            DB::table('types')->insertOrIgnore([
                'name'        => $type['name'],
                'slug'        => Str::slug($type['name']),
                'description' => $type['description'],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }
}