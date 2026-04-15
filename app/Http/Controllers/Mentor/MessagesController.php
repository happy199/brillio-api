<?php

namespace App\Http\Controllers\Mentor;

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

        $mentorships = Mentorship::with(['mentee.jeuneProfile', 'messages' => function ($q) {
            $q->latest()->limit(1);
        }])
            ->where('mentor_id', $user->id)
            ->where('status', 'accepted')
            ->latest()
            ->get();

        $unreadCounts = Message::whereIn('mentorship_id', $mentorships->pluck('id'))
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->selectRaw('mentorship_id, count(*) as count')
            ->groupBy('mentorship_id')
            ->pluck('count', 'mentorship_id');

        return view('mentor.messages.index', compact('mentorships', 'unreadCounts'));
    }

    /**
     * Afficher une conversation
     */
    public function show(Mentorship $mentorship)
    {
        $user = auth()->user();

        abort_if($mentorship->mentor_id !== $user->id, 403);
        abort_if($mentorship->status !== 'accepted', 403);

        $mentorship->load(['mentee.jeuneProfile', 'messages.sender']);

        // Marquer comme lus les messages du jeune
        $mentorship->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('mentor.messages.show', compact('mentorship'));
    }

    /**
     * Envoyer un message
     */
    public function store(Request $request, Mentorship $mentorship)
    {
        $user = auth()->user();

        abort_if($mentorship->mentor_id !== $user->id, 403);
        abort_if($mentorship->status !== 'accepted', 403);

        $request->validate([
            'body' => 'nullable|string|max:5000',
            'attachment' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,webp,zip,txt',
        ], [
            'attachment.max' => 'Le fichier est trop volumineux (maximum 10 Mo).',
            'attachment.mimes' => 'Ce type de fichier n\'est pas autorisé.',
            'attachment.uploaded' => 'Le fichier n\'a pas pu être téléchargé. Vérifiez sa taille ou votre connexion.',
        ]);

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

        // Modération du contenu
        if ($request->filled('body')) {
            $moderator = new \App\Services\ContentModerator;
            $moderationResult = $moderator->moderate($request->body, $mentorship);

            if ($moderationResult['is_flagged']) {
                $data['original_body'] = $request->body;
                $data['body'] = $moderationResult['redacted'];
                $data['is_flagged'] = true;
                $data['flag_reason'] = $moderationResult['reason'];
            }
        }

        $message = Message::create($data);

        // Envoyer une notification par email (immédiate)
        app(\App\Services\MentorshipNotificationService::class)->sendNewMessageNotification($message);

        return back()->with('success', 'Message envoyé.');
    }

    /**
     * Télécharger une pièce jointe
     */
    public function download(Message $message)
    {
        $user = auth()->user();
        $mentorship = $message->mentorship;

        abort_if($mentorship->mentor_id !== $user->id && $mentorship->mentee_id !== $user->id, 403);
        abort_if(! $message->hasAttachment(), 404);

        return Storage::disk('local')->download($message->attachment_path, $message->attachment_name);
    }

    /**
     * Modifier un message
     */
    public function update(Request $request, Message $message)
    {
        $user = auth()->user();

        abort_if($message->sender_id !== $user->id, 403);
        abort_if($message->is_deleted, 403);

        $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $data = ['body' => $request->body];

        // Modération du contenu
        $moderator = new \App\Services\ContentModerator;
        $moderationResult = $moderator->moderate($request->body, $message->mentorship);

        if ($moderationResult['is_flagged']) {
            $data['original_body'] = $request->body;
            $data['body'] = $moderationResult['redacted'];
            $data['is_flagged'] = true;
            $data['flag_reason'] = $moderationResult['reason'];
        } else {
            $data['is_flagged'] = false;
            $data['flag_reason'] = null;
            $data['original_body'] = null;
        }

        $message->update(array_merge($data, ['edited_at' => now()]));

        return back()->with('success', 'Message modifié.');
    }

    /**
     * Supprimer un message
     */
    public function destroy(Message $message)
    {
        $user = auth()->user();

        abort_if($message->sender_id !== $user->id, 403);

        $message->update([
            'is_deleted' => true,
            'body' => null,
            'attachment_path' => null,
            'attachment_name' => null,
        ]);

        return back()->with('success', 'Message supprimé.');
    }
}
