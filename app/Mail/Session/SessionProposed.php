<?php

namespace App\Mail\Session;

use App\Models\MentoringSession;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SessionProposed extends Mailable
{
    use Queueable, SerializesModels;

    public MentoringSession $session;

    public User $mentor;

    public User $mentee;

    public int $menteeCredits;

    public string $acceptUrl;

    public string $refuseUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(
        MentoringSession $session,
        User $mentor,
        User $mentee,
        int $menteeCredits,
        string $acceptUrl,
        string $refuseUrl
    ) {
        $this->session = $session;
        $this->mentor = $mentor;
        $this->mentee = $mentee;
        $this->menteeCredits = $menteeCredits;
        $this->acceptUrl = $acceptUrl;
        $this->refuseUrl = $refuseUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nouvelle session proposée par {$this->mentor->name} - Brillio",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.session.proposed',
            with: [
                'session' => $this->session,
                'mentor' => $this->mentor,
                'mentee' => $this->mentee,
                'menteeCredits' => $this->menteeCredits,
                'acceptUrl' => $this->acceptUrl,
                'refuseUrl' => $this->refuseUrl,
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
