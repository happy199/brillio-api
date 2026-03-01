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

class MentorshipTerminatedConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Mentorship $mentorship;

    public User $actor;

    public string $otherPartyName;

    public string $reason;

    /**
     * Create a new message instance.
     */
    public function __construct(Mentorship $mentorship, User $actor, string $otherPartyName, string $reason)
    {
        $this->mentorship = $mentorship;
        $this->actor = $actor;
        $this->otherPartyName = $otherPartyName;
        $this->reason = $reason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Confirmation de la fin de votre relation de mentorat avec {$this->otherPartyName} - Brillio",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.mentorship.terminated-confirmation',
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
