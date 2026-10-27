<?php

namespace App\Http\Controllers;

use App\Mail\Billing\AdminPaymentNotificationMail;
use App\Mail\Billing\PaymentReceiptMail;
use App\Models\CreditPack;
use App\Models\MonerooTransaction;
use App\Models\Organization;
use App\Models\PayoutRequest;
use App\Services\MentorshipNotificationService;
use App\Services\MonerooService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MonerooWebhookController extends Controller
{
    protected MonerooService $monerooService;

    protected WalletService $walletService;

    protected MentorshipNotificationService $notificationService;

    public function __construct(MonerooService $monerooService, WalletService $walletService, MentorshipNotificationService $notificationService)
    {
        $this->monerooService = $monerooService;
        $this->walletService = $walletService;
        $this->notificationService = $notificationService;
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

        // Reject immediately if the signature header is missing
        if (! $signature) {
            Log::warning('Moneroo webhook: Missing X-Moneroo-Signature header');

            return response()->json(['error' => 'Missing signature'], 401);
        }

        // Verify webhook signature
        if (! $this->monerooService->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Moneroo webhook signature verification failed');

            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $data = json_decode($payload, true);
        $event = $data['event'] ?? null;
        $paymentData = $data['data'] ?? null;

        if (! $event || ! $paymentData) {
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
        if ($event === 'payout.succeeded' || $event === 'payout.completed' || $event === 'payout.success') {
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
    protected function handlePaymentSuccess(array $paymentData): JsonResponse
    {
        $monerooTransactionId = $paymentData['id'] ?? '';
        try {
            return DB::transaction(function () use ($monerooTransactionId) {
                return $this->processPaymentSuccessTransaction($monerooTransactionId);
            });
        } catch (\Exception $e) {
            Log::error('Error processing Moneroo payment', [
                'moneroo_transaction_id' => $monerooTransactionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    /**
     * Traite la transaction Moneroo avec verrouillage pessimiste
     */
    private function processPaymentSuccessTransaction(string $monerooTransactionId): JsonResponse
    {
        $transaction = MonerooTransaction::where('moneroo_transaction_id', $monerooTransactionId)
            ->lockForUpdate()
            ->first();

        if (! $transaction) {
            Log::error('Moneroo transaction not found', ['moneroo_transaction_id' => $monerooTransactionId]);

            return response()->json(['error' => 'Transaction not found'], 404);
        }

        if ($transaction->status === 'completed') {
            Log::info('Moneroo transaction already completed', ['transaction_id' => $transaction->id]);

            return response()->json(['message' => 'Already processed'], 200);
        }

        $user = $transaction->user;
        if (! $user) {
            Log::error('User not found for transaction', ['transaction_id' => $transaction->id]);

            return response()->json(['error' => 'User not found'], 404);
        }

        $isOrgTransaction = ($transaction->metadata['user_type'] ?? '') === 'organization';
        $entity = ($isOrgTransaction && $user->organization) ? $user->organization : $user;

        $reference = $transaction->metadata['reference'] ?? '';
        if (str_starts_with($reference, 'SUB-')) {
            $this->handleSubscriptionActivation($transaction, $entity);
        }

        if ($transaction->credits_amount > 0) {
            $this->walletService->addCredits(
                $entity,
                $transaction->credits_amount,
                'purchase',
                "Achat de {$transaction->credits_amount} crédits via Moneroo",
                $transaction
            );
        }

        $transaction->markAsCompleted();

        $this->sendPaymentSuccessNotifications($transaction, $entity, $user);

        return response()->json(['message' => 'Payment processed'], 200);
    }

    /**
     * Envoie les notifications et factures suite à un paiement réussi
     */
    private function sendPaymentSuccessNotifications(MonerooTransaction $transaction, $entity, User $user): void
    {
        $this->notificationService->sendCreditRecharge($user, $transaction->credits_amount);

        try {
            $recipientEmail = $entity->email ?: ($entity->contact_email ?? $user->email);
            Mail::to($recipientEmail)->send(new PaymentReceiptMail($transaction, $entity));
        } catch (\Exception $e) {
            Log::error('Erreur envoi facture client', ['error' => $e->getMessage()]);
        }

        try {
            Mail::to('contact@brillio.africa')->send(new AdminPaymentNotificationMail($transaction, $entity));
        } catch (\Exception $e) {
            Log::error('Erreur envoi notification admin', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Handle failed payment
     */
    protected function handlePaymentFailed(array $paymentData): JsonResponse
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
    protected function handlePaymentCancelled(array $paymentData): JsonResponse
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
    protected function handlePayoutSuccess(array $payoutData): JsonResponse
    {
        $monerooPayoutId = $payoutData['id'];
        $payoutRequest = PayoutRequest::where('moneroo_payout_id', $monerooPayoutId)->first();

        if (! $payoutRequest) {
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
                'completed_at' => now(),
            ]);

            // Update total withdrawn for mentor
            $payoutRequest->mentorProfile->increment('total_withdrawn', $payoutRequest->amount);

            Log::info('Moneroo payout completed successfully', [
                'payout_id' => $payoutRequest->id,
                'moneroo_payout_id' => $monerooPayoutId,
                'amount' => $payoutRequest->amount,
            ]);

            // Notification email
            $this->notificationService->sendPayoutProcessed($payoutRequest);

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
    protected function handlePayoutFailed(array $payoutData): JsonResponse
    {
        $monerooPayoutId = $payoutData['id'];
        $payoutRequest = PayoutRequest::where('moneroo_payout_id', $monerooPayoutId)->first();

        if (! $payoutRequest) {
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
                'error_message' => $errorMessage,
            ]);

            // CRITICAL: Refund the balance to mentor
            $payoutRequest->mentorProfile->increment('available_balance', $payoutRequest->amount);

            // Refund credits
            $creditPrice = $this->walletService->getCreditPrice('mentor');
            $creditsRefund = intval($payoutRequest->amount / $creditPrice);

            $this->walletService->addCredits(
                $payoutRequest->mentorProfile->user,
                $creditsRefund,
                'refund',
                'Remboursement retrait échoué (Webhook)',
                $payoutRequest
            );

            Log::info('Moneroo payout failed and balance refunded', [
                'payout_id' => $payoutRequest->id,
                'moneroo_payout_id' => $monerooPayoutId,
                'amount_refunded' => $payoutRequest->amount,
                'credits_refunded' => $creditsRefund,
            ]);

            // Notification email
            $this->notificationService->sendPayoutProcessed($payoutRequest);

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

    /**
     * Activate organization subscription based on transaction metadata.
     */
    protected function handleSubscriptionActivation($transaction, $entity): void
    {
        if (! ($entity instanceof Organization)) {
            Log::warning('Subscription payment received but entity is not an organization', [
                'transaction_id' => $transaction->id,
                'entity_type' => get_class($entity),
            ]);

            return;
        }

        $metadata = $transaction->metadata;
        $planId = $metadata['plan_id'] ?? null;
        $billingCycle = $metadata['billing_cycle'] ?? 'monthly';

        if (! $planId) {
            Log::error('Subscription plan ID missing in transaction metadata', ['transaction_id' => $transaction->id]);

            return;
        }

        $plan = CreditPack::find($planId);
        if (! $plan) {
            Log::error('Subscription plan not found', ['plan_id' => $planId, 'transaction_id' => $transaction->id]);

            return;
        }

        $entity->update([
            'subscription_plan' => $plan->target_plan,
            'subscription_expires_at' => ($billingCycle === 'yearly') ? now()->addYear() : now()->addMonth(),
            'auto_renew' => true,
        ]);

        Log::info('Organization subscription activated via webhook', [
            'org_id' => $entity->id,
            'plan_name' => $plan->name,
            'plan_target' => $plan->target_plan,
        ]);
    }
}
