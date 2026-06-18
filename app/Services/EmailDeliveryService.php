<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class EmailDeliveryService
{
    /**
     * Adresses exclues de tout envoi (faux comptes, sécurité).
     *
     * @return list<string>
     */
    public function excludedEmails(): array
    {
        $raw = config('app.excluded_email_recipients', 'admin@brillio.com');

        return array_values(array_filter(array_map(
            fn (string $email) => strtolower(trim($email)),
            is_array($raw) ? $raw : explode(',', (string) $raw)
        )));
    }

    public function isExcludedEmail(string $email): bool
    {
        return in_array(strtolower(trim($email)), $this->excludedEmails(), true);
    }

    /**
     * Filtre une liste d'adresses (exclusion + format valide).
     *
     * @param  list<string>  $emails
     * @return list<string>
     */
    public function filterRecipientList(array $emails): array
    {
        $filtered = array_filter($emails, function (string $email) {
            $email = trim($email);

            return filter_var($email, FILTER_VALIDATE_EMAIL)
                && ! $this->isExcludedEmail($email);
        });

        return array_values(array_unique($filtered));
    }

    /**
     * Requête de base pour les utilisateurs éligibles aux e-mails de prospection.
     */
    public function marketingEligibleUsersQuery(): Builder
    {
        $excluded = $this->excludedEmails();

        return User::query()
            ->where('is_archived', false)
            ->where('is_blocked', false)
            ->when(! empty($excluded), fn (Builder $q) => $q->whereNotIn('email', $excluded));
    }

    /**
     * Détecte une erreur de livraison liée à la boîte mail du destinataire.
     */
    public function isMailboxDeliveryError(\Throwable $exception): bool
    {
        $message = strtolower($exception->getMessage());

        $patterns = [
            '452-4.2.2',
            '452 4.2.2',
            'overquotatemp',
            'out of storage space',
            'mailbox full',
            'mailbox is full',
            '552-',
            '552 ',
            '550-5.1.1',
            '550 5.1.1',
            'user unknown',
            'does not exist',
            'no such user',
            'invalid recipient',
            'invalid recipients',
            'recipient address rejected',
            'account disabled',
            'account has been disabled',
            'mailbox unavailable',
            'mailbox not found',
        ];

        foreach ($patterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Archive automatiquement le compte jeune associé à une adresse en échec de livraison.
     */
    public function handleDeliveryFailure(string $email, \Throwable $exception): void
    {
        if (! $this->isMailboxDeliveryError($exception)) {
            return;
        }

        $user = User::where('email', $email)->first();

        if (! $user || ! $user->isJeune() || $user->is_archived) {
            return;
        }

        $reason = 'Archivé automatiquement : boîte mail injoignable ou pleine ('.class_basename($exception).').';

        $user->update([
            'is_archived' => true,
            'archived_at' => now(),
            'archived_reason' => $reason,
        ]);

        Log::warning('Compte jeune archivé automatiquement suite à une erreur de livraison e-mail', [
            'user_id' => $user->id,
            'email' => $email,
            'error' => $exception->getMessage(),
        ]);
    }
}
