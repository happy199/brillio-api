<?php

use App\Http\Controllers\Api\V1\AcademicDocumentController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ChatController;
use App\Http\Controllers\Api\V1\MentorController;
use App\Http\Controllers\Api\V1\PersonalityController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Version 1
|--------------------------------------------------------------------------
|
| Routes API pour l'application mobile Brillio
| Toutes les routes sont préfixées par /api/v1
|
*/

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Routes Publiques (sans authentification)
    |--------------------------------------------------------------------------
    */

    // Authentification
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
    });

    // Liste des mentors publiés (accessible publiquement)
    Route::get('mentors', [MentorController::class, 'index']);
    Route::get('mentors/{id}', [MentorController::class, 'show']);

    // Référentiels publics
    Route::get('specializations', [MentorController::class, 'specializations']);
    Route::get('document-types', [AcademicDocumentController::class, 'types']);

    /*
    |--------------------------------------------------------------------------
    | Routes Protégées (avec authentification Sanctum)
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->group(function () {

        // === AUTHENTIFICATION ===
        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('user', [AuthController::class, 'user']);
            Route::put('profile', [AuthController::class, 'updateProfile']);
            Route::post('upload-photo', [AuthController::class, 'uploadPhoto']);
            Route::delete('delete-photo', [AuthController::class, 'deletePhoto']);
        });

        // === TEST DE PERSONNALITÉ ===
        Route::prefix('personality')->group(function () {
            Route::get('questions', [PersonalityController::class, 'questions']);
            Route::post('submit', [PersonalityController::class, 'submit']);
            Route::get('status', [PersonalityController::class, 'status']);
            Route::get('result', [PersonalityController::class, 'result']);
            Route::get('result/{userId}', [PersonalityController::class, 'result']);
        });

        // === CHATBOT IA ===
        Route::prefix('chat')->group(function () {
            Route::get('conversations', [ChatController::class, 'conversations']);
            Route::post('conversations', [ChatController::class, 'createConversation']);
            Route::get('conversations/{conversationId}/messages', [ChatController::class, 'messages']);
            Route::delete('conversations/{conversationId}', [ChatController::class, 'deleteConversation']);
            Route::post('send', [ChatController::class, 'send']);
        })->middleware('throttle:chat'); // Rate limiting spécifique pour le chat

        // === PROFIL MENTOR (pour les mentors) ===
        Route::prefix('mentor')->group(function () {
            Route::get('profile', [MentorController::class, 'myProfile']);
            Route::post('profile', [MentorController::class, 'createOrUpdateProfile']);
            Route::put('publish', [MentorController::class, 'publish']);

            // Roadmap
            Route::post('roadmap/step', [MentorController::class, 'addRoadmapStep']);
            Route::put('roadmap/step/{stepId}', [MentorController::class, 'updateRoadmapStep']);
            Route::delete('roadmap/step/{stepId}', [MentorController::class, 'deleteRoadmapStep']);
            Route::put('roadmap/reorder', [MentorController::class, 'reorderSteps']);
        });

        // === DOCUMENTS ACADÉMIQUES ===
        Route::prefix('academic')->group(function () {
            Route::get('documents', [AcademicDocumentController::class, 'index']);
            Route::post('upload', [AcademicDocumentController::class, 'upload']);
            Route::get('documents/{id}', [AcademicDocumentController::class, 'show']);
            Route::get('documents/{id}/download', [AcademicDocumentController::class, 'download'])
                ->name('api.academic.download');
            Route::delete('documents/{id}', [AcademicDocumentController::class, 'destroy']);
        });
    });
});

/*
|--------------------------------------------------------------------------
| Fallback Route
|--------------------------------------------------------------------------
*/

Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Endpoint non trouvé. Consultez la documentation API.',
    ], 404);
});
