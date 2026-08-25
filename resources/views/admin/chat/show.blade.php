@extends('layouts.admin')

@section('title', 'Conversation - ' . ($conversation->title ?? 'Sans titre'))

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.chat.index') }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $conversation->title ?? 'Sans titre' }}</h1>
                <p class="text-gray-600">Conversation avec {{ $conversation->user->name ?? 'Utilisateur supprimé' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <!-- Statut de la conversation -->
            @if($conversation->needs_human_support && !$conversation->human_support_active)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                    <span class="w-2 h-2 bg-red-500 rounded-full mr-2 animate-pulse"></span>
                    En attente de conseiller
                </span>
                <form action="{{ route('admin.chat.take-over', $conversation) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
                        Prendre en charge
                    </button>
                </form>
            @elseif($conversation->human_support_active)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                    <span class="w-2 h-2 bg-orange-500 rounded-full mr-2"></span>
                    Support actif
                    @if($conversation->supportAdmin)
                        - {{ $conversation->supportAdmin->name }}
                    @endif
                </span>
                <form action="{{ route('admin.chat.end-support', $conversation) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Êtes-vous sûr de vouloir terminer la session de support ? L\'utilisateur reviendra au chatbot IA.')"
                            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm font-medium">
                        Terminer le support
                    </button>
                </form>
            @else
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                    <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                    Mode IA
                </span>
            @endif

            <a href="{{ route('admin.chat.export-pdf', $conversation) }}"
               class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">
                Exporter PDF
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Infos utilisateur -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-center">
                    <div class="h-16 w-16 rounded-full bg-blue-100 flex items-center justify-center mx-auto">
                        <span class="text-blue-600 font-bold text-2xl">
                            {{ strtoupper(substr($conversation->user->name ?? '?', 0, 1)) }}
                        </span>
                    </div>
                    <h3 class="mt-4 font-semibold text-gray-900">{{ $conversation->user->name ?? 'Utilisateur supprimé' }}</h3>
                    <p class="text-sm text-gray-500">{{ $conversation->user->email ?? '-' }}</p>
                    @if($conversation->user)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-2 {{ $conversation->user->user_type === 'mentor' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ $conversation->user->user_type === 'mentor' ? 'Mentor' : 'Jeune' }}
                    </span>
                    @endif
                </div>

                @if($conversation->user)
                <div class="mt-6 border-t pt-6 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Pays</span>
                        <span class="text-gray-900">{{ $conversation->user->country ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Messages</span>
                        <span class="text-gray-900">{{ $conversation->messages->count() }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Créée le</span>
                        <span class="text-gray-900">{{ $conversation->created_at->format('d/m/Y') }}</span>
                    </div>
                    @if($conversation->human_support_started_at)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Support démarré</span>
                        <span class="text-gray-900">{{ $conversation->human_support_started_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                    @if($conversation->human_support_ended_at)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Support terminé</span>
                        <span class="text-gray-900">{{ $conversation->human_support_ended_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                </div>
                @endif

                @if($conversation->user && $conversation->user->personalityTest)
                <div class="mt-6 border-t pt-6">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">Personnalité</h4>
                    <div class="text-center p-4 bg-purple-50 rounded-lg">
                        <span class="text-2xl font-bold text-purple-600">
                            {{ $conversation->user->personalityTest->personality_type }}
                        </span>
                        <p class="text-sm text-purple-700 mt-1">
                            {{ $conversation->user->personalityTest->personality_label }}
                        </p>
                    </div>
                </div>
                @endif

                <!-- Actions rapides -->
                @if($conversation->user)
                <div class="mt-6 border-t pt-6">
                    <a href="{{ route('admin.users.show', $conversation->user) }}"
                       class="block w-full text-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                        Voir le profil complet
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Messages -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-xl shadow-sm flex flex-col" style="height: 700px;">
                <div class="p-4 border-b flex justify-between items-center bg-gray-50/50">
                    <h3 class="font-semibold text-gray-900">Messages ({{ $conversation->messages->count() }})</h3>
                    @if($conversation->human_support_active)
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-orange-600 font-semibold bg-orange-50 px-2.5 py-1 rounded-lg border border-orange-200">Mode conseiller actif</span>
                        <form action="{{ route('admin.chat.video-call.propose-counselor', $conversation) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                    onclick="return confirm('Proposer un appel vidéo d\'orientation à l\'élève ?')"
                                    class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Proposer un appel vidéo
                            </button>
                        </form>
                    </div>
                    @endif
                </div>

                <div class="flex-1 p-4 space-y-4 overflow-y-auto" id="messages-container">
                    @forelse($conversation->messages as $message)
                    @php
                        $advisorCall = null;
                        if (preg_match('/\[ADVISOR_VIDEO_CALL:(\d+)\]/', $message->content, $matches)) {
                            $advisorCall = \App\Models\AdvisorVideoCall::find($matches[1]);
                        }
                    @endphp
                    <div class="flex {{ $message->role === 'user' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[80%]">
                            <div class="flex items-center gap-2 mb-1 {{ $message->role === 'user' ? 'justify-end' : 'justify-start' }}">
                                @if($message->role === 'assistant')
                                    @if($message->is_from_human)
                                    <div class="w-6 h-6 rounded-full bg-orange-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <span class="text-xs text-orange-600 font-medium">
                                        Conseiller {{ $message->admin ? '(' . $message->admin->name . ')' : '' }}
                                    </span>
                                    @elseif($message->is_system_message)
                                    <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <span class="text-xs text-gray-500">Système</span>
                                    @else
                                    <div class="w-6 h-6 rounded-full bg-purple-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                        </svg>
                                    </div>
                                    <span class="text-xs text-gray-500">Brillio IA</span>
                                    @endif
                                @else
                                <span class="text-xs text-gray-500">{{ $conversation->user->name ?? 'Utilisateur' }}</span>
                                <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center">
                                    <span class="text-xs text-blue-600 font-semibold">
                                        {{ strtoupper(substr($conversation->user->name ?? '?', 0, 1)) }}
                                    </span>
                                </div>
                                @endif
                            </div>

                            <div class="rounded-2xl px-4 py-3
                                @if($message->role === 'user')
                                    bg-blue-600 text-white
                                @elseif($message->is_from_human)
                                    bg-orange-100 text-orange-900 border border-orange-200
                                @elseif($message->is_system_message)
                                    bg-gray-100 text-gray-600 italic
                                @else
                                    bg-gray-100 text-gray-800
                                @endif">
                                @php
                                    $cleanContent = trim(preg_replace('/\[ADVISOR_VIDEO_CALL:\d+\]/', '', $message->content));
                                    $videoUrl = null;
                                    if (preg_match('/(?:🎥\s*Enregistrement vidéo[^\n]*?\[[^\]]+\]\(([^\)]+)\)|https?:\/\/[^\s\)]+video_recordings[^\s\)]+|\/storage\/video_recordings[^\s\)]+|https?:\/\/res\.cloudinary\.com[^\s\)]+video[^\s\)]+)/i', $cleanContent, $vMatch)) {
                                        $videoUrl = $vMatch[1] ?? $vMatch[0];
                                        $cleanContent = trim(preg_replace('/🎥\s*Enregistrement vidéo[^\n]*?(\[[^\]]+\]\([^\)]+\)|\/storage[^\s]+|https?:\/\/[^\s]+)/i', '', $cleanContent));
                                    }
                                @endphp

                                @if(!empty($cleanContent))
                                    <p class="text-sm whitespace-pre-wrap">{{ $cleanContent }}</p>
                                @endif

                                @if($videoUrl)
                                    <div class="mt-3 bg-gradient-to-br from-indigo-900 via-slate-900 to-purple-950 text-white p-4 rounded-xl shadow-lg border border-indigo-500/30 space-y-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-indigo-500/20 flex items-center justify-center border border-indigo-400/30">
                                                <svg class="w-5 h-5 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-xs text-indigo-200 uppercase tracking-wider">Enregistrement de l'Entretien Vidéo</h4>
                                                <p class="text-[11px] text-gray-300">Vidéo disponible pour consultation</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 pt-1">
                                            <button type="button" onclick="window.openAdminVideoPlayer('{{ $videoUrl }}')"
                                                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-xs transition shadow cursor-pointer">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                                Visionner la vidéo
                                            </button>
                                            <a href="{{ $videoUrl }}" download target="_blank"
                                                class="inline-flex items-center gap-1.5 px-3 py-2 bg-white/10 hover:bg-white/20 text-gray-200 font-semibold rounded-lg text-xs transition cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                Télécharger
                                            </a>
                                        </div>
                                    </div>
                                @endif

                                @if($advisorCall)
                                    @if($advisorCall->isAccepted())
                                        <div class="mt-3 bg-indigo-900 text-white p-4 rounded-xl shadow border border-indigo-700 space-y-2">
                                            <div class="flex items-center gap-2 text-indigo-200 text-xs font-bold uppercase tracking-wider">
                                                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                                Appel Vidéo Conseiller Actif
                                            </div>
                                            <p class="text-xs text-indigo-100">La salle de visioconférence est prête.</p>
                                            <a href="{{ route('advisor-meeting.show', $advisorCall->meeting_id) }}" target="_blank" rel="noopener"
                                               class="inline-flex items-center gap-2 px-3 py-1.5 bg-white text-indigo-900 hover:bg-indigo-50 font-bold rounded-lg text-xs transition shadow">
                                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                </svg>
                                                Rejoindre l'appel vidéo
                                            </a>
                                        </div>
                                    @elseif($advisorCall->isPending())
                                        <div class="mt-3 bg-amber-50 text-amber-900 p-3 rounded-xl border border-amber-200 text-xs space-y-1">
                                            <div class="flex items-center gap-1.5 font-bold text-amber-700">
                                                <svg class="w-4 h-4 animate-spin text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                                Proposition d'appel vidéo envoyée
                                            </div>
                                            <p class="text-amber-800">En attente de réponse de l'élève ({{ $advisorCall->credits_cost }} crédits)...</p>
                                        </div>
                                    @elseif($advisorCall->isRefused())
                                        <div class="mt-2 bg-red-50 text-red-700 px-3 py-1.5 rounded-lg border border-red-200 text-xs font-bold inline-flex items-center gap-1.5">
                                            <span>❌ Le jeune a refusé l'appel vidéo.</span>
                                        </div>
                                    @endif
                                @endif
                            </div>
                            <p class="text-xs text-gray-400 mt-1 {{ $message->role === 'user' ? 'text-right' : 'text-left' }}">
                                {{ $message->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-12 text-gray-500">
                        Aucun message dans cette conversation
                    </div>
                    @endforelse
                </div>

                <!-- Zone de réponse du conseiller -->
                @if($conversation->human_support_active)
                <div class="p-4 border-t bg-orange-50">
                    <form action="{{ route('admin.chat.send-message', $conversation) }}" method="POST">
                        @csrf
                        <div class="flex gap-3">
                            <div class="flex-1">
                                <textarea name="content"
                                          rows="2"
                                          placeholder="Écrivez votre message en tant que conseiller..."
                                          class="w-full rounded-lg border-orange-200 focus:border-orange-400 focus:ring-orange-400 text-sm resize-none"
                                          required></textarea>
                            </div>
                            <div class="flex flex-col gap-2">
                                <button type="submit"
                                        class="px-6 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm font-medium h-full">
                                    Envoyer
                                </button>
                            </div>
                        </div>
                        <p class="text-xs text-orange-600 mt-2">
                            Vous répondez en tant que conseiller humain. L'utilisateur verra votre message comme venant de "Brillio".
                        </p>
                    </form>
                </div>
                @elseif($conversation->needs_human_support && !$conversation->human_support_active)
                <div class="p-4 border-t bg-red-50 text-center">
                    <p class="text-red-700 mb-3">Cet utilisateur attend un conseiller humain.</p>
                    <form action="{{ route('admin.chat.take-over', $conversation) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
                            Prendre en charge cette conversation
                        </button>
                    </form>
                </div>
                @else
                <div class="p-4 border-t bg-gray-50 text-center text-gray-500 text-sm">
                    Cette conversation est gérée par l'IA. Aucune action de votre part n'est requise.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Lecteur Vidéo Admin -->
<dialog id="adminVideoPlayerModal" class="fixed inset-0 z-50 p-0 m-0 w-full h-full max-w-none max-h-none bg-transparent backdrop:bg-gray-900/80 hidden border-0 overflow-y-auto" aria-labelledby="admin-video-modal-title">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <button type="button" aria-label="Fermer le lecteur vidéo" class="fixed inset-0 w-full h-full bg-gray-900/80 backdrop-blur-sm transition-opacity border-0 cursor-default" onclick="window.closeAdminVideoPlayer()"></button>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="relative inline-block align-bottom bg-gray-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-gray-800 z-10">
            <div class="bg-gray-900 px-6 py-4 flex justify-between items-center border-b border-gray-800">
                <h3 id="admin-video-modal-title" class="text-sm font-bold text-white flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 animate-pulse"></span>
                    Visionnage d'Enregistrement Vidéo
                </h3>
                <button type="button" onclick="window.closeAdminVideoPlayer()" class="text-gray-400 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-4 bg-black flex justify-center items-center">
                <video id="adminModalVideoElement" controls autoplay class="w-full max-h-[75vh] rounded-lg shadow-2xl">
                    <source id="adminModalVideoSource" src="" type="video/webm">
                    <track kind="captions" src="" srclang="fr" label="Français">
                </video>
            </div>
            <div class="bg-gray-900 px-6 py-3 flex justify-end">
                <button type="button" onclick="window.closeAdminVideoPlayer()" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-xs font-bold transition">
                    Fermer
                </button>
            </div>
        </div>
    </div>
</dialog>

@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    window.openAdminVideoPlayer = function(url) {
        const modal = document.getElementById('adminVideoPlayerModal');
        const video = document.getElementById('adminModalVideoElement');
        const source = document.getElementById('adminModalVideoSource');
        if (modal && video && source) {
            source.src = url;
            video.load();
            modal.classList.remove('hidden');
            if (typeof modal.showModal === 'function' && !modal.open) {
                modal.showModal();
            }
            video.play().catch(e => console.log('Autoplay handled:', e));
        }
    };

    window.closeAdminVideoPlayer = function() {
        const modal = document.getElementById('adminVideoPlayerModal');
        const video = document.getElementById('adminModalVideoElement');
        if (modal && video) {
            video.pause();
            modal.classList.add('hidden');
            if (typeof modal.close === 'function' && modal.open) {
                modal.close();
            }
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('messages-container');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }

        // Auto-polling automatique toutes les 3 secondes pour actualiser les messages en temps réel
        setInterval(async () => {
            try {
                const response = await fetch(window.location.href, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (response.ok) {
                    const html = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContent = doc.getElementById('messages-container');
                    const currentContainer = document.getElementById('messages-container');
                    if (newContent && currentContainer && newContent.innerHTML !== currentContainer.innerHTML) {
                        const isAtBottom = currentContainer.scrollHeight - currentContainer.scrollTop <= currentContainer.clientHeight + 100;
                        currentContainer.innerHTML = newContent.innerHTML;
                        if (isAtBottom) {
                            currentContainer.scrollTop = currentContainer.scrollHeight;
                        }
                    }
                }
            } catch (e) {
                console.error('Admin chat polling error:', e);
            }
        }, 3000);
    });
</script>
@endpush
@endsection
