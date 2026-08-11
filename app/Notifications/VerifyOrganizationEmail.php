<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyOrganizationEmail extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        $code = $notifiable->verification_code;

        if (! $code) {
            $code = $notifiable->generateVerificationCode();
        }

        return (new MailMessage)
            ->subject('Vérification de votre compte partenaire - Brillio')
            ->greeting('Bonjour !')
            ->line('Merci de rejoindre Brillio en tant qu’organisation partenaire.')
            ->line('Veuillez cliquer sur le bouton ci-dessous pour vérifier l’adresse e-mail de votre compte administrateur.')
            ->action('Vérifier l’adresse e-mail', $verificationUrl)
            ->line('Si le bouton ci-dessus ne fonctionne pas ou si vous utilisez l’application mobile, vous pouvez entrer ce code de vérification à 6 chiffres :')
            ->line('**'.$code.'**')
            ->line('Ce code et ce lien sont valables pendant 15 minutes.')
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
            Carbon::now()->addMinutes((int) Config::get('auth.verification.expire', 15)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
