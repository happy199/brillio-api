<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommercialActivity;
use App\Models\MentorProfile;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;

class CommercialActivityController extends Controller
{
    public function index(Request $request)
    {
        $activities = CommercialActivity::with(['commercial', 'assignable'])
            ->latest()
            ->paginate(20);

        return view('admin.commercials.activities', compact('activities'));
    }

    public function takeCharge(Request $request)
    {
        $request->validate([
            'type' => 'required|in:user,mentor,organization',
            'id' => 'required|integer',
        ]);

        $modelClass = match ($request->type) {
            'user' => User::class,
            'mentor' => MentorProfile::class,
            'organization' => Organization::class,
        };

        // Ensure not already taken by someone active
        $existing = CommercialActivity::where('assignable_type', $modelClass)
            ->where('assignable_id', $request->id)
            ->where('status', 'active')
            ->first();

        if ($existing) {
            return back()->with('error', 'Ce dossier est déjà pris en charge par '.$existing->commercial->name);
        }

        CommercialActivity::create([
            'commercial_id' => auth()->id(),
            'assignable_type' => $modelClass,
            'assignable_id' => $request->id,
            'status' => 'active',
            'started_at' => now(),
        ]);

        return back()->with('success', 'Dossier pris en charge avec succès.');
    }

    public function endCharge(Request $request, CommercialActivity $activity)
    {
        if ($activity->commercial_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            return back()->with('error', 'Non autorisé.');
        }

        $request->validate([
            'summary' => 'required|string|min:10',
        ]);

        $activity->update([
            'status' => 'closed',
            'summary' => $request->summary,
            'ended_at' => now(),
        ]);

        return back()->with('success', 'Dossier clôturé avec succès.');
    }
}
