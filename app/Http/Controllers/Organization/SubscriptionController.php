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

        $creditPacks = CreditPack::credits()
            ->where('user_type', 'organization')
            ->where('is_active', true)
            ->orderBy('price')
            ->get();

        return view('organization.subscriptions.index', compact('monthlyPlans', 'yearlyPlans', 'creditPacks'));
    }

    /**
     * Handle subscription upgrade/downgrade (Mock implementation for now or redirect to payment).
     */
    public function subscribe(Request $request, CreditPack $plan)
    {
        // Integration with Moneroo would go here.
        // For now, we simulate a successful subscription update for demonstration if previously validated via payment logic.

        // This is where we would redirect to payment gateway.
        // return redirect()->route('payment.process', ['pack' => $plan->id]);

        return redirect()->back()->with('info', 'Le système de paiement pour les abonnements est en cours d\'intégration.');
    }
}