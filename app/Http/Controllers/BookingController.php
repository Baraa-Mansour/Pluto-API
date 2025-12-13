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

    public function acceptBooking(int $bookingId)
    {
        try
        {
            Log::info('BookingController acceptBooking called');

            $isAccepted = $this->bookingService->acceptBooking($bookingId);

            return response()->json([
                'message' => 'Accepted successfully'
            ], 200);
        }
        catch(DomainException $e)
        {
            Log::error('BookingController acceptBooking failed. Error: ' . $e->getMessage());

            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function declineBooking(int $bookingId)
    {
        try
        {
            Log::info('BookingController declineBooking called');

            $isDeclined = $this->bookingService->declineBooking($bookingId);

            return response()->json([
                'message' => 'Declined successfully'
            ], 200);
        }
        catch(DomainException $e)
        {
            Log::error('BookingController declineBooking failed. Error: ' . $e->getMessage());

            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function cancelBooking(int $bookingId)
    {
        try
        {
            Log::info('BookingController cancelBooking called');

            $isCaceled = $this->bookingService->cancelBooking($bookingId);

            return response()->json([
                'message' => 'Canceled successfully'
            ], 200);
        }
        catch(DomainException $e)
        {
            Log::error('BookingController cancelBooking failed. Error: ' . $e->getMessage());

            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
