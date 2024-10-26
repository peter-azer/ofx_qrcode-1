<?php

namespace App\Services;

use GuzzleHttp\Client;

class GeideaPaymentService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => env('GEIDEA_API_BASE_URL'),
            'headers' => [
                'Authorization' => 'Bearer ' . env('GEIDEA_API_KEY'),
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function createPayment(float $amount, string $currency = 'SAR')
    {
        $response = $this->client->post('/v1/payments', [
            'json' => [
                'merchantId' => env('GEIDEA_MERCHANT_ID'),
                'amount' => $amount,
                'currency' => $currency,
                'callbackUrl' => route('geidea.callback'), // URL for callback after payment
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
