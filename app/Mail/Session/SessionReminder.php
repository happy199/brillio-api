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

class SessionReminder extends Mailable
{
    use Queueable, SerializesModels;

    public MentoringSession $session;
    public User $recipient;
    public Collection $participants;

    /**
     * Create a new message instance.
     */
    public function __construct(
        MentoringSession $session,
        User $recipient,
        Collection $participants
    ) {
        $this->session = $session;
        $this->recipient = $recipient;
        $this->participants = $participants;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $time = $this->session->scheduled_at->format('H:i');
        return new Envelope(
            subject: "⏰ Rappel : Session demain à {$time} - Brillio",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.session.reminder',
            with: [
                'session' => $this->session,
                'recipient' => $this->recipient,
                'participants' => $this->participants,
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