<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\PersonalityTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EstablishmentController extends Controller
{
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

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'country' => 'required|string',
            'city' => 'nullable|string',
            'description' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'website_url' => 'nullable|url',
            'google_maps_url' => 'nullable|url',
            'mbti_types' => 'nullable|array',
            'sectors' => 'nullable|array',
            'tuition_min' => 'nullable|numeric',
            'tuition_max' => 'nullable|numeric',
            'photo' => 'nullable|image|max:2048',
            'gallery.*' => 'nullable|image|max:5120',
            'brochures.*' => 'nullable|mimes:pdf,doc,docx,xls,xlsx,zip|max:10240',
            'presentation_videos' => 'nullable|array|max:3',
            'presentation_videos.*' => 'nullable|url',
        ]);

        if ($request->hasFile('photo')) {
            if ($establishment->photo_path) {
                Storage::disk('public')->delete($establishment->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('establishments/photos', 'public');
        }

        $existingGallery = $establishment->gallery ?? [];
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                $existingGallery[] = $file->store('establishments/gallery', 'public');
            }
        }
        $validated['gallery'] = $existingGallery;

        $existingBrochures = $establishment->brochures ?? [];
        if ($request->hasFile('brochures')) {
            foreach ($request->file('brochures') as $index => $file) {
                if (isset($existingBrochures[$index])) {
                    Storage::disk('public')->delete($existingBrochures[$index]);
                }
                $existingBrochures[$index] = $file->store('establishments/brochures', 'public');
            }
        }
        $validated['brochures'] = $existingBrochures;

        if ($request->has('presentation_videos')) {
            $validated['presentation_videos'] = array_values(array_filter($request->input('presentation_videos')));
        }

        $validated['is_published'] = $request->has('is_published');
        $validated['has_precise_form'] = $request->has('has_precise_form');
        $validated['precise_form_config'] = $request->has('has_precise_form')
            ? array_values($request->input('precise_form_config', []))
            : null;

        $validated['social_links'] = [
            'linkedin' => $request->input('linkedin'),
            'facebook' => $request->input('facebook'),
            'instagram' => $request->input('instagram'),
        ];

        $establishment->update($validated);

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
