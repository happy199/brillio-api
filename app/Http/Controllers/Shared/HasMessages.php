<?php

namespace App\Http\Controllers\Shared;

use App\Models\Mentorship;
use App\Models\Message;
use App\Services\ContentModerator;
use App\Services\MentorshipNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

trait HasMessages
{
    /**
     * Get the role-specific config for queries and views.
     */
    abstract protected function getMessageConfig(): array;

    public function index()
    {
        $user = auth()->user();
        $config = $this->getMessageConfig();

        $mentorships = Mentorship::with([$config['relation_profile'], 'messages' => function ($q) {
            $q->latest()->limit(1);
        }])
            ->where($config['user_column'], $user->id)
            ->where('status', 'accepted')
            ->latest()
            ->get();

        $unreadCounts = Message::whereIn('mentorship_id', $mentorships->pluck('id'))
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->selectRaw('mentorship_id, count(*) as count')
            ->groupBy('mentorship_id')
            ->pluck('count', 'mentorship_id');

        if (isset($config['is_api']) && $config['is_api']) {
            return response()->json([
                'mentorships' => $mentorships,
                'unread_counts' => $unreadCounts,
            ]);
        }

        return view($config['view_prefix'].'index', compact('mentorships', 'unreadCounts'));
    }

    public function show(Mentorship $mentorship)
    {
        $user = auth()->user();
        $config = $this->getMessageConfig();

        abort_if($mentorship->{$config['user_column']} !== $user->id, 403);
        abort_if($mentorship->status !== 'accepted', 403);

        $mentorship->load([$config['relation_profile'], 'messages.sender']);

        $mentorship->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if (isset($config['is_api']) && $config['is_api']) {
            return response()->json(['mentorship' => $mentorship]);
        }

        return view($config['view_prefix'].'show', compact('mentorship'));
    }

    public function store(Request $request, Mentorship $mentorship)
    {
        $user = auth()->user();
        $config = $this->getMessageConfig();

        abort_if($mentorship->{$config['user_column']} !== $user->id, 403);
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
            if (isset($config['is_api']) && $config['is_api']) {
                return response()->json(['message' => 'Veuillez écrire un message ou joindre un fichier.'], 422);
            }

            return back()->withErrors(['body' => 'Veuillez écrire un message ou joindre un fichier.']);
        }

        $data = [
            'mentorship_id' => $mentorship->id,
            'sender_id' => $user->id,
            'body' => $request->body,
        ];

        if ($request->hasFile('attachment')) {
            // nosemgrep
            $file = $request->file('attachment');
            $path = $file->store('messages/attachments', 'local');
            $data['attachment_path'] = $path;
            $data['attachment_name'] = $file->getClientOriginalName();
            $data['attachment_mime'] = $file->getMimeType();
        }

        if ($request->filled('body')) {
            $moderator = new ContentModerator;
            $moderationResult = $moderator->moderate($request->body, $mentorship);

            if ($moderationResult['is_flagged']) {
                $data['original_body'] = $request->body;
                $data['body'] = $moderationResult['redacted'];
                $data['is_flagged'] = true;
                $data['flag_reason'] = $moderationResult['reason'];
            }
        }

        $message = Message::create($data);

        app(MentorshipNotificationService::class)->sendNewMessageNotification($message);

        if (isset($config['is_api']) && $config['is_api']) {
            return response()->json(['message' => 'Message envoyé.', 'data' => $message]);
        }

        return back()->with('success', 'Message envoyé.');
    }

    public function download(Message $message)
    {
        $user = auth()->user();
        $mentorship = $message->mentorship;

        abort_if($mentorship->mentee_id !== $user->id && $mentorship->mentor_id !== $user->id, 403);
        abort_if(! $message->hasAttachment(), 404);

        return Storage::disk('local')->download($message->attachment_path, $message->attachment_name);
    }

    public function update(Request $request, Message $message)
    {
        $user = auth()->user();
        $config = $this->getMessageConfig();

        abort_if($message->sender_id !== $user->id, 403);
        abort_if($message->is_deleted, 403);

        $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $data = ['body' => $request->body];

        $moderator = new ContentModerator;
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

        if (isset($config['is_api']) && $config['is_api']) {
            return response()->json(['message' => 'Message modifié.', 'data' => $message]);
        }

        return back()->with('success', 'Message modifié.');
    }

    public function destroy(Message $message)
    {
        $user = auth()->user();
        $config = $this->getMessageConfig();

        abort_if($message->sender_id !== $user->id, 403);

        $message->update([
            'is_deleted' => true,
            'body' => null,
            'attachment_path' => null,
            'attachment_name' => null,
        ]);

        if (isset($config['is_api']) && $config['is_api']) {
            return response()->json(['message' => 'Message supprimé.']);
        }

        return back()->with('success', 'Message supprimé.');
    }
}
