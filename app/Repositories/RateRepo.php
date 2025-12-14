<?php

namespace App\Repositories;

use App\Models\Rate;
use Illuminate\Support\Facades\Log;

class RateRepo
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function isRatedByUser(int $userId, int $apartmentId)
    {
        Log::info('RateRepo isRatedByUser called');

        $isRated = Rate::where('user_id', $userId)
                        ->where('apartment_id', $apartmentId)
                        ->exists();
        
        return $isRated;
    }

    public function saveRate(Rate $rate)
    {
        Log::info('RateRepo saveRate called');

        return $rate->save();
    }
}
