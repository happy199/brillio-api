<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Mail\OrganizationInvitationMail;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InvitationController extends Controller
{
    /**
     * Get current organization for authenticated user
     */

    /**
     * Display a listing of invitations
     */
    public function index()
    {
        $organization = $this->getCurrentOrganization();

        $invitations = $organization->invitations()
            ->latest()
            ->paginate(20);

        return view('organization.invitations.index', compact('organization', 'invitations'));
    }

    /**
     * Show the form for creating a new invitation
     */
    public function create()
    {
        $organization = $this->getCurrentOrganization();

        return view('organization.invitations.create', compact('organization'));
    }

    /**
     * Store a newly created invitation
     */
    public function store(Request $request)
    {
        $organization = $this->getCurrentOrganization();

        $validated = $request->validate([
            'invited_emails' => ['nullable', 'string'],
            'expires_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'role' => ['nullable', 'string', 'in:jeune,mentor,standard'],
        ]);

        // Parse emails (one per line or comma-separated)
        $emailsString = $validated['invited_emails'] ?? '';
        $emails = [];

        if (! empty($emailsString)) {
            // Split by newlines and commas
            $rawEmails = preg_split('/[\n,]+/', $emailsString, -1, PREG_SPLIT_NO_EMPTY);

            // Clean and validate each email
            foreach ($rawEmails as $email) {
                $email = trim($email);
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $email;
                }
            }
        }

        $expiresDays = (int) ($validated['expires_days'] ?? 30);
        $role = $validated['role'] ?? 'standard';

        $createdInvitations = [];

        // If emails provided, create one invitation per email
        if (! empty($emails)) {
            foreach ($emails as $email) {
                $invitation = $organization->invitations()->create([
                    'invited_email' => $email,
                    'role' => $role,
                    'status' => 'pending',
                    'invited_at' => now(),
                    'expires_at' => now()->addDays($expiresDays),
                ]);
                $createdInvitations[] = $invitation;

                // Send invitation email
                try {
                    $registrationUrl = route('auth.choice', ['ref' => $invitation->referral_code]);

                    Mail::to($email)->send(new OrganizationInvitationMail($organization, $invitation, $registrationUrl));
                } catch (\Exception $e) {
                    Log::error('Erreur envoi email invitation: '.$e->getMessage(), [
                        'email' => $email,
                        'invitation_id' => $invitation->id,
                    ]);
                }
            }

            $count = count($createdInvitations);

            return redirect()->route('organization.invitations.index')
                ->with('success', "{$count} invitation(s) créée(s) avec succès ! Les emails ont été envoyés.");
        } else {
            // No emails, create a single shareable invitation
            $invitation = $organization->invitations()->create([
                'invited_email' => null,
                'role' => $role,
                'status' => 'pending',
                'invited_at' => now(),
                'expires_at' => now()->addDays($expiresDays),
            ]);

            // Get invitation URL
            $invitationUrl = route('auth.choice', ['ref' => $invitation->referral_code]);

            return redirect()->route('organization.invitations.index')
                ->with('success', 'Invitation créée avec succès !')
                ->with('invitation_url', $invitationUrl);
        }
    }

    /**
     * Remove the specified invitation
     */
    public function destroy(OrganizationInvitation $invitation)
    {
        $organization = $this->getCurrentOrganization();

        // Ensure invitation belongs to this organization
        if ($invitation->organization_id !== $organization->id) {
            abort(403, 'Accès non autorisé');
        }

        $invitation->delete();

        return redirect()->route('organization.invitations.index')
            ->with('success', 'Invitation supprimée avec succès.');
    }
}
