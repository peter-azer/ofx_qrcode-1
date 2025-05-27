<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GeideaPaymentService
{
    protected $baseUrl;
    protected $publicKey;
    protected $apiPassword;
    protected $secretKey;

    public function __construct()
    {
        $this->baseUrl = 'https://api.merchant.geidea.net/payment-intent/api/v2/direct/session'; // Correct URL for payment session
        $this->publicKey = 'c940b85f-c8f7-4229-a853-7c44d4a8db2f'; //test
        $this->apiPassword = '9cde412c-74e7-47ea-96e9-66f847bf4198';
        // $this->publicKey = 'b42f3fd5-782e-4d86-afda-0efbed7a1711'; //live
        // $this->apiPassword ='81e08fa6-00eb-4b52-b2fc-89071ebed43d';
    }

    /**
     * Generate the signature using the provided data.
     */
    function generateSignature($merchantPublicKey, $orderAmount, $orderCurrency, $orderMerchantReferenceId, $apiPassword, $timestamp)
    {
        // $apiPassword = '9cde412c-74e7-47ea-96e9-66f847bf4198'; //test
        // $apiPassword = '81e08fa6-00eb-4b52-b2fc-89071ebed43d'; //live
        // dd($merchantPublicKey, $orderAmount, $orderCurrency, $orderMerchantReferenceId, $apiPassword, $timestamp);
        $amountStr = number_format($orderAmount, 2, '.', '');
        $data = "{$merchantPublicKey}{$amountStr}{$orderCurrency}{$orderMerchantReferenceId}{$timestamp}";
        $hash = hash_hmac('sha256', $data, $apiPassword, true);
        return base64_encode($hash);
    }

    /**

     *
     * @param double $amount -
     * @param string $orderCurrency
     * @param string $orderMerchantReferenceId -
     *
     *
     */

    public function createSession($amount, $currency, $callbackUrl)
    {

        $formattedAmount = number_format($amount, 2, '.', '');


        $merchantReferenceId = uniqid();
        $timestamp = date('Y-m-d\TH:i:s\Z');
        $signature = $this->generateSignature('b42f3fd5-782e-4d86-afda-0efbed7a1711', $formattedAmount, $currency, $merchantReferenceId, '924eedea-4be1-4ef5-8324-e42e23255337', $timestamp);

        // Prepare the payload for the API request
        $payload = [
            'amount' => $formattedAmount,
            'currency' => $currency,
            'timestamp' => $timestamp,
            'merchantReferenceId' => $merchantReferenceId,
            'signature' => $signature,
            'callbackUrl' => $callbackUrl,
        ];



        // Log::info(' Request:', ['Request' => $payload]);

        try {


            $response = Http::withBasicAuth($this->publicKey, $this->apiPassword)
                ->post($this->baseUrl, $payload);

            if ($response->successful()) {
                Log::info('Geidea Payment Session Response:', ['response' => $response->json()]);
                return $response->json();
            } else {

                Log::error('Geidea API Error: ', ['error' => $response->body()]);
                return ['error' => 'Failed to initiate payment session', 'details' => $response->body()];
            }
        } catch (\Exception $e) {

            Log::error('Geidea API Exception: ', ['exception' => $e->getMessage()]);
            return ['error' => 'Error initiating payment session', 'exception' => $e->getMessage()];
        }
    }
}
