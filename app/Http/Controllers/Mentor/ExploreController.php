<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ExploreController extends Controller
{
    /**
     * Affiche la liste des jeunes avec filtres
     */
    public function index(Request $request)
    {
        $query = User::where('user_type', User::TYPE_JEUNE)
            ->whereHas('jeuneProfile', function ($q) {
                $q->where('is_public', true);
            });

        // Filtre MBTI
        if ($request->filled('mbti')) {
            $query->whereHas('personalityTest', function ($q) use ($request) {
                $q->where('personality_type', $request->mbti);
            });
        }

        // Filtre Niveau d'étude
        if ($request->filled('education_level')) {
            $query->where('onboarding_data->education_level', $request->education_level);
        }

        // Filtre Situation
        if ($request->filled('current_situation')) {
            $query->where('onboarding_data->current_situation', $request->current_situation);
        }

        // Filtre Centre d'intérêt
        if ($request->filled('interest')) {
            $query->whereJsonContains('onboarding_data->interests', $request->interest);
        }

        // Filtre Matching (Compatibilité)
        if ($request->filled('matching') && $request->matching == '1') {
            $mentor = auth()->user();
            if ($mentor->personalityTest && $mentor->personalityTest->personality_type) {
                $myType = $mentor->personalityTest->personality_type;
                $compatibleTypes = \App\Models\PersonalityCompatibility::getCompatibleTypes($myType);
                // Include my own type as per requirement
                $compatibleTypes[] = $myType;

                $query->whereHas('personalityTest', function ($q) use ($compatibleTypes) {
                    $q->whereIn('personality_type', $compatibleTypes);
                });
            }
        }

        $jeunes = $query->with(['jeuneProfile', 'personalityTest'])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('mentor.explore', compact('jeunes'));
    }
}
