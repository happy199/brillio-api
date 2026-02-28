<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\CreditPack;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Display subscription plans and credit packs.
     */
    public function index()
    {
        // Load all active subscription plans grouped by target_plan and indexed by duration_days
        $allPlans = CreditPack::subscriptions()
            ->where('user_type', 'organization')
            ->where('is_active', true)
            ->orderBy('duration_days')
            ->get();

        $proPlans = $allPlans->where('target_plan', 'pro')->keyBy('duration_days');
        $enterprisePlans = $allPlans->where('target_plan', 'enterprise')->keyBy('duration_days');

        return view('organization.subscriptions.index', compact('proPlans', 'enterprisePlans'));
    }

    /**
     * Handle subscription upgrade/downgrade.
     * The form now sends the exact plan ID — no need for billing_cycle lookup.
     */
    public function subscribe(Request $request, CreditPack $plan)
    {
        $organization = $this->getCurrentOrganization();

        // 1. Determine Billing Cycle and Amount
        $billingCycle = $request->input('billing_cycle', 'monthly'); // 'monthly' or 'yearly'

        // Ensure the plan matches the requested cycle (or find the corresponding one)
        // The UI sends the ID of the monthly plan by default, but if 'annual' is selected, we might need to find the yearly equivalent.
        // Current logic in view: sends ID of monthly plan.

        $targetPlanName = $plan->target_plan; // 'pro' or 'enterprise'

        if ($billingCycle === 'yearly') {
            $actualPlan = CreditPack::subscriptions()
                ->where('target_plan', $targetPlanName)
                ->where('duration_days', 365)
                ->first();
        } else {
            $actualPlan = CreditPack::subscriptions()
                ->where('target_plan', $targetPlanName)
                ->where('duration_days', 30)
                ->first();
        }

        if (! $actualPlan) {
            return redirect()->back()->with('error', 'Plan non trouvé pour la période choisie.');
        }

        $amount = $actualPlan->price;
        $description = 'Abonnement '.ucfirst($actualPlan->name).' ('.($billingCycle === 'yearly' ? 'Annuel' : 'Mensuel').')';

        // Detailed reference for callback parsing: SUB-{orgId}-{planId}-{billingCycle}-{timestamp}
        $reference = 'SUB-'.$organization->id.'-'.time();

        $callbackUrl = route('organization.payment.callback');
        $returnUrl = route('organization.dashboard');

        // 2. Initiate Payment
        $monerooService = app(\App\Services\MonerooService::class);
        $user = auth()->user();

        // Create pending transaction record
        $localTransaction = \App\Models\MonerooTransaction::create([
            'user_id' => $user->id,
            'user_type' => get_class($user),
            'amount' => $amount,
            'currency' => 'XOF',
            'status' => 'pending',
            'credits_amount' => 0,
            'metadata' => [
                'reference' => $reference,
                'plan_id' => $actualPlan->id,
                'billing_cycle' => $billingCycle,
                'user_type' => 'organization',
            ],
        ]);

        $customer = [
            'email' => $user->email,
            'first_name' => $monerooService->splitName($user->name)['first_name'],
            'last_name' => $monerooService->splitName($user->name)['last_name'],
            'phone' => $user->phone,
        ];

        $paymentData = $monerooService->initializePayment(
            $amount,
            $description,
            $customer,
            ['reference' => $reference, 'transaction_id' => $localTransaction->id],
            $returnUrl
        );

        if (isset($paymentData['checkout_url'])) {
            $localTransaction->update(['moneroo_transaction_id' => $paymentData['id']]);

            return redirect($paymentData['checkout_url']);
        }

        return redirect()->back()->with('error', "Erreur lors de l'initialisation du paiement.");
    }

    /**
     * Handle subscription downgrade (Disable auto-renew).
     * The plan remains active until subscription_expires_at.
     */
    public function downgrade(Request $request)
    {
        $organization = $this->getCurrentOrganization();

        if (! $organization->isPro() && ! $organization->isEnterprise()) {
            return redirect()->back()->with('error', 'Vous êtes déjà sur le plan Standard.');
        }

        $organization->update([
            'auto_renew' => false,
        ]);

        return redirect()->route('organization.subscriptions.index')->with('success', 'Votre demande de rétrogradation a été enregistrée. Votre plan actuel restera actif jusqu\'au '.$organization->subscription_expires_at->format('d/m/Y').'.');
    }
}
