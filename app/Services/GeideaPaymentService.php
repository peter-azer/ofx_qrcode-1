<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;

use GuzzleHttp\Client;

class GeideaPaymentService
{


    protected $baseUrl;
    protected $publicKey;
    protected $apiPassword;

    public function __construct()
    {
        $this->baseUrl = 'https://api.merchant.geidea.net';
        $this->publicKey = config('services.geidea.public_key');
        $this->apiPassword = config('services.geidea.api_password');
    }

    public function initiatePayment($amount, $currency, $orderId, $callbackUrl)
    {
        $response = Http::withBasicAuth($this->publicKey, $this->apiPassword)
            ->post("{$this->baseUrl}/v2/checkout", [
                'amount' => $amount,
                'currency' => $currency,
                'orderId' => $orderId,
                'callbackUrl' => $callbackUrl,
            ]);

        return $response->json();
    }
}
