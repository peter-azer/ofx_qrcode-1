<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GeideaPaymentService;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(GeideaPaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function sendMoney(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|string|size:3',
        ]);

        $amount = $request->input('amount');
        $currency = $request->input('currency', 'SAR');

        $response = $this->paymentService->createPayment($amount, $currency);

        return response()->json($response);
    }

    public function handleCallback(Request $request)
    {
        // Process the callback data sent by Geidea here
        $paymentStatus = $request->input('status');
        $paymentId = $request->input('paymentId');

        // Log or update the status in the database if necessary
        // Respond as per your application's requirement

        return response()->json(['status' => $paymentStatus, 'paymentId' => $paymentId]);
    }
}
