<?php

use App\Http\Controllers\Api\V1\AuthController;
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
    }
    );

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

        // Mentors (already existing but maybe needs better routing)
        Route::get('/mentors', [\App\Http\Controllers\Api\V1\MentorController::class, 'index']);
        Route::get('/mentors/{id}', [\App\Http\Controllers\Api\V1\MentorController::class, 'show']);

        // Chat (already existing)
        Route::get('/chat/conversations', [\App\Http\Controllers\Api\V1\ChatController::class, 'conversations']);
        Route::post('/chat/send', [\App\Http\Controllers\Api\V1\ChatController::class, 'send']);
    }
    );
});

/*
 |--------------------------------------------------------------------------
 | Moneroo Webhook (No CSRF Protection in API routes)
 |--------------------------------------------------------------------------
 */
Route::post('/webhooks/moneroo', [\App\Http\Controllers\MonerooWebhookController::class, 'handle'])
    ->name('api.webhooks.moneroo');
