<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->prefix('organization')
                ->name('organization.')
                ->group(base_path('routes/organization.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Alias pour les middlewares personnalisés
        $middleware->alias([
            'is_admin' => \App\Http\Middleware\IsAdmin::class,
            'user_type' => \App\Http\Middleware\CheckUserType::class,
            'organization' => \App\Http\Middleware\EnsureUserIsOrganization::class,
            'mentor_published' => \App\Http\Middleware\EnsureMentorProfilePublished::class,
            'organization_active' => \App\Http\Middleware\EnsureOrganizationIsActive::class,
            'organization_subscription' => \App\Http\Middleware\CheckOrganizationSubscription::class,
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
    })->create();