<?php

namespace App\Services;

use App\Models\Booking;
use App\Repositories\BookingRepo;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class BookingService
{
    private BookingRepo $bookingRepo;

    public function __construct(BookingRepo $bookingRepo)
    {
        $this->bookingRepo = $bookingRepo;
    }


    public function getAllUserBookings(int $userId)
    {
        Log::info('BookingService getAllUserBookings Called');

        $bookings = $this->bookingRepo->getAllUserBookings($userId);

        return $bookings;
    }

    public function getOwnerRegistrations(int $ownerId)
    {
        Log::info('BookingService getOwnerRegistrations called');

        $registration = $this->bookingRepo->getOwnerRegistrations($ownerId);

        return $registration;
    }

    public function createBooking(array $data, int $userId)
    {
        Log::info('BookingService createBooking Called');

        $booking = $this->getBookingInstanceFromData($data , $userId);

        $isAvailable = $this->bookingRepo->isBookingAccepted(
            $booking->apartment_id,
            $userId,
            $booking->from_date,
            $booking->to_date
        );

        if(!$isAvailable)
        {
            throw new DomainException('Apartment already booked or You have already registered at same date');
        }

        return $this->bookingRepo->createBooking($booking);
    }

    public function acceptBooking(int $bookingId)
    {
        Log::info('BookingService acceptBooking called');

        $booking = $this->bookingRepo->findBookingById($bookingId);

        if(!$this->validBookingToAccept($booking))
        {
            Log::error('BookingService acceptBooking condition failed');

            throw new DomainException('This booking already proceed or its expire');
        }

        $booking->status = Booking::STATUS_ACCEPTED;

        return $this->bookingRepo->acceptBookingAndDeclineOthers($booking);
    }

    public function declineBooking(int $bookingId)
    {
        Log::info('BookingService declineBooking called');

        $booking = $this->bookingRepo->findBookingById($bookingId);

        if($booking->status !== Booking::STATUS_REGISTERED)
        {
            Log::error('BookingService declineBooking condition failed');

            throw new DomainException('This booking already proceed');
        }

        $booking->status = Booking::STATUS_DECLINED;

        return $this->bookingRepo->saveBooking($booking);
    }

    public function cancelBooking(int $bookingId)
    {   
        Log::info('BookingService cancelBookiing called');

        $booking = $this->bookingRepo->findBookingById($bookingId);

        if(!$this->validBookingToCancel($booking))
        {
            Log::error('BookingService cancelBooking condition failed');

            throw new DomainException('This booking is already declined/canceled or currently in use');
        }

        $booking->status = Booking::STATUS_CANCELED;

        return $this->bookingRepo->saveBooking($booking);
    }
    
    private function validBookingToCancel(Booking $booking)
    {
        Log::info('BookingService validBookingToCancel called');

        $bookingStartDate = Carbon::parse($booking->from_date)->subDay();

        $isValid = $booking->status !== Booking::STATUS_DECLINED && 
                   $booking->status !== Booking::STATUS_CANCELED &&
                   $bookingStartDate->gt(Carbon::today());
                   
        return $isValid;        
    }
    private function validBookingToAccept(Booking $booking)
    {
        Log::info('BookingService validBookingToAccept called');

        $bookingStartDate = Carbon::parse($booking->from_date)->subDay();

        $isAccepted = $booking->status === Booking::STATUS_REGISTERED && $bookingStartDate->gt(Carbon::today());

        return $isAccepted;
    }
    private function getBookingInstanceFromData(array $data , int $userId)
    {
        $booking = new Booking();

        $booking->user_id = $userId;
        $booking->apartment_id = $data['apartment_id'];
        $booking->from_date = $data['from_date'];
        $booking->to_date = $data['to_date'];
        $booking->payment_method_id = $data['payment_method_id'];
        $booking->status = Booking::STATUS_REGISTERED;

        return $booking;
    }
}
