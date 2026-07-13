<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use App\Services\MentorshipNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use OpenApi\Annotations as OA;

/**
 * Controller pour l'authentification API (V1)
 */
class AuthController extends Controller
{
    public function __construct(
        private MentorshipNotificationService $notificationService
    ) {}

    /**
     * @OA\Post(
     * path="/api/v1/register",
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
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'user_type' => $validated['user_type'],
            'phone' => $validated['phone'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'country' => $validated['country'] ?? null,
            'city' => $validated['city'] ?? null,
        ]);

        // Envoyer l'email de bienvenue
        try {
            $this->notificationService->sendWelcomeEmail($user);
        } catch (\Exception $e) {
            \Log::error('Erreur envoi email bienvenue API: '.$e->getMessage());
        }

        // Créer le token d'accès
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->created([
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Inscription réussie');
    }

    /**
     * @OA\Post(
     * path="/api/v1/login",
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
        $validated = $request->validated();

        // Vérifier les credentials
        if (! Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']])) {
            return $this->unauthorized('Email ou mot de passe incorrect');
        }

        $user = User::where('email', $validated['email'])->first();

        // Supprimer les anciens tokens si demandé (connexion unique)
        if ($request->boolean('logout_other_devices', false)) {
            $user->tokens()->delete();
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Connexion réussie');
    }

    /**
     * @OA\Post(
     * path="/api/v1/logout",
     * summary= "Déconnexion de l'utilisateur",
     * tags={"Authentification"},
     *
     * @OA\Response(response= 200, description="Déconnexion réussie"),
     * @OA\Response(response= 401, description="Non authentifié"),
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        // Supprimer le token actuel
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Déconnexion réussie');
    }

    /**
     * @OA\Get(
     * path="/api/v1/user",
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
        $user = $request->user();
        $user->load(['personalityTest', 'mentorProfile.roadmapSteps']);

        return $this->success([
            'user' => new UserResource($user),
        ]);
    }

    /**
     * @OA\Post(
     * path="/api/v1/user/profile",
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
        $user = $request->user();
        $validated = $request->validated();

        // Mise à jour des champs autorisés
        $user->fill($validated);

        // Mise à jour du mot de passe si fourni
        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return $this->success([
            'user' => $this->formatUser($user),
        ], 'Profil mis à jour avec succès');
    }

    /**
     * @OA\Post(
     * path="/api/v1/user/photo",
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
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $user = $request->user();

        // Supprimer l'ancienne photo si elle existe
        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        // Stocker la nouvelle photo
// nosemgrep
        $path = $request->file('photo')->store('profile-photos', 'public');

        $user->profile_photo_path = $path;
        $user->save();

        return $this->success([
            'profile_photo_url' => $user->profile_photo_url,
        ], 'Photo de profil mise à jour');
    }

    /**
     * @OA\Delete(
     * path="/api/v1/user/photo",
     * summary="Supprime la photo de profil",
     * tags={"Profil"},
     *
     * @OA\Response(response= 200, description="Photo supprimée"),
     * @OA\Response(response= 401, description="Non authentifié"),
     * )
     */
    public function deletePhoto(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
            $user->profile_photo_path = null;
            $user->save();
        }

        return $this->success(null, 'Photo de profil supprimée');
    }

    /**
     * Formate les données utilisateur pour la réponse API
     */
    private function formatUser(User $user): array
    {
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'user_type' => $user->user_type,
            'phone' => $user->phone,
            'date_of_birth' => $user->date_of_birth?->format('Y-m-d'),
            'country' => $user->country,
            'city' => $user->city,
            'profile_photo_url' => $user->profile_photo_url,
            'linkedin_url' => $user->linkedin_url,
            'email_verified_at' => $user->email_verified_at?->toISOString(),
            'created_at' => $user->created_at->toISOString(),
        ];

        // Ajouter le test de personnalité si disponible
        if ($user->relationLoaded('personalityTest') && $user->personalityTest) {
            $data['personality_test'] = [
                'type' => $user->personalityTest->personality_type,
                'label' => $user->personalityTest->personality_label,
                'completed_at' => $user->personalityTest->completed_at?->toISOString(),
            ];
        }

        // Ajouter le profil mentor si disponible
        if ($user->relationLoaded('mentorProfile') && $user->mentorProfile) {
            $data['mentor_profile'] = [
                'id' => $user->mentorProfile->id,
                'is_published' => $user->mentorProfile->is_published,
                'specialization' => $user->mentorProfile->specialization,
            ];
        }

        return $data;
    }
}
