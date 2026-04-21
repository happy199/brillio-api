<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\MentoringSession;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ScheduledSessionController extends Controller
{
    /**
     * Formulaire de planification d'une séance
     */
    public function create()
    {
        $organization = auth()->user()->organization;

        // 1. Mentors classiques (ayant un profil mentor et liés à l'organisation)
        $standardMentors = $organization->mentors()
            ->where('is_guest', false)
            ->with('mentorProfile')
            ->get();

        // 2. Formateurs Invités (personnalités publiques sans compte complet)
        $guestMentors = User::where('is_guest', true)
            ->whereHas('organizations', function ($q) use ($organization) {
                $q->where('organizations.id', $organization->id);
            })
            ->with('mentorProfile')
            ->get();

        // 3. Récupérer les jeunes parrainés
        $mentees = User::where('user_type', User::TYPE_JEUNE)
            ->where('sponsored_by_organization_id', $organization->id)
            ->get();

        return view('organization.sessions.create', compact('standardMentors', 'guestMentors', 'mentees', 'organization'));
    }

    /**
     * Enregistrer la séance planifiée
     */
    public function store(Request $request)
    {
        $organization = auth()->user()->organization;

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'instructor_type' => 'required|in:mentor,guest',
            'mentor_ids' => 'required|array|min:1',
            'mentor_ids.*' => 'exists:users,id',
            'mentee_ids' => 'required|array|min:1',
            'mentee_ids.*' => 'exists:users,id',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|in:30,45,60,90,120',
        ]);

        DB::beginTransaction();
        try {
            // Le premier mentor de la liste devient le "Host" principal (pour compatibilité mentor_id)
            $primaryMentorId = $validated['mentor_ids'][0];
            $additionalMentorIds = array_slice($validated['mentor_ids'], 1);

            // Création de la séance
            $session = MentoringSession::create([
                'mentor_id' => $primaryMentorId,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'scheduled_at' => $validated['scheduled_at'],
                'duration_minutes' => $validated['duration_minutes'],
                'status' => 'confirmed', 
                'scheduled_by_organization_id' => $organization->id,
                'created_by' => 'organization',
                'is_paid' => false,
                'price' => 0,
            ]);

            // Attacher les mentors additionnels
            if (!empty($additionalMentorIds)) {
                $session->additionalMentors()->attach($additionalMentorIds);
            }

            // Attacher les jeunes
            foreach ($validated['mentee_ids'] as $menteeId) {
                $session->mentees()->attach($menteeId, ['status' => 'accepted']);
            }

            DB::commit();

            // Envoyer les notifications
            try {
                $notificationService = app(\App\Services\MentorshipNotificationService::class);
                $notificationService->sendSessionConfirmed($session);
            } catch (\Exception $e) {
                \Log::error("Erreur envoi notifications session planifiée : " . $e->getMessage());
            }

            return redirect()->route('organization.sessions.calendar')
                ->with('success', "La séance a été planifiée avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', "Erreur lors de la planification : " . $e->getMessage());
        }
    }
}
