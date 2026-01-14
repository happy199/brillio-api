<?php

namespace App\Services;

use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Log;

class LinkedInPdfParserService
{
    /**
     * Parse un PDF LinkedIn et extrait les informations
     */
    public function parsePdf($pdfPath)
    {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($pdfPath);

            // Extraire le texte brut
            $text = $pdf->getText();

            Log::info('📄 PDF Text extracted', [
                'length' => strlen($text),
                'first_1000_chars' => substr($text, 0, 1000)
            ]);

            // Nettoyer et diviser en lignes
            $lines = $this->cleanAndSplitText($text);

            // Parser les différentes sections
            $data = [
                'name' => $this->extractName($lines),
                'headline' => $this->extractHeadline($lines),
                'contact' => $this->extractContact($lines),
                'summary' => '', // Rarement présent dans les PDFs LinkedIn
                'experience' => $this->extractExperience($lines),
                'education' => $this->extractEducation($lines),
                'skills' => $this->extractSkills($lines),
            ];

            // Logger chaque champ extrait
            Log::info('✅ LinkedIn PDF Parsing Results', [
                'name' => $data['name'],
                'headline' => $data['headline'],
                'contact' => $data['contact'],
                'experience_count' => count($data['experience']),
                'education_count' => count($data['education']),
                'skills_count' => count($data['skills']),
                'skills' => $data['skills']
            ]);

            return $data;

        } catch (\Exception $e) {
            Log::error('❌ PDF Parsing Error', ['error' => $e->getMessage()]);
            throw new \Exception('Impossible de lire le PDF : ' . $e->getMessage());
        }
    }

    /**
     * Nettoyer le texte et le diviser en lignes
     */
    private function cleanAndSplitText($text)
    {
        // Supprimer les numéros de page
        $text = preg_replace('/Page \d+ of \d+/', '', $text);

        // Diviser en lignes et nettoyer
        $lines = explode("\n", $text);
        $cleanLines = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $cleanLines[] = $line;
            }
        }

        return $cleanLines;
    }

    /**
     * Extraire le nom (3 lignes avant "Expérience")
     */
    private function extractName($lines)
    {
        // Trouver l'index de "Expérience"
        foreach ($lines as $i => $line) {
            $lineLower = strtolower(trim($line));

            if (str_contains($lineLower, 'expérience') || $lineLower === 'experience') {
                // Remonter de 3 lignes pour trouver le nom
                if ($i >= 3) {
                    return trim($lines[$i - 3]);
                }
                break;
            }
        }

        return '';
    }

    /**
     * Extraire le headline (2 lignes avant "Expérience")
     */
    private function extractHeadline($lines)
    {
        // Trouver l'index de "Expérience"
        foreach ($lines as $i => $line) {
            $lineLower = strtolower(trim($line));

            if (str_contains($lineLower, 'expérience') || $lineLower === 'experience') {
                // Remonter de 2 lignes pour trouver le headline
                if ($i >= 2) {
                    return trim($lines[$i - 2]);
                }
                break;
            }
        }

        return '';
    }

    /**
     * Extraire les informations de contact
     */
    private function extractContact($lines)
    {
        $contact = [
            'email' => '',
            'phone' => '',
            'linkedin' => '',
            'website' => ''
        ];

        $linkedinUrl = '';

        foreach ($lines as $i => $line) {
            // Email
            if (preg_match('/[\w\.-]+@[\w\.-]+\.\w+/', $line, $matches)) {
                $contact['email'] = $matches[0];
            }

            // LinkedIn URL (peut être sur 2 lignes)
            if (preg_match('/linkedin\.com\/in\/([\w-]+)/', $line, $matches)) {
                $linkedinUrl = 'linkedin.com/in/' . $matches[1];

                // Vérifier la ligne suivante pour la suite de l'URL
                if (isset($lines[$i + 1])) {
                    $nextLine = $lines[$i + 1];
                    // Si la ligne suivante contient (LinkedIn), extraire la partie avant
                    if (str_contains($nextLine, '(LinkedIn)')) {
                        $urlPart = trim(str_replace('(LinkedIn)', '', $nextLine));
                        $linkedinUrl .= $urlPart;
                    }
                }

                $contact['linkedin'] = 'https://' . $linkedinUrl;
            }

            // Site web (format: domain.ext (Company))
            if (preg_match('/([a-z0-9-]+\.[a-z]{2,}(?:\.[a-z]{2,})?)\s*\(Company\)/i', $line, $matches)) {
                $contact['website'] = 'https://' . $matches[1];
            }

            // Téléphone
            if (preg_match('/\+?\d{10,}/', $line, $matches)) {
                $contact['phone'] = $matches[0];
            }
        }

        return $contact;
    }

    /**
     * Extraire les compétences (après "Principales compétences", avant nom/headline/location)
     */
    private function extractSkills($lines)
    {
        $skills = [];
        $inSkillsSection = false;
        $experienceIndex = null;

        // Trouver l'index de "Expérience" d'abord
        foreach ($lines as $i => $line) {
            if (stripos($line, 'Expérience') !== false || stripos($line, 'Experience') !== false) {
                $experienceIndex = $i;
                break;
            }
        }

        foreach ($lines as $i => $line) {
            // Détecter "Principales compétences"
            if (stripos($line, 'Principales compétences') !== false || stripos($line, 'Skills') !== false) {
                $inSkillsSection = true;
                continue;
            }

            if ($inSkillsSection) {
                // Arrêter 3 lignes avant "Expérience" (pour exclure nom, headline, localisation)
                if ($experienceIndex !== null && $i >= ($experienceIndex - 3)) {
                    break;
                }

                // Ajouter la ligne comme compétence
                if (strlen($line) > 2 && strlen($line) < 100) {
                    $skills[] = $line;
                }
            }
        }

        return array_unique($skills);
    }

    /**
     * Extraire les expériences (blocs de 4 lignes après "Expérience")
     */
    private function extractExperience($lines)
    {
        $experiences = [];
        $inExperienceSection = false;
        $currentBlock = [];

        foreach ($lines as $i => $line) {
            // Détecter la section "Expérience"
            if (stripos($line, 'Expérience') !== false || stripos($line, 'Experience') !== false) {
                $inExperienceSection = true;
                continue;
            }

            // Arrêter à "Formation"
            if (stripos($line, 'Formation') !== false || stripos($line, 'Education') !== false) {
                break;
            }

            if ($inExperienceSection) {
                $currentBlock[] = $line;

                // Tous les 4 lignes = une expérience
                if (count($currentBlock) === 4) {
                    $exp = $this->parseExperienceBlock($currentBlock);
                    if ($exp) {
                        $experiences[] = $exp;
                    }
                    $currentBlock = [];
                }
            }
        }

        return $experiences;
    }

    /**
     * Parser un bloc d'expérience (4 lignes)
     */
    private function parseExperienceBlock($block)
    {
        if (count($block) < 4) {
            return null;
        }

        $company = $block[0]; // Ligne 1: Entreprise
        $title = $block[1];   // Ligne 2: Poste
        $dates = $block[2];   // Ligne 3: Dates
        $location = $block[3]; // Ligne 4: Localisation

        // Extraire les dates
        $startDate = null;
        $endDate = null;
        $durationYears = 0;
        $durationMonths = 0;

        if (preg_match('/(janvier|février|mars|avril|mai|juin|juillet|août|septembre|octobre|novembre|décembre)\s+(\d{4})\s*-\s*(?:(janvier|février|mars|avril|mai|juin|juillet|août|septembre|octobre|novembre|décembre)\s+(\d{4})|Present)/i', $dates, $matches)) {
            $startDate = $matches[2];
            $endDate = isset($matches[4]) ? $matches[4] : null;
        }

        // Extraire la durée des parenthèses : (7 ans 1 mois)
        if (preg_match('/\((\d+)\s+ans?\s*(?:(\d+)\s+mois)?\)/i', $dates, $durationMatches)) {
            $durationYears = (int) $durationMatches[1];
            $durationMonths = isset($durationMatches[2]) ? (int) $durationMatches[2] : 0;
        }
        // Ou juste des mois : (5 mois)
        elseif (preg_match('/\((\d+)\s+mois\)/i', $dates, $durationMatches)) {
            $durationMonths = (int) $durationMatches[1];
        }

        return [
            'title' => $title,
            'company' => $company,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'description' => $location,
            'duration_years' => $durationYears,
            'duration_months' => $durationMonths,
        ];
    }

    /**
     * Extraire l'éducation (blocs de 2 lignes après "Formation")
     */
    private function extractEducation($lines)
    {
        $education = [];
        $inEducationSection = false;
        $currentBlock = [];

        foreach ($lines as $line) {
            // Détecter la section "Formation"
            if (stripos($line, 'Formation') !== false || stripos($line, 'Education') !== false) {
                $inEducationSection = true;
                continue;
            }

            if ($inEducationSection) {
                // Ignorer "Page X of Y"
                if (preg_match('/Page \d+ of \d+/', $line)) {
                    continue;
                }

                $currentBlock[] = $line;

                // Tous les 2 lignes = une formation
                if (count($currentBlock) === 2) {
                    $school = $currentBlock[0];
                    $degree = $currentBlock[1];

                    // Extraire les années de la parenthèse : (octobre 2020 - juillet 2022)
                    $yearStart = null;
                    $yearEnd = null;

                    // Chercher le pattern avec 2 années séparées par un tiret
                    if (preg_match('/\(.*?(\d{4}).*?-.*?(\d{4})\)/', $degree, $matches)) {
                        $yearStart = (int) $matches[1];  // Première année = début
                        $yearEnd = (int) $matches[2];    // Deuxième année = fin
                        \Log::info('Education years extracted', [
                            'degree' => $degree,
                            'year_start' => $yearStart,
                            'year_end' => $yearEnd
                        ]);
                    }
                    // Ou juste une année de début
                    elseif (preg_match('/\(.*?(\d{4})/', $degree, $matches)) {
                        $yearStart = (int) $matches[1];
                        $yearEnd = date('Y'); // Année courante si pas de fin
                    }

                    $education[] = [
                        'school' => $school,
                        'degree' => $degree,
                        'year_start' => $yearStart,
                        'year_end' => $yearEnd,
                    ];

                    $currentBlock = [];
                }
            }
        }

        return $education;
    }
}
