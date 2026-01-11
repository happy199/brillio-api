<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\MentorController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Public\PageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Site Vitrine & Dashboard Admin
|--------------------------------------------------------------------------
|
| Routes publiques pour le site vitrine et routes protégées pour
| le dashboard d'administration
|
*/

/*
|--------------------------------------------------------------------------
| Routes Publiques - Site Vitrine
|--------------------------------------------------------------------------
*/

// Page d'accueil
Route::get('/', [PageController::class, 'home'])->name('home');

// Pages informatives
Route::get('/a-propos', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'submitContact'])->name('contact.submit');

// Pages légales
Route::get('/politique-de-confidentialite', [PageController::class, 'privacy'])->name('privacy');
Route::get('/conditions-utilisation', [PageController::class, 'terms'])->name('terms');

/*
|--------------------------------------------------------------------------
| Routes Admin (protégées - URL secrète)
|--------------------------------------------------------------------------
|
| Accès admin via /brillioSecretTeamAdmin
| Cette URL est intentionnellement obscure pour la sécurité
|
*/

Route::prefix('brillioSecretTeamAdmin')->name('admin.')->group(function () {

    // === Authentification Admin ===
    Route::middleware('guest')->group(function () {
        Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
        Route::post('/login', [AuthController::class, 'login']);
    });

    Route::middleware(['auth', 'is_admin'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        // Dashboard principal
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Gestion des utilisateurs
        Route::resource('users', UserController::class)->only(['index', 'show', 'destroy']);
        Route::put('users/{user}/toggle-admin', [UserController::class, 'toggleAdmin'])->name('users.toggle-admin');

        // Gestion des mentors
        Route::get('mentors', [MentorController::class, 'index'])->name('mentors.index');
        Route::get('mentors/{mentor}', [MentorController::class, 'show'])->name('mentors.show');
        Route::patch('mentors/{mentor}/toggle-publish', [MentorController::class, 'togglePublish'])->name('mentors.toggle-publish');
        Route::put('mentors/{mentor}/validate', [MentorController::class, 'validate'])->name('mentors.validate');
        Route::put('mentors/{mentor}/reject', [MentorController::class, 'reject'])->name('mentors.reject');

        // Analytiques
        Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('personality', [AnalyticsController::class, 'personality'])->name('personality');
            Route::get('chat', [AnalyticsController::class, 'chat'])->name('chat');
            Route::get('export', [AnalyticsController::class, 'export'])->name('export');
        });

        // Chat conversations
        Route::get('chat', [ChatController::class, 'index'])->name('chat.index');
        Route::get('chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');

        // Documents
        Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
        Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
        Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    });
});
