<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyOrganizationEmail extends VerifyEmail
{
    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Vérification de votre compte partenaire - Brillio')
            ->greeting('Bonjour !')
            ->line('Merci de rejoindre Brillio en tant qu’organisation partenaire.')
            ->line('Veuillez cliquer sur le bouton ci-dessous pour vérifier l’adresse e-mail de votre compte administrateur.')
            ->action('Vérifier l’adresse e-mail', $verificationUrl)
            ->line('Si vous n’avez pas créé de compte, aucune autre action n’est requise.')
            ->salutation('Cordialement, Brillio');
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        if (static::$createUrlCallback) {
            return call_user_func(static::$createUrlCallback, $notifiable);
        }

        return URL::temporarySignedRoute(
            'organization.verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
