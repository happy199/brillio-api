<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Controller de base avec méthodes utilitaires pour les réponses API
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Réponse de succès standardisée
     */
    protected function success($data = null, string $message = 'Success', int $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Réponse d'erreur standardisée
     */
    protected function error(string $message = 'Error', int $code = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Réponse pour les ressources créées
     */
    protected function created($data = null, string $message = 'Resource created successfully')
    {
        return $this->success($data, $message, 201);
    }

    /**
     * Réponse pour les ressources non trouvées
     */
    protected function notFound(string $message = 'Resource not found')
    {
        return $this->error($message, 404);
    }

    /**
     * Réponse pour les erreurs de validation
     */
    protected function validationError($errors, string $message = 'Validation failed')
    {
        return $this->error($message, 422, $errors);
    }

    /**
     * Réponse pour les erreurs d'authentification
     */
    protected function unauthorized(string $message = 'Unauthorized')
    {
        return $this->error($message, 401);
    }

    /**
     * Réponse pour les accès interdits
     */
    protected function forbidden(string $message = 'Forbidden')
    {
        return $this->error($message, 403);
    }
}
