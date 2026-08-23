<?php

namespace App\Providers;

use App\Models\EmailLog;
use App\Models\ScheduledTaskLog;
use App\Services\EmailDeliveryService;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
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
        // Configurer la pagination par défaut
        Paginator::defaultView('pagination.modern-pagination');

        // === AUDIT LOGS & MAIL SUPPRESSION EVENTS ===

        // 0. Global Suppression Guard (MessageSending) - Annule instantanément tout envoi vers une adresse exclue
        Event::listen(MessageSending::class, function (MessageSending $event) {
            $message = $event->message;
            $recipients = collect($message->getTo())->map(fn ($address) => strtolower(trim($address->getAddress())));

            $deliveryService = app(EmailDeliveryService::class);

            foreach ($recipients as $email) {
                if ($deliveryService->isExcludedEmail($email)) {
                    Log::info("Envoi d'e-mail intercepté et annulé (adresse dans la liste d'exclusion)", [
                        'email' => $email,
                        'subject' => $message->getSubject(),
                    ]);

                    return false;
                }
            }
        });

        // 1. Emails (MessageSent)
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
