<?php

use App\Http\Controllers\Organization\AdvertisementController;
use App\Http\Controllers\Organization\Auth\RegisterController;
use App\Http\Controllers\Organization\Auth\VerifyEmailController;
use App\Http\Controllers\Organization\ConversationController;
use App\Http\Controllers\Organization\CreditDistributionController;
use App\Http\Controllers\Organization\DashboardController;
use App\Http\Controllers\Organization\EstablishmentController;
use App\Http\Controllers\Organization\ExportController;
use App\Http\Controllers\Organization\GuestController;
use App\Http\Controllers\Organization\InvitationController;
use App\Http\Controllers\Organization\MentorsController;
use App\Http\Controllers\Organization\MentorshipController;
use App\Http\Controllers\Organization\PaymentController;
use App\Http\Controllers\Organization\ProfileController;
use App\Http\Controllers\Organization\PromotionController;
use App\Http\Controllers\Organization\ResourceController;
use App\Http\Controllers\Organization\ScheduledSessionController;
use App\Http\Controllers\Organization\SessionController;
use App\Http\Controllers\Organization\SponsoredUsersController;
use App\Http\Controllers\Organization\SubscriptionController;
use App\Http\Controllers\Organization\TeamController;
use App\Http\Controllers\Organization\WalletController;
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
    Route::get('/verify', [VerifyEmailController::class, 'notice'])
        ->name('notice');

    Route::get('/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verify');

    Route::post('/verification-notification', [VerifyEmailController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('resend');
});

// Authenticated Organization Routes
Route::middleware('auth')->group(function () {

    // Protected routes (authenticated + verified + active organization)
    Route::middleware(['organization', 'organization_active'])->group(
        function () {
            // Dashboard
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

            // Credit Distribution
            Route::post('/credits/distribute', [CreditDistributionController::class, 'distribute'])
                ->middleware('organization_role:admin')
                ->name('credits.distribute');

            // Profile
            Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::put('/profile', [ProfileController::class, 'update'])
                ->middleware('organization_role:admin')
                ->name('profile.update');
            Route::get('/profile/check-domain', [ProfileController::class, 'checkDomainAvailability'])
                ->name('profile.check-domain');
            Route::get('/profile/verify-dns', [ProfileController::class, 'verifyDomainDNS'])
                ->name('profile.verify-dns');
            Route::post('/profile/activate-domain', [ProfileController::class, 'activateCustomDomain'])
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
            Route::middleware('organization_subscription:pro')->group(
                function () {
                    // Sponsored Users
                    Route::get('/users', [SponsoredUsersController::class, 'index'])->name('users.index');
                    Route::get('/users/{user}', [SponsoredUsersController::class, 'show'])->name('users.show');

                    // Mentors
                    Route::get('/mentors', [MentorsController::class, 'index'])->name('mentors.index');
                    Route::get('/mentors/{mentor:public_slug}/export-pdf', [MentorsController::class, 'exportPdf'])->name('mentors.export-pdf');
                    Route::get('/mentors/{mentor:public_slug}/export-csv', [MentorsController::class, 'exportCsv'])->name('mentors.export-csv');
                    Route::get('/mentors/{mentor:public_slug}', [MentorsController::class, 'show'])->name('mentors.show');

                    // Mentorships
                    Route::get('/mentorships', [MentorshipController::class, 'index'])->name('mentorships.index');
                    Route::get('/mentorships/create', [MentorshipController::class, 'create'])->name('mentorships.create');
                    Route::post('/mentorships', [MentorshipController::class, 'store'])->name('mentorships.store');
                    Route::get('/mentorships/{mentorship}', [MentorshipController::class, 'show'])->name('mentorships.show');
                    Route::post('/mentorships/{mentorship}/validate', [MentorshipController::class, 'validateMentorship'])->name('mentorships.validate');

                    // Conversation monitoring (Enterprise only)
                    Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations.index');
                    Route::get('/conversations/{mentorship}', [ConversationController::class, 'show'])->name('conversations.show');
                    Route::get('/conversations/download/{message}', [ConversationController::class, 'download'])->name('conversations.download');
                    Route::post('/mentorships/{mentorship}/terminate', [MentorshipController::class, 'terminate'])->name('mentorships.terminate');

                    // Sessions & Calendar
                    Route::get('/sessions', [SessionController::class, 'index'])->name('sessions.index');
                    Route::get('/sessions/calendar', [SessionController::class, 'calendar'])->name('sessions.calendar');
                    Route::get('/sessions/events', [SessionController::class, 'events'])->name('sessions.events');

                    // Wildecard Session route moved to ensure specific /create route (below) takes precedence
                    Route::get('/sessions/{session}/transcription', [SessionController::class, 'downloadTranscription'])->name('sessions.download-transcription');

                    // Individual User Export
                    Route::get('/users/{user}/export', [SponsoredUsersController::class, 'export'])->name('users.export');
                }
            );

            // Enterprise Specific Session Scheduling (Prioritized over Wildcards)
            Route::middleware('organization_subscription:enterprise')->group(function () {
                Route::prefix('sessions')->name('sessions.')->group(function () {
                    Route::get('/create', [ScheduledSessionController::class, 'create'])->name('create');
                    Route::post('/', [ScheduledSessionController::class, 'store'])->name('store');
                    Route::get('/{session}/edit', [ScheduledSessionController::class, 'edit'])->name('edit');
                    Route::put('/{session}', [ScheduledSessionController::class, 'update'])->name('update');
                    Route::post('/{session}/cancel', [ScheduledSessionController::class, 'cancel'])->name('cancel');
                    Route::post('/{session}/prefill-report', [SessionController::class, 'prefillReport'])->name('prefill-report');
                    Route::put('/{session}/report', [SessionController::class, 'updateReport'])->name('report.update');
                });
            });

            // Common Wildcard Session Access (Pro & Enterprise)
            Route::middleware('organization_subscription:pro')->group(function () {
                Route::get('/sessions/{session}', [SessionController::class, 'show'])->name('sessions.show');
            });

            // Exports
            Route::get('/exports', [ExportController::class, 'index'])->name('exports.index');

            Route::middleware('organization_subscription:enterprise')
                ->get('/exports/generate', [ExportController::class, 'generate'])
                ->name('exports.generate');

            // Subscriptions
            Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
            Route::post('/subscriptions/downgrade', [SubscriptionController::class, 'downgrade'])
                ->middleware('organization_role:admin')
                ->name('subscriptions.downgrade');
            Route::post('/subscriptions/request-contact', [SubscriptionController::class, 'requestContact'])
                ->middleware('organization_role:admin')
                ->name('subscriptions.request-contact');
            Route::post('/subscriptions/{plan}', [SubscriptionController::class, 'subscribe'])
                ->middleware('organization_role:admin')
                ->name('subscriptions.subscribe');

            // Payments
            Route::get('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');

            // Wallet
            Route::middleware('organization_role:admin')->group(
                function () {
                    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
                    Route::get('/wallet/history', [WalletController::class, 'history'])->name('wallet.history');
                    Route::get('/wallet/export-pdf', [WalletController::class, 'exportPdf'])->name('wallet.export-pdf');
                    Route::get('/wallet/export-csv', [WalletController::class, 'exportCsv'])->name('wallet.export-csv');
                    Route::post('/wallet/purchase', [WalletController::class, 'purchase'])->name('wallet.purchase');
                }
            );

            // Resources Library
            Route::prefix('resources')->name('resources.')->group(
                function () {
                    Route::get('/', [ResourceController::class, 'index'])->name('index');
                    Route::get('/{resource:slug}', [ResourceController::class, 'show'])->name('show');
                    Route::post('/{resource:slug}/gift', [ResourceController::class, 'gift'])->name('gift');
                }
            );

            // Advertisements
            Route::prefix('advertisements')->name('advertisements.')->group(function () {
                Route::get('/', [AdvertisementController::class, 'index'])->name('index');
                Route::get('/create', [AdvertisementController::class, 'create'])->name('create');
                Route::post('/', [AdvertisementController::class, 'store'])->name('store');
                Route::get('/{advertisement}/edit', [AdvertisementController::class, 'edit'])->name('edit');
                Route::put('/{advertisement}', [AdvertisementController::class, 'update'])->name('update');
                Route::delete('/{advertisement}', [AdvertisementController::class, 'destroy'])->name('destroy');
            });

            // Team Management (Enterprise only)
            Route::middleware('organization_subscription:enterprise')->prefix('team')->name('team.')->group(
                function () {
                    Route::get('/', [TeamController::class, 'index'])->name('index');
                    Route::get('/create', [TeamController::class, 'create'])->name('create');
                    Route::post('/', [TeamController::class, 'store'])->name('store');
                    Route::delete('/{user}', [TeamController::class, 'destroy'])->name('destroy');
                }
            );

            // Guest Trainers Management
            Route::prefix('guests')->name('guests.')->group(function () {
                Route::get('/', [GuestController::class, 'index'])->name('index');
                Route::get('/create', [GuestController::class, 'create'])->name('create');
                Route::post('/', [GuestController::class, 'store'])->name('store');
                Route::get('/{guest:id}', [GuestController::class, 'show'])->name('show');
                Route::get('/{guest:id}/edit', [GuestController::class, 'edit'])->name('edit');
                Route::put('/{guest:id}', [GuestController::class, 'update'])->name('update');
                Route::delete('/{guest:id}', [GuestController::class, 'destroy'])->name('destroy');
            });

            // Promotion (Establishment only)
            Route::middleware('organization_subscription:establishment')->group(function () {
                Route::prefix('promotion')->name('promotion.')->group(function () {
                    Route::get('/', [PromotionController::class, 'index'])->name('index');
                    Route::get('/export-pdf', [PromotionController::class, 'exportPdf'])->name('export-pdf');
                    Route::get('/export-csv', [PromotionController::class, 'exportCsv'])->name('export-csv');
                });

                Route::prefix('establishments')->name('establishments.')->group(function () {
                    Route::get('/edit', [EstablishmentController::class, 'edit'])->name('edit');
                    Route::put('/{establishment}', [EstablishmentController::class, 'update'])->name('update');
                    Route::post('/{establishment}/boost', [EstablishmentController::class, 'boost'])->name('boost');
                });
            });
        }
    );

    // Logout (Accessible even if unverified/inactive)
    Route::post('/logout', [RegisterController::class, 'logout'])->name('logout');
});
