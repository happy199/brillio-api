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
        $settings = SystemSetting::whereIn('key', [
            'credit_price_jeune',
            'credit_price_mentor',
            'credit_price_organization',
            'feature_cost_advanced_targeting',
            'payout_fee_percentage',
            'mentorship_commission_percent'
        ])->get()->keyBy('key');

        $creditPriceJeune = $settings['credit_price_jeune']->value ?? 50;
        $creditPriceMentor = $settings['credit_price_mentor']->value ?? 100;
        $creditPriceOrganization = $settings['credit_price_organization']->value ?? 150;
        $targetingCost = $settings['feature_cost_advanced_targeting']->value ?? 10;
        $payoutFeePercentage = $settings['payout_fee_percentage']->value ?? 5;
        $commissionPercent = $settings['mentorship_commission_percent']->value ?? 10;

        // Stats Détaillées

        // 1. Crédits Achetés (Montant > 0)
        $purchasedQuery = WalletTransaction::where('amount', '>', 0)
            ->join('users', 'wallet_transactions.user_id', '=', 'users.id')
            ->selectRaw('users.user_type, SUM(amount) as total_credits');

        $purchasedStats = $purchasedQuery->groupBy('users.user_type')->pluck('total_credits', 'user_type');

        $creditsPurchasedJeune = $purchasedStats['jeune'] ?? 0;
        $creditsPurchasedMentor = $purchasedStats['mentor'] ?? 0;
        $creditsPurchasedOrg = $purchasedStats['organization'] ?? 0;
        $totalCreditsPurchased = $creditsPurchasedJeune + $creditsPurchasedMentor + $creditsPurchasedOrg;

        // Estimation FCFA Achetés (Basé sur prix actuel)
        $fcfaPurchasedJeune = $creditsPurchasedJeune * $creditPriceJeune;
        $fcfaPurchasedMentor = $creditsPurchasedMentor * $creditPriceMentor;
        $fcfaPurchasedOrg = $creditsPurchasedOrg * $creditPriceOrganization;

        // 2. Crédits Consommés (Montant < 0)
        $consumedQuery = WalletTransaction::where('amount', '<', 0)
            ->join('users', 'wallet_transactions.user_id', '=', 'users.id')
            ->selectRaw('users.user_type, SUM(ABS(amount)) as total_consumed');

        $consumedStats = $consumedQuery->groupBy('users.user_type')->pluck('total_consumed', 'user_type');

        $creditsConsumedJeune = $consumedStats['jeune'] ?? 0;
        $creditsConsumedMentor = $consumedStats['mentor'] ?? 0;
        $creditsConsumedOrg = $consumedStats['organization'] ?? 0;
        $totalCreditsUsed = $creditsConsumedJeune + $creditsConsumedMentor + $creditsConsumedOrg;

        // Estimation FCFA Consommés
        $fcfaConsumedJeune = $creditsConsumedJeune * $creditPriceJeune;
        $fcfaConsumedMentor = $creditsConsumedMentor * $creditPriceMentor;
        $fcfaConsumedOrg = $creditsConsumedOrg * $creditPriceOrganization;

        // Revenue Réel Organizations (Payments completed)
        $orgRevenue = \App\Models\MonerooTransaction::where('status', 'completed')
            ->where('user_type', 'App\Models\User')
            ->whereHas('user', function ($q) {
            $q->where('user_type', 'organization');
        })
            ->sum('amount');

        // 50 dernières transactions (Include organization relationship)
        $transactions = WalletTransaction::with(['user', 'user.organization'])->latest()->limit(50)->get();

        return view('admin.monetization.index', compact(
            'creditPriceJeune',
            'creditPriceMentor',
            'creditPriceOrganization',
            'targetingCost',
            'payoutFeePercentage',
            'totalCreditsPurchased',
            'creditsPurchasedJeune',
            'creditsPurchasedMentor',
            'creditsPurchasedOrg',
            'fcfaPurchasedJeune',
            'fcfaPurchasedMentor',
            'fcfaPurchasedOrg',
            'totalCreditsUsed',
            'creditsConsumedJeune',
            'creditsConsumedMentor',
            'creditsConsumedOrg',
            'fcfaConsumedJeune',
            'fcfaConsumedMentor',
            'fcfaConsumedOrg',
            'orgRevenue',
            'commissionPercent',
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
            'credit_price_organization' => 'required|integer|min:1',
            'feature_cost_advanced_targeting' => 'required|integer|min:0',
            'payout_fee_percentage' => 'required|integer|min:0|max:100',
            'mentorship_commission_percent' => 'required|integer|min:0|max:100',
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