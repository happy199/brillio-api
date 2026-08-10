<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmail extends BaseVerifyEmail implements ShouldQueue
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

        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $verificationUrl);
        }

        return (new MailMessage)
            ->subject('Vérification de votre adresse e-mail - Brillio')
            ->greeting('Bonjour '.$notifiable->name.' !')
            ->line('Veuillez cliquer sur le bouton ci-dessous pour vérifier votre adresse e-mail.')
            ->action('Vérifier l’adresse e-mail', $verificationUrl)
            ->line('Si le bouton ci-dessus ne fonctionne pas ou si vous utilisez l’application mobile, vous pouvez entrer ce code de vérification à 6 chiffres :')
            ->line('**'.$code.'**')
            ->line('Ce code est valable pendant 60 minutes.')
            ->line('Si vous n’avez pas créé de compte, aucune autre action n’est requise.')
            ->salutation('Cordialement, Brillio');
    }
}
