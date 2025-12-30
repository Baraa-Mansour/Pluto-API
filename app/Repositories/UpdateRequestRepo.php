<?php

namespace App\Repositories;
use Illuminate\Support\Facades\Log;
use App\Models\UpdateRequest;

class UpdateRequestRepo
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getUpdateRequestForApartmentInDate(int $apartmentId, string $fromDate, string $toDate)
    {
        Log::info('UpdateRequestRepo getUpdateRequestForApartmentInDate called');

        $updateRequests = UpdateRequest::whereHas('booking', function ($query) use ($apartmentId){
                                            $query->where('apartment_id', $apartmentId);
                                        })
                                        ->where('new_from_date', '<=', $toDate)
                                        ->where('new_to_date', '>=', $fromDate);

        
        return $updateRequests;
    }
}
