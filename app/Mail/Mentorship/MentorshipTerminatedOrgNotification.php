<?php

namespace App\Mail\Mentorship;

use App\Models\Mentorship;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MentorshipTerminatedOrgNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Mentorship $mentorship;

    public string $actorName;

    public string $reason;

    public string $jeuneName;

    public string $mentorName;

    /**
     * Create a new message instance.
     */
    public function __construct(Mentorship $mentorship, string $actorName, string $reason, string $jeuneName, string $mentorName)
    {
        $this->mentorship = $mentorship;
        $this->actorName = $actorName;
        $this->reason = $reason;
        $this->jeuneName = $jeuneName;
        $this->mentorName = $mentorName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Rupture de relation de mentorat : {$this->jeuneName} / {$this->mentorName} - Brillio",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.mentorship.terminated-org-notification',
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
