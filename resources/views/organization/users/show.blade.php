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
        <div class="flex space-x-3">
            <span
                class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium {{ $user->onboarding_completed ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                {{ $user->onboarding_completed ? 'Actif' : 'Incomplet' }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
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
                        <svg class="mr-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        <svg class="mr-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        {{ $user->phone }}
                    </div>
                    @endif
                </div>
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
                            <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor"
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

            <!-- Mentorship (Placeholder) -->
            <div class="bg-white shadow rounded-lg opacity-75">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
                        Mentorat
                        <span class="ml-2 px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Bientôt
                            disponible</span>
                    </h3>
                </div>
                <div class="px-4 py-5 sm:p-6 text-center text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <p class="mt-2">Le suivi détaillé du mentorat sera disponible prochainement.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection