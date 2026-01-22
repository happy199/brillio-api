<?php

namespace App\Http\Controllers\Jeune;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    /**
     * Affiche le formulaire d'onboarding
     */
    public function index()
    {
        $user = auth()->user();

        // Si deja complete, rediriger vers le dashboard
        if ($user->onboarding_completed) {
            return redirect()->route('jeune.dashboard');
        }

        return view('jeune.onboarding', [
            'user' => $user,
            'countries' => $this->getAfricanCountries(),
        ]);
    }

    /**
     * Complete l'onboarding
     */
    public function complete(Request $request)
    {
        $validated = $request->validate([
            'birth_date' => 'required|date|before:today',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
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

        $user = auth()->user();

        // Préparer les données d'onboarding
        $onboardingData = [
            'education_level' => $validated['education_level'],
            'current_situation' => $validated['current_situation'],
            'interests' => $validated['interests'],
            'goals' => $validated['goals'],
            'how_found_us' => $validated['how_found_us'],
            'completed_at' => now()->toISOString(),
        ];

        // Ajouter les champs personnalisés si présents
        if ($validated['current_situation'] === 'autre' && !empty($validated['current_situation_other'])) {
            $onboardingData['current_situation_other'] = $validated['current_situation_other'];
        }

        if ($validated['how_found_us'] === 'other' && !empty($validated['how_found_us_other'])) {
            $onboardingData['how_found_us_other'] = $validated['how_found_us_other'];
        }

        $user->update([
            'date_of_birth' => $validated['birth_date'],
            'country' => $validated['country'],
            'city' => $validated['city'] ?? null,
            'onboarding_completed' => true,
            'onboarding_data' => $onboardingData,
        ]);

        return redirect()->route('jeune.dashboard')
            ->with('success', 'Bienvenue sur Brillio ! Votre profil a ete configure.');
    }

    /**
     * Liste des pays africains
     */
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
