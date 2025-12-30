<?php

namespace App\Repositories;

use App\Models\Booking;
use App\Models\UpdateRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class BookingRepo
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function createBooking(Booking $booking)
    {
        Log::info('BookingRepo createBooking called');

        return $booking->save();
    }

    public function findBookingById(int $id)
    {
        Log::info('BookingRepo findBookingById called');

        return Booking::findOrFail($id);
    }

    public function getAllUserBookings(int $userId)
    {
        Log::info('BookingRepo getAllUserBookings Called');

        $bookings = Booking::with('apartment')->where('user_id', $userId)->get();

        return $bookings;
    }

    public function getOwnerRegistrations(int $ownerId)
    {
        Log::info('BookingRepo getOwnerRegistrations called');

        $registration = Booking::with('apartment', 'user')
                        ->where('status', Booking::STATUS_REGISTERED)
                        ->whereHas('apartment', function ($query) use ($ownerId) 
                        {
                            $query->where('user_id', $ownerId);
                        })->get();

        return $registration;
    }

    public function cancelFutureBookingsForUser(int $userId)
    {
        Log::info('BookingRepo cancelFutureBookingsForUser called');

        Booking::where('user_id', $userId)
               ->whereIn('status', [Booking::STATUS_ACCEPTED,Booking::STATUS_REGISTERED])
               ->where('from_date', '>', now()->subDay())
               ->update(['status' => Booking::STATUS_CANCELED]);
    }
    public function declineFutureBookingsForOwner(int $userId)
    {
        Log::info('BookingRepo declineFutureRegistrationForOwner called');

        Booking::whereHas('apartment', function ($query) use ($userId) 
                {
                        $query->where('user_id', $userId);
                })
                ->whereIn('status', [Booking::STATUS_REGISTERED, Booking::STATUS_ACCEPTED])
                ->where('from_date', '>', now()->subDay())                
                ->update(['status' => Booking::STATUS_DECLINED]);
    }

    public function saveBooking(Booking $booking)
    {
        Log::info('BookingService saveBooking called');

        return $booking->save();
    }

    public function haveCompletedBookingForApartment(int $userId, int $apartmentId)
    {
        Log::info('BookingRepo haveCompletedBookingForApartment called');

        $haveBooking = Booking::where('user_id', $userId)
                              ->where('apartment_id', $apartmentId)
                              ->where('to_date', '<', now()->subDay())
                              ->where('status', Booking::STATUS_ACCEPTED)
                              ->exists();
                              
        return $haveBooking;
    }

    // check if apartment booked in date or user have registered
    public function isBookingAccepted(int $apartmentId, int $userId, $fromDate, $toDate)
    {
        Log::info('BookingRepo isBookinAccepted called');

        $query = " 
        SELECT 1
        FROM bookings
        WHERE apartment_id = ?
        AND (
                (status = 'accepted' AND (from_date <= ? AND to_date >= ?))
                OR 
                (user_id = ? AND status = 'registered' AND ((from_date = ?) AND (to_date = ?)))
            )
        LIMIT 1
        "; // query to check the 2 condition

        $result = DB::select($query, [
            $apartmentId,
            $toDate, $fromDate,
            $userId,
            $fromDate, $toDate
        ]);

        return empty($result);
    }

    public function declineOtherApartmentBookingsBetweenDates(Booking $booking)
    {
        Log::info('BookingRepo declineOtherApartmentBookingsBetweenDates called');

        $declinedBookings = Booking::where('apartment_id', $booking->apartment_id)
                                   ->where('from_date', '<=' ,$booking->to_date)
                                   ->where('to_date' , '>=' , $booking->from_date)
                                   ->where('id', '!=', $booking->id)
                                   ->where('status', Booking::STATUS_REGISTERED)
                                   ->get();

        foreach($declinedBookings as $b)
        {
            $b->status = Booking::STATUS_DECLINED;
            $b->save();
        }

        return $declinedBookings;
    }
    
    public function isApartmentBookedInDateFromAnother(int $apartmentId, int $bookingId, $fromDate, $toDate)
    {
        Log::info('BookingRepo isApartmentBookedInDateFromAnother called');

        return Booking::where('id', '!=', $bookingId)
                        ->where('apartment_id', $apartmentId)
                        ->where(function ($q) use ($fromDate, $toDate) 
                        {
                            $q->where('from_date', '<=', $toDate)
                            ->where('to_date', '>=', $fromDate);
                        })
                        ->where('status', Booking::STATUS_ACCEPTED)
                        ->exists();
    }
}
