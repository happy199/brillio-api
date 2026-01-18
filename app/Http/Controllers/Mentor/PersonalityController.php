<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\PersonalityQuestion;
use App\Services\MbtiCareersService;
use App\Services\PersonalityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PersonalityController extends Controller
{
    /**
     * Affiche la page du test de personnalité ou les résultats
     */
    public function index()
    {
        $user = auth()->user();
        $personalityTest = $user->personalityTest;
        $testHistory = \App\Models\PersonalityTest::where('user_id', $user->id)
            ->history()
            ->get();

        return view('mentor.personality', [
            'user' => $user,
            'personalityTest' => $personalityTest,
            'testHistory' => $testHistory,
        ]);
    }

    /**
     * Récupère les questions du test (API interne pour le frontend)
     */
    public function getQuestions()
    {
        $questions = PersonalityQuestion::getAllFormatted('fr');
        return response()->json([
            'success' => true,
            'questions' => $questions
        ]);
    }

    /**
     * Traite la soumission du test
     */
    public function submit(Request $request, PersonalityService $personalityService)
    {
        $validated = $request->validate([
            'responses' => 'required|array',
            'responses.*' => 'required|integer|min:1|max:5',
        ]);

        $user = auth()->user();
        $responses = $validated['responses'];

        try {
            // 1. Appeler OpenMBTI/Fallback pour calculer
            // NOTE: Copied Logic from JeuneDashboardController for consistency

            $mbtiResponse = Http::timeout(30)->post('https://openmbti.org/api/calculate', [
                'answers' => $responses,
                'locale' => 'en', // OpenMBTI usually needs English mapping or raw IDs
                'save' => false,
            ]);

            if (!$mbtiResponse->successful()) {
                // Fallback local
                $result = $personalityService->calculatePersonalityType($responses);
                $mbtiType = $result['type'];
                $percentages = $result['traits_scores'];
            } else {
                $mbtiData = $mbtiResponse->json();
                $result = $mbtiData['result'] ?? null;

                if (!$result) {
                    $localResult = $personalityService->calculatePersonalityType($responses);
                    $mbtiType = $localResult['type'];
                    $percentages = $localResult['traits_scores'];
                } else {
                    $mbtiType = $result['type'];
                    // Default 50/50 if not provided
                    $percentages = $result['percentages'] ?? ['E' => 50, 'I' => 50, 'S' => 50, 'N' => 50, 'T' => 50, 'F' => 50, 'J' => 50, 'P' => 50];
                }
            }

            // 2. Info du type
            $typeInfo = $personalityService::TYPE_DESCRIPTIONS[$mbtiType] ?? [
                'label' => $mbtiType,
                'description' => 'Type de personnalité ' . $mbtiType,
            ];

            // 3. Métiers/Secteurs (Optionnel pour Mentor, mais on garde pour info)
            $careers = MbtiCareersService::getCareersForType($mbtiType);
            $sectors = MbtiCareersService::getSectorsForType($mbtiType);

            // 4. Sauvegarde
            $personalityTest = $personalityService->savePreCalculatedResult(
                $user,
                $mbtiType,
                $typeInfo['label'],
                $typeInfo['description'],
                $percentages,
                $responses
            );

            // Update stats if needed (user model or profile)
            // Mentors don't have separate 'personality_type' field usually, it's via relation.

            return response()->json([
                'success' => true,
                'personality_type' => $mbtiType,
                'redirect_url' => route('mentor.personality')
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur soumission test mentor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors du calcul des résultats.'
            ], 500);
        }
    }
}
