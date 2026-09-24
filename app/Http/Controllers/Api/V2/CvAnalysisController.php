<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\CvAnalysis;
use App\Models\SystemSetting;
use App\Services\CvAnalysisService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use OpenApi\Annotations as OA;

/**
 * Controller pour l'analyse de CV via API Mobile
 *
 * Expose toutes les fonctionnalités CV disponibles sur le web :
 * - Upload + analyse IA d'un CV (PDF, DOCX, image)
 * - Liste des analyses passées
 * - Détail d'une analyse
 * - Réanalyse IA d'un CV existant
 * - Mise à jour de balises guidées (placeholders)
 * - Téléchargement du CV ATS (DOCX/PDF) avec déduction de crédits
 * - Copie texte du CV ATS
 *
 * @OA\Tag(name="CV Analysis", description="Analyse IA de CV pour les jeunes")
 */
class CvAnalysisController extends Controller
{
    public function __construct(
        private CvAnalysisService $cvService,
        private WalletService $walletService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v2/cv",
     *     summary="Liste toutes les analyses CV de l'utilisateur connecté",
     *     tags={"CV Analysis"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Liste des analyses CV",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="analyses", type="array",
     *
     *                     @OA\Items(
     *
     *                         @OA\Property(property="id", type="integer", example=42),
     *                         @OA\Property(property="original_filename", type="string", example="mon_cv.pdf"),
     *                         @OA\Property(property="global_score", type="integer", example=78),
     *                         @OA\Property(property="status_label", type="string", example="Très bien"),
     *                         @OA\Property(property="candidate_name", type="string", example="Jean Dupont"),
     *                         @OA\Property(property="candidate_title", type="string", example="Ingénieur logiciel"),
     *                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                     )
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=3)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Non authentifié")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $analyses = $user->cvAnalyses()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($a) => $this->formatAnalysisSummary($a));

        return response()->json([
            'success' => true,
            'message' => 'Succès',
            'data' => ['analyses' => $analyses, 'total' => $analyses->count()],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v2/cv/analyze",
     *     summary="Upload et analyse IA d'un nouveau CV",
     *     description="Téléverse un fichier CV (PDF, DOCX, JPG, PNG) et lance son analyse IA. Retourne le résultat complet : score global, scores par critère, points forts, axes d'amélioration, recommandations et CV ATS structuré.",
     *     tags={"CV Analysis"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *                 required={"cv_file"},
     *
     *                 @OA\Property(
     *                     property="cv_file",
     *                     type="string",
     *                     format="binary",
     *                     description="Fichier CV. Formats : PDF, DOCX, JPG, PNG. Max : 5 Mo."
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Analyse terminée avec succès",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="CV analysé avec succès ! Score : 78/100"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="analysis", ref="#/components/schemas/CvAnalysis")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=409, description="Analyse identique soumise récemment (anti-doublon 30s)"),
     *     @OA\Response(response=422, description="Fichier manquant ou format non supporté"),
     *     @OA\Response(response=500, description="Erreur serveur lors de l'analyse IA")
     * )
     */
    public function analyze(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cv_file' => ['required', 'file', 'mimes:pdf,docx,png,jpg,jpeg', 'max:5120'],
        ], [
            'cv_file.required' => 'Veuillez sélectionner un fichier CV à importer.',
            'cv_file.mimes' => 'Format non supporté. Formats acceptés : PDF, DOCX, JPG ou PNG.',
            'cv_file.max' => 'La taille maximale autorisée est de 5 Mo.',
        ]);

        $user = $request->user();
        $file = $validated['cv_file'];

        // Anti-doublon : même fichier soumis dans les 30 dernières secondes
        $recent = $user->cvAnalyses()
            ->where('original_filename', $file->getClientOriginalName())
            ->where('file_size', $file->getSize())
            ->where('created_at', '>=', now()->subSeconds(30))
            ->first();

        if ($recent) {
            return response()->json([
                'success' => true,
                'message' => 'CV déjà analysé récemment. Résultat retourné.',
                'data' => ['analysis' => $this->formatAnalysisFull($recent)],
            ], 200);
        }

        try {
            $analysis = $this->cvService->processAndAnalyze($file, $user);

            return response()->json([
                'success' => true,
                'message' => "CV analysé avec succès ! Score Career : {$analysis->global_score}/100",
                'data' => ['analysis' => $this->formatAnalysisFull($analysis)],
            ], 201);
        } catch (\Exception $e) {
            Log::error('API: Erreur analyse CV', ['error' => $e->getMessage(), 'user' => $user->id]);

            return response()->json(['success' => false, 'message' => 'Une erreur est survenue lors de l\'analyse de votre CV. Veuillez réessayer.'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v2/cv/{id}",
     *     summary="Détail complet d'une analyse CV",
     *     tags={"CV Analysis"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Détail de l'analyse",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="analysis", ref="#/components/schemas/CvAnalysis")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=404, description="CV introuvable ou non autorisé")
     * )
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $analysis = $request->user()->cvAnalyses()->find($id);

        if (! $analysis) {
            return response()->json(['success' => false, 'message' => 'CV introuvable ou non autorisé.'], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Succès',
            'data' => ['analysis' => $this->formatAnalysisFull($analysis)],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v2/cv/{id}/reanalyze",
     *     summary="Relance l'analyse IA d'un CV existant sans re-upload",
     *     description="Réanalyse le CV avec la dernière version du modèle IA sans avoir à re-téléverser le fichier. Crée une nouvelle entrée d'analyse.",
     *     tags={"CV Analysis"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Réanalyse terminée",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="analysis", ref="#/components/schemas/CvAnalysis"),
     *                 @OA\Property(property="new_id", type="integer", example=43)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=404, description="CV introuvable ou non autorisé")
     * )
     */
    public function reanalyze(Request $request, int $id): JsonResponse
    {
        $analysis = $request->user()->cvAnalyses()->find($id);

        if (! $analysis) {
            return response()->json(['success' => false, 'message' => 'CV introuvable ou non autorisé.'], 404);
        }

        try {
            $newAnalysis = $this->cvService->reanalyze($analysis);

            return response()->json([
                'success' => true,
                'message' => "CV réanalysé avec succès ! Nouveau score : {$newAnalysis->global_score}/100",
                'data' => ['analysis' => $this->formatAnalysisFull($newAnalysis), 'new_id' => $newAnalysis->id],
            ], 201);
        } catch (\Exception $e) {
            Log::error('API: Erreur réanalyse CV', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Une erreur est survenue lors de la réanalyse. Veuillez réessayer.'], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v2/cv/{id}",
     *     summary="Supprime une analyse CV",
     *     tags={"CV Analysis"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Analyse supprimée"),
     *     @OA\Response(response=404, description="CV introuvable ou non autorisé")
     * )
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $analysis = $request->user()->cvAnalyses()->find($id);

        if (! $analysis) {
            return response()->json(['success' => false, 'message' => 'CV introuvable ou non autorisé.'], 404);
        }

        $analysis->delete();

        return response()->json(['success' => true, 'message' => 'Analyse CV supprimée avec succès.']);
    }

    /**
     * @OA\Post(
     *     path="/api/v2/cv/{id}/placeholder",
     *     summary="Met à jour une balise guidée dans le CV ATS",
     *     description="Permet à l'utilisateur de remplir les balises de guidage ([guide:...]) générées dans le CV ATS optimisé. Chaque balise peut être remplie avec une valeur personnalisée ou réinitialisée.",
     *     tags={"CV Analysis"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"field_type", "original_tag"},
     *
     *             @OA\Property(property="field_type", type="string", enum={"summary", "experience"}, example="experience"),
     *             @OA\Property(property="exp_index", type="integer", example=0, description="Index 0-based de l'expérience (requis si field_type=experience)"),
     *             @OA\Property(property="bullet_index", type="integer", example=1, description="Index 0-based de la puce dans l'expérience"),
     *             @OA\Property(property="original_tag", type="string", example="[guide:Résultat chiffré obtenu]"),
     *             @OA\Property(property="new_value", type="string", example="Augmentation de 30% des ventes"),
     *             @OA\Property(property="custom_value", type="string", description="Alias de new_value")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Balise mise à jour",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="is_filled", type="boolean", example=true),
     *                 @OA\Property(property="new_value", type="string", example="Augmentation de 30% des ventes"),
     *                 @OA\Property(property="original_tag", type="string"),
     *                 @OA\Property(property="new_tag", type="string", example="[rempli:Augmentation de 30% des ventes|guide:Résultat chiffré obtenu]")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=404, description="CV introuvable ou non autorisé")
     * )
     */
    public function updatePlaceholder(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'field_type' => 'required|string|in:summary,experience',
            'exp_index' => 'nullable|integer|min:0',
            'bullet_index' => 'nullable|integer|min:0',
            'original_tag' => 'required|string|max:500',
            'new_value' => 'nullable|string|max:500',
            'custom_value' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $analysis = $user->cvAnalyses()->find($id);

        if (! $analysis) {
            return $this->error('CV introuvable ou non autorisé.', 404);
        }

        $parsed = $analysis->parsed_content ?? [];
        [$cleanGuide, $guideTagWithBrackets] = $this->extractGuideTagInfo($validated['original_tag']);
        $newValue = trim((string) ($validated['new_value'] ?? ($validated['custom_value'] ?? '')));
        $replacement = $newValue !== ''
            ? "[rempli:{$newValue}|guide:{$cleanGuide}]"
            : $guideTagWithBrackets;

        $pattern = $this->buildGuidePattern($cleanGuide, $guideTagWithBrackets);
        $updated = $this->applyPlaceholderReplacement($parsed, $validated['field_type'], $validated, $pattern, $replacement);

        if ($updated) {
            $analysis->update(['parsed_content' => $parsed]);
        }

        return response()->json([
            'success' => true,
            'message' => $newValue !== '' ? 'Information enregistrée avec succès !' : 'Balise réinitialisée.',
            'data' => [
                'is_filled' => $newValue !== '',
                'new_value' => $newValue,
                'original_tag' => $guideTagWithBrackets,
                'new_tag' => $replacement,
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v2/cv/{id}/action",
     *     summary="Copier ou télécharger le CV ATS optimisé",
     *     description="Permet de récupérer le texte du CV ATS ou d'obtenir une URL de téléchargement signée. Certaines actions consomment des crédits (voir `cost` dans la réponse).",
     *     tags={"CV Analysis"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"action"},
     *
     *             @OA\Property(
     *                 property="action",
     *                 type="string",
     *                 enum={"copy", "download_docx", "download_pdf"},
     *                 description="copy = texte brut (coûte des crédits). download_docx / download_pdf = URL de téléchargement (coûte des crédits selon le template).",
     *                 example="copy"
     *             ),
     *             @OA\Property(
     *                 property="template",
     *                 type="integer",
     *                 minimum=0,
     *                 maximum=5,
     *                 default=0,
     *                 description="Template visuel pour le téléchargement (0=Basic ATS gratuit, 1-5=templates premium).",
     *                 example=0
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Action exécutée avec succès",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="action", type="string", example="copy"),
     *                 @OA\Property(property="format", type="string", enum={"text","docx","pdf"}, example="text"),
     *                 @OA\Property(property="cost", type="integer", example=1, description="Crédits déduits"),
     *                 @OA\Property(property="remaining_balance", type="integer", example=9),
     *                 @OA\Property(property="cv_text", type="string", description="Texte brut du CV (uniquement pour action=copy)"),
     *                 @OA\Property(property="download_url", type="string", description="URL signée valide 60 min (pour download_docx/download_pdf)")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=402, description="Crédits insuffisants"),
     *     @OA\Response(response=404, description="CV introuvable ou non autorisé")
     * )
     */
    public function action(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|string|in:copy,download_docx,download_pdf',
            'template' => 'nullable|integer|between:0,5',
        ]);

        $user = $request->user();
        $analysis = $user->cvAnalyses()->find($id);

        if (! $analysis) {
            return $this->error('CV introuvable ou non autorisé.', 404);
        }

        $action = $validated['action'];
        $template = (int) ($validated['template'] ?? 0);
        $isCopy = $action === 'copy';

        // Calcul du coût
        if ($isCopy) {
            $cost = (int) SystemSetting::getValue('feature_cost_cv_copy', 1);
            $description = 'Copie du CV ATS';
        } else {
            $settingKey = $template === 0 ? 'feature_cost_cv_download' : 'feature_cost_cv_template_'.$template;
            $cost = (int) SystemSetting::getValue($settingKey, $template === 0 ? 0 : $template);
            $formatLabel = $action === 'download_pdf' ? 'PDF' : 'Word';
            $description = "Téléchargement CV ATS {$formatLabel} (Template {$template})";
        }

        // Vérification des crédits
        if ($cost > 0 && $user->credits_balance < $cost) {
            return response()->json([
                'success' => false,
                'message' => 'Solde de crédits insuffisant. Veuillez recharger votre portefeuille.',
                'required' => $cost,
                'balance' => (int) $user->credits_balance,
            ], 402);
        }

        // Déduction
        if ($cost > 0) {
            $this->walletService->deductCredits($user, $cost, 'cv_action', $description, $analysis);
        }

        // URL de téléchargement signée (60 min)
        $downloadUrl = null;
        if (! $isCopy) {
            $downloadUrl = URL::temporarySignedRoute(
                'jeune.cv.download-docx',
                now()->addMinutes(60),
                ['cv' => $analysis->id, 'template' => $template]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Succès',
            'data' => [
                'action' => $action,
                'format' => $action === 'download_pdf' ? 'pdf' : ($isCopy ? 'text' : 'docx'),
                'template' => $template,
                'cost' => $cost,
                'remaining_balance' => (int) $user->fresh()->credits_balance,
                'cv_text' => $isCopy ? $this->formatCvAsPlainText($analysis) : null,
                'download_url' => $downloadUrl,
            ],
        ]);
    }

    // =====================================================
    // Formatters
    // =====================================================

    private function formatAnalysisSummary(CvAnalysis $a): array
    {
        return [
            'id' => $a->id,
            'original_filename' => $a->original_filename,
            'global_score' => $a->global_score,
            'status_label' => $a->status_label,
            'candidate_name' => $a->candidate_name,
            'candidate_title' => $a->candidate_title,
            'created_at' => $a->created_at->toISOString(),
        ];
    }

    /**
     * @OA\Schema(
     *     schema="CvAnalysis",
     *     type="object",
     *
     *     @OA\Property(property="id", type="integer", example=42),
     *     @OA\Property(property="original_filename", type="string", example="mon_cv.pdf"),
     *     @OA\Property(property="global_score", type="integer", example=78, description="Score Career /100"),
     *     @OA\Property(property="status_label", type="string", example="Très bien"),
     *     @OA\Property(property="candidate_name", type="string"),
     *     @OA\Property(property="candidate_title", type="string"),
     *     @OA\Property(property="candidate_contact", type="object"),
     *     @OA\Property(property="summary", type="string", description="Résumé IA du profil"),
     *     @OA\Property(property="criteria_scores", type="object",
     *         @OA\Property(property="structure", type="integer"),
     *         @OA\Property(property="clarite", type="integer"),
     *         @OA\Property(property="experiences", type="integer"),
     *         @OA\Property(property="competences", type="integer"),
     *         @OA\Property(property="impact", type="integer"),
     *     ),
     *     @OA\Property(property="strengths", type="array", @OA\Items(type="string")),
     *     @OA\Property(property="improvements", type="array", @OA\Items(type="string")),
     *     @OA\Property(property="recommendations", type="array", @OA\Items(type="string")),
     *     @OA\Property(property="parsed_content", type="object", description="CV ATS structuré avec sections, expériences, compétences, balises guidées"),
     *     @OA\Property(property="created_at", type="string", format="date-time"),
     * )
     */
    private function formatAnalysisFull(CvAnalysis $a): array
    {
        return [
            'id' => $a->id,
            'original_filename' => $a->original_filename,
            'file_size' => $a->file_size,
            'global_score' => $a->global_score,
            'status_label' => $a->status_label,
            'candidate_name' => $a->candidate_name,
            'candidate_title' => $a->candidate_title,
            'candidate_contact' => $a->candidate_contact,
            'summary' => $a->summary,
            'criteria_scores' => $a->criteria_scores,
            'strengths' => $a->strengths,
            'improvements' => $a->improvements,
            'recommendations' => $a->recommendations,
            'parsed_content' => $a->parsed_content,
            'created_at' => $a->created_at->toISOString(),
        ];
    }

    private function formatCvAsPlainText(CvAnalysis $analysis): string
    {
        $parsed = $analysis->parsed_content ?? [];
        $lines = [];

        if (! empty($parsed['header'])) {
            $h = $parsed['header'];
            $lines[] = strtoupper($h['name'] ?? $analysis->candidate_name ?? '');
            if (! empty($h['title'])) {
                $lines[] = $h['title'];
            }
            $lines[] = '';
        }

        if (! empty($parsed['summary'])) {
            $lines[] = 'RÉSUMÉ PROFESSIONNEL';
            $lines[] = str_repeat('-', 40);
            $lines[] = $parsed['summary'];
            $lines[] = '';
        }

        if (! empty($parsed['experiences'])) {
            $lines[] = 'EXPÉRIENCES PROFESSIONNELLES';
            $lines[] = str_repeat('-', 40);
            foreach ($parsed['experiences'] as $exp) {
                $lines[] = ($exp['title'] ?? '').(' | '.($exp['company'] ?? '')).(' | '.($exp['dates'] ?? ''));
                foreach ($exp['bullets'] ?? [] as $bullet) {
                    $lines[] = '  • '.$bullet;
                }
                $lines[] = '';
            }
        }

        if (! empty($parsed['education'])) {
            $lines[] = 'FORMATION';
            $lines[] = str_repeat('-', 40);
            foreach ($parsed['education'] as $edu) {
                $lines[] = ($edu['degree'] ?? '').(' — '.($edu['school'] ?? '')).(' ('.($edu['dates'] ?? '').')');
            }
            $lines[] = '';
        }

        if (! empty($parsed['skills'])) {
            $lines[] = 'COMPÉTENCES';
            $lines[] = str_repeat('-', 40);
            $lines[] = implode(' • ', $parsed['skills']);
        }

        return implode("\n", $lines);
    }

    // =====================================================
    // Placeholder helpers (répliqués depuis JeuneDashboardController)
    // =====================================================

    private function extractGuideTagInfo(string $rawOriginalTag): array
    {
        $trimmedTag = trim($rawOriginalTag);
        if (preg_match('/guide:([^\]]+)/u', $trimmedTag, $matches)) {
            $cleanGuide = trim($matches[1]);
        } else {
            $cleanGuide = trim(preg_replace('/^\[rempli:[^\|]+\|guide:|^\[guide:|\]$/u', '', $trimmedTag));
        }

        $guideTagWithBrackets = "[guide:{$cleanGuide}]";

        return [$cleanGuide, $guideTagWithBrackets];
    }

    private function buildGuidePattern(string $cleanGuide, string $guideTagWithBrackets): string
    {
        $escapedGuide = preg_quote($cleanGuide, '/');

        return '/(\[rempli:[^\]]*\|guide:'.$escapedGuide.'\]|\[guide:'.$escapedGuide.'\])/u';
    }

    private function applyPlaceholderReplacement(array &$parsed, string $fieldType, array $validated, string $pattern, string $replacement): bool
    {
        $updated = false;

        if ($fieldType === 'summary') {
            if (isset($parsed['summary']) && preg_match($pattern, $parsed['summary'])) {
                $parsed['summary'] = preg_replace($pattern, $replacement, $parsed['summary']);
                $updated = true;
            }
        } elseif ($fieldType === 'experience') {
            $expIndex = $validated['exp_index'] ?? null;
            $bulletIndex = $validated['bullet_index'] ?? null;

            if ($expIndex !== null && isset($parsed['experiences'][$expIndex])) {
                $experience = &$parsed['experiences'][$expIndex];

                if ($bulletIndex !== null && isset($experience['bullets'][$bulletIndex])) {
                    if (preg_match($pattern, $experience['bullets'][$bulletIndex])) {
                        $experience['bullets'][$bulletIndex] = preg_replace($pattern, $replacement, $experience['bullets'][$bulletIndex]);
                        $updated = true;
                    }
                } elseif (isset($experience['description']) && preg_match($pattern, $experience['description'])) {
                    $experience['description'] = preg_replace($pattern, $replacement, $experience['description']);
                    $updated = true;
                }
            }
        }

        return $updated;
    }

    // Reuse parent Controller protected methods (success/error)
    // No private overrides needed.
}
