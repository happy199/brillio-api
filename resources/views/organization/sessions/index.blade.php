@extends('layouts.organization')

@section('title', 'Séances de Mentorat')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Séances de Mentorat</h1>
            <p class="mt-2 text-sm text-gray-700">
                Liste de toutes les séances programmées et passées pour vos jeunes parrainés.
            </p>
        </div>
        <div class="mt-4 sm:mt-0">
            @if($organization->isPro())
            <a href="{{ route('organization.sessions.calendar') }}"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-organization-500">
                <svg class="-ml-1 mr-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Voir le calendrier
            </a>
            @else
            <a href="{{ route('organization.subscriptions.index') }}"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Débloquer le Calendrier
            </a>
            @endif
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route('organization.sessions.index') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="w-full sm:w-64">
                <label for="status" class="block text-sm font-medium text-gray-700">Statut</label>
                <select name="status" id="status" onchange="this.form.submit()"
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-organization-500 focus:border-organization-500 sm:text-sm rounded-md">
                    <option value="">Tous les statuts</option>
                    <option value="confirmed" {{ request('status')=='confirmed' ? 'selected' : '' }}>Confirmée</option>
                    <option value="pending_payment" {{ request('status')=='pending_payment' ? 'selected' : '' }}>Attente
                        paiement</option>
                    <option value="completed" {{ request('status')=='completed' ? 'selected' : '' }}>Terminée</option>
                    <option value="cancelled" {{ request('status')=='cancelled' ? 'selected' : '' }}>Annulée</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Session Grid -->
    <div class="relative min-h-[400px]">
        @if(!$organization->isPro())
        <div
            class="absolute inset-0 z-10 bg-white/60 backdrop-blur-[2px] rounded-lg flex flex-col items-center justify-center text-center p-8">
            <div class="bg-white p-8 rounded-xl shadow-2xl border border-gray-200 max-w-md">
                <div
                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 mb-6">
                    @if($organization->isPro())
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                    @else
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                    @endif
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Fonctionnalité Pro</h3>
                <p class="text-gray-500 mb-8">
                    L'accès au calendrier détaillé et à l'historique des séances est réservé aux membres Pro.
                </p>
                <a href="{{ route('organization.subscriptions.index') }}"
                    class="inline-flex w-full justify-center items-center rounded-md bg-indigo-600 px-5 py-3 text-base font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-colors">
                    Passer au plan Pro
                </a>
            </div>
        </div>
        @endif

        <div
            class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 {{ !$organization->isPro() ? 'filter blur-[4px] select-none pointer-events-none' : '' }}">
            @if(!$organization->isPro())
            {{-- Mock data --}}
            @for($i = 0; $i < 6; $i++) <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex justify-between items-start mb-4">
                        <div class="h-5 bg-gray-200 rounded-full w-24"></div>
                        <div class="h-4 bg-gray-200 rounded w-20"></div>
                    </div>
                    <div class="h-6 bg-gray-200 rounded w-3/4 mb-4"></div>
                    <div class="space-y-2">
                        <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                        <div class="h-4 bg-gray-200 rounded w-2/3"></div>
                        <div class="h-4 bg-gray-200 rounded w-1/3"></div>
                    </div>
                </div>
        </div>
        @endfor
        @else
        @forelse($sessions as $session)
        <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow">
            <div class="p-5">
                <div class="flex justify-between items-start">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
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
                        @case('proposed') Proposée @break
                        @default {{ $session->status }}
                        @endswitch
                    </span>
                    <span class="text-xs text-gray-500">{{ $session->scheduled_at->format('d/m/Y H:i') }}</span>
                </div>

                <h3 class="mt-4 text-lg font-bold text-gray-900 truncate">{{ $session->title }}</h3>

                <div class="mt-4 space-y-2">
                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="font-medium mr-1">Mentor:</span> {{ $session->mentor->name }}
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span class="font-medium mr-1">Menté(s):</span>
                        {{ $session->mentees->pluck('name')->implode(', ') }}
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ $session->duration_minutes }} min
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3 border-t border-gray-100">
                <a href="{{ route('organization.sessions.show', $session) }}"
                    class="text-sm font-medium text-organization-600 hover:text-organization-500">
                    Détails de la séance &rarr;
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12 bg-white rounded-lg shadow">
            <p class="text-gray-500 italic">Aucune séance trouvée.</p>
        </div>
        @endforelse
        @endif
    </div>
</div>

<!-- Pagination -->
<div class="mt-6">
    {{ $sessions->links() }}
</div>
</div>
@endsection