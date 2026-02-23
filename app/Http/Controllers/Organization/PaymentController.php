<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\CreditPack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $monerooService;

    protected $walletService;

    public function __construct(\App\Services\MonerooService $monerooService, \App\Services\WalletService $walletService)
    {
        $this->monerooService = $monerooService;
        $this->walletService = $walletService;
    }

    /**
     * Handle payment callback from Moneroo.
     */
    public function callback(Request $request)
    {
        // Moneroo typically sends 'paymentId' and 'paymentStatus' in redirect URL
        $transactionId = $request->query('paymentId') ?? $request->query('payment_id') ?? $request->query('transaction_id');
        $status = $request->query('paymentStatus') ?? $request->query('status');

        Log::info('Moneroo organization callback', [
            'paymentId' => $transactionId,
            'status' => $status,
            'query' => $request->query(),
        ]);

        if (! $transactionId) {
            return redirect()->route('organization.dashboard')->with('error', 'Référence de transaction manquante.');
        }

        // Find existing transaction record
        $localTransaction = \App\Models\MonerooTransaction::where('moneroo_transaction_id', $transactionId)->first();

        // If transaction already completed (via webhook), redirect based on type
        if ($localTransaction && $localTransaction->status === 'completed') {
            $metadata = $localTransaction->metadata ?? [];
            $reference = $metadata['reference'] ?? null;

            if ($reference && str_starts_with($reference, 'SUB-')) {
                return redirect()->route('organization.subscriptions.index')->with('success', 'Abonnement activé avec succès !');
            } elseif ($reference && str_starts_with($reference, 'PACK-')) {
                return redirect()->route('organization.wallet.index')->with('success', 'Pack de crédits ajouté avec succès !');
            }

            return redirect()->route('organization.dashboard')->with('success', 'Paiement déjà traité avec succès.');
        }

        try {
            // Verify transaction status from API
            $paymentData = $this->monerooService->verifyPayment($transactionId);

            if (! $paymentData || ! in_array($paymentData['status'] ?? '', ['completed', 'success'])) {
                return redirect()->route('organization.dashboard')->with('error', 'Le paiement a échoué ou n\'a pas pu être vérifié.');
            }
        } catch (\Exception $e) {
            Log::error('Moneroo Verification Error in Callback: '.$e->getMessage());

            return redirect()->route('organization.dashboard')->with('error', 'Erreur lors de la vérification du paiement.');
        }

        // Process based on metadata
        $metadata = $paymentData['metadata'] ?? [];
        $reference = $metadata['reference'] ?? ($paymentData['reference'] ?? null);

        if (! $reference) {
            Log::error('Payment processed but missing reference', ['payment' => $paymentData]);

            return redirect()->route('organization.dashboard')->with('error', 'Paiement réussi mais référence interne introuvable. Contactez le support.');
        }

        // Reference format: SUB-{orgId}-{timestamp} or PACK-{orgId}-{timestamp}-{packId}
        // Actually, I should probably store strict data in DB beforehand (PaymentIntent) for robustness,
        // but for this task I'll encode needed info in metadata or reference.

        // Let's rely on parsing the reference or ensuring I pass structured metadata.
        // In initiatePayment I passed 'reference'.

        if (str_starts_with($reference, 'SUB-')) {
            return $this->handleSubscriptionPayment($paymentData, $reference);
        } elseif (str_starts_with($reference, 'PACK-')) {
            return $this->handleCreditPackPayment($paymentData, $reference);
        }

        return redirect()->route('organization.dashboard')->with('warning', 'Type de paiement inconnu.');
    }

    protected function handleSubscriptionPayment($paymentData, $reference)
    {
        // Find MonerooTransaction to get metadata
        $monerooTransactionId = $paymentData['id'] ?? null;
        $localTransaction = \App\Models\MonerooTransaction::where('moneroo_transaction_id', $monerooTransactionId)->first();

        if (! $localTransaction) {
            Log::error('Subscription callback: Local transaction not found', ['moneroo_id' => $monerooTransactionId]);

            return redirect()->route('organization.dashboard')->with('error', 'Transaction introuvable.');
        }

        $metadata = $localTransaction->metadata ?? [];
        $planId = $metadata['plan_id'] ?? null;
        $billingCycle = $metadata['billing_cycle'] ?? 'monthly';

        // Extract Org ID from reference (SUB-{orgId}-{timestamp})
        $parts = explode('-', $reference);
        $orgId = $parts[1] ?? null;

        $organization = \App\Models\Organization::find($orgId);
        $plan = CreditPack::find($planId); // It's a Subscription type credit pack

        if ($organization && $plan) {
            $organization->update([
                'subscription_plan' => $plan->target_plan,
                'subscription_expires_at' => ($billingCycle === 'yearly') ? now()->addYear() : now()->addMonth(),
                'auto_renew' => true,
            ]);

            // Mark moneroo transaction as completed
            if ($localTransaction->status !== 'completed') {
                $localTransaction->markAsCompleted();
            }

            return redirect()->route('organization.subscriptions.index')->with('success', 'Abonnement activé avec succès !');
        }

        return redirect()->route('organization.dashboard')->with('error', 'Organisation ou plan introuvable.');
    }

    protected function handleCreditPackPayment($paymentData, $reference)
    {
        // PACK-{orgId}-{packId}-{timestamp}
        $parts = explode('-', $reference);
        if (count($parts) < 4) {
            return redirect()->route('organization.wallet.index')->with('error', 'Référence pack invalide.');
        }

        $orgId = $parts[1];
        $packId = $parts[2];

        $organization = \App\Models\Organization::find($orgId);
        $pack = CreditPack::find($packId);

        if ($organization && $pack) {
            // Find MonerooTransaction to link it
            $monerooTransactionId = $paymentData['id'] ?? null;
            $localTransaction = \App\Models\MonerooTransaction::where('moneroo_transaction_id', $monerooTransactionId)->first();

            // Use WalletService to credit organization
            $this->walletService->addCredits(
                $organization,
                $pack->credits,
                'purchase',
                "Achat de {$pack->credits} crédits via Moneroo",
                $localTransaction
            );

            // Mark moneroo transaction as completed if found
            if ($localTransaction && $localTransaction->status !== 'completed') {
                $localTransaction->markAsCompleted();
            }

            return redirect()->route('organization.wallet.index')->with('success', "Pack de {$pack->credits} crédits ajouté avec succès !");
        }

        return redirect()->route('organization.wallet.index')->with('error', 'Pack introuvable.');
    }
}
