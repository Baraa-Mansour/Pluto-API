<?php

namespace App\Repositories;
use Illuminate\Support\Facades\Log;
use App\Models\PaymentMethod;

class PaymentMethodRepo
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getAllPaymentMethods()
    {
        Log::info('PaymentMethodRepo getAllPaymentMethods called');

        return PaymentMethod::all();
    }
}
