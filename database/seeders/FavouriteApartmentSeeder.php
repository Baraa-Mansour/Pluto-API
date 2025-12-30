<?php

namespace Database\Seeders;

use App\Models\FavouriteApartment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FavouriteApartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FavouriteApartment::factory()->count(20)->create();
    }
}
