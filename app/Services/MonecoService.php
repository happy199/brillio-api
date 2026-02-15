<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MonecoService
{
    protected $baseUrl;
    protected $apiKey;
    protected $apiSecret;

    public function __construct()
    {
        $this->baseUrl = config('services.moneroo.api_url', env('MONEROO_API_URL', 'https://api.moneroo.io/v1'));
        $this->apiKey = config('services.moneroo.secret_key', env('MONEROO_SECRET_KEY'));
        $this->apiSecret = config('services.moneroo.webhook_secret', env('MONEROO_WEBHOOK_SECRET'));
    }

    /**
     * Initiate a payment request.
     *
     * @param float $amount
     * @param string $currency
     * @param string $description
     * @param string $reference
     * @param string $callbackUrl
     * @param string $returnUrl
     * @return array|null
     */
    public function initiatePayment($amount, $currency, $description, $reference, $callbackUrl, $returnUrl)
    {
        try {
            // This is a hypothetical implementation based on typical payment gateway patterns.
            // Replace with actual Moneco API endpoint and payload structure.
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/payments/initialize', [
                'amount' => $amount,
                'currency' => $currency ?? env('MONEROO_CURRENCY', 'XOF'),
                'description' => $description,
                'metadata' => [
                    'reference' => $reference,
                ],
                'return_url' => $returnUrl,
                // 'callback_url' => $callbackUrl, // If Moneroo uses webhooks primarily, return_url is for browser redirect
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Moneco Payment Initiation Failed: ' . $response->body());
            return null;

        }
        catch (\Exception $e) {
            Log::error('Moneco Service Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verify a payment transaction.
     *
     * @param string $transactionId
     * @return array|null
     */
    public function verifyPayment($transactionId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->baseUrl . '/payments/' . $transactionId . '/verify');

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Moneco Payment Verification Failed: ' . $response->body());
            return null;

        }
        catch (\Exception $e) {
            Log::error('Moneco Verification Error: ' . $e->getMessage());
            return null;
        }
    }
}