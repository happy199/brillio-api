<?php

namespace App\Console\Commands;

use App\Mail\Messages\UnreadMessagesReminder;
use App\Models\Mentorship;
use App\Services\EmailDeliveryService;
use Illuminate\Console\Command;

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

        $deliveryService = app(EmailDeliveryService::class);

        foreach ($mentorships as $mentorship) {
            // Messages non lus du mentor → destinataire = jeune
            $unreadForMentee = $mentorship->messages
                ->where('sender_id', $mentorship->mentor_id)
                ->whereNull('read_at')
                ->count();

            if ($unreadForMentee > 0 && ! $mentorship->mentee->is_archived && ! $mentorship->mentee->archived_at && ! $deliveryService->isExcludedEmail($mentorship->mentee->email)) {
                $conversationUrl = route('jeune.messages.show', $mentorship);

                $deliveryService->safeSend(
                    $mentorship->mentee,
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

            if ($unreadForMentor > 0 && ! $mentorship->mentor->is_archived && ! $mentorship->mentor->archived_at && ! $deliveryService->isExcludedEmail($mentorship->mentor->email)) {
                $conversationUrl = route('mentor.messages.show', $mentorship);

                $deliveryService->safeSend(
                    $mentorship->mentor,
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
