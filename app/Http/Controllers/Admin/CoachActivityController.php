<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CoachActivityController extends Controller
{
    public function index(Request $request)
    {
        // On ne récupère que les coachs et admins qui ont pris en charge au moins un chat
        $coaches = User::whereIn('type', ['admin', 'coach'])
            ->whereHas('chatConversationsAsHumanSupport')
            ->orderBy('name')
            ->get();

        $query = ChatConversation::query()
            ->with(['user', 'humanSupportAdmin', 'messages'])
            ->whereNotNull('human_support_admin_id');

        // Filtre par coach
        if ($request->filled('coach_id')) {
            $query->where('human_support_admin_id', $request->coach_id);
        }

        // Filtres par dates
        if ($request->filled('date_from')) {
            $query->whereDate('human_support_started_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('human_support_started_at', '<=', $request->date_to);
        }

        // Prépare les données
        $activities = $query->latest('human_support_started_at')->get()->map(function ($chat) {
            $firstMessage = $chat->messages->first();
            $lastMessage = $chat->messages->last();

            $firstMessageTime = $firstMessage ? $firstMessage->created_at : null;
            $lastMessageTime = $lastMessage ? $lastMessage->created_at : null;

            // Calculs de durée en minutes
            $chatDuration = ($firstMessageTime && $lastMessageTime)
                ? $firstMessageTime->diffInMinutes($lastMessageTime)
                : 0;

            $supportDuration = ($chat->human_support_started_at && $chat->human_support_ended_at)
                ? $chat->human_support_started_at->diffInMinutes($chat->human_support_ended_at)
                : (($chat->human_support_started_at) ? $chat->human_support_started_at->diffInMinutes(now()) : 0);

            return (object) [
                'id' => $chat->id,
                'coach_name' => $chat->humanSupportAdmin ? $chat->humanSupportAdmin->name : 'N/A',
                'jeune_name' => $chat->user ? $chat->user->name : 'N/A',
                'started_at' => $chat->human_support_started_at,
                'ended_at' => $chat->human_support_ended_at,
                'messages_count' => $chat->messages->count(),
                'first_message_time' => $firstMessageTime,
                'last_message_time' => $lastMessageTime,
                'chat_duration_mins' => $chatDuration,
                'support_duration_mins' => $supportDuration,
                'is_active' => $chat->human_support_active,
            ];
        });

        // Export CSV
        if ($request->get('export') === 'csv') {
            return $this->exportCsv($activities);
        }

        // Export PDF
        if ($request->get('export') === 'pdf') {
            return $this->exportPdf($activities, $request);
        }

        // Pagination de la collection mappée (astuce Laravel pour paginer une collection simple)
        $perPage = 20;
        $page = $request->get('page', 1);
        $paginatedActivities = new \Illuminate\Pagination\LengthAwarePaginator(
            $activities->forPage($page, $perPage),
            $activities->count(),
            $perPage,
            $page,
            ['path' => route('admin.coaches.activity')]
        );

        // Calculate global statistics
        $stats = [
            'total_chats' => $activities->count(),
            'total_support_time' => $activities->sum('support_duration_mins'),
            'avg_support_time' => $activities->count() > 0 ? round($activities->avg('support_duration_mins')) : 0,
            'total_messages' => $activities->sum('messages_count'),
        ];

        return view('admin.coaches.activity', compact('coaches', 'paginatedActivities', 'stats'));
    }

    private function exportPdf($activities, Request $request)
    {
        $fileName = 'activite-coachs-' . date('Y-m-d') . '.pdf';
        
        $filters = [
            'coach' => $request->filled('coach_id') ? User::find($request->coach_id)->name ?? 'Tous' : 'Tous',
            'date_from' => $request->date_from ? \Carbon\Carbon::parse($request->date_from)->format('d/m/Y') : 'Début',
            'date_to' => $request->date_to ? \Carbon\Carbon::parse($request->date_to)->format('d/m/Y') : 'Aujourd\'hui',
        ];

        // Ensure stats are up to date for the PDF logic
        $pdfStats = [
            'total_chats' => $activities->count(),
            'total_support_time' => $activities->sum('support_duration_mins'),
            'avg_support_time' => $activities->count() > 0 ? round($activities->avg('support_duration_mins')) : 0,
            'total_messages' => $activities->sum('messages_count'),
        ];
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.coaches.pdf', [
            'activities' => $activities,
            'stats' => $pdfStats,
            'filters' => $filters
        ])->setPaper('a4', 'landscape');

        return $pdf->download($fileName);
    }

    private function exportCsv($activities)
    {
        $fileName = 'activite-coachs-'.date('Y-m-d').'.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$fileName",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = [
            'ID Chat', 'Coach', 'Enfant/Jeune', 'Prise en charge le', 'Fin de prise en charge le',
            'Statut', 'Nb Messages', 'Heure 1er msg', 'Heure dernier msg',
            'Durée totale Chat (min)', 'Durée de Prise en Charge (min)',
        ];

        $callback = function () use ($activities, $columns) {
            $file = fopen('php://output', 'w');

            // Add BOM for Excel UTF-8 display
            fwrite($file, $bom = (chr(0xEF).chr(0xBB).chr(0xBF)));

            fputcsv($file, $columns, ';'); // Using semi-colon for French Excel compatibility

            foreach ($activities as $row) {
                fputcsv($file, [
                    $row->id,
                    $row->coach_name,
                    $row->jeune_name,
                    $row->started_at ? $row->started_at->format('d/m/Y H:i:s') : 'N/A',
                    $row->ended_at ? $row->ended_at->format('d/m/Y H:i:s') : 'N/A',
                    $row->is_active ? 'En cours' : 'Terminé',
                    $row->messages_count,
                    $row->first_message_time ? $row->first_message_time->format('H:i:s d/m/Y') : 'N/A',
                    $row->last_message_time ? $row->last_message_time->format('H:i:s d/m/Y') : 'N/A',
                    $row->chat_duration_mins,
                    $row->support_duration_mins,
                ], ';');
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
