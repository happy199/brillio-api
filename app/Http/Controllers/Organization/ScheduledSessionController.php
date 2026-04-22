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
                // Charger les relations nécessaires pour les notifications
                $session->load(['mentor', 'mentees', 'additionalMentors', 'organization']);
                
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

    /**
     * Formulaire d'édition d'une séance
     */
    public function edit(MentoringSession $session)
    {
        $organization = auth()->user()->organization;

        // Vérifier que la séance appartient bien à l'organisation
        if ($session->scheduled_by_organization_id !== $organization->id) {
            abort(403, "Vous n'êtes pas autorisé à modifier cette séance.");
        }

        // 1. Mentors classiques
        $standardMentors = $organization->mentors()
            ->where('users.is_guest', false)
            ->with('mentorProfile')
            ->get();

        // 2. Formateurs Invités
        $guestMentors = User::where('is_guest', true)
            ->whereHas('organizations', function ($q) use ($organization) {
                $q->where('organizations.id', $organization->id);
            })
            ->with('mentorProfile')
            ->get();

        // 3. Jeunes parrainés
        $mentees = User::where('user_type', User::TYPE_JEUNE)
            ->where('sponsored_by_organization_id', $organization->id)
            ->get();

        $session->load(['mentees', 'additionalMentors']);

        return view('organization.sessions.edit', compact('session', 'standardMentors', 'guestMentors', 'mentees', 'organization'));
    }

    /**
     * Mettre à jour la séance
     */
    public function update(Request $request, MentoringSession $session)
    {
        $organization = auth()->user()->organization;

        if ($session->scheduled_by_organization_id !== $organization->id) {
            abort(403);
        }

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
            $primaryMentorId = $validated['mentor_ids'][0];
            $additionalMentorIds = array_slice($validated['mentor_ids'], 1);

            $session->update([
                'mentor_id' => $primaryMentorId,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'scheduled_at' => $validated['scheduled_at'],
                'duration_minutes' => $validated['duration_minutes'],
            ]);

            // Sync mentors
            $session->additionalMentors()->sync($additionalMentorIds);

            // Sync mentees
            $session->mentees()->syncWithPivotValues($validated['mentee_ids'], ['status' => 'accepted']);

            DB::commit();

            // Notification de modification
            try {
                $session->load(['mentor', 'mentees', 'additionalMentors', 'organization']);
                app(\App\Services\MentorshipNotificationService::class)->sendSessionUpdated($session, auth()->user());
            } catch (\Exception $e) {
                \Log::error("Erreur notifications modification session : " . $e->getMessage());
            }

            return redirect()->route('organization.sessions.show', $session)
                ->with('success', "La séance a été modifiée avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', "Erreur lors de la modification : " . $e->getMessage());
        }
    }

    /**
     * Annuler la séance
     */
    public function cancel(Request $request, MentoringSession $session)
    {
        $organization = auth()->user()->organization;

        if ($session->scheduled_by_organization_id !== $organization->id) {
            abort(403);
        }

        $request->validate([
            'cancel_reason' => 'required|string|max:500',
        ]);

        $session->update([
            'status' => 'cancelled',
            'cancel_reason' => $request->cancel_reason,
        ]);

        // Notification d'annulation
        try {
            $session->load(['mentor', 'mentees', 'additionalMentors', 'organization']);
            app(\App\Services\MentorshipNotificationService::class)->sendSessionCancelled($session, auth()->user());
        } catch (\Exception $e) {
            \Log::error("Erreur notifications annulation session : " . $e->getMessage());
        }

        return redirect()->route('organization.sessions.calendar')
            ->with('success', "La séance a été annulée. Tous les participants ont été informés.");
    }
}
