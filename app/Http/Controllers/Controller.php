<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Brillio API',
    version: '1.0.0',
    description: 'API Backend for Brillio Orientation System',
    contact: new OA\Contact(email: 'contact@brillio.africa')
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: 'Brillio API Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
/**
 * Controller de base avec méthodes utilitaires pour les réponses API
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * @OA\Get(
     *     path="/api/health",
     *     summary="Porte de santé de l'API",
     *     tags={"Système"},
     *
     *     @OA\Response(
     *         response=200,
     *         description="L'API est opérationnelle",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="ok"),
     *             @OA\Property(property="timestamp", type="string", format="date-time")
     *         )
     *     )
     * )
     *
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

    /**
     * Get current organization for authenticated user
     */
    protected function getCurrentOrganization(): \App\Models\Organization
    {
        $user = auth()->user();

        if (! $user || ! $user->isOrganization()) {
            abort(403, 'Accès réservé aux organisations.');
        }

        // 1. Direct link (Primary organization)
        if ($user->organization_id) {
            $organization = $user->organization;
            if ($organization && $user->organizations()->where(function ($q) use ($organization) {
                $q->where('organizations.id', $organization->id);
            })->exists()) {
                return $organization;
            }
        }

        // 2. Legacy / Owner check (Matching contact email)
        $organization = \App\Models\Organization::where('contact_email', $user->email)->first();
        if ($organization) {
            // Auto-fix: link user to organization with admin role
            $user->organizations()->syncWithoutDetaching([
                $organization->id => ['role' => 'admin'],
            ]);

            // Update user model for future requests
            $user->update([
                'organization_id' => $organization->id,
                'organization_role' => 'admin',
            ]);

            return $organization;
        }

        // 3. Check domain resolution (from ResolveOrganizationByDomain middleware)
        if (app()->bound('current_organization')) {
            $resolvedOrg = app('current_organization');
            if ($resolvedOrg && $user->organizations()->where(function ($q) use ($resolvedOrg) {
                $q->where('organizations.id', $resolvedOrg->id);
            })->exists()) {
                return $resolvedOrg;
            }
        }

        // 4. Fallback to first linked organization
        $firstOrg = $user->organizations()->first();
        if ($firstOrg) {
            $user->update([
                'organization_id' => $firstOrg->id,
                'organization_role' => $firstOrg->pivot->role ?? 'jeune',
            ]);

            return $firstOrg;
        }

        abort(404, 'Organisation non trouvée.');
    }
}
