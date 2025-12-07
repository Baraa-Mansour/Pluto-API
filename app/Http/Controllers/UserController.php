<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\UserResource\UserDetailResource;
use App\Http\Resources\ApartmentResource\ApartmentScrollResource;
use DomainException;

class UserController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function getProfile(Request $request)
    {
        Log::info('User requested profile', ['user_id' => $request->user()->id]);

        $user = $this->userService->getProfile($request->user()->id);

        $user_resource = new UserDetailResource($user);

        return response()->json([
            'data' => $user_resource
        ], 200);
    }

    public function getFavouriteApartments(Request $request)
    {
        Log::info('User requested favourite apartments', ['user_id' => $request->user()->id]);

        $favouriteApartments = $this->userService->getFavouriteApartments($request->user()->id);

        $favouriteApartments_resource = ApartmentScrollResource::collection($favouriteApartments);

        return response()->json([
            'data' => $favouriteApartments_resource
        ], 200);
    }

    public function addFavouriteApartment(Request $request , int $apartmentId)
    {
        try
        {
            $this->userService->addFavouriteApartment($request->user()->id, $apartmentId);

            return response()->json([
                'message' => 'Apartment added to favourites'
            ], 200);
        }
        catch(DomainException $e)
        {
            return response()->json([
                'message' => $e->getMessage()
            ], 409);
        }
    }

    public function removeFavouriteApartment(Request $request , int $apartmentId)
    {
        Log::info('Controller remove favourite apartment', ['user_id' => $request->user()->id, 'apartment_id' => $apartmentId]);

        $isRemoved = $this->userService->removeFavouriteApartment($request->user()->id, $apartmentId);

        return response()->json([
            'message' => 'Apartment removed from favourites'
        ], 200);
    }
}
