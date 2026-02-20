@extends('layouts.organization')

@section('title', 'Détails de la Séance')

@section('content')
<div class="space-y-6">
    <div class="flex items-center space-x-4">
        <a href="{{ route('organization.sessions.index') }}"
            class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
            <svg class="mr-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Retour à la liste
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Détails de la Séance</h1>
    </div>

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
                    Les détails complets de la séance et son compte-rendu sont réservés aux membres Pro.
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
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">{{ $session->title }}</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold 
                            @if($session->status === 'confirmed') bg-green-100 text-green-800 
                            @elseif($session->status === 'completed') bg-indigo-100 text-indigo-800
                            @elseif($session->status === 'cancelled') bg-red-100 text-red-800
                            @elseif($session->status === 'pending_payment') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800 @endif">
                            @switch($session->status)
                            @case('confirmed') Confirmée @break
                            @case('completed') Terminée @break
                            @case('cancelled') Annulée @break
                            @case('pending_payment') En attente de paiement @break
                            @default {{ $session->status }}
                            @endswitch
                        </span>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-700 whitespace-pre-wrap">{{ $session->description ?: 'Aucune description
                            fournie.' }}</p>

                        <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="flex items-center text-sm text-gray-600">
                                <div class="h-10 w-10 rounded-lg bg-gray-100 flex items-center justify-center mr-3">
                                    <svg class="h-6 w-6 text-gray-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $session->scheduled_at->translatedFormat('d
                                        F
                                        Y') }}</p>
                                    <p>{{ $session->scheduled_at->format('H:i') }} ({{ $session->duration_minutes }}
                                        min)
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report (if completed or has content) -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">Compte-rendu de la séance</h3>
                    </div>
                    <div class="p-6">
                        @if($session->report_content)
                        <div class="space-y-6">
                            @if(isset($session->report_content['progress']))
                            <div>
                                <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Progrès réalisés
                                </h4>
                                <p class="mt-2 text-gray-700">{{ $session->report_content['progress'] }}</p>
                            </div>
                            @endif
                            @if(isset($session->report_content['obstacles']))
                            <div>
                                <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Obstacles
                                    identifiés
                                </h4>
                                <p class="mt-2 text-gray-700">{{ $session->report_content['obstacles'] }}</p>
                            </div>
                            @endif
                            @if(isset($session->report_content['smart_goals']))
                            <div>
                                <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Objectifs SMART
                                    pour la
                                    suite</h4>
                                <p class="mt-2 text-gray-700">{{ $session->report_content['smart_goals'] }}</p>
                            </div>
                            @endif
                        </div>
                        @else
                        <div class="text-center py-6">
                            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-500 font-medium">Le compte-rendu n'est pas encore
                                disponible.
                            </p>
                            <p class="text-xs text-gray-400 mt-1">Il sera rédigé par le mentor après la fin de la
                                séance.
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="space-y-6">
                <!-- Participants -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">Participants</h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <span class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Mentor</span>
                            <div class="mt-2 flex items-center">
                                @if($session->mentor && $session->mentor->avatar_url)
                                <img class="h-8 w-8 rounded-full" src="{{ $session->mentor->avatar_url }}" alt="">
                                @else
                                <div
                                    class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xs">
                                    {{ substr($session->mentor->name ?? 'M', 0, 1) }}
                                </div>
                                @endif
                                <span class="ml-2 text-sm font-medium text-gray-900">{{ $session->mentor->name ??
                                    'Mentor' }}</span>
                            </div>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Menté(s)</span>
                            <div class="mt-2 space-y-3">
                                @foreach($session->mentees as $mentee)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        @if($mentee->avatar_url)
                                        <img class="h-8 w-8 rounded-full" src="{{ $mentee->avatar_url }}" alt="">
                                        @else
                                        <div
                                            class="h-8 w-8 rounded-full bg-organization-100 flex items-center justify-center text-organization-600 font-bold text-xs">
                                            {{ substr($mentee->name, 0, 1) }}
                                        </div>
                                        @endif
                                        <span class="ml-2 text-sm font-medium text-gray-900">{{ $mentee->name }}</span>
                                    </div>
                                    <span class="text-xs text-gray-500">
                                        @if($mentee->sponsored_by_organization_id === $organization->id)
                                        <span class="text-green-600 font-medium">Parrainé</span>
                                        @endif
                                    </span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection