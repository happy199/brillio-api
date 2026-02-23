<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditPack;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        $plans = CreditPack::subscriptions()
            ->orderBy('target_plan')
            ->orderBy('duration_days')
            ->get();

        return view('admin.subscription-plans.index', compact('plans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_plan' => 'required|in:pro,enterprise',
            'duration_days' => 'required|integer|min:1',
            'price' => 'required|integer|min:0',
            'promo_percent' => 'nullable|integer|min:0|max:100',
            'description' => 'nullable|string',
            'features' => 'nullable|array',
            'display_order' => 'integer',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['type'] = 'subscription';
        $validated['user_type'] = 'organization'; // Subscriptions are for organizations
        $validated['is_popular'] = $request->has('is_popular');
        $validated['is_active'] = $request->has('is_active');

        CreditPack::create($validated);

        return back()->with('success', 'Plan d\'abonnement créé avec succès.');
    }

    public function update(Request $request, CreditPack $subscriptionPlan)
    {
        // Ensure we are updating a subscription
        if ($subscriptionPlan->type !== 'subscription') {
            return back()->with('error', 'Ceci n\'est pas un plan d\'abonnement.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_plan' => 'required|in:pro,enterprise',
            'duration_days' => 'required|integer|min:1',
            'price' => 'required|integer|min:0',
            'promo_percent' => 'nullable|integer|min:0|max:100',
            'description' => 'nullable|string',
            'features' => 'nullable|array',
            'display_order' => 'integer',
        ]);

        $data = $validated;
        $data['is_popular'] = $request->has('is_popular');
        $data['is_active'] = $request->has('is_active');
        $data['features'] = $request->input('features', []); // Ensure features are included

        $subscriptionPlan->update($data);

        return back()->with('success', 'Plan mis à jour.');
    }

    public function destroy(CreditPack $subscriptionPlan)
    {
        if ($subscriptionPlan->type !== 'subscription') {
            return back()->with('error', 'Ceci n\'est pas un plan d\'abonnement.');
        }

        $subscriptionPlan->delete();

        return back()->with('success', 'Plan supprimé.');
    }
}
