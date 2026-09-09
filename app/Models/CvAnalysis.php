<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvAnalysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guest_token',
        'original_filename',
        'file_path',
        'file_size',
        'mime_type',
        'candidate_name',
        'candidate_title',
        'candidate_contact',
        'parsed_content',
        'global_score',
        'status_label',
        'criteria_scores',
        'strengths',
        'improvements',
        'recommendations',
        'summary',
        'is_claimed',
    ];

    protected $casts = [
        'candidate_contact' => 'array',
        'parsed_content' => 'array',
        'criteria_scores' => 'array',
        'strengths' => 'array',
        'improvements' => 'array',
        'recommendations' => 'array',
        'global_score' => 'integer',
        'file_size' => 'integer',
        'is_claimed' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calcule le statut textuel en fonction du score
     */
    public static function determineStatusLabel(int $score): string
    {
        return match (true) {
            $score >= 85 => 'Excellent',
            $score >= 70 => 'Très bien',
            $score >= 50 => 'Bon potentiel',
            default => 'À perfectionner',
        };
    }

    /**
     * Initiales du candidat pour l'avatar ATS
     */
    public function getInitialsAttribute(): string
    {
        $words = preg_split('/\s+/', trim($this->candidate_name ?? 'Candidat'));
        $initials = '';
        foreach (array_slice($words, 0, 2) as $w) {
            $initials .= mb_substr($w, 0, 1);
        }

        return mb_strtoupper($initials ?: 'CV');
    }

    /**
     * Libellé lisible et convivial du format de fichier pour le jeune (ex: Word, PDF, Image)
     */
    public function getFileFormatLabelAttribute(): string
    {
        $mime = strtolower((string) ($this->mime_type ?? ''));
        $ext = strtolower(pathinfo((string) ($this->original_filename ?? ''), PATHINFO_EXTENSION));

        if ($ext === 'docx' || str_contains($mime, 'wordprocessingml') || str_contains($mime, 'docx')) {
            return 'Document Word (.docx)';
        }

        if ($ext === 'doc' || str_contains($mime, 'msword')) {
            return 'Document Word (.doc)';
        }

        if ($ext === 'pdf' || str_contains($mime, 'pdf')) {
            return 'Document PDF (.pdf)';
        }

        if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp']) || str_contains($mime, 'image')) {
            return 'Image ('.strtoupper($ext ?: 'JPG').')';
        }

        if ($ext === 'txt' || str_contains($mime, 'text/plain')) {
            return 'Fichier Texte (.txt)';
        }

        if (! empty($ext)) {
            return 'Fichier .'.strtoupper($ext);
        }

        return 'Document CV';
    }

    /**
     * Classes Tailwind pour le badge de format de fichier
     */
    public function getFileFormatBadgeColorAttribute(): string
    {
        $mime = strtolower((string) ($this->mime_type ?? ''));
        $ext = strtolower(pathinfo((string) ($this->original_filename ?? ''), PATHINFO_EXTENSION));

        if ($ext === 'docx' || $ext === 'doc' || str_contains($mime, 'wordprocessingml') || str_contains($mime, 'msword') || str_contains($mime, 'docx')) {
            return 'bg-blue-50 text-blue-700 border-blue-200';
        }

        if ($ext === 'pdf' || str_contains($mime, 'pdf')) {
            return 'bg-red-50 text-red-700 border-red-200';
        }

        if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp']) || str_contains($mime, 'image')) {
            return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        }

        return 'bg-gray-100 text-gray-700 border-gray-200';
    }

    /**
     * Données normalisées prêtes pour les templates ATS (résolution des chaînes et clés bilingues)
     */
    public function getNormalizedCvDataAttribute(): array
    {
        $parsed = $this->parsed_content ?? [];
        $rawText = $parsed['raw_text'] ?? '';

        // 0. Détection de la langue du CV (Anglais vs Français)
        $searchCorpus = mb_strtolower($rawText.' '.json_encode($parsed));
        $englishScore = 0;
        $frenchScore = 0;

        $englishKeywords = [
            'professional summary', 'core skills', 'professional experience', 'education',
            'certifications', 'languages', 'years', 'current role', 'experience', 'skills',
            'degree', 'engineer', 'native', 'fluent', 'university', 'responsibilities',
        ];
        $frenchKeywords = [
            'profil professionnel', 'résumé professionnel', 'expériences professionnelles',
            'formations', 'diplômes', 'compétences', 'langues', 'années', 'poste actuel',
            'expérience', 'formation', 'ingénieur', 'courant', 'maternelle', 'université',
        ];

        foreach ($englishKeywords as $kw) {
            if (str_contains($searchCorpus, $kw)) {
                $englishScore += 2;
            }
        }
        foreach ($frenchKeywords as $kw) {
            if (str_contains($searchCorpus, $kw)) {
                $frenchScore += 2;
            }
        }

        if (preg_match('/\b(with|and|from|for|team|developer|lead|tools)\b/i', $rawText)) {
            $englishScore += 2;
        }
        if (preg_match('/\b(avec|dans|pour|équipe|développeur|gestion|outils)\b/i', $rawText)) {
            $frenchScore += 2;
        }

        $isEnglish = $englishScore > $frenchScore;

        $labels = $isEnglish ? [
            'profile' => 'PROFESSIONAL SUMMARY',
            'experience' => 'PROFESSIONAL EXPERIENCE',
            'education' => 'EDUCATION',
            'skills' => 'CORE SKILLS & TOOLS',
            'skills_simple' => 'CORE SKILLS',
            'certifications' => 'CERTIFICATIONS',
            'languages' => 'LANGUAGES',
            'recently' => 'Current role',
            'executive_summary' => 'EXECUTIVE SUMMARY',
            'achievements' => 'KEY ACHIEVEMENTS & ROLES',
            'leadership' => 'LEADERSHIP PROFILE',
            'higher_education' => 'HIGHER EDUCATION & DEGREES',
        ] : [
            'profile' => 'PROFIL PROFESSIONNEL',
            'experience' => 'EXPÉRIENCES PROFESSIONNELLES',
            'education' => 'FORMATIONS & DIPLÔMES',
            'skills' => 'COMPÉTENCES CLÉS & OUTILS',
            'skills_simple' => 'COMPÉTENCES',
            'certifications' => 'CERTIFICATIONS',
            'languages' => 'LANGUES',
            'recently' => 'Récemment',
            'executive_summary' => 'SYNTHÈSE EXÉCUTIVE',
            'achievements' => 'RÉALISATIONS & POSTES OCCUPÉS',
            'leadership' => 'PROFIL DE LEADERSHIP',
            'higher_education' => 'FORMATION & DIPLÔMES SUPÉRIEURS',
        ];

        // 1. Résumé du profil professionnel du candidat (exclure l'évaluation IA "Ce CV est très bien structuré...")
        $profileSummary = '';
        if (! empty($rawText)) {
            if (preg_match('/(?:PROFESSIONAL\s+SUMMARY|RÉSUMÉ\s+PROFESSIONNEL|RESUME\s+PROFESSIONNEL|PROFILE|PROFIL(?:\s+PROFESSIONNEL)?|SUMMARY|RÉSUMÉ|ABOUT\s+ME)\s*[:\n\-]+\s*(.*?)(?=\n+\s*(?:CORE\s+SKILLS|SKILLS|COMPÉTENCES|COMPETENCES|PROFESSIONAL\s+EXPERIENCE|EXPERIENCES?|EXPÉRIENCES?|FORMATION|EDUCATION|CERTIFICATIONS|LANGUAGES|LANGUES)|\z)/siu', $rawText, $sumMatch)) {
                $candidateRawSummary = trim($sumMatch[1]);
                if (! empty($candidateRawSummary) && ! preg_match('/^(?:Ce CV|Ce profil|This resume|This CV)\b/iu', $candidateRawSummary)) {
                    $profileSummary = $candidateRawSummary;
                }
            }
        }

        if (empty($profileSummary)) {
            $candProfile = $parsed['profil'] ?? $parsed['profile'] ?? $parsed['candidate_summary'] ?? '';
            if (! empty($candProfile) && ! preg_match('/^(?:Ce CV|Ce profil|This resume|This CV)\b/iu', trim($candProfile))) {
                $profileSummary = trim($candProfile);
            }
        }

        if (empty($profileSummary)) {
            $title = $this->candidate_title ?: 'Professional';
            $profileSummary = $isEnglish
                ? "Experienced {$title} with a proven track record of delivering impactful results and driving technical excellence across distributed environments."
                : "{$title} expérimenté avec une solide expertise technique et une capacité démontrée à piloter des projets avec impact.";
        }

        // 2. Expériences structurées
        $rawExperiences = $parsed['experiences'] ?? [];
        $experiences = [];
        foreach ($rawExperiences as $exp) {
            if (is_array($exp)) {
                $bullets = ! empty($exp['bullets']) ? (array) $exp['bullets'] : (! empty($exp['description']) ? array_filter(array_map('trim', explode("\n", $exp['description']))) : []);
                $experiences[] = [
                    'title' => $exp['title'] ?? 'Poste',
                    'company' => $exp['company'] ?? '',
                    'period' => $exp['period'] ?? '',
                    'description' => $exp['description'] ?? '',
                    'bullets' => $bullets,
                ];

                continue;
            }

            $expStr = (string) $exp;
            $title = 'Poste';
            $company = '';
            $period = '';
            $desc = '';
            $bullets = [];

            if (preg_match('/^([^-—:]+)\s*[-—]\s*([^(:—]+)(?:\s*\(([^)]+)\))?\s*(?::\s*(.*))?$/su', $expStr, $matches)) {
                $title = trim($matches[1]);
                $company = trim($matches[2]);
                $period = trim($matches[3] ?? '');
                $desc = trim($matches[4] ?? '');
            } else {
                $parts = explode(':', $expStr, 2);
                $title = trim($parts[0]);
                $desc = trim($parts[1] ?? '');
            }

            if (! empty($desc)) {
                $split = preg_split('/(?<=[.!?])\s+(?=[A-ZÀ-ÖØ-ß])/u', $desc);
                $bullets = array_filter(array_map('trim', $split ?: []));
            }

            $experiences[] = [
                'title' => $title,
                'company' => $company,
                'period' => $period,
                'description' => $desc,
                'bullets' => count($bullets) > 1 ? $bullets : (! empty($desc) ? [$desc] : []),
            ];
        }

        // 3. Formations structurées
        $rawFormations = $parsed['education'] ?? $parsed['formation'] ?? [];
        $formations = [];
        foreach ($rawFormations as $edu) {
            if (is_array($edu)) {
                $formations[] = [
                    'degree' => $edu['degree'] ?? 'Diplôme',
                    'school' => $edu['school'] ?? '',
                    'year' => $edu['year'] ?? '',
                ];

                continue;
            }

            $eduStr = (string) $edu;
            $degree = $eduStr;
            $school = '';
            $year = '';

            if (preg_match('/^([^-—]+)\s*[-—]\s*([^()]+)(?:\s*\(([^)]+)\))?$/u', $eduStr, $matches)) {
                $degree = trim($matches[1]);
                $school = trim($matches[2]);
                $year = trim($matches[3] ?? '');
            }

            $formations[] = [
                'degree' => $degree,
                'school' => $school,
                'year' => $year,
            ];
        }

        // 4. Compétences & Compétences catégorisées
        $rawSkills = $parsed['skills'] ?? $parsed['competences'] ?? [];
        $categorizedSkills = [];

        if (! empty($rawText) && preg_match_all('/^([A-Za-zÀ-ÿ\s&]+)\s*:\s*([A-Za-z0-9\s,._\-+]+)$/m', $rawText, $catMatches, PREG_SET_ORDER)) {
            $ignored = ['abidjan', 'professional summary', 'education', 'certifications', 'languages', 'phone', 'email'];
            foreach ($catMatches as $cm) {
                $catName = trim($cm[1]);
                if (in_array(strtolower($catName), $ignored)) {
                    continue;
                }
                $skillItems = array_filter(array_map('trim', explode(',', $cm[2])));
                if (! empty($skillItems)) {
                    $categorizedSkills[$catName] = $skillItems;
                }
            }
        }

        // 5. Certifications
        $rawCerts = $parsed['certifications'] ?? [];
        if (empty($rawCerts) && ! empty($rawText)) {
            if (preg_match('/(?:CERTIFICATIONS?)\s*[:\n\-]+\s*(.*?)(?=\n+\s*(?:LANGUAGES?|LANGUES?)|$)/siu', $rawText, $certMatch)) {
                $certLines = array_filter(array_map('trim', explode("\n", $certMatch[1])));
                foreach ($certLines as $cl) {
                    $cl = ltrim($cl, "•-* \t");
                    if (! empty($cl)) {
                        $rawCerts[] = $cl;
                    }
                }
            }
        }

        // 6. Langues
        $rawLanguages = $parsed['langues'] ?? $parsed['languages'] ?? [];
        if (empty($rawLanguages) && ! empty($rawText)) {
            if (preg_match('/(?:LANGUAGES?|LANGUES?)\s*[:\n\-]+\s*(.*?)(?=\n+[A-Z\s]{3,}:|$)/siu', $rawText, $langMatch)) {
                $langText = trim($langMatch[1]);
                if (str_contains($langText, '—')) {
                    $rawLanguages = array_filter(array_map('trim', explode('—', $langText)));
                } else {
                    $rawLanguages = array_filter(array_map('trim', explode("\n", $langText)));
                }
            }
        }

        return [
            'is_english' => $isEnglish,
            'labels' => $labels,
            'profile_summary' => $profileSummary,
            'experiences' => $experiences,
            'education' => $formations,
            'skills' => (array) $rawSkills,
            'categorized_skills' => $categorizedSkills,
            'certifications' => (array) $rawCerts,
            'languages' => (array) $rawLanguages,
        ];
    }
}
