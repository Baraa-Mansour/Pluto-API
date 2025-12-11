<?php

namespace App\Http\Controllers;

use App\Services\PaymentMethodService;
use Illuminate\Support\Facades\Log;

class PaymentMethodController extends Controller
{
    private PaymentMethodService $paymentMethodService;

    public function __construct(PaymentMethodService $paymentMethodService)
    {
        $this->paymentMethodService = $paymentMethodService;
    }

    public function getAllPaymentMethods()
    {
        Log::info('PaymentMethodController getAllPaymentMethods called');

        $methods = $this->paymentMethodService->getAllPaymentMethods();

        return response()->json([
            'data:' => $methods
        ], 200);
    }
}
