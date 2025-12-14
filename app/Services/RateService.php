<?php

namespace App\services;

use App\Models\Rate;
use App\Repositories\BookingRepo;
use Illuminate\Support\Facades\Log;
use App\Repositories\RateRepo;
use DomainException;

class RateService
{
    private RateRepo $rateRepo;
    private BookingRepo $bookingRepo;

    public function __construct(RateRepo $rateRepo, BookingRepo $bookingRepo)
    {
        $this->rateRepo = $rateRepo;
        $this->bookingRepo = $bookingRepo;
    }

    public function rateApartment(int $userId, int $apartmentId, int $rate)
    {
        Log::info('RateService rateApartment called');

        $isAlreadyRated = $this->rateRepo->isRatedByUser($userId,$apartmentId);

        if($isAlreadyRated)
        {
            Log::error('RateService rateApartment failed: already rated');

            throw new DomainException('Already rated');
        }

        $isBooked = $this->bookingRepo->haveCompletedBookingForApartment($userId,$apartmentId);

        if(!$isBooked)
        {
            Log::error('RateService rateApartment failed: no booking found');

            throw new DomainException('No completed Booking found for this apartment');
        }

        $rateModel = $this->getRateInstanceFromRequest($userId,$apartmentId,$rate);

        return $this->rateRepo->saveRate($rateModel);
    }

    public function getRateInstanceFromRequest(int $userId, int $apartmentId, int $rate)
    {
        Log::info('RateService getRateInstanceFromRequest called');

        $rateModel = new Rate();

        $rateModel->user_id = $userId;
        $rateModel->apartment_id = $apartmentId;
        $rateModel->rate = $rate;

        return $rateModel;
    }
}
