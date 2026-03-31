<?php

namespace App\Http\Controllers\Jeune;

use App\Http\Controllers\Controller;
use App\Models\UserDetailedProfile;
use App\Models\UserFeedback;
use Illuminate\Http\Request;

class UserProfilingController extends Controller
{
    /**
     * Enregistre un feedback (rating + comment)
     */
    public function storeFeedback(Request $request)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();

        UserFeedback::create([
            'user_id' => $user->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        $user->update([
            'last_feedback_at' => now(),
            'last_rating' => $validated['rating'],
        ]);

        return response()->json([
            'message' => 'Merci pour votre retour !',
            'needs_situation' => $user->needsSituationNudge(),
        ]);
    }

    /**
     * Reporte le feedback (clic sur fermer ou plus tard)
     */
    public function skipFeedback()
    {
        auth()->user()->update(['last_feedback_at' => now()]);
        return response()->json(['message' => 'Nudge reporté']);
    }

    /**
     * Enregistre une mise à jour de situation détaillée
     */
    public function storeSituation(Request $request)
    {
        $user = auth()->user();
        
        // On récupère la situation de base depuis l'onboarding_data ou current_situation
        $baseSituation = $user->onboarding_data['current_situation'] ?? 'autre';

        // Validation dynamique simplifiée (le plus gros du travail est côté Front)
        $validated = $request->validate([
            'data' => 'required|array',
        ]);

        UserDetailedProfile::create([
            'user_id' => $user->id,
            'status' => $baseSituation,
            'data' => $validated['data'],
        ]);

        $user->update([
            'last_situation_update_at' => now(),
        ]);

        return response()->json([
            'message' => 'Situation mise à jour avec succès !',
        ]);
    }

    /**
     * Reporte la mise à jour de situation
     */
    public function skipSituation()
    {
        auth()->user()->update(['last_situation_update_at' => now()]);
        return response()->json(['message' => 'Nudge reporté']);
    }
}
