<?php

namespace App\Console\Commands;

use App\Mail\Session\SessionReminder;
use App\Models\MentoringSession;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendSessionReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sessions:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send 24h reminder emails for upcoming mentoring sessions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->sendReminders('24h', now()->addHours(23), now()->addHours(25));
        $this->sendReminders('1h', now(), now()->addMinutes(90));

        return 0;
    }

    /**
     * Send reminders for a specific type and time window
     */
    protected function sendReminders(string $type, Carbon $start, Carbon $end)
    {
        $sentColumn = "reminder_{$type}_sent";

        $sessions = MentoringSession::where('status', 'confirmed')
            ->where($sentColumn, false)
            ->whereBetween('scheduled_at', [$start, $end])
            ->with(['mentor', 'mentees'])
            ->get();

        if ($sessions->isEmpty()) {
            return;
        }

        $emailsSent = 0;
        foreach ($sessions as $session) {
            try {
                // Send to mentor
                Mail::to($session->mentor->email)->queue(
                    new SessionReminder($session, $session->mentor, $session->mentees, $type)
                );
                $emailsSent++;

                // Send to each mentee
                foreach ($session->mentees as $mentee) {
                    $otherParticipants = $session->mentees->reject(fn ($m) => $m->id === $mentee->id);
                    Mail::to($mentee->email)->queue(
                        new SessionReminder($session, $mentee, $otherParticipants, $type)
                    );
                    $emailsSent++;
                }

                // Mark as sent - done after queueing to avoid re-runs if queue fails
                $session->update([$sentColumn => true]);
            } catch (\Exception $e) {
                $this->error("Failed to queue reminders for session {$session->id}: {$e->getMessage()}");
            }
        }

        $this->info("✅ Sent {$emailsSent} ({$type}) reminders for {$sessions->count()} sessions.");
    }
}
