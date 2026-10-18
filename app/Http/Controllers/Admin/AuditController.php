<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CronAutomation;
use App\Models\EmailLog;
use App\Models\EmailSuppression;
use App\Models\ScheduledTaskLog;
use App\Services\BounceProcessingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuditController extends Controller
{
    /**
     * Historique des e-mails envoyés par l'application
     */
    public function emails(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:100',
        ]);

        $query = EmailLog::query();

        if (! empty($validated['search'])) {
            $searchTerm = '%'.$validated['search'].'%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('to', 'like', $searchTerm)
                    ->orWhere('subject', 'like', $searchTerm);
            });
        }

        $logs = $query->latest('sent_at')->paginate(20)->withQueryString();

        return view('admin.audits.emails', compact('logs'));
    }

    /**
     * Tableau de bord des automatisations & chronologie des tâches planifiées (CRONs)
     */
    public function crons(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:100',
            'category' => 'nullable|string|in:all,emails,mentorship,security,system,organizations',
            'status' => 'nullable|string|in:all,active,paused',
            'tab' => 'nullable|string|in:automations,logs',
            'log_search' => 'nullable|string|max:100',
            'log_status' => 'nullable|string|in:all,success,failed',
        ]);

        // Synchroniser automatiquement le dictionnaire canonique des automatisations
        CronAutomation::syncAutomations();

        // Récupérer et filtrer les automatisations utilisateur
        $autoQuery = CronAutomation::query();

        if (! empty($validated['category']) && $validated['category'] !== 'all') {
            $autoQuery->where('category', $validated['category']);
        }

        if (! empty($validated['status']) && $validated['status'] !== 'all') {
            $autoQuery->where('is_enabled', $validated['status'] === 'active');
        }

        if (! empty($validated['search'])) {
            $searchTerm = '%'.$validated['search'].'%';
            $autoQuery->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm)
                    ->orWhere('target', 'like', $searchTerm)
                    ->orWhere('key', 'like', $searchTerm);
            });
        }

        $automations = $autoQuery->orderBy('category')->orderBy('name')->get();

        // Statistiques globales
        $totalCount = CronAutomation::count();
        $activeCount = CronAutomation::where('is_enabled', true)->count();
        $pausedCount = CronAutomation::where('is_enabled', false)->count();
        $totalRuns = (int) CronAutomation::sum('total_runs');
        $totalFailures = (int) CronAutomation::sum('total_failures');

        // Logs d'exécution basse-couche pour l'onglet technique
        $logsQuery = ScheduledTaskLog::query();

        if (! empty($validated['log_status']) && $validated['log_status'] !== 'all') {
            $logsQuery->where('status', $validated['log_status']);
        }

        if (! empty($validated['log_search'])) {
            $logsQuery->where('command', 'like', '%'.$validated['log_search'].'%');
        }

        $logs = $logsQuery->latest('run_at')->paginate(20, ['*'], 'logs_page')->withQueryString();

        return view('admin.audits.crons', compact(
            'automations',
            'logs',
            'totalCount',
            'activeCount',
            'pausedCount',
            'totalRuns',
            'totalFailures'
        ));
    }

    /**
     * Mettre en pause ou réactiver une automatisation
     */
    public function toggleCron(Request $request, string $key)
    {
        Validator::make(['key' => $key], [
            'key' => 'required|string|max:100',
        ])->validate();

        $automation = CronAutomation::toggle($key);

        if (! $automation) {
            return redirect()->back()->with('error', "Automatisation introuvable : {$key}");
        }

        $stateText = $automation->is_enabled ? 'activée et programmée' : 'mise en pause';

        return redirect()->back()->with('success', "L'automatisation « {$automation->name} » a été {$stateText} avec succès.");
    }

    /**
     * Exécuter manuellement une automatisation à la demande
     */
    public function runCron(Request $request, string $key)
    {
        Validator::make(['key' => $key], [
            'key' => 'required|string|max:100',
        ])->validate();

        $result = CronAutomation::runNow($key);

        if (! ($result['success'] ?? false)) {
            return redirect()->back()->with('error', "Échec de l'exécution de l'automatisation : ".($result['message'] ?? 'Erreur inconnue'));
        }

        $duration = number_format($result['duration'] ?? 0, 2);

        return redirect()->back()->with('success', "L'automatisation a été exécutée avec succès en {$duration}s. {$result['message']}");
    }

    /**
     * Liste des adresses emails exclues (Blacklist / Anti-bounce)
     */
    public function suppressions(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:100',
            'source' => 'nullable|string|in:all,system_auto,admin_manual',
            'bounce_type' => 'nullable|string|in:all,hard,soft,preflight_dns',
        ]);

        $query = EmailSuppression::with('creator');

        if (! empty($validated['search'])) {
            $searchTerm = '%'.$validated['search'].'%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('email', 'like', $searchTerm)
                    ->orWhere('reason', 'like', $searchTerm);
            });
        }

        if (! empty($validated['source']) && $validated['source'] !== 'all') {
            $query->where('source', $validated['source']);
        }

        if (! empty($validated['bounce_type']) && $validated['bounce_type'] !== 'all') {
            $query->where('bounce_type', $validated['bounce_type']);
        }

        $suppressions = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total' => EmailSuppression::count(),
            'hard' => EmailSuppression::where('bounce_type', 'hard')->count(),
            'soft' => EmailSuppression::where('bounce_type', 'soft')->count(),
            'dns' => EmailSuppression::where('bounce_type', 'preflight_dns')->count(),
            'manual' => EmailSuppression::where('source', 'admin_manual')->count(),
        ];

        return view('admin.audits.suppressions', compact('suppressions', 'stats'));
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
                'bounce_type' => 'manual',
                'bounce_count' => 1,
                'created_by' => auth()->id(),
            ]
        );

        return redirect()->back()->with('success', 'Adresse email ajoutée à la liste d\'exclusion avec succès.');
    }

    /**
     * Analyse et importe un texte brut de rejet (NDR) ou rapport de rebond
     */
    public function importBounces(Request $request)
    {
        $validated = $request->validate([
            'bounce_text' => 'required|string|max:500000',
        ], [
            'bounce_text.required' => 'Veuillez coller le texte ou le message de rejet (NDR) reçu.',
        ]);

        $service = app(BounceProcessingService::class);
        $result = $service->parseBounceText($validated['bounce_text']);

        if ($result['total_found'] === 0) {
            return redirect()->back()->with('error', 'Aucune adresse e-mail en échec ou format de rejet valide n\'a été détecté dans le texte fourni.');
        }

        $msg = sprintf(
            'Traitement réussi : %d adresse(s) en échec identifiée(s) (%d rejet(s) permanent(s) 550, %d boîte(s) saturée(s) 452). Les exclusions ont été appliquées pour protéger votre serveur Namecheap.',
            $result['total_found'],
            $result['hard_bounced'],
            $result['soft_bounced']
        );

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Scanner les adresses des utilisateurs pour exclure les domaines sans serveur MX actif
     */
    public function scanInvalidDomains(Request $request)
    {
        $service = app(BounceProcessingService::class);
        $result = $service->scanUsersForInvalidDomains(300);

        if ($result['excluded'] > 0) {
            $msg = sprintf(
                'Analyse effectuée sur %d utilisateurs : %d adresse(s) avec domaines inexistants ou sans serveur MX ont été automatiquement exclues.',
                $result['checked'],
                $result['excluded']
            );
        } else {
            $msg = sprintf(
                'Analyse effectuée sur %d utilisateurs : aucun domaine invalide détecté. Tous les serveurs MX vérifiés sont opérationnels.',
                $result['checked']
            );
        }

        return redirect()->back()->with('success', $msg);
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
