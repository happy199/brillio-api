<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use App\Models\EmailCampaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

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
            $query->where('email', 'like', '%' . $request->search . '%');
        }

        $subscribers = $query->latest()->paginate(50);
        $stats = [
            'total' => NewsletterSubscriber::count(),
            'active' => NewsletterSubscriber::active()->count(),
            'unsubscribed' => NewsletterSubscriber::unsubscribed()->count(),
        ];

        return view('admin.newsletter.index', compact('subscribers', 'stats'));
    }

    public function exportCsv(Request $request)
    {
        $query = NewsletterSubscriber::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $subscribers = $query->get();

        $filename = 'newsletter_subscribers_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
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

        return $pdf->download('newsletter_subscribers_' . now()->format('Y-m-d') . '.pdf');
    }

    public function sendEmail(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'recipients' => 'required|array',
            'recipients.*' => 'email',
        ]);

        // Créer la campagne
        $campaign = EmailCampaign::create([
            'subject' => $request->subject,
            'body' => $request->body,
            'type' => 'newsletter',
            'recipients_count' => count($request->recipients),
            'status' => 'sending',
            'sent_by' => auth()->id(),
            'recipient_emails' => $request->recipients,
        ]);

        $sent = 0;
        $failed = 0;

        foreach ($request->recipients as $email) {
            try {
                Mail::send([], [], function ($message) use ($email, $request) {
                    $message->to($email)
                        ->subject($request->subject)
                        ->html($request->body);
                });
                $sent++;
            } catch (\Exception $e) {
                $failed++;
                \Log::error('Newsletter email failed: ' . $e->getMessage(), ['email' => $email]);
            }
        }

        $campaign->update([
            'sent_count' => $sent,
            'failed_count' => $failed,
            'status' => $failed > 0 ? 'partial' : 'sent',
            'sent_at' => now(),
        ]);

        return redirect()->route('admin.newsletter.index')
            ->with('success', "Email envoyé à {$sent} destinataire(s). {$failed} échec(s).");
    }

    /**
     * Mettre à jour un abonné
     */
    public function update(Request $request, $id)
    {
        $subscriber = NewsletterSubscriber::findOrFail($id);

        $request->validate([
            'email' => 'required|email|unique:newsletter_subscribers,email,' . $subscriber->id,
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
}
