<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidEmailDomain implements ValidationRule
{
    /**
     * Liste des domaines d'emails jetables / temporaires bloqués.
     *
     * @var list<string>
     */
    protected array $disposableDomains = [
        'yopmail.com',
        'yopmail.fr',
        'yopmail.net',
        'tempmail.com',
        'temp-mail.org',
        'guerrillamail.com',
        'mailinator.com',
        '10minutemail.com',
        'trashmail.com',
        'dispostable.com',
        'getnada.com',
        'throwawaymail.com',
        'sharklasers.com',
        'maildrop.cc',
        'mohmal.com',
        'crazymailing.com',
    ];

    /**
     * Liste des fautes de frappes courantes.
     *
     * @var list<string>
     */
    protected array $typoDomains = [
        'icoud.com', 'icloude.com', 'gamail.com', 'gamil.com',
        'gmai.com', 'gmal.com', 'yaho.com', 'yhaoo.com',
        'outlok.com', 'hotmal.com', 'gmaill.com',
    ];

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || empty($value)) {
            return;
        }

        $email = strtolower(trim($value));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $fail('L’adresse e-mail n’est pas dans un format valide.');

            return;
        }

        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            $fail('L’adresse e-mail n’est pas valide.');

            return;
        }

        $domain = trim($parts[1]);

        if (in_array($domain, $this->typoDomains, true)) {
            $fail("Le domaine de l'e-mail ({$domain}) semble contenir une faute de frappe. Veuillez vérifier votre adresse.");

            return;
        }

        if (in_array($domain, $this->disposableDomains, true)) {
            $fail('Les adresses e-mail temporaires ou jetables ne sont pas autorisées.');

            return;
        }

        // En environnement local ou de test, éviter d'échouer si le DNS n'est pas accessible
        if (app()->environment('testing')) {
            return;
        }

        // Vérification de l'existence de serveurs de messagerie (MX) ou hôte (A) pour le domaine
        if (! checkdnsrr($domain, 'MX') && ! checkdnsrr($domain, 'A')) {
            $fail("Le domaine de l’adresse e-mail ({$domain}) n’existe pas ou ne peut pas recevoir d’e-mails.");
        }
    }
}
