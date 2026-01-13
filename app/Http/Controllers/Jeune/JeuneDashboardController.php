<?php

namespace App\Http\Controllers\Jeune;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\MentorProfile;
use App\Models\PersonalityQuestion;
use App\Models\PersonalityTest;
use App\Services\DeepSeekService;
use App\Services\MbtiCareersService;
use App\Services\PersonalityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JeuneDashboardController extends Controller
{
    /**
     * Dashboard principal du jeune
     */
    public function index()
    {
        $user = auth()->user();

        // Verifier si l'onboarding est complete
        if (!$user->onboarding_completed) {
            return redirect()->route('jeune.onboarding');
        }

        // Stats du profil
        $stats = [
            'personality_completed' => $user->personalityTest && $user->personalityTest->completed_at,
            'documents_count' => $user->academicDocuments()->count(),
            'conversations_count' => $user->chatConversations()->count(),
            'messages_count' => $user->chatConversations()->withCount('messages')->get()->sum('messages_count'),
        ];

        // Mentors recommandes (basé sur le type de personnalité si disponible)
        $personalityTest = $user->personalityTest;
        if ($personalityTest && $personalityTest->personality_type) {
            $recommendedMentors = MentorProfile::getRecommendedForMbtiType(
                $personalityTest->personality_type,
                4
            );
        } else {
            $recommendedMentors = MentorProfile::where('is_published', true)
                ->with('user')
                ->limit(4)
                ->get();
        }

        return view('jeune.dashboard', [
            'user' => $user,
            'stats' => $stats,
            'recommendedMentors' => $recommendedMentors,
        ]);
    }

    /**
     * Page du test de personnalite
     */
    public function personalityTest()
    {
        $user = auth()->user();

        // Récupérer le test actuel
        $personalityTest = PersonalityTest::where('user_id', $user->id)
            ->current()
            ->first();

        // Récupérer l'historique
        $testHistory = PersonalityTest::where('user_id', $user->id)
            ->history()
            ->get();

        return view('jeune.personality', [
            'user' => $user,
            'personalityTest' => $personalityTest,
            'testHistory' => $testHistory,
        ]);
    }

    /**
     * Récupère les questions du test depuis la base de données locale
     */
    public function getPersonalityQuestions()
    {
        $questions = PersonalityQuestion::getAllFormatted('fr');

        return response()->json([
            'success' => true,
            'total_questions' => count($questions),
            'questions' => $questions,
        ]);
    }

    /**
     * Soumet le test de personnalité
     * Utilise OpenMBTI API pour le calcul et MbtiCareersService pour les métiers
     */
    public function submitPersonalityTest(Request $request, DeepSeekService $deepSeekService, PersonalityService $personalityService)
    {
        $validated = $request->validate([
            'responses' => ['required', 'array', 'min:32', 'max:32'],
            'responses.*' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $user = auth()->user();
        $responses = $validated['responses'];

        Log::info('=== DEBUT SOUMISSION TEST PERSONNALITE ===', [
            'user_id' => $user->id,
            'responses_count' => count($responses),
            'responses_sample' => array_slice($responses, 0, 5, true),
        ]);

        try {
            // 1. Appeler OpenMBTI pour calculer les résultats
            Log::info('Appel API OpenMBTI /calculate', [
                'url' => 'https://openmbti.org/api/calculate',
                'answers_count' => count($responses),
            ]);

            $mbtiResponse = Http::timeout(30)->post('https://openmbti.org/api/calculate', [
                'answers' => $responses,
                'locale' => 'en',
                'save' => false,
            ]);

            Log::info('Reponse API OpenMBTI', [
                'status' => $mbtiResponse->status(),
                'successful' => $mbtiResponse->successful(),
                'body_length' => strlen($mbtiResponse->body()),
            ]);

            if (!$mbtiResponse->successful()) {
                Log::error('OpenMBTI API error', [
                    'status' => $mbtiResponse->status(),
                    'body' => $mbtiResponse->body(),
                ]);

                // Fallback: utiliser le calcul local
                Log::info('Utilisation du calcul local fallback');
                $result = $personalityService->calculatePersonalityType($responses);
                $mbtiType = $result['type'];
                $percentages = $result['traits_scores'];
            } else {
                $mbtiData = $mbtiResponse->json();
                Log::info('OpenMBTI data parsed', [
                    'has_result' => isset($mbtiData['result']),
                    'type' => $mbtiData['result']['type'] ?? 'N/A',
                ]);

                $result = $mbtiData['result'] ?? null;

                if (!$result) {
                    Log::warning('Resultat OpenMBTI invalide, utilisation fallback');
                    $localResult = $personalityService->calculatePersonalityType($responses);
                    $mbtiType = $localResult['type'];
                    $percentages = $localResult['traits_scores'];
                } else {
                    $mbtiType = $result['type'];
                    $percentages = $result['percentages'] ?? [
                        'E' => 50,
                        'I' => 50,
                        'S' => 50,
                        'N' => 50,
                        'T' => 50,
                        'F' => 50,
                        'J' => 50,
                        'P' => 50,
                    ];
                }
            }

            Log::info('Type MBTI determine', [
                'type' => $mbtiType,
                'percentages' => $percentages,
            ]);

            // 2. Récupérer les informations du type depuis notre service local
            $typeInfo = $personalityService::TYPE_DESCRIPTIONS[$mbtiType] ?? [
                'label' => $mbtiType,
                'description' => 'Type de personnalité ' . $mbtiType,
            ];

            // 3. Récupérer les métiers depuis MbtiCareersService (données statiques, pas d'API)
            $careers = MbtiCareersService::getCareersForType($mbtiType);
            $sectors = MbtiCareersService::getSectorsForType($mbtiType);

            Log::info('Metiers et secteurs recuperes', [
                'type' => $mbtiType,
                'careers_count' => count($careers),
                'sectors_count' => count($sectors),
            ]);

            // 4. Données supplémentaires depuis OpenMBTI (si disponibles)
            $strengths = [];
            $weaknesses = [];
            $compatibleTypes = [];
            $famousExamples = [];

            if (isset($result['typeInfo'])) {
                $strengths = $result['typeInfo']['strengths'] ?? [];
                $weaknesses = $result['typeInfo']['weaknesses'] ?? [];
                $compatibleTypes = $result['typeInfo']['compatibleTypes'] ?? [];
                $famousExamples = $result['typeInfo']['famousExamples'] ?? [];
            }

            // 5. Sauvegarder dans la base de données
            $personalityTest = $personalityService->savePreCalculatedResult(
                $user,
                $mbtiType,
                $typeInfo['label'],
                $typeInfo['description'],
                $percentages,
                $responses
            );

            // Sauvegarder les recommandations de métiers et secteurs
            $personalityTest->update([
                'recommended_careers' => $careers,
                'recommended_sectors' => array_keys($sectors),
            ]);

            Log::info('=== TEST PERSONNALITE SAUVEGARDE ===', [
                'test_id' => $personalityTest->id,
                'type' => $mbtiType,
                'careers_saved' => count($careers),
            ]);

            return response()->json([
                'success' => true,
                'personality_type' => $personalityTest->personality_type,
                'personality_label' => $personalityTest->personality_label,
                'personality_description' => $personalityTest->personality_description,
                'traits_scores' => $personalityTest->traits_scores,
                'strengths' => $strengths,
                'weaknesses' => $weaknesses,
                'compatible_types' => $compatibleTypes,
                'famous_examples' => $famousExamples,
                'recommended_careers' => $careers,
                'recommended_sectors' => $sectors,
                'completed_at' => $personalityTest->completed_at->toISOString(),
            ], 200);

        } catch (\Exception $e) {
            Log::error('Personality test submission error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la soumission du test.',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Récupère les détails d'un test historique
     */
    public function getHistoryTestDetails(int $testId)
    {
        $user = auth()->user();

        $test = PersonalityTest::where('user_id', $user->id)
            ->where('id', $testId)
            ->first();

        if (!$test) {
            return response()->json([
                'success' => false,
                'message' => 'Test non trouvé.',
            ], 404);
        }

        // Récupérer les infos du type
        $typeInfo = PersonalityService::TYPE_DESCRIPTIONS[$test->personality_type] ?? [
            'label' => $test->personality_type,
            'description' => 'Type de personnalité ' . $test->personality_type,
        ];

        // Récupérer les métiers (depuis les données sauvegardées ou générer)
        $careers = $test->recommended_careers;
        if (empty($careers)) {
            $careers = MbtiCareersService::getCareersForType($test->personality_type);
        }

        $sectors = MbtiCareersService::getSectorsForType($test->personality_type);

        return response()->json([
            'success' => true,
            'test' => [
                'id' => $test->id,
                'personality_type' => $test->personality_type,
                'personality_label' => $test->personality_label ?? $typeInfo['label'],
                'personality_description' => $test->personality_description ?? $typeInfo['description'],
                'traits_scores' => $test->traits_scores,
                'recommended_careers' => $careers,
                'recommended_sectors' => $sectors,
                'completed_at' => $test->completed_at->format('d/m/Y à H:i'),
                'is_current' => $test->is_current,
            ],
        ]);
    }

    /**
     * Page du chat IA
     */
    public function chat()
    {
        $user = auth()->user();
        $conversations = $user->chatConversations()
            ->with('messages')
            ->orderByDesc('updated_at')
            ->get();

        // Charger la dernière conversation par défaut
        $currentConversation = $conversations->first();

        return view('jeune.chat', [
            'user' => $user,
            'conversations' => $conversations,
            'currentConversation' => $currentConversation,
        ]);
    }

    /**
     * Page des documents
     */
    public function documents()
    {
        $user = auth()->user();
        $documents = $user->academicDocuments()
            ->orderByDesc('created_at')
            ->get();

        return view('jeune.documents', [
            'user' => $user,
            'documents' => $documents,
        ]);
    }

    /**
     * Page des mentors
     */
    public function mentors(Request $request)
    {
        $user = auth()->user();
        $query = MentorProfile::where('is_published', true)
            ->with(['user', 'roadmapSteps']);

        // Filtre par secteur MBTI
        if ($request->filled('sector')) {
            $query->byMbtiSector($request->sector);
        }

        // Filtre par spécialisation
        if ($request->filled('specialization')) {
            $query->where('specialization', $request->specialization);
        }

        // Filtre par pays
        if ($request->filled('country')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('country', 'like', '%' . $request->country . '%');
            });
        }

        // Recherche textuelle
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bio', 'like', '%' . $search . '%')
                    ->orWhere('current_position', 'like', '%' . $search . '%')
                    ->orWhere('current_company', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        // Filtre "Pour mon profil" - mentors recommandés basés sur le type MBTI
        if ($request->filled('for_profile') && $request->for_profile === 'true') {
            $personalityTest = $user->personalityTest;
            if ($personalityTest && $personalityTest->personality_type) {
                $query->byMbtiType($personalityTest->personality_type);
            }
        }

        $mentors = $query->paginate(12);

        $specializations = MentorProfile::SPECIALIZATIONS;
        $sectors = MbtiCareersService::getAllSectors();

        // Récupérer le type de personnalité de l'utilisateur pour la recommandation
        $personalityTest = $user->personalityTest;
        $userMbtiType = $personalityTest ? $personalityTest->personality_type : null;
        $userMbtiLabel = $personalityTest ? $personalityTest->personality_label : null;

        return view('jeune.mentors', [
            'mentors' => $mentors,
            'specializations' => $specializations,
            'sectors' => $sectors,
            'userMbtiType' => $userMbtiType,
            'userMbtiLabel' => $userMbtiLabel,
        ]);
    }

    /**
     * Page du profil
     */
    public function profile()
    {
        $user = auth()->user();

        return view('jeune.profile', [
            'user' => $user,
        ]);
    }

    /**
     * Mise a jour du profil
     */
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'phone' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
        ]);

        auth()->user()->update($validated);

        return back()->with('success', 'Profil mis a jour avec succes.');
    }

    /**
     * Detail d'un mentor
     */
    public function mentorShow(MentorProfile $mentor)
    {
        $mentor->load(['user', 'roadmapSteps']);

        // Mentors similaires (meme specialisation)
        $similarMentors = MentorProfile::where('is_published', true)
            ->where('id', '!=', $mentor->id)
            ->where('specialization', $mentor->specialization)
            ->with('user')
            ->limit(3)
            ->get();

        return view('jeune.mentor-show', [
            'mentor' => $mentor,
            'similarMentors' => $similarMentors,
        ]);
    }

    /**
     * Upload de document
     */
    public function storeDocument(Request $request)
    {
        $validated = $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'document_type' => 'required|in:bulletin,diplome,attestation,autre',
            'school_year' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:500',
        ]);

        $file = $request->file('document');
        $path = $file->store('documents/' . auth()->id(), 'public');

        auth()->user()->academicDocuments()->create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'document_type' => $validated['document_type'],
            'academic_year' => $validated['school_year'] ?? null,
            'uploaded_at' => now(),
        ]);

        return back()->with('success', 'Document ajoute avec succes.');
    }

    /**
     * Telecharger un document
     */
    public function downloadDocument($document)
    {
        $doc = auth()->user()->academicDocuments()->findOrFail($document);

        return response()->download(storage_path('app/public/' . $doc->file_path), $doc->file_name);
    }

    /**
     * Prévisualiser un document
     */
    public function viewDocument($document)
    {
        $doc = auth()->user()->academicDocuments()->findOrFail($document);
        $path = storage_path('app/public/' . $doc->file_path);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }

    /**
     * Supprimer un document
     */
    public function deleteDocument($document)
    {
        $doc = auth()->user()->academicDocuments()->findOrFail($document);
        \Storage::disk('public')->delete($doc->file_path);
        $doc->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Envoyer un message dans le chat
     */
    public function sendChatMessage(Request $request, DeepSeekService $deepSeekService)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'conversation_id' => 'nullable|integer',
        ]);

        $user = auth()->user();
        $conversationId = $validated['conversation_id'] ?? null;

        Log::info('=== DEBUT ENVOI MESSAGE CHAT ===', [
            'user_id' => $user->id,
            'conversation_id' => $conversationId,
            'message_length' => strlen($validated['message']),
            'api_configured' => $deepSeekService->isApiKeyConfigured(),
        ]);

        // Recuperer ou creer la conversation
        if ($conversationId) {
            $conversation = ChatConversation::where('id', $conversationId)
                ->where('user_id', $user->id)
                ->first();

            if (!$conversation) {
                Log::warning('Conversation non trouvee', ['conversation_id' => $conversationId]);
                return response()->json([
                    'success' => false,
                    'error' => 'Conversation non trouvee',
                ], 404);
            }
            Log::info('Conversation existante trouvee', ['conversation_id' => $conversation->id]);
        } else {
            // Vérifier la limite de conversations (max 2)
            $conversationCount = ChatConversation::where('user_id', $user->id)->count();

            if ($conversationCount >= 2) {
                Log::warning('Limite de conversations atteinte', ['user_id' => $user->id]);
                return response()->json([
                    'success' => false,
                    'error' => 'Tu as atteint la limite de 2 conversations. Supprime une conversation existante pour en créer une nouvelle.',
                    'limit_reached' => true,
                ], 400);
            }

            $conversation = $deepSeekService->createConversation($user);
            Log::info('Nouvelle conversation creee', ['conversation_id' => $conversation->id]);
        }

        try {
            // Envoyer le message et obtenir la reponse
            Log::info('Appel DeepSeekService->sendMessage');
            $assistantMessage = $deepSeekService->sendMessage($conversation, $validated['message']);
            Log::info('Reponse recue de DeepSeekService', [
                'message_id' => $assistantMessage->id,
                'content_length' => strlen($assistantMessage->content),
            ]);

            // Recharger la conversation pour avoir le titre mis a jour
            $conversation->refresh();

            Log::info('=== FIN ENVOI MESSAGE CHAT ===', [
                'conversation_id' => $conversation->id,
                'conversation_title' => $conversation->title,
            ]);

            return response()->json([
                'success' => true,
                'conversation_id' => $conversation->id,
                'conversation_title' => $conversation->title,
                'message' => $assistantMessage->content,
                'api_used' => $deepSeekService->isApiKeyConfigured(),
            ]);
        } catch (\Exception $e) {
            Log::error('Chat error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Une erreur est survenue lors de l\'envoi du message.',
            ], 500);
        }
    }

    /**
     * Récupérer une conversation spécifique
     */
    public function getConversation(ChatConversation $conversation)
    {
        $user = auth()->user();

        // Vérifier que la conversation appartient à l'utilisateur
        if ($conversation->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Conversation non trouvée',
            ], 404);
        }

        $messages = $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content]);

        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }

    /**
     * Supprimer une conversation
     */
    public function deleteConversation(ChatConversation $conversation)
    {
        $user = auth()->user();

        // Vérifier que la conversation appartient à l'utilisateur
        if ($conversation->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Conversation non trouvée',
            ], 404);
        }

        $conversation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Conversation supprimée avec succès',
        ]);
    }

    /**
     * Demander une assistance humaine
     */
    public function requestHumanSupport(ChatConversation $conversation)
    {
        $user = auth()->user();

        // Vérifier que la conversation appartient à l'utilisateur
        if ($conversation->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Conversation non trouvée',
            ], 404);
        }

        // Marquer la conversation comme nécessitant une assistance humaine
        $conversation->update(['needs_human_support' => true]);

        // TODO: Envoyer une notification aux conseillers

        return response()->json([
            'success' => true,
            'message' => 'Demande d\'assistance envoyée',
        ]);
    }
}
