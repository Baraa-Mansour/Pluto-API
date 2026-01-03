<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest\BookingRegisterRequest;
use App\Http\Resources\BookingResource\BookingsForUserResource;
use App\Services\BookingService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Exceptions\NotEnoughBalanceException;
use App\Http\Resources\BookingResource\BookingsForOwnerResource;

class BookingController extends Controller
{
    private BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function getAllUserBookings(Request $request)
    {
        Log::info('BookingController getAllUserBookings Called');

        $bookings = $this->bookingService->getAllUserBookings($request->user()->id);

        $bookings_resource = BookingsForUserResource::collection($bookings);

        return response()->json([
            'data' => $bookings_resource
        ], 200);
    }

    public function getOwnerRegistrations(Request $request)
    {
        Log::info('BookingController getOwnerRegistrations called');

        $registration = $this->bookingService->getOwnerRegistrations($request->user()->id);

        $registration_resource = BookingsForOwnerResource::collection($registration);

        return response()->json([
            'data' => $registration_resource
        ], 200);
    }

    public function createBooking(BookingRegisterRequest $request)
    {
        try
        {
            Log::info('BookingController createBooking called');

            $validated = $request->validated();

            $isSaved = $this->bookingService->createBooking($validated , $request->user()->id);

            return response()->json([
                'message' => 'Request added successfully'
            ], 201);
        }
        catch(NotEnoughBalanceException $e)
        {
            Log::warning('BookingController createBooking failed due to not enough balance');

            return response()->json([
                'message' => $e->getMessage()
            ], 402);
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
            ], 400);
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
            ], 400);
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
            ], 400);
        }
    }

    public function getOk()
    {
        return 'ok';
    }
}


