<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\EmailSuppression;
use App\Models\ScheduledTaskLog;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function emails(Request $request)
    {
        $query = EmailLog::query();

        if ($request->filled('search')) {
            $query->where('to', 'like', '%'.$request->search.'%')
                ->orWhere('subject', 'like', '%'.$request->search.'%');
        }

        $logs = $query->latest('sent_at')->paginate(20);

        return view('admin.audits.emails', compact('logs'));
    }

    public function crons(Request $request)
    {
        $query = ScheduledTaskLog::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('command', 'like', '%'.$request->search.'%');
        }

        $logs = $query->latest('run_at')->paginate(20);

        return view('admin.audits.crons', compact('logs'));
    }

    /**
     * Liste des adresses emails exclues (Blacklist / Anti-bounce)
     */
    public function suppressions(Request $request)
    {
        $query = EmailSuppression::with('creator');

        if ($request->filled('search')) {
            $query->where('email', 'like', '%'.$request->search.'%')
                ->orWhere('reason', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        $suppressions = $query->latest()->paginate(20);

        return view('admin.audits.suppressions', compact('suppressions'));
    }

    /**
     * Ajout manuel d'une adresse email dans la liste d'exclusion
     */
    public function storeSuppression(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'reason' => 'required|string|max:255',
        ], [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'reason.required' => 'La raison de l\'exclusion est obligatoire.',
        ]);

        EmailSuppression::updateOrCreate(
            ['email' => strtolower(trim($validated['email']))],
            [
                'reason' => $validated['reason'],
                'source' => 'admin_manual',
                'created_by' => auth()->id(),
            ]
        );

        return redirect()->back()->with('success', 'Adresse email ajoutée à la liste d\'exclusion avec succès.');
    }

    /**
     * Retrait d'une adresse email de la liste d'exclusion (déblocage d'envoi)
     */
    public function destroySuppression(EmailSuppression $suppression)
    {
        $email = $suppression->email;
        $suppression->delete();

        return redirect()->back()->with('success', "L'adresse email {$email} a été retirée de la liste d'exclusion. Les envois sont réactivés.");
    }
}
