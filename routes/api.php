<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\GovernorateController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\UpdateRequestController;
use Illuminate\Support\Facades\Route;


Route::prefix('/auth')
    ->group(function () 
    {
        Route::post('/register' , [AuthController::class , 'register']);
        Route::post('/login' , [AuthController::class , 'login']);
        Route::post('/logout' , [AuthController::class , 'logout'])->middleware('auth:sanctum');
    });


Route::prefix('/admin')
    ->middleware(['auth:sanctum' , 'adminCheck'])
    ->group(function () 
    {
        Route::get('/registers' , [AdminController::class , 'getAllRegisters']);
        Route::get('/owners' , [AdminController::class , 'getAllOwners']);
        Route::get('/users' , [AdminController::class , 'getAllUsers']);

        Route::get('/report' , [AdminController::class , 'getReport']);

        Route::delete('registers/{id}' , [AdminController::class , 'deleteRegister']);
        Route::patch('registers/{id}' , [AdminController::class , 'acceptRegister']); 

        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
    });


Route::prefix('/user')
    ->middleware('auth:sanctum')
    ->group(function()
    {
        Route::get('/profile' , [UserController::class , 'getProfile']);

        Route::get('/favourites' , [UserController::class , 'getFavouriteApartments']);
        Route::post('/favourites/{apartmentId}' , [UserController::class , 'addFavouriteApartment']);
        Route::delete('/favourites/{apartmentId}' , [UserController::class , 'removeFavouriteApartment']);

    });
 

Route::prefix('/apartments')
    ->middleware('auth:sanctum')
    ->group(function()
    {
        Route::get('/' , [ApartmentController::class , 'getApartmentsWithFilters']);
        Route::get('/{apartmentId}' , [ApartmentController::class , 'getApartmentById']);

        Route::post('/' , [ApartmentController::class , 'createApartment']);

        Route::post('/{apartmentId}/ratings' , [ApartmentController::class , 'rateApartment']);
    });


Route::prefix('/governorates')
    ->middleware('auth:sanctum')
    ->group(function()
    {
        Route::get('/' , [GovernorateController::class , 'getAllGovernorates']);
        Route::get('/{governorateId}/cities' , [GovernorateController::class , 'getGovernorateCities']);
    });


Route::prefix('/bookings')
    ->middleware('auth:sanctum')
    ->group(function()
    {
        Route::get('/' , [BookingController::class, 'getAllUserBookings']);
        Route::post('/' , [BookingController::class, 'createBooking']);

        Route::get('/registrations', [BookingController::class, 'getOwnerRegistrations']);
        Route::patch('/{bookingId}/accept', [BookingController::class, 'acceptBooking'])->middleware('apartmentOwnerCheck');
        Route::patch('/{bookingId}/decline', [BookingController::class, 'declineBooking'])->middleware('apartmentOwnerCheck');

        Route::patch('/{bookingId}/cancel' , [BookingController::class, 'cancelBooking'])->middleware('bookingOwnerCheck'); 
        Route::put('/{bookingId}/update', [UpdateRequestController::class, 'updateBookingRequest'])->middleware('bookingOwnerCheck');
    });


Route::get('/cities' , [CityController::class , 'getAllCities'])->middleware('auth:sanctum');
Route::get('/paymentMethods' , [PaymentMethodController::class , 'getAllPaymentMethods'])->middleware('auth:sanctum');
