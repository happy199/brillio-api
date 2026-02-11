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
use App\Http\Controllers\Public\NewsletterController as PublicNewsletterController;
use App\Http\Controllers\Public\ContactController as PublicContactController;
use App\Http\Controllers\Admin\NewsletterController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\MonetizationController;
use App\Http\Controllers\Mentor\WalletController;
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
Route::post('/contact', [PublicContactController::class, 'submit'])->name('contact.submit');

// Newsletter
Route::post('/newsletter/subscribe', [PublicNewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
Route::get('/newsletter/unsubscribe/{token}', [PublicNewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');

/*
|--------------------------------------------------------------------------
| Moneroo Payment Routes
|--------------------------------------------------------------------------
*/

// Payment callback (user returns from Moneroo checkout)
Route::get('/payments/callback', [\App\Http\Controllers\PaymentCallbackController::class, 'handle'])
    ->name('payments.callback');

// Webhook (Moneroo sends payment notifications here - NO CSRF protection)
Route::post('/webhooks/moneroo', [\App\Http\Controllers\MonerooWebhookController::class, 'handle'])
    ->name('webhooks.moneroo')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// Pages légales
Route::get('/politique-de-confidentialite', [PageController::class, 'privacy'])->name('privacy');

// SEO
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

Route::get('/conditions-utilisation', [PageController::class, 'terms'])->name('terms');

// Profil public mentor (partageable)
Route::get('/profil-mentor/{mentor}', [PageController::class, 'mentorProfile'])->name('public.mentor.profile');

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

        // Password Reset
        Route::get('/mot-de-passe-oublie', [WebAuthController::class, 'showForgotPasswordForm'])->name('password.request');
        Route::post('/mot-de-passe-oublie', [WebAuthController::class, 'sendResetLink'])->name('password.email');
        Route::get('/reinitialiser-mot-de-passe/{token}', [WebAuthController::class, 'showResetForm'])->name('password.reset');
        Route::post('/reinitialiser-mot-de-passe', [WebAuthController::class, 'resetPassword'])->name('password.update');
    });

    // Route process sans middleware guest (car appelée en AJAX après callback)
    Route::post('/oauth/{provider}/process', [WebAuthController::class, 'jeuneOAuthProcess'])->name('oauth.process');
});

// Routes de confirmation de changement de type (accessible sans authentification)
Route::get('/auth/confirm-type-change', [WebAuthController::class, 'showConfirmTypeChange'])->name('auth.confirm-type-change');
Route::post('/auth/confirm-type-change', [WebAuthController::class, 'confirmTypeChange'])->name('auth.confirm-type-change.post');

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

// Profil public Jeune
Route::get('/p/{slug}', [PageController::class, 'jeuneProfile'])->name('jeune.public.show');

/*
|--------------------------------------------------------------------------
| Common Authenticated Routes (Meeting, etc.)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/meeting/{meetingId}', [App\Http\Controllers\MeetingController::class, 'show'])->name('meeting.show');
});

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

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
    Route::get('/profil', [App\Http\Controllers\Jeune\ProfileController::class, 'index'])->name('profile');
    Route::post('/profil', [App\Http\Controllers\Jeune\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profil/publier', [App\Http\Controllers\Jeune\ProfileController::class, 'publishProfile'])->name('profile.publish');

    // Account archiving
    Route::get('/account/confirmation-code', [App\Http\Controllers\AccountController::class, 'generateConfirmationCode'])->name('account.confirmation-code');
    Route::post('/account/archive', [App\Http\Controllers\AccountController::class, 'archiveAccount'])->name('account.archive');
    Route::get('/changer-mot-de-passe', [\App\Http\Controllers\Jeune\PasswordController::class, 'showChangePasswordForm'])->name('password.change');
    Route::put('/changer-mot-de-passe', [\App\Http\Controllers\Jeune\PasswordController::class, 'updatePassword'])->name('password.update');

    // Ressources pédagogiques
    Route::resource('ressources', \App\Http\Controllers\Jeune\ResourceController::class)
        ->only(['index', 'show'])
        ->names('resources')
        ->parameters(['ressources' => 'resource']);

    Route::post('/ressources/{resource}/unlock', [\App\Http\Controllers\Jeune\ResourceController::class, 'unlock'])->name('resources.unlock');
    Route::post('/mentorship/request', [\App\Http\Controllers\Jeune\MentorshipController::class, 'store'])->name('mentorship.request');
    Route::post('/mentorship/{mentorship}/cancel', [\App\Http\Controllers\Jeune\MentorshipController::class, 'cancel'])->name('mentorship.cancel');
    Route::get('/mentorat', [\App\Http\Controllers\Jeune\MentorshipController::class, 'index'])->name('mentorship.index');

    // Séances (Jeune)
    Route::get('/mentorat/seances', [\App\Http\Controllers\Jeune\SessionController::class, 'index'])->name('sessions.index');
    Route::get('/mentorat/calendrier', [\App\Http\Controllers\Jeune\SessionController::class, 'calendar'])->name('sessions.calendar');
    Route::post('/mentorat/seances', [\App\Http\Controllers\Jeune\SessionController::class, 'store'])->name('sessions.store');
    Route::get('/mentorat/seances/reserver/{mentor}', [\App\Http\Controllers\Jeune\SessionController::class, 'create'])->name('sessions.create');
    Route::get('/mentorat/seances/{session}', [\App\Http\Controllers\Jeune\SessionController::class, 'show'])->name('sessions.show');
    Route::post('/mentorat/seances/{session}/cancel', [\App\Http\Controllers\Jeune\SessionController::class, 'cancel'])->name('sessions.cancel');
    Route::post('/mentorat/seances/{session}/pay-join', [\App\Http\Controllers\Jeune\SessionController::class, 'payAndJoin'])->name('sessions.pay-join');

    // Portefeuille & Crédits
    Route::get('/portefeuille', [\App\Http\Controllers\Jeune\WalletController::class, 'index'])->name('wallet.index');
    Route::post('/portefeuille/achat', [\App\Http\Controllers\Jeune\WalletController::class, 'purchase'])->name('wallet.purchase');
    Route::post('/portefeuille/coupon', [\App\Http\Controllers\Jeune\WalletController::class, 'redeemCoupon'])->name('wallet.redeem');
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
    Route::post('/profil/publier', [MentorDashboardController::class, 'publishProfile'])->name('profile.publish');
    Route::get('/parcours', [MentorDashboardController::class, 'roadmap'])->name('roadmap');
    Route::get('/parcours/{step}', [MentorDashboardController::class, 'getStep'])->name('roadmap.show');
    Route::post('/parcours', [MentorDashboardController::class, 'storeStep'])->name('roadmap.store');
    Route::put('/parcours/{step}', [MentorDashboardController::class, 'updateStep'])->name('roadmap.update');
    Route::delete('/parcours/{step}', [MentorDashboardController::class, 'deleteStep'])->name('roadmap.destroy');
    Route::get('/statistiques', [MentorDashboardController::class, 'stats'])->name('stats');
    Route::post('/profil/linkedin-import', [MentorDashboardController::class, 'importLinkedInData'])->name('profile.linkedin-import');
    Route::get('/explorer', [App\Http\Controllers\Mentor\ExploreController::class, 'index'])->name('explore');

    // Portefeuille & Crédits
    Route::get('/portefeuille', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/portefeuille/achat', [WalletController::class, 'purchase'])->name('wallet.purchase');
    Route::post('/portefeuille/coupon', [WalletController::class, 'redeemCoupon'])->name('wallet.redeem');

    // Test Personnalité Mentor
    Route::get('/test-personnalite', [App\Http\Controllers\Mentor\PersonalityController::class, 'index'])->name('personality');
    Route::get('/test-personnalite/questions', [App\Http\Controllers\Mentor\PersonalityController::class, 'getQuestions'])->name('personality.questions');
    Route::post('/test-personnalite/submit', [App\Http\Controllers\Mentor\PersonalityController::class, 'submit'])->name('personality.submit');
    Route::get('/test-personnalite/export-pdf', [App\Http\Controllers\Mentor\PersonalityPdfController::class, 'exportCurrent'])->name('personality.export-pdf');
    Route::get('/test-personnalite/export-history-pdf', [App\Http\Controllers\Mentor\PersonalityPdfController::class, 'exportHistory'])->name('personality.export-history-pdf');
    Route::get('/test-personnalite/history/{testId}', [App\Http\Controllers\Mentor\PersonalityController::class, 'getHistoryTestDetails'])->name('personality.history');

    // Account archiving
    Route::get('/account/confirmation-code', [App\Http\Controllers\AccountController::class, 'generateConfirmationCode'])->name('account.confirmation-code');
    Route::post('/account/archive', [App\Http\Controllers\AccountController::class, 'archiveAccount'])->name('account.archive');

    // --- ROUTES MENTORAT (Nécessitent profil publié) ---
    Route::middleware(['mentor_published'])->group(function () {
        // Dashboard Mentorat (Roadmap)
        Route::get('/mentorship/roadmap', [App\Http\Controllers\Mentor\MentorshipController::class, 'roadmap'])->name('mentor.roadmap');

        // Gestion des ressources mentor (URL: /ressources)
        // Le prefixe 'mentor.' est déjà appliqué par le groupe parent, donc ->names('resources') donnera 'mentor.resources.*'
        Route::resource('ressources', \App\Http\Controllers\Mentor\ResourceController::class)->names('resources');

        // Gestion des mentés
        // Le prefixe 'mentor.' est déjà appliqué. On ajoute 'mentorship.' -> 'mentor.mentorship.*'
        Route::name('mentorship.')->group(function () {
            // URL: /mentes
            Route::get('/mentes', [App\Http\Controllers\Mentor\MentorshipController::class, 'index'])->name('index'); // Liste mentés
            Route::get('/requests', [App\Http\Controllers\Mentor\MentorshipController::class, 'requests'])->name('requests'); // Demandes
            Route::post('/{mentorship}/accepter', [App\Http\Controllers\Mentor\MentorshipController::class, 'accept'])->name('accept');
            Route::post('/{mentorship}/refuser', [App\Http\Controllers\Mentor\MentorshipController::class, 'refuse'])->name('refuse');
            Route::post('/{mentorship}/deconnecter', [App\Http\Controllers\Mentor\MentorshipController::class, 'disconnect'])->name('disconnect');

            // Calendrier & Dispos (URL: /calendrier)
            Route::get('/calendrier', [\App\Http\Controllers\Mentor\SessionController::class, 'index'])->name('calendar');
            Route::post('/availability', [\App\Http\Controllers\Mentor\SessionController::class, 'storeAvailability'])->name('availability.store');

            // Séances
            // URL: /sessions/... (On garde /sessions pour éviter les conflits d'URL racine trop génériques)
            Route::get('/sessions/create', [\App\Http\Controllers\Mentor\SessionController::class, 'create'])->name('sessions.create');
            Route::post('/sessions', [\App\Http\Controllers\Mentor\SessionController::class, 'store'])->name('sessions.store');
            // Edit & Update routes
            Route::get('/sessions/{session}/edit', [\App\Http\Controllers\Mentor\SessionController::class, 'edit'])->name('sessions.edit');
            Route::put('/sessions/{session}', [\App\Http\Controllers\Mentor\SessionController::class, 'update'])->name('sessions.update');

            Route::get('/sessions/{session}', [\App\Http\Controllers\Mentor\SessionController::class, 'show'])->name('sessions.show');
            Route::put('/sessions/{session}/report', [\App\Http\Controllers\Mentor\SessionController::class, 'updateReport'])->name('sessions.report.update');
            Route::post('/sessions/{session}/accept', [\App\Http\Controllers\Mentor\SessionController::class, 'accept'])->name('sessions.accept');
            Route::post('/sessions/{session}/refuse', [\App\Http\Controllers\Mentor\SessionController::class, 'refuse'])->name('sessions.refuse');
            Route::post('/sessions/{session}/cancel', [\App\Http\Controllers\Mentor\SessionController::class, 'cancel'])->name('sessions.cancel');
        });
    });

    // Page de blocage (Accessible sans middleware mentor_published)
    Route::get('/mentorship/locked', function () {
        return view('mentor.mentorship.locked');
    })->name('mentorship.locked');
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
        Route::put('users/{user}/reactivate', [UserController::class, 'reactivate'])->name('users.reactivate');

        // Gestion des mentors
        Route::get('mentors', [MentorController::class, 'index'])->name('mentors.index');
        Route::get('mentors/{mentor}', [MentorController::class, 'show'])->name('mentors.show');
        Route::get('mentors/{mentor}/edit', [MentorController::class, 'edit'])->name('mentors.edit');
        Route::put('mentors/{mentor}', [MentorController::class, 'update'])->name('mentors.update');
        Route::post('mentors/{mentor}/photo', [MentorController::class, 'updateProfilePhoto'])->name('mentors.update-photo');
        Route::post('mentors/{mentor}/roadmap', [MentorController::class, 'storeRoadmapStep'])->name('mentors.roadmap.store');
        Route::put('mentors/{mentor}/roadmap/{step}', [MentorController::class, 'updateRoadmapStep'])->name('mentors.roadmap.update');
        Route::delete('mentors/{mentor}/roadmap/{step}', [MentorController::class, 'deleteRoadmapStep'])->name('mentors.roadmap.delete');
        Route::patch('mentors/{mentor}/toggle-publish', [MentorController::class, 'togglePublish'])->name('mentors.toggle-publish');
        Route::patch('mentors/{mentor}/toggle-validation', [MentorController::class, 'toggleValidation'])->name('mentors.toggle-validation');
        Route::put('mentors/{mentor}/approve', [MentorController::class, 'approve'])->name('mentors.approve');
        Route::put('mentors/{mentor}/reject', [MentorController::class, 'reject'])->name('mentors.reject');
        Route::get('mentors/{mentor}/download-linkedin', [MentorController::class, 'downloadLinkeInProfile'])->name('mentors.download-linkedin');

        // Gestion des ressources
        Route::resource('resources', \App\Http\Controllers\Admin\ResourceController::class);
        Route::post('resources/approve-all', [\App\Http\Controllers\Admin\ResourceController::class, 'approveAll'])->name('resources.approve_all');
        Route::put('resources/{resource}/approve', [\App\Http\Controllers\Admin\ResourceController::class, 'approve'])->name('resources.approve');
        Route::put('resources/{resource}/reject', [\App\Http\Controllers\Admin\ResourceController::class, 'reject'])->name('resources.reject');

        // Monétisation
        Route::get('comptabilite/historique', [\App\Http\Controllers\Admin\AccountingController::class, 'history'])->name('accounting.history');
        Route::get('comptabilite', [\App\Http\Controllers\Admin\AccountingController::class, 'index'])->name('accounting.index');
        Route::get('monetisation', [MonetizationController::class, 'index'])->name('monetization.index');
        Route::post('monetisation/settings', [MonetizationController::class, 'updateSettings'])->name('monetization.settings.update');
        Route::get('monetisation/coupons', [MonetizationController::class, 'coupons'])->name('monetization.coupons');
        Route::post('monetisation/coupons', [MonetizationController::class, 'storeCoupon'])->name('monetization.coupons.store');
        Route::delete('monetisation/coupons/{coupon}', [MonetizationController::class, 'destroyCoupon'])->name('monetization.coupons.destroy');

        // Gestion des Packs de Crédits
        Route::resource('credit-packs', \App\Http\Controllers\Admin\CreditPackController::class);

        // Payouts Mentors
        Route::get('payouts', [App\Http\Controllers\Admin\PayoutController::class, 'index'])->name('payouts.index');
        Route::get('payouts/{payout}', [App\Http\Controllers\Admin\PayoutController::class, 'show'])->name('payouts.show');


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

        // Newsletter
        Route::get('newsletter', [NewsletterController::class, 'index'])->name('newsletter.index');
        Route::get('newsletter/export-csv', [NewsletterController::class, 'exportCsv'])->name('newsletter.export-csv');
        Route::get('newsletter/export-pdf', [NewsletterController::class, 'exportPdf'])->name('newsletter.export-pdf');
        Route::post('newsletter/send-email', [NewsletterController::class, 'sendEmail'])->name('newsletter.send-email');
        Route::get('newsletter/campaigns', [NewsletterController::class, 'campaigns'])->name('newsletter.campaigns');
        Route::put('newsletter/{subscriber}', [NewsletterController::class, 'update'])->name('newsletter.update');
        Route::delete('newsletter/{subscriber}', [NewsletterController::class, 'destroy'])->name('newsletter.destroy');

        // Messages de contact
        Route::get('contact-messages', [ContactMessageController::class, 'index'])->name('contact-messages.index');
        Route::get('contact-messages/{message}', [ContactMessageController::class, 'show'])->name('contact-messages.show');
        Route::post('contact-messages/{message}/reply', [ContactMessageController::class, 'reply'])->name('contact-messages.reply');
        Route::delete('contact-messages/{message}', [ContactMessageController::class, 'destroy'])->name('contact-messages.destroy');
        Route::get('contact-messages-export-pdf', [ContactMessageController::class, 'exportPdf'])->name('contact-messages.export-pdf');

        // Documents
        Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
        Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
        Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    });
    // Mentorat (Nouveau Groupe)
    Route::get('/mentorship/requests', [App\Http\Controllers\Admin\MentorshipController::class, 'requests'])->name('mentorship.requests');
    Route::get('/mentorship/requests/{mentorship}', [App\Http\Controllers\Admin\MentorshipController::class, 'showRequest'])->name('mentorship.requests.show');
    Route::get('/mentorship/sessions', [App\Http\Controllers\Admin\MentorshipController::class, 'sessions'])->name('mentorship.sessions');
    Route::get('/mentorship/sessions/{session}', [App\Http\Controllers\Admin\MentorshipController::class, 'showSession'])->name('mentorship.sessions.show');
});