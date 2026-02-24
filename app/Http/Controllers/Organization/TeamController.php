<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeamController extends Controller
{
    public function index()
    {
        $organization = $this->getCurrentOrganization();

        // Members linked to this organization with admin or viewer role
        // excluding the current user if they are the owner
        $members = $organization->users()
            ->wherePivotIn('role', ['admin', 'viewer'])
            ->get();

        return view('organization.team.index', compact('organization', 'members'));
    }

    public function create()
    {
        $organization = $this->getCurrentOrganization();

        return view('organization.team.create', compact('organization'));
    }

    public function store(Request $request)
    {
        $organization = $this->getCurrentOrganization();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'role' => ['required', 'string', 'in:admin,viewer'],
        ]);

        $password = Str::random(12);

        return DB::transaction(function () use ($organization, $validated, $password) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($password),
                'user_type' => User::TYPE_ORGANIZATION,
                'organization_id' => $organization->id,
                'organization_role' => $validated['role'],
                'email_verified_at' => now(),
            ]);

            // Link to organization pivot
            $user->organizations()->attach($organization->id, [
                'role' => $validated['role'],
            ]);

            return redirect()->route('organization.team.index')
                ->with('success', 'Membre de l\'équipe ajouté avec succès.')
                ->with('new_user_data', [
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => $password,
                    'role' => $validated['role'] === 'admin' ? 'Administrateur' : 'Observateur',
                ]);
        });
    }

    public function destroy(User $user)
    {
        $organization = $this->getCurrentOrganization();

        // Security: ensure the user belongs to the organization
        if (! $organization->users()->where(function ($q) use ($user) {
            $q->where('users.id', $user->id);
        })->exists()) {
            abort(403);
        }

        // Prevent self-deletion if the current user is in the list
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas vous supprimer vous-même.');
        }

        // Detach from organization
        $user->organizations()->detach($organization->id);

        // Clear primary organization link if it matches the one being removed
        if ($user->organization_id === $organization->id) {
            $user->update(['organization_id' => null]);
        }

        // If the user has no other organizations and is of type organization, we could delete it,
        // but it's safer to just detach for now.
        if ($user->organizations()->count() === 0 && $user->user_type === User::TYPE_ORGANIZATION) {
            $user->delete();
        }

        return redirect()->route('organization.team.index')
            ->with('success', 'Membre supprimé de l\'équipe.');
    }
}
