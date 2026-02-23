<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmail extends BaseVerifyEmail
{
    use Queueable;

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $verificationUrl);
        }

        return (new MailMessage)
            ->subject('Vérification de votre adresse e-mail')
            ->greeting('Bonjour !')
            ->line('Veuillez cliquer sur le bouton ci-dessous pour vérifier votre adresse e-mail.')
            ->action('Vérifier l’adresse e-mail', $verificationUrl)
            ->line('Si vous n’avez pas créé de compte, aucune autre action n’est requise.')
            ->salutation('Cordialement, Brillio');
    }
}
