<?php

namespace App\Mail\Session;

use App\Models\MentoringSession;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SessionRefused extends Mailable
{
    use Queueable, SerializesModels;

    public MentoringSession $session;
    public User $mentor;
    public User $mentee;
    public ?string $reason;

    /**
     * Create a new message instance.
     */
    public function __construct(MentoringSession $session, User $mentor, User $mentee, ?string $reason = null)
    {
        $this->session = $session;
        $this->mentor = $mentor;
        $this->mentee = $mentee;
        $this->reason = $reason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Session de mentorat refusée - Brillio",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.session.refused',
            with: [
                'session' => $this->session,
                'mentor' => $this->mentor,
                'mentee' => $this->mentee,
                'reason' => $this->reason,
            ],
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