<?php

namespace App\Repositories;

use App\Models\Apartment;
use Illuminate\Support\Facades\Log;

class ApartmentRepo
{
    public function getApartmentById(int $id)
    {
        Log::info('ApartmentRepo getApartmentById called for id: ' . $id);

        $apartment = Apartment::findOrFail($id);

        return $apartment;
    }
}