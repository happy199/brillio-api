<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Services\CloudflareService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Show the form for editing the organization profile.
     */
    public function edit(Request $request)
    {
        $organization = $this->getCurrentOrganization();

        // Fallback for session data lost during cross-domain redirect
        if ($request->has('domain_updated') && ! session()->has('domain_updated')) {
            session()->flash('domain_updated', true);
            session()->flash('success', 'Votre espace est désormais accessible via votre propre lien personnalisé.');
        }

        return view('organization.profile.edit', compact('organization'));
    }

    /**
     * Check if a custom domain or subdomain is available.
     */
    public function checkDomainAvailability(Request $request)
    {
        $domain = $request->query('domain');
        $organization = $this->getCurrentOrganization();

        if (strlen($domain) < 2) {
            return response()->json(['available' => true]);
        }

        // Clean domain
        $domain = strtolower(trim($domain));

        // Base domain for subdomains
        $baseDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? 'brillio.africa';
        $fullSubdomain = $domain.'.'.$baseDomain;

        $exists = \App\Models\Organization::where('id', '!=', $organization->id)
            ->where(function ($query) use ($domain, $fullSubdomain) {
                $query->where('slug', $domain)
                    ->orWhere('custom_domain', $domain)
                    ->orWhere('custom_domain', $fullSubdomain);
            })
            ->exists();

        return response()->json([
            'available' => ! $exists,
            'message' => $exists ? 'Ce domaine est déjà utilisé.' : 'Disponible !',
        ]);
    }

    /**
     * Verify the DNS configuration (CNAME) for a custom domain.
     */
    public function verifyDomainDNS(Request $request)
    {
        $domain = $request->query('domain');
        $organization = $this->getCurrentOrganization();

        if (! $organization->isEnterprise()) {
            return response()->json(['success' => false, 'message' => 'Plan Enterprise requis.']);
        }

        if (empty($domain)) {
            return response()->json(['success' => false, 'message' => 'Domaine non spécifié.']);
        }

        $domain = strtolower(trim($domain));

        // Skip if it is a subdomain of the app URL (already handled by our DNS)
        $baseDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? 'brillio.africa';
        if (str_ends_with($domain, '.'.$baseDomain)) {
            return response()->json([
                'success' => true,
                'message' => 'Configuration valide (Sous-domaine Brillio détecté).',
            ]);
        }

        try {
            // Check CNAME record
            $records = dns_get_record($domain, DNS_CNAME);

            $target = $baseDomain;
            $found = false;

            foreach ($records as $record) {
                if (isset($record['target']) && (
                    $record['target'] === $target ||
                    $record['target'] === 'www.'.$target ||
                    str_contains($record['target'], $target)
                )) {
                    $found = true;
                    break;
                }
            }

            if ($found) {
                return response()->json([
                    'success' => true,
                    'message' => 'DNS configuré avec succès ! Votre domaine pointe vers Brillio.',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Aucun enregistrement CNAME trouvé pointant vers '.$target.'. Veuillez vérifier vos configurations DNS.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification DNS : '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Activate the custom domain on Cloudflare.
     */
    public function activateCustomDomain(Request $request, CloudflareService $cloudflare)
    {
        $domain = $request->input('domain');
        $organization = $this->getCurrentOrganization();

        if (! $organization->isEnterprise()) {
            return response()->json(['success' => false, 'message' => 'Plan Enterprise requis.']);
        }

        if (empty($domain)) {
            return response()->json(['success' => false, 'message' => 'Domaine non spécifié.']);
        }

        $domain = strtolower(trim($domain));

        // Skip Cloudflare registration for internal subdomains
        $baseDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? 'brillio.africa';
        if (str_ends_with($domain, '.'.$baseDomain)) {
            return response()->json([
                'success' => true,
                'message' => 'Configuration interne validée. Aucune action Cloudflare requise.',
            ]);
        }

        // Call Cloudflare API
        $result = $cloudflare->registerCustomHostname($domain);

        return response()->json($result);
    }

    /**
     * Update the organization profile.
     */
    public function update(Request $request)
    {
        $organization = $this->getCurrentOrganization();
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

        if ($organization->isEnterprise() && $request->has('custom_domain')) {
            $domain = strtolower(trim($request->input('custom_domain')));

            if (! empty($domain)) {
                // Check if it should be treated as a subdomain or root domain
                if (! str_contains($domain, '.')) {
                    $baseDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? 'brillio.africa';
                    $domain = $domain.'.'.$baseDomain;
                }
                $validated['custom_domain'] = $domain;
            } else {
                $validated['custom_domain'] = null;
            }
        }

        if ($request->hasFile('logo') && $organization->isEnterprise()) {
            // Delete old logo if exists and not default
            if ($organization->logo_url && ! str_contains($organization->logo_url, 'placeholder')) {
                $oldPath = str_replace('/storage/', '', $organization->logo_url);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('logo')->store('organizations/logos', 'public');
            $validated['logo_url'] = '/storage/'.$path;
        }

        $organization->update($validated);
        $organization->refresh();

        $domainChanged = ($organization->wasChanged('custom_domain') && ! empty($organization->custom_domain));

        if ($domainChanged) {
            $newUrl = (request()->secure() ? 'https://' : 'http://').$organization->custom_domain.(app()->environment('local') ? ':8000' : '');

            return redirect()->away($newUrl.'/organization/profile?success=1&domain_updated=1');
        }

        return redirect()->route('organization.profile.edit')
            ->with('success', 'Profil mis à jour avec succès.');
    }
}
