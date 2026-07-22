<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\Organization;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $organizations = Organization::withCount([
            'sponsoredUsers',
            'users as jeunes_count' => fn ($q) => $q->where('users.user_type', 'jeune'),
            'users as mentors_count' => fn ($q) => $q->where('users.user_type', 'mentor'),
        ])->latest()->paginate(10);

        return view('admin.organizations.index', compact('organizations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $establishments = Establishment::orderBy('name')->get();

        return view('admin.organizations.form', compact('establishments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'sector' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048', // 2MB Max
            'status' => 'required|in:active,inactive',
            'subscription_plan' => ['required', Rule::in(['free', 'pro', 'enterprise', 'establishment'])],
            'subscription_expires_at' => 'required_if:subscription_plan,pro,enterprise,establishment|nullable|date',
            'establishment_id' => 'nullable|exists:establishments,id',
        ]);

        if (isset($validated['logo'])) {
            $path = $validated['logo']->store('organizations/logos', 'public');
            $validated['logo_url'] = $path;
        }

        $organization = Organization::create($validated);

        if ($request->filled('establishment_id')) {
            Establishment::where('id', $request->establishment_id)->update(['organization_id' => $organization->id]);
        }

        return redirect()->route('admin.organizations.index')
            ->with('success', 'Organisation créée avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Organization $organization)
    {
        $organization->load(['sponsoredUsers']);

        return view('admin.organizations.show', compact('organization'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Organization $organization)
    {
        $establishments = Establishment::orderBy('name')->get();

        return view('admin.organizations.form', compact('organization', 'establishments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'sector' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'status' => 'required|in:active,inactive',
            'subscription_plan' => ['required', Rule::in(['free', 'pro', 'enterprise', 'establishment'])],
            'subscription_expires_at' => 'required_if:subscription_plan,pro,enterprise,establishment|nullable|date',
            'establishment_id' => 'nullable|exists:establishments,id',
        ]);

        if (isset($validated['logo'])) {
            // Delete old logo if exists and not default
            if ($organization->logo_url && ! str_contains($organization->logo_url, 'placeholder')) {
                $oldPath = str_replace('/storage/', '', $organization->logo_url);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $validated['logo']->store('organizations/logos', 'public');
            $validated['logo_url'] = $path;
        }

        $organization->update($validated);

        if ($request->has('establishment_id')) {
            // Unlink current establishments for this organization (main link)
            Establishment::where('organization_id', $organization->id)->update(['organization_id' => null]);

            if ($request->filled('establishment_id')) {
                Establishment::where('id', $request->establishment_id)->update(['organization_id' => $organization->id]);
            }
        }

        return redirect()->route('admin.organizations.index')
            ->with('success', 'Organisation mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organization $organization)
    {
        if ($organization->sponsoredUsers()->exists()) {
            return back()->with('error', 'Impossible de supprimer une organisation qui a des utilisateurs actifs.');
        }

        $organization->delete();

        return redirect()->route('admin.organizations.index')
            ->with('success', 'Organisation supprimée avec succès.');
    }

    /**
     * Update the credit balance of the specified organization.
     */
    public function updateCredits(Request $request, Organization $organization, WalletService $walletService)
    {
        $validated = $request->validate([
            'credit_action' => 'required|in:add,deduct,reset',
            'amount' => 'required_if:credit_action,add,deduct|nullable|integer|min:1',
        ]);

        $action = $validated['credit_action'];
        $amount = (int) ($validated['amount'] ?? 0);

        try {
            if ($action === 'add') {
                $walletService->addCredits(
                    $organization,
                    $amount,
                    'adjustment',
                    'Réévaluation de crédit par Brillio'
                );
                $message = "Solde de l'organisation augmenté de {$amount} crédits.";
            } elseif ($action === 'deduct') {
                $walletService->deductCredits(
                    $organization,
                    $amount,
                    'adjustment',
                    'Réévaluation de crédit par Brillio'
                );
                $message = "Solde de l'organisation diminué de {$amount} crédits.";
            } elseif ($action === 'reset') {
                $currentBalance = $organization->credits_balance;
                if ($currentBalance > 0) {
                    $walletService->deductCredits(
                        $organization,
                        $currentBalance,
                        'adjustment',
                        'Réévaluation de crédit par Brillio (Remise à zéro)'
                    );
                }
                $message = "Le solde de crédits de l'organisation a été vidé.";
            }

            return back()->with('success', $message ?? 'Aucune action effectuée.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la réévaluation des crédits : '.$e->getMessage());
        }
    }
}
