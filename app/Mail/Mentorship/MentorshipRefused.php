<?php

namespace App\Mail\Mentorship;

use App\Models\Mentorship;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MentorshipRefused extends Mailable
{
    use Queueable, SerializesModels;

    public Mentorship $mentorship;

    public User $mentor;

    public User $mentee;

    public ?string $reason;

    /**
     * Create a new message instance.
     */
    public function __construct(Mentorship $mentorship, User $mentor, User $mentee, ?string $reason = null)
    {
        $this->mentorship = $mentorship;
        $this->mentor = $mentor;
        $this->mentee = $mentee;
        $this->reason = $reason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Mise à jour concernant votre demande de mentorat - Brillio',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.mentorship.refused',
            with: [
                'mentorship' => $this->mentorship,
                'mentor' => $this->mentor,
                'mentee' => $this->mentee,
                'reason' => $this->reason,
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
