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

class MentorshipTerminatedByOrgToMentee extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Mentorship $mentorship;

    public User $mentee;

    public User $mentor;

    public string $organizationName;

    /**
     * Create a new message instance.
     */
    public function __construct(Mentorship $mentorship, User $mentee, User $mentor, string $organizationName)
    {
        $this->mentorship = $mentorship;
        $this->mentee = $mentee;
        $this->mentor = $mentor;
        $this->organizationName = $organizationName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Information concernant votre mentorat avec {$this->mentor->name} - Brillio",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.mentorship.terminated-by-org-mentee',
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
