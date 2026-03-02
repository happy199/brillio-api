<?php

namespace App\Http\Controllers\Jeune;

use App\Http\Controllers\Controller;
use App\Models\Mentorship;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MessagesController extends Controller
{
    /**
     * Liste des conversations (mentorships accepted)
     */
    public function index()
    {
        $user = auth()->user();

        $mentorships = Mentorship::with(['mentor.mentorProfile', 'messages' => function ($q) {
            $q->latest()->limit(1);
        }])
            ->where('mentee_id', $user->id)
            ->where('status', 'accepted')
            ->latest()
            ->get();

        // Compter les messages non lus par mentorship
        $unreadCounts = Message::whereIn('mentorship_id', $mentorships->pluck('id'))
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->selectRaw('mentorship_id, count(*) as count')
            ->groupBy('mentorship_id')
            ->pluck('count', 'mentorship_id');

        return view('jeune.messages.index', compact('mentorships', 'unreadCounts'));
    }

    /**
     * Afficher une conversation
     */
    public function show(Mentorship $mentorship)
    {
        $user = auth()->user();

        // Vérifier que le jeune est bien le mentee
        abort_if($mentorship->mentee_id !== $user->id, 403);
        abort_if($mentorship->status !== 'accepted', 403);

        $mentorship->load(['mentor.mentorProfile', 'messages.sender']);

        // Marquer comme lus les messages du mentor
        $mentorship->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('jeune.messages.show', compact('mentorship'));
    }

    /**
     * Envoyer un message
     */
    public function store(Request $request, Mentorship $mentorship)
    {
        $user = auth()->user();

        abort_if($mentorship->mentee_id !== $user->id, 403);
        abort_if($mentorship->status !== 'accepted', 403);

        $request->validate([
            'body' => 'nullable|string|max:5000',
            'attachment' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,webp,zip,txt',
        ]);

        // Au moins body ou attachment requis
        if (! $request->filled('body') && ! $request->hasFile('attachment')) {
            return back()->withErrors(['body' => 'Veuillez écrire un message ou joindre un fichier.']);
        }

        $data = [
            'mentorship_id' => $mentorship->id,
            'sender_id' => $user->id,
            'body' => $request->body,
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('messages/attachments', 'local');
            $data['attachment_path'] = $path;
            $data['attachment_name'] = $file->getClientOriginalName();
            $data['attachment_mime'] = $file->getMimeType();
        }

        Message::create($data);

        return back()->with('success', 'Message envoyé.');
    }

    /**
     * Télécharger une pièce jointe
     */
    public function download(Message $message)
    {
        $user = auth()->user();
        $mentorship = $message->mentorship;

        abort_if($mentorship->mentee_id !== $user->id && $mentorship->mentor_id !== $user->id, 403);
        abort_if(! $message->hasAttachment(), 404);

        return Storage::disk('local')->download($message->attachment_path, $message->attachment_name);
    }
}
