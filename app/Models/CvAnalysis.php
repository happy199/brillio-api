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
     * Données normalisées prêtes pour les templates ATS (résolution des chaînes et clés bilingues)
     */
    public function getNormalizedCvDataAttribute(): array
    {
        $parsed = $this->parsed_content ?? [];
        $rawText = $parsed['raw_text'] ?? '';

        // 1. Expériences structurées
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

        // 2. Formations structurées
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

        // 3. Compétences & Compétences catégorisées
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

        return [
            'experiences' => $experiences,
            'education' => $formations,
            'skills' => (array) $rawSkills,
            'categorized_skills' => $categorizedSkills,
            'certifications' => (array) ($parsed['certifications'] ?? []),
            'languages' => (array) ($parsed['langues'] ?? $parsed['languages'] ?? []),
        ];
    }
}
