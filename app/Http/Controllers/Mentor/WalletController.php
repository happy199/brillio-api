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
        $transactions = $user->walletTransactions()->latest()->paginate(10);

        $creditPrice = $this->walletService->getCreditPrice();

        // Packs suggérés
        $packs = [
            ['credits' => 10, 'price' => 10 * $creditPrice, 'bonus' => 0],
            ['credits' => 50, 'price' => 50 * $creditPrice * 0.95, 'bonus' => 5], // 5% reduc
            ['credits' => 100, 'price' => 100 * $creditPrice * 0.9, 'bonus' => 10], // 10% reduc
            ['credits' => 500, 'price' => 500 * $creditPrice * 0.85, 'bonus' => 15], // 15% reduc
        ];

        return view('mentor.wallet.index', compact('user', 'transactions', 'creditPrice', 'packs'));
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
