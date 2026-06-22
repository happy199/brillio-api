<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\CreditPack;
use App\Models\MonerooTransaction;
use App\Models\Organization;
use App\Services\MonerooService;
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

        $freePlan = $allPlans->where('target_plan', Organization::PLAN_FREE)->first();
        $proPlans = $allPlans->where('target_plan', 'pro')->keyBy('duration_days');
        $enterprisePlans = $allPlans->where('target_plan', 'enterprise')->keyBy('duration_days');
        $establishmentPlans = $allPlans->where('target_plan', 'establishment')->keyBy('duration_days');

        return view('organization.subscriptions.index', compact('freePlan', 'proPlans', 'enterprisePlans', 'establishmentPlans'));
    }

    /**
     * Handle request for Establishment Plan contact.
     */
    public function requestContact(Request $request)
    {
        $organization = $this->getCurrentOrganization();
        $user = auth()->user();

        // Log the request (or send email)
        ContactMessage::create([
            'name' => $user->name,
            'email' => $user->email,
            'subject' => "Demande de Plan Établissement - {$organization->name}",
            'message' => "L'organisation {$organization->name} (ID: {$organization->id}) souhaite être recontactée pour souscrire au plan Établissement.",
        ]);

        return redirect()->back()->with('success', 'Votre demande a été envoyée. L\'équipe Brillio vous recontactera très prochainement.');
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
        $monerooService = app(MonerooService::class);
        $user = auth()->user();

        // Create pending transaction record (stores plan_id for the callback)
        $localTransaction = MonerooTransaction::create([
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
                'description' => $description,
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

        // Target plan can be 'free', 'pro', or 'enterprise'
        $targetPlan = $request->input('to', 'free');

        if (! in_array($targetPlan, ['free', 'pro', 'enterprise'])) {
            return redirect()->back()->with('error', 'Plan de rétrogradation invalide.');
        }

        $planHierarchy = [
            'free' => 0,
            'pro' => 1,
            'enterprise' => 2,
            'establishment' => 3,
        ];

        $currentPlanValue = $planHierarchy[$organization->subscription_plan] ?? 0;
        $targetPlanValue = $planHierarchy[$targetPlan] ?? 0;

        if ($targetPlanValue >= $currentPlanValue) {
            return redirect()->back()->with('error', 'Vous ne pouvez rétrograder que vers un plan inférieur.');
        }

        $organization->update([
            'auto_renew' => false,
            'pending_downgrade_to' => $targetPlan,
        ]);

        $planLabels = [
            'free' => 'Standard (Gratuit)',
            'pro' => 'Professionnel',
            'enterprise' => 'Entreprise',
        ];
        $label = $planLabels[$targetPlan] ?? 'Standard';

        return redirect()->route('organization.subscriptions.index')->with('success', 'Votre demande de rétrogradation vers le plan '.$label.' a été enregistrée. Votre plan actuel restera actif jusqu\'au '.$organization->subscription_expires_at->format('d/m/Y').'.');
    }
}
