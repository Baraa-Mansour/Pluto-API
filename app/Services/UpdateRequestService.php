<?php

namespace App\Services;

use App\Models\UpdateRequest;
use App\Repositories\BookingRepo;
use App\Repositories\UpdateRequestRepo;
use DomainException;
use Illuminate\Support\Facades\Log;
use function Symfony\Component\Clock\now;
use Illuminate\Support\Facades\DB;

class UpdateRequestService
{
    private UpdateRequestRepo $updateRequestRepo;
    private BookingRepo $bookingRepo;
    private BookingService $bookingService;
    private BalanceService $balanceService;

    public function __construct(UpdateRequestRepo $updateRequestRepo, BookingRepo $bookingRepo,
        BookingService $bookingService, BalanceService $balanceService)
    {
        $this->updateRequestRepo = $updateRequestRepo;
        $this->bookingRepo = $bookingRepo;
        $this->bookingService = $bookingService;
        $this->balanceService = $balanceService;
    }

    public function createUpdateBookingRequest(array $data)
    {
        Log::info('UpdateRequestService createUpdateBookingRequest called');

        $haveUpdateRequest = UpdateRequest::where('booking_id', $data['booking']->id)->exists();

        if($haveUpdateRequest)
        {
            Log::info('UpdateRequestService createUpdateBookingRequest faild: already have update request');

            throw new DomainException('Already have request');
        }

        $isBookedInDate = $this->bookingRepo->isApartmentBookedInDateFromAnother(
            $data['booking']->apartment->id,
            $data['booking']->id,
            $data['new_from_date'],
            $data['new_to_date']
        );

        if($isBookedInDate)
        {
            Log::info('UpdateRequestService createUpdateBookingRequest faild: date not avilable');

            throw new DomainException('Date not avilable');
        }

        $updateRequest = new UpdateRequest();
        $updateRequest->booking_id = $data['booking']->id;
        $updateRequest->new_from_date = $data['new_from_date'];
        $updateRequest->new_to_date = $data['new_to_date'];

        return $updateRequest->save();
    }

    public function getOwnerUpdateRequest(int $ownerId)
    {
        return UpdateRequest::with('booking.apartment')
                            ->where('new_from_date', '>', now())
                            ->whereHas('booking', function ($query) 
                            {
                                $query->where('from_date', '>', now());
                            })
                            ->whereHas('booking.apartment', function ($query) use ($ownerId) 
                            {
                                $query->where('user_id', $ownerId);
                            })
                            ->get();
    }

    public function getUpdateRequestById(int $updateRequestId)
    {
        return UpdateRequest::with('booking.apartment')
                            ->where('id', $updateRequestId)
                            ->where('new_from_date', '>', now())
                            ->firstOrFail();
    }

    public function acceptUpdate(UpdateRequest $updateRequest)
    {
        $booking = $this->bookingRepo->findBookingById($updateRequest->booking_id);

        $booking->from_date = $updateRequest->new_from_date;
        $booking->to_date = $updateRequest->new_to_date;

        return $this->bookingService->acceptBookingAndDeclineOthers($booking);

    }

    public function deleteBookingUpdateRequest(int $bookingId)
    {
        $booking = $this->bookingRepo->findBookingById($bookingId);

        UpdateRequest::where('booking_id', $booking->id)->delete();
    }

    public function deleteUpdateRequestForApartmentInDate(int $apartmentId, string $fromDate, string $toDate)
    {
        Log::info('UpdateRequestService deleteUpdateRequestForApartmentInDate called');

        $updateRequests = $this->updateRequestRepo->getUpdateRequestForApartmentInDate(
            $apartmentId, $fromDate, $toDate
        );

        // refund then delete
        foreach ($updateRequests as $updateRequest) 
        {
            db::transaction(function () use ($updateRequest) 
            {                
                if($updateRequest->price_change > 0)
                {
                    $user = $this->bookingRepo->findBookingById($updateRequest->booking_id)->user;

                    // refund balance to user
                    $this->balanceService->increase(
                        $user, 
                        $updateRequest->price_change
                    );                    
                }

                $updateRequest->delete();
            });
        }
        
    }
}
