<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CoachActivityController extends Controller
{
    public function index(Request $request)
    {
        // On ne récupère que les coachs et admins qui ont pris en charge au moins un chat
        $coaches = User::where(function ($query) {
            $query->where('is_admin', true)
                ->orWhere('is_coach', true);
        })
            ->whereHas('chatConversationsAsHumanSupport')
            ->orderBy('name')
            ->get();

        $query = ChatConversation::query()
            ->with(['user', 'supportAdmin', 'messages'])
            ->whereNotNull('human_support_admin_id');

        $validated = $request->validate([
            'coach_id' => 'nullable|integer|exists:users,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'export' => 'nullable|string|in:csv,pdf',
            'page' => 'nullable|integer|min:1',
        ]);

        // Filtre par coach
        if ($coachId = $validated['coach_id'] ?? null) {
            $query->where('human_support_admin_id', $coachId);
        }

        // Filtres par dates
        if ($dateFrom = $validated['date_from'] ?? null) {
            $query->whereDate('human_support_started_at', '>=', $dateFrom);
        }
        if ($dateTo = $validated['date_to'] ?? null) {
            $query->whereDate('human_support_started_at', '<=', $dateTo);
        }

        // Prépare les données
        $activities = $query->latest('human_support_started_at')->get()->map(function ($chat) {

            // On cherche uniquement les messages du coach qui a pris en charge
            $coachMessages = $chat->messages->filter(function ($msg) use ($chat) {
                return $msg->admin_id === $chat->human_support_admin_id;
            });

            $firstMessage = $coachMessages->first();
            $lastMessage = $coachMessages->last();

            $firstMessageTime = $firstMessage ? $firstMessage->created_at : null;
            $lastMessageTime = $lastMessage ? $lastMessage->created_at : null;

            // Calculs de durée en minutes (arrondis à l'inférieur)
            $chatDuration = ($firstMessageTime && $lastMessageTime)
                ? (int) floor($firstMessageTime->diffInMinutes($lastMessageTime))
                : 0;

            $supportDuration = ($chat->human_support_started_at && $chat->human_support_ended_at)
                ? (int) floor($chat->human_support_started_at->diffInMinutes($chat->human_support_ended_at))
                : (($chat->human_support_started_at) ? (int) floor($chat->human_support_started_at->diffInMinutes(now())) : 0);

            return (object) [
                'id' => $chat->id,
                'coach_name' => $chat->supportAdmin ? $chat->supportAdmin->name : 'N/A',
                'jeune_name' => $chat->user ? $chat->user->name : 'N/A',
                'started_at' => $chat->human_support_started_at,
                'ended_at' => $chat->human_support_ended_at,
                'messages_count' => $coachMessages->count(), // Optionnel: ne compter que ses propres messages ou tous les messages humains ? Le user demande sur le total du chat pour son intervention. On va compter les siens.
                'first_message_time' => $firstMessageTime,
                'last_message_time' => $lastMessageTime,
                'chat_duration_mins' => $chatDuration,
                'support_duration_mins' => $supportDuration,
                'chat_duration_formatted' => $this->formatDuration($chatDuration),
                'support_duration_formatted' => $this->formatDuration($supportDuration),
                'is_active' => $chat->human_support_active,
            ];
        });

        // Export CSV
        if (($validated['export'] ?? null) === 'csv') {
            return $this->exportCsv($activities);
        }

        // Export PDF
        if (($validated['export'] ?? null) === 'pdf') {
            return $this->exportPdf($activities, $validated);
        }

        // Pagination de la collection mappée (astuce Laravel pour paginer une collection simple)
        $perPage = 20;
        $page = $validated['page'] ?? 1;
        $paginatedActivities = new LengthAwarePaginator(
            $activities->forPage($page, $perPage),
            $activities->count(),
            $perPage,
            $page,
            ['path' => route('admin.coaches.activity')]
        );

        // Calculate global statistics
        $stats = [
            'total_chats' => $activities->count(),
            'total_support_time' => $this->formatDuration($activities->sum('support_duration_mins')),
            'avg_support_time' => $this->formatDuration($activities->count() > 0 ? floor($activities->avg('support_duration_mins')) : 0),
            'total_messages' => $activities->sum('messages_count'),
        ];

        return view('admin.coaches.activity', compact('coaches', 'paginatedActivities', 'stats'));
    }

    /**
     * Helper paramétrable pour formater les minutes en (Xh Ymin)
     */
    private function formatDuration($minutes)
    {
        if ($minutes < 60) {
            return $minutes.' min';
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        return $remainingMinutes > 0
            ? "{$hours}h {$remainingMinutes}min"
            : "{$hours}h";
    }

    private function exportPdf($activities, array $validated = [])
    {
        $fileName = 'activite-coachs-'.date('Y-m-d').'.pdf';

        $filters = [
            'coach' => ! empty($validated['coach_id']) ? User::find($validated['coach_id'])->name ?? 'Tous' : 'Tous',
            'date_from' => ! empty($validated['date_from']) ? Carbon::parse($validated['date_from'])->format('d/m/Y') : 'Début',
            'date_to' => ! empty($validated['date_to']) ? Carbon::parse($validated['date_to'])->format('d/m/Y') : 'Aujourd\'hui',
        ];

        // Ensure stats are up to date for the PDF logic
        $pdfStats = [
            'total_chats' => $activities->count(),
            'total_support_time' => $this->formatDuration($activities->sum('support_duration_mins')),
            'avg_support_time' => $this->formatDuration($activities->count() > 0 ? floor($activities->avg('support_duration_mins')) : 0),
            'total_messages' => $activities->sum('messages_count'),
        ];

        $pdf = Pdf::loadView('admin.coaches.pdf', [
            'activities' => $activities,
            'stats' => $pdfStats,
            'filters' => $filters,
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
                    $row->chat_duration_formatted,
                    $row->support_duration_formatted,
                ], ';');
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
