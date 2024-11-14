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
        $this->publicKey = env('GEIDEA_PUBLIC_KEY');
        $this->apiPassword = env('GEIDEA_API_PASSWORD');
    }

    public function initiatePayment($amount, $currency, $orderId, $callbackUrl)
    {
        $response = Http::withBasicAuth($this->publicKey, $this->apiPassword)
            ->post("{$this->baseUrl}/v2", [
                'amount' => $amount,
                'currency' => $currency,
                'orderId' => $orderId,
                'callbackUrl' => $callbackUrl,
            ]);

        return $response->json();
    }
}
