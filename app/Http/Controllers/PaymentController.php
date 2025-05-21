<?php

namespace App\Http\Controllers;

use App\Services\GeideaPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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

    $validator = \Validator::make($request->all(), [
        'amount' => 'required|numeric|min:1',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'errors' => $validator->errors(),
        ], 400);
    }

    $amount = $request->input('amount');
    $currency = 'EGP';

    $response = $this->geideaService->createSession($amount, $currency,  'https://backend.ofx-qrcode.com/api/payment/callback');

    if (isset($response['session']['id'])) {
        return response()->json([
            'status' => 'success',
            'sessionId' => $response['session']['id'],

        ]);
    } else {

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




    public function createPaymentLink(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $user = $request->user(); // Fetch authenticated user
        $amount = $request->input('amount');

        // Prepare data to send to Geidea API
        $payload = [
            'amount' => $amount,
            'currency' => 'EGP',
            'customer' => [
                'name' => $user->name ?? 'Unknown',
                'email' => $user->email ?? 'unknown@example.com',
                'phoneCountryCode' => '+20',
                'phoneNumber' => $user->phone ?? '01111111111',
            ],
            'eInvoiceDetails' => [
                'extraChargesType' => 'Amount',
                'invoiceDiscountType' => 'Amount',
                // 'subtotal' => $amount,
                // 'grandTotal' => $amount,
            ],
            "callbackurl"=> 'https://backend.ofx-qrcode.com/api/payment/callback', // Add this line for the redirection URL
        ];




        $response = Http::withBasicAuth('c940b85f-c8f7-4229-a853-7c44d4a8db2f', '225235e9-336a-45aa-91b4-ff9cfd31be50')
        ->post('https://api.merchant.geidea.net/payment-intent/api/v1/direct/eInvoice', $payload);

        \Log::info('Geidea Payment Session Request:', ['payload' => $payload]);

        // Handle the response
        if ($response->successful()) {
            return response()->json([
                'message' => 'Payment link created successfully.',
                'data' => $response->json(),
            ], 200);
        } else {
            return response()->json([
                'message' => 'Failed to create payment link.',
                'error' => $response->json(),
            ], $response->status());
        }
    }


    public function handleCallbacks(Request $request)
    {


        $response = $request->all();
        \Storage::put('geidea_response.json', json_encode($response));

    }

  



        public function handleCallback(Request $request)
        {
            try {

                $payload = $request->all();
            //    dd($payload);

                \Log::info('Webhook Received:', $payload);

                \Storage::put('geidea_response.json', json_encode($payload, JSON_PRETTY_PRINT));

                return response()->json(['message' => 'Webhook received successfully'], 200);
            } catch (\Exception $e) {
                \Log::error('Webhook handling failed:', [
                    'error' => $e->getMessage(),
                ]);

                return response()->json(['message' => 'Webhook handling failed'], 500);
            }
        }
}


