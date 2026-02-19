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
        // Get sessions scheduled for tomorrow (24h from now ± 1 hour window)
        $tomorrow = Carbon::now()->addDay();
        $startWindow = $tomorrow->copy()->subHour();
        $endWindow = $tomorrow->copy()->addHour();

        $sessions = MentoringSession::where('status', 'confirmed')
            ->whereBetween('scheduled_at', [$startWindow, $endWindow])
            ->with(['mentor', 'mentees'])
            ->get();

        $emailsSent = 0;

        foreach ($sessions as $session) {
            // Send to mentor
            $participants = $session->mentees;
            Mail::to($session->mentor->email)->send(
                new SessionReminder($session, $session->mentor, $participants)
            );
            $emailsSent++;

            // Send to each mentee
            foreach ($session->mentees as $mentee) {
                $otherParticipants = $session->mentees->reject(fn($m) => $m->id === $mentee->id);
                Mail::to($mentee->email)->send(
                    new SessionReminder($session, $mentee, $otherParticipants)
                );
                $emailsSent++;
            }
        }

        $this->info("✅ {$emailsSent} reminder emails sent for {$sessions->count()} sessions");
        return 0;
    }
}