<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * Controller pour la gestion du compte utilisateur via API
 */
class AccountController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v2/account/archive",
     *     summary="Archiver le compte de l'utilisateur",
     *     tags={"Mon Compte"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(response=200, description="Compte archivé avec succès")
     * )
     */
    public function archive(Request $request): JsonResponse
    {
        $user = $request->user();

        // Soft delete the user
        $user->update(['is_archived' => true]);

        // Optional: revoke tokens
        $user->tokens()->delete();

        return $this->success(null, 'Compte archivé avec succès.');
    }

    /**
     * @OA\Put(
     *     path="/api/v2/account/password",
     *     summary="Mettre à jour le mot de passe de l'utilisateur",
     *     tags={"Mon Compte"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"current_password", "password", "password_confirmation"},
     *
     *             @OA\Property(property="current_password", type="string", format="password"),
     *             @OA\Property(property="password", type="string", format="password", example="NouveauPasse123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="NouveauPasse123")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Mot de passe mis à jour avec succès")
     * )
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();
        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);

        return $this->success(null, 'Mot de passe mis à jour avec succès.');
    }
}
