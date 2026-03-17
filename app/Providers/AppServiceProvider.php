<?php

namespace App\Providers;

use App\Models\EmailLog;
use App\Models\ScheduledTaskLog;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // === AUDIT LOGS EVENTS ===

        // 1. Emails
        Event::listen(MessageSent::class, function (MessageSent $event) {
            $message = $event->message;
            $to = collect($message->getTo())->map(fn ($address) => $address->getAddress())->implode(', ');

            EmailLog::create([
                'to' => $to,
                'subject' => $message->getSubject(),
                'body' => $message->getHtmlBody() ?? $message->getTextBody(),
                'sent_at' => now(),
            ]);
        });

        // 2. CRONs (Success)
        Event::listen(ScheduledTaskFinished::class, function (ScheduledTaskFinished $event) {
            ScheduledTaskLog::create([
                'command' => $event->task->command ?: $event->task->description,
                'status' => $event->task->exitCode === 0 ? 'success' : 'failed',
                'duration' => $event->runtime ?? 0,
                'output' => null,
                'run_at' => now(),
            ]);
        });

        // 3. CRONs (Failed)
        Event::listen(ScheduledTaskFailed::class, function (ScheduledTaskFailed $event) {
            ScheduledTaskLog::create([
                'command' => $event->task->command ?: $event->task->description,
                'status' => 'failed',
                'duration' => 0,
                'output' => substr($event->exception->getMessage(), 0, 10000),
                'run_at' => now(),
            ]);
        });
    }
}
