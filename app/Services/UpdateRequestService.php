<?php

namespace App\Services;

use App\Models\UpdateRequest;
use App\Repositories\BookingRepo;
use App\Repositories\UpdateRequestRepo;
use DomainException;
use Illuminate\Support\Facades\Log;

class UpdateRequestService
{
    private UpdateRequestRepo $updateRequestRepo;
    private BookingRepo $bookingRepo;
    public function __construct(UpdateRequestRepo $updateRequestRepo, BookingRepo $bookingRepo)
    {
        $this->updateRequestRepo = $updateRequestRepo;
        $this->bookingRepo = $bookingRepo;
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
}
