<?php

namespace App\Services;

use App\Models\Establishment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EstablishmentService
{
    /**
     * Common validation rules for both Admin and Organization
     */
    public static function validationRules(): array
    {
        return [
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
        ];
    }

    /**
     * Handle the update of an establishment
     */
    public function update(Establishment $establishment, array $validated, Request $request): Establishment
    {
        // 1. Photo
        if ($request->hasFile('photo')) {
            if ($establishment->photo_path) {
                Storage::disk('public')->delete($establishment->photo_path);
            }
// nosemgrep
            $validated['photo_path'] = $request->file('photo')->store('establishments/photos', 'public');
        }

        // 2. Gallery
        $existingGallery = $establishment->gallery ?? [];
        if ($request->hasFile('gallery')) {
// nosemgrep
            foreach ($request->file('gallery') as $file) {
                $existingGallery[] = $file->store('establishments/gallery', 'public');
            }
        }
        $validated['gallery'] = $existingGallery;

        // 3. Brochures
        $existingBrochures = $establishment->brochures ?? [];
        if ($request->hasFile('brochures')) {
// nosemgrep
            foreach ($request->file('brochures') as $index => $file) {
                if (isset($existingBrochures[$index])) {
                    Storage::disk('public')->delete($existingBrochures[$index]);
                }
                $existingBrochures[$index] = $file->store('establishments/brochures', 'public');
            }
        }
        $validated['brochures'] = $existingBrochures;

        // 4. Videos
        if ($request->has('presentation_videos')) {
// nosemgrep
            $validated['presentation_videos'] = array_values(array_filter($request->input('presentation_videos')));
        }

        // 5. Checkboxes & Config
        $validated['is_published'] = $request->has('is_published');
        $validated['has_precise_form'] = $request->has('has_precise_form');
        $validated['precise_form_config'] = $request->has('has_precise_form')
// nosemgrep
            ? array_values($request->input('precise_form_config', []))
            : null;

        // 6. Social Links
        $validated['social_links'] = [
// nosemgrep
            'linkedin' => $request->input('linkedin'),
// nosemgrep
            'facebook' => $request->input('facebook'),
// nosemgrep
            'instagram' => $request->input('instagram'),
        ];

        $establishment->update($validated);

        return $establishment;
    }

    /**
     * Handle the creation of an establishment (shared parts with update)
     */
    public function store(array $validated, Request $request): Establishment
    {
        if ($request->hasFile('photo')) {
// nosemgrep
            $validated['photo_path'] = $request->file('photo')->store('establishments/photos', 'public');
        }

        if ($request->hasFile('gallery')) {
            $galleryPaths = [];
// nosemgrep
            foreach ($request->file('gallery') as $file) {
                $galleryPaths[] = $file->store('establishments/gallery', 'public');
            }
            $validated['gallery'] = $galleryPaths;
        }

        if ($request->hasFile('brochures')) {
            $brochurePaths = [];
// nosemgrep
            foreach ($request->file('brochures') as $file) {
                $brochurePaths[] = $file->store('establishments/brochures', 'public');
            }
            $validated['brochures'] = $brochurePaths;
        }

        if (isset($validated['presentation_videos'])) {
            $validated['presentation_videos'] = array_values(array_filter($validated['presentation_videos']));
        }

        $validated['is_published'] = $request->has('is_published');
        $validated['has_precise_form'] = $request->has('has_precise_form');
        $validated['precise_form_config'] = $request->has('has_precise_form')
// nosemgrep
            ? array_values($request->input('precise_form_config', []))
            : null;

        $validated['social_links'] = [
// nosemgrep
            'linkedin' => $request->input('linkedin'),
// nosemgrep
            'facebook' => $request->input('facebook'),
// nosemgrep
            'instagram' => $request->input('instagram'),
        ];

        return Establishment::create($validated);
    }
}
