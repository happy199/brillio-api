<?php

namespace App\Console\Commands;

use App\Mail\Messages\UnreadMessagesReminder;
use App\Models\Mentorship;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendUnreadMessagesReminders extends Command
{
    protected $signature = 'messages:send-unread-reminders';

    protected $description = 'Envoie un rappel email aux utilisateurs ayant des messages non lus sans réponse';

    public function handle(): int
    {
        // Récupérer tous les mentorships acceptés avec des messages non lus
        $mentorships = Mentorship::where('status', 'accepted')
            ->whereHas('messages', fn ($q) => $q->whereNull('read_at'))
            ->with(['mentor', 'mentee', 'messages'])
            ->get();

        $sent = 0;

        foreach ($mentorships as $mentorship) {
            // Messages non lus du mentor → destinataire = jeune
            $unreadForMentee = $mentorship->messages
                ->where('sender_id', $mentorship->mentor_id)
                ->whereNull('read_at')
                ->count();

            if ($unreadForMentee > 0) {
                $conversationUrl = route('jeune.messages.show', $mentorship);

                Mail::to($mentorship->mentee->email)->send(
                    new UnreadMessagesReminder(
                        recipient: $mentorship->mentee,
                        senderName: $mentorship->mentor->name,
                        messageCount: $unreadForMentee,
                        conversationUrl: $conversationUrl,
                        recipientRole: 'jeune',
                    )
                );

                $sent++;
            }

            // Messages non lus du jeune → destinataire = mentor
            $unreadForMentor = $mentorship->messages
                ->where('sender_id', $mentorship->mentee_id)
                ->whereNull('read_at')
                ->count();

            if ($unreadForMentor > 0) {
                $conversationUrl = route('mentor.messages.show', $mentorship);

                Mail::to($mentorship->mentor->email)->send(
                    new UnreadMessagesReminder(
                        recipient: $mentorship->mentor,
                        senderName: $mentorship->mentee->name,
                        messageCount: $unreadForMentor,
                        conversationUrl: $conversationUrl,
                        recipientRole: 'mentor',
                    )
                );

                $sent++;
            }
        }

        $this->info("✅ {$sent} rappel(s) envoyé(s).");

        return self::SUCCESS;
    }
}
