<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Mentorship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MentorshipReportController extends Controller
{
    /**
     * Signaler une conversation de mentorat.
     */
    public function report(Request $request, Mentorship $mentorship)
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur fait partie de la conversation
        abort_if($mentorship->mentor_id !== $user->id && $mentorship->mentee_id !== $user->id, 403);

        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $mentorship->update([
            'reported_at' => now(),
            'reported_by_id' => $user->id,
            'report_reason' => $request->reason,
        ]);

        // Optionnel: Envoyer un email ou une notification aux admins ici

        return back()->with('success', 'La conversation a été signalée à l\'équipe de modération. Merci de votre vigilance.');
    }
}
