@extends('layouts.jeune')

@section('title', 'Détails de la séance')

@section('content')
    <div class="max-w-4xl mx-auto space-y-8">
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('jeune.sessions.index') }}"
                class="p-2 hover:bg-gray-100 rounded-full transition text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $session->title }}</h1>
                <p class="text-gray-500">Avec {{ $session->mentor->name }} •
                    {{ $session->scheduled_at->format('d/m/Y à H:i') }}
                </p>
            </div>
        </div>

        <!-- Session Info -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="grid md:grid-cols-3 gap-6">
                <div class="md:col-span-2">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">À propos de cette séance</h2>
                    <p class="text-gray-600 whitespace-pre-wrap">
                        {{ $session->description ?? 'Aucune description fournie.' }}
                    </p>

                    <div class="mt-6 flex flex-wrap gap-4">
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Durée : {{ $session->duration_minutes }} min
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            En ligne (Jitsi)
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-xl p-5 border border-gray-100">
                    <div class="flex items-center gap-3 mb-4">
                        <img src="{{ $session->mentor->avatar_url }}" alt="{{ $session->mentor->name }}"
                            class="w-12 h-12 rounded-full object-cover bg-gray-200">
                        <div>
                            <p class="text-sm text-gray-500">Mentor</p>
                            <p class="font-bold text-gray-900">{{ $session->mentor->name }}</p>
                        </div>
                    </div>

                    @if($session->is_paid)
                        <div class="mb-4 bg-purple-50 border border-purple-200 rounded-lg p-3 text-center">
                            <p class="text-sm text-purple-800 font-medium">Séance payante</p>
                            <p class="text-2xl font-bold text-purple-900">{{ $session->credit_cost }} <span class="text-sm font-normal">Crédits</span></p>
                            @if($session->status === 'confirmed' || $session->status === 'accepted')
                                <span class="inline-block mt-1 px-2 py-0.5 bg-green-100 text-green-800 text-xs font-bold rounded-full">Payée</span>
                            @else
                                <span class="inline-block mt-1 px-2 py-0.5 bg-yellow-100 text-yellow-800 text-xs font-bold rounded-full">À régler</span>
                            @endif
                        </div>
                    @endif

                    @if($session->status === 'cancelled')
                        <div class="bg-red-100 text-red-800 px-4 py-2 rounded-lg text-center font-bold text-sm">
                            Séance annulée
                        </div>
                    @elseif($session->scheduled_at >= now())
                        @if($session->status === 'confirmed' || $session->status === 'accepted')
                            @if(!$session->is_paid || $session->status === 'confirmed')
                                <!-- CAS 1: Gratuit OU Payé (et confirmé) -->
                                @if($session->meeting_link)
                                    <a href="{{ route('meeting.show', $session->meeting_id) }}" target="_blank"
                                        class="block w-full text-center py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition mb-3">
                                        En ligne
                                    </a>
                                @else
                                    <button disabled
                                        class="w-full py-2 bg-gray-200 text-gray-500 font-medium rounded-lg cursor-not-allowed mb-3">
                                        Lien bientôt disponible
                                    </button>
                                @endif
                            @elseif($session->is_paid && $session->status !== 'confirmed')
                                 <!-- Fallback improbable si status confirmed mais is_paid false? Non, couvert par le if au-dessus -->
                            @endif
                        @else
                             <!-- CAS 2: Non confirmé (donc pending_payment pour les payants) -->
                             @if($session->is_paid)
                                <form action="{{ route('jeune.sessions.pay-join', $session) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full py-2 bg-purple-600 text-white font-medium rounded-lg hover:bg-purple-700 transition mb-3 flex items-center justify-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Payer & Rejoindre
                                    </button>
                                </form>
                             @else
                                <div class="bg-yellow-100 text-yellow-800 px-4 py-2 rounded-lg text-center font-bold text-sm mb-3">
                                    En attente de validation
                                </div>
                             @endif
                        @endif

                        <!-- Cancel Button & Modal Trigger -->
                        <div x-data="{ open: false }">
                            <button @click="open = true"
                                class="w-full py-2 text-red-600 hover:bg-red-50 font-medium rounded-lg transition text-sm">
                                Annuler la séance
                            </button>

                            <!-- Cancel Modal -->
                            <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                                <div
                                    class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                    <div x-show="open" @click="open = false" class="fixed inset-0 transition-opacity"
                                        aria-hidden="true">
                                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                                    </div>

                                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                                        aria-hidden="true">&#8203;</span>

                                    <div x-show="open"
                                        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                        <form action="{{ route('jeune.sessions.cancel', $session) }}" method="POST" class="p-6">
                                            @csrf
                                            <h3 class="text-lg font-medium text-gray-900 mb-4">Annuler la séance ?</h3>
                                            <p class="text-sm text-gray-500 mb-4">
                                                Veuillez indiquer la raison de l'annulation. Cette action est irréversible.
                                            </p>

                                            <div class="mb-4">
                                                <label for="cancel_reason"
                                                    class="block text-sm font-medium text-gray-700 mb-1">Motif
                                                    d'annulation</label>
                                                <textarea name="cancel_reason" id="cancel_reason" rows="3" required
                                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                                    placeholder="J'ai un empêchement..."></textarea>
                                            </div>

                                            <div class="mt-5 sm:mt-6 flex gap-3 justify-end">
                                                <button type="button" @click="open = false"
                                                    class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:text-sm">
                                                    Retour
                                                </button>
                                                <button type="submit"
                                                    class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:text-sm">
                                                    Confirmer l'annulation
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-gray-100 text-gray-800 px-4 py-2 rounded-lg text-center font-bold text-sm">
                            Séance terminée
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Report -->
        @if($session->report_content)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Compte rendu de séance
                </h2>

                <div class="space-y-6">
                    @if(!empty($session->report_content['progress']))
                        <div>
                            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-2">Progrès réalisés</h3>
                            <div class="bg-green-50 rounded-xl p-4 text-gray-800 whitespace-pre-line border-l-4 border-green-400">
                                {{ $session->report_content['progress'] }}
                            </div>
                        </div>
                    @endif

                    @if(!empty($session->report_content['obstacles']))
                        <div>
                            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-2">Obstacles & Défis</h3>
                            <div class="bg-red-50 rounded-xl p-4 text-gray-800 whitespace-pre-line border-l-4 border-red-400">
                                {{ $session->report_content['obstacles'] }}
                            </div>
                        </div>
                    @endif

                    @if(!empty($session->report_content['smart_goals']))
                        <div>
                            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-2">Objectifs pour la prochaine
                                fois</h3>
                            <div class="bg-blue-50 rounded-xl p-4 text-gray-800 whitespace-pre-line border-l-4 border-blue-400">
                                {{ $session->report_content['smart_goals'] }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @elseif($session->scheduled_at < now() && $session->status !== 'cancelled')
            <div class="bg-gray-50 rounded-2xl p-8 text-center border border-dashed border-gray-300">
                <p class="text-gray-500 italic">Le compte rendu n'a pas encore été rédigé par le mentor.</p>
            </div>
        @endif
    </div>
@endsection