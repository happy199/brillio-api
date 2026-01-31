@extends('layouts.mentor')

@section('title', $session->title)

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="mb-6">
            <a href="{{ route('mentor.mentorship.calendar') }}"
                class="text-gray-500 hover:text-gray-700 flex items-center gap-2 mb-4 text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                Retour au calendrier
            </a>

            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <h1 class="text-2xl font-bold text-gray-900">{{ $session->title }}</h1>
                        @if($session->status == 'completed')
                            <span
                                class="bg-green-100 text-green-700 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase">Terminée</span>
                        @elseif($session->status == 'cancelled')
                            <span
                                class="bg-red-100 text-red-700 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase">Annulée</span>
                        @elseif($session->status == 'confirmed')
                            <span
                                class="bg-indigo-100 text-indigo-700 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase">Confirmée</span>
                        @else
                            <span
                                class="bg-gray-100 text-gray-700 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase">{{ $session->status }}</span>
                        @endif
                    </div>
                    <p class="text-gray-500">{{ $session->scheduled_at->format('d F Y à H:i') }} •
                        {{ $session->duration_minutes }} min
                    </p>
                </div>

                <div class="flex gap-3">
                    @if($session->status === 'proposed')
                        <form action="{{ route('mentor.mentorship.sessions.accept', $session) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg font-medium transition flex items-center gap-2 shadow-sm shadow-indigo-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                    </path>
                                </svg>
                                Accepter
                            </button>
                        </form>

                        <form action="{{ route('mentor.mentorship.sessions.refuse', $session) }}" method="POST"
                            onsubmit="return confirm('Voulez-vous vraiment refuser cette demande ?');">
                            @csrf
                            <button type="submit"
                                class="bg-white border border-gray-300 text-gray-700 hover:text-red-600 hover:border-red-200 px-4 py-2.5 rounded-lg font-medium transition flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Refuser
                            </button>
                        </form>
                    @elseif($session->status === 'confirmed' || $session->status === 'accepted')
                        <a href="{{ route('meeting.show', $session->meeting_id) }}" target="_blank"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg font-medium transition flex items-center gap-2 shadow-sm shadow-indigo-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
                                </path>
                            </svg>
                            Rejoindre la visio
                        </a>

                        <button onclick="document.getElementById('cancel-modal').showModal()"
                            class="bg-white border border-gray-300 text-gray-700 hover:text-red-600 hover:border-red-200 px-4 py-2.5 rounded-lg font-medium transition">
                            Annuler
                        </button>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Column: Details & Report -->
                <div class="lg:col-span-2 space-y-6">
                    @if($session->status == 'cancelled')
                        <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-800">
                            <h4 class="font-bold text-sm mb-1">Séance annulée</h4>
                            <p class="text-sm">{{ $session->cancel_reason }}</p>
                        </div>
                    @endif

                    <!-- Participants -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-bold text-gray-900 mb-4">Participants</h3>
                        <div class="flex -space-x-2 overflow-hidden mb-4">
                            <img class="inline-block h-10 w-10 rounded-full ring-2 ring-white"
                                src="{{ $session->mentor->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($session->mentor->name) }}"
                                alt="{{ $session->mentor->name }}" title="Mentor: {{ $session->mentor->name }}">
                            @foreach($session->mentees as $mentee)
                                <img class="inline-block h-10 w-10 rounded-full ring-2 ring-white"
                                    src="{{ $mentee->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($mentee->name) }}"
                                    alt="{{ $mentee->name }}" title="Menté: {{ $mentee->name }}">
                            @endforeach
                        </div>
                        <div class="text-sm text-gray-500">
                            Avec :
                            @foreach($session->mentees as $mentee)
                                <span class="font-medium text-gray-900">{{ $mentee->name }}</span>{{ !$loop->last ? ',' : '' }}
                            @endforeach
                        </div>
                    </div>

                    <!-- Report Form -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-bold text-gray-900 mb-2">Compte Rendu de Séance</h3>
                        <p class="text-gray-500 text-sm mb-6">À remplir pendant ou après la séance pour assurer le suivi.
                        </p>

                        <form action="{{ route('mentor.mentorship.sessions.report.update', $session) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="space-y-6">
                                <!-- 1. Progress -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">1. Avancées du menté depuis
                                        la
                                        dernière session</label>
                                    <textarea name="progress" rows="3"
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Qu'est-ce qui a été accompli ?">{{ $session->report_content['progress'] ?? '' }}</textarea>
                                </div>

                                <!-- 2. Topics / Obstacles -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">2. Sujets abordés &
                                        Obstacles</label>
                                    <textarea name="obstacles" rows="3"
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Points clés discutés et blocages identifiés...">{{ $session->report_content['obstacles'] ?? '' }}</textarea>
                                </div>

                                <!-- 3. SMART Goals -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">3. Objectifs SMART pour la
                                        prochaine fois</label>
                                    <div class="bg-blue-50 p-3 rounded-lg mb-2 text-xs text-blue-800">
                                        <strong>SMART</strong> : Spécifique, Mesurable, Atteignable, Réaliste, Temporel.
                                    </div>
                                    <textarea name="smart_goals" rows="3"
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Actions concrètes à réaliser...">{{ $session->report_content['smart_goals'] ?? '' }}</textarea>
                                </div>
                            </div>

                            <div class="flex justify-end mt-6">
                                <button type="submit"
                                    class="bg-gray-900 hover:bg-black text-white px-6 py-2.5 rounded-lg font-medium transition">
                                    Sauvegarder le compte rendu
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Right Column: Info -->
                <div class="space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-bold text-gray-800 mb-4 text-sm uppercase tracking-wide">Détails de la séance</h3>

                        <div class="space-y-4 text-sm">
                            <div class="flex justify-between py-2 border-b border-gray-50">
                                <span class="text-gray-500">Statut</span>
                                <span class="font-medium text-gray-900 capitalize">{{ $session->status }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-50">
                                <span class="text-gray-500">Type</span>
                                <span
                                    class="font-medium text-gray-900">{{ $session->is_paid ? 'Payante' : 'Gratuite' }}</span>
                            </div>
                            @if($session->is_paid)
                                <div class="flex justify-between py-2 border-b border-gray-50">
                                    <span class="text-gray-500">Prix</span>
                                    <span class="font-medium text-gray-900">{{ number_format($session->price, 0, ',', ' ') }}
                                        FCFA</span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-gray-50">
                                    <span class="text-gray-500">Commission (10%)</span>
                                    <span
                                        class="font-medium text-red-500">-{{ number_format($session->price * 0.10, 0, ',', ' ') }}
                                        FCFA</span>
                                </div>
                                <div class="flex justify-between py-2 font-bold">
                                    <span class="text-gray-900">Net estimé</span>
                                    <span class="text-green-600">{{ number_format($session->price * 0.90, 0, ',', ' ') }}
                                        FCFA</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($session->description)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h3 class="font-bold text-gray-800 mb-4 text-sm uppercase tracking-wide">Description</h3>
                            <p class="text-gray-600 text-sm leading-relaxed">{{ $session->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Cancel Modal -->
        <dialog id="cancel-modal" class="modal bg-white rounded-xl shadow-xl p-0 w-full max-w-md backdrop:bg-gray-900/50">
            <div class="p-6">
                <h3 class="font-bold text-lg mb-4 text-red-600">Annuler la séance ?</h3>
                <p class="text-gray-600 mb-4 text-sm">Voulez-vous vraiment annuler cette séance ? Les participants seront
                    notifiés.</p>

                <form action="{{ route('mentor.mentorship.sessions.cancel', $session) }}" method="POST">
                    @csrf
                    <textarea name="cancel_reason" rows="3"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-red-500 focus:ring-red-500 mb-4 text-sm"
                        placeholder="Raison de l'annulation..." required></textarea>

                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="document.getElementById('cancel-modal').close()"
                            class="text-gray-500 hover:text-gray-700 text-sm font-medium px-4 py-2">Ne pas annuler</button>
                        <button type="submit"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Confirmer
                            l'annulation</button>
                    </div>
                </form>
            </div>
        </dialog>
@endsection