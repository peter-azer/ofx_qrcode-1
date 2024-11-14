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
        $this->baseUrl = 'https://api.merchant.geidea.net'; // Base URL for Geidea Egypt
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
        $amount = number_format((float)$amount, 2, '.', '');  // Format amount to 2 decimals

        // Generate the timestamp and unique merchant reference ID
        $timestamp = now()->toIso8601String();  // Use UTC time for consistency
        $merchantReferenceId = uniqid();  // Generate a unique merchant reference ID

        // Generate the signature for the session
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

        // Optionally include orderId if it's provided
        if ($orderId) {
            $payload['orderId'] = $orderId;
        }

        // Log the payload and signature for debugging
        Log::info('Geidea Payment Session Request:', $payload);
        Log::info('Generated Signature:', ['signature' => $signature]);

        // Send the request to Geidea’s API
        try {
            // Make the API request to Geidea to create a session
            $response = Http::withBasicAuth($this->publicKey, $this->apiPassword)
                ->post("{$this->baseUrl}/v2/session", $payload);

            // Check if the response is successful
            if ($response->successful()) {
                // Log and return the response if successful
                Log::info('Geidea Payment Session Response:', $response->json());
                return $response->json();
            } else {
                // Log and return an error if the response is not successful
                Log::error('Geidea API Error: ' . $response->body());
                return ['error' => 'Failed to initiate payment session', 'details' => $response->body()];
            }
        } catch (\Exception $e) {
            // Log any exceptions that occur during the API call
            Log::error('Geidea API Exception: ' . $e->getMessage());
            return ['error' => 'Error initiating payment session', 'exception' => $e->getMessage()];
        }
    }
}
