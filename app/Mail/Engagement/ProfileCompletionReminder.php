<?php

namespace App\Mail\Engagement;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProfileCompletionReminder extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public array $missingSections;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, array $missingSections)
    {
        $this->user = $user;
        $this->missingSections = $missingSections;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🚀 Boostez votre visibilité sur Brillio en complétant votre profil',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.engagement.profile-completion',
            with: [
                'user' => $this->user,
                'missingSections' => $this->missingSections,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
