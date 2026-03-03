<?php

namespace App\Services;

class ContentModerator
{
    /**
     * List of sensitive keywords to flag and redact.
     */
    protected array $blacklistedKeywords = [
        'insulte', 'idiot', 'connard', 'salope', 'pute', 'merde',
        'raciste', 'nègre', 'bougnoule', 'pd', 'pede', 'gay' /* as insult */,
        'misogyne', 'sexe', 'porno', 'viande', 'manger', /* context dependent but standard filter often includes these */
    ];

    /**
     * Moderate content: detect PII and keywords.
     * Returns an array with is_flagged, redacted, and reason.
     */
    public function moderate(string $content, ?\App\Models\Mentorship $mentorship = null): array
    {
        $isFlagged = false;
        $reasons = [];
        $redacted = $content;

        // 1. Detect Emails
        $emailPattern = '/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}/i';
        if (preg_match_all($emailPattern, $redacted, $matches)) {
            $isFlagged = true;
            $reasons[] = 'Email détecté';
            foreach ($matches[0] as $match) {
                $redacted = str_replace($match, 'XXXXXXXXXXX', $redacted);
            }
        }

        // 2. Detect Phone Numbers (Simple pattern for international/local)
        // Matches: +229 01 00 00 00, 01000000, 00229...
        $phonePattern = '/(\+?[0-9]{1,4}[ \-.]?)?([0-9]{2,}[ \-.]?){3,}/';
        if (preg_match_all($phonePattern, $redacted, $matches)) {
            foreach ($matches[0] as $match) {
                // Filter out short strings that might be years or small numbers
                if (strlen(preg_replace('/[^0-9]/', '', $match)) >= 8) {
                    $isFlagged = true;
                    $reasons[] = 'Numéro de téléphone détecté';
                    $redacted = str_replace($match, 'XXXXXXXXXXX', $redacted);
                }
            }
        }

        // 3. Detect Blacklisted Keywords
        foreach ($this->blacklistedKeywords as $keyword) {
            $pattern = '/\b' . preg_quote($keyword, '/') . '\b/iu';
            if (preg_match($pattern, $redacted)) {
                $isFlagged = true;
                $reasons[] = "Mot clé sensible détecté: {$keyword}";
                $redacted = preg_replace($pattern, 'XXXXXXXXXXX', $redacted);
            }
        }

        // 4. Detect Custom Forbidden Keywords (Mentorship specific)
        if ($mentorship && !empty($mentorship->custom_forbidden_keywords)) {
            foreach ($mentorship->custom_forbidden_keywords as $keyword) {
                $pattern = '/\b' . preg_quote($keyword, '/') . '\b/iu';
                if (preg_match($pattern, $redacted)) {
                    $isFlagged = true;
                    $reasons[] = "Sujet à risque détecté (IA): {$keyword}";
                    $redacted = preg_replace($pattern, 'XXXXXXXXXXX', $redacted);
                }
            }
        }

        return [
            'is_flagged' => $isFlagged,
            'redacted' => $redacted,
            'reason' => $isFlagged ? implode(', ', array_unique($reasons)) : null,
        ];
    }
}