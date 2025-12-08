<?php

namespace App\Repositories;

use App\Models\City;
use Illuminate\Support\Facades\Log;

class CityRepo
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getAllCities()
    {
        Log::info('cityRepo: getAllCities');

        return City::select('id', 'name')->get();
    }
}
