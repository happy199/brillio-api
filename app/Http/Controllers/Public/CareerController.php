<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Career;
use Illuminate\Http\Request;

class CareerController extends Controller
{
    /**
     * Récupère les détails d'un métier par son titre exact.
     */
    public function getDetailsByTitle(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $title = $validated['title'];

        $career = Career::where('title', $title)->first();

        if (! $career) {
            return response()->json(['success' => false, 'message' => 'Fiche non trouvée'], 404);
        }

        return response()->json([
            'success' => true,
            'career' => [
                'title' => $career->title,
                'description' => $career->description,
                'african_context' => $career->african_context,
                'future_prospects' => $career->future_prospects,
                'demand_level' => $career->demand_level,
                'ai_impact_level' => $career->ai_impact_level,
                'ai_impact_explanation' => $career->ai_impact_explanation,
            ],
        ]);
    }
}
