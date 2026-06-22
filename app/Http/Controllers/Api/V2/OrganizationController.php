<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\V1\OrganizationController as V1OrganizationController;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * Controller pour les organisations via API
 */
class OrganizationController extends V1OrganizationController
{
    /**
     * @OA\Post(
     *     path="/api/v2/organizations/{id}/track-click",
     *     summary="Enregistrer un clic sur un lien d'organisation",
     *     tags={"Organisations"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Clic enregistré avec succès")
     * )
     */
    public function trackClick(Request $request, $id)
    {
        return parent::trackClick($request, $id);
    }
}
