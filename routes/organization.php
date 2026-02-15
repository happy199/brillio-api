<?php

use App\Http\Controllers\Organization\Auth\RegisterController;
use App\Http\Controllers\Organization\DashboardController;
use App\Http\Controllers\Organization\InvitationController;
use App\Http\Controllers\Organization\ExportController;
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
    Route::get('/register', [RegisterController::class , 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class , 'register'])->name('register.submit');

    // Login
    Route::get('/login', [RegisterController::class , 'showLoginForm'])->name('login');
    Route::post('/login', [RegisterController::class , 'login'])->name('login.submit');
});

// Protected routes (authenticated organizations only)
Route::middleware(['auth', 'organization', 'organization_active'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class , 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [\App\Http\Controllers\Organization\ProfileController::class , 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\Organization\ProfileController::class , 'update'])->name('profile.update');

    // Invitations
    Route::get('/invitations', [InvitationController::class , 'index'])->name('invitations.index');
    Route::get('/invitations/create', [InvitationController::class , 'create'])->name('invitations.create');
    Route::post('/invitations', [InvitationController::class , 'store'])->name('invitations.store');
    Route::delete('/invitations/{invitation}', [InvitationController::class , 'destroy'])->name('invitations.destroy');

    // Pro Features (Jeunes, Mentorships, Sessions)
    Route::middleware('organization_subscription:pro')->group(function () {
            // Sponsored Users
            Route::get('/users', [\App\Http\Controllers\Organization\SponsoredUsersController::class , 'index'])->name('users.index');
            Route::get('/users/{user}', [\App\Http\Controllers\Organization\SponsoredUsersController::class , 'show'])->name('users.show');

            // Mentors
            Route::get('/mentors/{mentor}', [\App\Http\Controllers\Organization\SponsoredUsersController::class , 'mentorShow'])->name('mentors.show');

            // Mentorships
            Route::get('/mentorships', [\App\Http\Controllers\Organization\MentorshipController::class , 'index'])->name('mentorships.index');
            Route::get('/mentorships/{mentorship}', [\App\Http\Controllers\Organization\MentorshipController::class , 'show'])->name('mentorships.show');

            // Sessions & Calendar
            Route::get('/sessions', [\App\Http\Controllers\Organization\SessionController::class , 'index'])->name('sessions.index');
            Route::get('/sessions/calendar', [\App\Http\Controllers\Organization\SessionController::class , 'calendar'])->name('sessions.calendar');
            Route::get('/sessions/events', [\App\Http\Controllers\Organization\SessionController::class , 'events'])->name('sessions.events');
            Route::get('/sessions/{session}', [\App\Http\Controllers\Organization\SessionController::class , 'show'])->name('sessions.show');

            // Individual User Export (moved here as it's part of user details)
            Route::get('/users/{user}/export', [SponsoredUsersController::class , 'export'])->name('users.export');
        }
        );

        // Exports
        Route::get('/exports', [ExportController::class , 'index'])->name('exports.index'); // Export Center (Viewable by all, but protected actions)
    
        Route::middleware('organization_subscription:enterprise')->get('/exports/generate', [ExportController::class , 'generate'])->name('exports.generate');


        // Subscriptions
        Route::get('/subscriptions', [\App\Http\Controllers\Organization\SubscriptionController::class , 'index'])->name('subscriptions.index');
        Route::post('/subscriptions/downgrade', [\App\Http\Controllers\Organization\SubscriptionController::class , 'downgrade'])->name('subscriptions.downgrade');
        Route::post('/subscriptions/{plan}', [\App\Http\Controllers\Organization\SubscriptionController::class , 'subscribe'])->name('subscriptions.subscribe');

        // Payments
        Route::get('/payment/callback', [\App\Http\Controllers\Organization\PaymentController::class , 'callback'])->name('payment.callback');

        // Wallet
        Route::get('/wallet', [\App\Http\Controllers\Organization\WalletController::class , 'index'])->name('wallet.index');
        Route::post('/wallet/purchase', [\App\Http\Controllers\Organization\WalletController::class , 'purchase'])->name('wallet.purchase');

        // Logout
        Route::post('/logout', [RegisterController::class , 'logout'])->name('logout');
    });