<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

/**
 * Controller pour la gestion du portefeuille via API
 */
class WalletController extends Controller
{
    public function __construct(
        private WalletService $walletService
    ) {}

    /**
     * Récupère le solde et l'historique des transactions
     */
    #[OA\Get(
        path: '/api/v1/wallet',
        summary: 'Récupère le solde et les transactions du portefeuille',
        tags: ['Portefeuille'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Détails du portefeuille'),
        ]
    )]
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
     * Liste les packs de crédits disponibles
     */
    #[OA\Get(
        path: '/api/v1/wallet/packs',
        summary: 'Liste les packs de crédits disponibles',
        tags: ['Portefeuille'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Liste des packs'),
        ]
    )]
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
                'credits_amount' => $p->credits_amount,
                'price_fcfa' => $p->price,
                'description' => $p->description,
            ]),
        ]);
    }

    /**
     * Utiliser un coupon
     */
    #[OA\Post(
        path: '/api/v1/wallet/redeem-coupon',
        summary: 'Utiliser un coupon pour obtenir des crédits',
        tags: ['Portefeuille'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['code'],
                properties: [
                    new OA\Property(property: 'code', type: 'string', example: 'MONCOUPON'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Coupon validé avec succès'),
            new OA\Response(response: 400, description: 'Coupon invalide ou déjà utilisé'),
            new OA\Response(response: 500, description: 'Erreur serveur'),
        ]
    )]
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
