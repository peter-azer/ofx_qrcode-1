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

    /**
     * Initialize the payment by creating a session.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function initializePayment(Request $request)
    {
        // Get payment details from the request
        $amount = $request->input('amount');
        $orderId = $request->input('orderId');
        $currency = 'EGP'; // Egyptian Pound for this example
        $callbackUrl = route('payment.callback'); // Define your callback route

        // Call the GeideaPaymentService to create a session
        $response = $this->geideaService->createSession($amount, $currency, $orderId, $callbackUrl);

        // Check if the session creation was successful and return the session ID and redirect URL
        if (isset($response['session']['id'])) {
            return response()->json([
                'status' => 'success',
                'sessionId' => $response['session']['id'],  // Return session ID
                'redirectUrl' => $response['session']['redirectUrl']  // Return redirect URL (for Geidea Checkout)
            ]);
        } else {
            // If session creation fails, return an error response
            return response()->json([
                'status' => 'error',
                'message' => $response['message'] ?? 'Failed to create session.'
            ], 500);
        }
    }

    /**
     * Handle the callback from Geidea after payment completion.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function paymentCallback(Request $request)
    {
        // Process the payment details returned from Geidea
        return response()->json([
            'status' => $request->input('status'),
            'orderId' => $request->input('orderId'),
            'message' => $request->input('message'),
        ]);
    }
}
