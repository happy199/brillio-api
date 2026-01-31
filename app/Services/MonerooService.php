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

    /**
     * Récupérer les méthodes de paiement disponibles pour les payouts
     *
     * @return array
     */
    public function getPayoutMethods(): array
    {
        try {
            // L'endpoint est /utils/payment/methods et ne semble pas préfixé par /v1 selon l'API
            $baseUrl = str_replace('/v1', '', $this->apiUrl);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Accept' => 'application/json'
            ])->get($baseUrl . '/utils/payment/methods');

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Moneroo: Payout methods retrieved', ['data' => $data]);
                return $data;
            }

            Log::error('Moneroo: Failed to get payout methods', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [];
        } catch (Exception $e) {
            Log::error('Moneroo: Exception during getPayoutMethods', [
                'message' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Créer un payout (retrait) pour un mentor
     *
     * @param float $amount Montant du payout en FCFA
     * @param string $phone Numéro de téléphone du bénéficiaire
     * @param string $method Méthode de paiement (ex: mtn_bj, moov_bj)
     * @return array
     */
    public function createPayout(float $amount, string $phone, string $method, string $country, string $dialCode, array $customer = []): array
    {
        try {
            // Split name if accessible from authenticated user context, otherwise defaults
            $user = auth()->user();
            $firstName = $customer['first_name'] ?? ($user ? $this->splitName($user->name)['first_name'] : 'Beneficiary');
            $lastName = $customer['last_name'] ?? ($user ? $this->splitName($user->name)['last_name'] : 'Name');
            $email = $customer['email'] ?? ($user ? $user->email : 'no-email@brillio.africa');

            // Normaliser le numéro de téléphone (enlever tout sauf les chiffres)
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

            // Le dial_code est déjà fourni depuis le frontend (ex: "+229")
            // On ajoute l'indicatif s'il n'est pas déjà présent
            $dialCodeDigits = preg_replace('/[^0-9]/', '', $dialCode); // "229"
            if (!str_starts_with($cleanPhone, $dialCodeDigits)) {
                $cleanPhone = $dialCodeDigits . $cleanPhone;
            }

            // L'API veut un entier
            $formattedPhone = (int) $cleanPhone;

            $payload = [
                'amount' => (int) $amount,
                'currency' => $this->currency,
                'description' => 'Retrait de credits Brillio',
                'customer' => [
                    'email' => $email,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => $formattedPhone,
                    'country' => $country, // Code pays (ex: "BJ") - CRITIQUE pour éviter l'erreur commonName
                ],
                'method' => $method,
                'recipient' => [
                    'msisdn' => $formattedPhone
                ],
            ];

            Log::info('Moneroo: Creating payout', $payload);

            // POST /v1/payouts/initialize
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post($this->apiUrl . '/payouts/initialize', $payload);

            $data = $response->json();

            Log::info('Moneroo: Payout response', [
                'status' => $response->status(),
                'data' => $data
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $data
                ];
            }

            return [
                'success' => false,
                'message' => $data['message'] ?? 'Payout failed',
                'errors' => $data['errors'] ?? []
            ];
        } catch (Exception $e) {
            Log::error('Moneroo: Exception during createPayout', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Vérifier le statut d'un payout
     *
     * @param string $payoutId ID du payout Moneroo
     * @return array
     */
    public function getPayoutStatus(string $payoutId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Accept' => 'application/json'
            ])->get($this->apiUrl . '/payouts/' . $payoutId);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to get payout status'
            ];
        } catch (Exception $e) {
            Log::error('Moneroo: Exception during getPayoutStatus', [
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Calculer les frais de retrait
     *
     * @param float $amount Montant du retrait en FCFA
     * @return float
     */
    public function calculateFee(float $amount): float
    {
        $feeRate = config('payout.fee_rate', 0.02); // 2% par défaut
        $minFee = config('payout.min_fee', 100); // 100 FCFA minimum

        $fee = max($amount * $feeRate, $minFee);
        return round($fee, 2);
    }
}
