<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class DataExportController extends Controller
{
    /**
     * Exporte toutes les données de l'utilisateur connecté en format JSON.
     * Conformité RGPD (Droit à la portabilité).
     */
    public function export(Request $request)
    {
        $user = $request->user();

        // Charger toutes les relations pertinentes
        $user->load([
            'jeuneProfile',
            'mentorProfile',
            'personalityTests',
            'chatConversations.messages',
            'academicDocuments',
            'purchases',
            'walletTransactions',
            'mentorshipsAsMentor',
            'mentorshipsAsMentee',
            'organizations',
        ]);

        $data = [
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'user_type' => $user->user_type,
                'date_of_birth' => $user->date_of_birth,
                'country' => $user->country,
                'city' => $user->city,
                'created_at' => $user->created_at,
            ],
            'details' => $user->isJeune() ? $user->jeuneProfile : ($user->isMentor() ? $user->mentorProfile : null),
            'personality_tests' => $user->personalityTests->map(function ($test) {
                return [
                    'completed_at' => $test->completed_at,
                    'results' => $test->results, // Assumé comme un tableau JSON dans la BDD
                    'type' => $test->type,
                ];
            }),
            'chat_history' => $user->chatConversations->map(function ($conv) {
                return [
                    'id' => $conv->id,
                    'started_at' => $conv->created_at,
                    'messages' => $conv->messages->map(function ($msg) {
                        return [
                            'sender_id' => $msg->sender_id,
                            'content' => $msg->content,
                            'sent_at' => $msg->created_at,
                        ];
                    }),
                ];
            }),
            'mentorship' => [
                'as_mentor' => $user->mentorshipsAsMentor,
                'as_mentee' => $user->mentorshipsAsMentee,
            ],
            'financial' => [
                'purchases' => $user->purchases,
                'wallet_transactions' => $user->walletTransactions,
            ],
            'academic_documents' => $user->academicDocuments,
            'exported_at' => now()->toDateTimeString(),
        ];

        $filename = 'brillio-data-export-'.$user->id.'-'.date('Y-m-d').'.json';

        return Response::make(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
