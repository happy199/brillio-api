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
use OpenApi\Attributes as OA;

/**
 * Controller pour l'authentification API
 *
 * Gère l'inscription, connexion, déconnexion et gestion du profil
 */
class AuthController extends Controller
{
    public function __construct(
        private MentorshipNotificationService $notificationService
    ) {}

    #[OA\Post(
        path: "/api/register",
        summary: "Inscription d'un nouvel utilisateur",
        tags: ["Authentification"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password", "password_confirmation", "user_type"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Jean Dupont"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "jean@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "Password123"),
                    new OA\Property(property: "password_confirmation", type: "string", format: "password", example: "Password123"),
                    new OA\Property(property: "user_type", type: "string", enum: ["jeune", "mentor"], example: "jeune"),
                    new OA\Property(property: "phone", type: "string", example: "+2250102030405"),
                    new OA\Property(property: "date_of_birth", type: "string", format: "date", example: "2000-01-01"),
                    new OA\Property(property: "country", type: "string", example: "Côte d'Ivoire"),
                    new OA\Property(property: "city", type: "string", example: "Abidjan")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Utilisateur créé avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Inscription réussie"),
                        new OA\Property(property: "data", type: "object",
                            properties: [
                                new OA\Property(property: "user", ref: "#/components/schemas/User"),
                                new OA\Property(property: "token", type: "string"),
                                new OA\Property(property: "token_type", type: "string", example: "Bearer")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Erreur de validation")
        ]
    )]
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

    #[OA\Post(
        path: "/api/login",
        summary: "Connexion d'un utilisateur existant",
        tags: ["Authentification"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "jean@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "Password123"),
                    new OA\Property(property: "logout_other_devices", type: "boolean", example: false)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Connexion réussie",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Connexion réussie"),
                        new OA\Property(property: "data", type: "object",
                            properties: [
                                new OA\Property(property: "user", ref: "#/components/schemas/User"),
                                new OA\Property(property: "token", type: "string"),
                                new OA\Property(property: "token_type", type: "string", example: "Bearer")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Email ou mot de passe incorrect")
        ]
    )]
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
     * Déconnexion de l'utilisateur
     */
    #[OA\Post(
        path: "/api/logout",
        summary: "Déconnexion de l'utilisateur",
        tags: ["Authentification"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Déconnexion réussie"),
            new OA\Response(response: 401, description: "Non authentifié")
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        // Supprimer le token actuel
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Déconnexion réussie');
    }

    #[OA\Get(
        path: "/api/user",
        summary: "Récupère le profil de l'utilisateur connecté",
        tags: ["Profil"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Profil utilisateur récupéré",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", type: "object",
                            properties: [
                                new OA\Property(property: "user", ref: "#/components/schemas/User")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié")
        ]
    )]
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['personalityTest', 'mentorProfile.roadmapSteps']);

        return $this->success([
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Met à jour le profil de l'utilisateur
     */
    #[OA\Post(
        path: "/api/user/profile",
        summary: "Met à jour le profil de l'utilisateur",
        tags: ["Profil"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Jean Dupont"),
                    new OA\Property(property: "phone", type: "string", example: "+2250102030405"),
                    new OA\Property(property: "password", type: "string", format: "password")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Profil mis à jour"),
            new OA\Response(response: 401, description: "Non authentifié"),
            new OA\Response(response: 422, description: "Erreur de validation")
        ]
    )]
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
     * Upload de la photo de profil
     */
    #[OA\Post(
        path: "/api/user/photo",
        summary: "Upload de la photo de profil",
        tags: ["Profil"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "photo", type: "string", format: "binary")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Photo mise à jour"),
            new OA\Response(response: 401, description: "Non authentifié")
        ]
    )]
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
        $path = $request->file('photo')->store('profile-photos', 'public');

        $user->profile_photo_path = $path;
        $user->save();

        return $this->success([
            'profile_photo_url' => $user->profile_photo_url,
        ], 'Photo de profil mise à jour');
    }

    #[OA\Delete(
        path: "/api/user/photo",
        summary: "Supprime la photo de profil",
        tags: ["Profil"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Photo supprimée"),
            new OA\Response(response: 401, description: "Non authentifié")
        ]
    )]
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