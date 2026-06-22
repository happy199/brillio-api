<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\EstablishmentClick;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * Controller pour les organisations via API
 */
class OrganizationController extends Controller
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
        $establishment = Establishment::findOrFail($id);

        EstablishmentClick::create([
            'establishment_id' => $establishment->id,
            'user_id' => $request->user()?->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['message' => 'Click tracked successfully']);
    }
}
