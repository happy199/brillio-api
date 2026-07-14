<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\PersonalityTest;
use App\Services\EstablishmentService;
use Illuminate\Http\Request;

class EstablishmentController extends Controller
{
    protected $establishmentService;

    public function __construct(EstablishmentService $establishmentService)
    {
        $this->establishmentService = $establishmentService;
    }

    /**
     * Show the form for editing the organization's establishment profile.
     */
    public function edit(Request $request)
    {
        $organization = auth()->user()->organization;

        // For now, we assume one organization edits its first establishment
        // If there are multiple, we could add an ID parameter, but the request implies "the" card shown to youth.
        $establishment = $organization->establishments()->first();

        if (! $establishment) {
            // Create one if it doesn't exist? Or redirect with error.
            // In theory, if they are on the promotion page, they should have one.
            return redirect()->route('organization.promotion.index')->with('error', "Aucun établissement associé à votre organisation n'a été trouvé.");
        }

        $mbtiTypes = array_keys(PersonalityTest::PERSONALITY_TYPES);

        return view('organization.establishments.edit', compact('establishment', 'mbtiTypes'));
    }

    /**
     * Update the establishment profile.
     */
    public function update(Request $request, Establishment $establishment)
    {
        // Ensure the establishment belongs to the user's organization
        if ($establishment->organization_id !== auth()->user()->organization_id) {
            abort(403);
        }

        $validated = $request->validate($this->establishmentService::validationRules());

        $this->establishmentService->update($establishment, $validated);

        return redirect()->route('organization.promotion.index')->with('success', 'La fiche de votre établissement a été mise à jour avec succès.');
    }

    /**
     * Boost the establishment profile (Placeholder for credit-based highlight feature).
     */
    public function boost(Request $request, Establishment $establishment)
    {
        // Ensure the establishment belongs to the user's organization
        if ($establishment->organization_id !== auth()->user()->organization_id) {
            abort(403);
        }

        // Feature to be implemented: deduct credits and set a "boosted_until" date
        return back()->with('info', 'La fonctionnalité de boost sera disponible prochainement. Elle vous permettra d\'utiliser vos crédits pour mettre en avant votre établissement.');
    }
}
