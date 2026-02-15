<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\CreditPack;
use App\Models\Organization;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Display subscription plans and credit packs.
     */
    public function index()
    {
        $monthlyPlans = CreditPack::subscriptions()
            ->where('duration_days', 30)
            ->where('is_active', true)
            ->orderBy('price')
            ->get();

        $yearlyPlans = CreditPack::subscriptions()
            ->where('duration_days', 365)
            ->where('is_active', true)
            ->orderBy('price')
            ->get();

        return view('organization.subscriptions.index', compact('monthlyPlans', 'yearlyPlans'));
    }

    /**
     * Handle subscription upgrade/downgrade.
     */
    public function subscribe(Request $request, CreditPack $plan)
    {
        $organization = auth()->user()->organization;

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
        }
        else {
            $actualPlan = CreditPack::subscriptions()
                ->where('target_plan', $targetPlanName)
                ->where('duration_days', 30)
                ->first();
        }

        if (!$actualPlan) {
            return redirect()->back()->with('error', 'Plan non trouvé pour la période choisie.');
        }

        $amount = $actualPlan->price;
        $description = "Abonnement " . ucfirst($actualPlan->name) . " (" . ($billingCycle === 'yearly' ? 'Annuel' : 'Mensuel') . ")";

        // Detailed reference for callback parsing: SUB-{orgId}-{planId}-{billingCycle}-{timestamp}
        $reference = 'SUB-' . $organization->id . '-' . time();

        $callbackUrl = route('organization.payment.callback');
        $returnUrl = route('organization.dashboard');

        // 2. Initiate Payment
        $monerooService = app(\App\Services\MonerooService::class);
        $user = auth()->user();
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
        ['reference' => $reference],
            $returnUrl
        );

        if (isset($paymentData['checkout_url'])) {
            return redirect($paymentData['checkout_url']);
        }

        return redirect()->back()->with('error', "Erreur lors de l'initialisation du paiement.");
    }
}