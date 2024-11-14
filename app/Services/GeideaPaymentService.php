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
        $this->baseUrl = 'https://api.merchant.geidea.net/payment-intent/api/v2/direct/session'; // Correct URL for payment session
        $this->publicKey = env('GEIDEA_PUBLIC_KEY');
        $this->apiPassword = env('GEIDEA_API_PASSWORD');
        // $this->secretKey = env('GEIDEA_SECRET_KEY');
    }

    /**
     * Generate the signature using the provided data.
     */
    function generateSignature($merchantPublicKey, $amount, $currency, $merchantReferenceId, $apiPassword, $timestamp)
    {
        // Format the amount to two decimal places
        $amountStr = number_format($amount, 2, '.', '');

        // Concatenate the necessary fields to create the base string for the signature
        $data = "{$merchantPublicKey}{$amountStr}{$currency}{$merchantReferenceId}{$timestamp}";

        // Generate the signature using HMAC with SHA256 and the API password
        $hash = hash_hmac('sha256', $data, $apiPassword, true);

        // Return the base64 encoded hash as the signature
        return base64_encode($hash);
    }

    public function createSession($amount, $currency, $orderId, $callbackUrl)
    {
        // Generate the timestamp and merchant reference ID
        $timestamp = now()->toIso8601String();
        $merchantReferenceId = uniqid();

        // Generate the signature
        $signature = $this->generateSignature($this->publicKey, $amount, $currency, $merchantReferenceId, $this->apiPassword, $timestamp);

        // Prepare the payload for the API request
        $payload = [
            'amount' => number_format($amount, 2, '.', ''),  // Ensure amount is properly formatted as a string with 2 decimal places
            'currency' => $currency,
            'timestamp' => $timestamp,
            'merchantReferenceId' => $merchantReferenceId,
            'signature' => $signature,
            'callbackUrl' => $callbackUrl,
        ];

        // Add the orderId to the payload if it's provided
        if ($orderId) {
            $payload['orderId'] = $orderId;
        }

        // Log the request payload for debugging
        Log::info('Geidea Payment Session Request:', $payload);

        try {
            // Send the POST request to Geidea API with basic authentication
            $response = Http::withBasicAuth($this->publicKey, $this->apiPassword)
                ->post($this->baseUrl, $payload);

            // Check if the response was successful
            if ($response->successful()) {
                Log::info('Geidea Payment Session Response:', $response->json());
                return $response->json();
            } else {
                // Log the error response for debugging
                Log::error('Geidea API Error: ' . $response->body());
                return ['error' => 'Failed to initiate payment session', 'details' => $response->body()];
            }
        } catch (\Exception $e) {
            // Log any exceptions that occur during the API request
            Log::error('Geidea API Exception: ' . $e->getMessage());
            return ['error' => 'Error initiating payment session', 'exception' => $e->getMessage()];
        }
    }

}
