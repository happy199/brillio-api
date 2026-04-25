<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailCampaign;
use App\Models\NewsletterSubscriber;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function index(Request $request)
    {
        $query = NewsletterSubscriber::query();

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('email', 'like', '%'.$request->search.'%');
        }

        $subscribers = $query->latest()->paginate(50);
        $stats = [
            'total' => NewsletterSubscriber::count(),
            'active' => NewsletterSubscriber::active()->count(),
            'unsubscribed' => NewsletterSubscriber::unsubscribed()->count(),
            'total_users' => \App\Models\User::count(),
            'total_jeunes' => \App\Models\User::where('user_type', 'jeune')->count(),
            'total_mentors' => \App\Models\User::where('user_type', 'mentor')->count(),
            'total_organizations' => \App\Models\User::where('user_type', 'organization')->count(),
        ];

        return view('admin.newsletter.index', compact('subscribers', 'stats'));
    }

    public function campaigns()
    {
        $campaigns = EmailCampaign::latest()->paginate(20);

        return view('admin.newsletter.campaigns', compact('campaigns'));
    }

    public function exportCsv(Request $request)
    {
        $query = NewsletterSubscriber::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $subscribers = $query->get();

        $filename = 'newsletter_subscribers_'.now()->format('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($subscribers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Email', 'Status', 'Subscribed At', 'Unsubscribed At']);

            foreach ($subscribers as $subscriber) {
                fputcsv($file, [
                    $subscriber->email,
                    $subscriber->status,
                    $subscriber->subscribed_at?->format('Y-m-d H:i:s'),
                    $subscriber->unsubscribed_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $query = NewsletterSubscriber::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $subscribers = $query->get();
        $stats = [
            'total' => $subscribers->count(),
            'active' => $subscribers->where('status', 'active')->count(),
            'unsubscribed' => $subscribers->where('status', 'unsubscribed')->count(),
        ];

        $pdf = Pdf::loadView('admin.newsletter.pdf', compact('subscribers', 'stats'));

        return $pdf->download('newsletter_subscribers_'.now()->format('Y-m-d').'.pdf');
    }

    public function sendEmail(Request $request)
    {
        // Fix pour le JS qui envoie du JSON stringifié au lieu d'un array
        if ($request->has('recipients') && is_string($request->input('recipients'))) {
            $request->merge([
                'recipients' => json_decode($request->input('recipients'), true),
            ]);
        }

        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'recipient_type' => 'required|string|in:all,all_users,custom,selected,specific_population',
            'recipients' => 'nullable|array',
            'populations' => 'nullable|array',
            'custom_emails' => 'nullable|string',
            'is_recurring' => 'nullable',
            'frequency' => 'required_if:is_recurring,on|nullable|string|in:daily,weekly,monthly',
            'start_date' => 'required_if:is_recurring,on|nullable|date',
            'end_date' => 'required_if:is_recurring,on|nullable|date|after_or_equal:start_date',
        ]);

        $recipientEmails = [];

        switch ($request->recipient_type) {
            case 'all':
                $recipientEmails = NewsletterSubscriber::active()->pluck('email')->toArray();
                break;
            case 'all_users':
                // On prend tous les utilisateurs qui ne sont pas archivés
                $recipientEmails = \App\Models\User::whereNull('archived_at')->pluck('email')->toArray();
                break;
            case 'custom':
                if ($request->filled('custom_emails')) {
                    // Split par virgule, point-virgule ou retour à la ligne
                    $emails = preg_split('/[,\n\r;]+/', $request->custom_emails);
                    $recipientEmails = array_filter(array_map('trim', $emails), function ($email) {
                        return filter_var($email, FILTER_VALIDATE_EMAIL);
                    });
                }
                break;
            case 'selected':
                $recipientEmails = $request->recipients ?? [];
                break;
            case 'specific_population':
                if ($request->filled('populations')) {
                    $recipientEmails = \App\Models\User::whereIn('user_type', $request->populations)
                        ->whereNull('archived_at')
                        ->pluck('email')
                        ->toArray();
                }
                break;
        }

        if (empty($recipientEmails) && $request->recipient_type !== 'specific_population') {
            return back()->with('error', '⚠️ Aucun destinataire valide trouvé pour cette sélection.');
        }

        $recipientEmails = array_values(array_unique($recipientEmails));
        $isRecurring = $request->has('is_recurring');

        // Gestion des pièces jointes
        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if ($file->isValid()) {
                    $path = $file->store('newsletters/attachments', 'public');
                    $attachmentPaths[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'mime' => $file->getMimeType(),
                    ];
                }
            }
        }

        // Créer la campagne
        $campaign = EmailCampaign::create([
            'subject' => $request->subject,
            'body' => $request->body,
            'type' => 'newsletter',
            'is_recurring' => $isRecurring,
            'frequency' => $isRecurring ? $request->frequency : null,
            'start_date' => $isRecurring ? $request->start_date : null,
            'end_date' => $isRecurring ? $request->end_date : null,
            'next_run_at' => $isRecurring ? \Carbon\Carbon::parse($request->start_date)->startOfDay()->addHours(9) : null,
            'recipient_filters' => [
                'type' => $request->recipient_type,
                'populations' => $request->populations,
                'custom_emails' => $request->custom_emails,
            ],
            'recipients_count' => count($recipientEmails),
            'status' => $isRecurring ? 'active' : 'queued',
            'sent_by' => auth()->id(),
            'recipient_emails' => $isRecurring ? [] : $recipientEmails,
            'attachments' => $attachmentPaths,
        ]);

        if (! $isRecurring) {
            // Dispatcher le Job dans la queue seulement si ce n'est pas récurrent
            \App\Jobs\SendNewsletterJob::dispatch($campaign);

            return redirect()->route('admin.newsletter.index')
                ->with('success', "La campagne a été mise en file d'attente. L'envoi se fera en arrière-plan.");
        }

        return redirect()->route('admin.newsletter.index')
            ->with('success', 'La campagne récurrente a été planifiée avec succès.');
    }

    /**
     * Mettre à jour un abonné
     */
    public function update(Request $request, $id)
    {
        $subscriber = NewsletterSubscriber::findOrFail($id);

        $request->validate([
            'email' => 'required|email|unique:newsletter_subscribers,email,'.$subscriber->id,
            'status' => 'required|in:active,unsubscribed',
        ]);

        $subscriber->update([
            'email' => $request->email,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.newsletter.index')
            ->with('success', 'Abonné mis à jour avec succès !');
    }

    public function destroy($id)
    {
        $subscriber = NewsletterSubscriber::findOrFail($id);
        $subscriber->delete();

        return redirect()->route('admin.newsletter.index')
            ->with('success', 'Abonné supprimé avec succès.');
    }

    public function toggleCampaign($id)
    {
        $campaign = EmailCampaign::findOrFail($id);

        if (! $campaign->is_recurring) {
            return back()->with('error', '⚠️ Cette action est réservée aux campagnes récurrentes.');
        }

        $newStatus = $campaign->status === 'active' ? 'paused' : 'active';
        $campaign->update(['status' => $newStatus]);

        $message = $newStatus === 'active' ? 'Campagne relancée !' : 'Campagne mise en pause.';

        return back()->with('success', $message);
    }

    public function destroyCampaign($id)
    {
        $campaign = EmailCampaign::findOrFail($id);
        $campaign->delete();

        return back()->with('success', 'Campagne supprimée avec succès.');
    }

    public function showCampaign($id)
    {
        $campaign = EmailCampaign::with('sentBy')->findOrFail($id);

        return response()->json([
            'subject' => $campaign->subject,
            'body' => $campaign->body,
            'is_recurring' => $campaign->is_recurring,
            'frequency' => $campaign->frequency,
            'start_date' => $campaign->start_date?->format('d/m/Y'),
            'end_date' => $campaign->end_date?->format('d/m/Y'),
            'next_run_at' => $campaign->next_run_at?->format('d/m/Y H:i'),
            'recipients_count' => $campaign->recipients_count,
            'attachments' => $campaign->attachments,
            'status' => $campaign->status,
            'sent_by' => $campaign->sentBy?->name ?? 'Système',
            'created_at' => $campaign->created_at->format('d/m/Y H:i'),
            'parent_id' => $campaign->parent_id,
        ]);
    }
}
