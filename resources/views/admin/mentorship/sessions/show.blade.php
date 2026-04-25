@extends('layouts.admin')

@section('title', 'Détails Séance de Mentorat')
@section('header', 'Détails Séance de Mentorat')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Navigation -->
    <a href="{{ route('admin.mentorship.sessions') }}"
        class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
            </path>
        </svg>
        Retour aux séances
    </a>

    <!-- Header Stats -->
    <div
        class="bg-white rounded-lg shadow-md p-6 border-l-4 {{ $session->status === 'cancelled' ? 'border-red-500' : ($session->status === 'completed' ? 'border-blue-500' : 'border-green-500') }} flex justify-between items-start">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $session->title }}</h1>
            <p class="text-gray-500 flex items-center mt-1">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                    </path>
                </svg>
                {{ $session->scheduled_at->format('l d F Y à H:i') }} ({{ $session->duration_minutes }} min)
            </p>
        </div>
        <div class="text-right">
            <span class="px-3 py-1 text-sm font-bold rounded-full 
                    @switch($session->status)
                        @case('cancelled') bg-red-100 text-red-800 @break
                        @case('completed') bg-blue-100 text-blue-800 @break
                        @case('confirmed') bg-green-100 text-green-800 @break
                        @case('proposed') bg-yellow-100 text-yellow-800 @break
                        @case('pending_payment') bg-orange-100 text-orange-800 @break
                        @default bg-gray-100 text-gray-800
                    @endswitch">
                @switch($session->status)
                @case('cancelled') Annulée @break
                @case('completed') Terminée @break
                @case('confirmed') Confirmée @break
                @case('proposed') Proposée @break
                @case('pending_payment') En attente de paiement @break
                @default {{ ucfirst($session->status) }}
                @endswitch
            </span>
            <div class="mt-2 text-sm text-gray-500">
                Type:
                @if($session->is_paid)
                <span class="font-bold text-purple-600">Payant ({{ number_format($session->price, 0) }} FCFA)</span>
                @else
                <span class="font-bold text-green-600">Gratuit</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Participants -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Mentor -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <h3 class="font-bold text-gray-500 text-xs uppercase tracking-wide mb-4">Mentor</h3>
            <div class="flex items-center space-x-4">
                <img src="{{ $session->mentor->avatar_url }}" alt="" class="w-12 h-12 rounded-full object-cover">
                <div>
                    <p class="font-bold text-gray-900">{{ $session->mentor->name }}</p>
                    <a href="{{ route('admin.mentors.show', $session->mentor) }}"
                        class="text-xs text-indigo-600 hover:underline">Voir Profil</a>
                </div>
            </div>
        </div>

        <!-- Mentees -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <h3 class="font-bold text-gray-500 text-xs uppercase tracking-wide mb-4">Participants (Mentés)</h3>
            <div class="space-y-3">
                @foreach($session->mentees as $mentee)
                <div class="flex items-center space-x-4">
                    <img src="{{ $mentee->avatar_url }}" alt="" class="w-12 h-12 rounded-full object-cover">
                    <div>
                        <p class="font-bold text-gray-900">{{ $mentee->name }}</p>
                        <a href="{{ route('admin.users.show', $mentee) }}"
                            class="text-xs text-indigo-600 hover:underline">Voir Profil</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Description & Report -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden" x-data="{ showTranscription: false }">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="font-bold text-gray-900">Information & Compte Rendu</h3>
            @if($session->has_transcription)
                <button @click="showTranscription = true" 
                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Voir la transcription
                </button>
            @endif
        </div>
        <div class="p-6 space-y-6">
            <div>
                <h4 class="text-sm font-medium text-gray-500">Description de la séance</h4>
                <p class="mt-1 text-gray-900">{{ $session->description ?? 'Aucune description.' }}</p>
            </div>

            <div class="border-t border-gray-100 pt-4">
                <h4 class="text-sm font-medium text-gray-500">Compte Rendu (Mentor)</h4>
                @if($session->report_content)
                <div class="mt-2 space-y-4">
                    @if(!empty($session->report_content['progress']))
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Progression</p>
                        <p class="text-gray-800 whitespace-pre-line">{{ $session->report_content['progress'] }}</p>
                    </div>
                    @endif
                    @if(!empty($session->report_content['obstacles']))
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Obstacles</p>
                        <p class="text-gray-800 whitespace-pre-line">{{ $session->report_content['obstacles'] }}</p>
                    </div>
                    @endif
                    @if(!empty($session->report_content['smart_goals']))
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Objectifs SMART</p>
                        <p class="text-gray-800 whitespace-pre-line">{{ $session->report_content['smart_goals'] }}</p>
                    </div>
                    @endif
                </div>
                @else
                <p class="mt-1 text-gray-400 italic">Aucun compte rendu soumis pour le moment.</p>
                @endif
            </div>
        </div>

        <!-- Progress Modal (Transcription) -->
        <template x-if="showTranscription">
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showTranscription = false"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                    <div class="flex justify-between items-center mb-6">
                                        <h3 class="text-xl leading-6 font-bold text-gray-900" id="modal-title">
                                            Transcription de la séance
                                        </h3>
                                        <button @click="showTranscription = false" class="text-gray-400 hover:text-gray-500">
                                            <span class="sr-only">Fermer</span>
                                            <svg class="h-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    
                                    <div class="max-h-[70vh] overflow-y-auto px-2">
                                        @if($session->transcription_summary)
                                            <div class="mb-8 p-4 bg-indigo-50 border-l-4 border-indigo-400 rounded-r-lg">
                                                <h4 class="text-indigo-800 font-bold text-sm uppercase mb-2">Résumé de la séance (IA)</h4>
                                                <p class="text-indigo-900 text-sm italic">{{ $session->transcription_summary }}</p>
                                            </div>
                                        @endif

                                        @if($session->transcription_raw && is_array($session->transcription_raw))
                                            <div class="space-y-6">
                                                @foreach($session->transcription_raw as $entry)
                                                    <div class="flex flex-col sm:flex-row sm:space-x-4 border-l-2 border-gray-100 pl-4 py-1">
                                                        <span class="sm:w-32 flex-shrink-0 font-bold text-indigo-700 text-sm pt-0.5">
                                                            {{ $entry['speaker'] ?? 'Intervenant' }}
                                                        </span>
                                                        <div class="flex-grow">
                                                            <p class="text-gray-800 leading-relaxed">{{ $entry['text'] ?? '' }}</p>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-center py-10 text-gray-500 italic">
                                                Aucune donnée brute disponible pour cette transcription.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" @click="showTranscription = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Fermer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
@endsection