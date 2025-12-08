<?php

namespace App\Repositories;

use App\Models\Governorate;
use App\Models\City;
use Illuminate\Support\Facades\Log;

class GovernorateRepo
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function isGovernorateExists(int $governorateId)
    {
        Log::info('governorateRepo: isGovernorateExists ID: ' . $governorateId);

        return Governorate::where('id', $governorateId)->exists();
    }

    public function getAllGovernorates()
    {
        Log::info('governorateRepo: getAllGovernorates');

        return Governorate::all();
    }

    public function getGovernorateCities(int $governorateId)
    {
        Log::info('governorateRepo: getGovernorateCities ID: ' . $governorateId);

        return City::where('governorate_id', $governorateId)->select('id', 'name')->get();
    }
}
