<?php

namespace App\Services;

use App\Models\Establishment;
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
            'gallery' => 'nullable|array',
            'gallery.*' => 'nullable|image|max:5120',
            'brochures' => 'nullable|array',
            'brochures.*' => 'nullable|mimes:pdf,doc,docx,xls,xlsx,zip|max:10240',
            'presentation_videos' => 'nullable|array|max:3',
            'presentation_videos.*' => 'nullable|url',
            'is_published' => 'sometimes|boolean',
            'has_precise_form' => 'sometimes|boolean',
            'precise_form_config' => 'nullable|array',
            'linkedin' => 'nullable|url|max:255',
            'facebook' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
        ];
    }

    /**
     * Handle the update of an establishment
     */
    public function update(Establishment $establishment, array $validated): Establishment
    {
        // 1. Photo
        if (isset($validated['photo'])) {
            if ($establishment->photo_path) {
                Storage::disk('public')->delete($establishment->photo_path);
            }
            $validated['photo_path'] = $validated['photo']->store('establishments/photos', 'public');
        }

        // 2. Gallery
        $existingGallery = $establishment->gallery ?? [];
        if (isset($validated['gallery'])) {
            foreach ($validated['gallery'] as $file) {
                $existingGallery[] = $file->store('establishments/gallery', 'public');
            }
        }
        $validated['gallery'] = $existingGallery;

        // 3. Brochures
        $existingBrochures = $establishment->brochures ?? [];
        if (isset($validated['brochures'])) {
            foreach ($validated['brochures'] as $index => $file) {
                if (isset($existingBrochures[$index])) {
                    Storage::disk('public')->delete($existingBrochures[$index]);
                }
                $existingBrochures[$index] = $file->store('establishments/brochures', 'public');
            }
        }
        $validated['brochures'] = $existingBrochures;

        // 4. Videos
        if (isset($validated['presentation_videos'])) {
            $validated['presentation_videos'] = array_values(array_filter($validated['presentation_videos']));
        }

        // 5. Checkboxes & Config
        $validated['is_published'] = filter_var($validated['is_published'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $validated['has_precise_form'] = filter_var($validated['has_precise_form'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $validated['precise_form_config'] = $validated['has_precise_form']
            ? array_values($validated['precise_form_config'] ?? [])
            : null;

        // 6. Social Links
        $validated['social_links'] = [
            'linkedin' => $validated['linkedin'] ?? null,
            'facebook' => $validated['facebook'] ?? null,
            'instagram' => $validated['instagram'] ?? null,
        ];

        $establishment->update($validated);

        return $establishment;
    }

    /**
     * Handle the creation of an establishment (shared parts with update)
     */
    public function store(array $validated): Establishment
    {
        if (isset($validated['photo'])) {
            $validated['photo_path'] = $validated['photo']->store('establishments/photos', 'public');
        }

        if (isset($validated['gallery'])) {
            $galleryPaths = [];
            foreach ($validated['gallery'] as $file) {
                $galleryPaths[] = $file->store('establishments/gallery', 'public');
            }
            $validated['gallery'] = $galleryPaths;
        }

        if (isset($validated['brochures'])) {
            $brochurePaths = [];
            foreach ($validated['brochures'] as $file) {
                $brochurePaths[] = $file->store('establishments/brochures', 'public');
            }
            $validated['brochures'] = $brochurePaths;
        }

        if (isset($validated['presentation_videos'])) {
            $validated['presentation_videos'] = array_values(array_filter($validated['presentation_videos']));
        }

        $validated['is_published'] = filter_var($validated['is_published'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $validated['has_precise_form'] = filter_var($validated['has_precise_form'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $validated['precise_form_config'] = $validated['has_precise_form']
            ? array_values($validated['precise_form_config'] ?? [])
            : null;

        $validated['social_links'] = [
            'linkedin' => $validated['linkedin'] ?? null,
            'facebook' => $validated['facebook'] ?? null,
            'instagram' => $validated['instagram'] ?? null,
        ];

        return Establishment::create($validated);
    }
}
