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

class SessionConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public MentoringSession $session;
    public User $recipient;
    public Collection $participants;
    public string $calendarUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(
        MentoringSession $session,
        User $recipient,
        Collection $participants,
        string $calendarUrl
    ) {
        $this->session = $session;
        $this->recipient = $recipient;
        $this->participants = $participants;
        $this->calendarUrl = $calendarUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $date = $this->session->scheduled_at->translatedFormat('j F Y');
        return new Envelope(
            subject: "Session confirmée - {$date} - Brillio",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.session.confirmed',
            with: [
                'session' => $this->session,
                'recipient' => $this->recipient,
                'participants' => $this->participants,
                'calendarUrl' => $this->calendarUrl,
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