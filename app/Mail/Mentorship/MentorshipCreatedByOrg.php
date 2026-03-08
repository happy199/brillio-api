<?php

namespace App\Mail\Mentorship;

use App\Models\Mentorship;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MentorshipCreatedByOrg extends Mailable
{
    use Queueable, SerializesModels;

    public Mentorship $mentorship;

    public User $recipient;

    public string $otherPartyName;

    public string $organizationName;

    public string $actionUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Mentorship $mentorship, User $recipient, string $otherPartyName, string $organizationName, string $actionUrl)
    {
        $this->mentorship = $mentorship;
        $this->recipient = $recipient;
        $this->otherPartyName = $otherPartyName;
        $this->organizationName = $organizationName;
        $this->actionUrl = $actionUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nouvelle relation de mentorat avec {$this->otherPartyName} - {$this->organizationName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.mentorship.created_by_org',
            with: [
                'mentorship' => $this->mentorship,
                'recipient' => $this->recipient,
                'otherPartyName' => $this->otherPartyName,
                'organizationName' => $this->organizationName,
                'actionUrl' => $this->actionUrl,
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
