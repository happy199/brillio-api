<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="Onboarding", description="Gestion de l'onboarding utilisateur")
 */
class OnboardingController extends Controller
{
    use \App\Traits\HandlesAfricanPhoneNumbers;

    private const COTE_D_IVOIRE = "Cote d'Ivoire";

    /**
     * @OA\Get(
     *     path="/api/v2/onboarding",
     *     summary="Récupère les options de configuration pour l'onboarding",
     *     tags={"Onboarding"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Options d'onboarding récupérées",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="countries", type="object", example={"BJ": "Benin", "CI": "Cote d'Ivoire"}),
     *                 @OA\Property(property="education_levels", type="array", @OA\Items(type="string"), example={"college", "lycee", "bac", "licence", "master", "doctorat"}),
     *                 @OA\Property(property="situations", type="array", @OA\Items(type="string"), example={"etudiant", "recherche_emploi", "emploi", "entrepreneur", "autre"})
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        return $this->success([
            'countries' => $this->getAfricanCountries(),
            'education_levels' => ['college', 'lycee', 'bac', 'licence', 'master', 'doctorat'],
            'situations' => ['etudiant', 'recherche_emploi', 'emploi', 'entrepreneur', 'autre'],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v2/onboarding/complete",
     *     summary="Valide et complète l'onboarding d'un jeune",
     *     tags={"Onboarding"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"birth_date", "country", "city", "phone", "education_level", "current_situation", "interests", "goals", "how_found_us"},
     *
     *             @OA\Property(property="birth_date", type="string", format="date", example="2002-05-15"),
     *             @OA\Property(property="country", type="string", example="Benin"),
     *             @OA\Property(property="city", type="string", example="Cotonou"),
     *             @OA\Property(property="phone", type="string", example="+22990909090"),
     *             @OA\Property(property="education_level", type="string", enum={"college", "lycee", "bac", "licence", "master", "doctorat"}, example="licence"),
     *             @OA\Property(property="current_situation", type="string", enum={"etudiant", "recherche_emploi", "emploi", "entrepreneur", "autre"}, example="etudiant"),
     *             @OA\Property(property="current_situation_other", type="string", example="Autre situation"),
     *             @OA\Property(property="interests", type="array", @OA\Items(type="string"), example={"informatique", "design", "marketing", "finance", "entrepreneuriat"}),
     *             @OA\Property(property="goals", type="array", @OA\Items(type="string"), example={"trouver_un_emploi"}),
     *             @OA\Property(property="how_found_us", type="string", example="social_media"),
     *             @OA\Property(property="how_found_us_other", type="string", example="Un ami")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Onboarding complété avec succès",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Onboarding complété avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function complete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'birth_date' => 'required|date|before:today',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'phone' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($request) {
                    $country = $request->input('country');
                    if (empty($country)) {
                        return;
                    }
                    if (! $this->isValidAfricanPhoneNumber($country, $value)) {
                        $fail("Le champ $attribute n'est pas un numéro de téléphone valide pour le pays sélectionné.");
                    }
                },
            ],
            'education_level' => 'required|string|in:college,lycee,bac,licence,master,doctorat',
            'current_situation' => 'required|string|in:etudiant,recherche_emploi,emploi,entrepreneur,autre',
            'current_situation_other' => 'nullable|string|max:255',
            'interests' => 'required|array|size:5',
            'interests.*' => 'string',
            'goals' => 'required|array|min:1|max:3',
            'goals.*' => 'string',
            'how_found_us' => 'required|string',
            'how_found_us_other' => 'nullable|string|max:255',
        ]);

        $user = $request->user();

        // Préparer les données d'onboarding
        $onboardingData = [
            'education_level' => $validated['education_level'],
            'current_situation' => $validated['current_situation'],
            'interests' => $validated['interests'],
            'goals' => $validated['goals'],
            'how_found_us' => $validated['how_found_us'],
            'completed_at' => now()->toISOString(),
        ];

        if ($validated['current_situation'] === 'autre' && ! empty($validated['current_situation_other'])) {
            $onboardingData['current_situation_other'] = $validated['current_situation_other'];
        }

        if ($validated['how_found_us'] === 'other' && ! empty($validated['how_found_us_other'])) {
            $onboardingData['how_found_us_other'] = $validated['how_found_us_other'];
        }

        $normalizedPhone = $this->normalizeToE164($validated['country'], $validated['phone']);

        $user->update([
            'date_of_birth' => $validated['birth_date'],
            'country' => $validated['country'],
            'city' => $validated['city'],
            'phone' => $normalizedPhone,
            'onboarding_completed' => true,
            'onboarding_data' => $onboardingData,
        ]);

        return $this->success([
            'user' => new UserResource($user),
        ], 'Onboarding complété avec succès');
    }
}
