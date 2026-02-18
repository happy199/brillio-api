<?php

namespace App\Mail\Mentorship;

use App\Models\Mentorship;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MentorshipRequested extends Mailable
{
    use Queueable, SerializesModels;

    public Mentorship $mentorship;
    public User $mentor;
    public User $mentee;
    public string $acceptUrl;
    public string $refuseUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Mentorship $mentorship, User $mentor, User $mentee, string $acceptUrl, string $refuseUrl)
    {
        $this->mentorship = $mentorship;
        $this->mentor = $mentor;
        $this->mentee = $mentee;
        $this->acceptUrl = $acceptUrl;
        $this->refuseUrl = $refuseUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nouvelle demande de mentorat de {$this->mentee->name} - Brillio",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.mentorship.requested',
            with: [
                'mentorship' => $this->mentorship,
                'mentor' => $this->mentor,
                'mentee' => $this->mentee,
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