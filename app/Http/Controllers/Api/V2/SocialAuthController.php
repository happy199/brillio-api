<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Auth\WebAuthController;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use App\Services\MentorshipNotificationService;
use App\Services\SupabaseAuthService;
use App\Services\UserAvatarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

/**
 * Controller pour l'authentification sociale (Google, LinkedIn) via API
 *
 * L'app mobile obtient un token OAuth (via Supabase PKCE ou SDK natif),
 * puis l'envoie à ces endpoints pour recevoir en retour un Sanctum token.
 *
 * @OA\Tag(name="Authentification Sociale", description="OAuth social pour l'application mobile")
 */
class SocialAuthController extends WebAuthController
{
    public function __construct(
        SupabaseAuthService $supabase,
        UserAvatarService $avatarService,
        MentorshipNotificationService $notificationService
    ) {
        parent::__construct($supabase, $avatarService, $notificationService);
    }

    /**
     * @OA\Get(
     *     path="/api/v2/auth/social/{provider}/url",
     *     summary="Obtenir l'URL OAuth pour initier l'authentification sociale",
     *     description="Génère l'URL Supabase OAuth pour que l'app mobile puisse ouvrir le navigateur et lancer le flow PKCE. Après l'authentification, Supabase redirige vers le redirect_uri avec un code ou access_token.",
     *     tags={"Authentification Sociale"},
     *
     *     @OA\Parameter(
     *         name="provider",
     *         in="path",
     *         required=true,
     *         description="Le provider OAuth (google pour jeune, linkedin pour mentor)",
     *
     *         @OA\Schema(type="string", enum={"google", "linkedin"})
     *     ),
     *
     *     @OA\Parameter(
     *         name="redirect_uri",
     *         in="query",
     *         required=true,
     *         description="Deep link de l'app mobile où Supabase redirigera après authentification",
     *
     *         @OA\Schema(type="string", example="myapp://auth/callback")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="URL générée avec succès",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="url", type="string", example="https://xxx.supabase.co/auth/v1/authorize?provider=google&..."),
     *                 @OA\Property(property="provider", type="string", example="google")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=400, description="Provider non supporté ou redirect_uri manquant"),
     *     @OA\Response(response=422, description="Paramètres de validation invalides")
     * )
     */
    public function getOAuthUrl(Request $request, string $provider): JsonResponse
    {
        $validated = $request->validate([
            'redirect_uri' => 'required|string|max:2048',
        ]);

        if (! in_array($provider, ['google', 'linkedin'])) {
            return $this->error('Provider non supporté. Utilisez "google" ou "linkedin".', 400);
        }

        $redirectUri = $validated['redirect_uri'];

        if ($provider === 'linkedin') {
            $scopes = ['openid', 'profile', 'email'];
            $supabaseProvider = 'linkedin_oidc';
        } else {
            $scopes = [];
            $supabaseProvider = 'google';
        }

        $url = app(SupabaseAuthService::class)->getOAuthUrl($supabaseProvider, $redirectUri, $scopes);

        return $this->success([
            'url' => $url,
            'provider' => $provider,
        ], 'URL OAuth générée avec succès');
    }

    /**
     * @OA\Post(
     *     path="/api/v2/auth/social/{provider}",
     *     summary="Authentifier via token OAuth social (Google ou LinkedIn)",
     *     description="Échange un token Supabase OAuth (obtenu côté mobile) contre un token Sanctum de l'API Brillio. Crée le compte si l'utilisateur n'existe pas encore.",
     *     tags={"Authentification Sociale"},
     *
     *     @OA\Parameter(
     *         name="provider",
     *         in="path",
     *         required=true,
     *         description="Le provider OAuth",
     *
     *         @OA\Schema(type="string", enum={"google", "linkedin"})
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"provider_token"},
     *
     *             @OA\Property(
     *                 property="provider_token",
     *                 type="string",
     *                 description="Access token Supabase obtenu après le flow OAuth côté mobile"
     *             ),
     *             @OA\Property(
     *                 property="user_type",
     *                 type="string",
     *                 enum={"jeune", "mentor"},
     *                 description="Type de compte. Défaut : 'jeune' pour Google, 'mentor' pour LinkedIn.",
     *                 example="jeune"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Authentification réussie",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Authentification réussie"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="token", type="string"),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="is_new_user", type="boolean", example=false),
     *                 @OA\Property(property="onboarding_required", type="boolean", example=false)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=400, description="Token invalide ou provider non supporté"),
     *     @OA\Response(response=401, description="Compte bloqué ou accès refusé"),
     *     @OA\Response(response=409, description="Conflit de type de compte (compte actif d'un autre type existe déjà)"),
     *     @OA\Response(response=422, description="Paramètres invalides")
     * )
     */
    public function authenticate(Request $request, string $provider): JsonResponse
    {
        if (! in_array($provider, ['google', 'linkedin'])) {
            return $this->error('Provider non supporté. Utilisez "google" ou "linkedin".', 400);
        }

        $validated = $request->validate([
            'provider_token' => 'required|string|max:4096',
            'user_type' => 'nullable|string|in:jeune,mentor',
        ]);

        $providerToken = $validated['provider_token'];
        $requestedUserType = $validated['user_type'] ?? ($provider === 'linkedin' ? 'mentor' : 'jeune');

        // Récupérer les infos utilisateur depuis Supabase via le token
        $supabase = app(SupabaseAuthService::class);
        $userData = $supabase->getUser($providerToken);

        if (! $userData) {
            Log::warning('SocialAuth: Invalid provider_token', [
                'provider' => $provider,
                'ip' => $request->ip(),
            ]);

            return $this->error(
                'Token OAuth invalide ou expiré. Veuillez relancer le processus de connexion.',
                400
            );
        }

        Log::info('SocialAuth: Token validated', [
            'provider' => $provider,
            'email' => $userData['email'] ?? 'unknown',
            'user_type' => $requestedUserType,
        ]);

        // Déléguer à la logique métier existante (réutilise WebAuthController)
        try {
            if ($provider === 'linkedin' || $requestedUserType === 'mentor') {
                $result = $this->handleMentorSocialAuth($userData, $provider);
            } else {
                $result = $this->handleJeuneSocialAuth($userData, $provider);
            }
        } catch (\Exception $e) {
            Log::error('SocialAuth: Unexpected error', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->error('Une erreur inattendue est survenue. Veuillez réessayer.', 500);
        }

        if (! $result['success']) {
            // Conflit de type de compte (compte actif d'un autre type)
            $statusCode = isset($result['redirect_url']) ? 409 : 401;
            $extra = isset($result['redirect_url']) ? ['conflict_redirect_url' => $result['redirect_url']] : null;

            return $this->error($result['error'], $statusCode, $extra);
        }

        // Migration inter-type nécessaire (compte archivé d'un autre type)
        if ($result['success'] === 'redirect_confirm') {
            return $this->error(
                "Un compte archivé d'un autre type existe avec cet email. Une migration de compte est nécessaire.",
                409,
                ['migration_redirect' => $result['redirect'] ?? null]
            );
        }

        /** @var User $user */
        $user = User::where('email', $userData['email'])->first();

        if (! $user) {
            return $this->error('Impossible de récupérer le compte utilisateur après authentification.', 500);
        }

        // Générer le Sanctum token pour l'app mobile
        $token = $user->createToken('mobile_social_auth_'.$provider)->plainTextToken;

        $user->load(['personalityTest', 'mentorProfile.roadmapSteps']);

        return $this->success([
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
            'is_new_user' => ! $user->onboarding_completed,
            'onboarding_required' => ! $user->onboarding_completed,
        ], 'Authentification réussie');
    }

    /**
     * Gère l'authentification sociale pour les jeunes (Google)
     * Utilise la logique de WebAuthController sans la partie session/redirect web
     */
    private function handleJeuneSocialAuth(array $userData, string $provider): array
    {
        return $this->createOrUpdateJeuneUser($userData, $provider);
    }

    /**
     * Gère l'authentification sociale pour les mentors (LinkedIn)
     * Utilise la logique de WebAuthController sans la partie session/redirect web
     */
    private function handleMentorSocialAuth(array $userData, string $provider): array
    {
        return $this->createOrUpdateMentorUser($userData);
    }
}
