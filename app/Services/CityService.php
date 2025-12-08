<?php

namespace App\Services;

use App\Repositories\CityRepo;
use Illuminate\Support\Facades\Log;

class CityService
{
    private CityRepo $cityRepo;

    public function __construct()
    {
        $this->cityRepo = new CityRepo();
    }

    public function getAllCities()
    {
        Log::info('cityService: getAllCities');

        return $this->cityRepo->getAllCities();
    }
}
