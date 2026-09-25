@extends('layouts.admin')

@section('title', 'Détails Évaluation Séance : ' . $session->title)

@section('content')
<div class="space-y-6 max-w-6xl mx-auto" x-data="{ stopModalOpen: false }">
    <!-- Navigation Back Button -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.mentorship.evaluations') }}"
            class="inline-flex items-center text-gray-600 hover:text-gray-900 transition font-medium text-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Retour aux évaluations des séances
        </a>

        @if($mentorship && $mentorship->status !== 'disconnected')
            <button @click="stopModalOpen = true"
                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-sm rounded-xl transition shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
                Arrêter la relation de mentorat
            </button>
        @else
            <span class="px-3 py-1 bg-red-100 text-red-800 font-bold text-xs rounded-full border border-red-200">
                Relation de mentorat interrompue
            </span>
        @endif
    </div>

    <!-- Header Summary Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $session->title }}</h1>
                    @if($session->is_first_session)
                        <span class="px-3 py-1 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-full border border-indigo-200">Première séance</span>
                    @endif
                </div>
                <p class="text-gray-500 text-sm mt-1">
                    Séance du {{ $session->full_scheduled_at_with_gmt }} • Durée : {{ $session->duration_minutes }} min
                </p>
            </div>

            <div class="flex items-center gap-4 border-t md:border-t-0 pt-4 md:pt-0 border-gray-100">
                <!-- Mentor Info -->
                <div class="flex items-center gap-3 bg-gray-50 p-3 rounded-xl border border-gray-100">
                    <div class="w-10 h-10 rounded-full bg-indigo-600 text-white font-bold flex items-center justify-center text-sm">
                        {{ strtoupper(substr($session->mentor->name ?? 'M', 0, 2)) }}
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-medium">Mentor</p>
                        <p class="font-bold text-gray-900 text-sm">{{ $session->mentor->name }}</p>
                    </div>
                </div>

                <!-- Mentee Info -->
                @if($mentee)
                    <div class="flex items-center gap-3 bg-gray-50 p-3 rounded-xl border border-gray-100">
                        <div class="w-10 h-10 rounded-full bg-purple-600 text-white font-bold flex items-center justify-center text-sm">
                            {{ strtoupper(substr($mentee->name ?? 'J', 0, 2)) }}
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 font-medium">Jeune / Menté</p>
                            <p class="font-bold text-gray-900 text-sm">{{ $mentee->name }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Grid: Video Player + Observations -->
    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Media, Transcription & Mentee Evaluation -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Video Recording Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    Enregistrement Vidéo de la séance
                </h2>

                @if($session->video_recording_url)
                    <div class="rounded-xl overflow-hidden bg-black shadow-inner">
                        <video controls class="w-full max-h-[420px] rounded-xl" preload="metadata">
                            <source src="{{ $session->video_recording_url }}" type="video/webm">
                            <source src="{{ $session->video_recording_url }}" type="video/mp4">
                            Votre navigateur ne prend pas en charge la lecture vidéo.
                        </video>
                    </div>
                    <div class="mt-3 flex justify-end">
                        <a href="{{ $session->video_recording_url }}" target="_blank" download
                            class="text-xs text-indigo-600 hover:text-indigo-800 font-bold flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Ouvrir/Télécharger la vidéo originale
                        </a>
                    </div>
                @else
                    <div class="bg-gray-50 border border-dashed border-gray-300 rounded-xl p-8 text-center text-gray-500">
                        <svg class="w-10 h-10 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <p class="font-medium">Aucun enregistrement vidéo disponible pour cette séance.</p>
                        <p class="text-xs text-gray-400 mt-1">L'enregistrement vidéo n'a pas été transmis ou la séance n'a pas eu lieu dans la salle intégrée.</p>
                    </div>
                @endif
            </div>

            <!-- Youth Evaluation Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    Évaluation laissée par le Jeune
                </h2>

                @php
                    $eval = $session->evaluations->first();
                @endphp

                @if($eval)
                    <div class="bg-amber-50/50 border border-amber-200/60 rounded-xl p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1 text-amber-400">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-5 h-5 {{ $i <= $eval->rating ? 'fill-current' : 'text-gray-300 fill-current' }}" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                                <span class="ml-2 font-bold text-gray-900 text-base">{{ $eval->rating }} / 5</span>
                            </div>
                            <span class="text-xs text-gray-400">{{ $eval->created_at->format('d/m/Y H:i') }}</span>
                        </div>

                        @if($eval->comment)
                            <p class="text-sm text-gray-700 italic whitespace-pre-wrap bg-white/80 p-3 rounded-lg border border-amber-100">"{{ trim($eval->comment) }}"</p>
                        @else
                            <p class="text-xs text-gray-400 italic">Aucun commentaire textuel laissé par le jeune.</p>
                        @endif
                    </div>
                @else
                    <p class="text-gray-500 text-sm italic">Le jeune n'a pas encore laissé d'évaluation pour cette séance.</p>
                @endif
            </div>

            <!-- Mentor Session Report -->
            @if($session->report_content)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Compte-rendu rédigé par le Mentor
                    </h2>

                    <div class="space-y-4">
                        @if(!empty($session->report_content['progress']))
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Progrès réalisés :</p>
                                <div class="bg-green-50/50 p-3 rounded-xl text-sm text-gray-800 border-l-4 border-green-500">
                                    {{ $session->report_content['progress'] }}
                                </div>
                            </div>
                        @endif

                        @if(!empty($session->report_content['obstacles']))
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Obstacles & Défis :</p>
                                <div class="bg-red-50/50 p-3 rounded-xl text-sm text-gray-800 border-l-4 border-red-500">
                                    {{ $session->report_content['obstacles'] }}
                                </div>
                            </div>
                        @endif

                        @if(!empty($session->report_content['smart_goals']))
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Objectifs suivants :</p>
                                <div class="bg-blue-50/50 p-3 rounded-xl text-sm text-gray-800 border-l-4 border-blue-500">
                                    {{ $session->report_content['smart_goals'] }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Right 1 Col: Admin Quality Evaluation Form -->
        <div class="space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-indigo-100 p-6 sticky top-6">
                <h2 class="text-lg font-bold text-indigo-950 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Évaluation Qualité Admin
                </h2>

                <form action="{{ route('admin.mentorship.evaluations.observation', $session) }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label for="admin_evaluation_status" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">
                            Statut de Conformité :
                        </label>
                        <select id="admin_evaluation_status" name="admin_evaluation_status" required
                            class="w-full border-gray-300 rounded-xl text-sm p-3 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="validated" {{ old('admin_evaluation_status', $session->admin_evaluation_status) === 'validated' ? 'selected' : '' }}>
                                ✅ Séance conforme & validée
                            </option>
                            <option value="warning_issued" {{ old('admin_evaluation_status', $session->admin_evaluation_status) === 'warning_issued' ? 'selected' : '' }}>
                                ⚠️ Avertissement (Ré-enregistrer séance suivante)
                            </option>
                            <option value="needs_revision" {{ old('admin_evaluation_status', $session->admin_evaluation_status) === 'needs_revision' ? 'selected' : '' }}>
                                🔍 À réviser / Complément demandé
                            </option>
                            <option value="terminated" {{ old('admin_evaluation_status', $session->admin_evaluation_status) === 'terminated' ? 'selected' : '' }}>
                                🚫 Relation interrompue
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="admin_observation" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">
                            Observations & Remarques Admin :
                        </label>
                        <textarea id="admin_observation" name="admin_observation" rows="5" required
                            class="w-full border-gray-300 rounded-xl text-sm p-3 focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Notez vos remarques sur le déroulement de la séance, le ton, la clarté des objectifs, les points d'amélioration...">{{ old('admin_observation', $session->admin_observation) }}</textarea>
                    </div>

                    @if($session->admin_reviewed_at)
                        <div class="bg-indigo-50 p-3 rounded-xl text-xs text-indigo-900 border border-indigo-100">
                            <strong>Dernier contrôle :</strong> par {{ $session->adminReviewer->name ?? 'Admin' }} le {{ $session->admin_reviewed_at->format('d/m/Y à H:i') }}
                        </div>
                    @endif

                    <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition shadow-md flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Enregistrer l'évaluation
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Stop Mentorship Relationship -->
    @if($mentee)
        <div x-show="stopModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div @click.away="stopModalOpen = false" @keydown.escape.window="stopModalOpen = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
                <div class="flex items-center gap-3 text-red-600">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-gray-900">Interrompre la relation de mentorat ?</h3>
                </div>

                <p class="text-sm text-gray-600">
                    Cette action arrêtera immédiatement la relation entre le mentor <strong>{{ $session->mentor->name }}</strong> et le jeune <strong>{{ $mentee->name }}</strong>.
                </p>

                <form action="{{ route('admin.mentorship.evaluations.stop-relationship', $session) }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="mentee_id" value="{{ $mentee->id }}">
                    <div>
                        <label for="reason" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Motif de l'interruption :</label>
                        <textarea id="reason" name="reason" rows="3" required
                            class="w-full border-gray-300 rounded-xl text-sm p-3 focus:border-red-500 focus:ring-red-500"
                            placeholder="Indiquez la raison de la résiliation de cette relation de mentorat..."></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="stopModalOpen = false" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-200">
                            Annuler
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-xl text-sm font-bold hover:bg-red-700 shadow">
                            Confirmer l'arrêt de la relation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
