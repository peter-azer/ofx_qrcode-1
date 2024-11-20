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
        $this->baseUrl = 'https://api.merchant.geidea.net/payment-intent/api/v1/direct/session'; // Correct URL for payment session
        $this->publicKey = env('GEIDEA_PUBLIC_KEY');
        $this->apiPassword = env('GEIDEA_API_PASSWORD');

    }

    /**
     * Generate the signature using the provided data.
     */
    public function generateSignature($merchantPublicKey, $amount, $orderCurrency, $orderMerchantReferenceId,$apiPassword, $timestamp)
    {

        $amountStr = number_format($amount, 2, '.', '');

        $data = "{$merchantPublicKey}{$amountStr}{$orderCurrency}{$orderMerchantReferenceId}{$timestamp}";

        // Generate HMAC SHA256 hash and encode it in Base64
        $hash = hash_hmac('sha256', $data,  true);
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
    $signature = $this->generateSignature($this->publicKey, $formattedAmount, $currency, $merchantReferenceId,$this->apiPassword, $timestamp);

    // Prepare the payload for the API request
    $payload = [
        'amount' => $formattedAmount,
        'currency' => $currency,
        'timestamp' => $timestamp,
        'merchantReferenceId' => $merchantReferenceId,
        // 'signature' =>$signature,
        'callbackUrl' => $callbackUrl,
    ];



    Log::info(' Request:', ['publickey' => $this->publicKey]);

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

