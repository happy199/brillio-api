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
        $settings = SystemSetting::whereIn('key', ['credit_price_jeune', 'credit_price_mentor', 'feature_cost_advanced_targeting'])->get()->keyBy('key');

        $creditPriceJeune = $settings['credit_price_jeune']->value ?? 50;
        $creditPriceMentor = $settings['credit_price_mentor']->value ?? 100;
        $targetingCost = $settings['feature_cost_advanced_targeting']->value ?? 10;

        // Stats Détaillées

        // 1. Crédits Achetés (Montant > 0)
        $purchasedQuery = WalletTransaction::where('amount', '>', 0)
            ->join('users', 'wallet_transactions.user_id', '=', 'users.id')
            ->selectRaw('users.user_type, SUM(amount) as total_credits');

        $purchasedStats = $purchasedQuery->groupBy('users.user_type')->pluck('total_credits', 'user_type');

        $creditsPurchasedJeune = $purchasedStats['jeune'] ?? 0;
        $creditsPurchasedMentor = $purchasedStats['mentor'] ?? 0;
        $totalCreditsPurchased = $creditsPurchasedJeune + $creditsPurchasedMentor;

        // Estimation FCFA Achetés (Basé sur prix actuel)
        $fcfaPurchasedJeune = $creditsPurchasedJeune * $creditPriceJeune;
        $fcfaPurchasedMentor = $creditsPurchasedMentor * $creditPriceMentor;


        // 2. Crédits Consommés (Montant < 0)
        // On prend la valeur absolue pour l'affichage
        $consumedQuery = WalletTransaction::where('amount', '<', 0)
            ->join('users', 'wallet_transactions.user_id', '=', 'users.id')
            ->selectRaw('users.user_type, SUM(ABS(amount)) as total_consumed');

        $consumedStats = $consumedQuery->groupBy('users.user_type')->pluck('total_consumed', 'user_type');

        $creditsConsumedJeune = $consumedStats['jeune'] ?? 0;
        $creditsConsumedMentor = $consumedStats['mentor'] ?? 0;
        $totalCreditsUsed = $creditsConsumedJeune + $creditsConsumedMentor;

        // Estimation FCFA Consommés
        $fcfaConsumedJeune = $creditsConsumedJeune * $creditPriceJeune;
        $fcfaConsumedMentor = $creditsConsumedMentor * $creditPriceMentor;


        // 50 dernières transactions
        $transactions = WalletTransaction::with('user')->latest()->limit(50)->get();

        return view('admin.monetization.index', compact(
            'creditPriceJeune',
            'creditPriceMentor',
            'targetingCost',
            'totalCreditsPurchased',
            'creditsPurchasedJeune',
            'creditsPurchasedMentor',
            'fcfaPurchasedJeune',
            'fcfaPurchasedMentor',
            'totalCreditsUsed',
            'creditsConsumedJeune',
            'creditsConsumedMentor',
            'fcfaConsumedJeune',
            'fcfaConsumedMentor',
            'transactions'
        ));
    }

    /**
     * Mettre à jour les paramètres de prix
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'credit_price_jeune' => 'required|integer|min:1',
            'credit_price_mentor' => 'required|integer|min:1',
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
