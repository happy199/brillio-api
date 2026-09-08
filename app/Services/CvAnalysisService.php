<?php

namespace App\Services;

use App\Models\CvAnalysis;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser;

class CvAnalysisService
{
    protected BrillioIAService $brillioIAService;

    public function __construct(BrillioIAService $brillioIAService)
    {
        $this->brillioIAService = $brillioIAService;
    }

    /**
     * Traite un fichier CV téléversé, extrait son contenu et réalise l'analyse IA.
     */
    public function processAndAnalyze(UploadedFile $file, ?User $user = null): CvAnalysis
    {
        $originalFilename = $file->getClientOriginalName();
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';
        $fileSize = $file->getSize();

        // 1. Sauvegarde sécurisée du fichier dans le stockage local/public
        $folder = $user ? 'cv_analyses/users/'.$user->id : 'cv_analyses/guests';
        $path = $file->store($folder, 'public');

        // 2. Extraction du texte selon le format
        $fullPath = storage_path('app/public/'.$path);
        $extractedText = $this->extractText($fullPath, $file->getClientOriginalExtension());

        // 3. Analyse via IA avec fallback robuste
        $analysisResult = $this->analyzeWithAI($extractedText, $originalFilename);

        // 4. Génération d'un token invité unique et persistance
        $guestToken = Str::random(40);
        $globalScore = (int) ($analysisResult['global_score'] ?? 65);
        $globalScore = max(10, min(99, $globalScore));
        $statusLabel = CvAnalysis::determineStatusLabel($globalScore);

        $parsedContent = $analysisResult['parsed_content'] ?? [];
        if (empty($parsedContent['raw_text'])) {
            $parsedContent['raw_text'] = $extractedText;
        }

        return CvAnalysis::create([
            'user_id' => $user?->id,
            'guest_token' => $guestToken,
            'original_filename' => $originalFilename,
            'file_path' => $path,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'candidate_name' => $analysisResult['candidate_name'] ?? 'Candidat Brillio',
            'candidate_title' => $analysisResult['candidate_title'] ?? 'Profil Professionnel',
            'candidate_contact' => $analysisResult['candidate_contact'] ?? [],
            'parsed_content' => $parsedContent,
            'global_score' => $globalScore,
            'status_label' => $statusLabel,
            'criteria_scores' => $analysisResult['criteria_scores'] ?? [
                'structure' => 65,
                'clarite' => 70,
                'experiences' => 60,
                'competences' => 68,
                'impact' => 62,
            ],
            'strengths' => $analysisResult['strengths'] ?? [
                'Structure globale lisible et aérée',
                'Parcours académique et diplômes bien mentionnés',
                'Compétences techniques identifiables rapidement',
            ],
            'improvements' => $analysisResult['improvements'] ?? [
                'Quantifier davantage vos réalisations avec des chiffres et métriques d\'impact',
                'Ajouter une phrase d\'accroche professionnelle percutante sous le titre',
                'Harmoniser les dates et l\'intitulé précis des missions exercées',
            ],
            'recommendations' => $analysisResult['recommendations'] ?? [
                'Utilisez des verbes d\'action au début de chaque puce d\'expérience',
                'Adaptez vos compétences clés aux mots-clés recherchés par les recruteurs du secteur',
                'Veillez à inclure un lien vers votre profil LinkedIn ou portfolio en ligne',
            ],
            'summary' => $analysisResult['summary'] ?? 'Profil prometteur avec de bonnes bases. En structurant davantage les réalisations concrètes, ce CV gagnera significativement en attractivité auprès des recruteurs.',
            'is_claimed' => (bool) $user,
        ]);
    }

    /**
     * Extrait le texte d'un fichier selon son extension
     */
    public function extractText(string $filePath, string $extension): string
    {
        $extension = strtolower($extension);

        if (! file_exists($filePath)) {
            return '';
        }

        try {
            if ($extension === 'pdf') {
                $parser = new Parser;
                $pdf = $parser->parseFile($filePath);
                $text = $pdf->getText();

                return $this->cleanExtractedText($text);
            }

            if (in_array($extension, ['docx', 'doc'])) {
                return $this->extractFromDocx($filePath);
            }

            if (in_array($extension, ['png', 'jpg', 'jpeg'])) {
                // Pour les images sans OCR binaire système lourd, extraire le nom et fournir un contexte
                return 'CV au format image : '.basename($filePath);
            }
        } catch (\Exception $e) {
            Log::warning('Échec extraction texte CV', [
                'path' => $filePath,
                'error' => $e->getMessage(),
            ]);
        }

        return '';
    }

    /**
     * Extraction de texte native pour les fichiers DOCX via XML
     */
    private function extractFromDocx(string $filePath): string
    {
        if (! class_exists('ZipArchive')) {
            return '';
        }

        $zip = new \ZipArchive;
        if ($zip->open($filePath) === true) {
            $xmlContent = $zip->getFromName('word/document.xml');
            $zip->close();

            if ($xmlContent) {
                // Remplacer les balises de paragraphe et de saut de ligne
                $xmlContent = str_replace(['</w:p>', '</w:tr>', '<w:br/>'], "\n", $xmlContent);
                $text = strip_tags($xmlContent);

                return $this->cleanExtractedText($text);
            }
        }

        return '';
    }

    /**
     * Nettoyage et normalisation UTF-8 du texte
     */
    private function cleanExtractedText(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Remplacer caractères de contrôle non imprimables
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text) ?? $text;
        // Normaliser les espaces multiples
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;

        return trim($text);
    }

    /**
     * Analyse le texte du CV via l'IA ou produit une analyse basée sur des heuristiques
     */
    private function analyzeWithAI(string $text, string $originalFilename): array
    {
        // Limiter la taille du texte pour éviter de dépasser les quotas de tokens
        $sampleText = mb_substr($text, 0, 8000);

        if (mb_strlen($sampleText) < 40) {
            return $this->generateFallbackAnalysis($originalFilename);
        }

        $systemPrompt = "Tu es un expert mondial en recrutement et spécialiste des systèmes ATS (Applicant Tracking Systems tels que Workday, Taleo, Greenhouse, Lever, SmartRecruiters). Tu analyses un CV pour évaluer sa compatibilité ATS, sa structure, sa lisibilité algorithmique et son impact humain auprès des recruteurs, quel que soit le secteur d'activité (Tech, Finance, Marketing, Droit, Santé, Logistique, Enseignement, Commerce, etc.) et quel que soit le niveau d'expérience (débutant, intermédiaire, senior).

RÈGLES D'ÉVALUATION ATS UNIVERSELLES :
1. Structure & Parsage (25%) : Lisibilité des rubriques standardisées (Profil, Expériences, Formation, Certifications, Compétences, Langues), détection des sections sans dépendance à des éléments graphiques.
2. Clarté & Coordonnées (20%) : Présence d'un titre de poste clair, coordonnées nettoyées (téléphone, email, ville/pays).
3. Chronologie & Dates (15%) : Cohérence des dates permettant au parser ATS de calculer l'ancienneté.
4. Quantification de l'Impact (20%) : Application de la formule Google XYZ / méthode STAR (verbes d'action + indicateurs chiffrés, pourcentages, volumes).
5. Mots-clés & Compétences Métier (20%) : Pertinence et densité du vocabulaire technique et méthodologique adapté au métier ciblé, avec sigles et noms complets.

RÈGLE ABSOLUE :
Réponds UNIQUEMENT avec un objet JSON valide, sans aucune balise markdown, ni texte avant ou après.

Format JSON attendu :
{
  \"candidate_name\": \"Nom et prénom du candidat détecté (ou 'Candidat')\",
  \"candidate_title\": \"Intitulé du poste ou spécialité ciblée\",
  \"candidate_contact\": {
    \"phone\": \"Numéro ou non spécifié\",
    \"email\": \"Email ou non spécifié\",
    \"location\": \"Ville, Pays ou non spécifié\"
  },
  \"parsed_content\": {
    \"profil\": \"Résumé du profil professionnel en 2-3 phrases accrocheuses\",
    \"experiences\": [\"Intitulé poste - Entreprise (Dates) : mission formulée avec verbe d'action et impact chiffré si possible\"],
    \"formation\": [\"Diplôme ou Cursus - Établissement (Année)\"],
    \"certifications\": [\"Certification professionnelle reconnue (ex: CKA, AWS, PMP, Scrum)\"],
    \"competences\": [\"Compétence 1\", \"Compétence 2\", \"Compétence 3\"],
    \"langues\": [\"Français (Courant)\", \"Anglais (Professionnel)\"]
  },
  \"global_score\": 75,
  \"criteria_scores\": {
    \"structure\": 75,
    \"clarite\": 80,
    \"experiences\": 70,
    \"competences\": 75,
    \"impact\": 65
  },
  \"strengths\": [
    \"Point fort concret (ex: rubriques bien normalisées pour les parsers ATS)\",
    \"Point fort 2\",
    \"Point fort 3\"
  ],
  \"improvements\": [
    \"Axe d'amélioration actionnable (ex: chiffrer les accomplissements dans les expériences)\",
    \"Axe d'amélioration 2\",
    \"Axe d'amélioration 3\"
  ],
  \"recommendations\": [
    \"Conseil pratique directement applicable pour franchir les filtres ATS\",
    \"Recommandation 2\",
    \"Recommandation 3\"
  ],
  \"summary\": \"Synthèse d'évaluation ATS en 2-3 phrases valorisante et constructive.\"
}";

        $userPrompt = "Voici le texte extrait du CV ('{$originalFilename}') :\n\n".$sampleText."\n\nÉvalue ce CV selon les standards stricts des filtres ATS et des recruteurs. Attribue une note globale réaliste entre 40 et 92 selon la qualité de la structure et du contenu.";

        try {
            $response = $this->brillioIAService->analyzeText($userPrompt, $systemPrompt);
            $cleanJson = $this->brillioIAService->cleanJson($response);
            $decoded = json_decode($cleanJson, true);

            if (is_array($decoded) && isset($decoded['global_score'])) {
                return $decoded;
            }
        } catch (\Exception $e) {
            Log::warning('Échec appel IA analyse CV, utilisation du fallback heuristique', [
                'error' => $e->getMessage(),
            ]);
        }

        return $this->generateHeuristicAnalysis($sampleText, $originalFilename);
    }

    /**
     * Analyse heuristique si l'IA distante est indisponible
     */
    private function generateHeuristicAnalysis(string $text, string $originalFilename): array
    {
        // Détecter un nom potentiel
        $lines = array_filter(array_map('trim', explode("\n", $text)));
        $firstLine = reset($lines) ?: 'Candidat Brillio';
        $candidateName = mb_strlen($firstLine) < 50 ? $firstLine : 'Candidat';

        // Détection email
        preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text, $emailMatches);
        $email = $emailMatches[0] ?? null;

        // Détection téléphone
        preg_match('/(?:\+?\d{1,4}[ -]?)?\(?\d{2,4}\)?[ -]?\d{2,4}[ -]?\d{2,4}/', $text, $phoneMatches);
        $phone = $phoneMatches[0] ?? null;

        $hasExp = (bool) preg_match('/exp[eé]rience/i', $text);
        $hasForm = (bool) preg_match('/formation|dipl[oô]me|[eé]tude|education|degree/i', $text);
        $hasComp = (bool) preg_match('/comp[eé]tence|skills/i', $text);
        $hasCert = (bool) preg_match('/certif|cka|aws|azure|gcp|cisco|pmp|scrum|itil/i', $text);

        $baseScore = 55;
        if ($hasExp) {
            $baseScore += 12;
        }
        if ($hasForm) {
            $baseScore += 10;
        }
        if ($hasComp) {
            $baseScore += 8;
        }
        if ($email && $phone) {
            $baseScore += 5;
        }
        $score = min(88, max(45, $baseScore));

        return [
            'candidate_name' => $candidateName,
            'candidate_title' => 'Candidat & Talent',
            'candidate_contact' => [
                'phone' => $phone ?? 'Disponible sur le CV',
                'email' => $email ?? 'Disponible sur le CV',
                'location' => 'Afrique de l\'Ouest',
            ],
            'parsed_content' => [
                'profil' => 'Profil dynamique prêt à s\'investir dans de nouveaux challenges professionnels.',
                'experiences' => $hasExp ? ['Expériences professionnelles répertoriées dans le document'] : ['Débutant / En formation'],
                'formation' => $hasForm ? ['Formations et diplômes mentionnés'] : ['Cursus en cours'],
                'certifications' => $hasCert ? ['Certifications techniques identifiées'] : [],
                'competences' => $hasComp ? ['Compétences techniques et relationnelles'] : ['Organisation', 'Communication'],
                'langues' => ['Français (Courant)', 'Anglais (Professionnel)'],
            ],
            'global_score' => $score,
            'criteria_scores' => [
                'structure' => min(95, $score + 4),
                'clarite' => min(95, $score + 2),
                'experiences' => $hasExp ? min(95, $score + 5) : 50,
                'competences' => $hasComp ? min(95, $score + 3) : 52,
                'impact' => max(45, $score - 4),
            ],
            'strengths' => [
                'Présence des rubriques fondamentales pour un premier contact recruteur',
                'Coordonnées de contact facilement identifiables',
                'Volonté claire d\'évolution professionnelle',
            ],
            'improvements' => [
                'Chiffrer les accomplissements passés avec des résultats mesurables',
                'Valoriser un titre professionnel clair aligné avec le poste visé',
                'Détailler le niveau de maîtrise pour chaque compétence indiquée',
            ],
            'recommendations' => [
                'Structurez chaque expérience selon la méthode STAR (Situation, Tâche, Action, Résultat)',
                'Ajoutez des certifications ou projets personnels pour booster votre attractivité',
                'Créez votre compte Brillio pour accéder à nos modèles optimisés et au réseau de mentors',
            ],
            'summary' => 'Votre CV présente une base solide et un potentiel prometteur. En précisant vos réalisations avec des données quantifiées, votre score pourra atteindre le niveau supérieur.',
        ];
    }

    /**
     * Fallback minimal si fichier sans texte
     */
    private function generateFallbackAnalysis(string $filename): array
    {
        return [
            'candidate_name' => 'Candidat',
            'candidate_title' => 'Postulant',
            'candidate_contact' => [
                'phone' => 'Non spécifié',
                'email' => 'Non spécifié',
                'location' => 'Non spécifié',
            ],
            'parsed_content' => [
                'profil' => 'Document importé : '.$filename,
                'experiences' => ['Document analysé'],
                'formation' => ['Formation indiquée dans le document'],
                'competences' => ['Compétences professionnelles'],
                'langues' => ['Français'],
            ],
            'global_score' => 60,
            'criteria_scores' => [
                'structure' => 60,
                'clarite' => 62,
                'experiences' => 58,
                'competences' => 65,
                'impact' => 55,
            ],
            'strengths' => [
                'Fichier transmis et pris en compte avec succès',
                'Format de document standard',
            ],
            'improvements' => [
                'Assurez-vous que le document contient du texte sélectionnable pour une meilleure lisibilité ATS',
                'Complétez votre profil avec vos dernières expériences',
            ],
            'recommendations' => [
                'Exportez de préférence votre CV au format PDF texte natif depuis Word ou Canva',
                'Connectez-vous pour échanger avec un mentor et parfaire votre présentation',
            ],
            'summary' => 'Document reçu. Pour une analyse encore plus fine, assurez-vous que votre PDF comporte du texte directement sélectionnable.',
        ];
    }

    /**
     * Rapproche une analyse de CV invité à un utilisateur fraîchement connecté ou inscrit.
     */
    public function claimGuestCv(string $token, User $user): ?CvAnalysis
    {
        $cvAnalysis = CvAnalysis::where('guest_token', $token)
            ->whereNull('user_id')
            ->first();

        if ($cvAnalysis) {
            $cvAnalysis->update([
                'user_id' => $user->id,
                'is_claimed' => true,
            ]);

            // Si le jeune n'a pas encore de CV enregistré dans son profil, lui affecter
            if ($user->jeuneProfile && empty($user->jeuneProfile->cv_path)) {
                $user->jeuneProfile->update([
                    'cv_path' => $cvAnalysis->file_path,
                ]);
            }

            return $cvAnalysis;
        }

        return null;
    }
}
