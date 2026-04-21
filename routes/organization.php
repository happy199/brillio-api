<?php

use App\Http\Controllers\Organization\Auth\RegisterController;
use App\Http\Controllers\Organization\ConversationController;
use App\Http\Controllers\Organization\DashboardController;
use App\Http\Controllers\Organization\ExportController;
use App\Http\Controllers\Organization\InvitationController;
use App\Http\Controllers\Organization\SponsoredUsersController;
use Illuminate\Support\Facades\Route;

/*
 |--------------------------------------------------------------------------
 | Organization Routes
 |--------------------------------------------------------------------------
 |
 | Routes for organization partnership system
 | Organizations can register, login, invite users, and monitor progress
 |
 */

// Public routes (guest only)
Route::middleware('guest')->group(function () {
    // Registration
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');

    // Login
    Route::get('/login', [RegisterController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [RegisterController::class, 'login'])->name('login.submit');
});

// Email Verification (Authenticated but not necessarily verified)
Route::middleware('auth')->prefix('email')->name('verification.')->group(function () {
    Route::get('/verify', [App\Http\Controllers\Organization\Auth\VerifyEmailController::class, 'notice'])
        ->name('notice');

    Route::get('/verify/{id}/{hash}', [App\Http\Controllers\Organization\Auth\VerifyEmailController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verify');

    Route::post('/verification-notification', [App\Http\Controllers\Organization\Auth\VerifyEmailController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('resend');
});

// Authenticated Organization Routes
Route::middleware('auth')->group(function () {

    // Protected routes (authenticated + verified + active organization)
    Route::middleware(['organization', 'organization_active'])->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Credit Distribution
        Route::post('/credits/distribute', [\App\Http\Controllers\Organization\CreditDistributionController::class, 'distribute'])
            ->middleware('organization_role:admin')
            ->name('credits.distribute');

        // Profile
        Route::get('/profile', [\App\Http\Controllers\Organization\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [\App\Http\Controllers\Organization\ProfileController::class, 'update'])
            ->middleware('organization_role:admin')
            ->name('profile.update');
        Route::get('/profile/check-domain', [\App\Http\Controllers\Organization\ProfileController::class, 'checkDomainAvailability'])
            ->name('profile.check-domain');
        Route::get('/profile/verify-dns', [\App\Http\Controllers\Organization\ProfileController::class, 'verifyDomainDNS'])
            ->name('profile.verify-dns');
        Route::post('/profile/activate-domain', [\App\Http\Controllers\Organization\ProfileController::class, 'activateCustomDomain'])
            ->middleware('organization_role:admin')
            ->name('profile.activate-domain');

        // Invitations
        Route::get('/invitations', [InvitationController::class, 'index'])->name('invitations.index');
        Route::get('/invitations/create', [InvitationController::class, 'create'])
            ->middleware('organization_role:admin')
            ->name('invitations.create');
        Route::post('/invitations', [InvitationController::class, 'store'])
            ->middleware('organization_role:admin')
            ->name('invitations.store');
        Route::delete('/invitations/{invitation}', [InvitationController::class, 'destroy'])
            ->middleware('organization_role:admin')
            ->name('invitations.destroy');

        // Pro Features (Jeunes, Mentorships, Sessions)
        Route::middleware('organization_subscription:pro')->group(function () {
            // Sponsored Users
            Route::get('/users', [\App\Http\Controllers\Organization\SponsoredUsersController::class, 'index'])->name('users.index');
            Route::get('/users/{user}', [\App\Http\Controllers\Organization\SponsoredUsersController::class, 'show'])->name('users.show');

            // Mentors
            Route::get('/mentors', [\App\Http\Controllers\Organization\MentorsController::class, 'index'])->name('mentors.index');
            Route::get('/mentors/{mentor:public_slug}/export-pdf', [\App\Http\Controllers\Organization\MentorsController::class, 'exportPdf'])->name('mentors.export-pdf');
            Route::get('/mentors/{mentor:public_slug}/export-csv', [\App\Http\Controllers\Organization\MentorsController::class, 'exportCsv'])->name('mentors.export-csv');
            Route::get('/mentors/{mentor:public_slug}', [\App\Http\Controllers\Organization\MentorsController::class, 'show'])->name('mentors.show');

            // Mentorships
            Route::get('/mentorships', [\App\Http\Controllers\Organization\MentorshipController::class, 'index'])->name('mentorships.index');
            Route::get('/mentorships/create', [\App\Http\Controllers\Organization\MentorshipController::class, 'create'])->name('mentorships.create');
            Route::post('/mentorships', [\App\Http\Controllers\Organization\MentorshipController::class, 'store'])->name('mentorships.store');
            Route::get('/mentorships/{mentorship}', [\App\Http\Controllers\Organization\MentorshipController::class, 'show'])->name('mentorships.show');
            Route::post('/mentorships/{mentorship}/validate', [\App\Http\Controllers\Organization\MentorshipController::class, 'validateMentorship'])->name('mentorships.validate');

            // Conversation monitoring (Enterprise only)
            Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations.index');
            Route::get('/conversations/{mentorship}', [ConversationController::class, 'show'])->name('conversations.show');
            Route::get('/conversations/download/{message}', [ConversationController::class, 'download'])->name('conversations.download');
            Route::post('/mentorships/{mentorship}/terminate', [\App\Http\Controllers\Organization\MentorshipController::class, 'terminate'])->name('mentorships.terminate');

            // Sessions & Calendar
            Route::get('/sessions', [\App\Http\Controllers\Organization\SessionController::class, 'index'])->name('sessions.index');
            Route::get('/sessions/calendar', [\App\Http\Controllers\Organization\SessionController::class, 'calendar'])->name('sessions.calendar');
            Route::get('/sessions/events', [\App\Http\Controllers\Organization\SessionController::class, 'events'])->name('sessions.events');
            
            // Wildecard Session route moved to ensure specific /create route (below) takes precedence
            Route::get('/sessions/{session}/transcription', [\App\Http\Controllers\Organization\SessionController::class, 'downloadTranscription'])->name('sessions.download-transcription');

            // Individual User Export
            Route::get('/users/{user}/export', [SponsoredUsersController::class, 'export'])->name('users.export');
        }
        );

        // Enterprise Specific Session Scheduling (Prioritized over Wildcards)
        Route::middleware('organization_subscription:enterprise')->group(function() {
            Route::prefix('sessions')->name('sessions.')->group(function () {
                Route::get('/create', [\App\Http\Controllers\Organization\ScheduledSessionController::class, 'create'])->name('create');
                Route::post('/', [\App\Http\Controllers\Organization\ScheduledSessionController::class, 'store'])->name('store');
            });
        });

        // Common Wildcard Session Access (Pro & Enterprise)
        Route::middleware('organization_subscription:pro')->group(function() {
            Route::get('/sessions/{session}', [\App\Http\Controllers\Organization\SessionController::class, 'show'])->name('sessions.show');
        });

        // Exports
        Route::get('/exports', [ExportController::class, 'index'])->name('exports.index');

        Route::middleware('organization_subscription:enterprise')
            ->get('/exports/generate', [ExportController::class, 'generate'])
            ->name('exports.generate');

        // Subscriptions
        Route::get('/subscriptions', [\App\Http\Controllers\Organization\SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::post('/subscriptions/downgrade', [\App\Http\Controllers\Organization\SubscriptionController::class, 'downgrade'])
            ->middleware('organization_role:admin')
            ->name('subscriptions.downgrade');
        Route::post('/subscriptions/{plan}', [\App\Http\Controllers\Organization\SubscriptionController::class, 'subscribe'])
            ->middleware('organization_role:admin')
            ->name('subscriptions.subscribe');

        // Payments
        Route::get('/payment/callback', [\App\Http\Controllers\Organization\PaymentController::class, 'callback'])->name('payment.callback');

        // Wallet
        Route::middleware('organization_role:admin')->group(function () {
            Route::get('/wallet', [\App\Http\Controllers\Organization\WalletController::class, 'index'])->name('wallet.index');
            Route::get('/wallet/history', [\App\Http\Controllers\Organization\WalletController::class, 'history'])->name('wallet.history');
            Route::get('/wallet/export-pdf', [\App\Http\Controllers\Organization\WalletController::class, 'exportPdf'])->name('wallet.export-pdf');
            Route::get('/wallet/export-csv', [\App\Http\Controllers\Organization\WalletController::class, 'exportCsv'])->name('wallet.export-csv');
            Route::post('/wallet/purchase', [\App\Http\Controllers\Organization\WalletController::class, 'purchase'])->name('wallet.purchase');
        }
        );

        // Resources Library
        Route::prefix('resources')->name('resources.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Organization\ResourceController::class, 'index'])->name('index');
            Route::get('/{resource:slug}', [\App\Http\Controllers\Organization\ResourceController::class, 'show'])->name('show');
            Route::post('/{resource:slug}/gift', [\App\Http\Controllers\Organization\ResourceController::class, 'gift'])->name('gift');
        }
        );

        // Team Management (Enterprise only)
        Route::middleware('organization_subscription:enterprise')->group(function () {
            Route::prefix('team')->name('team.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Organization\TeamController::class, 'index'])->name('index');
                Route::get('/create', [\App\Http\Controllers\Organization\TeamController::class, 'create'])->name('create');
                Route::post('/', [\App\Http\Controllers\Organization\TeamController::class, 'store'])->name('store');
                Route::delete('/{user}', [\App\Http\Controllers\Organization\TeamController::class, 'destroy'])->name('destroy');
            });

            // Guest Trainers Management
            Route::prefix('guests')->name('guests.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Organization\GuestController::class, 'index'])->name('index');
                Route::get('/create', [\App\Http\Controllers\Organization\GuestController::class, 'create'])->name('create');
                Route::post('/', [\App\Http\Controllers\Organization\GuestController::class, 'store'])->name('store');
                Route::get('/{guest:id}', [\App\Http\Controllers\Organization\GuestController::class, 'show'])->name('show');
                Route::get('/{guest:id}/edit', [\App\Http\Controllers\Organization\GuestController::class, 'edit'])->name('edit');
                Route::put('/{guest:id}', [\App\Http\Controllers\Organization\GuestController::class, 'update'])->name('update');
                Route::delete('/{guest:id}', [\App\Http\Controllers\Organization\GuestController::class, 'destroy'])->name('destroy');
            });
        });
    }
    );

    // Logout (Accessible even if unverified/inactive)
    Route::post('/logout', [RegisterController::class, 'logout'])->name('logout');
});
