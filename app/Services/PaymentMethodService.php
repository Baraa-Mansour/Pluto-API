<?php

namespace App\Services;

use App\Repositories\PaymentMethodRepo;
use Illuminate\Support\Facades\Log;

class PaymentMethodService
{
    private PaymentMethodRepo $paymentMethodRepo;

    public function __construct(PaymentMethodRepo $paymentMethodRepo)
    {
        $this->paymentMethodRepo = $paymentMethodRepo;
    }

    public function getAllPaymentMethods()
    {
        Log::info('PaymentMethods service getAllPaymentMethods called');

        return $this->paymentMethodRepo->getAllPaymentMethods();
    }
}
