<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\EstablishmentClick;
use Illuminate\Http\Request;

/**
 * Controller pour les organisations via API (V1)
 */
class OrganizationController extends Controller
{
    /**
     * Enregistre un clic sur un établissement
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
