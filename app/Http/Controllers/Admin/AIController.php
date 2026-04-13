<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BrillioIAService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AIController extends Controller
{
    protected $brillioAIService;

    public function __construct(BrillioIAService $brillioAIService)
    {
        $this->brillioAIService = $brillioAIService;
    }

    /**
     * Génère le contenu d'une fiche métier à partir d'un titre
     */
    public function generateCareerContent(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $title = $validated['title'];

        $systemPrompt = "Tu es un expert en ressources humaines et en orientation professionnelle en Afrique.\n".
            "Ta mission est d'aider un administrateur à remplir une fiche métier.\n".
            "Tu dois générer des textes inspirants, précis et adaptés au contexte local africain.\n\n".
            "Réponds UNIQUEMENT sous forme d'un objet JSON avec les clés suivantes :\n".
            "- 'description' : Une description professionnelle du métier (2-3 phrases moyennes).\n".
            "- 'african_context' : Pourquoi ce métier est important ou en croissance en Afrique.\n".
            "- 'future_prospects' : Les perspectives d'évolution détaillées (ex: Influence de la tech, nouveaux marchés). Environ 200 à 400 caractères.\n".
            "- 'demand_level' : Le niveau de demande locale estimé (ex: 'Haute', 'Moyenne', 'En forte croissance').\n".
            "- 'ai_impact_level' : Le niveau d'influence de l'IA (low, medium, high).\n".
            "- 'ai_impact_explanation' : Une phrase expliquant pourquoi ce niveau.";

        $prompt = "Génère le contenu pour le métier suivant : {$title}";

        try {
            $response = $this->brillioAIService->analyzeText($prompt, $systemPrompt);
            $json = $this->brillioAIService->cleanJson($response);
            $data = json_decode($json, true);

            if (! $data) {
                return response()->json(['error' => 'Erreur de formatage IA'], 500);
            }

            if (isset($data['ai_impact_level'])) {
                $rawImpact = strtolower($data['ai_impact_level']);
                if (str_contains($rawImpact, 'low')) {
                    $data['ai_impact_level'] = 'low';
                } elseif (str_contains($rawImpact, 'medi')) {
                    $data['ai_impact_level'] = 'medium';
                } elseif (str_contains($rawImpact, 'high')) {
                    $data['ai_impact_level'] = 'high';
                }
            }

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Admin AI Generation Error: '.$e->getMessage());

            return response()->json(['error' => 'Erreur lors de l\'appel à l\'IA'], 500);
        }
    }
}
