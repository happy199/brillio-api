<?php

namespace App\Mail\Engagement;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewMentorsWeekly extends Mailable
{
    use Queueable, SerializesModels;

    public User $jeune;
    public Collection $mentors;

    /**
     * Create a new message instance.
     */
    public function __construct(User $jeune, Collection $mentors)
    {
        $this->jeune = $jeune;
        $this->mentors = $mentors;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $count = $this->mentors->count();
        return new Envelope(
            subject: "🚀 {$count} nouveaux mentors ont rejoint Brillio cette semaine !",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.engagement.new-mentors',
            with: [
                'jeune' => $this->jeune,
                'mentors' => $this->mentors,
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