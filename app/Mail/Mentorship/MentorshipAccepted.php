<?php

namespace App\Mail\Mentorship;

use App\Models\Mentorship;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MentorshipAccepted extends Mailable
{
    use Queueable, SerializesModels;

    public Mentorship $mentorship;
    public User $mentor;
    public User $mentee;
    public string $bookingUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Mentorship $mentorship, User $mentor, User $mentee, string $bookingUrl)
    {
        $this->mentorship = $mentorship;
        $this->mentor = $mentor;
        $this->mentee = $mentee;
        $this->bookingUrl = $bookingUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->mentor->name} a accepté de devenir votre mentor ! - Brillio",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.mentorship.accepted',
            with: [
                'mentorship' => $this->mentorship,
                'mentor' => $this->mentor,
                'mentee' => $this->mentee,
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