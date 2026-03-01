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

class MentorshipTerminatedByOrgToMentor extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Mentorship $mentorship;

    public User $mentor;

    public User $mentee;

    public string $organizationName;

    /**
     * Create a new message instance.
     */
    public function __construct(Mentorship $mentorship, User $mentor, User $mentee, string $organizationName)
    {
        $this->mentorship = $mentorship;
        $this->mentor = $mentor;
        $this->mentee = $mentee;
        $this->organizationName = $organizationName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Fin du mentorat avec {$this->mentee->name} - Brillio",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.mentorship.terminated-by-org-mentor',
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
