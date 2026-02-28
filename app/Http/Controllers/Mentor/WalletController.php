<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\WalletTransaction;
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
        $walletTransactions = WalletTransaction::where('user_id', $user->id)
            ->whereIn('type', ['purchase', 'expense', 'coupon', 'service_fee', 'gift', 'refund'])
            ->latest()
            ->paginate(10, ['*'], 'wallet_page');

        // 2. Historique REVENUS (Ventes ressources, sessions, etc.)
        // Types: 'income'
        $incomeTransactions = WalletTransaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->latest()
            ->paginate(10, ['*'], 'income_page');

        $creditPrice = $this->walletService->getCreditPrice('mentor');

        // Calcul des crédits achetés (Total cumulé)
        $totalCreditsPurchased = WalletTransaction::where('user_id', $user->id)
            ->whereIn('type', ['purchase', 'coupon', 'gift', 'refund'])
            ->where('amount', '>', 0)
            ->sum('amount');

        // Calcul des crédits gagnés (Total cumulé)
        $totalCreditsEarned = WalletTransaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->sum('amount');

        // Calcul des dépenses (Total cumulé)
        $totalExpenses = abs(WalletTransaction::where('user_id', $user->id)
            ->whereIn('type', ['expense', 'service_fee'])
            ->sum('amount'));

        // Calcul des retraits (Total cumulé)
        $totalWithdrawn = abs(WalletTransaction::where('user_id', $user->id)
            ->where('type', 'payout')
            ->sum('amount'));

        // Breakdown des crédits par source
        $creditBreakdown = [
            'purchased' => $totalCreditsPurchased, // Cumulé
            'earned' => $totalCreditsEarned,     // Cumulé
            'spent' => $totalExpenses,           // Cumulé
            'withdrawn' => $totalWithdrawn,       // Cumulé
        ];

        // Breakdown des crédits restants par source

        // Valeur en FCFA des revenus (Basé sur le prix de rachat/vente actuel ou une valeur fixe)
        // Ici on estime la valeur basée sur le prix d'achat mentor, ou un taux de reversement spécifique
        // Pour l'instant on utilise le prix du crédit mentor comme base de valeur
        $estimatedValueFcfa = $totalCreditsEarned * $creditPrice;

        // Packs dynamiques depuis la DB
        $packs = \App\Models\CreditPack::where('user_type', 'mentor')
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();

        $payoutFeePercentage = \App\Models\SystemSetting::getValue('payout_fee_percentage', 5);
        $payoutMinFee = \App\Models\SystemSetting::getValue('payout_min_fee', 100);

        return view('mentor.wallet.index', compact(
            'user',
            'walletTransactions',
            'incomeTransactions',
            'creditPrice',
            'packs',
            'totalCreditsEarned',
            'estimatedValueFcfa',
            'payoutFeePercentage',
            'payoutMinFee',
            'creditBreakdown'
        ));
    }

    /**
     * Initialize credit purchase via Moneroo
     */
    public function purchase(Request $request)
    {
        $validated = $request->validate([
            'pack_id' => 'required|exists:credit_packs,id',
        ]);

        $pack = \App\Models\CreditPack::findOrFail($validated['pack_id']);

        // Security check
        if ($pack->user_type !== 'mentor') {
            return back()->withErrors(['pack_id' => 'Ce pack n\'est pas disponible pour votre type de compte.']);
        }

        $credits = $pack->credits;
        $amountXOF = $pack->price;
        $user = Auth::user();

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
                    'user_name' => $user->prenom.' '.$user->nom,
                    'pack_id' => $pack->id,
                    'pack_name' => $pack->name,
                ],
            ]);

            // Split name for Moneroo API
            $nameParts = $monerooService->splitName($user->name);

            // Initialize Moneroo payment
            $paymentData = $monerooService->initializePayment(
                amount: $amountXOF,
                description: "Achat : {$pack->name} ({$credits} crédits)",
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
                    'pack_id' => $pack->id,
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
            Log::error('Moneroo payment initialization failed', [
                'user_id' => $user->id,
                'pack_id' => $pack->id,
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
        if (! $coupon || ! $coupon->isValid($user)) {
            if ($coupon && $coupon->hasBeenUsedBy($user)) {
                return back()->withErrors(['code' => 'Vous avez déjà utilisé ce coupon.']);
            }

            return back()->withErrors(['code' => 'Ce coupon est invalide ou expiré.']);
        }

        try {
            $this->walletService->redeemCoupon($user, $code);

            return back()->with('success', 'Coupon validé !');

        } catch (\Exception $e) {
            Log::error('Coupon redemption failed', [
                'user_id' => $user->id,
                'coupon_code' => $code,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['code' => $e->getMessage()]);
        }
    }
}