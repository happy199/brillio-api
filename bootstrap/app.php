<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->prefix('organization')
                ->name('organization.')
                ->group(base_path('routes/organization.php'));

            // === AUDIT LOGS EVENTS ===
            // 1. Emails
            \Illuminate\Support\Facades\Event::listen(\Illuminate\Mail\Events\MessageSent::class, function (\Illuminate\Mail\Events\MessageSent $event) {
                $message = $event->message;
                $to = collect($message->getTo())->map(fn ($address) => $address->getAddress())->implode(', ');

                \App\Models\EmailLog::create([
                    'to' => $to,
                    'subject' => $message->getSubject(),
                    'body' => $message->getHtmlBody() ?? $message->getTextBody(),
                    'sent_at' => now(),
                ]);
            });

            // 2. CRONs (Success)
            \Illuminate\Support\Facades\Event::listen(\Illuminate\Console\Events\ScheduledTaskFinished::class, function (\Illuminate\Console\Events\ScheduledTaskFinished $event) {
                \App\Models\ScheduledTaskLog::create([
                    'command' => $event->task->command ?: $event->task->description,
                    'status' => $event->task->exitCode === 0 ? 'success' : 'failed',
                    'duration' => $event->runtime ?? 0,
                    'output' => null, // Limited access directly from listener
                    'run_at' => now(),
                ]);
            });

            // 3. CRONs (Failed)
            \Illuminate\Support\Facades\Event::listen(\Illuminate\Console\Events\ScheduledTaskFailed::class, function (\Illuminate\Console\Events\ScheduledTaskFailed $event) {
                \App\Models\ScheduledTaskLog::create([
                    'command' => $event->task->command ?: $event->task->description,
                    'status' => 'failed',
                    'duration' => 0,
                    'output' => substr($event->exception->getMessage(), 0, 10000),
                    'run_at' => now(),
                ]);
            });
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\UpdateLastLogin::class,
            \App\Http\Middleware\ResolveOrganizationByDomain::class,
        ]);

        // Alias pour les middlewares personnalisés
        $middleware->alias([
            'is_admin' => \App\Http\Middleware\IsAdmin::class,
            'user_type' => \App\Http\Middleware\CheckUserType::class,
            'organization' => \App\Http\Middleware\EnsureUserIsOrganization::class,
            'mentor_published' => \App\Http\Middleware\EnsureMentorProfilePublished::class,
            'organization_active' => \App\Http\Middleware\EnsureOrganizationIsActive::class,
            'organization_subscription' => \App\Http\Middleware\CheckOrganizationSubscription::class,
            'organization_role' => \App\Http\Middleware\EnsureOrganizationRole::class,
            'jeune_published' => \App\Http\Middleware\EnsureJeuneProfilePublished::class,
            'is_coach' => \App\Http\Middleware\IsCoach::class,
        ]);

        // Redirection pour les non-authentifiés
        $middleware->redirectGuestsTo('/rejoindre');

        // Rate limiting pour l'API
        $middleware->throttleApi('60,1');

        // Configuration CORS
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Gestion personnalisée des exceptions API
        $exceptions->shouldRenderJsonWhen(function ($request, Throwable $e) {
            return $request->expectsJson() || $request->is('api/*');
        });

        Integration::handles($exceptions);
    })->create();
