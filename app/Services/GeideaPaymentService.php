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
        $this->baseUrl = 'https://api.merchant.geidea.net/payment-intent/api/v2/direct'; // Correct URL for payment session
        $this->publicKey = env('GEIDEA_PUBLIC_KEY');
        $this->apiPassword = env('GEIDEA_API_PASSWORD');
        // $this->secretKey = env('GEIDEA_SECRET_KEY');
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
        // Ensure the amount has exactly two decimal places as a double
        $amount = number_format((double)$amount, 2, '.', ''); // This keeps it as "139.00" (string)


        // Generate the timestamp and unique merchant reference ID
        $timestamp = now()->toIso8601String();
        $merchantReferenceId = uniqid();

        // Generate the signature using the formatted amount
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

        if ($orderId) {
            $payload['orderId'] = $orderId;
        }

        Log::info('Geidea Payment Session Request:', $payload);

        try {
            $response = Http::withBasicAuth($this->publicKey, $this->apiPassword)
                ->post("{$this->baseUrl}/session", $payload);

            if ($response->successful()) {
                Log::info('Geidea Payment Session Response:', $response->json());
                return $response->json();
            } else {
                Log::error('Geidea API Error: ' . $response->body());
                return ['error' => 'Failed to initiate payment session', 'details' => $response->body()];
            }
        } catch (\Exception $e) {
            Log::error('Geidea API Exception: ' . $e->getMessage());
            return ['error' => 'Error initiating payment session', 'exception' => $e->getMessage()];
        }
    }

}
