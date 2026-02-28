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
            ->orderBy('display_order')
            ->get();

        $freePlan = $allPlans->where('target_plan', \App\Models\Organization::PLAN_FREE)->first();
        $proPlans = $allPlans->where('target_plan', 'pro')->keyBy('duration_days');
        $enterprisePlans = $allPlans->where('target_plan', 'enterprise')->keyBy('duration_days');

        return view('organization.subscriptions.index', compact('freePlan', 'proPlans', 'enterprisePlans'));
    }

    /**
     * Handle subscription upgrade/downgrade.
     * The view sends the exact CreditPack ID for the chosen plan + period.
     * No billing_cycle resolution needed — use $plan directly.
     */
    public function subscribe(Request $request, CreditPack $plan)
    {
        $organization = $this->getCurrentOrganization();

        // Validate that this is indeed an active organization subscription plan
        if ($plan->type !== 'subscription' || ! $plan->is_active) {
            return redirect()->back()->with('error', 'Plan non trouvé ou inactif.');
        }

        $amount = $plan->price;
        $description = 'Abonnement '.$plan->name.' — '.$organization->name;
        $reference = 'SUB-'.$organization->id.'-'.time();
        $returnUrl = route('organization.dashboard');

        // Initiate Payment
        $monerooService = app(\App\Services\MonerooService::class);
        $user = auth()->user();

        // Create pending transaction record (stores plan_id for the callback)
        $localTransaction = \App\Models\MonerooTransaction::create([
            'user_id' => $user->id,
            'user_type' => get_class($user),
            'amount' => $amount,
            'currency' => 'XOF',
            'status' => 'pending',
            'credits_amount' => 0,
            'metadata' => [
                'reference' => $reference,
                'plan_id' => $plan->id, // exact plan: period + price
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
