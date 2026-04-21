<?php

namespace App\Mail\Session;

use App\Models\MentoringSession;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GuestInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public MentoringSession $session;
    public User $recipient;
    public string $magicLink;

    /**
     * Create a new message instance.
     */
    public function __construct(MentoringSession $session, User $recipient)
    {
        $this->session = $session;
        $this->recipient = $recipient;
        $this->magicLink = route('guest.sessions.confirm', [
            'session' => $session->id,
            'token' => $session->guest_token
        ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $orgName = $this->session->organization->name ?? 'une organisation';
        
        return new Envelope(
            subject: "Invitation à animer une séance - {$orgName} - Brillio",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.session.guest_invitation',
            with: [
                'session' => $this->session,
                'recipient' => $this->recipient,
                'magicLink' => $this->magicLink,
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
