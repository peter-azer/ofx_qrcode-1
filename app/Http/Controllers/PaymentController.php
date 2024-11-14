<?php

namespace App\Http\Controllers;

use App\Services\GeideaPaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $geideaService;

    public function __construct(GeideaPaymentService $geideaService)
    {
        $this->geideaService = $geideaService;
    }

    // Initiate the payment process
    public function initiatePayment(Request $request)
    {
        $amount = $request->input('amount');
        $currency = 'EGP';  // Assuming Egyptian Pound for this example
        $orderId = uniqid(); // Generate a unique order ID
        $callbackUrl = route('payment.callback'); // Define your callback route

        $response = $this->geideaService->initiatePayment($amount, $currency, $orderId, $callbackUrl);

        if (isset($response['redirectUrl'])) {
            // Send the URL back to the frontend
            return response()->json([
                'status' => 'success',
                'redirectUrl' => $response['redirectUrl']
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to initiate payment.'
            ], 500);
        }
    }

    // Handle callback after payment completion
    public function paymentCallback(Request $request)
    {
        // You can process payment details here or return them to the frontend
        return response()->json([
            'status' => $request->input('status'),
            'orderId' => $request->input('orderId'),
            'message' => $request->input('message'),
        ]);
    }
}
