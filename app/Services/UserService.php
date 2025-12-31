<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepo;
use App\Repositories\FavouriteRepo;
use App\Repositories\ApartmentRepo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use App\Models\FavouriteApartment;
use App\Models\Apartment;
use App\Repositories\BookingRepo;
use DomainException;
use Illuminate\Support\Facades\DB;

class UserService
{
    private UserRepo $userRepo;
    private FavouriteRepo $favouriteRepo;
    private ApartmentRepo $apartmentRepo;
    private BookingRepo $bookingRepo;
    private BookingService $bookingService;

    public function __construct(UserRepo $userRepo, FavouriteRepo $favouriteRepo,ApartmentRepo $apartmentRepo,
    BookingRepo $bookingRepo, BookingService $bookingService)
    {
        $this->userRepo = $userRepo;
        $this->favouriteRepo = $favouriteRepo;
        $this->apartmentRepo = $apartmentRepo;
        $this->bookingRepo = $bookingRepo;
        $this->bookingService = $bookingService;
    }

    public function getAllRegisters()
    {
        Log::info('UserService getAllRegisters called');

        $users = $this->userRepo->getRegisters();

        Log::info('UserService getAllRegisters retrieved ' . $users->count() . ' users');

        return $users;
    }

    public function getAllUsers()
    {
        Log::info('UserService getAllUsers called');

        $users = $this->userRepo->getUsers();

        Log::info('UserService getAllUsers retrieved ' . $users->count() . ' users');

        return $users;
    }

    public function getAllOwners()
    {
        Log::info('UserService getAllOwners called');

        $users = $this->userRepo->getOwners();

        Log::info('UserService getAllOwners retrieved ' . $users->count() . ' users');

        return $users;
    }

    public function generateReport() // need optimization if have time
    {
        Log::info('UserService generateReport called');

        $totalUsers = $this->userRepo->countUsers();
        $totalOwners = $this->userRepo->countOwners();
        $totalRegisters = $this->userRepo->countRegisters();

        $report = [
            'total_users' => $totalUsers,
            'total_owners' => $totalOwners,
            'total_registers' => $totalRegisters,
        ];

        Log::info('UserService generateReport generated report');

        return $report;
    }


    public function deleteRegister(int $id) : bool
    {
        Log::info('UserService deleteRegister called for register id: ' . $id);

        $user = $this->userRepo->findUserById($id);

        if($user->status !== User::STATUS_REGISTERED)
        {
            Log::warning('UserService deleteRegister register not found for id: ' . $id);
            throw (new ModelNotFoundException())->setModel(User::class , $id);
        }

        $result = $this->userRepo->deleteUserById($id); // hard delete

        if ($result) 
        {
            Log::info('UserService deleteRegister successfully deleted register id: ' . $id);
        } 
        else 
        {
            Log::warning('UserService deleteRegister failed to delete register id: ' . $id);
        }

        return $result;
    }

    public function acceptRegister(int $id) : bool
    {
        Log::info('UserService acceptRegister called for user id: ' . $id);

        $user = $this->userRepo->findUserById($id);

        if($user->status !== User::STATUS_REGISTERED)
        {
            Log::warning('UserService acceptRegister user not found for id: ' . $id);
            throw (new ModelNotFoundException())->setModel(User::class , $id);
        }

        $user->status = User::STATUS_ACTIVE;
        $result = $this->userRepo->updateUser($user);

        if ($result) {
            Log::info('UserService acceptRegister successfully updated status for user id: ' . $id);
        } else {
            Log::warning('UserService acceptRegister failed to update status for user id: ' . $id);
        }

        return $result;
    }

    public function getProfile(int $id) : User
    {
        Log::info('UserService getProfile called for id: ' . $id);

        $user = $this->userRepo->findUserById($id);

        Log::info('UserService getProfile retrieved user for id: ' . $id);

        return $user;
    }

    public function softDeleteUserWithFutureBookingsAndTokens(User $user)
    {
        Log::info('UserService deleteUserWithFutureBookingsAndTokens called');

        DB::transaction(function () use($user)
        {
            $user->tokens()->delete();

            $futureBookings = $this->bookingRepo->futureBookingsForUser($user->id);

            foreach ($futureBookings as $booking) 
            {
                $this->bookingService->cancelBooking($booking->id);
            }

            $futureOwnerBookings = $this->bookingRepo->futureBookingsForOwner($user->id);

            foreach ($futureOwnerBookings as $booking) 
            {
                $this->bookingService->declineBooking($booking->id);
            }

            $this->userRepo->softDeleteUser($user->id);
        });

        return true;
    } 


    public function getFavouriteApartments(int $id)
    {
        Log::info('UserService getFavouriteApartments called for id: ' . $id);

        $favouriteApartments = $this->favouriteRepo->getFavouriteApartments($id);

        Log::info('UserService getFavouriteApartments retrieved ' . $favouriteApartments->count() . ' favourite apartments');

        return $favouriteApartments;
    }

    public function addFavouriteApartment(int $userId, int $apartmentId)
    {
        Log::info('UserService addFavouriteApartment called for user id: ' . $userId . ' and apartment id: ' . $apartmentId);

        $isApartmentExists = $this->apartmentRepo->checkIfApartmentExists($apartmentId);

        if(!$isApartmentExists)
        {
            Log::warning('UserService addFavouriteApartment apartment not found for id: ' . $apartmentId);
            throw (new ModelNotFoundException())->setModel(Apartment::class , $apartmentId);
        }

        $isFavourite = $this->favouriteRepo->isFavourite($userId, $apartmentId);

        if($isFavourite)
        {
            Log::warning('UserService addFavouriteApartment apartment already in favourites for user id: ' . $userId . ' and apartment id: ' . $apartmentId);
            throw new DomainException('Apartment already in favourites');
        }
        
        $result = $this->favouriteRepo->addFavourite($userId, $apartmentId); 

        return $result;
    }

    public function removeFavouriteApartment(int $userId, int $apartmentId)
    {
        Log::info('UserService removeFavouriteApartment called for user id: ' . $userId . ' and apartment id: ' . $apartmentId);

        $result = $this->favouriteRepo->removeFavourite($userId, $apartmentId);

        if (!$result) 
        {
            Log::warning('UserService removeFavouriteApartment failed to remove favourite apartment for user id: ' . $userId . ' and apartment id: ' . $apartmentId);
            throw (new ModelNotFoundException())->setModel(FavouriteApartment::class , $userId , $apartmentId);
        }

        return $result;
    }
}
