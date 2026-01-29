<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class MonerooService
{
    protected string $secretKey;
    protected bool $isSandbox;
    protected string $apiUrl;
    protected string $currency;

    public function __construct()
    {
        $this->secretKey = config('services.moneroo.secret_key');
        $this->isSandbox = config('services.moneroo.is_sandbox');
        $this->apiUrl = config('services.moneroo.api_url');
        $this->currency = config('services.moneroo.currency');
    }

    /**
     * Initialize a payment with Moneroo
     *
     * @param float $amount Amount in XOF
     * @param string $description Payment description
     * @param array $customer Customer information ['first_name', 'last_name', 'email', 'phone']
     * @param array $metadata Additional data to store
     * @param string $returnUrl URL to redirect after payment
     * @return array Payment data with checkout URL
     * @throws Exception
     */
    public function initializePayment(
        float $amount,
        string $description,
        array $customer,
        array $metadata = [],
        ?string $returnUrl = null
    ): array {
        try {
            $returnUrl = $returnUrl ?? route('payments.callback');

            $payload = [
                'amount' => (int) $amount, // Moneroo expects amount in smallest unit (e.g., cents)
                'currency' => $this->currency,
                'description' => $description,
                'customer' => [
                    'email' => $customer['email'] ?? null,
                    'first_name' => $customer['first_name'] ?? null,
                    'last_name' => $customer['last_name'] ?? null,
                    'phone' => $customer['phone'] ?? null,
                    'country' => $customer['country'] ?? 'BJ', // Bénin par défaut
                ],
                'return_url' => $returnUrl,
                'metadata' => $metadata,
            ];

            Log::info('Moneroo: Initializing payment', [
                'amount' => $amount,
                'currency' => $this->currency,
                'customer' => $customer['email'] ?? 'unknown',
                'country' => $payload['customer']['country'] ?? 'not set',
                'sandbox' => $this->isSandbox,
                'full_payload' => $payload,
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->apiUrl . '/payments/initialize', $payload);

            if (!$response->successful()) {
                Log::error('Moneroo: Payment initialization failed', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);
                throw new Exception('Failed to initialize payment: ' . $response->body());
            }

            $data = $response->json();

            Log::info('Moneroo: Payment initialized successfully', [
                'transaction_id' => $data['data']['id'] ?? null,
            ]);

            return $data['data'];
        } catch (Exception $e) {
            Log::error('Moneroo: Exception during payment initialization', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Verify a payment transaction
     *
     * @param string $transactionId Moneroo transaction ID
     * @return array Transaction data
     * @throws Exception
     */
    public function verifyPayment(string $transactionId): array
    {
        try {
            Log::info('Moneroo: Verifying payment', ['transaction_id' => $transactionId]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Accept' => 'application/json',
            ])->get($this->apiUrl . '/payments/' . $transactionId);

            if (!$response->successful()) {
                Log::error('Moneroo: Payment verification failed', [
                    'transaction_id' => $transactionId,
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);
                throw new Exception('Failed to verify payment: ' . $response->body());
            }

            $data = $response->json();

            Log::info('Moneroo: Payment verified', [
                'transaction_id' => $transactionId,
                'status' => $data['data']['status'] ?? 'unknown',
            ]);

            return $data['data'];
        } catch (Exception $e) {
            Log::error('Moneroo: Exception during payment verification', [
                'transaction_id' => $transactionId,
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Verify webhook signature from Moneroo
     *
     * @param string $payload Raw webhook payload
     * @param string $signature Signature from X-Moneroo-Signature header
     * @return bool True if signature is valid
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $webhookSecret = config('services.moneroo.webhook_secret');

        if (empty($webhookSecret)) {
            Log::warning('Moneroo: Webhook secret not configured');
            return false;
        }

        $computedSignature = hash_hmac('sha256', $payload, $webhookSecret);
        $isValid = hash_equals($computedSignature, $signature);

        if (!$isValid) {
            Log::warning('Moneroo: Invalid webhook signature', [
                'expected' => $computedSignature,
                'received' => $signature,
            ]);
        }

        return $isValid;
    }

    /**
     * Convert credits to XOF amount
     * 
     * @param int $credits Number of credits
     * @return float Amount in XOF
     */
    public function creditsToAmount(int $credits): float
    {
        // 1 crédit = 100 XOF (à ajuster selon votre tarification)
        return $credits * 100;
    }

    /**
     * Convert XOF amount to credits
     * 
     * @param float $amount Amount in XOF
     * @return int Number of credits
     */
    public function amountToCredits(float $amount): int
    {
        // 100 XOF = 1 crédit
        return (int) ($amount / 100);
    }

    /**
     * Split a full name into first and last name
     * 
     * @param string $fullName Full name (e.g., "Tidjani Happy")
     * @return array ['first_name' => string, 'last_name' => string]
     */
    public function splitName(string $fullName): array
    {
        $names = explode(' ', trim($fullName), 2);

        return [
            'first_name' => $names[0] ?? '',
            'last_name' => $names[1] ?? $names[0] ?? '', // Use first name as last if no space
        ];
    }
}
