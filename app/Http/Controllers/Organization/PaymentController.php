<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\CreditPack;
use App\Services\MonerooService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $monerooService;

    public function __construct(\App\Services\MonerooService $monerooService)
    {
        $this->monerooService = $monerooService;
    }

    /**
     * Handle payment callback from Moneroo.
     */
    public function callback(Request $request)
    {
        // Moneroo typically sends 'payment_id'
        $transactionId = $request->input('payment_id') ?? $request->input('transaction_id');

        if (!$transactionId) {
            return redirect()->route('organization.dashboard')->with('error', 'Référence de transaction manquante.');
        }

        try {
            // Verify transaction
            $paymentData = $this->monerooService->verifyPayment($transactionId);

            if (!$paymentData || !in_array($paymentData['status'] ?? '', ['completed', 'success'])) {
                return redirect()->route('organization.dashboard')->with('error', 'Le paiement a échoué ou n\'a pas pu être vérifié.');
            }
        }
        catch (\Exception $e) {
            Log::error('Moneroo Verification Error in Callback: ' . $e->getMessage());
            return redirect()->route('organization.dashboard')->with('error', 'Erreur lors de la vérification du paiement.');
        }

        // Process based on metadata
        $metadata = $paymentData['metadata'] ?? [];
        $reference = $metadata['reference'] ?? ($paymentData['reference'] ?? null);

        if (!$reference) {
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
        }
        elseif (str_starts_with($reference, 'PACK-')) {
            return $this->handleCreditPackPayment($paymentData, $reference);
        }

        return redirect()->route('organization.dashboard')->with('warning', 'Type de paiement inconnu.');
    }

    protected function handleSubscriptionPayment($paymentData, $reference)
    {
        // For simplicity, I'll extract Org ID and maybe Plan ID if I add it to reference.
        // Better: SUB-{orgId}-{planId}-{billingCycle}-{timestamp}
        $parts = explode('-', $reference);
        if (count($parts) < 4) {
            return redirect()->route('organization.dashboard')->with('error', 'Référence abonnement invalide.');
        }

        $orgId = $parts[1];
        $planId = $parts[2];
        $billingCycle = $parts[3];

        $organization = \App\Models\Organization::find($orgId);
        $plan = CreditPack::find($planId); // It's a Subscription type credit pack

        if ($organization && $plan) {
            $organization->update([
                'subscription_plan' => $plan->target_plan,
                'subscription_expires_at' => ($billingCycle === 'yearly') ? now()->addYear() : now()->addMonth(),
                'auto_renew' => true, // Assuming auto-renew by default or tracked elsewhere
            ]);

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
            $organization->increment('credits_balance', $pack->credits);

            // Optionally log transaction in a transactions table

            return redirect()->route('organization.wallet.index')->with('success', "Pack de {$pack->credits} crédits ajouté avec succès !");
        }

        return redirect()->route('organization.wallet.index')->with('error', 'Pack introuvable.');
    }
}