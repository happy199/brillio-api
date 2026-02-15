<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\CreditPack;

class CreditPackController extends Controller
{
    public function index()
    {
        $jeunePacks = CreditPack::credits()->where('user_type', 'jeune')->orderBy('display_order')->get();
        $mentorPacks = CreditPack::credits()->where('user_type', 'mentor')->orderBy('display_order')->get();
        $organizationPacks = CreditPack::credits()->where('user_type', 'organization')->orderBy('display_order')->get();

        // Base prices for calculation in view
        $walletService = app(\App\Services\WalletService::class);
        $jeuneCreditPrice = $walletService->getCreditPrice('jeune');
        $mentorCreditPrice = $walletService->getCreditPrice('mentor');
        $organizationCreditPrice = $walletService->getCreditPrice('organization');

        return view('admin.credit-packs.index', compact(
            'jeunePacks',
            'mentorPacks',
            'organizationPacks',
            'jeuneCreditPrice',
            'mentorCreditPrice',
            'organizationCreditPrice'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_type' => 'required|in:jeune,mentor,organization',
            'credits' => 'required|integer|min:1',
            'price' => 'required|integer|min:0',
            'promo_percent' => 'nullable|integer|min:0|max:100',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'display_order' => 'integer',
            'is_popular' => 'boolean'
        ]);

        $validated['type'] = 'credits';
        $validated['promo_percent'] = $validated['promo_percent'] ?? 0;
        $validated['is_popular'] = $request->has('is_popular');
        $validated['is_active'] = $request->has('is_active');

        CreditPack::create($validated);

        return back()->with('success', 'Pack de crédits créé avec succès.');
    }

    public function update(Request $request, CreditPack $creditPack)
    {
        $validated = $request->validate([
            'credits' => 'required|integer|min:1',
            'price' => 'required|integer|min:0',
            'promo_percent' => 'nullable|integer|min:0|max:100',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'display_order' => 'integer',
        ]);

        $entry = $request->all();
        $entry['promo_percent'] = $entry['promo_percent'] ?? 0;
        $entry['is_popular'] = $request->has('is_popular');
        $entry['is_active'] = $request->has('is_active');

        $creditPack->update($entry);

        return back()->with('success', 'Pack mis à jour.');
    }

    public function destroy(CreditPack $creditPack)
    {
        $creditPack->delete();
        return back()->with('success', 'Pack supprimé.');
    }
}