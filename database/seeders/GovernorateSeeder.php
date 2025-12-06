<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GovernorateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('governorates')->insert([
            ['name' => 'Homs'],
            ['name' => 'Hama'],
            ['name' => 'Damascus'],
            ['name' => 'Aleppo'],
            ['name' => 'Idlib'],
            ['name' => 'Latakia'],
            ['name' => 'Deir Al-Zor'],
            ['name' => 'Tartus'],
            ['name' => 'Daraa'],
            ['name' => 'Rif Dimashq'],
            ['name' => 'Al-Quneitra'],
            ['name' => 'Al-Hasakah'],
            ['name' => 'Al-Raqqah'],
            ['name' => 'Al-Suwayda'],
        ]);
    }
}
