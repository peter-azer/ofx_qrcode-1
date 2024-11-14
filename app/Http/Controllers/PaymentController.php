<?php

namespace App\Http\Controllers;

use App\Services\GeideaPaymentService;
use Illuminate\Http\Request;





class PaymentController extends Controller
{
    protected $geideaService;

    // Constructor to inject the GeideaPaymentService
    public function __construct(GeideaPaymentService $geideaService)
    {
        $this->geideaService = $geideaService;
    }

    /**
     * Initiate the payment process.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function initiatePayment(Request $request)
    {
        // Get the amount from the request
        $amount = $request->input('amount');

        // Assuming Egyptian Pound (EGP) as the currency
        $currency = 'EGP';

        // Generate a unique order ID
        $orderId = uniqid();

        // Define the callback URL (This should be the route that Geidea will call after the transaction)
        $callbackUrl = route('payment.callback');

        // Call the service method to initiate payment
        $response = $this->geideaService->initiatePayment($amount, $currency, $orderId, $callbackUrl);

        // Check if the response contains a redirect URL
        if (isset($response['redirectUrl'])) {
            // If successful, return the redirect URL to the frontend
            return response()->json([
                'status' => 'success',
                'redirectUrl' => $response['redirectUrl']
            ]);
        } else {
            // If there was an error, return a failure response
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to initiate payment.'
            ], 500);
        }
    }

    /**
     * Handle the callback after payment completion.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function paymentCallback(Request $request)
    {
        // Here, we assume the response will include a status and order ID
        $status = $request->input('status');
        $orderId = $request->input('orderId');
        $message = $request->input('message');

        // Log or process the payment details as needed (e.g., store payment status, send confirmation)
        // Example: log the response for debugging
        \Log::info('Payment Callback: ', ['status' => $status, 'orderId' => $orderId, 'message' => $message]);

        // Respond with the details to the frontend
        return response()->json([
            'status' => $status,
            'orderId' => $orderId,
            'message' => $message,
        ]);
    }
}

