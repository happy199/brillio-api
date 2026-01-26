@extends('layouts.jeune')

@section('title', $mentor->user->name ?? 'Profil mentor')

@section('content')
    <div class="space-y-8">
        <!-- Back Button -->
        <a href="{{ route('jeune.mentors') }}"
            class="inline-flex items-center text-gray-600 hover:text-gray-900 transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Retour aux mentors
        </a>

        <!-- Profile Header -->
        <div class="bg-gradient-to-r from-orange-500 via-red-500 to-pink-500 rounded-3xl overflow-hidden">
            <div class="p-8 text-white">
                <div class="flex flex-col md:flex-row md:items-start gap-6">
                    <div
                        class="w-28 h-28 rounded-2xl flex items-center justify-center overflow-hidden flex-shrink-0 {{ $mentor->user && $mentor->user->avatar_url ? 'bg-white shadow-lg' : 'bg-white/20 backdrop-blur-sm' }}">
                        @if($mentor->user && $mentor->user->avatar_url)
                            <img src="{{ $mentor->user->avatar_url }}" alt="{{ $mentor->user->name }}"
                                class="w-full h-full object-cover">
                        @else
                            <span
                                class="text-4xl font-bold text-white">{{ strtoupper(substr($mentor->user->name ?? '?', 0, 2)) }}</span>
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="flex items-start justify-between">
                            <div>
                                <h1 class="text-3xl font-bold">{{ $mentor->user->name ?? 'Mentor' }}</h1>
                                <p class="text-white/90 text-lg mt-1">{{ $mentor->current_position }}</p>
                                @if($mentor->current_company)
                                    <p class="text-white/70">{{ $mentor->current_company }}</p>
                                @endif
                            </div>
                            @if($mentor->is_validated)
                                <span
                                    class="px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full text-sm flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Profil verifie
                                </span>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-2 mt-4">
                            @if($mentor->specialization)
                                <span
                                    class="px-3 py-1 bg-white/20 rounded-full text-sm">{{ $mentor->specialization_label }}</span>
                            @endif
                            @if($mentor->years_of_experience)
                                <span class="px-3 py-1 bg-white/20 rounded-full text-sm">{{ $mentor->years_of_experience }} ans
                                    d'experience</span>
                            @endif
                            @if($mentor->user && $mentor->user->country)
                                <span class="px-3 py-1 bg-white/20 rounded-full text-sm">{{ $mentor->user->country }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Left Column - Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Bio -->
                @if($mentor->bio)
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">A propos</h2>
                        <p class="text-gray-600 whitespace-pre-line">{{ $mentor->bio }}</p>
                    </div>
                @endif

                <!-- Roadmap -->
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-6">Mon parcours</h2>

                    @if($mentor->roadmapSteps && $mentor->roadmapSteps->count() > 0)
                        <div class="relative">
                            <!-- Timeline Line -->
                            <div
                                class="absolute left-6 top-0 bottom-0 w-0.5 bg-gradient-to-b from-orange-500 via-red-500 to-pink-500">
                            </div>

                            <div class="space-y-8">
                                @foreach($mentor->roadmapSteps->sortBy('position') as $step)
                                    <div class="relative pl-16">
                                        <!-- Timeline Dot -->
                                        <div
                                            class="absolute left-4 w-5 h-5 bg-white border-4 border-orange-500 rounded-full transform -translate-x-1/2">
                                        </div>

                                        <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-xl p-5">
                                            <div class="flex items-start justify-between mb-2">
                                                <h3 class="font-bold text-gray-900">{{ $step->title }}</h3>
                                                @if($step->year_start || $step->year_end)
                                                    <span class="text-sm text-gray-500">
                                                        {{ $step->year_start }}{{ $step->year_end ? ' - ' . $step->year_end : '' }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if($step->organization)
                                                <p class="text-sm text-orange-600 font-medium mb-2">{{ $step->organization }}</p>
                                            @endif
                                            @if($step->description)
                                                <p class="text-gray-600 text-sm">{{ $step->description }}</p>
                                            @endif
                                            @if($step->skills && is_array($step->skills))
                                                <div class="flex flex-wrap gap-2 mt-3">
                                                    @foreach($step->skills as $skill)
                                                        <span
                                                            class="px-2 py-1 bg-white text-gray-600 text-xs rounded-full">{{ $skill }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <p class="text-gray-500">Ce mentor n'a pas encore partage son parcours.</p>
                        </div>
                    @endif
                </div>

                <!-- Advice Section -->
                @if($mentor->advice)
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Mes conseils</h2>
                        <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-xl p-5 border-l-4 border-orange-500">
                            <svg class="w-8 h-8 text-orange-400 mb-3" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
                            </svg>
                            <p class="text-gray-700 italic">{{ $mentor->advice }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Column - Sidebar -->
            <div class="space-y-6">
                <!-- Contact Card -->
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Connecter</h2>
                    <div class="space-y-3">
                        @if($mentor->linkedin_url)
                            <a href="{{ $mentor->linkedin_url }}" target="_blank" rel="noopener"
                                class="flex items-center gap-3 p-3 bg-blue-50 rounded-xl text-blue-700 hover:bg-blue-100 transition">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                                </svg>
                                Voir sur LinkedIn
                            </a>
                        @endif
                        @php
                            // Construire le message pre-rempli
                            $mentorName = $mentor->user->name ?? 'ce mentor';
                            $mentorPosition = $mentor->current_position ?? '';
                            $mentorCompany = $mentor->current_company ?? '';
                            $mentorSpecialization = $mentor->specialization_label ?? '';

                            $user = auth()->user();
                            $userName = explode(' ', $user->name ?? '')[0] ?? '';

                            // Construire les etapes du parcours pour le contexte
                            $roadmapSummary = '';
                            if ($mentor->roadmapSteps && $mentor->roadmapSteps->count() > 0) {
                                $steps = $mentor->roadmapSteps->sortBy('position')->take(3);
                                $stepDescriptions = $steps->map(fn($s) => $s->title . ($s->organization ? ' chez ' . $s->organization : ''))->implode(', ');
                                $roadmapSummary = "Son parcours inclut: " . $stepDescriptions . ".";
                            }

                            $prefilledMessage = "Bonjour ! Je suis inspire(e) par le profil de {$mentorName}";
                            if ($mentorPosition) {
                                $prefilledMessage .= " qui travaille actuellement comme {$mentorPosition}";
                                if ($mentorCompany) {
                                    $prefilledMessage .= " chez {$mentorCompany}";
                                }
                            }
                            $prefilledMessage .= ". ";
                            if ($roadmapSummary) {
                                $prefilledMessage .= $roadmapSummary . " ";
                            }
                            $prefilledMessage .= "J'aimerais avoir un parcours similaire dans le domaine " . ($mentorSpecialization ?: "de ce professionnel") . ". ";
                            $prefilledMessage .= "Quelles sont les etapes cles que je devrais suivre pour atteindre un profil similaire ? Quelles formations ou competences dois-je acquerir ?";
                        @endphp
                        <a href="{{ route('jeune.chat') }}?mentor_id={{ $mentor->id }}&prefill={{ urlencode($prefilledMessage) }}"
                            class="flex items-center justify-center gap-2 w-full py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            Discuter avec l'IA sur ce profil
                        </a>
                    </div>
                </div>

                <!-- Quick Info -->
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Infos rapides</h2>
                    <div class="space-y-4">
                        @if($mentor->specialization)
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Domaine</p>
                                    <p class="font-medium text-gray-900">{{ $mentor->specialization_label }}</p>
                                </div>
                            </div>
                        @endif
                        @if($mentor->years_of_experience)
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Experience</p>
                                    <p class="font-medium text-gray-900">{{ $mentor->years_of_experience }} ans</p>
                                </div>
                            </div>
                        @endif
                        @if($mentor->user && $mentor->user->country)
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Localisation</p>
                                    <p class="font-medium text-gray-900">
                                        {{ $mentor->user->city ? $mentor->user->city . ', ' : '' }}{{ $mentor->user->country }}
                                    </p>
                                </div>
                            </div>
                        @endif
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Etapes du parcours</p>
                                <p class="font-medium text-gray-900">
                                    {{ $mentor->roadmapSteps ? $mentor->roadmapSteps->count() : 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Similar Mentors -->
                @if(isset($similarMentors) && $similarMentors->count() > 0)
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Mentors similaires</h2>
                        <div class="space-y-3">
                            @foreach($similarMentors->take(3) as $similar)
                                <a href="{{ route('jeune.mentors.show', $similar) }}"
                                    class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-lg transition">
                                    <div
                                        class="w-10 h-10 rounded-lg flex items-center justify-center overflow-hidden flex-shrink-0 {{ $similar->user && $similar->user->avatar_url ? '' : 'bg-gradient-to-br from-orange-400 to-red-500' }}">
                                        @if($similar->user && $similar->user->avatar_url)
                                            <img src="{{ $similar->user->avatar_url }}" alt="{{ $similar->user->name }}"
                                                class="w-full h-full object-cover">
                                        @else
                                            <span
                                                class="text-white font-bold text-sm">{{ strtoupper(substr($similar->user->name ?? '?', 0, 2)) }}</span>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900 truncate">{{ $similar->user->name ?? 'Mentor' }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ $similar->current_position }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection