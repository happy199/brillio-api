<?php

use App\Http\Controllers\Organization\Auth\RegisterController;
use App\Http\Controllers\Organization\DashboardController;
use App\Http\Controllers\Organization\InvitationController;
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
Route::middleware(['auth', 'organization'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class , 'index'])->name('dashboard');

    // Invitations
    Route::get('/invitations', [InvitationController::class , 'index'])->name('invitations.index');
    Route::get('/invitations/create', [InvitationController::class , 'create'])->name('invitations.create');
    Route::post('/invitations', [InvitationController::class , 'store'])->name('invitations.store');
    Route::delete('/invitations/{invitation}', [InvitationController::class , 'destroy'])->name('invitations.destroy');

    // Logout
    Route::post('/logout', [RegisterController::class , 'logout'])->name('logout');
});