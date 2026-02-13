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
            <a href="{{ route('organization.sessions.calendar') }}"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-organization-500">
                <svg class="-ml-1 mr-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Voir le calendrier
            </a>
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
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($sessions as $session)
        <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow">
            <div class="p-5">
                <div class="flex justify-between items-start">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                        @if($session->status === 'confirmed') bg-green-100 text-green-800 
                        @elseif($session->status === 'completed') bg-indigo-100 text-indigo-800
                        @elseif($session->status === 'cancelled') bg-red-100 text-red-800
                        @else bg-yellow-100 text-yellow-800 @endif">
                        @switch($session->status)
                        @case('confirmed') Confirmée @break
                        @case('completed') Terminée @break
                        @case('cancelled') Annulée @break
                        @case('pending_payment') Attente paiement @break
                        @case('proposed') Proposée @break
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
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $sessions->links() }}
    </div>
</div>
@endsection