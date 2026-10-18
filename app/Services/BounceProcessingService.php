<?php

namespace App\Services;

use App\Models\EmailSuppression;
use App\Models\User;

class BounceProcessingService
{
    /**
     * Analyse un texte brut d'e-mail de rejet (NDR) ou un lot de notifications de rebonds.
     * Extrait les adresses e-mails en échec, les codes d'erreurs et met à jour les exclusions.
     *
     * @return array{total_found: int, hard_bounced: int, soft_bounced: int, suppressed: list<string>, details: list<array>}
     */
    public function parseBounceText(string $rawText): array
    {
        $details = [];
        $suppressed = [];
        $hardCount = 0;
        $softCount = 0;

        // Découper par blocs d'e-mails ou analyser globalement
        $blocks = preg_split('/(?=Delivery to the following recipient|Delivery incomplete|Final-Recipient:|Action:\s*failed)/i', $rawText);

        if (count($blocks) <= 1) {
            $blocks = [$rawText];
        }

        foreach ($blocks as $block) {
            $emails = $this->extractEmailsFromBlock($block);
            if (empty($emails)) {
                continue;
            }

            $classification = $this->classifyBounceReason($block);
            $reason = $classification['reason'];
            $isHard = $classification['is_hard'];

            foreach ($emails as $email) {
                $email = strtolower(trim($email));

                // Ne pas exclure nos propres adresses système
                if (in_array($email, ['contact@brillio.africa', 'noreply@brillio.africa', 'admin@brillio.com'], true)) {
                    continue;
                }

                $record = EmailSuppression::where('email', $email)->first();

                if ($isHard) {
                    $hardCount++;
                    EmailSuppression::updateOrCreate(
                        ['email' => $email],
                        [
                            'reason' => mb_strimwidth($reason, 0, 250, '...'),
                            'source' => 'system_auto',
                            'bounce_type' => 'hard',
                            'bounce_count' => ($record ? $record->bounce_count : 0) + 1,
                            'last_bounce_at' => now(),
                        ]
                    );

                    $suppressed[] = $email;
                    $details[] = [
                        'email' => $email,
                        'type' => 'hard',
                        'status' => 'Exclusion immédiate',
                        'reason' => $reason,
                    ];
                } else {
                    // Soft bounce (ex: boîte pleine 452 4.2.2)
                    $softCount++;
                    $count = ($record ? $record->bounce_count : 0) + 1;
                    $isExcludedNow = $count >= 3;

                    EmailSuppression::updateOrCreate(
                        ['email' => $email],
                        [
                            'reason' => mb_strimwidth($reason, 0, 250, '...'),
                            'source' => 'system_auto',
                            'bounce_type' => 'soft',
                            'bounce_count' => $count,
                            'last_bounce_at' => now(),
                        ]
                    );

                    $suppressed[] = $email;
                    $details[] = [
                        'email' => $email,
                        'type' => 'soft',
                        'status' => $isExcludedNow ? "Exclu (seuil de {$count}/3 atteint)" : "Avertissement (rebond {$count}/3)",
                        'reason' => $reason,
                    ];
                }
            }
        }

        // Si aucun format structuré n'a fonctionné, extraction de secours par motif regex d'e-mails
        if (empty($suppressed)) {
            $fallbackEmails = $this->extractAllValidEmails($rawText);
            foreach ($fallbackEmails as $email) {
                if (in_array($email, ['contact@brillio.africa', 'noreply@brillio.africa', 'admin@brillio.com'], true)) {
                    continue;
                }

                $classification = $this->classifyBounceReason($rawText);
                EmailSuppression::updateOrCreate(
                    ['email' => $email],
                    [
                        'reason' => mb_strimwidth($classification['reason'], 0, 250, '...'),
                        'source' => 'system_auto',
                        'bounce_type' => $classification['is_hard'] ? 'hard' : 'soft',
                        'bounce_count' => 1,
                        'last_bounce_at' => now(),
                    ]
                );

                $suppressed[] = $email;
                $details[] = [
                    'email' => $email,
                    'type' => $classification['is_hard'] ? 'hard' : 'soft',
                    'status' => 'Exclusion enregistrée',
                    'reason' => $classification['reason'],
                ];
            }
        }

        return [
            'total_found' => count(array_unique($suppressed)),
            'hard_bounced' => $hardCount,
            'soft_bounced' => $softCount,
            'suppressed' => array_values(array_unique($suppressed)),
            'details' => $details,
        ];
    }

    /**
     * Vérifie la validité de l'enregistrement MX du domaine de l'adresse e-mail.
     * Permet d'éliminer les faux domaines avant tout contact avec le serveur SMTP Namecheap.
     */
    public function hasValidMxRecord(string $email): bool
    {
        $email = strtolower(trim($email));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $parts = explode('@', $email);
        $domain = end($parts);

        if (! $domain) {
            return false;
        }

        // En environnement de test ou pour les domaines réservés RFC, considérer comme valide
        $reservedTestDomains = ['example.com', 'example.org', 'example.net', 'test.com', 'localhost'];
        if (in_array($domain, $reservedTestDomains, true) || app()->environment('testing')) {
            return true;
        }

        // Vérifier l'existence d'enregistrements MX ou A sur le domaine
        if (function_exists('checkdnsrr')) {
            try {
                if (checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A')) {
                    return true;
                }

                // Auto-exclusion préventive pour éviter de surcharger Namecheap
                EmailSuppression::updateOrCreate(
                    ['email' => $email],
                    [
                        'reason' => "Domaine invalide ou sans serveur de messagerie MX ({$domain})",
                        'source' => 'system_auto',
                        'bounce_type' => 'preflight_dns',
                        'bounce_count' => 1,
                        'last_bounce_at' => now(),
                    ]
                );

                return false;
            } catch (\Throwable $e) {
                // En cas d'indisponibilité DNS locale temporaire, ne pas bloquer
                return true;
            }
        }

        return true;
    }

    /**
     * Scanne les adresses des utilisateurs en base pour exclure les domaines sans serveur mail actif.
     */
    public function scanUsersForInvalidDomains(int $limit = 200): array
    {
        $excludedCount = 0;
        $checkedCount = 0;
        $excludedEmails = [];

        $users = User::whereNotNull('email')
            ->whereNotIn('email', EmailSuppression::pluck('email'))
            ->latest('id')
            ->limit($limit)
            ->get();

        foreach ($users as $user) {
            $checkedCount++;
            if (! $this->hasValidMxRecord($user->email)) {
                $excludedCount++;
                $excludedEmails[] = $user->email;
            }
        }

        return [
            'checked' => $checkedCount,
            'excluded' => $excludedCount,
            'emails' => $excludedEmails,
        ];
    }

    /**
     * Extrait les adresses e-mails cibles d'un bloc de notification NDR.
     */
    private function extractEmailsFromBlock(string $block): array
    {
        $patterns = [
            '/delivering your message to\s+([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/i',
            '/recipient failed permanently:\s*[\r\n\s]*([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/i',
            '/Final-Recipient:\s*(?:rfc822;)?\s*([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/i',
            '/Failed recipient:\s*<?([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})>?/i',
            '/To:\s*<([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})>/i',
        ];

        $found = [];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $block, $matches)) {
                foreach ($matches[1] as $match) {
                    $candidate = strtolower(trim($match));
                    if (filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                        $found[] = $candidate;
                    }
                }
            }
        }

        return array_values(array_unique($found));
    }

    /**
     * Extrait toutes les adresses e-mails valides d'un texte.
     */
    private function extractAllValidEmails(string $text): array
    {
        $found = [];
        if (preg_match_all('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text, $matches)) {
            foreach ($matches[0] as $email) {
                $email = strtolower(trim($email));
                // Filtrer les adresses d'infrastructure de rebond
                if (! str_contains($email, 'mailer-daemon') && ! str_contains($email, 'jellyfish.systems')) {
                    $found[] = $email;
                }
            }
        }

        return array_values(array_unique($found));
    }

    /**
     * Détermine la nature de l'erreur (Hard bounce vs Soft bounce) et formule un motif explicite.
     */
    private function classifyBounceReason(string $text): array
    {
        $lower = strtolower($text);

        // Motifs Soft Bounce (Erreurs temporaires / Boîte saturée)
        if (str_contains($lower, '452-4.2.2') || str_contains($lower, '452 4.2.2') || str_contains($lower, 'overquotatemp') || str_contains($lower, 'out of storage space') || str_contains($lower, 'mailbox full')) {
            return [
                'is_hard' => false,
                'reason' => 'Boîte e-mail saturée / quota dépassé (Erreur SMTP 452 4.2.2 OverQuotaTemp)',
            ];
        }

        if (str_contains($lower, '451') || str_contains($lower, '421') || str_contains($lower, 'temporarily deferred')) {
            return [
                'is_hard' => false,
                'reason' => 'Serveur destinataire temporairement inaccessible (Erreur SMTP 4xx)',
            ];
        }

        // Motifs Hard Bounce (Compte inexistant / Rejet définitif)
        if (str_contains($lower, '550-5.1.1') || str_contains($lower, '550 5.1.1') || str_contains($lower, 'nosuchuser') || str_contains($lower, 'does not exist')) {
            return [
                'is_hard' => true,
                'reason' => 'Compte e-mail inexistant / adresse introuvable (Erreur SMTP 550 5.1.1 NoSuchUser)',
            ];
        }

        if (str_contains($lower, 'user unknown') || str_contains($lower, 'invalid recipient') || str_contains($lower, 'recipient address rejected')) {
            return [
                'is_hard' => true,
                'reason' => 'Destinataire invalide ou refusé par le serveur de messagerie (550 User Unknown)',
            ];
        }

        if (str_contains($lower, 'account disabled') || str_contains($lower, 'mailbox unavailable')) {
            return [
                'is_hard' => true,
                'reason' => 'Compte désactivé ou indisponible auprès du fournisseur',
            ];
        }

        return [
            'is_hard' => true,
            'reason' => 'Échec définitif de livraison signalé par Mail Delivery Subsystem',
        ];
    }
}
