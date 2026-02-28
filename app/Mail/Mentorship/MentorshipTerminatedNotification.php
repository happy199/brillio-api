<?php

namespace App\Mail\Mentorship;

use App\Models\Mentorship;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MentorshipTerminatedNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Mentorship $mentorship;

    public User $recipient;

    public string $actorName;

    public string $reason;

    /**
     * Create a new message instance.
     */
    public function __construct(Mentorship $mentorship, User $recipient, string $actorName, string $reason)
    {
        $this->mentorship = $mentorship;
        $this->recipient = $recipient;
        $this->actorName = $actorName;
        $this->reason = $reason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Information : Fin de votre relation de mentorat par {$this->actorName} - Brillio",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.mentorship.terminated-notification',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
