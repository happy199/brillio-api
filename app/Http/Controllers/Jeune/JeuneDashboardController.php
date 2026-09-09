<?php

namespace App\Http\Controllers\Jeune;

use App\Http\Controllers\Controller;
use App\Models\AcademicDocument;
use App\Models\AdvisorVideoCall;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\CvAnalysis;
use App\Models\MentorProfile;
use App\Models\MentorProfileView;
use App\Models\PersonalityQuestion;
use App\Models\PersonalityTest;
use App\Models\Resource;
use App\Models\SystemSetting;
use App\Services\BrillioIAService;
use App\Services\CvAnalysisService;
use App\Services\CvDocxExportService;
use App\Services\MbtiCareersService;
use App\Services\PersonalityService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class JeuneDashboardController extends Controller
{
    /**
     * Dashboard principal du jeune
     */
    public function index()
    {
        $user = auth()->user()->load(['personalityTest', 'jeuneProfile']);

        // Verifier si l'onboarding est complete
        if (! $user->onboarding_completed) {
            return redirect()->route('jeune.onboarding');
        }

        // Stats du profil (Optimisé avec comptage direct)
        $stats = [
            'personality_completed' => $user->personalityTest && $user->personalityTest->completed_at,
            'documents_count' => $user->academicDocuments()->count(),
            'conversations_count' => $user->chatConversations()->count(),
            'messages_count' => $user->chatMessages()->count(),
            'profile_views' => $user->jeuneProfile?->profile_views ?? 0,
            'mentor_views' => $user->jeuneProfile?->mentor_views ?? 0,
        ];

        // Mentors recommandés (basé sur le type de personnalité si disponible)
        $personalityTest = $user->personalityTest;
        $recommendedQuery = MentorProfile::where('is_published', true)->with('user');

        if ($user->hasPrivateCircleRestriction()) {
            $orgIds = $user->getPrivateCircleOrganizationIds();
            $recommendedQuery->whereHas('user.organizations', function ($q) use ($orgIds) {
                $q->whereIn('organizations.id', $orgIds);
            });
        } else {
            // General youths cannot see Private Circle Plus mentors
            $recommendedQuery->whereDoesntHave('user.organizations', function ($q) {
                $q->where('private_circle_plus_enabled', true);
            });
        }

        if ($personalityTest && $personalityTest->personality_type) {
            $recommendedMentors = $recommendedQuery->byMbtiType($personalityTest->personality_type)
                ->limit(4)
                ->get();
        } else {
            $recommendedMentors = $recommendedQuery->limit(4)->get();
        }

        // --- RESSOURCES RECOMMANDÉES ---
        $userProfile = $user->onboarding_data ?? [];
        $userEducation = $userProfile['education_level'] ?? null;
        $userSituation = $userProfile['current_situation'] ?? null;
        $userInterests = $userProfile['interests'] ?? [];
        $userCountry = $userProfile['country'] ?? null;
        $rawMbti = $personalityTest?->type_code ?? $userProfile['mbti'] ?? null;
        $userMbti = $rawMbti ? explode(' ', $rawMbti)[0] : null;

        $resourceQuery = Resource::where('is_published', true)
            ->where('is_validated', true)
            ->whereHas('user', function ($q) {
                $q->where('is_admin', true)
                    ->orWhereHas('mentorProfile', function ($mp) {
                        $mp->where('is_published', true);
                    });
            })
            ->with('user');

        $allResources = $resourceQuery->get();

        $targetedResources = collect();
        $randomResources = collect();

        foreach ($allResources as $resource) {
            $targeting = $resource->targeting;
            $isTargeted = false;

            if (! empty($resource->mbti_types) && $userMbti && in_array($userMbti, $resource->mbti_types)) {
                $isTargeted = true;
            }

            if (! $isTargeted && ! empty($targeting)) {
                $targetEducations = $targeting['education_levels'] ?? [];
                if (! empty($targetEducations) && $userEducation && in_array($userEducation, $targetEducations)) {
                    $isTargeted = true;
                }

                $targetSituations = $targeting['situations'] ?? [];
                if (! $isTargeted && ! empty($targetSituations) && $userSituation && in_array($userSituation, $targetSituations)) {
                    $isTargeted = true;
                }

                $targetCountries = $targeting['countries'] ?? [];
                if (! $isTargeted && ! empty($targetCountries) && $userCountry) {
                    foreach ($targetCountries as $country) {
                        if (str_contains(strtolower($userCountry), strtolower($country))) {
                            $isTargeted = true;
                            break;
                        }
                    }
                }

                $targetInterests = $targeting['interests'] ?? [];
                if (! $isTargeted && ! empty($targetInterests) && ! empty($userInterests)) {
                    $commonInterests = array_intersect($targetInterests, $userInterests);
                    if (! empty($commonInterests)) {
                        $isTargeted = true;
                    }
                }
            }

            // Exclure les ressources avec des restrictions fortes non matchées ? Non, on va juste prendre celles qui ont un match direct comme ciblage
            if ($isTargeted) {
                $targetedResources->push($resource);
            } else {
                $randomResources->push($resource);
            }
        }

        $targetedResources = $targetedResources->shuffle();
        $randomResources = $randomResources->shuffle();

        $recommendedResources = $targetedResources->merge($randomResources)->take(10);

        return view('jeune.dashboard', [
            'user' => $user,
            'stats' => $stats,
            'recommendedMentors' => $recommendedMentors,
            'recommendedResources' => $recommendedResources,
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
     * Récupère les questions reformulées par l'IA selon le profil de l'utilisateur
     */
    public function getDynamicPersonalityQuestions(BrillioIAService $brillioIAService)
    {
        $user = auth()->user();

        // --- CACHE CHECK ---
        // Si l'utilisateur a déjà des questions reformulées en base, on les retourne immédiatement
        if (! empty($user->mbti_reformulated_questions)) {
            Log::info('Utilisation du cache DB pour les questions reformulées', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'total_questions' => count($user->mbti_reformulated_questions),
                'questions' => $user->mbti_reformulated_questions,
                'is_personalized' => true,
                'from_cache' => true,
            ]);
        }

        $questions = PersonalityQuestion::getAllFormatted('fr');

        // Préparer le contexte (Onboarding data)
        $onboarding = $user->onboarding_data ?? [];
        $situation = $onboarding['current_situation'] ?? 'étudiant';
        $education = $onboarding['education_level'] ?? '';

        // Traduction des termes techniques pour l'IA
        $situationMap = [
            'etudiant' => 'étudiant',
            'recherche_emploi' => 'jeune diplômé en recherche d\'emploi',
            'emploi' => 'salarié',
            'entrepreneur' => 'entrepreneur',
        ];

        $educationMap = [
            'college' => 'collégien (élève au collège)',
            'lycee' => 'lycéen (élève au lycée)',
            'bac' => 'bachelier',
        ];

        $situationText = $situationMap[$situation] ?? $situation;
        if ($situation === 'etudiant' && isset($educationMap[$education])) {
            $situationText = $educationMap[$education];
        }

        try {
            Log::info('Demande de reformulation AI pour le test MBTI', [
                'user_id' => $user->id,
                'context' => $situationText,
            ]);

            $dynamicQuestions = $brillioIAService->reformulatePersonalityQuestions($questions, $situationText);

            // Sauvegarder dans le cache de l'utilisateur
            $user->update([
                'mbti_reformulated_questions' => $dynamicQuestions,
                'mbti_reformulated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'total_questions' => count($dynamicQuestions),
                'questions' => $dynamicQuestions,
                'is_personalized' => true,
                'context' => $situationText,
                'from_cache' => false,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur getDynamicPersonalityQuestions: '.$e->getMessage());

            return response()->json([
                'success' => true,
                'total_questions' => count($questions),
                'questions' => $questions,
                'is_personalized' => false,
                'error' => 'Fallback to original questions due to AI error',
            ]);
        }
    }

    /**
     * Soumet le test de personnalité
     * Utilise OpenMBTI API pour le calcul et MbtiCareersService pour les métiers
     */
    public function submitPersonalityTest(Request $request, BrillioIAService $brillioIAService, PersonalityService $personalityService)
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

            if (! $mbtiResponse->successful()) {
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

                if (! $result) {
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
                'description' => 'Type de personnalité '.$mbtiType,
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

        if (! $test) {
            return response()->json([
                'success' => false,
                'message' => 'Test non trouvé.',
            ], 404);
        }

        // Récupérer les infos du type
        $typeInfo = PersonalityService::TYPE_DESCRIPTIONS[$test->personality_type] ?? [
            'label' => $test->personality_type,
            'description' => 'Type de personnalité '.$test->personality_type,
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
            ->with(['messages' => function ($q) {
                $q->with('admin');
            }])
            ->orderByDesc('updated_at')
            ->get();

        // Charger la dernière conversation par défaut
        $currentConversation = $conversations->first();

        // Déterminer la localisation pour les suggestions
        $location = $this->getUserLocation($user);
        $videoCallAdvisorCost = SystemSetting::getValue('feature_cost_video_call_advisor', 50);

        return view('jeune.chat', [
            'user' => $user,
            'conversations' => $conversations,
            'currentConversation' => $currentConversation,
            'location' => $location,
            'videoCallAdvisorCost' => $videoCallAdvisorCost,
        ]);
    }

    /**
     * Déterminer la localisation de l'utilisateur pour les suggestions
     */
    private function getUserLocation($user): string
    {
        // Priorité: ville > pays > défaut (Sénégal)
        if ($user->city) {
            return "à {$user->city}";
        }

        if ($user->country) {
            return "au {$user->country}";
        }

        return 'au Sénégal';
    }

    /**
     * Page des opportunités (Emploi, Formation)
     */
    public function opportunities(Request $request)
    {
        $validated = $request->validate([
            'tab' => 'nullable|string|in:emploi,formation',
        ]);

        $tab = $validated['tab'] ?? 'emploi';

        return view('jeune.opportunities', [
            'tab' => $tab,
        ]);
    }

    /**
     * Page des outils (CV IA, Ressources, Documents Drive)
     */
    public function outils(Request $request)
    {
        $validated = $request->validate([
            'tab' => 'nullable|string|in:emploi,formation,drive,cv,documents,ressources',
            'cv_id' => 'nullable|integer',
        ]);

        $user = auth()->user();
        $tab = $validated['tab'] ?? 'cv';
        if ($tab === 'documents') {
            $tab = 'drive';
        }

        // Si l'utilisateur a un token de CV invité en session, le rattacher automatiquement
        if (session('pending_cv_token')) {
            try {
                $cvService = app(CvAnalysisService::class);
                $cvService->claimGuestCv(session('pending_cv_token'), $user);
                session()->forget('pending_cv_token');
            } catch (\Exception $e) {
                Log::warning('Échec rattachement CV invité: '.$e->getMessage());
            }
        }

        $this->cleanupDuplicateCvRecords($user);

        $documents = $user->academicDocuments()
            ->orderByDesc('created_at')
            ->get();

        $cvAnalyses = $user->cvAnalyses()
            ->orderByDesc('created_at')
            ->get();

        $activeCv = null;
        if (! empty($validated['cv_id'])) {
            $activeCv = $cvAnalyses->firstWhere('id', $validated['cv_id']);
        }
        if (! $activeCv) {
            $activeCv = $cvAnalyses->first();
        }

        $cvCopyCost = (int) SystemSetting::getValue('feature_cost_cv_copy', 1);
        $cvDownloadCost = (int) SystemSetting::getValue('feature_cost_cv_download', 0);
        $templateCosts = [
            0 => $cvDownloadCost,
            1 => (int) SystemSetting::getValue('feature_cost_cv_template_1', 1),
            2 => (int) SystemSetting::getValue('feature_cost_cv_template_2', 2),
            3 => (int) SystemSetting::getValue('feature_cost_cv_template_3', 3),
            4 => (int) SystemSetting::getValue('feature_cost_cv_template_4', 4),
            5 => (int) SystemSetting::getValue('feature_cost_cv_template_5', 5),
        ];

        return view('jeune.outils', [
            'user' => $user,
            'tab' => $tab,
            'documents' => $documents,
            'cvAnalyses' => $cvAnalyses,
            'activeCv' => $activeCv,
            'cvCopyCost' => $cvCopyCost,
            'cvDownloadCost' => $cvDownloadCost,
            'templateCosts' => $templateCosts,
        ]);
    }

    /**
     * Nettoie les doublons de CV originaux et d'analyses générés par des clics multiples
     */
    private function cleanupDuplicateCvRecords($user): void
    {
        $cvDocs = $user->academicDocuments()
            ->where('document_type', AcademicDocument::TYPE_CV)
            ->orderBy('id')
            ->get();

        $seenDocs = [];
        foreach ($cvDocs as $doc) {
            $key = $doc->file_name.'_'.$doc->file_size;
            if (isset($seenDocs[$key])) {
                $doc->delete();
            } else {
                $seenDocs[$key] = true;
            }
        }

        $analyses = $user->cvAnalyses()
            ->orderBy('id')
            ->get();

        $seenAnalyses = [];
        foreach ($analyses as $an) {
            $minuteKey = $an->created_at ? $an->created_at->format('Y-m-d H:i') : 'now';
            $key = $an->original_filename.'_'.$an->file_size.'_'.$minuteKey;
            if (isset($seenAnalyses[$key])) {
                $an->delete();
            } else {
                $seenAnalyses[$key] = true;
            }
        }
    }

    /**
     * Traite l'action payante ou gratuite de copie ou de téléchargement/impression du CV
     */
    public function handleCvAction(Request $request, WalletService $walletService)
    {
        $validated = $request->validate([
            'action' => 'required|string|in:copy,download,download_docx,download_pdf',
            'cv_id' => 'required|integer',
            'template' => 'nullable|integer|between:0,5',
        ]);

        $user = auth()->user();
        $cvAnalysis = $user->cvAnalyses()->find($validated['cv_id']);

        if (! $cvAnalysis) {
            return response()->json([
                'success' => false,
                'message' => 'CV introuvable ou non autorisé.',
            ], 404);
        }

        $action = $validated['action'];
        $template = (int) ($validated['template'] ?? 0);
        $isCopy = $action === 'copy';

        if ($isCopy) {
            $cost = (int) SystemSetting::getValue('feature_cost_cv_copy', 1);
            $description = 'Copie du CV ATS';
        } else {
            $templateLabels = [
                0 => 'Basic ATS (Simple)',
                1 => 'Standard Classique',
                2 => 'Standard Minimaliste',
                3 => 'Professionnel Élite',
                4 => 'Expert Moderne',
                5 => 'Avancé Cadre',
            ];
            $formatLabel = $action === 'download_pdf' ? 'PDF' : 'Word';
            $settingKey = $template === 0 ? 'feature_cost_cv_download' : 'feature_cost_cv_template_'.$template;
            $defaultCost = $template === 0 ? 0 : $template;
            $cost = (int) SystemSetting::getValue($settingKey, $defaultCost);
            $description = "Téléchargement CV ATS {$formatLabel} (Template ".($templateLabels[$template] ?? 'Basic ATS').')';
        }

        if ($cost > 0 && $user->credits_balance < $cost) {
            return response()->json([
                'success' => false,
                'redirect_to_wallet' => true,
                'wallet_url' => route('jeune.wallet.index'),
                'message' => 'Solde de crédits insuffisant. Veuillez recharger votre portefeuille.',
            ], 402);
        }

        if ($cost > 0) {
            $walletService->deductCredits($user, $cost, 'cv_action', $description, $cvAnalysis);
        }

        $downloadUrl = null;
        if ($action === 'download' || $action === 'download_docx') {
            $downloadUrl = URL::temporarySignedRoute(
                'jeune.cv.download-docx',
                now()->addMinutes(60),
                ['cv' => $cvAnalysis->id, 'template' => $template]
            );
        }

        return response()->json([
            'success' => true,
            'action' => $action,
            'format' => $action === 'download_pdf' ? 'pdf' : ($action === 'copy' ? 'text' : 'docx'),
            'template' => $template,
            'cost' => $cost,
            'remaining_balance' => (int) $user->fresh()->credits_balance,
            'cv_text' => $this->formatCvAsPlainText($cvAnalysis),
            'download_url' => $downloadUrl,
        ]);
    }

    /**
     * Téléchargement direct du CV restructuré sous format Word (.docx) modifiable
     */
    public function downloadDocxCv(int $cvId, Request $request, CvDocxExportService $docxService)
    {
        $validated = $request->validate([
            'template' => ['nullable', 'integer', 'between:0,5'],
        ]);

        $user = auth()->user();
        $cvAnalysis = $user->cvAnalyses()->findOrFail($cvId);
        $template = isset($validated['template']) ? (int) $validated['template'] : 0;

        // Si le template est payant et que la requête n'a pas de signature valide
        $settingKey = $template === 0 ? 'feature_cost_cv_download' : 'feature_cost_cv_template_'.$template;
        $defaultCost = $template === 0 ? 0 : $template;
        $cost = (int) SystemSetting::getValue($settingKey, $defaultCost);

        if ($cost > 0 && ! $request->hasValidSignature()) {
            abort(403, 'Lien de téléchargement expiré ou non autorisé.');
        }

        $filePath = $docxService->generateDocx($cvAnalysis, $template);
        $filename = $docxService->generateFilename($cvAnalysis);

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Visualiser le fichier CV d'origine téléversé par le jeune
     */
    public function viewOriginalCv(int $cvId)
    {
        $cv = auth()->user()->cvAnalyses()->findOrFail($cvId);

        if (! Storage::disk('public')->exists($cv->file_path)) {
            abort(404);
        }

        return response()->file(Storage::disk('public')->path($cv->file_path));
    }

    /**
     * Télécharger exactement le fichier CV original téléversé par le jeune
     */
    public function downloadOriginalCv(int $cvId)
    {
        $cv = auth()->user()->cvAnalyses()->findOrFail($cvId);

        if (! $cv->file_path || ! Storage::disk('public')->exists($cv->file_path)) {
            abort(404, 'Le fichier original est introuvable.');
        }

        return response()->download(
            Storage::disk('public')->path($cv->file_path),
            $cv->original_filename ?: 'CV_Original.'.pathinfo($cv->file_path, PATHINFO_EXTENSION)
        );
    }

    /**
     * Formate les données du CV en texte brut pour le presse-papier
     */
    private function formatCvAsPlainText(CvAnalysis $cv): string
    {
        $separator = '-------------------------';
        $norm = $cv->normalized_cv_data;
        $labels = $norm['labels'] ?? [];
        $lines = [];

        $this->appendCvHeader($lines, $cv);
        $this->appendCvSummary($lines, $labels, $norm, $separator);
        $this->appendCvExperiences($lines, $labels, $norm['experiences'] ?? [], $separator);
        $this->appendCvEducation($lines, $labels, $norm['education'] ?? [], $separator);
        $this->appendCvSkills($lines, $labels, $norm['skills'] ?? [], $separator);
        $this->appendCvCertifications($lines, $labels, $norm['certifications'] ?? [], $separator);
        $this->appendCvLanguages($lines, $labels, $norm['languages'] ?? [], $separator);

        return trim(implode("\n", $lines));
    }

    private function appendCvHeader(array &$lines, CvAnalysis $cv): void
    {
        $lines[] = mb_strtoupper($cv->candidate_name ?? 'Candidat');
        if ($cv->candidate_title) {
            $lines[] = $cv->candidate_title;
        }

        $contact = [];
        if (! empty($cv->candidate_contact['email'])) {
            $contact[] = $cv->candidate_contact['email'];
        }
        if (! empty($cv->candidate_contact['phone'])) {
            $contact[] = $cv->candidate_contact['phone'];
        }
        if (! empty($cv->candidate_contact['location'])) {
            $contact[] = $cv->candidate_contact['location'];
        }
        if (! empty($contact)) {
            $lines[] = implode(' | ', $contact);
        }
    }

    private function appendCvSummary(array &$lines, array $labels, array $norm, string $separator): void
    {
        $lines[] = '';
        $lines[] = $labels['profile'] ?? 'PROFESSIONAL SUMMARY';
        $lines[] = $separator;
        $lines[] = $norm['profile_summary'] ?? '';
    }

    private function appendCvExperiences(array &$lines, array $labels, array $experiences, string $separator): void
    {
        if (empty($experiences)) {
            return;
        }

        $lines[] = '';
        $lines[] = $labels['experience'] ?? 'PROFESSIONAL EXPERIENCE';
        $lines[] = $separator;
        foreach ($experiences as $exp) {
            $header = $exp['title'].' — '.$exp['company'];
            if (! empty($exp['period'])) {
                $header .= ' ('.$exp['period'].')';
            }
            $lines[] = $header;
            if (! empty($exp['bullets'])) {
                foreach ($exp['bullets'] as $b) {
                    $lines[] = '• '.$b;
                }
            } elseif (! empty($exp['description'])) {
                $lines[] = $exp['description'];
            }
            $lines[] = '';
        }
    }

    private function appendCvEducation(array &$lines, array $labels, array $education, string $separator): void
    {
        if (empty($education)) {
            return;
        }

        $lines[] = $labels['education'] ?? 'EDUCATION';
        $lines[] = $separator;
        foreach ($education as $edu) {
            $item = $edu['degree'].' — '.$edu['school'];
            if (! empty($edu['year'])) {
                $item .= ' ('.$edu['year'].')';
            }
            $lines[] = $item;
        }
        $lines[] = '';
    }

    private function appendCvSkills(array &$lines, array $labels, array $skills, string $separator): void
    {
        if (empty($skills)) {
            return;
        }

        $lines[] = $labels['skills'] ?? 'SKILLS';
        $lines[] = $separator;
        $lines[] = implode(', ', $skills);
        $lines[] = '';
    }

    private function appendCvCertifications(array &$lines, array $labels, array $certifications, string $separator): void
    {
        if (empty($certifications)) {
            return;
        }

        $lines[] = $labels['certifications'] ?? 'CERTIFICATIONS';
        $lines[] = $separator;
        foreach ($certifications as $cert) {
            $lines[] = '• '.(is_array($cert) ? ($cert['name'] ?? implode(', ', $cert)) : $cert);
        }
        $lines[] = '';
    }

    private function appendCvLanguages(array &$lines, array $labels, array $languages, string $separator): void
    {
        if (empty($languages)) {
            return;
        }

        $lines[] = $labels['languages'] ?? 'LANGUAGES';
        $lines[] = $separator;
        foreach ($languages as $lang) {
            $lines[] = '• '.(is_array($lang) ? ($lang['language'] ?? implode(', ', $lang)) : $lang);
        }
        $lines[] = '';
    }

    /**
     * Alias de rétrocompatibilité pour documents
     */
    public function documents(Request $request)
    {
        return $this->outils($request);
    }

    /**
     * Analyse un nouveau CV pour le jeune connecté
     */
    public function analyzeCv(Request $request, CvAnalysisService $cvService)
    {
        $validated = $request->validate([
            'cv_file' => ['required', 'file', 'mimes:pdf,docx,png,jpg,jpeg', 'max:5120'],
        ], [
            'cv_file.required' => 'Veuillez sélectionner un fichier CV à importer.',
            'cv_file.file' => 'Le document téléversé est invalide.',
            'cv_file.mimes' => 'Format non supporté. Formats acceptés : PDF, DOCX, JPG ou PNG.',
            'cv_file.max' => 'La taille maximale autorisée est de 5 Mo.',
        ]);

        try {
            $user = auth()->user();
            $file = $validated['cv_file'];

            // Éviter les soumissions multiples répétées (ex: multi-clics successifs dans les 30 dernières secondes)
            $recent = $user->cvAnalyses()
                ->where('original_filename', $file->getClientOriginalName())
                ->where('file_size', $file->getSize())
                ->where('created_at', '>=', now()->subSeconds(30))
                ->first();

            $analysis = $recent ?: $cvService->processAndAnalyze($file, $user);

            return redirect()->route('jeune.outils', ['tab' => 'cv', 'cv_id' => $analysis->id])
                ->with('success', 'Votre CV a été analysé avec succès ! Votre nouveau score Career est de '.$analysis->global_score.'/100.');
        } catch (\Exception $e) {
            Log::error('Erreur analyse CV jeune: '.$e->getMessage());

            return back()->withErrors([
                'cv_file' => 'Une erreur est survenue lors de l\'analyse de votre CV. Veuillez réessayer.',
            ]);
        }
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
                $q->where('country', 'like', '%'.$request->country.'%');
            });
        }

        // Recherche textuelle
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bio', 'like', '%'.$search.'%')
                    ->orWhere('current_position', 'like', '%'.$search.'%')
                    ->orWhere('current_company', 'like', '%'.$search.'%')
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', '%'.$search.'%');
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

        // --- PRIVATE CIRCLE RESTRICTION ---
        if ($user->hasPrivateCircleRestriction()) {
            $orgIds = $user->getPrivateCircleOrganizationIds();
            $query->whereHas('user.organizations', function ($q) use ($orgIds) {
                $q->whereIn('organizations.id', $orgIds);
            });
        } else {
            // General youths cannot see Private Circle Plus mentors
            $query->whereDoesntHave('user.organizations', function ($q) {
                $q->where('private_circle_plus_enabled', true);
            });
        }

        $mentors = $query->orderByDesc('is_validated')
            ->inRandomOrder()
            ->paginate(12);

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
        $user = auth()->user();
        // Incrémenter le compteur de vues (global)
        $mentor->increment('profile_views');
        // Enregistrer la vue spécifique de l'utilisateur
        if (auth()->check()) {
            MentorProfileView::create([
                'user_id' => auth()->id(),
                'mentor_id' => $mentor->user_id,
                'viewed_at' => now(),
            ]);
        }

        $mentor->load(['user', 'roadmapSteps']);

        // Mentors similaires (meme specialisation)
        $similarMentors = MentorProfile::where('is_published', true)
            ->where('id', '!=', $mentor->id)
            ->where('specialization', $mentor->specialization)
            ->with('user')
            ->limit(3)
            ->get();

        // Vérifier s'il y a déjà une relation de mentorat (pending ou accepted)
        $existingMentorship = auth()->user()->mentorshipsAsMentee()
            ->where('mentor_id', $mentor->user_id)
            ->latest()
            ->first();

        // --- PRIVATE CIRCLE RESTRICTION ---
        // 1. If the viewing user is in a private circle, they can only see mentors from their circle.
        if ($user->hasPrivateCircleRestriction()) {
            $myOrgIds = $user->getPrivateCircleOrganizationIds();
            $isSameOrg = $mentor->user->organizations()
                ->whereIn('organizations.id', $myOrgIds)
                ->exists();

            if (! $isSameOrg) {
                abort(403, "Ce profil n'est pas accessible dans le cadre de votre cercle privé.");
            }
        }

        // 2. Check if the MENTOR is in a private circle (to restrict the request form)
        $mentorPrivateOrgIds = $mentor->user->organizations()
            ->where('private_circle_enabled', true)
            ->pluck('organizations.id')
            ->toArray();

        $canRequestMentorship = $mentor->accepts_mentorship_requests ?? true;
        $isClosedByMentor = ! ($mentor->accepts_mentorship_requests ?? true);

        if ($canRequestMentorship && ! empty($mentorPrivateOrgIds)) {
            // The mentor is private. The user must be in at least one of these organizations.
            $userOrgIds = $user->organizations()->pluck('organizations.id')->toArray();
            $canRequestMentorship = ! empty(array_intersect($mentorPrivateOrgIds, $userOrgIds));
        }

        $resources = \App\Models\Resource::where('user_id', $mentor->user_id)
            ->where('is_published', true)
            ->where('is_validated', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('jeune.mentor-show', [
            'mentor' => $mentor,
            'similarMentors' => $similarMentors,
            'existingMentorship' => $existingMentorship,
            'canRequestMentorship' => $canRequestMentorship,
            'isClosedByMentor' => $isClosedByMentor,
            'resources' => $resources,
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

        $file = $validated['document'];
        $path = $file->store('documents/'.auth()->id(), 'public');

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

        if (! Storage::disk('public')->exists($doc->file_path)) {
            abort(404);
        }

        return response()->download(Storage::disk('public')->path($doc->file_path), $doc->file_name);
    }

    /**
     * Prévisualiser un document
     */
    public function viewDocument($document)
    {
        $doc = auth()->user()->academicDocuments()->findOrFail($document);

        if (! Storage::disk('public')->exists($doc->file_path)) {
            abort(404);
        }

        $path = Storage::disk('public')->path($doc->file_path);
        $ext = strtolower(pathinfo($doc->file_name ?: $doc->file_path, PATHINFO_EXTENSION));

        if ($ext === 'docx' || str_contains($doc->mime_type ?? '', 'wordprocessingml')) {
            $cvService = app(CvAnalysisService::class);
            $extractedText = $cvService->extractText($path, 'docx');

            return view('jeune.documents_docx_preview', [
                'document' => $doc,
                'content' => $extractedText,
            ]);
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
    public function sendChatMessage(Request $request, BrillioIAService $brillioIAService)
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
            'api_configured' => $brillioIAService->isApiKeyConfigured(),
        ]);

        // Recuperer ou creer la conversation
        if ($conversationId) {
            $conversation = ChatConversation::where('id', $conversationId)
                ->where('user_id', $user->id)
                ->first();

            if (! $conversation) {
                Log::warning('Conversation non trouvee', ['conversation_id' => $conversationId]);

                return response()->json([
                    'success' => false,
                    'error' => 'Conversation non trouvee',
                ], 404);
            }
            Log::info('Conversation existante trouvee', ['conversation_id' => $conversation->id]);
        } else {
            // Check if it's the first conversation (free) or subsequent (10 credits)
            $conversationCount = ChatConversation::where('user_id', $user->id)->count();

            if ($conversationCount >= 1) {
                if ($user->credits_balance < 10) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Solde insuffisant (10 crédits requis pour une nouvelle conversation).',
                        'redirect_to_wallet' => true,
                        'wallet_url' => route('jeune.wallet.index'),
                    ], 402);
                }

                // Deduct credits
                app(WalletService::class)->deductCredits(
                    $user,
                    10,
                    'feature_use',
                    'Nouvelle conversation avec l\'Assistant Brillio'
                );
            }

            $conversation = $brillioIAService->createConversation($user);
            Log::info('Nouvelle conversation creee', ['conversation_id' => $conversation->id]);
        }

        try {
            // Si le support humain est actif, on n'envoie pas à l'IA
            if ($conversation->human_support_active) {
                // On enregistre juste le message de l'utilisateur
                $conversation->messages()->create([
                    'role' => ChatMessage::ROLE_USER,
                    'content' => $validated['message'],
                ]);

                return response()->json([
                    'success' => true,
                    'conversation_id' => $conversation->id,
                    'conversation_title' => $conversation->title,
                    'message' => null, // Pas de réponse de l'IA
                    'is_from_human' => false,
                    'sender_name' => 'Assistant Brillio',
                    'needs_human_support' => (bool) $conversation->needs_human_support,
                    'is_human_support_active' => (bool) $conversation->human_support_active,
                    'api_used' => false,
                ]);
            }

            // Envoyer le message et obtenir la reponse
            Log::info('Appel DeepSeekService->sendMessage');
            $assistantMessage = $brillioIAService->sendMessage($conversation, $validated['message']);
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
                'is_from_human' => false,
                'sender_name' => 'Assistant Brillio',
                'needs_human_support' => (bool) $conversation->needs_human_support,
                'is_human_support_active' => (bool) $conversation->human_support_active,
                'api_used' => $brillioIAService->isApiKeyConfigured(),
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
            ->with('admin')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($m) {
                $call = null;
                if (preg_match('/\[ADVISOR_VIDEO_CALL:(\d+)\]/', $m->content, $matches)) {
                    $c = AdvisorVideoCall::find($matches[1]);
                    if ($c) {
                        $call = [
                            'id' => $c->id,
                            'status' => $c->status,
                            'credits_cost' => $c->credits_cost,
                            'meeting_id' => $c->meeting_id,
                            'initiated_by' => $c->initiated_by,
                        ];
                    }
                }

                return [
                    'id' => $m->id,
                    'role' => $m->role,
                    'content' => $m->content,
                    'is_from_human' => (bool) $m->is_from_human,
                    'is_system_message' => (bool) $m->is_system_message,
                    'sender_name' => $m->is_from_human ? ($m->admin?->name ?? 'Conseiller') : 'Assistant Brillio',
                    'advisor_video_call' => $call,
                ];
            });

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'needs_human_support' => (bool) $conversation->needs_human_support,
            'is_human_support_active' => (bool) $conversation->human_support_active,
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

        // Vérifier le solde de crédits (10 crédits requis)
        if ($user->credits_balance < 10) {
            return response()->json([
                'success' => false,
                'error' => 'Solde insuffisant (10 crédits requis pour parler à un conseiller).',
                'redirect_to_wallet' => true,
                'wallet_url' => route('jeune.wallet.index'),
            ], 402);
        }

        // Déduire les crédits
        app(WalletService::class)->deductCredits(
            $user,
            10,
            'feature_use',
            'Demande de conseiller d\'orientation expert'
        );

        // Marquer la conversation comme nécessitant une assistance humaine
        $conversation->update(['needs_human_support' => true]);

        // Créer un message système pour informer l'utilisateur
        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => 'Votre demande de discussion avec un conseiller a été envoyée, un conseiller prendra la conversation en charge dans les meilleurs délais.',
            'is_from_human' => false,
            'is_system_message' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Demande d\'assistance envoyée (10 crédits déduits)',
        ]);
    }

    /**
     * Annuler une demande d'assistance humaine
     */
    public function cancelHumanSupport(ChatConversation $conversation)
    {
        $user = auth()->user();

        // Vérifier que la conversation appartient à l'utilisateur
        if ($conversation->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Conversation non trouvée',
            ], 404);
        }

        // Annuler la demande
        $conversation->update(['needs_human_support' => false]);

        // Créer un message système pour informer l'utilisateur
        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => 'Fin de la demande de discussion avec conseiller d\'orientation, vous pouvez continuer à discuter avec l\'assistant IA.',
            'is_from_human' => false,
            'is_system_message' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Demande d\'assistance annulée',
        ]);
    }
}
