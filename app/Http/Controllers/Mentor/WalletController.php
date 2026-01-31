<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
     * Initialize credit purchase via Moneroo
     */
    public function purchase(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|integer|min:1',
        ]);

        $credits = $validated['amount'];
        $user = Auth::user();

        // Calculate price in XOF (100 XOF = 1 crédit)
        $amountXOF = $credits * 100;

        try {
            $monerooService = app(\App\Services\MonerooService::class);

            // Create pending transaction record
            $transaction = \App\Models\MonerooTransaction::create([
                'user_id' => $user->id,
                'user_type' => get_class($user),
                'amount' => $amountXOF,
                'currency' => 'XOF',
                'status' => 'pending',
                'credits_amount' => $credits,
                'metadata' => [
                    'user_type' => 'mentor',
                    'user_name' => $user->prenom . ' ' . $user->nom,
                ],
            ]);

            // Split name for Moneroo API
            $nameParts = $monerooService->splitName($user->name);

            // Initialize Moneroo payment
            $paymentData = $monerooService->initializePayment(
                amount: $amountXOF,
                description: "Achat de {$credits} crédits Brillio",
                customer: [
                    'email' => $user->email,
                    'first_name' => $nameParts['first_name'],
                    'last_name' => $nameParts['last_name'],
                    'phone' => $user->telephone ?? null,
                ],
                metadata: [
                    'transaction_id' => $transaction->id,
                    'user_id' => $user->id,
                    'user_type' => 'mentor',
                    'credits' => $credits,
                ],
                returnUrl: route('payments.callback')
            );

            // Save Moneroo transaction ID
            $transaction->update([
                'moneroo_transaction_id' => $paymentData['id'],
            ]);

            // Redirect to Moneroo checkout
            return redirect($paymentData['checkout_url']);

        } catch (\Exception $e) {
            \Log::error('Moneroo payment initialization failed', [
                'user_id' => $user->id,
                'credits' => $credits,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Une erreur est survenue lors de l\'initialisation du paiement. Veuillez réessayer.']);
        }
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

        $user = Auth::user();

        // Check if coupon exists and is valid for this user
        if (!$coupon || !$coupon->isValid($user)) {
            if ($coupon && $coupon->hasBeenUsedBy($user)) {
                return back()->withErrors(['code' => 'Vous avez déjà utilisé ce coupon.']);
            }
            return back()->withErrors(['code' => 'Ce coupon est invalide ou expiré.']);
        }

        try {
            // Add credits to user
            $this->walletService->addCredits(
                $user,
                $coupon->credits_amount,
                'coupon',
                "Coupon : {$code}",
                $coupon
            );

            // Record this redemption in pivot table
            $coupon->users()->attach($user->id, [
                'credits_received' => $coupon->credits_amount,
                'redeemed_at' => now(),
            ]);

            // Increment global usage count
            $coupon->increment('uses_count');

            return back()->with('success', "Coupon validé ! +{$coupon->credits_amount} crédits.");

        } catch (\Exception $e) {
            Log::error('Coupon redemption failed', [
                'user_id' => $user->id,
                'coupon_code' => $code,
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => "Erreur lors de l'ajout des crédits."]);
        }
    }
}
