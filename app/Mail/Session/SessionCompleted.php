<?php

namespace App\Mail\Session;

use App\Models\MentoringSession;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SessionCompleted extends Mailable
{
    use Queueable, SerializesModels;

    public MentoringSession $session;
    public User $recipient;
    public Collection $participants;
    public string $sessionUrl;
    public string $bookingUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(
        MentoringSession $session,
        User $recipient,
        Collection $participants,
        string $sessionUrl,
        string $bookingUrl = ''
    ) {
        $this->session = $session;
        $this->recipient = $recipient;
        $this->participants = $participants;
        $this->sessionUrl = $sessionUrl;
        $this->bookingUrl = $bookingUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Session terminée - Merci ! - Brillio",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.session.completed',
            with: [
                'session' => $this->session,
                'recipient' => $this->recipient,
                'participants' => $this->participants,
                'sessionUrl' => $this->sessionUrl,
                'bookingUrl' => $this->bookingUrl,
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