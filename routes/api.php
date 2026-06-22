<?php

use App\Http\Controllers\Api\V2\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
 |--------------------------------------------------------------------------
 | API Routes
 |--------------------------------------------------------------------------
 |
 | Here is where you can register API routes for your application. These
 | routes are loaded by the RouteServiceProvider and all of them will
 | be assigned to the "api" middleware group. Make something great!
 |
 */

// Health Check
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// V2 Authentication (Guest)
Route::prefix('v2')->group(function () {
    Route::post('/register', [\App\Http\Controllers\Api\V2\AuthController::class, 'register']);
    Route::post('/login', [\App\Http\Controllers\Api\V2\AuthController::class, 'login']);
    Route::post('/password/email', [\App\Http\Controllers\Api\V2\AuthController::class, 'sendResetLinkEmail']);
    Route::post('/password/reset', [\App\Http\Controllers\Api\V2\AuthController::class, 'resetPassword']);
});

// V1 Authentication (Guest)
Route::prefix('v1')->group(function () {
    Route::post('/register', [\App\Http\Controllers\Api\V1\AuthController::class, 'register']);
    Route::post('/login', [\App\Http\Controllers\Api\V1\AuthController::class, 'login']);
});

// Default Fallback Authentication (pointed to V2 by import)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Mentor Payout Routes
    Route::prefix('mentor')->group(function () {
        Route::get('/balance', [\App\Http\Controllers\Api\Mentor\PayoutController::class, 'getBalance']);
        Route::get('/payout-methods', [\App\Http\Controllers\Api\Mentor\PayoutController::class, 'getPayoutMethods']);
        Route::post('/payout/request', [\App\Http\Controllers\Api\Mentor\PayoutController::class, 'requestPayout']);
        Route::get('/payout-requests', [\App\Http\Controllers\Api\Mentor\PayoutController::class, 'getPayoutRequests']);
        Route::post('/payout/{payout}/cancel', [\App\Http\Controllers\Api\Mentor\PayoutController::class, 'cancelPayout']);
    });

    // V1 Resources
    Route::prefix('v1')->group(function () {
        // Resources
        Route::get('/resources', [\App\Http\Controllers\Api\V1\ResourceController::class, 'index']);
        Route::get('/resources/{id}', [\App\Http\Controllers\Api\V1\ResourceController::class, 'show']);
        Route::post('/resources/{id}/unlock', [\App\Http\Controllers\Api\V1\ResourceController::class, 'unlock']);

        // Mentorship
        Route::get('/mentorships', [\App\Http\Controllers\Api\V1\MentorshipController::class, 'index']);
        Route::post('/mentorships', [\App\Http\Controllers\Api\V1\MentorshipController::class, 'store']);
        Route::post('/mentorships/{id}/cancel', [\App\Http\Controllers\Api\V1\MentorshipController::class, 'cancel']);

        // Sessions
        Route::get('/sessions', [\App\Http\Controllers\Api\V1\SessionController::class, 'index']);
        Route::post('/sessions', [\App\Http\Controllers\Api\V1\SessionController::class, 'store']);
        Route::post('/sessions/{id}/cancel', [\App\Http\Controllers\Api\V1\SessionController::class, 'cancel']);
        Route::post('/sessions/{id}/pay', [\App\Http\Controllers\Api\V1\SessionController::class, 'pay']);

        // Wallet
        Route::get('/wallet', [\App\Http\Controllers\Api\V1\WalletController::class, 'index']);
        Route::get('/wallet/packs', [\App\Http\Controllers\Api\V1\WalletController::class, 'packs']);
        Route::post('/wallet/redeem', [\App\Http\Controllers\Api\V1\WalletController::class, 'redeemCoupon']);

        // Academic Documents
        Route::get('/documents', [\App\Http\Controllers\Api\V1\AcademicDocumentController::class, 'index']);
        Route::post('/documents', [\App\Http\Controllers\Api\V1\AcademicDocumentController::class, 'upload']);
        Route::get('/documents/{id}', [\App\Http\Controllers\Api\V1\AcademicDocumentController::class, 'show']);
        Route::get('/documents/{id}/download', [\App\Http\Controllers\Api\V1\AcademicDocumentController::class, 'download']);
        Route::delete('/documents/{id}', [\App\Http\Controllers\Api\V1\AcademicDocumentController::class, 'destroy']);
        Route::get('/document-types', [\App\Http\Controllers\Api\V1\AcademicDocumentController::class, 'types']);

        // Personality Test
        Route::get('/personality/questions', [\App\Http\Controllers\Api\V1\PersonalityController::class, 'questions']);
        Route::post('/personality/submit', [\App\Http\Controllers\Api\V1\PersonalityController::class, 'submit']);
        Route::get('/personality/result/{userId?}', [\App\Http\Controllers\Api\V1\PersonalityController::class, 'result']);
        Route::get('/personality/status', [\App\Http\Controllers\Api\V1\PersonalityController::class, 'status']);

        // Mentors
        Route::get('/mentors', [\App\Http\Controllers\Api\V1\MentorController::class, 'index']);
        Route::get('/mentors/{id}', [\App\Http\Controllers\Api\V1\MentorController::class, 'show']);

        // Organizations
        Route::post('/organizations/{id}/track-click', [\App\Http\Controllers\Api\V1\OrganizationController::class, 'trackClick']);

        // Chat
        Route::get('/chat/conversations', [\App\Http\Controllers\Api\V1\ChatController::class, 'conversations']);
        Route::post('/chat/send', [\App\Http\Controllers\Api\V1\ChatController::class, 'send']);
    });

    // V2 Resources
    Route::prefix('v2')->group(function () {
        // Resources
        Route::get('/resources', [\App\Http\Controllers\Api\V2\ResourceController::class, 'index']);
        Route::post('/resources', [\App\Http\Controllers\Api\V2\ResourceController::class, 'store']);
        Route::get('/resources/{id}', [\App\Http\Controllers\Api\V2\ResourceController::class, 'show']);
        Route::put('/resources/{id}', [\App\Http\Controllers\Api\V2\ResourceController::class, 'update']);
        Route::delete('/resources/{id}', [\App\Http\Controllers\Api\V2\ResourceController::class, 'destroy']);
        Route::post('/resources/{id}/unlock', [\App\Http\Controllers\Api\V2\ResourceController::class, 'unlock']);

        // Mentorship
        Route::get('/mentorships', [\App\Http\Controllers\Api\V2\MentorshipController::class, 'index']);
        Route::post('/mentorships', [\App\Http\Controllers\Api\V2\MentorshipController::class, 'store']);
        Route::post('/mentorships/{id}/cancel', [\App\Http\Controllers\Api\V2\MentorshipController::class, 'cancel']);
        Route::post('/mentorships/{id}/disconnect', [\App\Http\Controllers\Api\V2\MentorshipController::class, 'disconnect']);
        Route::post('/mentorships/{id}/accept', [\App\Http\Controllers\Api\V2\MentorshipController::class, 'accept']);
        Route::post('/mentorships/{id}/refuse', [\App\Http\Controllers\Api\V2\MentorshipController::class, 'refuse']);
        Route::get('/mentorships/requests', [\App\Http\Controllers\Api\V2\MentorshipController::class, 'requests']);
        Route::post('/mentorships/availability', [\App\Http\Controllers\Api\V2\MentorshipController::class, 'setAvailability']);
        Route::get('/mentorships/calendar', [\App\Http\Controllers\Api\V2\MentorshipController::class, 'calendar']);

        // Sessions
        Route::get('/sessions', [\App\Http\Controllers\Api\V2\SessionController::class, 'index']);
        Route::post('/sessions', [\App\Http\Controllers\Api\V2\SessionController::class, 'store']);
        Route::put('/sessions/{id}', [\App\Http\Controllers\Api\V2\SessionController::class, 'update']);
        Route::post('/sessions/{id}/cancel', [\App\Http\Controllers\Api\V2\SessionController::class, 'cancel']);
        Route::post('/sessions/{id}/pay', [\App\Http\Controllers\Api\V2\SessionController::class, 'pay']);
        Route::post('/sessions/{id}/accept', [\App\Http\Controllers\Api\V2\SessionController::class, 'accept']);
        Route::post('/sessions/{id}/refuse', [\App\Http\Controllers\Api\V2\SessionController::class, 'refuse']);
        Route::put('/sessions/{id}/report', [\App\Http\Controllers\Api\V2\SessionController::class, 'report']);
        Route::get('/sessions/{id}/download-report', [\App\Http\Controllers\Api\V2\SessionController::class, 'downloadReport']);
        Route::get('/sessions/{id}/download-transcription', [\App\Http\Controllers\Api\V2\SessionController::class, 'downloadTranscription']);

        // Wallet
        Route::get('/wallet', [\App\Http\Controllers\Api\V2\WalletController::class, 'index']);
        Route::get('/wallet/packs', [\App\Http\Controllers\Api\V2\WalletController::class, 'packs']);
        Route::post('/wallet/redeem', [\App\Http\Controllers\Api\V2\WalletController::class, 'redeemCoupon']);
        Route::post('/wallet/purchase', [\App\Http\Controllers\Api\V2\WalletController::class, 'purchase']);

        // Academic Documents
        Route::get('/documents', [\App\Http\Controllers\Api\V2\AcademicDocumentController::class, 'index']);
        Route::post('/documents', [\App\Http\Controllers\Api\V2\AcademicDocumentController::class, 'upload']);
        Route::get('/documents/{id}', [\App\Http\Controllers\Api\V2\AcademicDocumentController::class, 'show']);
        Route::get('/documents/{id}/download', [\App\Http\Controllers\Api\V2\AcademicDocumentController::class, 'download']);
        Route::delete('/documents/{id}', [\App\Http\Controllers\Api\V2\AcademicDocumentController::class, 'destroy']);
        Route::get('/document-types', [\App\Http\Controllers\Api\V2\AcademicDocumentController::class, 'types']);

        // Personality Test
        Route::get('/personality/questions', [\App\Http\Controllers\Api\V2\PersonalityController::class, 'questions']);
        Route::post('/personality/submit', [\App\Http\Controllers\Api\V2\PersonalityController::class, 'submit']);
        Route::get('/personality/result/{userId?}', [\App\Http\Controllers\Api\V2\PersonalityController::class, 'result']);
        Route::get('/personality/status', [\App\Http\Controllers\Api\V2\PersonalityController::class, 'status']);

        // Mentors
        Route::get('/mentors', [\App\Http\Controllers\Api\V2\MentorController::class, 'index']);
        Route::get('/mentors/specializations', [\App\Http\Controllers\Api\V2\MentorController::class, 'specializations']);
        Route::get('/mentors/{id}', [\App\Http\Controllers\Api\V2\MentorController::class, 'show']);

        // Mentor Profile & Roadmap
        Route::get('/mentor/profile', [\App\Http\Controllers\Api\V2\MentorController::class, 'myProfile']);
        Route::post('/mentor/profile', [\App\Http\Controllers\Api\V2\MentorController::class, 'createOrUpdateProfile']);
        Route::put('/mentor/publish', [\App\Http\Controllers\Api\V2\MentorController::class, 'publish']);
        Route::post('/mentor/roadmap/step', [\App\Http\Controllers\Api\V2\MentorController::class, 'addRoadmapStep']);
        Route::put('/mentor/roadmap/step/{id}', [\App\Http\Controllers\Api\V2\MentorController::class, 'updateRoadmapStep']);
        Route::delete('/mentor/roadmap/step/{id}', [\App\Http\Controllers\Api\V2\MentorController::class, 'deleteRoadmapStep']);
        Route::post('/mentor/roadmap/reorder', [\App\Http\Controllers\Api\V2\MentorController::class, 'reorderSteps']);

        // Organizations
        Route::post('/organizations/{id}/track-click', [\App\Http\Controllers\Api\V2\OrganizationController::class, 'trackClick']);

        // Chat (orientation & conseiller)
        Route::get('/chat/conversations', [\App\Http\Controllers\Api\V2\ChatController::class, 'conversations']);
        Route::post('/chat/conversations', [\App\Http\Controllers\Api\V2\ChatController::class, 'createConversation']);
        Route::get('/chat/conversations/{id}', [\App\Http\Controllers\Api\V2\ChatController::class, 'messages']);
        Route::delete('/chat/conversations/{id}', [\App\Http\Controllers\Api\V2\ChatController::class, 'deleteConversation']);
        Route::post('/chat/conversations/{id}/request-human', [\App\Http\Controllers\Api\V2\ChatController::class, 'requestHumanSupport']);
        Route::post('/chat/conversations/{id}/cancel-human', [\App\Http\Controllers\Api\V2\ChatController::class, 'cancelHumanSupport']);
        Route::post('/chat/send', [\App\Http\Controllers\Api\V2\ChatController::class, 'send']);

        // Messages (Chat entre Jeune et Mentor)
        Route::get('/messages', [\App\Http\Controllers\Api\V2\MessagesController::class, 'index']);
        Route::get('/messages/{mentorship}', [\App\Http\Controllers\Api\V2\MessagesController::class, 'show']);
        Route::post('/messages/{mentorship}', [\App\Http\Controllers\Api\V2\MessagesController::class, 'store']);
        Route::get('/messages/file/{message}/download', [\App\Http\Controllers\Api\V2\MessagesController::class, 'download']);
        Route::patch('/messages/{message}/update', [\App\Http\Controllers\Api\V2\MessagesController::class, 'update']);
        Route::delete('/messages/{message}', [\App\Http\Controllers\Api\V2\MessagesController::class, 'destroy']);

        // Account
        Route::post('/account/archive', [\App\Http\Controllers\Api\V2\AccountController::class, 'archive']);
        Route::put('/account/password', [\App\Http\Controllers\Api\V2\AccountController::class, 'updatePassword']);

        // Quizzes
        Route::get('/quizzes/{quiz}', [\App\Http\Controllers\Api\V2\QuizController::class, 'show']);
        Route::post('/quizzes/{quiz}/submit', [\App\Http\Controllers\Api\V2\QuizController::class, 'submit']);
        Route::get('/quizzes/result/{attempt}', [\App\Http\Controllers\Api\V2\QuizController::class, 'result']);

        // User Profile
        Route::get('/user', [\App\Http\Controllers\Api\V2\AuthController::class, 'user']);
        Route::post('/user/profile', [\App\Http\Controllers\Api\V2\AuthController::class, 'updateProfile']);
        Route::post('/user/photo', [\App\Http\Controllers\Api\V2\AuthController::class, 'uploadPhoto']);
        Route::delete('/user/photo', [\App\Http\Controllers\Api\V2\AuthController::class, 'deletePhoto']);

        // Onboarding
        Route::get('/onboarding', [\App\Http\Controllers\Api\V2\OnboardingController::class, 'index']);
        Route::post('/onboarding/complete', [\App\Http\Controllers\Api\V2\OnboardingController::class, 'complete']);

        // Establishments (Recommandations & Clics)
        Route::get('/establishments/recommended', [\App\Http\Controllers\Api\V2\EstablishmentController::class, 'recommended']);
        Route::post('/establishments/{establishment}/interest-quick', [\App\Http\Controllers\Api\V2\EstablishmentController::class, 'quickInterest']);
        Route::post('/establishments/{establishment}/interest-precise', [\App\Http\Controllers\Api\V2\EstablishmentController::class, 'preciseInterest']);
        Route::post('/establishments/{establishment}/track-click', [\App\Http\Controllers\Api\V2\EstablishmentController::class, 'trackClick']);

        // Session Extra Actions
        Route::post('/sessions/unlock-history', [\App\Http\Controllers\Api\V2\SessionController::class, 'unlockHistory']);
        Route::post('/sessions/compiled-reports', [\App\Http\Controllers\Api\V2\SessionController::class, 'downloadCompiledReports']);

        // Dynamic Personality Questions
        Route::get('/personality/questions/dynamic', [\App\Http\Controllers\Api\V2\PersonalityController::class, 'dynamicQuestions']);

        // Profiling & Feedback (Nudges)
        Route::post('/feedback', [\App\Http\Controllers\Api\V2\UserProfilingController::class, 'storeFeedback']);
        Route::post('/feedback/skip', [\App\Http\Controllers\Api\V2\UserProfilingController::class, 'skipFeedback']);
        Route::post('/situation', [\App\Http\Controllers\Api\V2\UserProfilingController::class, 'storeSituation']);
        Route::post('/situation/skip', [\App\Http\Controllers\Api\V2\UserProfilingController::class, 'skipSituation']);
    });
});

/*
 |--------------------------------------------------------------------------
 | Moneroo Webhook (No CSRF Protection in API routes)
 |--------------------------------------------------------------------------
 */
Route::post('/webhooks/moneroo', [\App\Http\Controllers\MonerooWebhookController::class, 'handle'])
    ->name('api.webhooks.moneroo');

Route::post('/webhooks/jitsi', [\App\Http\Controllers\Webhook\JitsiWebhookController::class, 'handle'])
    ->name('api.webhooks.jitsi');
