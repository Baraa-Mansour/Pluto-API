<?php

namespace App\Repositories;

use App\Models\FavouriteApartment;
use Illuminate\Support\Facades\Log;


class FavouriteRepo
{

    public function getFavouriteApartments(int $id)
    {
        Log::info('FavouriteRepo getFavouriteApartments called for id: ' . $id);

        $favouriteApartments = FavouriteApartment::where('user_id', $id)
                                ->wherehas('apartment', function($query) {
                                    $query->where('is_deleted', false);
                                })
                                ->with('apartment.city.governorate', 'apartment.rates')
                                ->get()
                                ->pluck('apartment');

        Log::info('FavouriteRepo getFavouriteApartments retrieved ' . $favouriteApartments->count() . ' favourite apartments');

        return $favouriteApartments;
    }

    public function addFavourite(int $userId, int $apartmentId) : bool
    {
        Log::info('Adding favourite for user: ' . $userId . ' and apartment: ' . $apartmentId);

        $isAdded = FavouriteApartment::create([
            'user_id' => $userId,
            'apartment_id' => $apartmentId,
        ]);

        return $isAdded;
    }

    public function removeFavourite(int $userId, int $apartmentId) : bool
    {
        Log::info('Removing favourite for user: ' . $userId . ' and apartment: ' . $apartmentId);

        $isRemoved = FavouriteApartment::where('user_id', $userId)->where('apartment_id', $apartmentId)->delete();

        return $isRemoved;
    }
}
