<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\MentorController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Auth\WebAuthController;
use App\Http\Controllers\Jeune\JeuneDashboardController;
use App\Http\Controllers\Jeune\OnboardingController;
use App\Http\Controllers\Mentor\MentorDashboardController;
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
| Routes Authentification Jeunes & Mentors
|--------------------------------------------------------------------------
*/

// Choix du type de compte
Route::get('/rejoindre', [WebAuthController::class, 'showChoice'])->name('auth.choice');

// Authentification Jeunes
Route::prefix('jeune')->name('auth.jeune.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/inscription', [WebAuthController::class, 'showJeuneRegister'])->name('register');
        Route::post('/inscription', [WebAuthController::class, 'jeuneRegister'])->name('register.submit');
        Route::get('/connexion', [WebAuthController::class, 'showJeuneLogin'])->name('login');
        Route::post('/connexion', [WebAuthController::class, 'jeuneLogin'])->name('login.submit');

        // OAuth (Google, Facebook)
        Route::get('/oauth/{provider}', [WebAuthController::class, 'jeuneOAuthRedirect'])->name('oauth');
        Route::get('/oauth/{provider}/callback', [WebAuthController::class, 'jeuneOAuthCallback'])->name('oauth.callback');
    });
});

// Authentification Mentors (LinkedIn uniquement)
Route::prefix('mentor')->name('auth.mentor.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/connexion', [WebAuthController::class, 'showMentorLogin'])->name('login');
        Route::get('/linkedin', [WebAuthController::class, 'mentorLinkedInRedirect'])->name('linkedin');
        Route::get('/linkedin/callback', [WebAuthController::class, 'mentorLinkedInCallback'])->name('linkedin.callback');
    });
});

// Deconnexion commune
Route::post('/deconnexion', [WebAuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Espace Jeune (protege)
|--------------------------------------------------------------------------
*/

Route::prefix('espace-jeune')->name('jeune.')->middleware(['auth', 'user_type:jeune'])->group(function () {
    // Onboarding
    Route::get('/bienvenue', [OnboardingController::class, 'index'])->name('onboarding');
    Route::post('/bienvenue', [OnboardingController::class, 'complete'])->name('onboarding.complete');

    // Dashboard et fonctionnalites
    Route::get('/', [JeuneDashboardController::class, 'index'])->name('dashboard');
    Route::get('/test-personnalite', [JeuneDashboardController::class, 'personalityTest'])->name('personality');
    Route::get('/chat', [JeuneDashboardController::class, 'chat'])->name('chat');
    Route::get('/documents', [JeuneDashboardController::class, 'documents'])->name('documents');
    Route::get('/mentors', [JeuneDashboardController::class, 'mentors'])->name('mentors');
    Route::get('/profil', [JeuneDashboardController::class, 'profile'])->name('profile');
});

/*
|--------------------------------------------------------------------------
| Espace Mentor (protege)
|--------------------------------------------------------------------------
*/

Route::prefix('espace-mentor')->name('mentor.')->middleware(['auth', 'user_type:mentor'])->group(function () {
    Route::get('/', [MentorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profil', [MentorDashboardController::class, 'profile'])->name('profile');
    Route::post('/profil', [MentorDashboardController::class, 'updateProfile'])->name('profile.update');
    Route::get('/parcours', [MentorDashboardController::class, 'roadmap'])->name('roadmap');
    Route::get('/statistiques', [MentorDashboardController::class, 'stats'])->name('stats');
});

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
        Route::post('/', [AuthController::class, 'login'])->name('login.post');
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
        Route::put('mentors/{mentor}/approve', [MentorController::class, 'approve'])->name('mentors.approve');
        Route::put('mentors/{mentor}/reject', [MentorController::class, 'reject'])->name('mentors.reject');

        // Analytiques
        Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('personality', [AnalyticsController::class, 'personality'])->name('personality');
            Route::get('chat', [AnalyticsController::class, 'chat'])->name('chat');
            Route::get('export', [AnalyticsController::class, 'export'])->name('export');
            Route::get('export-pdf', [AnalyticsController::class, 'exportPdf'])->name('export-pdf');
        });

        // Chat conversations
        Route::get('chat', [ChatController::class, 'index'])->name('chat.index');
        Route::get('chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');
        Route::post('chat/{conversation}/take-over', [ChatController::class, 'takeOver'])->name('chat.take-over');
        Route::post('chat/{conversation}/send-message', [ChatController::class, 'sendMessage'])->name('chat.send-message');
        Route::post('chat/{conversation}/end-support', [ChatController::class, 'endSupport'])->name('chat.end-support');
        Route::get('chat/{conversation}/export-pdf', [ChatController::class, 'exportPdf'])->name('chat.export-pdf');

        // Documents
        Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
        Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
        Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    });
});