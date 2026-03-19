@extends('layouts.jeune')

@section('title', 'Mes Séances')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Mes Séances de Mentorat</h1>
            <p class="text-gray-600 mt-1">Retrouvez toutes vos séances planifiées et passées.</p>
        </div>


        <a href="{{ route('jeune.mentorship.index') }}"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Nouvelle réservation
        </a>
    </div>

    <!-- Upcoming Sessions -->
    <div>
        <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            À venir
        </h2>

        @if($upcomingSessions->isEmpty())
        <div class="bg-indigo-50 rounded-2xl p-8 text-center border border-indigo-100">
            <p class="text-indigo-900 font-medium">Aucune séance à venir.</p>
            <p class="text-indigo-600 text-sm mt-1">C'est le moment idéal pour planifier votre prochaine étape !</p>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($upcomingSessions as $session)
            <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition">
                <div class="flex justify-between items-start mb-3">
                    <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-2 py-1 rounded-lg">
                        {{ \Carbon\Carbon::parse($session->scheduled_at)->isoFormat('D MMM YYYY') }}
                    </span>
                    <span class="text-gray-500 text-sm font-medium">
                        {{ \Carbon\Carbon::parse($session->scheduled_at)->format('H:i') }} ({{
                        $session->duration_minutes }} min)
                    </span>
                </div>

                <div class="flex items-center justify-between mb-1">
                    <h3 class="font-bold text-gray-900">{{ $session->title }}</h3>
                    @if($session->is_paid)
                    <span
                        class="bg-purple-100 text-purple-700 text-xs font-bold px-2 py-0.5 rounded border border-purple-200">
                        {{ $session->credit_cost }} Crédits
                    </span>
                    @endif
                </div>

                <div class="flex items-center gap-2 mb-4">
                    <img src="{{ $session->mentor->avatar_url }}" alt="" class="w-6 h-6 rounded-full bg-gray-200">
                    <span class="text-sm text-gray-600">Avec {{ $session->mentor->name }}</span>
                </div>

                <div class="flex gap-2 mt-auto">
                    @php
                    // Check Access Individuellement (Comme dans show.blade.php)
                    $currentUserPivot = $session->mentees->find(auth()->id())?->pivot;
                    $hasPaid = $currentUserPivot && $currentUserPivot->status === 'accepted';
                    $canAccess = !$session->is_paid || $hasPaid;
                    @endphp

                    @if($session->status === 'confirmed' || $session->status === 'accepted')
                    @if($canAccess)
                    <!-- CAS 1: ACCÈS AUTORISÉ (Gratuit ou Payé) -->
                    @if($session->meeting_link)
                    <a href="{{ route('meeting.show', $session->meeting_id) }}" target="_blank"
                        class="flex-1 text-center py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                        En ligne
                    </a>
                    @else
                    <button disabled
                        class="flex-1 py-2 bg-gray-100 text-gray-400 text-sm font-medium rounded-lg cursor-not-allowed">
                        Lien bientôt dispo
                    </button>
                    @endif
                    @else

                    <!-- CAS 2: Payant & Non Payé (Même si confirmé globalement OU si pending_payment et pivot accepted par erreur) -->
                    <!-- Note: Si on est ici, c'est que $canAccess est faux. Donc il faut payer. -->
                    <form action="{{ route('jeune.sessions.pay-join', $session) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit"
                            class="w-full py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                </path>
                            </svg>
                            Payer & Rejoindre
                        </button>
                    </form>
                    @endif
                    @else
                    <!-- Status NOT confirmed/accepted (e.g. pending_payment) -->
                    @if($session->is_paid)
                    <form action="{{ route('jeune.sessions.pay-join', $session) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit"
                            class="w-full py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                </path>
                            </svg>
                            Payer & Rejoindre
                        </button>
                    </form>
                    @else
                    <span class="flex-1 text-center py-2 bg-yellow-100 text-yellow-800 text-sm font-medium rounded-lg">
                        En attente
                    </span>
                    @endif
                    @endif
                    <a href="{{ route('jeune.sessions.show', $session) }}"
                        class="px-3 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                            </path>
                        </svg>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- Past Sessions -->
    <div x-data="{
        selectedSessions: [],
        toggleAll(event) {
            if (event.target.checked) {
                this.selectedSessions = Array.from(document.querySelectorAll('.session-checkbox'))
                    .filter(cb => !cb.disabled)
                    .map(cb => cb.value);
            } else {
                this.selectedSessions = [];
            }
        }
    }" x-cloak>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
            <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Passées
            </h2>

            <!-- Bouton Rapport Compilé -->
            <form x-show="selectedSessions.length >= 2" action="{{ route('jeune.sessions.download-compiled-reports') }}" method="POST" style="display: none;">
                @csrf
                <input type="hidden" name="session_ids" x-bind:value="selectedSessions.join(',')">
                <button type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 shadow-sm flex items-center gap-2 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Générer rapport compilé (5 crédits)
                </button>
            </form>
        </div>

        @if($pastSessions->isEmpty())
        <p class="text-gray-500 text-sm italic">Aucune séance terminée.</p>
        @else
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden text-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 w-10">
                                <input type="checkbox" @change="toggleAll" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sujet
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Mentor</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Statut</th>
                            <th scope="col" class="relative px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($pastSessions as $session)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" value="{{ $session->id }}" x-model="selectedSessions" class="session-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" @if($session->status !== 'completed' || empty($session->report_content)) disabled @endif>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                                {{ \Carbon\Carbon::parse($session->scheduled_at)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 truncate max-w-[200px]" title="{{ $session->title }}">
                                {{ \Illuminate\Support\Str::limit($session->title, 40) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                <div class="flex items-center gap-2">
                                    <img src="{{ $session->mentor->avatar_url }}" alt="" class="w-6 h-6 rounded-full bg-gray-200 object-cover">
                                    {{ $session->mentor->name }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($session->pivot->status === 'cancelled' || $session->status === 'cancelled')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Annulée</span>
                                @elseif($session->pivot->status === 'rejected')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Refusée</span>
                                @elseif($session->status === 'completed')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Terminée</span>
                                @elseif($session->scheduled_at < now()) 
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Passée</span>
                                @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">{{ $session->status }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-medium">
                                <div class="flex items-center justify-end gap-3">
                                    @if($session->status === 'completed' && !empty($session->report_content))
                                    <a href="{{ route('jeune.sessions.download-report', $session) }}" class="text-green-600 hover:text-green-900" title="Télécharger le compte rendu (PDF)">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                    </a>
                                    @endif
                                    <a href="{{ route('jeune.sessions.show', $session) }}"
                                        class="text-indigo-600 hover:text-indigo-900 flex items-center gap-1" title="Voir détails">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination or Unlock -->
        <div class="mt-6 flex justify-center">
            @if($hasUnlockedHistory)
                {{ $pastSessions->links() }}
            @else
                @if(count($pastSessions) >= 10)
                <div class="w-full sm:w-auto">
                    <form action="{{ route('jeune.sessions.unlock-history') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-medium rounded-xl hover:from-purple-700 hover:to-indigo-700 shadow-sm flex items-center justify-center gap-2 transition transform hover:scale-105">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Débloquer l'historique complet (5 crédits)
                        </button>
                    </form>
                </div>
                @endif
            @endif
                @endif
            @endif
        </div>
        @endif
    </div>
</div>
@endsection