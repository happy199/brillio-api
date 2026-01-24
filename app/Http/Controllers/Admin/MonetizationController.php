<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\SystemSetting;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MonetizationController extends Controller
{
    /**
     * Tableau de bord Monétisation & Configuration Px
     */
    public function index()
    {
        $settings = SystemSetting::whereIn('key', ['credit_price', 'feature_cost_advanced_targeting'])->get()->keyBy('key');

        $creditPrice = $settings['credit_price']->value ?? 50;
        $targetingCost = $settings['feature_cost_advanced_targeting']->value ?? 10;

        // Stats basiques
        $totalCreditsPurchased = WalletTransaction::where('type', 'purchase')->sum('amount');
        $totalCreditsUsed = abs(WalletTransaction::where('type', 'expense')->sum('amount'));

        // 50 dernières transactions
        $transactions = WalletTransaction::with('user')->latest()->limit(50)->get();

        return view('admin.monetization.index', compact('creditPrice', 'targetingCost', 'totalCreditsPurchased', 'totalCreditsUsed', 'transactions'));
    }

    /**
     * Mettre à jour les paramètres de prix
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'credit_price' => 'required|integer|min:1',
            'feature_cost_advanced_targeting' => 'required|integer|min:0',
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'type' => 'integer']
            );
        }

        return back()->with('success', 'Paramètres mis à jour.');
    }

    /**
     * Gestion des coupons
     */
    public function coupons()
    {
        $coupons = Coupon::latest()->paginate(20);
        return view('admin.monetization.coupons', compact('coupons'));
    }

    /**
     * Création de coupon
     */
    public function storeCoupon(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:coupons,code|min:3|max:20',
            'credits_amount' => 'required|integer|min:1',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date|after:today',
        ]);

        Coupon::create([
            'code' => strtoupper($validated['code']),
            'credits_amount' => $validated['credits_amount'],
            'max_uses' => $validated['max_uses'],
            'expires_at' => $validated['expires_at'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Coupon créé.');
    }

    /**
     * Suppression de coupon
     */
    public function destroyCoupon(Coupon $coupon)
    {
        $coupon->delete();
        return back()->with('success', 'Coupon supprimé.');
    }
}
