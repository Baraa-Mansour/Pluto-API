<?php

namespace App\Http\Controllers;

use App\Services\CityService;
use Illuminate\Support\Facades\Log;

class CityController extends Controller
{
    private CityService $cityService;

    public function __construct(CityService $cityService)
    {
        $this->cityService = $cityService;
    }

    public function getAllCities()
    {
        Log::info('cityController: getAllCities');

        return response()->json([
            'data' => $this->cityService->getAllCities(),
        ], 200);
    }
}
