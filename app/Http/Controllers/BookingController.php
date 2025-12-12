<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest\BookingRegisterRequest;
use App\Services\BookingService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    private BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function createBooking(BookingRegisterRequest $request)
    {
        try
        {
            Log::info('BookingController createBooking called');

            $validated = $request->validated();

            $isSaved = $this->bookingService->createBooking($validated , $request->user()->id);

            return response()->json([
                'message' => 'Reequest added successfully'
            ], 201);
        }
        catch(DomainException $e)
        {
            Log::warning('BookingController createBooking failed');
            return response()->json([
                'message' => $e->getMessage()
            ], 409);
        }
    }
}
