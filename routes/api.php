<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ApartmentController;
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
    });


Route::prefix('/user')
    ->middleware('auth:sanctum')
    ->group(function()
    {
        Route::get('/profile' , [UserController::class , 'getProfile']);

        Route::get('/favourites' , [UserController::class , 'getFavouriteApartments']);
        Route::post('/favourites/{apartmentId}' , [UserController::class , 'addFavouriteApartment']);
        Route::delete('/favourites/{apartmentId}' , [UserController::class , 'removeFavouriteApartment']);

        Route::post('/rates/{apartmentId}' , [UserController::class , 'rateApartment']); // not implemented
    });
 

Route::prefix('/apartments')
    ->middleware('auth:sanctum')
    ->group(function()
    {
        Route::get('/' , [ApartmentController::class , 'getApartmentsWithFilters']);
        Route::get('/{apartmentId}' , [ApartmentController::class , 'getApartmentById']);
    });