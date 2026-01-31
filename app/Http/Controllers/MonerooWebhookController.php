<?php

namespace App\Http\Controllers;

use App\Models\MonerooTransaction;
use App\Models\PayoutRequest;
use App\Services\MonerooService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MonerooWebhookController extends Controller
{
    protected MonerooService $monerooService;
    protected WalletService $walletService;

    public function __construct(MonerooService $monerooService, WalletService $walletService)
    {
        $this->monerooService = $monerooService;
        $this->walletService = $walletService;
    }

    /**
     * Handle webhook notifications from Moneroo
     */
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Moneroo-Signature');

        Log::info('Moneroo webhook received', [
            'headers' => $request->headers->all(),
            'payload_length' => strlen($payload),
        ]);

        // Verify webhook signature
        if (!$this->monerooService->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Moneroo webhook signature verification failed');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $data = json_decode($payload, true);
        $event = $data['event'] ?? null;
        $paymentData = $data['data'] ?? null;

        if (!$event || !$paymentData) {
            Log::error('Moneroo webhook missing event or data');
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        Log::info('Moneroo webhook event', [
            'event' => $event,
            'transaction_id' => $paymentData['id'] ?? null,
        ]);

        // Handle payment success
        if ($event === 'payment.succeeded' || $event === 'payment.completed' || $event === 'payment.success') {
            return $this->handlePaymentSuccess($paymentData);
        }

        // Handle payment failure
        if ($event === 'payment.failed') {
            return $this->handlePaymentFailed($paymentData);
        }

        // Handle payment cancelled
        if ($event === 'payment.cancelled') {
            return $this->handlePaymentCancelled($paymentData);
        }

        // Handle payout success
        if ($event === 'payout.succeeded' || $event === 'payout.completed') {
            return $this->handlePayoutSuccess($paymentData);
        }

        // Handle payout failure
        if ($event === 'payout.failed') {
            return $this->handlePayoutFailed($paymentData);
        }

        Log::info('Moneroo webhook event not handled', ['event' => $event]);
        return response()->json(['message' => 'Event received'], 200);
    }

    /**
     * Handle successful payment
     */
    protected function handlePaymentSuccess(array $paymentData): \Illuminate\Http\JsonResponse
    {
        $monerooTransactionId = $paymentData['id'];
        $transaction = MonerooTransaction::where('moneroo_transaction_id', $monerooTransactionId)->first();

        if (!$transaction) {
            Log::error('Moneroo transaction not found', ['moneroo_transaction_id' => $monerooTransactionId]);
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        if ($transaction->status === 'completed') {
            Log::info('Moneroo transaction already completed', ['transaction_id' => $transaction->id]);
            return response()->json(['message' => 'Already processed'], 200);
        }

        try {
            // Get user
            $user = $transaction->user;

            if (!$user) {
                Log::error('User not found for transaction', ['transaction_id' => $transaction->id]);
                return response()->json(['error' => 'User not found'], 404);
            }

            // Add credits to user wallet
            $this->walletService->addCredits(
                $user,
                $transaction->credits_amount,
                'purchase',
                "Achat de {$transaction->credits_amount} crédits via Moneroo",
                $transaction
            );

            // Mark transaction as completed
            $transaction->markAsCompleted();

            Log::info('Moneroo payment processed successfully', [
                'transaction_id' => $transaction->id,
                'user_id' => $user->id,
                'credits' => $transaction->credits_amount,
            ]);

            return response()->json(['message' => 'Payment processed'], 200);

        } catch (\Exception $e) {
            Log::error('Error processing Moneroo payment', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    /**
     * Handle failed payment
     */
    protected function handlePaymentFailed(array $paymentData): \Illuminate\Http\JsonResponse
    {
        $monerooTransactionId = $paymentData['id'];
        $transaction = MonerooTransaction::where('moneroo_transaction_id', $monerooTransactionId)->first();

        if ($transaction) {
            $transaction->markAsFailed();
            Log::info('Moneroo payment marked as failed', ['transaction_id' => $transaction->id]);
        }

        return response()->json(['message' => 'Payment failed processed'], 200);
    }

    /**
     * Handle cancelled payment
     */
    protected function handlePaymentCancelled(array $paymentData): \Illuminate\Http\JsonResponse
    {
        $monerooTransactionId = $paymentData['id'];
        $transaction = MonerooTransaction::where('moneroo_transaction_id', $monerooTransactionId)->first();

        if ($transaction && $transaction->status === 'pending') {
            $transaction->update(['status' => 'cancelled']);
            Log::info('Moneroo payment marked as cancelled', ['transaction_id' => $transaction->id]);
        }

        return response()->json(['message' => 'Payment cancellation processed'], 200);
    }

    /**
     * Handle successful payout
     */
    protected function handlePayoutSuccess(array $payoutData): \Illuminate\Http\JsonResponse
    {
        $monerooPayoutId = $payoutData['id'];
        $payoutRequest = PayoutRequest::where('moneroo_payout_id', $monerooPayoutId)->first();

        if (!$payoutRequest) {
            Log::error('Moneroo payout not found', ['moneroo_payout_id' => $monerooPayoutId]);
            return response()->json(['error' => 'Payout not found'], 404);
        }

        if ($payoutRequest->status === PayoutRequest::STATUS_COMPLETED) {
            Log::info('Moneroo payout already completed', ['payout_id' => $payoutRequest->id]);
            return response()->json(['message' => 'Already processed'], 200);
        }

        try {
            // Mark payout as completed
            $payoutRequest->update([
                'status' => PayoutRequest::STATUS_COMPLETED,
                'completed_at' => now()
            ]);

            // Update total withdrawn for mentor
            $payoutRequest->mentorProfile->increment('total_withdrawn', $payoutRequest->amount);

            Log::info('Moneroo payout completed successfully', [
                'payout_id' => $payoutRequest->id,
                'moneroo_payout_id' => $monerooPayoutId,
                'amount' => $payoutRequest->amount
            ]);

            return response()->json(['message' => 'Payout completed'], 200);

        } catch (\Exception $e) {
            Log::error('Error processing Moneroo payout success', [
                'payout_id' => $payoutRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    /**
     * Handle failed payout
     */
    protected function handlePayoutFailed(array $payoutData): \Illuminate\Http\JsonResponse
    {
        $monerooPayoutId = $payoutData['id'];
        $payoutRequest = PayoutRequest::where('moneroo_payout_id', $monerooPayoutId)->first();

        if (!$payoutRequest) {
            Log::error('Moneroo payout not found for failure', ['moneroo_payout_id' => $monerooPayoutId]);
            return response()->json(['error' => 'Payout not found'], 404);
        }

        if ($payoutRequest->status === PayoutRequest::STATUS_FAILED) {
            Log::info('Moneroo payout already failed', ['payout_id' => $payoutRequest->id]);
            return response()->json(['message' => 'Already processed'], 200);
        }

        try {
            // Mark payout as failed
            $errorMessage = $payoutData['status'] ?? 'Gateway error occurred';
            $payoutRequest->update([
                'status' => PayoutRequest::STATUS_FAILED,
                'error_message' => $errorMessage
            ]);

            // CRITICAL: Refund the balance to mentor
            $payoutRequest->mentorProfile->increment('available_balance', $payoutRequest->amount);

            Log::info('Moneroo payout failed and balance refunded', [
                'payout_id' => $payoutRequest->id,
                'moneroo_payout_id' => $monerooPayoutId,
                'amount_refunded' => $payoutRequest->amount
            ]);

            return response()->json(['message' => 'Payout failure processed'], 200);

        } catch (\Exception $e) {
            Log::error('Error processing Moneroo payout failure', [
                'payout_id' => $payoutRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }
}
