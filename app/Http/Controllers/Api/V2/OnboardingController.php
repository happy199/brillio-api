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
                        $fail("Le numéro de téléphone n'est pas valide pour le pays sélectionné.");
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

    private function isValidAfricanPhoneNumber(string $country, string $phone): bool
    {
        $configs = $this->getAfricanPhoneConfigs();
        $clean = preg_replace('/\D/', '', $phone);

        if (! isset($configs[$country])) {
            return strlen($clean) >= 7 && strlen($clean) <= 12;
        }

        $config = $configs[$country];
        $dialCode = preg_replace('/\D/', '', $config['code']);

        if (str_starts_with($clean, $dialCode)) {
            $local = substr($clean, strlen($dialCode));
        } elseif (str_starts_with($clean, '00'.$dialCode)) {
            $local = substr($clean, strlen('00'.$dialCode));
        } else {
            $local = $clean;
        }

        if ($country === 'Cote d\'Ivoire' && strlen($local) === 9 && in_array($local[0], ['1', '5', '7'])) {
            $local = '0'.$local;
        }
        if ($country === 'Benin' && strlen($local) === 9 && in_array($local[0], ['1', '4', '5', '6', '9'])) {
            $local = '0'.$local;
        }

        foreach ($config['lengths'] as $len) {
            if (strlen($local) === $len) {
                return true;
            }
            if (strlen($local) === $len + 1 && str_starts_with($local, '0')) {
                return true;
            }
        }

        return false;
    }

    private function normalizeToE164(string $country, string $phone): string
    {
        $configs = $this->getAfricanPhoneConfigs();
        $clean = preg_replace('/\D/', '', $phone);

        if (! isset($configs[$country])) {
            return '+'.$clean;
        }

        $config = $configs[$country];
        $dialCode = preg_replace('/\D/', '', $config['code']);

        if (str_starts_with($clean, $dialCode)) {
            $local = substr($clean, strlen($dialCode));
        } elseif (str_starts_with($clean, '00'.$dialCode)) {
            $local = substr($clean, strlen('00'.$dialCode));
        } else {
            $local = $clean;
        }

        if ($country === 'Cote d\'Ivoire' && strlen($local) === 9 && in_array($local[0], ['1', '5', '7'])) {
            $local = '0'.$local;
        }
        if ($country === 'Benin' && strlen($local) === 9 && in_array($local[0], ['1', '4', '5', '6', '9'])) {
            $local = '0'.$local;
        }

        foreach ($config['lengths'] as $len) {
            if (strlen($local) === $len + 1 && str_starts_with($local, '0')) {
                $local = substr($local, 1);
                break;
            }
        }

        return '+'.$dialCode.$local;
    }

    private function getAfricanPhoneConfigs(): array
    {
        return [
            'Algerie' => ['code' => '+213', 'lengths' => [9]],
            'Angola' => ['code' => '+244', 'lengths' => [9]],
            'Benin' => ['code' => '+229', 'lengths' => [8, 10]],
            'Botswana' => ['code' => '+267', 'lengths' => [7, 8]],
            'Burkina Faso' => ['code' => '+226', 'lengths' => [8]],
            'Burundi' => ['code' => '+257', 'lengths' => [8]],
            'Cap-Vert' => ['code' => '+238', 'lengths' => [7]],
            'Cameroun' => ['code' => '+237', 'lengths' => [9]],
            'Centrafrique' => ['code' => '+236', 'lengths' => [8]],
            'Tchad' => ['code' => '+235', 'lengths' => [8]],
            'Comores' => ['code' => '+269', 'lengths' => [7]],
            'Congo' => ['code' => '+242', 'lengths' => [7, 9]],
            'RD Congo' => ['code' => '+243', 'lengths' => [9]],
            'Djibouti' => ['code' => '+253', 'lengths' => [8]],
            'Egypte' => ['code' => '+20', 'lengths' => [9, 10, 11]],
            'Guinee Equatoriale' => ['code' => '+240', 'lengths' => [9]],
            'Erythree' => ['code' => '+291', 'lengths' => [7]],
            'Eswatini' => ['code' => '+268', 'lengths' => [8]],
            'Ethiopie' => ['code' => '+251', 'lengths' => [9]],
            'Gabon' => ['code' => '+241', 'lengths' => [7, 8, 9]],
            'Gambie' => ['code' => '+220', 'lengths' => [7]],
            'Ghana' => ['code' => '+233', 'lengths' => [9]],
            'Guinee' => ['code' => '+224', 'lengths' => [9]],
            'Guinee-Bissau' => ['code' => '+245', 'lengths' => [7, 9]],
            'Cote d\'Ivoire' => ['code' => '+225', 'lengths' => [10]],
            'Kenya' => ['code' => '+254', 'lengths' => [9]],
            'Lesotho' => ['code' => '+266', 'lengths' => [8]],
            'Liberia' => ['code' => '+231', 'lengths' => [7, 9]],
            'Libye' => ['code' => '+218', 'lengths' => [9, 10]],
            'Madagascar' => ['code' => '+261', 'lengths' => [9]],
            'Malawi' => ['code' => '+265', 'lengths' => [7, 8, 9]],
            'Mali' => ['code' => '+223', 'lengths' => [8]],
            'Mauritanie' => ['code' => '+222', 'lengths' => [8]],
            'Maurice' => ['code' => '+230', 'lengths' => [7, 8]],
            'Maroc' => ['code' => '+212', 'lengths' => [9]],
            'Mozambique' => ['code' => '+258', 'lengths' => [9]],
            'Namibie' => ['code' => '+264', 'lengths' => [8, 9]],
            'Niger' => ['code' => '+227', 'lengths' => [8]],
            'Nigeria' => ['code' => '+234', 'lengths' => [10]],
            'Rwanda' => ['code' => '+250', 'lengths' => [9]],
            'Sao Tome-et-Principe' => ['code' => '+239', 'lengths' => [7]],
            'Senegal' => ['code' => '+221', 'lengths' => [9]],
            'Seychelles' => ['code' => '+248', 'lengths' => [7]],
            'Sierra Leone' => ['code' => '+232', 'lengths' => [8]],
            'Somalie' => ['code' => '+252', 'lengths' => [8, 9]],
            'Afrique du Sud' => ['code' => '+27', 'lengths' => [9]],
            'Soudan du Sud' => ['code' => '+211', 'lengths' => [9]],
            'Soudan' => ['code' => '+249', 'lengths' => [9]],
            'Tanzanie' => ['code' => '+255', 'lengths' => [9]],
            'Togo' => ['code' => '+228', 'lengths' => [8]],
            'Tunisie' => ['code' => '+216', 'lengths' => [8]],
            'Ouganda' => ['code' => '+256', 'lengths' => [9]],
            'Zambie' => ['code' => '+260', 'lengths' => [9]],
            'Zimbabwe' => ['code' => '+263', 'lengths' => [9]],
        ];
    }

    private function getAfricanCountries(): array
    {
        return [
            'DZ' => 'Algerie',
            'AO' => 'Angola',
            'BJ' => 'Benin',
            'BW' => 'Botswana',
            'BF' => 'Burkina Faso',
            'BI' => 'Burundi',
            'CV' => 'Cap-Vert',
            'CM' => 'Cameroun',
            'CF' => 'Centrafrique',
            'TD' => 'Tchad',
            'KM' => 'Comores',
            'CG' => 'Congo',
            'CD' => 'RD Congo',
            'DJ' => 'Djibouti',
            'EG' => 'Egypte',
            'GQ' => 'Guinee Equatoriale',
            'ER' => 'Erythree',
            'SZ' => 'Eswatini',
            'ET' => 'Ethiopie',
            'GA' => 'Gabon',
            'GM' => 'Gambie',
            'GH' => 'Ghana',
            'GN' => 'Guinee',
            'GW' => 'Guinee-Bissau',
            'CI' => 'Cote d\'Ivoire',
            'KE' => 'Kenya',
            'LS' => 'Lesotho',
            'LR' => 'Liberia',
            'LY' => 'Libye',
            'MG' => 'Madagascar',
            'MW' => 'Malawi',
            'ML' => 'Mali',
            'MR' => 'Mauritanie',
            'MU' => 'Maurice',
            'MA' => 'Maroc',
            'MZ' => 'Mozambique',
            'NA' => 'Namibie',
            'NE' => 'Niger',
            'NG' => 'Nigeria',
            'RW' => 'Rwanda',
            'ST' => 'Sao Tome-et-Principe',
            'SN' => 'Senegal',
            'SC' => 'Seychelles',
            'SL' => 'Sierra Leone',
            'SO' => 'Somalie',
            'ZA' => 'Afrique du Sud',
            'SS' => 'Soudan du Sud',
            'SD' => 'Soudan',
            'TZ' => 'Tanzanie',
            'TG' => 'Togo',
            'TN' => 'Tunisie',
            'UG' => 'Ouganda',
            'ZM' => 'Zambie',
            'ZW' => 'Zimbabwe',
        ];
    }
}
