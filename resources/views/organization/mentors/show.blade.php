@extends('layouts.organization')

@section('title', 'Profil Mentor - ' . $mentor->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('organization.mentors.index') }}"
                class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                <svg class="mr-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Retour
            </a>
            <h1 class="text-2xl font-bold text-gray-900">{{ $mentor->name }}</h1>
        </div>
        <div class="flex items-center space-x-3">
            @if($organization->isPro())
            <!-- Export Options -->
            <div class="flex items-center bg-white border border-gray-200 rounded-lg shadow-sm">
                <a href="{{ route('organization.mentors.export-pdf', $mentor->mentorProfile) }}"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 rounded-l-lg border-r border-gray-200"
                    title="Télécharger en PDF">
                    <svg class="mr-2 h-4 w-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" />
                    </svg>
                    PDF
                </a>
                <a href="{{ route('organization.mentors.export-csv', $mentor->mentorProfile) }}"
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
            @endif
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="relative min-h-[600px]">
        @if(!$organization->isPro())
        <div
            class="absolute inset-0 z-10 bg-white/60 backdrop-blur-[4px] rounded-lg flex flex-col items-center justify-center text-center p-8">
            <div class="bg-white p-8 rounded-xl shadow-2xl border border-gray-200 max-w-md sticky top-1/3 text-center">
                <div
                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 mb-6 font-bold">
                    <svg class="h-8 w-8 text-organization-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Profil Mentor Verrouillé</h3>
                <p class="text-gray-500 mb-8">
                    L'accès détaillé au profil des mentors, incluant leurs spécialisations et l'historique de leurs
                    séances avec vos jeunes, est réservé au plan Pro.
                </p>
                <a href="{{ route('organization.subscription.index') }}"
                    class="inline-flex w-full justify-center items-center rounded-md bg-organization-600 px-5 py-3 text-base font-semibold text-white shadow-sm hover:bg-organization-700 transition-colors">
                    Passer au plan Pro
                </a>
            </div>
        </div>
        @endif

        <div
            class="grid grid-cols-1 gap-6 lg:grid-cols-3 {{ !$organization->isPro() ? 'filter blur-[6px] select-none pointer-events-none opacity-50' : '' }}">
            <!-- Left Column: Identity & Bio -->
            <div class="space-y-6 lg:col-span-1">
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="flex flex-col items-center">
                        @if($mentor->avatar_url)
                        <img class="h-32 w-32 rounded-full object-cover border-4 border-organization-50"
                            src="{{ $mentor->avatar_url }}" alt="{{ $mentor->name }}">
                        @else
                        <div
                            class="h-32 w-32 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-3xl border-4 border-indigo-50">
                            {{ substr($mentor->name, 0, 1) }}
                        </div>
                        @endif
                        <div class="flex items-center gap-2 mt-4">
                            <h2 class="text-xl font-bold text-gray-900">{{ $mentor->name }}</h2>
                            @if($mentor->mentorProfile->is_validated)
                            <span
                                class="inline-flex items-center justify-center w-5 h-5 bg-green-100 text-green-600 rounded-full"
                                title="Profil vérifié">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </span>
                            @endif
                        </div>
                        <p class="text-sm text-indigo-600 font-medium">{{ $mentor->mentorProfile->current_position ??
                            'Mentor' }}</p>
                        <p class="text-gray-500 text-sm italic">{{ $mentor->mentorProfile->current_company ?? '-' }}</p>
                    </div>

                    <div class="mt-6 border-t border-gray-100 pt-6 space-y-4">
                        <div class="flex items-center text-sm text-gray-600">
                            <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            {{ $mentor->email }}
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ $mentor->city ?? 'France' }}
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            {{ $mentor->mentorProfile->years_of_experience ?? '-' }} ans d'expérience
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Bio & Spécialisation</h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Spécialisation</p>
                            <p
                                class="mt-1 text-sm font-medium text-indigo-700 bg-indigo-50 px-3 py-1 rounded-full inline-block">
                                {{ $mentor->mentorProfile->specialization_label ?? '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Bio</p>
                            <p class="mt-2 text-sm text-gray-600 leading-relaxed italic">
                                "{{ $mentor->mentorProfile->bio ?? 'Pas de bio renseignée.' }}"
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Stats & Sessions -->
            <div class="space-y-6 lg:col-span-2">
                <!-- Activity Stats -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="bg-white overflow-hidden shadow rounded-xl border border-gray-100 p-5">
                        <div class="flex items-center">
                            <div class="p-3 bg-organization-100 rounded-lg">
                                <svg class="h-6 w-6 text-organization-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Vos jeunes accompagnés</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $youthsCount }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow rounded-xl border border-gray-100 p-5">
                        <div class="flex items-center">
                            <div class="p-3 bg-indigo-100 rounded-lg">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total séances (vos jeunes)</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $sessions->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sessions History -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900">Historique des séances (vos jeunes)</h3>
                        <span class="text-xs text-gray-500 italic">Vues filtrées à votre organisation</span>
                    </div>
                    <div class="px-6 py-4">
                        @if($sessions->count() > 0)
                        <div class="flow-root">
                            <ul role="list" class="-my-5 divide-y divide-gray-200">
                                @foreach($sessions as $session)
                                <li class="py-5">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            @php $mentee = $session->mentees->first(); @endphp
                                            @if($mentee && $mentee->avatar_url)
                                            <img class="h-10 w-10 rounded-full object-cover"
                                                src="{{ $mentee->avatar_url }}" alt="">
                                            @else
                                            <div
                                                class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 font-bold">
                                                {{ $mentee ? substr($mentee->name, 0, 1) : '?' }}
                                            </div>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold text-gray-900 truncate">
                                                Séance avec {{ $mentee ? $mentee->name : 'un jeune' }}
                                            </p>
                                            <p class="text-sm text-gray-500 truncate">
                                                {{ ucfirst($session->translated_status) }} • {{
                                                $session->scheduled_at->format('d/m/Y H:i') }}
                                            </p>
                                        </div>
                                        <div>
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $session->status_color }}">
                                                {{ $session->credit_cost }} crédits
                                            </span>
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @else
                        <div class="text-center py-10">
                            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-500 italic">Aucune séance encore enregistrée avec vos
                                jeunes parrainés.</p>
                        </div>
                        @endif
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>
@endsection