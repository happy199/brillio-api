<?php

namespace App\Http\Controllers\Jeune;

use App\Http\Controllers\Controller;
use App\Traits\HandlesAfricanPhoneNumbers;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    use HandlesAfricanPhoneNumbers;

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
        if ($validated['current_situation'] === 'autre' && ! empty($validated['current_situation_other'])) {
            $onboardingData['current_situation_other'] = $validated['current_situation_other'];
        }

        if ($validated['how_found_us'] === 'other' && ! empty($validated['how_found_us_other'])) {
            $onboardingData['how_found_us_other'] = $validated['how_found_us_other'];
        }

        // Formater le numéro de téléphone au format E.164
        $normalizedPhone = $this->normalizeToE164($validated['country'], $validated['phone']);

        $user->update([
            'date_of_birth' => $validated['birth_date'],
            'country' => $validated['country'],
            'city' => $validated['city'] ?? null,
            'phone' => $normalizedPhone,
            'onboarding_completed' => true,
            'onboarding_data' => $onboardingData,
        ]);

        return redirect()->route('jeune.dashboard')
            ->with('success', 'Bienvenue sur Brillio ! Votre profil a ete configure.');
    }
}
