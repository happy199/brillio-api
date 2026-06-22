<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

/**
 * Controller pour la gestion du portefeuille via API
 */
class WalletController extends Controller
{
    public function __construct(
        private WalletService $walletService
    ) {}

    /**
     * @OA\Get(
     * path="/api/v2/wallet",
     * summary="Récupère le solde et les transactions du portefeuille",
     * tags={"Portefeuille"},
     *
     * @OA\Response(response= 200, description="Détails du portefeuille"),
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $transactions = $user->walletTransactions()->latest()->paginate(20);

        return $this->success([
            'credits_balance' => $user->credits_balance,
            'transactions' => $transactions->map(fn ($t) => $this->formatTransaction($t)),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'total' => $transactions->total(),
            ],
            'credit_price_fcfa' => $this->walletService->getCreditPrice($user->user_type ?? 'jeune'),
        ]);
    }

    /**
     * @OA\Get(
     * path="/api/v2/wallet/packs",
     * summary="Liste les packs de crédits disponibles",
     * tags={"Portefeuille"},
     *
     * @OA\Response(response= 200, description="Liste des packs"),
     * )
     */
    public function packs(Request $request): JsonResponse
    {
        $user = Auth::user();
        $packs = \App\Models\CreditPack::where('user_type', $user->user_type ?? 'jeune')
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();

        return $this->success([
            'packs' => $packs->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'credits_amount' => $p->credits,
                'price_fcfa' => $p->price,
                'description' => $p->description,
            ]),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v2/wallet/redeem",
     *     summary="Utiliser un coupon pour obtenir des crédits",
     *     tags={"Portefeuille"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"code"},
     *
     *             @OA\Property(property="code", type="string", example="MONCOUPON"),
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Coupon validé avec succès"),
     *     @OA\Response(response=400, description="Coupon invalide ou déjà utilisé"),
     *     @OA\Response(response=500, description="Erreur serveur"),
     * )
     */
    public function redeemCoupon(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string',
        ]);

        $code = strtoupper($validated['code']);
        $coupon = Coupon::where('code', $code)->first();
        $user = $request->user();

        if (! $coupon || ! $coupon->isValid($user)) {
            if ($coupon && $coupon->hasBeenUsedBy($user)) {
                return $this->error('Vous avez déjà utilisé ce coupon.', 400);
            }

            return $this->error('Ce coupon est invalide ou expiré.', 400);
        }

        try {
            $coupon = $this->walletService->redeemCoupon($user, $code);
            $user->refresh();

            return $this->success([
                'credits_received' => $coupon->credits_amount,
                'new_balance' => $user->credits_balance,
            ], "Coupon validé ! +{$coupon->credits_amount} crédits.");
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v2/wallet/purchase",
     *     summary="Initialise l'achat d'un pack de crédits via Moneroo",
     *     tags={"Portefeuille"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"pack_id"},
     *
     *             @OA\Property(property="pack_id", type="integer", example=1)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Lien de paiement initialisé",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="checkout_url", type="string", example="https://checkout.moneroo.io/..."),
     *             @OA\Property(property="transaction_id", type="integer")
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Erreur de validation ou pack indisponible")
     * )
     */
    public function purchase(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pack_id' => 'required|exists:credit_packs,id',
        ]);

        $pack = \App\Models\CreditPack::findOrFail($validated['pack_id']);
        $user = $request->user();

        if ($pack->user_type !== ($user->user_type ?? 'jeune')) {
            return $this->error('Ce pack n\'est pas disponible pour votre type de compte.', 422);
        }

        $credits = $pack->credits;
        $amountXOF = $pack->price;

        try {
            $monerooService = app(\App\Services\MonerooService::class);

            $transaction = \App\Models\MonerooTransaction::create([
                'user_id' => $user->id,
                'user_type' => get_class($user),
                'amount' => $amountXOF,
                'currency' => 'XOF',
                'status' => 'pending',
                'credits_amount' => $credits,
                'metadata' => [
                    'user_type' => $user->user_type ?? 'jeune',
                    'user_name' => $user->name,
                    'pack_id' => $pack->id,
                    'pack_name' => $pack->name,
                ],
            ]);

            $nameParts = $monerooService->splitName($user->name);

            $paymentData = $monerooService->initializePayment(
                amount: $amountXOF,
                description: "Achat : {$pack->name} ({$credits} crédits)",
                customer: [
                    'email' => $user->email,
                    'first_name' => $nameParts['first_name'],
                    'last_name' => $nameParts['last_name'],
                    'phone' => $user->phone ?? null,
                ],
                metadata: [
                    'transaction_id' => $transaction->id,
                    'user_id' => $user->id,
                    'user_type' => $user->user_type ?? 'jeune',
                    'credits' => $credits,
                    'pack_id' => $pack->id,
                ],
                returnUrl: route('payments.callback')
            );

            $transaction->update([
                'moneroo_transaction_id' => $paymentData['id'],
            ]);

            return $this->success([
                'checkout_url' => $paymentData['checkout_url'],
                'transaction_id' => $transaction->id,
            ], 'Paiement initialisé avec succès.');

        } catch (\Exception $e) {
            \Log::error('Moneroo payment initialization failed from API', [
                'user_id' => $user->id,
                'pack_id' => $pack->id,
                'error' => $e->getMessage(),
            ]);

            return $this->error('Une erreur est survenue lors de l\'initialisation du paiement.', 500);
        }
    }

    /**
     * Formate une transaction pour l'API
     */
    private function formatTransaction($transaction): array
    {
        return [
            'id' => $transaction->id,
            'amount' => $transaction->amount,
            'type' => $transaction->type,
            'description' => $transaction->description,
            'created_at' => $transaction->created_at->toISOString(),
            'related_id' => $transaction->related_id,
            'related_type' => $transaction->related_type ? class_basename($transaction->related_type) : null,
        ];
    }
}
