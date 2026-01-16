<?php

namespace App\Services;

use App\Exceptions\NotEnoughBalanceException;
use App\Models\Booking;
use App\Models\User;
use App\Repositories\BookingRepo;
use App\Repositories\UserRepo;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\ApartmentRepo;
use App\Services\BalanceService;
use App\Services\FcmService;

class BookingService
{
    private BookingRepo $bookingRepo;
    private UpdateRequestService $updateRequestService;
    private UserRepo $userRepo;
    private ApartmentRepo $apartmentRepo;
    private BalanceService $balanceService;
    private FcmService $fcmService;

    public function __construct(BookingRepo $bookingRepo, UpdateRequestService $updateRequestService, UserRepo $userRepo, 
    ApartmentRepo $apartmentRepo, BalanceService $balanceService, FcmService $fcmService)
    {
        $this->bookingRepo = $bookingRepo;
        $this->updateRequestService = $updateRequestService;
        $this->userRepo = $userRepo;
        $this->apartmentRepo = $apartmentRepo;
        $this->balanceService = $balanceService;
        $this->fcmService = $fcmService;
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

        $user = $this->userRepo->findUserById($userId);
        $apartment = $this->apartmentRepo->getApartmentById($data['apartment_id']);

        $totalPrice = $this->calculateTotalPrice($apartment->price, $data['from_date'], $data['to_date']);

        if($user->balance < $totalPrice)
        {
            Log::error('BookingService createBooking dont have enough balance');

            throw new NotEnoughBalanceException();
        }

        $data['total_price'] = $totalPrice;
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

        db::transaction(function () use ($booking, $user, $totalPrice)
        {
            // Deduct user balance
            $this->balanceService->deduct($user, $totalPrice);

            $this->bookingRepo->saveBooking($booking);
        });

        $this->sendNotification(
            $user,
            $booking->id,
            'Booking Request Created',
            'Your booking request has been created successfully. #' . $booking->id
        );

        return true;
    }

    // owner
    public function acceptBooking(int $bookingId)
    {
        Log::info('BookingService acceptBooking called');

        $booking = $this->bookingRepo->findBookingById($bookingId);

        if(!$this->validBookingToAccept($booking))
        {
            Log::error('BookingService acceptBooking condition failed');

            throw new DomainException('This booking already proceed or its expire');
        }

        $this->acceptBookingAndDeclineOthers($booking);

        $this->sendNotification(
            $booking->user,
            $booking->id,
            'Booking Request Accepted',
            'Your booking request has been accepted. #' . $booking->id
        );
    }

    public function declineBooking(int $bookingId)
    {
        Log::info('BookingService declineBooking called');

        $booking = $this->bookingRepo->findBookingById($bookingId);

        if(!$this->validBookingToDecline($booking))
        {
            Log::error('BookingService declineBooking condition failed');

            throw new DomainException('This booking already proceed');
        }

        db::transaction(function () use ($booking)
        {
            // Refund user balance
            $user = $this->userRepo->findUserById($booking->user_id);
            $this->balanceService->increase($user, $booking->total_price);

            $booking->status = Booking::STATUS_DECLINED;
            $this->bookingRepo->saveBooking($booking);

            $this->updateRequestService->deleteBookingUpdateRequest($booking->id);

        });

        $this->sendNotification(
            $booking->user,
            $booking->id,
            'Booking Request Declined',
            'Your booking request has been declined. #' . $booking->id
        );

        return true;
    }
    //--
    public function completeBooking(Booking $booking)
    {
        Log::info('BookingService completeBookings called');

        if(!$this->validBookingToComplete($booking))
        {
            Log::error('BookingService completeBookings condition failed');

            throw new DomainException('This booking is not valid to complete now');
        }

        $owner = $booking->apartment->user;

        $booking->status = Booking::STATUS_COMPLETED;
        db::transaction(function () use ($booking, $owner)
        {
            $this->bookingRepo->saveBooking($booking);

            // pay owner
            $this->balanceService->increase($owner, $booking->total_price);
        });

        $this->sendNotification(
            $booking->user,
            $booking->id,
            'Booking Completed',
            'Your booking has been completed successfully. #' . $booking->id
        );

    }
    // customer
    public function cancelBooking(int $bookingId)
    {   
        Log::info('BookingService cancelBooking called');

        $booking = $this->bookingRepo->findBookingById($bookingId);

        if(!$this->validBookingToCancel($booking))
        {
            Log::error('BookingService cancelBooking condition failed');

            throw new DomainException('This booking is already declined/canceled or currently in use');
        }

        $booking->status = Booking::STATUS_CANCELED;

        DB::transaction(function () use ($booking)
        {
            $this->bookingRepo->saveBooking($booking);

            $this->updateRequestService->deleteBookingUpdateRequest($booking->id);

            $this->balanceService->increase(
                $this->userRepo->findUserById($booking->user_id),
                $booking->total_price
            );
        });

        $this->sendNotification(
            $booking->user,
            $booking->id,
            'Booking Canceled',
            'Your booking has been canceled successfully. #' . $booking->id
        );

        return true;
    }
    //--

    // Background Jobs
    public function completeAcceptedBookings()
    {
        Log::info('BookingService completeAcceptedBookings called');

        $expiredBookings = $this->bookingRepo->getAllExpiredAcceptedBookings();

        foreach ($expiredBookings as $booking) 
        {
            try 
            {
                $this->completeBooking($booking);
            } 
            catch (DomainException $e) 
            {
                Log::error('BookingService completeAcceptedBookings error: ' . $e->getMessage());
            }
        }
    }

    public function declineExpiredRegistrations()
    {
        Log::info('BookingService declineExpiredRegistrations called');

        $expiredBookings = $this->bookingRepo->getAllExpiredRegisteredBookings();

        foreach ($expiredBookings as $booking) 
        {
            try 
            {
                $this->declineBooking($booking->id);
            } 
            catch (DomainException $e) 
            {
                Log::error('BookingService declineExpiredRegistrations error: ' . $e->getMessage());
            }
        }
    }
    //--

    // helpers
    private function validBookingToCancel(Booking $booking)
    {
        Log::info('BookingService validBookingToCancel called');

        $bookingStartDate = Carbon::parse($booking->from_date);

        $isValid = $booking->status !== Booking::STATUS_DECLINED && 
                   $booking->status !== Booking::STATUS_CANCELED &&
                   $booking->status !== Booking::STATUS_COMPLETED &&
                   $bookingStartDate->gt(Carbon::today());
                   
        return $isValid;        
    }
    private function validBookingToAccept(Booking $booking)
    {
        Log::info('BookingService validBookingToAccept called');

        $bookingStartDate = Carbon::parse($booking->from_date);

        $isAccepted = $booking->status === Booking::STATUS_REGISTERED && $bookingStartDate->gt(Carbon::today());

        return $isAccepted;
    }
    private function validBookingToDecline(Booking $booking)
    {
        Log::info('BookingService validBookingToDecline called');

        $isDeclined = $booking->status === Booking::STATUS_REGISTERED ||
                      ($booking->status === Booking::STATUS_ACCEPTED && Carbon::parse($booking->from_date)->gt(Carbon::today()));

        return $isDeclined;
    }
    private function validBookingToComplete(Booking $booking)
    {
        Log::info('BookingService validBookingToComplete called');

        $bookingEndDate = Carbon::parse($booking->to_date);

        $isValid = $booking->status === Booking::STATUS_ACCEPTED && 
                   $bookingEndDate->lt(Carbon::today());
                   
        return $isValid;        
    }

    private function getBookingInstanceFromData(array $data , int $userId)
    {
        $booking = new Booking();

        $booking->user_id = $userId;
        $booking->apartment_id = $data['apartment_id'];
        $booking->from_date = $data['from_date'];
        $booking->to_date = $data['to_date'];
        //$booking->payment_method_id = $data['payment_method_id'];
        $booking->status = Booking::STATUS_REGISTERED;
        $booking->total_price = $data['total_price'];

        return $booking;
    }

    public function acceptBookingAndDeclineOthers(Booking $booking)
    {
        Log::info('BookingRepo acceptBookingAndDeclineOthers');

        $declinedBookings = DB::transaction(function () use ($booking) {
            // accept this booking
            $booking->status = Booking::STATUS_ACCEPTED;
            $booking->save();
    
            // decline all others with same apartment and on range date
            $declinedBookingsTransaction = $this->bookingRepo->declineOtherApartmentBookingsBetweenDates($booking);

            foreach ($declinedBookingsTransaction as $declinedBooking) 
            {
                // refund balance to users of declined bookings
                $user = $this->userRepo->findUserById($declinedBooking->user_id);
                $this->balanceService->increase($user, $declinedBooking->total_price);
                $user->save();
            }

            // delete conflict updateRequest
            $this->updateRequestService->deleteUpdateRequestForApartmentInDate(
                $booking->apartment_id, 
                $booking->from_date, 
                $booking->to_date
            );

            return $declinedBookingsTransaction;
        });

        foreach ($declinedBookings as $declinedBooking) 
        {
            $this->sendNotification(
                $declinedBooking->user,
                $declinedBooking->id,
                'Booking Request Declined',
                'Your booking request has been declined. #' . $declinedBooking->id
            );
        }
        
        return true;
    }

    private function getDaysBetweenDates(string $fromDate , string $toDate)
    {
        $from = Carbon::parse($fromDate);
        $to = Carbon::parse($toDate);

        return $from->diffInDays($to) + 1;
    }
    public function calculateTotalPrice(float $pricePerDay, string $fromDate , string $toDate)
    {
        $days = $this->getDaysBetweenDates($fromDate , $toDate);

        return $pricePerDay * $days;
    }

    public function sendNotification(User $user, int $bookingId, string $title, string $body)
    {
        if(isset($user->fcm_token)) 
        {
            $this->fcmService->sendNotification($user->fcm_token, $title, $body, [
                'booking_id' => $bookingId,
            ]);
        }
    }
    //--
}
