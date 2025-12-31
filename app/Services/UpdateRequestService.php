<?php

namespace App\Services;

use App\Exceptions\NotEnoughBalanceException;
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
    private BalanceService $balanceService;

    public function __construct(UpdateRequestRepo $updateRequestRepo, BookingRepo $bookingRepo,
        BalanceService $balanceService)
    {
        $this->updateRequestRepo = $updateRequestRepo;
        $this->bookingRepo = $bookingRepo;
        $this->balanceService = $balanceService;
    }

    public function createUpdateBookingRequest(array $data, BookingService $bookingService)
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

        $originalPrice = $data['booking']->total_price;
        $newPrice = $bookingService->calculateTotalPrice(
            $data['booking']->apartment->price,
            $data['new_from_date']->toDateString(),
            $data['new_to_date']->toDateString()
        );

        $updateRequest->price_change = $this->calculatePriceChange($originalPrice, $newPrice);

        if($updateRequest->price_change > $data['booking']->user->balance)
        {
            Log::info('UpdateRequestService createUpdateBookingRequest faild: not enough balance for price change');

            throw new NotEnoughBalanceException('Not enough balance!');
        }

        db::transaction(function () use ($updateRequest, $data) 
        {
            if($updateRequest->price_change > 0)
            {
                $user = $data['booking']->user;

                $this->balanceService->deduct(
                    $user, 
                    $updateRequest->price_change
                );

                $user->save();
            }

            $updateRequest->save();
        });

    }

    public function getOwnerUpdateRequest(int $ownerId)
    {
        return UpdateRequest::with('booking.apartment')
                            ->where('new_from_date', '>', today())
                            ->whereHas('booking', function ($query) 
                            {
                                $query->where('from_date', '>', today());
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
                            ->where('new_from_date', '>', today())
                            ->firstOrFail();
    }

    public function acceptUpdate(UpdateRequest $updateRequest, BookingService $bookingService)
    {
        $booking = $this->bookingRepo->findBookingById($updateRequest->booking_id);

        $booking->from_date = $updateRequest->new_from_date;
        $booking->to_date = $updateRequest->new_to_date;
        $booking->total_price += $updateRequest->price_change;

        db::transaction(function () use($booking, $updateRequest, $bookingService)
        {
            if($updateRequest->price_change < 0)
            {
                $user = $booking->user;

                $this->balanceService->increase(
                    $user, 
                    abs($updateRequest->price_change)
                );

                $user->save();
            }

            $bookingService->acceptBookingAndDeclineOthers($booking);

            $updateRequest->delete();
        });

    }

    public function deleteBookingUpdateRequest(int $bookingId)
    {
        $booking = $this->bookingRepo->findBookingById($bookingId);
        $updateRequest = UpdateRequest::where('booking_id', $booking->id)->first();

        if(!$updateRequest)
        {
            return;
        }

        if($updateRequest->price_change > 0)
        {
            db::transaction(function () use ($updateRequest, $booking) 
            {                
                $user = $booking->user;

                $this->balanceService->increase(
                    $user, 
                    $updateRequest->price_change
                );
                $user->save();

                $updateRequest->delete();      
            });
            return;
        }

        $updateRequest->delete();
    }

    public function deleteUpdateRequest(UpdateRequest $updateRequest)
    {

        if($updateRequest->price_change > 0)
        {
            db::transaction(function () use ($updateRequest) 
            {                
                $user = $updateRequest->booking->user;

                $this->balanceService->increase(
                    $user, 
                    $updateRequest->price_change
                );

                $user->save();
                
                $updateRequest->delete();      
            });

            return;
        }

        $updateRequest->delete();
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
            if(!$updateRequest)
            {
                return;
            }
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
                    
                    $user->save();
                }

                $updateRequest->delete();
            });
        }
        
    }

    private function calculatePriceChange(float $originalPrice, float $newPrice): float
    {
        return $newPrice - $originalPrice;
    }
}
