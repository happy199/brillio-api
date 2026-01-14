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

// Choix du type de compte (inscription)
Route::get('/rejoindre', [WebAuthController::class, 'showChoice'])->name('auth.choice');

// Choix du type de compte (connexion)
Route::get('/connexion', [WebAuthController::class, 'showLoginChoice'])->name('auth.login');

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

    // Route process sans middleware guest (car appelée en AJAX après callback)
    Route::post('/oauth/{provider}/process', [WebAuthController::class, 'jeuneOAuthProcess'])->name('oauth.process');
});

// Authentification Mentors (LinkedIn uniquement)
Route::prefix('mentor')->name('auth.mentor.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/connexion', [WebAuthController::class, 'showMentorLogin'])->name('login');
        Route::get('/linkedin', [WebAuthController::class, 'mentorLinkedInRedirect'])->name('linkedin');
        Route::get('/linkedin/callback', [WebAuthController::class, 'mentorLinkedInCallback'])->name('linkedin.callback');
    });

    // Route process sans middleware guest (car appelée en AJAX après callback)
    Route::post('/linkedin/process', [WebAuthController::class, 'mentorLinkedInProcess'])->name('linkedin.process');
});

// Alias pour la landing page
Route::get('/devenir-mentor', [WebAuthController::class, 'showMentorLogin'])->name('mentor.login');

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
    Route::get('/test-personnalite/questions', [JeuneDashboardController::class, 'getPersonalityQuestions'])->name('personality.questions');
    Route::post('/test-personnalite/submit', [JeuneDashboardController::class, 'submitPersonalityTest'])->name('personality.submit');
    Route::get('/test-personnalite/history/{testId}', [JeuneDashboardController::class, 'getHistoryTestDetails'])->name('personality.history');
    Route::get('/test-personnalite/export-pdf', [\App\Http\Controllers\Jeune\PersonalityPdfController::class, 'exportCurrent'])->name('personality.export-pdf');
    Route::get('/test-personnalite/export-history-pdf', [\App\Http\Controllers\Jeune\PersonalityPdfController::class, 'exportHistory'])->name('personality.export-history-pdf');
    Route::get('/chat', [JeuneDashboardController::class, 'chat'])->name('chat');
    Route::post('/chat/send', [JeuneDashboardController::class, 'sendChatMessage'])->name('chat.send');
    Route::get('/chat/{conversation}', [JeuneDashboardController::class, 'getConversation'])->name('chat.get');
    Route::delete('/chat/{conversation}', [JeuneDashboardController::class, 'deleteConversation'])->name('chat.delete');
    Route::post('/chat/{conversation}/request-human', [JeuneDashboardController::class, 'requestHumanSupport'])->name('chat.request-human');
    Route::get('/documents', [JeuneDashboardController::class, 'documents'])->name('documents');
    Route::post('/documents', [JeuneDashboardController::class, 'storeDocument'])->name('documents.store');
    Route::get('/documents/{document}/download', [JeuneDashboardController::class, 'downloadDocument'])->name('documents.download');
    Route::get('/documents/{document}/view', [JeuneDashboardController::class, 'viewDocument'])->name('documents.view');
    Route::delete('/documents/{document}', [JeuneDashboardController::class, 'deleteDocument'])->name('documents.destroy');
    Route::get('/mentors', [JeuneDashboardController::class, 'mentors'])->name('mentors');
    Route::get('/mentors/{mentor}', [JeuneDashboardController::class, 'mentorShow'])->name('mentors.show');
    Route::get('/profil', [JeuneDashboardController::class, 'profile'])->name('profile');
    Route::put('/profil', [JeuneDashboardController::class, 'updateProfile'])->name('profile.update');
    Route::get('/changer-mot-de-passe', [\App\Http\Controllers\Jeune\PasswordController::class, 'showChangePasswordForm'])->name('password.change');
    Route::put('/changer-mot-de-passe', [\App\Http\Controllers\Jeune\PasswordController::class, 'updatePassword'])->name('password.update');
});

// Routes publiques
Route::get('/politique-de-confidentialite', function () {
    return view('privacy-policy');
})->name('privacy-policy');

Route::post('/accept-cookies', function (Illuminate\Http\Request $request) {
    \App\Models\CookieConsent::create([
        'user_id' => auth()->id(),
        'accepted_at' => now(),
        'ip_address' => $request->ip(),
        'user_agent' => $request->userAgent(),
    ]);
    return response()->json(['success' => true]);
})->name('accept-cookies');

/*
|--------------------------------------------------------------------------
| Espace Mentor (protege)
|--------------------------------------------------------------------------
*/

Route::prefix('espace-mentor')->name('mentor.')->middleware(['auth', 'user_type:mentor'])->group(function () {
    Route::get('/', [MentorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profil', [MentorDashboardController::class, 'profile'])->name('profile');
    Route::put('/profil', [MentorDashboardController::class, 'updateProfile'])->name('profile.update');
    Route::get('/parcours', [MentorDashboardController::class, 'roadmap'])->name('roadmap');
    Route::get('/parcours/{step}', [MentorDashboardController::class, 'getStep'])->name('roadmap.show');
    Route::post('/parcours', [MentorDashboardController::class, 'storeStep'])->name('roadmap.store');
    Route::put('/parcours/{step}', [MentorDashboardController::class, 'updateStep'])->name('roadmap.update');
    Route::delete('/parcours/{step}', [MentorDashboardController::class, 'deleteStep'])->name('roadmap.destroy');
    Route::get('/statistiques', [MentorDashboardController::class, 'stats'])->name('stats');
    Route::post('/profil/linkedin-import', [MentorDashboardController::class, 'importLinkedInData'])->name('profile.linkedin-import');
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

        // Gestion des spécialisations
        Route::resource('specializations', \App\Http\Controllers\Admin\SpecializationController::class);
        Route::get('specializations-moderate', [\App\Http\Controllers\Admin\SpecializationController::class, 'moderate'])->name('specializations.moderate');
        Route::post('specializations/{specialization}/approve', [\App\Http\Controllers\Admin\SpecializationController::class, 'approve'])->name('specializations.approve');
        Route::post('specializations/{specialization}/reject', [\App\Http\Controllers\Admin\SpecializationController::class, 'reject'])->name('specializations.reject');

        // Documents
        Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
        Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
        Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    });
});