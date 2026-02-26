@extends('layouts.organization')

@section('title', 'Profil de ' . $user->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('organization.users.index') }}"
                class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                <svg class="mr-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Retour
            </a>
            <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
        </div>
        <div class="flex items-center space-x-3">
            <!-- Export Options -->
            <div class="flex items-center bg-white border border-gray-200 rounded-lg shadow-sm mr-2">
                <a href="{{ route('organization.users.export', [$user, 'format' => 'pdf']) }}"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 rounded-l-lg border-r border-gray-200"
                    title="Télécharger en PDF">
                    <svg class="mr-2 h-4 w-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" />
                    </svg>
                    PDF
                </a>
                <a href="{{ route('organization.users.export', [$user, 'format' => 'csv']) }}"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 rounded-r-lg"
                    title="Télécharger en CSV">
                    <svg class="mr-2 h-4 w-4 text-organization-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                    CSV
                </a>
            </div>

            <div class="text-right mr-2 hidden sm:block">
                <div class="text-xs text-gray-500 uppercase font-semibold">Complétion</div>
                <div class="text-sm font-bold text-indigo-600">{{ $user->profile_completion_percentage }}%</div>
            </div>
            <span
                class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium {{ $user->profile_completion_percentage === 100 ? 'bg-organization-100 text-organization-800' : 'bg-yellow-100 text-yellow-800' }}">
                {{ $user->profile_completion_percentage === 100 ? 'Complet' : 'Incomplet' }}
            </span>
        </div>
    </div>

    <!-- Main Content Container with proportional blur/lock for non-pro -->
    <div class="relative min-h-[600px]">
        @if(!$organization->isPro())
        <div
            class="absolute inset-0 z-10 bg-white/60 backdrop-blur-[4px] rounded-lg flex flex-col items-center justify-center text-center p-8">
            <div class="bg-white p-8 rounded-xl shadow-2xl border border-gray-200 max-w-md sticky top-1/3">
                <div
                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 mb-6">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Fonctionnalité Pro</h3>
                <p class="text-gray-500 mb-8">
                    L'accès détaillé au profil des jeunes, incluant leurs documents, activités et mentorats, est réservé
                    aux membres Pro.
                </p>
                <a href="{{ route('organization.subscriptions.index') }}"
                    class="inline-flex w-full justify-center items-center rounded-md bg-indigo-600 px-5 py-3 text-base font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-colors">
                    Passer au plan Pro
                </a>
            </div>
        </div>
        @endif

        <div
            class="grid grid-cols-1 gap-6 lg:grid-cols-3 {{ !$organization->isPro() ? 'filter blur-[6px] select-none pointer-events-none opacity-50' : '' }}">
            <!-- Left Column -->
            <div class="space-y-6 lg:col-span-1">
                <!-- Profile Card -->
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="flex flex-col items-center">
                        @if($user->avatar_url)
                        <img class="h-32 w-32 rounded-full object-cover" src="{{ $user->avatar_url }}"
                            alt="{{ $user->name }}">
                        @else
                        <div
                            class="h-32 w-32 rounded-full bg-organization-100 flex items-center justify-center text-organization-600 font-bold text-3xl">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        @endif
                        <h2 class="mt-4 text-xl font-bold text-gray-900">{{ $user->name }}</h2>
                        <p class="text-gray-500">{{ $user->email }}</p>
                        <p class="mt-1 text-sm text-gray-500">Inscrit le {{ $user->created_at->format('d/m/Y') }}</p>
                    </div>

                    <div class="mt-6 border-t border-gray-100 pt-6 space-y-4">
                        @if($user->city || $user->country)
                        <div class="flex items-center text-sm text-gray-600">
                            <svg class="mr-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ $user->city ? $user->city . ', ' : '' }}{{ $user->country }}
                        </div>
                        @endif

                        @if($user->phone)
                        <div class="flex items-center text-sm text-gray-600">
                            <svg class="mr-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            {{ $user->phone }}
                        </div>
                        @endif

                        @if($user->date_of_birth)
                        <div class="flex items-center text-sm text-gray-600">
                            <svg class="mr-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Né(e) le {{ $user->date_of_birth->format('d/m/Y') }}
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Profile Completion Card -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Complétion du Profil</h3>
                    <div class="relative pt-1">
                        <div class="flex mb-2 items-center justify-between">
                            <div>
                                <span
                                    class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-indigo-600 bg-indigo-100">
                                    Progression
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-semibold inline-block text-indigo-600">
                                    {{ $user->profile_completion_percentage }}%
                                </span>
                            </div>
                        </div>
                        <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-indigo-50">
                            <div style="width:{{ $user->profile_completion_percentage }}%"
                                class="shadow-none flex flex-col text-center white-space-nowrap text-white justify-center bg-indigo-500 transition-all duration-500">
                            </div>
                        </div>
                    </div>

                    @if($user->profile_completion_percentage < 100) <div class="mt-4">
                        <p class="text-sm font-medium text-gray-700 mb-2">Champs à compléter :</p>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <ul class="text-xs text-gray-500 space-y-1.5">
                                @foreach($user->missing_profile_fields as $field)
                                <li class="flex items-center gap-2">
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                    {{ $field }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                </div>
                @else
                <div class="mt-4 p-3 bg-organization-50 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5 text-organization-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm text-organization-700 font-medium">Profil complété à 100%</p>
                </div>
                @endif
            </div>

            <!-- Personality Card -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Personnalité MBTI</h3>
                @if($user->personalityTest && $user->personalityTest->completed_at)
                <div class="text-center">
                    <span
                        class="inline-flex items-center px-4 py-2 rounded-full text-2xl font-bold bg-purple-100 text-purple-800">
                        {{ $user->personalityTest->personality_type }}
                    </span>
                    <p class="mt-2 font-medium text-gray-900">{{ $user->personalityTest->personality_label }}</p>
                    <p class="mt-2 text-sm text-gray-500 text-justify">{{
                        $user->personalityTest->personality_description }}</p>
                </div>

                @if(isset($user->personalityTest->traits_scores))
                <div class="mt-6 space-y-3">
                    @foreach($user->personalityTest->traits_scores as $trait => $score)
                    @if(in_array($trait, ['E', 'S', 'T', 'J'])) <!-- Show only primary traits for simplicity -->
                    <div>
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>{{ $trait }}</span>
                            <span>{{ $score }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                            <div class="bg-purple-600 h-1.5 rounded-full" style="width: {{ $score }}%"></div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
                @endif
                @else
                <div class="text-center py-6">
                    <p class="text-gray-500 text-sm">Le test n'a pas encore été passé.</p>
                </div>
                @endif
            </div>

            <!-- Onboarding Data Card -->
            @if($user->onboarding_data)
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Parcours & Objectifs</h3>

                <div class="space-y-4">
                    @if(isset($user->onboarding_data['current_situation']))
                    <div>
                        <p class="text-sm font-medium text-gray-500">Situation actuelle</p>
                        <p class="mt-1 text-gray-900">
                            @switch($user->onboarding_data['current_situation'])
                            @case('student') Étudiant @break
                            @case('employed') En emploi @break
                            @case('seeking') En recherche d'emploi @break
                            @case('training') En formation @break
                            @default {{ ucfirst($user->onboarding_data['current_situation'] ?? '') }}
                            @endswitch
                        </p>
                    </div>
                    @endif

                    @if(isset($user->onboarding_data['education_level']))
                    <div>
                        <p class="text-sm font-medium text-gray-500">Niveau d'études</p>
                        <p class="mt-1 text-gray-900">
                            @switch($user->onboarding_data['education_level'])
                            @case('college') Collège @break
                            @case('lycee') Lycée @break
                            @case('bac') Bac @break
                            @case('bac_2') Bac +2 (BTS, DUT...) @break
                            @case('licence') Licence (Bac +3) @break
                            @case('master') Master (Bac +5) @break
                            @case('doctorat') Doctorat @break
                            @default {{ ucfirst($user->onboarding_data['education_level'] ?? '') }}
                            @endswitch
                        </p>
                    </div>
                    @endif

                    @if(isset($user->onboarding_data['goals']) && is_array($user->onboarding_data['goals']))
                    <div>
                        <p class="text-sm font-medium text-gray-500">Objectifs</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($user->onboarding_data['goals'] as $goal)
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                @switch($goal)
                                @case('orientation') M'orienter @break
                                @case('career') Trouver un métier @break
                                @case('training') Trouver une formation @break
                                @case('job') Trouver un emploi @break
                                @case('ia') Découvrir l'IA @break
                                @case('personnalite') Mieux me connaître @break
                                @default {{ ucfirst($goal) }}
                                @endswitch
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if(isset($user->onboarding_data['interests']) && is_array($user->onboarding_data['interests']))
                    <div>
                        <p class="text-sm font-medium text-gray-500">Centres d'intérêt</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($user->onboarding_data['interests'] as $interest)
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                {{ $interest }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($user->jeuneProfile)
                    <div class="pt-4 border-t border-gray-100 space-y-4">
                        @if($user->jeuneProfile->bio)
                        <div>
                            <p class="text-sm font-medium text-gray-500">Bio / À propos</p>
                            <p class="mt-1 text-sm text-gray-600 text-justify">{{ $user->jeuneProfile->bio }}</p>
                        </div>
                        @endif

                        <div class="flex flex-wrap gap-4">
                            @if($user->jeuneProfile->portfolio_url)
                            <a href="{{ $user->jeuneProfile->portfolio_url }}" target="_blank"
                                class="text-sm text-organization-600 hover:text-organization-500 flex items-center">
                                <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                Portfolio
                            </a>
                            @endif

                            @if($user->jeuneProfile->cv_path)
                            <a href="{{ asset('storage/' . $user->jeuneProfile->cv_path) }}" target="_blank"
                                class="text-sm text-organization-600 hover:text-organization-500 flex items-center">
                                <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Voir le CV
                            </a>
                            @endif

                            @if($user->linkedin_url)
                            <a href="{{ $user->linkedin_url }}" target="_blank"
                                class="text-sm text-organization-600 hover:text-organization-500 flex items-center">
                                <svg class="mr-1 h-4 w-4 fill-current" viewBox="0 0 24 24">
                                    <path
                                        d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                                </svg>
                                LinkedIn
                            </a>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="space-y-6 lg:col-span-2">
            <!-- Activity Stats -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Conversations IA</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $aiConversationsCount }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-organization-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-organization-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Dernière activité IA</dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        {{ $lastAiActivity ? \Carbon\Carbon::parse($lastAiActivity)->diffForHumans() :
                                        'Jamais' }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Documents Académiques</h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    @if($user->academicDocuments->count() > 0)
                    <ul class="divide-y divide-gray-200">
                        @foreach($user->academicDocuments as $doc)
                        <li class="py-3 flex justify-between items-center">
                            <div class="flex items-center">
                                <svg class="flex-shrink-0 h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span class="ml-2 text-sm text-gray-900">{{ $doc->file_name }}</span>
                                <span
                                    class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    {{ $doc->document_type }}
                                </span>
                            </div>
                            <span class="text-sm text-gray-500">{{ $doc->created_at->format('d/m/Y') }}</span>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <p class="text-sm text-gray-500 italic">Aucun document téléchargé.</p>
                    @endif
                </div>
            </div>

            <!-- Mentorship -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Mentorat</h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    @if($mentorships->count() > 0)
                    <div class="space-y-6">
                        @foreach($mentorships as $mentorship)
                        <div class="border rounded-lg p-4 bg-gray-50">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="h-10 w-10 rounded-full bg-organization-100 flex items-center justify-center text-organization-600 font-bold">
                                        {{ substr($mentorship->mentor->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('organization.mentors.show', $mentorship->mentor->mentorProfile) }}"
                                            class="text-sm font-bold text-gray-900 hover:text-indigo-600 transition">
                                            {{ $mentorship->mentor->name }}
                                        </a>
                                        <p class="text-xs text-gray-500">{{
                                            $mentorship->mentor->mentorProfile?->current_position }}</p>
                                    </div>
                                </div>
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ ($mentorship->status === 'active' || $mentorship->status === 'accepted') ? 'bg-organization-100 text-organization-800' : 'bg-blue-100 text-blue-800' }}">
                                    @switch($mentorship->status)
                                    @case('active') Actif @break
                                    @case('accepted') Accepté @break
                                    @case('pending') En attente @break
                                    @case('completed') Terminé @break
                                    @default {{ ucfirst($mentorship->status) }}
                                    @endswitch
                                </span>
                            </div>
                            <div class="text-xs text-gray-500 space-y-1">
                                <p>Débuté le {{ $mentorship->created_at->format('d/m/Y') }}</p>
                                <p>{{ $mentorship->sessions_count }} séances effectuées</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-6 text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <p class="mt-2 text-sm italic">Aucun mentorat actif.</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Ressources Engagement -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Ressources & Contenus</h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <div class="space-y-6">
                        <!-- Purchased -->
                        <div>
                            <h4
                                class="text-sm font-semibold text-gray-700 mb-3 border-l-4 border-organization-500 pl-2">
                                Ressources débloquées</h4>
                            @if($purchasedResources->count() > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($purchasedResources as $resource)
                                <div class="bg-gray-50 border rounded-md p-3">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $resource->title }}</p>
                                    <p class="text-xs text-organization-600 font-medium">Débloquée le {{
                                        $resource->pivot?->purchased_at ?
                                        \Carbon\Carbon::parse($resource->pivot->purchased_at)->format('d/m/Y') : '' }}
                                    </p>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <p class="text-xs text-gray-500 italic ml-4">Aucune ressource payante débloquée.</p>
                            @endif
                        </div>

                        <!-- Viewed -->
                        <div>
                            <h4
                                class="text-sm font-semibold text-gray-700 mb-3 border-l-4 border-blue-500 pl-2 text-justify">
                                Ressources consultées</h4>
                            @if($viewedResources->count() > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($viewedResources as $resource)
                                <div class="bg-gray-50 border rounded-md p-3">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $resource->title }}</p>
                                    <p class="text-xs text-gray-500">Vue le {{ $resource->pivot?->viewed_at ?
                                        \Carbon\Carbon::parse($resource->pivot->viewed_at)->format('d/m/Y') : '' }}</p>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <p class="text-xs text-gray-500 italic ml-4">Aucune ressource consultée.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mentors Consultés -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Mentors consultés</h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    @if($consultedMentors->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($consultedMentors as $mentor)
                        <div class="flex items-center space-x-3 p-3 border rounded-lg bg-gray-50">
                            <div
                                class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                                {{ substr($mentor->name, 0, 1) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('organization.mentors.show', $mentor->mentorProfile) }}"
                                    class="text-sm font-medium text-gray-900 truncate hover:text-indigo-600 transition block">
                                    {{ $mentor->name }}
                                </a>
                                <p class="text-xs text-gray-500 truncate">{{ $mentor->mentorProfile?->current_position
                                    }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-6 text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <p class="mt-2 text-sm italic">Aucun mentor consulté.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection