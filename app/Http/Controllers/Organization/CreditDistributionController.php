<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreditDistributionController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Distribute credits to sponsored users
     */
    public function distribute(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:1',
            'user_ids' => 'required_unless:target,all|array',
            'user_ids.*' => 'exists:users,id',
            'target' => 'nullable|string|in:all,selection,single',
        ]);

        $organization = $this->getCurrentOrganization();
        $amountPerUser = $request->amount;

        // Determine target users
        if ($request->target === 'all') {
            $targetUsers = $organization->sponsoredUsers()->get();
        } else {
            $targetUsers = User::whereIn('id', $request->user_ids)
                ->where(function ($q) use ($organization) {
                    $q->where('sponsored_by_organization_id', $organization->id)
                        ->orWhereHas('organizations', function ($sq) use ($organization) {
                            $sq->where('organizations.id', $organization->id);
                        }
                        );
                })
                ->get();
        }

        $userCount = $targetUsers->count();
        if ($userCount === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun utilisateur cible trouvé.',
            ], 422);
        }

        $totalCost = $amountPerUser * $userCount;

        // Check organization balance
        if ($organization->credits_balance < $totalCost) {
            return response()->json([
                'success' => false,
                'insufficient_funds' => true,
                'current_balance' => $organization->credits_balance,
                'required_balance' => $totalCost,
                'message' => "Solde insuffisant. Vous avez besoin de {$totalCost} crédits pour cette distribution, mais votre solde actuel est de {$organization->credits_balance}.",
            ], 422);
        }

        try {
            DB::transaction(function () use ($organization, $targetUsers, $amountPerUser, $totalCost) {
                // 1. Deduct from organization
                $this->walletService->deductCredits(
                    $organization,
                    $totalCost,
                    'distribution',
                    "Distribution de crédits à {$targetUsers->count()} jeunes parrainés"
                );

                // 2. Add to each user
                foreach ($targetUsers as $targetUser) {
                    $this->walletService->addCredits(
                        $targetUser,
                        $amountPerUser,
                        'gift',
                        "Crédits offerts par {$organization->name}",
                        $organization
                    );

                    // Notification par email
                    app(\App\Services\MentorshipNotificationService::class)->sendCreditGiftedNotification($targetUser, $organization, $amountPerUser);
                }
            });

            return response()->json([
                'success' => true,
                'message' => "Félicitations ! {$totalCost} crédits ont été distribués avec succès à {$userCount} jeunes.",
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la distribution : '.$e->getMessage(),
            ], 500);
        }
    }
}
