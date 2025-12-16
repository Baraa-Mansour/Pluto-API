<?php

namespace App\Http\Controllers;

use App\Http\Requests\bookingRequest\UpdateBookingRequest;
use App\Http\Resources\BookingResource\BookingUpdateRequestResource;
use App\Models\Booking;
use App\Services\UpdateRequestService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateRequestController extends Controller
{
    private UpdateRequestService $updateRequestService;

    public function __construct(UpdateRequestService $updateRequestService)
    {
        $this->updateRequestService = $updateRequestService;
    }

    public function updateBookingRequest(UpdateBookingRequest $request)
    {
        Log::info('UpdateRequestController updateBookingRequest called');

        $validated = $request->validated();

        $booking = Booking::where('id', $request->route('bookingId'))
                          ->where('from_date', '>', now())
                          ->where('status', Booking::STATUS_ACCEPTED)
                          ->firstOrFail();

        $validated['new_from_date'] = Carbon::parse($validated['new_from_date']);
        $validated['new_to_date']   = Carbon::parse($validated['new_to_date']);

        $validated['booking'] = $booking;

        try
        {
            $isUpdateRequestAdded = $this->updateRequestService->createUpdateBookingRequest($validated);

            return response()->json([
                'message' => 'Request added successfully'
            ], 200);
        }
        catch(DomainException $ex)
        {
            Log::error('UpdateRequestController updateBookingRequest failed: ' . $ex->getMessage());

            return response()->json([
                'message' => $ex->getMessage()
            ], 422);
        }
    }

    public function getUpdateRequests(Request $request)
    {
        $userId = $request->user()->id;

        $updateRequests = $this->updateRequestService->getOwnerUpdateRequest($userId);

        $request_resource = BookingUpdateRequestResource::collection($updateRequests);

        return response()->json([
            'data' => $request_resource
        ], 200);
    }

    public function deleteUpdate(Request $request, int $updateRequestId)
    {
        $updateRequest = $this->updateRequestService->getUpdateRequestById($updateRequestId);

        if($request->user()->id !== $updateRequest->booking->apartment->user_id)
        {
            return response()->json([
                'message' => 'UnAuthorize'
            ], 403);
        }

        $updateRequest->delete();

        return response()->json([
            'message' => 'deleted successfully'
        ], 200);
    }
}
