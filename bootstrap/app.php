<?php

use App\Http\Middleware\CheckOrganizationSubscription;
use App\Http\Middleware\CheckUserType;
use App\Http\Middleware\EnsureJeuneProfilePublished;
use App\Http\Middleware\EnsureMentorProfilePublished;
use App\Http\Middleware\EnsureOrganizationIsActive;
use App\Http\Middleware\EnsureOrganizationRole;
use App\Http\Middleware\EnsureUserIsOrganization;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsCoach;
use App\Http\Middleware\ResolveOrganizationByDomain;
use App\Http\Middleware\RestrictSwaggerAccess;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Http\Middleware\UpdateLastLogin;
use App\Http\Middleware\VerifyAdminTwoFactor;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
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
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            UpdateLastLogin::class,
            ResolveOrganizationByDomain::class,
            SecurityHeadersMiddleware::class,
        ]);

        // Alias pour les middlewares personnalisés
        $middleware->alias([
            'is_admin' => IsAdmin::class,
            'user_type' => CheckUserType::class,
            'organization' => EnsureUserIsOrganization::class,
            'mentor_published' => EnsureMentorProfilePublished::class,
            'organization_active' => EnsureOrganizationIsActive::class,
            'organization_subscription' => CheckOrganizationSubscription::class,
            'organization_role' => EnsureOrganizationRole::class,
            'jeune_published' => EnsureJeuneProfilePublished::class,
            'is_coach' => IsCoach::class,
            'admin_2fa' => VerifyAdminTwoFactor::class,
            'swagger_secure' => RestrictSwaggerAccess::class,
        ]);

        // Redirection pour les non-authentifiés
        $middleware->redirectGuestsTo('/rejoindre');

        // Rate limiting pour l'API
        $middleware->throttleApi('60,1');

        // Configuration CORS
        $middleware->api(prepend: [
            HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Gestion personnalisée des exceptions API
        $exceptions->shouldRenderJsonWhen(function ($request, Throwable $e) {
            return $request->expectsJson() || $request->is('api/*');
        });

        Integration::handles($exceptions);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('campaigns:process-recurring')->everyMinute();
    })
    ->create();
