<?php

namespace App\Repositories;

use App\Models\Booking;
use Illuminate\Support\Facades\Date;
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
}
