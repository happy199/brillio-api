<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Page du portefeuille
     */
    public function index()
    {
        $user = Auth::user();
        // Transactions globales (pour l'historique général sidebar si gardé) ou juste recent
        // Mais on veut séparer.

        // 1. Historique RECHARGEMENT / DÉPENSES (Lié au wallet perso pour les features)
        // Types: 'purchase' (achat crédits), 'expense' (consommation features), 'coupon', 'service_fee'
        $walletTransactions = $user->walletTransactions()
            ->whereIn('type', ['purchase', 'expense', 'coupon', 'service_fee'])
            ->latest()
            ->paginate(10, ['*'], 'wallet_page');

        // 2. Historique REVENUS (Ventes ressources, sessions, etc.)
        // Types: 'income'
        $incomeTransactions = $user->walletTransactions()
            ->where('type', 'income')
            ->latest()
            ->paginate(10, ['*'], 'income_page');

        $creditPrice = $this->walletService->getCreditPrice('mentor');

        // Calcul des totaux revenus
        $totalCreditsEarned = $user->walletTransactions()->where('type', 'income')->sum('amount');

        // Valeur en FCFA des revenus (Basé sur le prix de rachat/vente actuel ou une valeur fixe)
        // Ici on estime la valeur basée sur le prix d'achat mentor, ou un taux de reversement spécifique
        // Pour l'instant on utilise le prix du crédit mentor comme base de valeur
        $estimatedValueFcfa = $totalCreditsEarned * $creditPrice;

        // Packs suggérés
        $packs = [
            ['credits' => 10, 'price' => 10 * $creditPrice, 'bonus' => 0],
            ['credits' => 50, 'price' => 50 * $creditPrice * 0.95, 'bonus' => 5], // 5% reduc
            ['credits' => 100, 'price' => 100 * $creditPrice * 0.9, 'bonus' => 10], // 10% reduc
            ['credits' => 500, 'price' => 500 * $creditPrice * 0.85, 'bonus' => 15], // 15% reduc
        ];

        return view('mentor.wallet.index', compact(
            'user',
            'walletTransactions',
            'incomeTransactions',
            'creditPrice',
            'packs',
            'totalCreditsEarned',
            'estimatedValueFcfa'
        ));
    }

    /**
     * Simulation d'achat de crédits
     */
    public function purchase(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|integer|min:1',
        ]);

        $amount = $validated['amount'];
        $user = Auth::user();

        // Ici, on redirigerait vers Stripe/CinetPay. Pour l'instant, on simule le succès.

        $this->walletService->addCredits(
            $user,
            $amount,
            'purchase',
            "Achat de {$amount} crédits"
        );

        return back()->with('success', 'Crédits ajoutés avec succès !');
    }

    /**
     * Utiliser un coupon
     */
    public function redeemCoupon(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
        ]);

        $code = strtoupper($validated['code']);
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon || !$coupon->isValid()) {
            return back()->withErrors(['code' => 'Ce coupon est invalide ou expiré.']);
        }

        // Vérifier si déjà utilisé par ce user (Optionnel, mais recommandé)
        // Pour l'instant on simplifie, mais idéalement on aurait une table coupon_usage

        $user = Auth::user();

        try {
            $this->walletService->addCredits(
                $user,
                $coupon->credits_amount,
                'coupon',
                "Coupon : {$code}",
                $coupon
            );

            $coupon->increment('uses_count');

            return back()->with('success', "Coupon validé ! +{$coupon->credits_amount} crédits.");

        } catch (\Exception $e) {
            return back()->withErrors(['error' => "Erreur lors de l'ajout des crédits."]);
        }
    }
}
