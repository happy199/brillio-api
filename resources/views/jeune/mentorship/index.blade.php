@extends('layouts.jeune')

@section('title', 'Mes Mentors')

@section('content')
    <div x-data="{ 
        activeTab: 'active',
        showCancelModal: false, 
        cancelUrl: '',
        openCancelModal(url) {
            this.cancelUrl = url;
            this.showCancelModal = true;
        }
    }">
        <div class="space-y-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Gestion de mes mentorats</h1>
                    <p class="text-gray-600 mt-1">Gérez vos demandes et suivez vos mentors.</p>
                </div>
                <a href="{{ route('jeune.mentors') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition shadow-sm">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Trouver un nouveau mentor
                </a>
            </div>

            <!-- Tabs -->
            <div class="flex border-b border-gray-200 mb-6 overflow-x-auto">
                <button 
                    @click="activeTab = 'active'" 
                    :class="{ 'border-indigo-600 text-indigo-600': activeTab === 'active', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'active' }"
                    class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors flex items-center gap-2"
                >
                    Mentors actifs
                    @if($activeMentorships->count() > 0)
                        <span class="bg-green-100 text-green-600 py-0.5 px-2.5 rounded-full text-xs font-bold">{{ $activeMentorships->count() }}</span>
                    @endif
                </button>

                <button 
                    @click="activeTab = 'pending'" 
                    :class="{ 'border-indigo-600 text-indigo-600': activeTab === 'pending', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'pending' }"
                    class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors flex items-center gap-2"
                >
                    Demandes en attente
                    @if($pendingRequests->count() > 0)
                        <span class="bg-indigo-100 text-indigo-600 py-0.5 px-2.5 rounded-full text-xs font-bold">{{ $pendingRequests->count() }}</span>
                    @endif
                </button>

                <button 
                    @click="activeTab = 'history'" 
                    :class="{ 'border-indigo-600 text-indigo-600': activeTab === 'history', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'history' }"
                    class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors"
                >
                    Historique
                </button>
            </div>

            <!-- TAB 1: MENTORS ACTIFS -->
            <div x-show="activeTab === 'active'" x-transition.opacity>
                @if($activeMentorships->isEmpty())
                    <div class="bg-white rounded-2xl p-8 text-center shadow-sm border border-gray-100">
                        <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun mentor actif</h3>
                        <p class="text-gray-500 mb-6 max-w-md mx-auto">Vous n'êtes actuellement accompagné par aucun mentor.</p>
                        <a href="{{ route('jeune.mentors') }}" class="text-indigo-600 font-medium hover:text-indigo-800">Trouver un mentor &rarr;</a>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($activeMentorships as $mentorship)
                            @php 
                                $mentorUser = $mentorship->mentor;
                                $mentorProfile = $mentorUser->mentorProfile;
                            @endphp
                            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition h-full flex flex-col">
                                <div class="flex items-center gap-4 mb-6">
                                    <img src="{{ $mentorUser->avatar_url }}" alt="{{ $mentorUser->name }}" class="w-16 h-16 rounded-xl object-cover bg-gray-100 border-2 border-indigo-50">
                                    <div>
                                        <h3 class="font-bold text-gray-900 text-lg">{{ $mentorUser->name }}</h3>
                                        <p class="text-gray-500 text-sm">{{ $mentorProfile->current_position ?? 'Mentor' }}</p>
                                        <p class="text-xs text-green-600 font-medium mt-1 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                            Actif depuis {{ $mentorship->updated_at->format('M Y') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-auto pt-6 border-t border-gray-100 flex gap-3">
                                    <a href="{{ route('jeune.mentors.show', $mentorProfile) }}" class="flex-1 text-center px-4 py-2 bg-gray-50 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-100 transition">
                                        Voir profil
                                    </a>
                                    <a href="{{ route('jeune.sessions.create', $mentorUser->id) }}" class="flex-1 text-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition shadow-sm">
                                        Réserver
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- TAB 2: DEMANDES EN ATTENTE -->
            <div x-show="activeTab === 'pending'" x-cloak>
                @if($pendingRequests->isEmpty())
                    <div class="bg-white rounded-2xl p-8 text-center shadow-sm border border-gray-100">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune demande en cours</h3>
                        <p class="text-gray-500 mb-6 max-w-md mx-auto">Toutes vos demandes ont été traitées ou vous n'en avez pas encore envoyé.</p>
                        <a href="{{ route('jeune.mentors') }}" class="text-indigo-600 font-medium hover:text-indigo-800">Chercher un mentor &rarr;</a>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($pendingRequests as $mentorship)
                            @php 
                                $mentorUser = $mentorship->mentor;
                                $mentorProfile = $mentorUser->mentorProfile;
                            @endphp
                            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition h-full flex flex-col">
                                <div class="flex items-center gap-4 mb-4">
                                    <img src="{{ $mentorUser->avatar_url }}" alt="{{ $mentorUser->name }}" class="w-14 h-14 rounded-xl object-cover bg-gray-100">
                                    <div>
                                        <h3 class="font-bold text-gray-900">{{ $mentorUser->name }}</h3>
                                        <p class="text-sm text-gray-500">{{ $mentorProfile->current_position ?? 'Mentor' }}</p>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 mt-1">
                                            En attente
                                        </span>
                                    </div>
                                </div>

                                @if($mentorship->request_message)
                                    <div class="bg-gray-50 rounded-lg p-3 mb-6 text-sm text-gray-600 italic">
                                        "{{ Str::limit($mentorship->request_message, 100) }}"
                                    </div>
                                @else
                                    <div class="bg-gray-50 rounded-lg p-3 mb-6 text-sm text-gray-400 italic">
                                        Aucun message
                                    </div>
                                @endif

                                <div class="mt-auto pt-4 border-t border-gray-100 flex gap-3">
                                    <a href="{{ route('jeune.mentors.show', $mentorProfile) }}" class="flex-1 text-center px-4 py-2 bg-gray-50 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-100 transition">
                                        Voir profil
                                    </a>
                                    <button @click="openCancelModal('{{ route('jeune.mentorship.cancel', $mentorship) }}')" 
                                        class="flex-1 px-4 py-2 bg-white border border-red-200 text-red-600 text-sm font-medium rounded-lg hover:bg-red-50 transition">
                                        Annuler
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- TAB 3: HISTORIQUE -->
            <div x-show="activeTab === 'history'" x-cloak>
                 <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    @if($history->isEmpty())
                        <div class="p-8 text-center text-gray-500">
                            Aucun historique disponible.
                        </div>
                    @else
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mentor</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Note / Raison</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($history as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-8 w-8">
                                                    <img class="h-8 w-8 rounded-full object-cover" src="{{ $item->mentor->avatar_url }}" alt="">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $item->mentor->name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $item->updated_at->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($item->status == 'refused')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Refusé</span>
                                            @elseif($item->status == 'cancelled')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Annulé</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Terminé</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="{{ $item->refusal_reason ?? $item->diction_reason }}">
                                            {{ Str::limit($item->refusal_reason ?? $item->diction_reason, 50) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>

        <!-- Cancel Modal -->
        <div x-show="showCancelModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" x-cloak x-transition>
            <div class="bg-white rounded-3xl max-w-md w-full p-6" @click.away="showCancelModal = false">
                <div class="flex items-center justify-center w-14 h-14 bg-red-100 rounded-full mx-auto mb-4">
                    <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Annuler la demande ?</h3>
                <p class="text-gray-600 text-center mb-6">
                    Êtes-vous sûr de vouloir annuler cette demande de mentorat ? Cette action est irréversible.
                </p>

                <form :action="cancelUrl" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="cancellation_reason" class="block text-sm font-medium text-gray-700 mb-1">Raison (obligatoire)</label>
                        <textarea name="cancellation_reason" id="cancellation_reason" rows="3" required
                            class="w-full rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500 text-sm"
                            placeholder="Pourquoi annulez-vous cette demande ?"></textarea>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" @click="showCancelModal = false"
                            class="flex-1 py-3 border border-gray-300 rounded-xl font-medium text-gray-700 hover:bg-gray-50 transition">
                            Retour
                        </button>
                        <button type="submit" class="flex-1 py-3 bg-red-600 text-white font-semibold rounded-xl hover:bg-red-700 transition">
                            Confirmer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
