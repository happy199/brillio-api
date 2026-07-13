<?php

namespace App\Http\Controllers\Shared;

use App\Models\Coupon;
use App\Models\CreditPack;
use App\Models\MonerooTransaction;
use App\Services\MonerooService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait HasWallet
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Get the role-specific config for queries and views.
     * Expected to return an array:
     * [
     *     'user_type' => 'jeune', // or 'mentor'
     *     'view_prefix' => 'jeune.wallet.', // or 'mentor.wallet.'
     * ]
     */
    abstract protected function getWalletConfig(): array;

    public function index()
    {
        $user = Auth::user();
        $config = $this->getWalletConfig();
        $userType = $config['user_type'];

        $transactions = $user->walletTransactions()->latest()->paginate(10);

        $creditPrice = $this->walletService->getCreditPrice($userType);

        $packs = CreditPack::where('user_type', $userType)
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();

        return view($config['view_prefix'].'index', compact('user', 'transactions', 'creditPrice', 'packs'));
    }

    public function purchase(Request $request)
    {
        $validated = $request->validate([
            'pack_id' => 'required|exists:credit_packs,id',
        ]);

        $config = $this->getWalletConfig();
        $userType = $config['user_type'];
        $pack = CreditPack::findOrFail($validated['pack_id']);

        if ($pack->user_type !== $userType) {
            return back()->withErrors(['pack_id' => 'Ce pack n\'est pas disponible pour votre type de compte.']);
        }

        $credits = $pack->credits;
        $amountXOF = $pack->price;
        $user = Auth::user();

        try {
            $monerooService = app(MonerooService::class);

            $transaction = new MonerooTransaction;
            $transaction->fill([
                'user_id' => $user->id,
                'user_type' => get_class($user),
                'amount' => $amountXOF,
                'currency' => 'XOF',
                'status' => 'pending',
                'credits_amount' => $credits,
                'metadata' => [
                    'user_type' => $userType,
                    'user_name' => $user->prenom.' '.$user->nom,
                    'pack_id' => $pack->id,
                    'pack_name' => $pack->name,
                ],
            ]);
            $transaction->save();

            $nameParts = $monerooService->splitName($user->name);

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
                    'user_type' => $userType,
                    'credits' => $credits,
                    'pack_id' => $pack->id,
                ],
                returnUrl: route('payments.callback')
            );

            $transaction->update([
                'moneroo_transaction_id' => $paymentData['id'],
            ]);

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

    public function redeemCoupon(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
        ]);

        $code = strtoupper($validated['code']);
        $coupon = Coupon::where('code', $code)->first();

        $user = Auth::user();

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
