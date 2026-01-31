<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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
    });
});

/*
|--------------------------------------------------------------------------
| Moneroo Webhook (No CSRF Protection in API routes)
|--------------------------------------------------------------------------
*/
Route::post('/webhooks/moneroo', [\App\Http\Controllers\MonerooWebhookController::class, 'handle'])
    ->name('api.webhooks.moneroo');
