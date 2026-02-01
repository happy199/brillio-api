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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
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
                                    {{ \Carbon\Carbon::parse($session->scheduled_at)->format('H:i') }} ({{ $session->duration_minutes }} min)
                                </span>
                            </div>
                            
                            <div class="flex items-center justify-between mb-1">
                                <h3 class="font-bold text-gray-900">{{ $session->title }}</h3>
                                @if($session->is_paid)
                                    <span class="bg-purple-100 text-purple-700 text-xs font-bold px-2 py-0.5 rounded border border-purple-200">
                                        {{ $session->credit_cost }} Crédits
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center gap-2 mb-4">
                                <img src="{{ $session->mentor->avatar_url }}" alt="" class="w-6 h-6 rounded-full bg-gray-200">
                                <span class="text-sm text-gray-600">Avec {{ $session->mentor->name }}</span>
                            </div>
                            
                            <div class="flex gap-2 mt-auto">
                                @if($session->status === 'confirmed' || $session->status === 'accepted')
                                    @if(!$session->is_paid || $session->status === 'confirmed')
                                        <!-- CAS 1: Gratuit OU Payé -->
                                        @if($session->meeting_link)
                                            <a href="{{ route('meeting.show', $session->meeting_id) }}" target="_blank" class="flex-1 text-center py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                                                En ligne
                                            </a>
                                        @else
                                            <button disabled class="flex-1 py-2 bg-gray-100 text-gray-400 text-sm font-medium rounded-lg cursor-not-allowed">
                                                Lien bientôt dispo
                                            </button>
                                        @endif
                                    @elseif($session->is_paid && $session->status !== 'confirmed')
                                        <!-- CAS 2: Payant & Non Payé -->
                                        <form action="{{ route('jeune.sessions.pay-join', $session) }}" method="POST" class="flex-1">
                                            @csrf
                                            <button type="submit" class="w-full py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition flex items-center justify-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                Payer & Rejoindre
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <span class="flex-1 text-center py-2 bg-yellow-100 text-yellow-800 text-sm font-medium rounded-lg">
                                        En attente
                                    </span>
                                @endif
                                <a href="{{ route('jeune.sessions.show', $session) }}" class="px-3 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition text-gray-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Past Sessions -->
        <div>
            <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Passées
            </h2>

            @if($pastSessions->isEmpty())
                <p class="text-gray-500 text-sm italic">Aucune séance terminée.</p>
            @else
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sujet</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mentor</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($pastSessions as $session)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($session->scheduled_at)->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $session->title }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $session->mentor->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($session->status === 'cancelled')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Annulée</span>
                                        @elseif($session->status === 'completed')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Terminée</span>
                                        @elseif($session->scheduled_at < now())
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Passée</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">{{ $session->status }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('jeune.sessions.show', $session) }}" class="text-indigo-600 hover:text-indigo-900">
                                            Voir détails
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- Pagination for past sessions -->
                <div class="mt-4">
                    {{ $pastSessions->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
