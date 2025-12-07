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

        $favourite = new FavouriteApartment();
        $favourite->user_id = $userId;
        $favourite->apartment_id = $apartmentId;
        
        $isAdded = $favourite->save();

        return $isAdded;
    }

    public function removeFavourite(int $userId, int $apartmentId) : bool
    {
        Log::info('Removing favourite for user: ' . $userId . ' and apartment: ' . $apartmentId);

        $isRemoved = FavouriteApartment::where('user_id', $userId)->where('apartment_id', $apartmentId)->delete();

        return $isRemoved;
    }

    public function isFavourite(int $userId, int $apartmentId) : bool
    {
        Log::info('FavouriteRepo isFavourite called for user: ' . $userId . ' and apartment: ' . $apartmentId);

        $isFavourite = FavouriteApartment::where('user_id', $userId)->where('apartment_id', $apartmentId)->exists();

        return $isFavourite;
    }
}
