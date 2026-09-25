@extends('layouts.admin')

@section('title', 'Évaluation des séances de mentorat')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Évaluation des séances de mentorat</h1>
            <p class="text-gray-500 text-sm mt-1">
                Assurance qualité et contrôle des premières séances de mentorat entre mentors et jeunes.
            </p>
        </div>
        <div class="flex items-center gap-2">
            @if($firstSessionsOnly)
                <a href="{{ route('admin.mentorship.evaluations', array_merge(request()->except(['page']), ['first_only' => 0])) }}"
                    class="px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition shadow-sm flex items-center gap-2"
                    title="Cliquer pour afficher l'ensemble des séances">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    Toutes les séances
                </a>
            @else
                <a href="{{ route('admin.mentorship.evaluations', array_merge(request()->except(['page']), ['first_only' => 1])) }}"
                    class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50 transition shadow-sm flex items-center gap-2"
                    title="Cliquer pour filtrer uniquement les 1ères séances">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    1ères séances uniquement
                </a>
            @endif
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Premières séances</p>
                    <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ number_format($stats['total_first']) }}</p>
                </div>
                <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">À contrôler par Admin</p>
                    <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ number_format($stats['pending_review']) }}</p>
                </div>
                <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Séances contrôlées</p>
                    <p class="text-2xl font-extrabold text-green-600 mt-1">{{ number_format($stats['reviewed']) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Enregistrements Vidéo</p>
                    <p class="text-2xl font-extrabold text-purple-600 mt-1">{{ number_format($stats['with_video']) }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
        <form action="{{ route('admin.mentorship.evaluations') }}" method="GET" class="grid md:grid-cols-4 gap-4">
            <input type="hidden" name="first_only" value="{{ $firstSessionsOnly ? 1 : 0 }}">
            <div class="md:col-span-2">
                <label for="search" class="block text-xs font-semibold text-gray-600 mb-1">Recherche (Nom mentor, jeune ou titre)</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}"
                    placeholder="Chercher par nom de mentor ou de jeune..."
                    class="w-full border-gray-300 rounded-xl text-sm p-2.5 focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label for="review_status" class="block text-xs font-semibold text-gray-600 mb-1">Statut d'évaluation Admin</label>
                <select id="review_status" name="review_status"
                    class="w-full border-gray-300 rounded-xl text-sm p-2.5 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Tous les statuts</option>
                    <option value="pending" {{ request('review_status') === 'pending' ? 'selected' : '' }}>En attente de contrôle</option>
                    <option value="reviewed" {{ request('review_status') === 'reviewed' ? 'selected' : '' }}>Contrôlées (Visionnées)</option>
                    <option value="validated" {{ request('review_status') === 'validated' ? 'selected' : '' }}>Conformes</option>
                    <option value="warning_issued" {{ request('review_status') === 'warning_issued' ? 'selected' : '' }}>Avertissement émis</option>
                    <option value="terminated" {{ request('review_status') === 'terminated' ? 'selected' : '' }}>Relation interrompue</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition shadow-sm">
                    Filtrer
                </button>
                <a href="{{ route('admin.mentorship.evaluations') }}" class="px-3 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-semibold rounded-xl transition">
                    Réinitialiser
                </a>
            </div>
        </form>
    </div>

    <!-- Sessions List Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 uppercase text-[11px] font-bold tracking-wider border-b border-gray-100">
                        <th class="py-3.5 px-6">Séance & Date</th>
                        <th class="py-3.5 px-6">Mentor</th>
                        <th class="py-3.5 px-6">Jeune / Menté</th>
                        <th class="py-3.5 px-6">Évaluation Jeune</th>
                        <th class="py-3.5 px-6">Vidéo & Transcription</th>
                        <th class="py-3.5 px-6">Contrôle Admin</th>
                        <th class="py-3.5 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($sessions as $session)
                        @php
                            $mentee = $session->mentees->first();
                            $eval = $session->evaluations->first();
                        @endphp
                        <tr class="hover:bg-gray-50/80 transition">
                            <!-- Title & Date -->
                            <td class="py-4 px-6">
                                <div class="font-bold text-gray-900 flex items-center gap-2">
                                    {{ $session->title }}
                                    @if($session->is_first_session)
                                        <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 text-[10px] font-bold rounded-full border border-indigo-200">1ère Séance</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $session->full_scheduled_at_with_gmt }}</p>
                            </td>

                            <!-- Mentor -->
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                        {{ strtoupper(substr($session->mentor->name ?? 'M', 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 text-sm leading-tight">{{ $session->mentor->name }}</p>
                                        @if($session->mentor->mentorProfile && $session->mentor->mentorProfile->average_rating)
                                            <p class="text-[11px] text-amber-600 font-bold flex items-center gap-0.5">
                                                ★ {{ number_format($session->mentor->mentorProfile->average_rating, 1) }} / 5
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Mentee -->
                            <td class="py-4 px-6">
                                @if($mentee)
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                            {{ strtoupper(substr($mentee->name ?? 'J', 0, 2)) }}
                                        </div>
                                        <p class="font-semibold text-gray-900 text-sm">{{ $mentee->name }}</p>
                                    </div>
                                @else
                                    <span class="text-gray-400 italic text-xs">Aucun menté</span>
                                @endif
                            </td>

                            <!-- Rating Youth -->
                            <td class="py-4 px-6 whitespace-nowrap">
                                @if($eval)
                                    <div>
                                        <div class="flex items-center text-amber-400 gap-0.5 text-sm">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-4 h-4 {{ $i <= $eval->rating ? 'fill-current' : 'text-gray-300 fill-current' }}" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endfor
                                            <span class="text-xs font-bold text-gray-700 ml-1">{{ $eval->rating }}/5</span>
                                        </div>
                                        @if($eval->comment)
                                            <p class="text-xs text-gray-500 mt-1 italic truncate max-w-xs" title="{{ $eval->comment }}">
                                                "{{ Str::limit($eval->comment, 40) }}"
                                            </p>
                                        @endif
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-gray-100 text-gray-500 rounded-full text-xs font-medium border border-gray-200/60 whitespace-nowrap shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Non évaluée par le jeune
                                    </span>
                                @endif
                            </td>

                            <!-- Media -->
                            <td class="py-4 px-6">
                                <div class="flex flex-col gap-1">
                                    @if($session->video_recording_url)
                                        <span class="inline-flex items-center gap-1 text-xs font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded border border-purple-200 w-fit">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                            Vidéo disponible
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">Pas de vidéo</span>
                                    @endif

                                    @if($session->has_transcription)
                                        <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 w-fit">
                                            Transcription dispo
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Admin Control Status -->
                            <td class="py-4 px-6 whitespace-nowrap">
                                @if($session->admin_reviewed_at)
                                    <div>
                                        @if($session->admin_evaluation_status === 'validated')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-green-50 text-green-700 rounded-full text-xs font-bold border border-green-200 whitespace-nowrap shadow-sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Conforme
                                            </span>
                                        @elseif($session->admin_evaluation_status === 'warning_issued')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-800 rounded-full text-xs font-bold border border-amber-200 whitespace-nowrap shadow-sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Avertissement
                                            </span>
                                        @elseif($session->admin_evaluation_status === 'terminated')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-50 text-red-700 rounded-full text-xs font-bold border border-red-200 whitespace-nowrap shadow-sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Relation Interrompue
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-bold border border-blue-200 whitespace-nowrap shadow-sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>Visionné
                                            </span>
                                        @endif
                                        <p class="text-[10px] text-gray-400 mt-1 whitespace-nowrap">Par {{ $session->adminReviewer->name ?? 'Admin' }} le {{ $session->admin_reviewed_at->format('d/m/Y') }}</p>
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-800 border border-amber-200/80 rounded-full text-xs font-bold whitespace-nowrap shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>En attente de contrôle
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-6 text-right">
                                <a href="{{ route('admin.mentorship.evaluations.show', $session) }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-bold text-xs rounded-xl transition shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Évaluer / Contrôler
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-500">
                                Aucune séance correspondant aux critères de recherche.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-100">
            {{ $sessions->links() }}
        </div>
    </div>
</div>
@endsection
