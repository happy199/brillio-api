<?php

namespace App\Services;

use App\Models\EmailSuppression;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class BounceProcessingService
{
    public const SYSTEM_EMAIL_CONTACT = 'contact@brillio.africa';

    public const SYSTEM_EMAIL_NOREPLY = 'noreply@brillio.africa';

    public const SYSTEM_EMAIL_ADMIN = 'admin@brillio.com';

    private const SYSTEM_EMAILS = [
        self::SYSTEM_EMAIL_CONTACT,
        self::SYSTEM_EMAIL_NOREPLY,
        self::SYSTEM_EMAIL_ADMIN,
    ];

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

        $blocks = $this->splitIntoBlocks($rawText);

        foreach ($blocks as $block) {
            $this->processBlock($block, $details, $suppressed, $hardCount, $softCount);
        }

        if (empty($suppressed)) {
            $this->processFallback($rawText, $details, $suppressed);
        }

        $uniqueSuppressed = array_values(array_unique($suppressed));

        return [
            'total_found' => count($uniqueSuppressed),
            'hard_bounced' => $hardCount,
            'soft_bounced' => $softCount,
            'suppressed' => $uniqueSuppressed,
            'details' => $details,
        ];
    }

    /**
     * Découpe le texte en blocs distincts de notifications NDR.
     */
    private function splitIntoBlocks(string $rawText): array
    {
        $blocks = preg_split('/(?=Delivery to the following recipient|Delivery incomplete|Final-Recipient:|Action:\s*failed)/i', $rawText);

        return (empty($blocks) || count($blocks) <= 1) ? [$rawText] : $blocks;
    }

    /**
     * Traite un bloc individuel d'erreur NDR.
     */
    private function processBlock(string $block, array &$details, array &$suppressed, int &$hardCount, int &$softCount): void
    {
        $emails = $this->extractEmailsFromBlock($block);
        if (empty($emails)) {
            return;
        }

        $classification = $this->classifyBounceReason($block);

        foreach ($emails as $email) {
            $normalized = strtolower(trim($email));
            if ($this->isSystemEmail($normalized)) {
                continue;
            }

            $this->processSingleEmailBounce($normalized, $classification, $details, $suppressed, $hardCount, $softCount);
        }
    }

    /**
     * Enregistre le rejet pour une adresse e-mail unique (Hard ou Soft).
     */
    private function processSingleEmailBounce(
        string $email,
        array $classification,
        array &$details,
        array &$suppressed,
        int &$hardCount,
        int &$softCount
    ): void {
        $record = EmailSuppression::where('email', $email)->first();
        $isHard = (bool) $classification['is_hard'];
        $reason = (string) $classification['reason'];

        if ($isHard) {
            $hardCount++;
            $this->recordSuppression($email, $reason, 'hard', ($record ? $record->bounce_count : 0) + 1);
            $status = 'Exclusion immédiate';
        } else {
            $softCount++;
            $count = ($record ? $record->bounce_count : 0) + 1;
            $this->recordSuppression($email, $reason, 'soft', $count);
            $status = $count >= 3 ? "Exclu (seuil de {$count}/3 atteint)" : "Avertissement (rebond {$count}/3)";
        }

        $suppressed[] = $email;
        $details[] = [
            'email' => $email,
            'type' => $isHard ? 'hard' : 'soft',
            'status' => $status,
            'reason' => $reason,
        ];
    }

    /**
     * Extraction de secours si aucun motif de bloc standard n'a été identifié.
     */
    private function processFallback(string $rawText, array &$details, array &$suppressed): void
    {
        $fallbackEmails = $this->extractAllValidEmails($rawText);
        $classification = $this->classifyBounceReason($rawText);
        $type = $classification['is_hard'] ? 'hard' : 'soft';

        foreach ($fallbackEmails as $email) {
            $normalized = strtolower(trim($email));
            if ($this->isSystemEmail($normalized)) {
                continue;
            }

            $this->recordSuppression($normalized, $classification['reason'], $type);
            $suppressed[] = $normalized;
            $details[] = [
                'email' => $normalized,
                'type' => $type,
                'status' => 'Exclusion enregistrée',
                'reason' => $classification['reason'],
            ];
        }
    }

    /**
     * Vérifie si une adresse e-mail correspond à une adresse système protégée.
     */
    private function isSystemEmail(string $email): bool
    {
        return in_array($email, self::SYSTEM_EMAILS, true);
    }

    /**
     * Enregistre ou met à jour une exclusion e-mail avec décompte.
     */
    private function recordSuppression(
        string $email,
        string $reason,
        string $type = 'hard',
        int $bounceCount = 1
    ): EmailSuppression {
        return EmailSuppression::updateOrCreate(
            ['email' => $email],
            [
                'reason' => mb_strimwidth($reason, 0, 250, '...'),
                'source' => 'system_auto',
                'bounce_type' => $type,
                'bounce_count' => $bounceCount,
                'last_bounce_at' => now(),
            ]
        );
    }

    /**
     * Vérifie la validité de l'enregistrement MX du domaine de l'adresse e-mail.
     * Permet d'éliminer les faux domaines avant tout contact avec le serveur SMTP Namecheap.
     */
    public function hasValidMxRecord(string $email): bool
    {
        $normalized = strtolower(trim($email));
        $domain = substr(strrchr($normalized, '@') ?: '', 1);

        if (! filter_var($normalized, FILTER_VALIDATE_EMAIL) || empty($domain)) {
            return false;
        }

        $reserved = ['example.com', 'example.org', 'example.net', 'test.com', 'localhost'];
        $isBypassed = in_array($domain, $reserved, true) || app()->environment('testing');

        return $isBypassed || $this->verifyDnsRecord($normalized, $domain);
    }

    /**
     * Effectue la vérification réseau DNS MX / A sur le domaine.
     */
    private function verifyDnsRecord(string $email, string $domain): bool
    {
        $hasRecord = true;

        try {
            if (function_exists('checkdnsrr') && ! (checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A'))) {
                $hasRecord = false;
                $this->recordSuppression(
                    $email,
                    "Domaine invalide ou sans serveur de messagerie MX ({$domain})",
                    'preflight_dns'
                );
            }
        } catch (\Throwable $e) {
            $hasRecord = true;
        }

        return $hasRecord;
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
     * Extrait toutes les adresses e-mails trouvées dans un texte sans restriction de contexte.
     */
    private function extractAllValidEmails(string $text): array
    {
        $emails = [];
        if (preg_match_all('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text, $matches)) {
            foreach ($matches[0] as $candidate) {
                $candidate = strtolower(trim($candidate));
                if (filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $candidate;
                }
            }
        }

        return array_values(array_unique($emails));
    }

    /**
     * Détermine la nature de l'erreur (Hard bounce vs Soft bounce) et formule un motif explicite.
     */
    private function classifyBounceReason(string $text): array
    {
        $lower = strtolower($text);
        $isHard = true;
        $reason = 'Échec définitif de livraison signalé par Mail Delivery Subsystem';

        $isMailboxFull = str_contains($lower, '452-4.2.2')
            || str_contains($lower, '452 4.2.2')
            || str_contains($lower, 'overquotatemp')
            || str_contains($lower, 'out of storage space')
            || str_contains($lower, 'mailbox full');

        $isDeferral = str_contains($lower, '451')
            || str_contains($lower, '421')
            || str_contains($lower, 'temporarily deferred');

        $isNoSuchUser = str_contains($lower, '550-5.1.1')
            || str_contains($lower, '550 5.1.1')
            || str_contains($lower, 'nosuchuser')
            || str_contains($lower, 'does not exist');

        $isInvalidRecipient = str_contains($lower, 'user unknown')
            || str_contains($lower, 'invalid recipient')
            || str_contains($lower, 'recipient address rejected');

        $isDisabledAccount = str_contains($lower, 'account disabled')
            || str_contains($lower, 'mailbox unavailable');

        if ($isMailboxFull) {
            $isHard = false;
            $reason = 'Boîte e-mail saturée / quota dépassé (Erreur SMTP 452 4.2.2 OverQuotaTemp)';
        } elseif ($isDeferral) {
            $isHard = false;
            $reason = 'Serveur destinataire temporairement inaccessible (Erreur SMTP 4xx)';
        } elseif ($isNoSuchUser) {
            $reason = 'Compte e-mail inexistant / adresse introuvable (Erreur SMTP 550 5.1.1 NoSuchUser)';
        } elseif ($isInvalidRecipient) {
            $reason = 'Destinataire invalide ou refusé par le serveur de messagerie (550 User Unknown)';
        } elseif ($isDisabledAccount) {
            $reason = 'Compte désactivé ou indisponible auprès du fournisseur';
        }

        return [
            'is_hard' => $isHard,
            'reason' => $reason,
        ];
    }

    /**
     * Analyse un rejet complexe ou non standard via l'IA Brillio (OpenRouter / Gemini).
     * Recommande l'action à mener et extrait les adresses en échec.
     *
     * @return array{success: bool, message?: string, data?: array, suppressed_count?: int}
     */
    public function analyzeBounceWithAi(string $rawText): array
    {
        $result = [
            'success' => false,
            'message' => 'Initialisation',
        ];

        try {
            $iaService = app(BrillioIAService::class);

            if (! $iaService->isApiKeyConfigured()) {
                $result['message'] = "La clé API de l'IA (OpenRouter / Gemini) n'est pas configurée.";
            } else {
                $result = $this->executeAiBounceAnalysis($iaService, $rawText);
            }
        } catch (\Throwable $e) {
            Log::error("Erreur lors de l'analyse du bounce par l'IA Brillio: ".$e->getMessage());
            $result = [
                'success' => false,
                'message' => "Erreur lors de l'appel à l'IA : ".$e->getMessage(),
            ];
        }

        return $result;
    }

    /**
     * Exécute l'analyse IA via OpenRouter et applique les exclusions recommandées.
     */
    private function executeAiBounceAnalysis(BrillioIAService $iaService, string $rawText): array
    {
        $systemPrompt = "Tu es un ingénieur expert en délivrabilité d'e-mails et protocoles SMTP.
Ta tâche est d'analyser un message d'erreur ou notification de rejet d'e-mail (NDR/Bounce).
Extrais les informations au format JSON strict suivant :
{
    \"recipients\": [\"adresse@example.com\"],
    \"diagnostic\": \"Explication claire en français de la cause du rejet\",
    \"bounce_type\": \"hard\" (compte inexistant, faux domaine) OU \"soft\" (boîte pleine, temporaire) OU \"spam_block\",
    \"smtp_code\": \"ex: 550 5.1.1 ou 452 4.2.2\",
    \"recommended_action\": \"exclude_immediately\" OU \"retry_later\" OU \"check_spf_dkim\",
    \"action_explanation\": \"Conseil précis en français sur l'action à entreprendre\"
}
Réponds UNIQUEMENT avec un JSON valide, sans balises markdown ``` ni texte autour.";

        $response = $iaService->analyzeText($rawText, $systemPrompt);
        $cleanJson = $iaService->cleanJson($response);
        $data = json_decode($cleanJson, true);

        if (! is_array($data) || empty($data['recipients'])) {
            return [
                'success' => false,
                'message' => "L'IA n'a pas pu identifier d'adresse en échec dans ce message.",
                'raw_response' => $response,
            ];
        }

        $suppressedCount = $this->applyAiExclusions($data);

        return [
            'success' => true,
            'data' => $data,
            'suppressed_count' => $suppressedCount,
        ];
    }

    /**
     * Applique les exclusions retournées par l'IA.
     */
    private function applyAiExclusions(array $data): int
    {
        $suppressedCount = 0;
        $recipients = (array) ($data['recipients'] ?? []);
        $isHard = ($data['bounce_type'] ?? '') === 'hard' || ($data['recommended_action'] ?? '') === 'exclude_immediately';
        $reason = ($data['smtp_code'] ?? 'IA').' : '.($data['diagnostic'] ?? 'Rejet identifié par IA');
        $type = $isHard ? 'hard' : 'soft';

        foreach ($recipients as $email) {
            $normalized = strtolower(trim((string) $email));
            if (filter_var($normalized, FILTER_VALIDATE_EMAIL) && ! $this->isSystemEmail($normalized)) {
                $this->recordSuppression($normalized, $reason, $type);
                $suppressedCount++;
            }
        }

        return $suppressedCount;
    }
}
