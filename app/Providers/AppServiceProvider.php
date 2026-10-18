<?php

namespace App\Providers;

use App\Models\CronAutomation;
use App\Models\EmailLog;
use App\Models\ScheduledTaskLog;
use App\Services\EmailDeliveryService;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
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
        $this->configurePagination();
        $this->registerMailEvents();
        $this->registerCronEvents();
    }

    /**
     * Configuration globale de la pagination et conservation des paramètres URL.
     */
    private function configurePagination(): void
    {
        Paginator::defaultView('pagination.modern-pagination');

        $this->app->bind(LengthAwarePaginator::class, function ($app, $parameters) {
            $paginator = new LengthAwarePaginator(
                $parameters['items'] ?? [],
                $parameters['total'] ?? 0,
                $parameters['perPage'] ?? 15,
                $parameters['currentPage'] ?? null,
                $parameters['options'] ?? []
            );

            return $paginator->withQueryString();
        });

        $this->app->bind(Paginator::class, function ($app, $parameters) {
            $paginator = new Paginator(
                $parameters['items'] ?? [],
                $parameters['perPage'] ?? 15,
                $parameters['currentPage'] ?? null,
                $parameters['options'] ?? []
            );

            return $paginator->withQueryString();
        });
    }

    /**
     * Enregistre les écouteurs d'événements relatifs aux e-mails (Garde-fou et audit).
     */
    private function registerMailEvents(): void
    {
        // Garde-fou d'envoi : intercepte tout e-mail vers une adresse exclue avant transmission SMTP
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

        // Journalisation de l'e-mail envoyé
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
    }

    /**
     * Enregistre les écouteurs d'événements pour les tâches planifiées CRON.
     */
    private function registerCronEvents(): void
    {
        Event::listen(ScheduledTaskFinished::class, function (ScheduledTaskFinished $event) {
            $cmd = $event->task->command ?: $event->task->description;
            $isSuccess = $event->task->exitCode === 0;

            ScheduledTaskLog::create([
                'command' => $cmd,
                'status' => $isSuccess ? 'success' : 'failed',
                'duration' => $event->runtime ?? 0,
                'output' => null,
                'run_at' => now(),
            ]);

            $this->updateCronAutomationRecord($cmd, $isSuccess ? 'success' : 'failed', $event->runtime ?? 0);
        });

        Event::listen(ScheduledTaskFailed::class, function (ScheduledTaskFailed $event) {
            $cmd = $event->task->command ?: $event->task->description;
            $msg = substr($event->exception->getMessage(), 0, 10000);

            ScheduledTaskLog::create([
                'command' => $cmd,
                'status' => 'failed',
                'duration' => 0,
                'output' => $msg,
                'run_at' => now(),
            ]);

            $this->updateCronAutomationRecord($cmd, 'failed', 0, substr($msg, 0, 1000));
        });
    }

    /**
     * Met à jour les métriques de la tâche dans la table cron_automations si applicable.
     */
    private function updateCronAutomationRecord(?string $cmd, string $status, float $duration, ?string $output = null): void
    {
        if (! $cmd || ! Schema::hasTable('cron_automations')) {
            return;
        }

        $attributes = [
            'last_run_at' => now(),
            'last_run_status' => $status,
            'last_run_duration' => $duration,
        ];

        if ($output !== null) {
            $attributes['last_run_output'] = $output;
        }

        CronAutomation::where('action_class', 'like', "%{$cmd}%")
            ->orWhere('key', $cmd)
            ->update($attributes);
    }
}
