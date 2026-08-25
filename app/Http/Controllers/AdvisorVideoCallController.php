<?php

namespace App\Http\Controllers;

use App\Models\AdvisorVideoCall;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\SystemSetting;
use App\Services\BrillioIAService;
use App\Services\JitsiService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdvisorVideoCallController extends Controller
{
    protected BrillioIAService $aiService;

    protected JitsiService $jitsiService;

    public function __construct(BrillioIAService $aiService, JitsiService $jitsiService)
    {
        $this->aiService = $aiService;
        $this->jitsiService = $jitsiService;
    }

    /**
     * Proposer un appel vidéo depuis le jeune (débit immédiat, acceptation automatique)
     */
    public function proposeByJeune(Request $request, ChatConversation $conversation)
    {
        $user = Auth::user();

        if ($conversation->user_id !== $user->id) {
            abort(403, 'Accès non autorisé à cette conversation.');
        }

        if (! $conversation->human_support_active) {
            return back()->with('error', 'Le conseiller doit être présent dans la conversation pour lancer un appel vidéo.');
        }

        // Récupérer le coût configuré (défaut 50 crédits)
        $cost = (int) (SystemSetting::where('key', 'feature_cost_video_call_advisor')->value('value') ?? 50);

        if ($user->credits_balance < $cost) {
            return back()->with('error', "Solde insuffisant pour un appel vidéo avec le conseiller ({$cost} crédits requis).");
        }

        DB::beginTransaction();
        try {
            // Débiter les crédits du jeune via WalletService
            app(WalletService::class)->deductCredits(
                $user,
                $cost,
                'feature_cost',
                "Appel vidéo avec un conseiller d'orientation"
            );

            $meetingId = 'brillio_advisor_'.Str::uuid()->toString();

            $call = AdvisorVideoCall::create([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'counselor_id' => $conversation->human_support_admin_id,
                'initiated_by' => 'jeune',
                'status' => 'accepted',
                'credits_cost' => $cost,
                'meeting_id' => $meetingId,
                'started_at' => now(),
            ]);

            // Ajouter le message dans la conversation
            ChatMessage::create([
                'conversation_id' => $conversation->id,
                'role' => ChatMessage::ROLE_ASSISTANT,
                'content' => "📹 L'élève a démarré un appel vidéo avec le conseiller. [ADVISOR_VIDEO_CALL:{$call->id}]",
                'is_system_message' => true,
            ]);

            DB::commit();

            return back()->with('success', 'Appel vidéo démarré ! Cliquez sur "Rejoindre l\'appel vidéo".');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('AdvisorVideoCall proposeByJeune error: '.$e->getMessage());

            return back()->with('error', 'Erreur lors du démarrage de l\'appel vidéo.');
        }
    }

    /**
     * Proposer un appel vidéo depuis le conseiller (en attente de confirmation du jeune)
     */
    public function proposeByCounselor(Request $request, ChatConversation $conversation)
    {
        $admin = Auth::user();

        if (! $admin || (! $admin->is_admin && $admin->user_type !== 'admin')) {
            abort(403, 'Action réservée aux conseillers.');
        }

        if (! $conversation->human_support_active) {
            return back()->with('error', 'La prise en charge humaine doit être active pour proposer une visio.');
        }

        $cost = (int) (SystemSetting::where('key', 'feature_cost_video_call_advisor')->value('value') ?? 50);
        $meetingId = 'brillio_advisor_'.Str::uuid()->toString();

        $call = AdvisorVideoCall::create([
            'conversation_id' => $conversation->id,
            'user_id' => $conversation->user_id,
            'counselor_id' => $admin->id,
            'initiated_by' => 'counselor',
            'status' => 'pending_acceptance',
            'credits_cost' => $cost,
            'meeting_id' => $meetingId,
        ]);

        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => ChatMessage::ROLE_ASSISTANT,
            'content' => "📹 Le conseiller vous propose un appel vidéo ({$cost} crédits). [ADVISOR_VIDEO_CALL:{$call->id}]",
            'is_from_human' => true,
            'admin_id' => $admin->id,
        ]);

        return back()->with('success', 'Proposition d\'appel vidéo envoyée au jeune.');
    }

    /**
     * Accepter la proposition d'appel vidéo par le jeune
     */
    public function acceptByJeune(Request $request, AdvisorVideoCall $call)
    {
        $user = Auth::user();

        if ($call->user_id !== $user->id) {
            abort(403, 'Accès non autorisé.');
        }

        if ($call->status !== 'pending_acceptance') {
            return back()->with('error', 'Cette proposition n\'est plus en attente.');
        }

        $cost = $call->credits_cost;

        if ($user->credits_balance < $cost) {
            return back()->with('error', "Solde insuffisant ({$cost} crédits requis). Veuillez recharger votre solde.");
        }

        DB::beginTransaction();
        try {
            app(WalletService::class)->deductCredits(
                $user,
                $cost,
                'feature_cost',
                "Appel vidéo avec un conseiller d'orientation (Accepté)"
            );

            $call->update([
                'status' => 'accepted',
                'started_at' => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Appel vidéo accepté ! Cliquez sur "Rejoindre l\'appel vidéo".');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('AdvisorVideoCall acceptByJeune error: '.$e->getMessage());

            return back()->with('error', 'Erreur lors de l\'acceptation de l\'appel vidéo.');
        }
    }

    /**
     * Refuser la proposition d'appel vidéo par le jeune
     */
    public function refuseByJeune(Request $request, AdvisorVideoCall $call)
    {
        $user = Auth::user();

        if ($call->user_id !== $user->id) {
            abort(403, 'Accès non autorisé.');
        }

        if ($call->status !== 'pending_acceptance') {
            return back()->with('error', 'Cette proposition n\'est plus en attente.');
        }

        $call->update([
            'status' => 'refused',
        ]);

        ChatMessage::create([
            'conversation_id' => $call->conversation_id,
            'role' => ChatMessage::ROLE_ASSISTANT,
            'content' => "Le jeune a refusé l'appel vidéo.",
            'is_system_message' => true,
        ]);

        return back()->with('info', 'Vous avez refusé la proposition d\'appel vidéo.');
    }

    /**
     * Afficher la salle de réunion visio dans un nouvel onglet
     */
    public function showMeeting(Request $request, $meetingId)
    {
        $user = Auth::user();

        if (! $user) {
            abort(403, 'Utilisateur non authentifié.');
        }

        $call = AdvisorVideoCall::where('meeting_id', $meetingId)->firstOrFail();

        $isJeune = $call->user_id === $user->id;
        $isCounselor = $user->is_admin || $call->counselor_id === $user->id;

        if (! $isJeune && ! $isCounselor) {
            abort(403, 'Vous ne faites pas partie de cet appel vidéo.');
        }

        if (! $call->isAccepted()) {
            return redirect()->route('jeune.chat')
                ->with('error', 'Cet appel vidéo n\'est pas actif ou a été refusé.');
        }

        $roomName = $call->meeting_id;
        $jwt = $this->jitsiService->generateToken($user, $roomName, $isCounselor);
        $appId = env('JAAS_APP_ID');
        $meetingLink = "https://8x8.vc/{$appId}/{$roomName}";

        return view('common.meeting.advisor_show', compact('call', 'meetingLink', 'jwt', 'isCounselor', 'appId', 'roomName', 'user'));
    }

    /**
     * Enregistrer un fragment de transcription vocale depuis la visio
     */
    public function appendTranscription(Request $request, AdvisorVideoCall $call)
    {
        $validated = $request->validate([
            'text' => 'required|string',
            'speaker' => 'required|string',
            'timestamp' => 'required|numeric',
        ]);

        $currentTranscription = $call->transcription_raw ?: [];

        if (! is_array($currentTranscription)) {
            $currentTranscription = [];
        }

        $currentTranscription[] = [
            'speaker' => $validated['speaker'],
            'text' => $validated['text'],
            'timestamp' => $validated['timestamp'],
        ];

        $call->update([
            'transcription_raw' => $currentTranscription,
        ]);

        return response()->json(['status' => 'success']);
    }

    /**
     * Clôturer l'appel et générer automatiquement le résumé IA dans le chat
     */
    public function finishMeeting(Request $request, AdvisorVideoCall $call)
    {
        if ($call->status === 'completed') {
            return response()->json(['status' => 'already_completed']);
        }

        $call->update([
            'status' => 'completed',
            'ended_at' => now(),
        ]);

        // Générer le résumé automatique par l'IA
        $transcriptionRaw = $call->transcription_raw ?: [];
        $counselorName = $call->counselor?->name ?? 'Conseiller';
        $youthName = $call->user?->name ?? 'Jeune';

        $summaryData = null;
        if (! empty($transcriptionRaw)) {
            $summaryData = $this->aiService->summarizeTranscription($transcriptionRaw, $counselorName, $youthName);
        }

        if ($summaryData && is_array($summaryData)) {
            $formatVal = function ($val) {
                if (is_array($val)) {
                    return '• '.implode("\n• ", array_map('strval', $val));
                }

                return (string) $val;
            };

            $summaryText = "📝 **Résumé automatique de l'entretien vidéo d'orientation :**\n\n";
            if (! empty($summaryData['progress'])) {
                $summaryText .= "📌 **Points abordés & Échanges :**\n".$formatVal($summaryData['progress'])."\n\n";
            }
            if (! empty($summaryData['obstacles'])) {
                $summaryText .= "⚠️ **Besoins & Difficultés :**\n".$formatVal($summaryData['obstacles'])."\n\n";
            }
            if (! empty($summaryData['smart_goals'])) {
                $summaryText .= "🎯 **Recommandations & Plan d'action :**\n".$formatVal($summaryData['smart_goals']);
            }
        } else {
            $summaryText = "📝 **Résumé de l'entretien vidéo :**\nL'entretien visio d'orientation entre {$youthName} et {$counselorName} s'est achevé avec succès.";
        }

        $call->update([
            'ai_summary' => $summaryText,
        ]);

        // Poster le message de résumé dans la conversation chat
        ChatMessage::create([
            'conversation_id' => $call->conversation_id,
            'role' => ChatMessage::ROLE_ASSISTANT,
            'content' => $summaryText,
            'is_from_human' => true,
            'admin_id' => $call->counselor_id,
        ]);

        return response()->json(['status' => 'success', 'summary' => $summaryText]);
    }
}
