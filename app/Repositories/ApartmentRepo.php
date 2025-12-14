<?php

namespace App\Repositories;

use App\Models\Apartment;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ApartmentRepo
{
    public function getApartmentById(int $id)
    {
        Log::info('ApartmentRepo getApartmentById called for id: ' . $id);

        $apartment = Apartment::with('city.governorate' , 'rates')->findOrFail($id);

        return $apartment;
    }

    public function checkIfApartmentExists(int $id)
    {
        Log::info('ApartmentRepo checkIfApartmentExists called for id: ' . $id);
        return Apartment::where('id', $id)->exists();
    }

    public function getAllApartmentsWithFilters(array $filters)
    {
        Log::info('ApartmentRepo getAllApartmentsWithFilters called');

        $apartments = Apartment::with('city.governorate' , 'rates')
        ->when($filters['governorate_id'] ?? null, fn($q, $id) =>
            $q->whereHas('city.governorate', fn($q2) => $q2->where('id', $id))
        )
        ->when($filters['city_id'] ?? null, fn($q, $id) =>
            $q->where('city_id', $id)
        )
        ->when($filters['price'] ?? null, fn($q, $price) =>
            $q->where('price', $price)
        )
        ->when($filters['area'] ?? null, fn($q, $area) =>
            $q->where('area', $area)
        )
        ->when($filters['rooms'] ?? null, fn($q, $rooms) =>
            $q->where('rooms', $rooms)
        )
        ->whereHas('user', fn($q) =>
            $q->where('status', '!=', User::STATUS_DELETED)
        )
        ->get();

        return $apartments;
    }

    public function createApartment(Apartment $apartment)
    {
        Log::info('ApartmentRepo createApartment called');

        return $apartment->save();
    }
}