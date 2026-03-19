<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

class LinkedInPdfParserService
{
    private DeepSeekService $deepSeekService;

    public function __construct(DeepSeekService $deepSeekService)
    {
        $this->deepSeekService = $deepSeekService;
    }

    /**
     * Parse un PDF LinkedIn et extrait les informations (Hybride: IA avec Fallback Regex)
     */
    public function parsePdf($pdfPath)
    {
        $text = '';
        try {
            $parser = new Parser;
            $pdf = $parser->parseFile($pdfPath);

            // Extraire le texte brut
            $rawText = $pdf->getText();

            // Normaliser le texte : réparer les emojis cassés et les caractères spéciaux
            $text = $this->normalizeText($rawText);

            Log::info('📄 PDF Text extracted & normalized', [
                'length' => strlen($text),
                'first_1000_chars' => substr($text, 0, 1000),
            ]);

            // Tentative de parsing via IA
            return $this->parseWithAI($text);

        } catch (\Exception $e) {
            Log::warning('⚠️ AI Parsing failed, falling back to legacy regex parser', [
                'error' => $e->getMessage(),
            ]);

            // Fallback sur l'ancien système si l'IA échoue
            return $this->parseLegacy($text);
        }
    }

    /**
     * Normalise le texte brut extrait du PDF LinkedIn.
     * Corrige les problèmes courants d'encodage des emojis et caractères spéciaux.
     */
    private function normalizeText(string $text): string
    {
        // Étape 1: Décoder les entités HTML numériques (&#x1F4BC; → 💼, &#128188; → 💼, &amp; → &, etc.)
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Étape 2: Corriger les séquences de substitution de polices PDF connues
        // Le PDF LinkedIn utilise une police symbolique qui mappe certains glyphes
        // Les patterns ci-dessous couvrent les remplacements les plus fréquents
        $fontSubstitutions = [
            // Marqueurs de liste / bullets
            '/[●•▪▸‣⁃◆◦]/' => '•',

            // Artifacts hexadécimaux PDF courants (caractères privés Unicode U+F0xx)
            '/[\xEF\x80\x80-\xEF\x83\xBF]/' => '',  // Zone d'usage privé (PUA) Latin
            '/[\xF0\x9F\x80\x80-\xF0\x9F\xBF\xBF]+/' => '', // Conservés tels quels (vraies emojis)

            // Caractères de contrôle / remplacement parasites
            '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F\x80-\x9F]/' => '',

            // Réparer les tirets em (–) qui deviennent souvent des artéfacts
            '/\x96/' => '–',
            '/\x97/' => '—',

            // Ligatures et caractères spéciaux typiques du PDF LinkedIn
            '/fi/' => 'fi',  // ligature fi
            '/fl/' => 'fl',  // ligature fl

            // Supprimer les espaces insécables parasites multiples
            '/\xC2\xA0+/' => ' ',

            // Supprimer des séquences parasites résiduelles (ex: glyphe manquant → carré)
            '/[\x{FFFD}\x{25A0}\x{25A1}]/u' => '',
        ];

        foreach ($fontSubstitutions as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text) ?? $text;
        }

        // Étape 3: Normaliser les espaces multiples et sauts de ligne
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }

    /**
     * Parsing via IA (DeepSeek/Gemini)
     */
    private function parseWithAI($text)
    {
        $systemPrompt = "Tu es un expert en extraction de données de CV (Resume Parser).\n".
            "Ta mission est d'analyser le texte brut d'un profil LinkedIn PDF et d'en extraire les informations structurées au format JSON STRICT.\n\n".
            "RÈGLES IMPORTANTES :\n".
            "- Ne jamais inventer d'information. Si une info est manquante, mets null ou une chaine vide.\n".
            "- Répont UNIQUEMENT avec le bloc JSON, sans texte avant ou après, sans balises markdown (```json), sans commentaires et sans virgules traînantes.\n".
            "- Le format de sortie doit respecter exactement la structure demandée.\n\n".
            "RÈGLE EMOJIS & CARACTÈRES SPÉCIAUX :\n".
            "- Les PDFs LinkedIn encodent les emojis en caractères de substitution (ex: '⁃', '✴', '&', glyphes illisibles).\n".
            "- Si tu rencontres des groupes de caractères étranges ou illisibles là où un emoji serait attendu (ex: début de bullet, dans un titre), remplace-les par l'emoji le plus vraisemblable dans ce contexte.\n".
            "- Exemples : '⁃' ou '✦' en début de ligne → '•', '&' seul entre 2 espaces → '&', '™' mal encodé → '™', '®' mal encodé → '®'.\n".
            "- L'objectif est que le texte extrait soit identique à ce qui apparaît sur le profil LinkedIn web réel du mentor.\n".
            "- Préserve les vrais emojis Unicode déjà présents dans le texte (🚀, 💼, ✅, etc.).\n\n".
            "STRUCTURE JSON ATTENDUE :\n".
            '{"name": "Nom complet", "headline": "Titre du profil ou poste actuel", "contact": {"email": "email found or empty", "phone": "phone found or empty", "linkedin": "linkedin url or empty", "website": "website url or empty"}, "summary": "Bio", "skills": ["Compétence 1"], "experience": [{"title": "Poste", "company": "Entreprise", "description": "Tâches", "start_date": "YYYY-MM-DD", "end_date": "YYYY-MM-DD or null if currently in this role", "duration_years": 0, "duration_months": 0}], "education": [{"school": "Ecole", "degree": "Diplôme", "year_start": 0, "year_end": 0}]}';

        $prompt = "Voici le contenu brut du PDF LinkedIn. Extrais les données en JSON :\n\n".substr($text, 0, 60000);

        Log::info('🤖 Sending PDF text to AI...');
        $jsonResponse = $this->deepSeekService->analyzeText($prompt, $systemPrompt);

        $cleanJson = $this->deepSeekService->cleanJson($jsonResponse);
        $data = json_decode($cleanJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Erreur lors du décodage de la réponse IA: '.json_last_error_msg());
        }

        // Stats
        Log::info('✅ AI Parsing Successful', [
            'name' => $data['name'] ?? 'Unknown',
        ]);

        return $data;
    }

    /**
     * Ancien système de parsing basé sur les Regex (Fallback)
     */
    private function parseLegacy($text)
    {
        if (empty($text)) {
            throw new \Exception('Texte extrait vide, impossible d\'utiliser le fallback.');
        }

        Log::info('🕵️ Using Legacy Regex Parser Fallback');

        // Nettoyer et diviser en lignes
        $lines = $this->cleanAndSplitText($text);

        // Parser les différentes sections
        $data = [
            'name' => $this->extractName($lines),
            'headline' => $this->extractHeadline($lines),
            'contact' => $this->extractContact($lines),
            'summary' => '',
            'experience' => $this->extractExperience($lines),
            'education' => $this->extractEducation($lines),
            'skills' => $this->extractSkills($lines),
            'is_fallback' => true, // Flag pour info
        ];

        return $data;
    }

    /**
     * Nettoyer le texte et le diviser en lignes
     */
    private function cleanAndSplitText($text)
    {
        $text = preg_replace('/Page \d+ of \d+/', '', $text);
        $lines = explode("\n", $text);
        $cleanLines = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (! empty($line)) {
                $cleanLines[] = $line;
            }
        }

        return $cleanLines;
    }

    private function extractName($lines)
    {
        foreach ($lines as $i => $line) {
            $lineLower = strtolower(trim($line));
            if (str_contains($lineLower, 'expérience') || $lineLower === 'experience') {
                if ($i >= 3) {
                    return trim($lines[$i - 3]);
                }
                break;
            }
        }

        return '';
    }

    private function extractHeadline($lines)
    {
        foreach ($lines as $i => $line) {
            $lineLower = strtolower(trim($line));
            if (str_contains($lineLower, 'expérience') || $lineLower === 'experience') {
                if ($i >= 2) {
                    return trim($lines[$i - 2]);
                }
                break;
            }
        }

        return '';
    }

    private function extractContact($lines)
    {
        $contact = ['email' => '', 'phone' => '', 'linkedin' => '', 'website' => ''];
        foreach ($lines as $i => $line) {
            if (preg_match('/[\w\.-]+@[\w\.-]+\.\w+/', $line, $matches)) {
                $contact['email'] = $matches[0];
            }
            if (preg_match('/linkedin\.com\/in\/([\w-]+)/', $line, $matches)) {
                $url = 'https://linkedin.com/in/'.$matches[1];
                if (isset($lines[$i + 1]) && str_contains($lines[$i + 1], '(LinkedIn)')) {
                    $url .= trim(str_replace('(LinkedIn)', '', $lines[$i + 1]));
                }
                $contact['linkedin'] = $url;
            }
            if (preg_match('/([a-z0-9-]+\.[a-z]{2,})\s*\(Company\)/i', $line, $matches)) {
                $contact['website'] = 'https://'.$matches[1];
            }
            if (preg_match('/\+?\d{10,}/', $line, $matches)) {
                $contact['phone'] = $matches[0];
            }
        }

        return $contact;
    }

    private function extractSkills($lines)
    {
        $skills = [];
        $inSkillsSection = false;
        $experienceIndex = null;

        foreach ($lines as $i => $line) {
            if (stripos($line, 'Expérience') !== false || stripos($line, 'Experience') !== false) {
                $experienceIndex = $i;
                break;
            }
        }

        foreach ($lines as $i => $line) {
            if (stripos($line, 'Principales compétences') !== false || stripos($line, 'Skills') !== false) {
                $inSkillsSection = true;

                continue;
            }
            if ($inSkillsSection) {
                if ($experienceIndex !== null && $i >= ($experienceIndex - 3)) {
                    break;
                }
                if (strlen($line) > 2 && strlen($line) < 100) {
                    $skills[] = $line;
                }
            }
        }

        return array_unique($skills);
    }

    private function extractExperience($lines)
    {
        $experiences = [];
        $inSection = false;
        $block = [];

        foreach ($lines as $line) {
            if (stripos($line, 'Expérience') !== false || stripos($line, 'Experience') !== false) {
                $inSection = true;

                continue;
            }
            if (stripos($line, 'Formation') !== false || stripos($line, 'Education') !== false) {
                break;
            }

            if ($inSection) {
                $block[] = $line;
                if (count($block) === 4) {
                    $exp = $this->parseExperienceBlock($block);
                    if ($exp) {
                        $experiences[] = $exp;
                    }
                    $block = [];
                }
            }
        }

        return $experiences;
    }

    private function parseExperienceBlock($block)
    {
        $company = $block[0];
        $title = $block[1];
        $dates = $block[2];
        $location = $block[3];

        $startDate = $endDate = null;
        $years = $months = 0;

        if (preg_match('/(janvier|février|mars|avril|mai|juin|juillet|août|septembre|octobre|novembre|décembre)\s+(\d{4})\s*-\s*(?:.*?(\d{4})|Present|Aujourd’hui|Présent)/i', $dates, $m)) {
            $startDate = $m[2];
            $endDate = (! empty($m[3])) ? $m[3] : null;
        }

        if (preg_match('/\((\d+)\s+ans?\s*(?:(\d+)\s+mois)?\)/i', $dates, $m)) {
            $years = (int) $m[1];
            $months = (int) ($m[2] ?? 0);
        } elseif (preg_match('/\((\d+)\s+mois\)/i', $dates, $m)) {
            $months = (int) $m[1];
        }

        return [
            'title' => $title, 'company' => $company, 'start_date' => $startDate, 'end_date' => $endDate,
            'description' => $location, 'duration_years' => $years, 'duration_months' => $months,
        ];
    }

    private function extractEducation($lines)
    {
        $education = [];
        $inSection = false;
        $block = [];

        foreach ($lines as $line) {
            if (stripos($line, 'Formation') !== false || stripos($line, 'Education') !== false) {
                $inSection = true;

                continue;
            }
            if ($inSection) {
                if (preg_match('/Page \d+ of \d+/', $line)) {
                    continue;
                }
                $block[] = $line;
                if (count($block) === 2) {
                    $school = $block[0];
                    $degree = $block[1];
                    $start = $end = null;
                    if (preg_match('/\(.*?(\d{4}).*?-.*?(\d{4})\)/', $degree, $m)) {
                        $start = (int) $m[1];
                        $end = (int) $m[2];
                    } elseif (preg_match('/\(.*?(\d{4})/', $degree, $m)) {
                        $start = (int) $m[1];
                        $end = (int) date('Y');
                    }
                    $education[] = ['school' => $school, 'degree' => $degree, 'year_start' => $start, 'year_end' => $end];
                    $block = [];
                }
            }
        }

        return $education;
    }
}
