<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMentorResourceNotification extends Notification
{
    use Queueable;

    public $resource;
    public $mentor;

    /**
     * Create a new notification instance.
     */
    public function __construct($resource, $mentor)
    {
        $this->resource = $resource;
        $this->mentor = $mentor;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database']; // Can add 'mail' later if needed
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Nouvelle ressource de votre mentor')
                    ->line('Votre mentor ' . $this->mentor->first_name . ' ' . $this->mentor->last_name . ' a publié une nouvelle ressource : ' . $this->resource->title)
                    ->action('Voir la ressource', url('/ressources/' . $this->resource->slug))
                    ->line('Bon apprentissage !');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Nouvelle ressource : ' . $this->resource->title,
            'message' => 'Votre mentor ' . $this->mentor->first_name . ' ' . $this->mentor->last_name . ' a publié une nouvelle ressource.',
            'url' => url('/ressources/' . $this->resource->slug),
            'resource_id' => $this->resource->id,
            'mentor_id' => $this->mentor->id,
        ];
    }
}
