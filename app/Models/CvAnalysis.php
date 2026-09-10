<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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
            $label = 'Document Word (.docx)';
        } elseif ($ext === 'doc' || str_contains($mime, 'msword')) {
            $label = 'Document Word (.doc)';
        } elseif ($ext === 'pdf' || str_contains($mime, 'pdf')) {
            $label = 'Document PDF (.pdf)';
        } elseif (in_array($ext, ['png', 'jpg', 'jpeg', 'webp']) || str_contains($mime, 'image')) {
            $label = 'Image ('.strtoupper($ext ?: 'JPG').')';
        } elseif ($ext === 'txt' || str_contains($mime, 'text/plain')) {
            $label = 'Fichier Texte (.txt)';
        } elseif (! empty($ext)) {
            $label = 'Fichier .'.strtoupper($ext);
        } else {
            $label = 'Document CV';
        }

        return $label;
    }

    /**
     * Classes Tailwind pour le badge de format de fichier
     */
    public function getFileFormatBadgeColorAttribute(): string
    {
        $mime = strtolower((string) ($this->mime_type ?? ''));
        $ext = strtolower(pathinfo((string) ($this->original_filename ?? ''), PATHINFO_EXTENSION));

        if ($ext === 'docx' || $ext === 'doc' || str_contains($mime, 'wordprocessingml') || str_contains($mime, 'msword') || str_contains($mime, 'docx')) {
            $class = 'bg-blue-50 text-blue-700 border-blue-200';
        } elseif ($ext === 'pdf' || str_contains($mime, 'pdf')) {
            $class = 'bg-red-50 text-red-700 border-red-200';
        } elseif (in_array($ext, ['png', 'jpg', 'jpeg', 'webp']) || str_contains($mime, 'image')) {
            $class = 'bg-emerald-50 text-emerald-700 border-emerald-200';
        } else {
            $class = 'bg-gray-100 text-gray-700 border-gray-200';
        }

        return $class;
    }

    /**
     * Vérifie si le fichier d'origine téléversé existe réellement sur le disque public
     */
    public function getHasOriginalFileAttribute(): bool
    {
        return ! empty($this->file_path) && Storage::disk('public')->exists($this->file_path);
    }

    /**
     * Données normalisées prêtes pour les templates ATS (résolution des chaînes et clés bilingues)
     */
    public function getNormalizedCvDataAttribute(): array
    {
        $parsed = $this->parsed_content ?? [];
        $rawText = $parsed['raw_text'] ?? '';

        $isEnglish = $this->detectLanguage($rawText, $parsed);
        $labels = $this->resolveLocalizedLabels($isEnglish);
        $profileSummary = $this->extractCandidateSummary($rawText, $parsed, $isEnglish);
        $rawExperiences = (array) ($parsed['experiences'] ?? []);
        if (! empty($parsed['projets']) && is_array($parsed['projets'])) {
            $rawExperiences = array_merge($rawExperiences, $parsed['projets']);
        } elseif (! empty($parsed['projects']) && is_array($parsed['projects'])) {
            $rawExperiences = array_merge($rawExperiences, $parsed['projects']);
        }
        $experiences = $this->extractNormalizedExperiences($rawExperiences);
        $formations = $this->extractNormalizedEducation($parsed['education'] ?? $parsed['formation'] ?? []);
        $rawSkills = (array) ($parsed['skills'] ?? $parsed['competences'] ?? []);
        $categorizedSkills = $this->extractCategorizedSkills($rawText);
        $certifications = $this->extractNormalizedCertifications($parsed['certifications'] ?? [], $rawText);
        $languages = $this->extractNormalizedLanguages($parsed['langues'] ?? $parsed['languages'] ?? [], $rawText);

        return [
            'is_english' => $isEnglish,
            'labels' => $labels,
            'profile_summary' => $profileSummary,
            'experiences' => $experiences,
            'education' => $formations,
            'skills' => $rawSkills,
            'categorized_skills' => $categorizedSkills,
            'certifications' => $certifications,
            'languages' => $languages,
        ];
    }

    private function detectLanguage(string $rawText, array $parsed): bool
    {
        $corpus = mb_strtolower($rawText.' '.json_encode($parsed));
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
            if (str_contains($corpus, $kw)) {
                $englishScore += 2;
            }
        }
        foreach ($frenchKeywords as $kw) {
            if (str_contains($corpus, $kw)) {
                $frenchScore += 2;
            }
        }

        if (preg_match('/\b(with|and|from|for|team|developer|lead|tools)\b/i', $rawText)) {
            $englishScore += 2;
        }
        if (preg_match('/\b(avec|dans|pour|équipe|développeur|gestion|outils)\b/i', $rawText)) {
            $frenchScore += 2;
        }

        return $englishScore > $frenchScore;
    }

    private function resolveLocalizedLabels(bool $isEnglish): array
    {
        if ($isEnglish) {
            return [
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
            ];
        }

        return [
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
    }

    private const KNOWN_SECTION_KEYWORDS = [
        'COMPÉTENCES', 'COMPETENCES', 'SKILLS', 'CORE SKILLS',
        'EXPÉRIENCE', 'EXPERIENCE', 'EXPÉRIENCES', 'EXPERIENCES',
        'FORMATION', 'FORMATIONS', 'EDUCATION', 'DIPLÔMES', 'DIPLOMES',
        'PROJETS', 'PROJECTS', 'RÉALISATIONS', 'REALISATIONS',
        'LANGUES', 'LANGUAGES', 'CERTIFICATIONS', 'CERTIFICATION',
        'CENTRES D\'INTÉRÊT', 'CENTRES D’INTÉRÊT', 'INTERESTS', 'LOISIRS',
        'RÉFÉRENCES', 'REFERENCES', 'CONTACT',
    ];

    private function extractSectionByHeader(string $rawText, array $headers): string
    {
        if (empty($rawText)) {
            return '';
        }

        $capturing = false;
        $captured = [];

        foreach (explode("\n", $rawText) as $line) {
            $trimmed = trim($line);
            if ($capturing) {
                if ($this->isSectionBoundary($trimmed, $headers)) {
                    break;
                }
                $captured[] = $line;

                continue;
            }

            $rest = $this->matchHeaderPrefix($trimmed, $headers);
            if ($rest !== null) {
                $capturing = true;
                if ($rest !== '') {
                    $captured[] = $rest;
                }
            }
        }

        return trim(implode("\n", $captured));
    }

    private function matchHeaderPrefix(string $trimmed, array $headers): ?string
    {
        foreach ($headers as $header) {
            if (stripos($trimmed, $header) === 0) {
                return trim(substr($trimmed, strlen($header)), ": \t-");
            }
        }

        return null;
    }

    private function isSectionBoundary(string $trimmed, array $currentHeaders): bool
    {
        if ($trimmed === '') {
            return false;
        }

        $upper = mb_strtoupper($trimmed);
        foreach (self::KNOWN_SECTION_KEYWORDS as $keyword) {
            if (! $this->isHeaderInCurrentList($keyword, $currentHeaders) && mb_stripos($upper, $keyword) === 0) {
                return true;
            }
        }

        return (bool) preg_match('/^[A-ZÀ-ÖØ-ß\s]{3,}:?$/u', $trimmed);
    }

    private function isHeaderInCurrentList(string $keyword, array $currentHeaders): bool
    {
        foreach ($currentHeaders as $ch) {
            if (mb_stripos($keyword, $ch) !== false || mb_stripos($ch, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    private function extractCandidateSummary(string $rawText, array $parsed, bool $isEnglish): string
    {
        $candProfile = trim((string) ($parsed['profil'] ?? $parsed['profile'] ?? $parsed['summary'] ?? $parsed['candidate_summary'] ?? ''));
        if (! empty($candProfile) && ! preg_match('/^(?:Ce CV|Ce profil|This resume|This CV)\b/iu', $candProfile)) {
            return $candProfile;
        }

        if (! empty($rawText)) {
            $summaryRaw = $this->extractSectionByHeader($rawText, [
                'PROFESSIONAL SUMMARY',
                'RÉSUMÉ PROFESSIONNEL',
                'RESUME PROFESSIONNEL',
                'PROFIL PROFESSIONNEL',
                'PROFILE',
                'PROFIL',
                'SUMMARY',
                'RÉSUMÉ',
                'ABOUT ME',
            ]);
            if (! empty($summaryRaw) && ! preg_match('/^(?:Ce CV|Ce profil|This resume|This CV)\b/iu', $summaryRaw)) {
                return $summaryRaw;
            }
        }

        $title = $this->candidate_title ?: 'Professional';

        return $isEnglish
            ? "Experienced {$title} with a proven track record of delivering impactful results and driving technical excellence across distributed environments."
            : "{$title} expérimenté avec une solide expertise technique et une capacité démontrée à piloter des projets avec impact.";
    }

    private function extractNormalizedExperiences(array $rawExperiences): array
    {
        $experiences = [];
        foreach ($rawExperiences as $exp) {
            $experiences[] = is_array($exp)
                ? $this->normalizeArrayExperience($exp)
                : $this->parseStringExperience((string) $exp);
        }

        return $experiences;
    }

    private function normalizeArrayExperience(array $exp): array
    {
        $bullets = [];
        if (! empty($exp['bullets'])) {
            $bullets = (array) $exp['bullets'];
        } elseif (! empty($exp['description'])) {
            $bullets = array_filter(array_map('trim', explode("\n", (string) $exp['description'])));
        }

        $description = (string) ($exp['description'] ?? '');
        if (empty($description) && ! empty($bullets)) {
            $description = implode(' ', $bullets);
        }

        return [
            'title' => $exp['title'] ?? 'Poste',
            'company' => $exp['company'] ?? '',
            'period' => $exp['period'] ?? '',
            'description' => $description,
            'bullets' => $bullets,
        ];
    }

    private function parseStringExperience(string $expStr): array
    {
        $company = '';
        $period = '';
        $desc = '';

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

        $bullets = [];
        if (! empty($desc)) {
            $split = preg_split('/(?<=[.!?])\s+(?=[A-ZÀ-ÖØ-ß])/u', $desc);
            $bullets = array_filter(array_map('trim', $split ?: []));
        }

        $finalBullets = count($bullets) > 1 ? $bullets : (! empty($desc) ? [$desc] : []);

        return [
            'title' => $title,
            'company' => $company,
            'period' => $period,
            'description' => $desc,
            'bullets' => $finalBullets,
        ];
    }

    private function extractNormalizedEducation(array $rawFormations): array
    {
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

        return $formations;
    }

    private function extractCategorizedSkills(string $rawText): array
    {
        $categorizedSkills = [];
        if (empty($rawText) || ! preg_match_all('/^([A-Za-zÀ-ÿ\s&]+)\s*:\s*([A-Za-z0-9\s,._\-+]+)$/m', $rawText, $catMatches, PREG_SET_ORDER)) {
            return $categorizedSkills;
        }

        $ignored = ['abidjan', 'professional summary', 'education', 'certifications', 'languages', 'phone', 'email'];
        foreach ($catMatches as $cm) {
            $catName = trim($cm[1]);
            if (in_array(strtolower($catName), $ignored, true)) {
                continue;
            }
            $skillItems = array_filter(array_map('trim', explode(',', $cm[2])));
            if (! empty($skillItems)) {
                $categorizedSkills[$catName] = $skillItems;
            }
        }

        return $categorizedSkills;
    }

    private function extractNormalizedCertifications(array $rawCerts, string $rawText): array
    {
        if (empty($rawCerts) && ! empty($rawText)) {
            $certText = $this->extractSectionByHeader($rawText, ['CERTIFICATIONS', 'CERTIFICATION']);
            if (! empty($certText)) {
                $certLines = array_filter(array_map('trim', explode("\n", $certText)));
                foreach ($certLines as $cl) {
                    $cleaned = ltrim($cl, "•-* \t");
                    if ($cleaned !== '') {
                        $rawCerts[] = $cleaned;
                    }
                }
            }
        }

        return $rawCerts;
    }

    private function extractNormalizedLanguages(array $rawLanguages, string $rawText): array
    {
        if (empty($rawLanguages) && ! empty($rawText)) {
            $langText = $this->extractSectionByHeader($rawText, ['LANGUAGES', 'LANGUES', 'LANGUAGE', 'LANGUE']);
            if (! empty($langText)) {
                $delimiter = str_contains($langText, '—') ? '—' : "\n";
                $rawLanguages = array_filter(array_map('trim', explode($delimiter, $langText)));
            }
        }

        return $rawLanguages;
    }
}
