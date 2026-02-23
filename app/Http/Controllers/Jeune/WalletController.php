<?php

namespace App\Http\Controllers\Jeune;

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
        $transactions = $user->walletTransactions()->latest()->paginate(10);

        $creditPrice = $this->walletService->getCreditPrice('jeune');

        // Packs dynamiques depuis la DB
        $packs = \App\Models\CreditPack::where('user_type', 'jeune')
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();

        return view('jeune.wallet.index', compact('user', 'transactions', 'creditPrice', 'packs'));
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
        if ($pack->user_type !== 'jeune') {
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
                    'user_type' => 'jeune',
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
                    'user_type' => 'jeune',
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
            \Log::error('Moneroo payment initialization failed', [
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
