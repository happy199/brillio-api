<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Mentorship;
use App\Models\Message;
use App\Models\Organization;
use Illuminate\Support\Facades\Storage;

class ConversationController extends Controller
{
    /**
     * List all conversations (mentorships) involving BOTH sponsored jeunes AND linked mentors.
     */
    public function index()
    {
        $organization = $this->getCurrentOrganization();

        // Get IDs of currently sponsored jeunes
        $sponsoredJeuneIds = $organization->sponsoredUsers()
            ->where(fn ($q) => $q->where('user_type', 'jeune'))
            ->pluck('id');

        // Get IDs of currently linked mentors
        $linkedMentorIds = $organization->mentors()
            ->pluck('users.id');

        // Fetch mentorships involving BOTH linked participants
        $mentorships = Mentorship::whereIn('mentee_id', $sponsoredJeuneIds)
            ->whereIn('mentor_id', $linkedMentorIds)
            ->with(['mentor', 'mentee', 'messages' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->whereIn('status', ['accepted', 'disconnected', 'validated'])
            ->get();

        return view('organization.conversations.index', compact('mentorships', 'organization'));
    }

    /**
     * Display a specific conversation in read-only mode.
     */
    public function show(Mentorship $mentorship)
    {
        $organization = $this->getCurrentOrganization();

        if (! $organization->isEnterprise()) {
            abort(403, 'Cette fonctionnalité est réservée aux comptes Entreprise.');
        }

        // Security: Ensure the organization is linked to BOTH participants
        $isMenteeSponsored = $mentorship->mentee->sponsored_by_organization_id === $organization->id;
        $isMentorLinked = $organization->mentors()->where(fn ($q) => $q->where('users.id', $mentorship->mentor_id))->exists();

        if (! $isMenteeSponsored || ! $isMentorLinked) {
            abort(403, "Vous n'avez pas accès à cette conversation car l'un des participants n'est plus lié à votre organisation.");
        }

        $mentorship->load(['mentor', 'mentee', 'messages.sender']);
        $messages = $mentorship->messages()->orderBy('created_at', 'asc')->get();

        return view('organization.conversations.show', compact('mentorship', 'messages', 'organization'));
    }

    /**
     * Download an attachment from a conversation.
     */
    public function download(Message $message)
    {
        $organization = $this->getCurrentOrganization();

        if (! $organization->isEnterprise()) {
            abort(403, 'Cette fonctionnalité est réservée aux comptes Entreprise.');
        }

        $mentorship = $message->mentorship;

        // Security: Ensure the organization is linked to BOTH participants
        $isMenteeSponsored = $mentorship->mentee->sponsored_by_organization_id === $organization->id;
        $isMentorLinked = $organization->mentors()->where(fn ($q) => $q->where('users.id', $mentorship->mentor_id))->exists();

        if (! $isMenteeSponsored || ! $isMentorLinked) {
            abort(403, "Vous n'avez pas accès à ce fichier.");
        }

        if (! $message->hasAttachment()) {
            abort(404, 'Ce message ne contient aucune pièce jointe.');
        }

        return Storage::disk('local')->download($message->attachment_path, $message->attachment_name);
    }
}
