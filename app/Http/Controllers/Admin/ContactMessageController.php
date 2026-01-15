<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $query = ContactMessage::with('repliedBy');

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('email', 'like', '%' . $request->search . '%')
                    ->orWhere('name', 'like', '%' . $request->search . '%')
                    ->orWhere('subject', 'like', '%' . $request->search . '%');
            });
        }

        $messages = $query->latest()->paginate(20);
        $stats = [
            'total' => ContactMessage::count(),
            'new' => ContactMessage::new()->count(),
            'replied' => ContactMessage::replied()->count(),
        ];

        return view('admin.contact-messages.index', compact('messages', 'stats'));
    }

    public function show($id)
    {
        $message = ContactMessage::with('repliedBy')->findOrFail($id);
        $message->markAsRead();

        return view('admin.contact-messages.show', compact('message'));
    }

    public function reply(Request $request, $id)
    {
        $request->validate([
            'reply_message' => 'required|string',
            'format' => 'required|in:html,text',
        ]);

        $message = ContactMessage::findOrFail($id);

        try {
            // Envoyer l'email
            if ($request->format === 'html') {
                Mail::html($request->reply_message, function ($mail) use ($message) {
                    $mail->to($message->email)
                        ->subject('Re: ' . $message->subject);
                });
            } else {
                Mail::raw($request->reply_message, function ($mail) use ($message) {
                    $mail->to($message->email)
                        ->subject('Re: ' . $message->subject);
                });
            }

            // Marquer comme répondu
            $message->markAsReplied(auth()->id(), $request->reply_message);

            return redirect()->route('admin.contact-messages.show', $message->id)
                ->with('success', 'Réponse envoyée avec succès.');
        } catch (\Exception $e) {
            \Log::error('Contact reply failed: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de l\'envoi de la réponse: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->delete();

        return redirect()->route('admin.contact-messages.index')
            ->with('success', 'Message supprimé avec succès.');
    }

    public function exportPdf(Request $request)
    {
        $query = ContactMessage::with('repliedBy');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $messages = $query->latest()->get();

        $pdf = Pdf::loadView('admin.contact-messages.pdf', compact('messages'));

        return $pdf->download('contact_messages_' . now()->format('Y-m-d') . '.pdf');
    }
}
