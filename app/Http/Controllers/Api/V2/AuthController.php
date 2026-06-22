<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\V1\AuthController as V1AuthController;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Models\User;
use App\Services\MentorshipNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;

/**
 * Controller pour l'authentification API
 */
class AuthController extends V1AuthController
{
    public function __construct(
        private MentorshipNotificationService $notificationService
    ) {
        parent::__construct($notificationService);
    }

    /**
     * @OA\Post(
     * path="/api/v2/register",
     * summary= "Inscription d'un nouvel utilisateur",
     * tags={"Authentification"},
     *
     * @OA\RequestBody(
     * required= true,
     *
     * @OA\JsonContent(
     * required={"name", "email", "password", "password_confirmation", "user_type"},
     *
     * @OA\Property(property="name", type="string", example="Jean Dupont"),
     * @OA\Property(property="email", type="string", format="email", example="jean@example.com"),
     * @OA\Property(property="password", type="string", format="password", example="Password123"),
     * @OA\Property(property="password_confirmation", type="string", format="password", example="Password123"),
     * @OA\Property(property="user_type", type="string", enum={"jeune", "mentor"}, example="jeune"),
     * @OA\Property(property="phone", type="string", example="+2250102030405"),
     * @OA\Property(property="date_of_birth", type="string", format="date", example="2000-01-01"),
     * @OA\Property(property="country", type="string", example= "Côte d'Ivoire"),
     * @OA\Property(property="city", type="string", example="Abidjan"),
     * )
     * ),
     *
     * @OA\Response(
     * response= 201,
     * description="Utilisateur créé avec succès",
     *
     * @OA\JsonContent(
     *
     * @OA\Property(property="success", type="boolean", example= true),
     * @OA\Property(property="message", type="string", example="Inscription réussie"),
     * @OA\Property(property="data", type="object",
     * @OA\Property(property="user", ref="#/components/schemas/User"),
     * @OA\Property(property="token", type="string"),
     * @OA\Property(property="token_type", type="string", example="Bearer"),
     * ),
     * )
     * ),
     *
     * @OA\Response(response= 422, description="Erreur de validation"),
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        return parent::register($request);
    }

    /**
     * @OA\Post(
     * path="/api/v2/login",
     * summary= "Connexion d'un utilisateur existant",
     * tags={"Authentification"},
     *
     * @OA\RequestBody(
     * required= true,
     *
     * @OA\JsonContent(
     * required={"email", "password"},
     *
     * @OA\Property(property="email", type="string", format="email", example="jean@example.com"),
     * @OA\Property(property="password", type="string", format="password", example="Password123"),
     * @OA\Property(property="logout_other_devices", type="boolean", example= false),
     * )
     * ),
     *
     * @OA\Response(
     * response= 200,
     * description="Connexion réussie",
     *
     * @OA\JsonContent(
     *
     * @OA\Property(property="success", type="boolean", example= true),
     * @OA\Property(property="message", type="string", example="Connexion réussie"),
     * @OA\Property(property="data", type="object",
     * @OA\Property(property="user", ref="#/components/schemas/User"),
     * @OA\Property(property="token", type="string"),
     * @OA\Property(property="token_type", type="string", example="Bearer"),
     * ),
     * )
     * ),
     *
     * @OA\Response(response= 401, description="Email ou mot de passe incorrect"),
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        return parent::login($request);
    }

    /**
     * @OA\Post(
     * path="/api/v2/logout",
     * summary= "Déconnexion de l'utilisateur",
     * tags={"Authentification"},
     *
     * @OA\Response(response= 200, description="Déconnexion réussie"),
     * @OA\Response(response= 401, description="Non authentifié"),
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        return parent::logout($request);
    }

    /**
     * @OA\Post(
     *     path="/api/v2/password/email",
     *     summary="Demander un lien de réinitialisation de mot de passe",
     *     tags={"Authentification"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email"},
     *
     *             @OA\Property(property="email", type="string", format="email", example="jean@example.com")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Lien envoyé par e-mail avec succès",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Un lien de réinitialisation vous a été envoyé par email.")
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Adresse e-mail invalide ou non trouvée")
     * )
     */
    public function sendResetLinkEmail(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return $this->error('Aucun utilisateur trouvé avec cette adresse email.', 422);
        }

        $token = Str::random(60);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        try {
            $this->notificationService->sendPasswordResetEmail($user, $token);
        } catch (\Exception $e) {
            Log::error('Erreur envoi email reset password: '.$e->getMessage());

            return $this->error('Une erreur est survenue lors de l\'envoi de l\'email.', 500);
        }

        return $this->success(null, 'Un lien de réinitialisation vous a été envoyé par email.');
    }

    /**
     * @OA\Post(
     *     path="/api/v2/password/reset",
     *     summary="Réinitialiser le mot de passe avec le token reçu",
     *     tags={"Authentification"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"token", "email", "password", "password_confirmation"},
     *
     *             @OA\Property(property="token", type="string", example="token_received_in_email"),
     *             @OA\Property(property="email", type="string", format="email", example="jean@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="NewPassword123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="NewPassword123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Mot de passe réinitialisé avec succès",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Votre mot de passe a été réinitialisé avec succès.")
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Erreur de validation ou token expiré/invalide")
     * )
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ], [
            'password.min' => 'Le mot de passe doit contenir au moins 8 caracteres.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
        ]);

        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        $errorMsg = null;

        if (! $resetRecord || ! Hash::check($request->token, $resetRecord->token)) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            $errorMsg = 'Ce lien de réinitialisation est invalide ou a expiré.';
        } elseif (now()->subMinutes(60)->gt($resetRecord->created_at)) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            $errorMsg = 'Ce lien de réinitialisation a expiré.';
        } else {
            $user = User::where('email', $request->email)->first();
            if (! $user) {
                $errorMsg = 'Aucun utilisateur trouvé avec cette adresse email.';
            } else {
                $user->password = Hash::make($request->password);
                $user->save();
                DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            }
        }

        if ($errorMsg) {
            return $this->error($errorMsg, 422);
        }

        return $this->success(null, 'Votre mot de passe a été réinitialisé avec succès.');
    }

    /**
     * @OA\Get(
     * path="/api/v2/user",
     * summary= "Récupère le profil de l'utilisateur connecté",
     * tags={"Profil"},
     *
     * @OA\Response(
     * response= 200,
     * description="Profil utilisateur récupéré",
     *
     * @OA\JsonContent(
     *
     * @OA\Property(property="success", type="boolean", example= true),
     * @OA\Property(property="data", type="object",
     * @OA\Property(property="user", ref="#/components/schemas/User"),
     * ),
     * )
     * ),
     *
     * @OA\Response(response= 401, description="Non authentifié"),
     * )
     */
    public function user(Request $request): JsonResponse
    {
        return parent::user($request);
    }

    /**
     * @OA\Post(
     * path="/api/v2/user/profile",
     * summary= "Met à jour le profil de l'utilisateur",
     * tags={"Profil"},
     *
     * @OA\RequestBody(
     * required= true,
     *
     * @OA\JsonContent(
     *
     * @OA\Property(property="name", type="string", example="Jean Dupont"),
     * @OA\Property(property="phone", type="string", example="+2250102030405"),
     * @OA\Property(property="password", type="string", format="password"),
     * )
     * ),
     *
     * @OA\Response(response= 200, description="Profil mis à jour"),
     * @OA\Response(response= 401, description="Non authentifié"),
     * @OA\Response(response= 422, description="Erreur de validation"),
     * )
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        return parent::updateProfile($request);
    }

    /**
     * @OA\Post(
     * path="/api/v2/user/photo",
     * summary="Upload de la photo de profil",
     * tags={"Profil"},
     *
     * @OA\RequestBody(
     * required= true,
     *
     * @OA\MediaType(
     * mediaType="multipart/form-data",
     *
     * @OA\Schema(
     *
     * @OA\Property(property="photo", type="string", format="binary"),
     * )
     * )
     * ),
     *
     * @OA\Response(response= 200, description="Photo mise à jour"),
     * @OA\Response(response= 401, description="Non authentifié"),
     * )
     */
    public function uploadPhoto(Request $request): JsonResponse
    {
        return parent::uploadPhoto($request);
    }

    /**
     * @OA\Delete(
     * path="/api/v2/user/photo",
     * summary="Supprime la photo de profil",
     * tags={"Profil"},
     *
     * @OA\Response(response= 200, description="Photo supprimée"),
     * @OA\Response(response= 401, description="Non authentifié"),
     * )
     */
    public function deletePhoto(Request $request): JsonResponse
    {
        return parent::deletePhoto($request);
    }
}
