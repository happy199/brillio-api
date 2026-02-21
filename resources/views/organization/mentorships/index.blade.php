@extends('layouts.organization')

@section('title', 'Suivi du Mentorat')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Suivi du Mentorat</h1>
            <p class="mt-2 text-sm text-gray-700">
                Aperçu des relations de mentorat pour vos jeunes parrainés.
            </p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route('organization.mentorships.index') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="w-full sm:w-64">
                <label for="status" class="block text-sm font-medium text-gray-700">Statut</label>
                <select name="status" id="status" onchange="this.form.submit()"
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-organization-500 focus:border-organization-500 sm:text-sm rounded-md">
                    <option value="">Tous les statuts</option>
                    <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>En attente</option>
                    <option value="accepted" {{ request('status')=='accepted' ? 'selected' : '' }}>Accepté / En cours
                    </option>
                    <option value="refused" {{ request('status')=='refused' ? 'selected' : '' }}>Refusé</option>
                    <option value="disconnected" {{ request('status')=='disconnected' ? 'selected' : '' }}>Terminé /
                        Déconnecté</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Mentorship Table -->
    <div class="relative min-h-[400px]">
        @if(!$organization->isPro())
        <div
            class="absolute inset-0 z-10 bg-white/60 backdrop-blur-[2px] rounded-lg flex flex-col items-center justify-center text-center p-8">
            <div class="bg-white p-8 rounded-xl shadow-2xl border border-gray-200 max-w-md">
                <div
                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 mb-6">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Fonctionnalité Pro</h3>
                <p class="text-gray-500 mb-8">
                    Le suivi détaillé du mentorat est réservé aux membres Pro.
                </p>
                <a href="{{ route('organization.subscriptions.index') }}"
                    class="inline-flex w-full justify-center items-center rounded-md bg-indigo-600 px-5 py-3 text-base font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-colors">
                    Passer au plan Pro
                </a>
            </div>
        </div>
        @endif

        <div
            class="bg-white shadow overflow-hidden sm:rounded-lg {{ !$organization->isPro() ? 'filter blur-[4px] select-none pointer-events-none' : '' }}">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Élève
                            (Menté)</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Mentor
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Statut
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date
                            de
                            début</th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @if(!$organization->isPro())
                    {{-- Mock data --}}
                    @for($i = 0; $i < 5; $i++) <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full"></div>
                                <div class="ml-4 space-y-2">
                                    <div class="h-4 bg-gray-200 rounded w-24"></div>
                                    <div class="h-3 bg-gray-200 rounded w-32"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full"></div>
                                <div class="ml-4">
                                    <div class="h-4 bg-gray-200 rounded w-24"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="h-6 bg-gray-200 rounded-full w-20"></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="h-4 bg-gray-200 rounded w-20"></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="h-4 bg-gray-200 rounded w-16 ml-auto"></div>
                        </td>
                        </tr>
                        @endfor
                        @else
                        @forelse($mentorships as $mentorship)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        @if($mentorship->mentee->avatar_url)
                                        <img class="h-10 w-10 rounded-full object-cover"
                                            src="{{ $mentorship->mentee->avatar_url }}" alt="">
                                        @else
                                        <div
                                            class="h-10 w-10 rounded-full bg-organization-100 flex items-center justify-center text-organization-600 font-bold">
                                            {{ substr($mentorship->mentee->name, 0, 1) }}
                                        </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $mentorship->mentee->name }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $mentorship->mentee->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        @if($mentorship->mentor->avatar_url)
                                        <img class="h-10 w-10 rounded-full object-cover"
                                            src="{{ $mentorship->mentor->avatar_url }}" alt="">
                                        @else
                                        <div
                                            class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">
                                            {{ substr($mentorship->mentor->name, 0, 1) }}
                                        </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $mentorship->mentor->name }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($mentorship->status === 'accepted') bg-organization-100 text-organization-800 
                                    @elseif($mentorship->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($mentorship->status === 'refused') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    @switch($mentorship->status)
                                    @case('accepted') Accepté @break
                                    @case('pending') En attente @break
                                    @case('refused') Refusé @break
                                    @case('disconnected') Déconnecté @break
                                    @default {{ $mentorship->status }}
                                    @endswitch
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $mentorship->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('organization.mentorships.show', $mentorship) }}"
                                    class="text-organization-600 hover:text-organization-900">Voir détails</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                Aucune relation de mentorat trouvée.
                            </td>
                        </tr>
                        @endforelse
                        @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $mentorships->links() }}
    </div>
</div>
@endsection