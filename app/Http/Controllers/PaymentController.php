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

    $amount =' 100';
    $currency = 'EGP';




    $callbackUrl = route('payment.callback');

    $response = $this->geideaService->createSession($amount, $currency,  'https://127.0.0.1:8000/payment/callback');

    if (isset($response['session']['id'])) {
        return response()->json([
            'status' => 'success',
            'sessionId' => $response['session']['id'],
            'redirectUrl' => $response['session']['redirectUrl']
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
            "callbackurl"=> "https://backend.ofx-qrcode.com/api/payment/callback", // Add this line for the redirection URL
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


    public function handleCallback(Request $request)
    {

      \Log::info('Payment Callback Received:', $request->all());

        $orderAmount = $request->input('OrderAmount');
        $orderCurrency = $request->input('OrderCurrency');
        $orderId = $request->input('Orderid');
        $status = $request->input('Status');
        $merchantReferenceId = $request->input('MerchantRefrenceId');

        // Your Merchant API Pasword (must be stored securely)
        $merchantApiPassword = env('MERCHANT_API_PASSWORD');


        // Step 5: Verify payment status and other parameters
        if ($status === 'Success' && $request->input('responseCode') === '000' && $request->input('detailedResponseCode') === '000') {
            // Update order status to 'Paid'
            \Log::info('Payment Successful', ['order_id' => $orderId]);

            // Your logic to mark the order as paid or trigger any further actions
        } else {
           \Log::warning('Payment Failed or Invalid Status', $request->all());
        }

        return response()->json(['message' => 'Callback processed successfully'], 200);
    }
}


