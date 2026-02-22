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
     * Check if a custom domain or subdomain is available.
     */
    public function checkDomainAvailability(Request $request)
    {
        $domain = $request->query('domain');
        $organization = auth()->user()->organization;

        if (strlen($domain) < 2) {
            return response()->json(['available' => true]);
        }

        // Clean domain
        $domain = strtolower(trim($domain));

        // Base domain for subdomains
        $baseDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? 'brillio.africa';
        $fullSubdomain = $domain . '.' . $baseDomain;

        $exists = \App\Models\Organization::where('id', '!=', $organization->id)
            ->where(function ($query) use ($domain, $fullSubdomain) {
            $query->where('slug', $domain)
                ->orWhere('custom_domain', $domain)
                ->orWhere('custom_domain', $fullSubdomain);
        })
            ->exists();

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'Ce domaine est déjà utilisé.' : 'Disponible !'
        ]);
    }

    /**
     * Update the organization profile.
     */
    public function update(Request $request)
    {
        $organization = auth()->user()->organization;
        $oldDomain = $organization->custom_domain;

        $rules = [
            'name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'sector' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ];

        if ($organization->isEnterprise()) {
            $rules['primary_color'] = 'nullable|string|max:7';
            $rules['secondary_color'] = 'nullable|string|max:7';
            $rules['accent_color'] = 'nullable|string|max:7';
            $rules['custom_domain'] = [
                'nullable',
                'string',
                'max:255',
                Rule::unique('organizations')->ignore($organization->id),
            ];
            $rules['logo'] = 'nullable|image|max:2048'; // 2MB Max
        }

        $validated = $request->validate($rules);

        if ($organization->isEnterprise() && !empty($validated['custom_domain'])) {
            $domain = strtolower(trim($validated['custom_domain']));

            // Normalize: if no dot, assume it's a subdomain of the current APP_URL host
            if (!str_contains($domain, '.')) {
                $baseDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? 'brillio.africa';
                $domain = $domain . '.' . $baseDomain;
            }

            $validated['custom_domain'] = $domain;
        }

        if ($request->hasFile('logo') && $organization->isEnterprise()) {
            // Delete old logo if exists and not default
            if ($organization->logo_url && !str_contains($organization->logo_url, 'placeholder')) {
                $oldPath = str_replace('/storage/', '', $organization->logo_url);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('logo')->store('organizations/logos', 'public');
            $validated['logo_url'] = '/storage/' . $path;
        }

        $organization->update($validated);

        $domainChanged = ($organization->wasChanged('custom_domain') && !empty($organization->custom_domain));

        $redirect = redirect()->route('organization.profile.edit')
            ->with('success', 'Profil mis à jour avec succès.');

        if ($domainChanged) {
            $redirect->with('domain_updated', true)
                ->with('new_url', 'http://' . $organization->custom_domain . (app()->environment('local') ? ':8000' : ''));
        }

        return $redirect;
    }
}