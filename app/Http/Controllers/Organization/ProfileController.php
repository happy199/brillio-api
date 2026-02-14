<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Show the form for editing the organization profile.
     */
    public function edit()
    {
        $organization = auth()->user()->organization;
        return view('organization.profile.edit', compact('organization'));
    }

    /**
     * Update the organization profile.
     */
    public function update(Request $request)
    {
        $organization = auth()->user()->organization;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'sector' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048', // 2MB Max
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo if exists and not default
            if ($organization->logo_url && !str_contains($organization->logo_url, 'placeholder')) {
                $oldPath = str_replace('/storage/', '', $organization->logo_url);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('logo')->store('organizations/logos', 'public');
            $validated['logo_url'] = $path;
        }

        $organization->update($validated);

        return redirect()->route('organization.profile.edit')
            ->with('success', 'Profil mis à jour avec succès.');
    }
}