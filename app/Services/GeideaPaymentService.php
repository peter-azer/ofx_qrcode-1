<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeideaPaymentService
{
    protected $baseUrl;
    protected $publicKey;
    protected $apiPassword;
    protected $secretKey;

    public function __construct()
    {
        $this->baseUrl = 'https://api.merchant.geidea.net';
        $this->publicKey = env('GEIDEA_PUBLIC_KEY');
        $this->apiPassword = env('GEIDEA_API_PASSWORD');
        $this->secretKey = env('GEIDEA_SECRET_KEY');
    }

    /**
     * Generate the signature using the provided data.
     */
    private function generateSignature($amount, $currency, $timestamp, $merchantReferenceId)
    {
        // Concatenate the necessary fields to create the base string for signature
        $signatureBase = $this->publicKey . $amount . $currency . $timestamp . $merchantReferenceId;

        // Generate the signature using HMAC with SHA256 and the secret key
        return hash_hmac('sha256', $signatureBase, $this->secretKey);
    }

    /**
     * Initiate a payment session using Geidea API.
     */
    public function createSession($amount, $currency, $orderId, $callbackUrl)
    {
        // Ensure the amount has two decimal places
        $amount = number_format((float)$amount, 2, '.', '');


        $timestamp = now()->toIso8601String();
        $merchantReferenceId = uniqid();


        $signature = $this->generateSignature($amount, $currency, $timestamp, $merchantReferenceId);

        // Prepare the request payload
        $payload = [
            'amount' => $amount,
            'currency' => $currency,
            'timestamp' => $timestamp,
            'merchantReferenceId' => $merchantReferenceId,
            'signature' => $signature,
            'callbackUrl' => $callbackUrl,
        ];

        // Optionally include tokenId if using tokenized payments
        if ($orderId) {
            $payload['orderId'] = $orderId;
        }

        // Send the request to Geidea’s API
        try {
            $response = Http::withBasicAuth($this->publicKey, $this->apiPassword)
                ->post("{$this->baseUrl}/v2/session", $payload);

            // Handle the response
            if ($response->successful()) {
                // Return the response if successful
                return $response->json();
            } else {
                // Log and return an error if the response is not successful
                Log::error('Geidea API Error: ' . $response->body());
                return ['error' => 'Failed to initiate payment session'];
            }
        } catch (\Exception $e) {
            // Log any exceptions that occur during the API call
            Log::error('Geidea API Exception: ' . $e->getMessage());
            return ['error' => 'Error initiating payment session'];
        }
    }
}
