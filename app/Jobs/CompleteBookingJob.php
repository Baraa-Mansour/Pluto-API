<?php

namespace App\Jobs;

use App\Services\BookingService;

class CompleteBookingJob
{

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(BookingService $bookingService): void
    {
        $bookingService->completeAcceptedBookings();
    }
}
