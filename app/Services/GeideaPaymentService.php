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
        $this->publicKey = env('GEIDEA_PUBLIC_KEY');
        $this->apiPassword = env('GEIDEA_API_PASSWORD');
        // Optional: $this->secretKey = env('GEIDEA_SECRET_KEY'); // If required
    }

    /**
     * Generate the signature using the provided data.
     */
    public function generateSignature($merchantPublicKey, $amount, $orderCurrency, $orderMerchantReferenceId, $apiPassword, $timestamp)
    {
        // Format amount to 2 decimal places
        $amountStr = number_format($amount, 2, '.', '');
        // Concatenate data for signature
        $data = "{$merchantPublicKey}{$amountStr}{$orderCurrency}{$orderMerchantReferenceId}{$timestamp}";

        // Generate HMAC SHA256 hash and encode it in Base64
        $hash = hash_hmac('sha256', $data, $apiPassword, true);
        return base64_encode($hash);
    }

    /**
     * Create a payment session by sending data to the Geidea API.
     *
     * @param float $amount - The order amount.
     * @param string $orderCurrency - The currency of the order.
     * @param string $orderMerchantReferenceId - A unique reference for the order.
     *
     * @return array - The response from the API.
     */

     public function createSession($amount, $orderCurrency, $callbackUrl)
     {
         $merchantPublicKey = $this->publicKey;
         $apiPassword = $this->apiPassword;
         $timestamp = time();

         $orderMerchantReferenceId = Str::uuid()->toString();

         $signature = $this->generateSignature($merchantPublicKey, $amount, $orderCurrency, $orderMerchantReferenceId, $apiPassword, $timestamp);

         $data = [
             'merchantPublicKey' => $merchantPublicKey,
             'orderAmount' => number_format($amount, 2, '.', ''),
             'orderCurrency' => $orderCurrency,
             'orderMerchantReferenceId' => $orderMerchantReferenceId,
             'timestamp' => $timestamp,
             'signature' => $signature,
             'callbackUrl' => $callbackUrl,
         ];

         Log::info('Geidea Payment Session Request:', $data);

         $url = $this->baseUrl;

         $response = $this->sendPostRequest($url, $data);

         if (isset($response['error'])) {
             return ['error' => 'Session creation failed', 'details' => $response];
         }

         return $response;
     }

     protected function sendPostRequest($url, $data)
     {
         try {
             // Log the request data before sending to make sure everything is correct
             Log::info('Geidea Payment Session Request Data:', $data);

             $response = \Http::post($url, $data);

             // Check if the response is successful
             if ($response->successful()) {
                 Log::info('Geidea Payment Session Response:', $response->json());
                 return $response->json();
             } else {
                 // Log the response body for debugging
                 Log::error('Geidea API Error: ' . $response->body());

                 // Handle specific error response to help with debugging
                 $errorMessage = $response->json()['message'] ?? 'Request failed';
                 return [
                     'error' => $errorMessage,
                     'status' => $response->status(),
                     'message' => $response->body()
                 ];
             }
         } catch (\Exception $e) {
             // Log exception details
             Log::error('Geidea API Exception: ' . $e->getMessage());

             return ['error' => 'Exception occurred: ' . $e->getMessage()];
         }
     }

    }

    // public function __construct()
    // {
    //     $this->baseUrl = 'https://api.merchant.geidea.net/payment-intent/api/v2/direct/session'; // Correct URL for payment session
    //     $this->publicKey = env('GEIDEA_PUBLIC_KEY');
    //     $this->apiPassword = env('GEIDEA_API_PASSWORD');
    //     // $this->secretKey = env('GEIDEA_SECRET_KEY');
    // }

    /**
     * Generate the signature using the provided data.
     */
//     private function generateSignature($merchantPublicKey, $amount, $currency, $orderMerchantReferenceId, $timestamp)
// {

//     $amountStr = number_format($amount, 2, '.', '');  // Ensure 2 decimal places


//     $data = "{$merchantPublicKey}{$amountStr}{$currency}{$orderMerchantReferenceId}{$timestamp}";


//     $hash = hash_hmac('sha256', $data, '', true);

//     // Return the base64 encoded hash as the signature
//     return base64_encode($hash);
// }
// public function createSession($amount, $currency, $orderId, $callbackUrl)
// {
//     // Ensure the amount is a valid number (float)
//     // if (!is_numeric($amount) || $amount === null) {
//     //     Log::error('Invalid amount: ' . $amount);
//     //     return ['error' => 'Invalid amount provided'];
//     // }

//     // Format the amount to 2 decimal places and ensure it's a string
//     $formattedAmount = number_format($amount, 2, '.', '');

//     // Generate the timestamp and merchant reference ID
//     $timestamp = now()->toIso8601String();
//     $merchantReferenceId = uniqid();

//     // Generate the signature (without API password)
//     $signature = $this->generateSignature($this->publicKey, $formattedAmount, $currency, $merchantReferenceId, $timestamp);

//     // Prepare the payload for the API request
//     $payload = [
//         'amount' => "100.00",  // Ensure amount is properly formatted as a string with 2 decimal places
//         'currency' => $currency,
//         'timestamp' => $timestamp,
//         'merchantReferenceId' => $merchantReferenceId,
//         'signature' => $signature,
//         'callbackUrl' => $callbackUrl,
//     ];

//     // Add the orderId to the payload if it's provided
//     if ($orderId) {
//         $payload['orderId'] = $orderId;
//     }

//     // Log the request payload for debugging
//     Log::info('Geidea Payment Session Request:', $payload);

//     try {
//         // Send the POST request to Geidea API with basic authentication
//         $response = Http::withBasicAuth($this->publicKey, $this->apiPassword)
//             ->post($this->baseUrl, $payload);

//         // Check if the response was successful
//         if ($response->successful()) {
//             Log::info('Geidea Payment Session Response:', $response->json());
//             return $response->json();
//         } else {
//             // Log the error response for debugging
//             Log::error('Geidea API Error: ' . $response->body());
//             return ['error' => 'Failed to initiate payment session', 'details' => $response->body()];
//         }
//     } catch (\Exception $e) {
//         // Log any exceptions that occur during the API request
//         Log::error('Geidea API Exception: ' . $e->getMessage());
//         return ['error' => 'Error initiating payment session', 'exception' => $e->getMessage()];
//     }
// }

// function generateSignature($merchantPublicKey, $amount, $orderCurrency, $orderMerchantReferenceId, $apiPassword, $timestamp)
// {
//     $amountStr = number_format($amount, 2, '.', '');
//     $data = "{$merchantPublicKey}{$amountStr}{$orderCurrency}{$orderMerchantReferenceId}{$timestamp}";
//     $hash = hash_hmac('sha256', $data, $apiPassword, true);
//     return base64_encode($hash);
// }

// public function createSession($amount, $orderCurrency, $orderMerchantReferenceId)
// {
//     // Get the necessary data from the environment
//     $merchantPublicKey = $this->publicKey;
//     $apiPassword = $this->apiPassword;
//     $timestamp = time();  // You can adjust this timestamp format as needed

//     // Generate the signature
//     $signature = $this->generateSignature($merchantPublicKey, $amount, $orderCurrency, $orderMerchantReferenceId, $apiPassword, $timestamp);

//     // Prepare the request data
//     $data = [
//         'merchantPublicKey' => $merchantPublicKey,
//         'orderAmount' => number_format($amount, 2, '.', ''),
//         'orderCurrency' => $orderCurrency,
//         'orderMerchantReferenceId' => $orderMerchantReferenceId,
//         'timestamp' => $timestamp,
//         'signature' => $signature,
//     ];

//     // Set the endpoint URL
//     $url = 'https://api.ksamerchant.geidea.net/payment-intent/api/v2/direct/session';

//     // Send the request
//     $response = $this->sendPostRequest($url, $data);

//     // Return the response from the API
//     return $response;
// }

// /**
//  * Send a POST request with the given data.
//  */
// protected function sendPostRequest($url, $data)
// {
//     // Use Laravel's HTTP client to send the POST request
//     try {
//         $response = \Http::post($url, $data);

//         if ($response->successful()) {
//             return $response->json();  // Return the JSON response
//         } else {
//             // Handle the error or return a failed response
//             return ['error' => 'Request failed', 'status' => $response->status()];
//         }
//     } catch (\Exception $e) {
//         // Handle any exceptions that occur during the request
//         return ['error' => $e->getMessage()];
//     }
// }


// }
