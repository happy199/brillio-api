<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

class LinkedInPdfParserService
{
    private BrillioIAService $brillioIAService;

    public function __construct(BrillioIAService $brillioIAService)
    {
        $this->brillioIAService = $brillioIAService;
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
     * Patterns identifiés sur un vrai échantillon de CV LinkedIn exporté en PDF.
     */
    private function normalizeText(string $text): string
    {
        // Étape 0: Supprimer les artefacts de pagination PDF (ex: "Page 1 of 6")
        $text = preg_replace('/Page\s+\d+\s+(of|sur)\s+\d+/i', '', $text) ?? $text;

        // Étape 1: Décoder les entités HTML numériques (&#x1F4BC; → 💼, &amp; → &, etc.)
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Note : On ne supprime PAS les marques combinantes (U+0301) car les PDFs encodent
        // souvent les 'é', 'à', etc. en utilisant une lettre de base + un accent combinant (NFD).
        // Supprimer U+0301 détruit les lettres accentuées.

        // Étape 3: Remplacer les marqueurs PDF courants par leurs équivalents propres
        $fontSubstitutions = [
            // Bullet points et marqueurs de liste
            '/[●▪▸‣⁃◆◦]/u' => '•',
            '/◈\s*/u' => '• ',   // Diamond bullet LinkedIn (correct, normaliser en bullet)

            // Marqueurs KPI / impact en début de ligne (ex: "# -70%" ou "## +30%")
            // Ces # et ## sont des emojis de médaille/checkmark encodés en substitution
            '/^##\s+/mu' => '✅ ',
            '/^#\s+/mu' => '🔹 ',

            // Supprimer un ? isolé entre espaces (placeholder d'emoji inconnu)
            '/\s\?\s/' => ' ',

            // Tirets spéciaux mal encodés
            '/\x96/' => '–',
            '/\x97/' => '—',

            // Espaces insécables (U+00A0) → espace normal
            '/\xC2\xA0+/' => ' ',

            // Glyphes remplacés (U+FFFD, carrés pleins/vides)
            '/[\x{FFFD}\x{25A0}\x{25A1}]/u' => '',

            // Ligatures PDF (fi, fl encodées comme glyphes séparés)
            '/\x{FB01}/u' => 'fi',
            '/\x{FB02}/u' => 'fl',
        ];

        foreach ($fontSubstitutions as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text) ?? $text;
        }

        // Étape 4: Supprimer les séquences parasites résiduelles typiques LinkedIn
        // Ex: séquences de ́ , | restées après nettoyage des emojis de section
        $text = preg_replace('/(\s*[|,]\s*){2,}/', ' ', $text) ?? $text;
        // Supprimer des tirets isolés en début de chaîne ou entre symboles
        $text = preg_replace('/^–\s+/mu', '', $text) ?? $text;

        // Étape 5: Normaliser les espaces multiples et sauts de ligne
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = preg_replace('/\n{3,}/', "\n\n", $text) ?? $text;

        // Étape 6: Forcer la validité UTF-8
        // Utiliser mb_scrub() qui est natif PHP 8+ et remplace proprement
        // les séquences d'octets invalides sans détruire le reste du texte (contrairement à iconv).
        $text = mb_scrub($text, 'UTF-8');

        return trim($text);
    }

    /**
     * Sanitise récursivement toutes les chaînes d'un tableau pour s'assurer
     * qu'elles sont en UTF-8 valide avant d'être encodées en JSON.
     */
    public function sanitizeUtf8(mixed $data): mixed
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeUtf8'], $data);
        }

        if (is_string($data)) {
            // mb_scrub() la méthode la plus sûre et native en PHP 8 pour nettoyer l'UTF-8 invalide
            return mb_scrub($data, 'UTF-8');
        }

        return $data;
    }

    /**
     * Parsing via IA (DeepSeek/Gemini)
     */
    private function parseWithAI($text)
    {
        $systemPrompt = "Tu es un expert en extraction de données de CV (Resume Parser).\n".
            "Ta mission est d'analyser le texte brut d'un profil LinkedIn PDF et d'en extraire les informations structurées au format JSON STRICT.\n\n".
            "RÈGLES D'EXTRACTION CRITIQUES :\n".
            "1. STRUCTURE DES SECTIONS : Un profil LinkedIn PDF est organisé ainsi :\n".
            "   - En-tête : Nom, Titre (Headline), Localisation, Contact.\n".
            "   - Résumé (Summary) : Un paragraphe de présentation (parfois absent).\n".
            "   - Expérience : Liste de postes. Chaque poste commence par l'intitulé, puis l'entreprise, les dates et la description.\n".
            "   - Formation (Education) : Liste de diplômes et écoles.\n".
            "   - Compétences (Skills) : Liste de mots-clés.\n\n".
            "2. REGROUPEMENT DES MISSIONS : Les lignes commençant par des tirets (-), des puces (•) ou des points sont des détails de l'expérience ou de la formation qui précède. \n".
            "   → Tu DOIS impérativement regrouper toutes ces lignes dans le champ 'description' de l'objet parent. \n".
            "   → NE JAMAIS créer une nouvelle entrée d'expérience ou de formation pour un bullet point.\n".
            "   → NE JAMAIS inventer d'étapes si le texte est répétitif ou mal formé.\n\n".
            "3. DISTINCTION EXPÉRIENCE/FORMATION : \n".
            "   → La catégorie 'education' est EXCLUSIVEMENT réservée aux diplômes académiques (Master, Licence, Ecole, etc.).\n".
            "   → NE JAMAIS mettre des missions, tâches ou projets professionnels (ex: 'Conception de...', 'Suivi de...') dans 'education'. Ils vont TOUJOURS dans 'experience'.\n\n".
            "4. NETTOYAGE DES MOTS COLLÉS (STICKY WORDS) : Le parsing PDF colle souvent des mots (ex: 'Engineermars' au lieu de 'Engineer mars', 'RennesFrance' au lieu de 'Rennes, France').\n".
            "   → Tu DOIS détecter et séparer ces mots pour un rendu professionnel.\n\n".
            "5. COMPÉTENCES (SKILLS) : Ne conserve que des mots-clés courts (ex: 'Python', 'Gestion de projet', 'React').\n".
            "   → Si tu trouves une phrase entière dans les compétences, reformule-la en mots-clés ou ignore-la si elle appartient à une description de poste.\n\n".
            "6. DATES : LinkedIn utilise souvent le format 'mois année - mois année' ou 'mois année - Aujourd’hui'. Convertis-les au format YYYY-MM-DD (ex: 'janvier 2024' -> '2024-01-01').\n\n".
            "RÈGLES IMPORTANTES :\n".
            "- Ne jamais inventer d'information. Si une info est manquante, mets null ou une chaine vide.\n".
            "- Réponds UNIQUEMENT avec le bloc JSON, sans texte avant ou après, sans balises markdown (```json), sans commentaires et sans virgules traînantes.\n".
            "- Le format de sortie doit respecter exactement la structure demandée.\n\n".
            "RÈGLE EMOJIS & CARACTÈRES SPÉCIAUX (PDF LinkedIn) :\n".
            "Les PDFs LinkedIn exportés encodent mal les emojis en raison des limitations des polices PDF.\n".
            "Voici les patterns les plus courants que tu devras interpréter :\n".
            "- '| ́ &amp; | ́ -' → chaîne d'icônes emojis (ex : 🌐 & 🎯 - ou similaire). Substitue par des emojis contextuellement cohérents ou supprime si le sens est incertain.\n".
            "- '́' seul (accent aigu isolé) → résidu fantôme d'un emoji supprimé. IGNORER/SUPPRIMER.\n".
            "- '◈' en début de ligne → bullet point LinkedIn. Restaurer en '•'.\n".
            "- '✅ ' ou '🔹 ' en début de ligne → marqueur de résultat/KPI. CONSERVER.\n".
            "- '® 6, , , ®,' → emojis indicateurs (ex: 🏆, 🎯). Interpréter ou supprimer le bruit.\n".
            "- Un '?' isolé entre deux espaces → placeholder d'emoji inconnu. Supprimer.\n".
            "- '&amp;' → '&' (entité HTML). Toujours décoder.\n".
            "- '#' ou '##' en début de KPI (ex: '# -70% du temps') → c'était un emoji (✅, 📊). Les métriques elles-mêmes sont correctes, conserver les chiffres.\n".
            "- Préserve à 100% les vrais emojis Unicode déjà présents (🚀, 💼, ✅, 📈, etc.).\n".
            "- L'objectif est que le texte final soit identique à ce qui apparaît sur le profil LinkedIn web réel.\n\n".
            "RÈGLE DE RECONSTITUTION DU TEXTE (ORTHOGRAPHE & GRAMMAIRE) :\n".
            "Les PDFs LinkedIn génèrent parfois des erreurs d'extraction qui font disparaître des lettres accentuées ou des apostrophes (ex: 'systmes' au lieu de 'systèmes', 'dassurance' au lieu de 'd\\'assurance', 'quipes' au lieu d\\'équipes').\n".
            "→ Tu DOIS OBLIGATOIREMENT corriger ces mots. Le texte final dans le JSON doit être dans un français parfait, fluide, cohérent, sans fautes d'orthographe ou de grammaire. Si une lettre manque à cause du parsing PDF, déduis le mot correct et répare-le.\n\n".
            "RÈGLES DE MAPPING DES CHAMPS :\n".
            "- 'headline' → Titre/accroche sous le nom (ex: 'Data Product Manager | J'aligne vision produit...'). Ne JAMAIS mettre ici le contenu de la section Résumé.\n".
            "- 'summary' → Contenu COMPLET de la section 'Résumé' du profil LinkedIn PDF. Si cette section est absente ou vide, mettre une chaîne vide ''. NE PAS substituer par le headline.\n".
            "- 'location' → Ville et pays du mentor (ex: 'Lille, France' ou 'Courbevoie, Île-de-France, France'). Extraire depuis l'en-tête du PDF.\n\n".
            "STRUCTURE JSON ATTENDUE :\n".
            '{"name": "Nom complet", "headline": "Titre/accroche du profil", "location": "Ville, Pays", "contact": {"email": "email found or empty", "phone": "phone found or empty", "linkedin": "linkedin url or empty", "website": "website url or empty"}, "summary": "Contenu exact de la section Résumé du PDF ou chaine vide si absente", "skills": ["Compétence 1"], "experience": [{"title": "Poste", "company": "Entreprise", "description": "Tâches", "start_date": "YYYY-MM-DD", "end_date": "YYYY-MM-DD or null if currently in this role", "duration_years": 0, "duration_months": 0}], "education": [{"school": "Ecole", "degree": "Diplôme", "year_start": 0, "year_end": 0}]}';

        $prompt = "Voici le contenu brut du PDF LinkedIn. Extrais les données en JSON :\n\n".substr($text, 0, 60000);

        Log::info('🤖 Sending PDF text to AI...');
        $jsonResponse = $this->brillioIAService->analyzeText($prompt, $systemPrompt);

        $cleanJson = $this->brillioIAService->cleanJson($jsonResponse);
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
            $trimmedLine = trim($line);

            // Détection robuste de début de section (ligne presque exacte)
            if (preg_match('/^(Expérience|Experience)$/i', $trimmedLine)) {
                $inSection = true;

                continue;
            }

            // Détection robuste de fin de section (ligne presque exacte pour Formation/Education)
            if ($inSection && preg_match('/^(Formation|Education)$/i', $trimmedLine)) {
                break;
            }

            if ($inSection) {
                // Si la ligne commence par une puce, on l'ajoute à la description du dernier bloc au lieu de créer un nouveau bloc
                if (preg_match('/^[•\-\*]/u', $trimmedLine) && ! empty($experiences)) {
                    $lastIdx = count($experiences) - 1;
                    $experiences[$lastIdx]['description'] .= "\n".$trimmedLine;

                    continue;
                }

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
            $trimmedLine = trim($line);

            if (preg_match('/^(Formation|Education)$/i', $trimmedLine)) {
                $inSection = true;

                continue;
            }

            // Fin de section si on tombe sur Compétences ou autre section majeure
            if ($inSection && preg_match('/^(Compétences|Skills|Honneurs|Langues|Languages)$/i', $trimmedLine)) {
                break;
            }

            if ($inSection) {
                if (preg_match('/Page \d+ (of|sur) \d+/', $line)) {
                    continue;
                }

                // Ignorer les puces en éducation pour le parseur legacy (souvent du bruit)
                if (preg_match('/^[•\-\*]/u', $trimmedLine)) {
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
