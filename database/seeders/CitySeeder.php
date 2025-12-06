<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('cities')->insert([
            
            // Homs id = 1
            ['name' => 'Al-Rastan', 'governorate_id' => 1],
            ['name' => 'Talkalakh', 'governorate_id' => 1],
            ['name' => 'Al-Qusayr', 'governorate_id' => 1],
            
            // Hama id = 2
            ['name' => 'Al-Salamiyah', 'governorate_id' => 2],
            ['name' => 'Al-Gab', 'governorate_id' => 2],
            ['name' => 'Mahrada', 'governorate_id' => 2],

            // Damascus id = 3
            ['name' => 'Al-Midan', 'governorate_id' => 3],
            ['name' => 'Al-Qadam', 'governorate_id' => 3],
            ['name' => 'Dimashq Al-Qadimah','governorate_id' => 3],
            ['name' => 'Jobar','governorate_id' => 3],
            ['name' => 'Al-Qanawat','governorate_id' => 3],
            ['name' => 'Al-Shaghour','governorate_id' => 3],

            // Aleppo id = 4
            ['name' => 'Jrablous', 'governorate_id' => 4],
            ['name' => 'AL-Bab', 'governorate_id' => 4],
            
            // Idlib id = 5
            ['name' => 'Areha', 'governorate_id' => 5],
            ['name' => 'Marat Al-Numan', 'governorate_id' => 5],
            ['name' => 'Jisr ash-Shughur', 'governorate_id' => 5],
            ['name' => 'Izaz', 'governorate_id' => 5],
            
            // Latakia id = 6
            ['name' => 'Jableh', 'governorate_id' => 6],
            ['name' => 'Al-Qardaha', 'governorate_id' => 6],
            ['name' => 'Al-Hafah', 'governorate_id' => 6],

            // Rif Dimashq id = 10
            ['name' => 'Doma', 'governorate_id' => 10],
            ['name' => 'Al-Tal', 'governorate_id' => 10],
            ['name' => 'Al-Nabk', 'governorate_id' => 10],
            ['name' => 'Darayya', 'governorate_id' => 10],
            ['name' => 'Al-Zabadani', 'governorate_id' => 10],
            ['name' => 'Al-Mleha', 'governorate_id' => 10],
        ]);
    }
}
