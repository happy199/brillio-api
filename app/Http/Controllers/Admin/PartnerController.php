<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PartnerController extends Controller
{
    /**
     * Display a listing of partners (both external and Brillio organizations with logos).
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'tab' => 'nullable|string|in:all,external,organizations',
        ]);

        $search = $validated['search'] ?? null;
        $tab = $validated['tab'] ?? 'all';

        // 1. External Partners
        $externalPartnersQuery = Partner::ordered();
        if ($search) {
            $externalPartnersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('website', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }
        $externalPartners = $externalPartnersQuery->get();

        // 2. Brillio Organizations with logo (and public)
        $orgPartnersQuery = Organization::active()
            ->whereNotNull('logo_url')
            ->where('private_circle_plus_enabled', false);

        if ($search) {
            $orgPartnersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('website', 'like', "%{$search}%")
                    ->orWhere('contact_email', 'like', "%{$search}%");
            });
        }
        $organizationPartners = $orgPartnersQuery->orderBy('name')->get();

        // 3. Stats for dashboard cards
        $totalActiveExternal = Partner::active()->count();
        $totalOrgs = Organization::active()
            ->whereNotNull('logo_url')
            ->where('private_circle_plus_enabled', false)
            ->count();
        $totalActivePartners = $totalActiveExternal + $totalOrgs;
        $totalExternalPartners = Partner::count();

        return view('admin.partners.index', compact(
            'externalPartners',
            'organizationPartners',
            'totalActivePartners',
            'totalActiveExternal',
            'totalExternalPartners',
            'totalOrgs',
            'tab',
            'search'
        ));
    }

    /**
     * Show the form for creating a new external partner.
     */
    public function create()
    {
        return view('admin.partners.form');
    }

    /**
     * Store a newly created external partner in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'website' => 'nullable|url|max:255',
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'display_order' => 'nullable|integer|min:0',
            'type' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->hasFile('logo') && isset($validated['logo'])) {
            $path = $validated['logo']->store('partners/logos', 'public');
            $validated['logo_path'] = $path;
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['display_order'] = $validated['display_order'] ?? 0;
        $validated['type'] = $validated['type'] ?? 'external';

        Partner::create($validated);

        return redirect()->route('admin.partners.index', ['tab' => 'external'])
            ->with('success', 'Partenaire ajouté avec succès.');
    }

    /**
     * Show the form for editing the specified partner.
     */
    public function edit(Partner $partner)
    {
        return view('admin.partners.form', compact('partner'));
    }

    /**
     * Update the specified partner in storage.
     */
    public function update(Request $request, Partner $partner)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'display_order' => 'nullable|integer|min:0',
            'type' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->hasFile('logo') && isset($validated['logo'])) {
            // Delete old logo if exists
            if ($partner->logo_path && ! str_starts_with($partner->logo_path, 'http')) {
                Storage::disk('public')->delete($partner->logo_path);
            }

            $path = $validated['logo']->store('partners/logos', 'public');
            $validated['logo_path'] = $path;
        }

        $validated['is_active'] = $request->boolean('is_active', false);
        $validated['display_order'] = $validated['display_order'] ?? 0;
        $validated['type'] = $validated['type'] ?? 'external';

        $partner->update($validated);

        return redirect()->route('admin.partners.index', ['tab' => 'external'])
            ->with('success', 'Partenaire mis à jour avec succès.');
    }

    /**
     * Toggle active status of an external partner.
     */
    public function toggleStatus(Partner $partner)
    {
        $partner->is_active = ! $partner->is_active;
        $partner->save();

        $statusText = $partner->is_active ? 'activé (visible sur la page À propos)' : 'désactivé (masqué)';

        return back()->with('success', "Partenaire « {$partner->name} » {$statusText}.");
    }

    /**
     * Remove the specified partner from storage.
     */
    public function destroy(Partner $partner)
    {
        if ($partner->logo_path && ! str_starts_with($partner->logo_path, 'http')) {
            Storage::disk('public')->delete($partner->logo_path);
        }

        $partner->delete();

        return redirect()->route('admin.partners.index', ['tab' => 'external'])
            ->with('success', 'Partenaire supprimé avec succès.');
    }
}
