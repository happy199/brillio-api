<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\V1\WalletController as V1WalletController;
use App\Models\CreditPack;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use OpenApi\Annotations as OA;

/**
 * Controller pour la gestion du portefeuille via API
 */
class WalletController extends V1WalletController
{
    public function __construct(
        WalletService $walletService
    ) {
        parent::__construct($walletService);
    }

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
        return parent::index($request);
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
        return parent::packs($request);
    }

    /**
     * @OA\Post(
     * path="/api/v2/wallet/redeem",
     * summary="Utiliser un coupon pour obtenir des crédits",
     * tags={"Portefeuille"},
     *
     * @OA\RequestBody(
     * required= true,
     *
     * @OA\JsonContent(
     * required={"code"},
     *
     * @OA\Property(property="code", type="string", example="MONCOUPON"),
     * )
     * ),
     *
     * @OA\Response(response= 200, description="Coupon validé avec succès"),
     * @OA\Response(response= 400, description="Coupon invalide ou déjà utilisé"),
     * @OA\Response(response= 500, description="Erreur serveur"),
     * )
     */
    public function redeemCoupon(Request $request): JsonResponse
    {
        return parent::redeemCoupon($request);
    }

    /**
     * @OA\Post(
     *     path="/api/v2/wallet/purchase",
     *     summary="Initialise un achat de pack de crédits (Mobile App WebView Checkout)",
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
     *             @OA\Property(property="transaction_id", type="string", example="tx_123456")
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Erreur de validation ou pack inexistant")
     * )
     */
    public function purchase(Request $request): JsonResponse
    {
        $request->validate([
            'pack_id' => 'required|exists:credit_packs,id',
        ]);

        $user = $request->user();
        $pack = CreditPack::findOrFail($request->pack_id);

        if (! $pack->is_active || $pack->user_type !== $user->user_type) {
            return $this->error('Ce pack n\'est pas disponible.', 422);
        }

        // Utilisation de la configuration Moneroo
        $monerooKey = config('services.moneroo.secret_key');
        if (empty($monerooKey)) {
            return $this->error('Le service de paiement n\'est pas configuré.', 500);
        }

        // Appel à l'API Moneroo pour initialiser le paiement
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$monerooKey,
            'Accept' => 'application/json',
        ])->post('https://api.moneroo.io/v1/payments/initialize', [
            'amount' => (int) ($pack->price),
            'currency' => 'XOF',
            'description' => "Achat de {$pack->credits_amount} crédits Brillio",
            'customer' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'metadata' => [
                'user_id' => $user->id,
                'pack_id' => $pack->id,
            ],
            'return_url' => $user->isMentor() ? route('mentor.wallet.index') : route('jeune.wallet.index'), // fallback redirection
        ]);

        if (! $response->successful()) {
            Log::error('Moneroo Initialization Failure: '.$response->body());

            return $this->error('Impossible d\'initialiser le paiement pour le moment.', 500);
        }

        $resData = $response->json();

        return $this->success([
            'checkout_url' => $resData['data']['checkout_url'],
            'transaction_id' => $resData['data']['id'],
        ], 'Paiement initialisé.');
    }
}
