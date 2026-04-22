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
                    <div class="px-6 py-5 border-b border-gray-200 bg-gray-50 flex justify-between items-center flex-wrap gap-4">
                        <h3 class="text-lg font-medium text-gray-900">{{ $session->title }}</h3>
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold 
                                @if($session->status === 'confirmed') bg-organization-100 text-organization-800 
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
                            @if($session->status === 'confirmed' && $session->meeting_link)
                            <a href="{{ route('organization.sessions.join', ['session' => $session, 'token' => $session->guest_token]) }}"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-bold rounded-lg text-white bg-organization-600 hover:bg-organization-700 shadow-md transition-all transform hover:scale-105 active:scale-95">
                                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Rejoindre la séance
                            </a>
                            @endif

                            @if($session->scheduled_by_organization_id === (auth()->user()->organization_id ?? auth()->user()->scheduled_by_organization_id) && $session->status !== 'cancelled' && $session->status !== 'completed')
                            <div class="flex items-center space-x-2 ml-4" x-data="{ showCancelModal: false }">
                                <a href="{{ route('organization.sessions.edit', $session) }}" 
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-bold rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-all shadow-sm">
                                    Modifier
                                </a>
                                
                                <button @click="showCancelModal = true"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-bold rounded-lg text-red-600 bg-red-50 hover:bg-red-100 transition-all">
                                    Annuler la séance
                                </button>

                                <!-- Modal d'annulation -->
                                <template x-teleport="body">
                                    <div x-show="showCancelModal" 
                                        class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50"
                                        x-cloak>
                                        <div @click.away="showCancelModal = false" 
                                            class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-8 transform transition-all">
                                            <h3 class="text-2xl font-bold text-gray-900 mb-4">Annuler la séance</h3>
                                            <p class="text-gray-600 mb-6">
                                                Veuillez indiquer la raison de l'annulation. Celle-ci sera envoyée par email à tous les participants.
                                            </p>
                                            
                                            <form action="{{ route('organization.sessions.cancel', $session) }}" method="POST">
                                                @csrf
                                                <div class="mb-6">
                                                    <label for="cancel_reason" class="block text-sm font-bold text-gray-700 mb-2">Raison de l'annulation <span class="text-red-500">*</span></label>
                                                    <textarea name="cancel_reason" id="cancel_reason" rows="4" required
                                                        placeholder="Ex: Imprévu, Séance reportée..."
                                                        class="w-full bg-gray-50 border-gray-200 rounded-xl focus:ring-red-500 focus:border-red-500 p-4"></textarea>
                                                </div>
                                                
                                                <div class="flex justify-end space-x-4">
                                                    <button type="button" @click="showCancelModal = false"
                                                        class="px-6 py-3 text-sm font-bold text-gray-500 hover:text-gray-700">
                                                        Retour
                                                    </button>
                                                    <button type="submit" 
                                                        class="px-8 py-3 bg-red-600 text-white font-bold rounded-xl hover:bg-red-700 shadow-lg active:scale-95 transition-all">
                                                        Confirmer l'annulation
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            @endif
                        </div>
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

                <!-- Report Form -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">Compte-rendu de la séance</h3>
                    </div>
                    <div class="p-6">
                        @php
                            $prefilled = session('prefilled_report');
                            $progress = $prefilled['progress'] ?? ($session->report_content['progress'] ?? '');
                            $obstacles = $prefilled['obstacles'] ?? ($session->report_content['obstacles'] ?? '');
                            $smart_goals = $prefilled['smart_goals'] ?? ($session->report_content['smart_goals'] ?? '');
                            
                            $isOwner = $session->scheduled_by_organization_id === $organization->id;
                            $isPast = $session->scheduled_at->isPast() || $session->status === 'completed';
                            $canEdit = $isOwner && $isPast;
                        @endphp

                        @if($canEdit)
                        <form action="{{ route('organization.sessions.report.update', $session) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">1. Progrès réalisés</label>
                                    <textarea name="progress" rows="3"
                                        class="w-full bg-gray-50 border-gray-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 p-4 text-sm font-medium"
                                        placeholder="Quelles avancées ont été constatées ?">{{ $progress }}</textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">2. Obstacles & Points Clés</label>
                                    <textarea name="obstacles" rows="3"
                                        class="w-full bg-gray-50 border-gray-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 p-4 text-sm font-medium"
                                        placeholder="Sujets abordés et blocages identifiés...">{{ $obstacles }}</textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">3. Objectifs SMART</label>
                                    <textarea name="smart_goals" rows="3"
                                        class="w-full bg-gray-50 border-gray-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 p-4 text-sm font-medium"
                                        placeholder="Actions concrètes pour la suite...">{{ $smart_goals }}</textarea>
                                </div>
                            </div>

                            <div class="flex justify-end mt-6">
                                <button type="submit"
                                    class="px-6 py-3 bg-gray-900 text-white font-bold rounded-xl hover:bg-black shadow-lg transition-all active:scale-95">
                                    Enregistrer le compte-rendu
                                </button>
                            </div>
                        </form>
                        @elseif($session->report_content)
                        <div class="space-y-8">
                            @if(isset($session->report_content['progress']))
                            <div class="p-6 bg-gray-50 rounded-2xl border border-gray-100">
                                <h4 class="text-xs font-bold text-indigo-600 uppercase tracking-widest mb-3">Progrès réalisés</h4>
                                <p class="text-gray-700 leading-relaxed">{{ $session->report_content['progress'] }}</p>
                            </div>
                            @endif
                            @if(isset($session->report_content['obstacles']))
                            <div class="p-6 bg-gray-50 rounded-2xl border border-gray-100">
                                <h4 class="text-xs font-bold text-indigo-600 uppercase tracking-widest mb-3">Obstacles & Points Clés</h4>
                                <p class="text-gray-700 leading-relaxed">{{ $session->report_content['obstacles'] }}</p>
                            </div>
                            @endif
                            @if(isset($session->report_content['smart_goals']))
                            <div class="p-6 bg-gray-50 rounded-2xl border border-gray-100">
                                <h4 class="text-xs font-bold text-indigo-600 uppercase tracking-widest mb-3">Objectifs SMART</h4>
                                <p class="text-gray-700 leading-relaxed">{{ $session->report_content['smart_goals'] }}</p>
                            </div>
                            @endif
                        </div>
                        @else
                        <div class="text-center py-12">
                            <div class="mx-auto h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="text-gray-500 font-medium">Le compte-rendu sera disponible prochainement.</p>
                            <p class="text-xs text-gray-400 mt-2">@if($isOwner) Vous pourrez le rédiger une fois la séance terminée. @else Il sera rédigé par le mentor après la séance. @endif</p>
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
                            <span class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Mentor (Hôte)</span>
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

                        @if($session->additionalMentors->count() > 0)
                        <div>
                            <span class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Intervenants Invités</span>
                            <div class="mt-3 space-y-3">
                                @foreach($session->additionalMentors as $guest)
                                <div class="flex items-center">
                                    @if($guest->avatar_url)
                                    <img class="h-8 w-8 rounded-full" src="{{ $guest->avatar_url }}" alt="">
                                    @else
                                    <div class="h-8 w-8 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 font-bold text-xs">
                                        {{ substr($guest->name, 0, 1) }}
                                    </div>
                                    @endif
                                    <span class="ml-2 text-sm font-medium text-gray-900">{{ $guest->name }}</span>
                                    <span class="ml-2 px-1.5 py-0.5 rounded-md bg-purple-50 text-purple-700 text-[10px] font-bold uppercase">Guest</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
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
                                        <span class="text-organization-600 font-medium">Parrainé</span>
                                        @endif
                                    </span>
                                </div>
                        @endforeach
                            </div>
                        </div>

                        <!-- Transcription & IA Options (Moved here for better UI) -->
                        @if($session->has_transcription)
                        <div class="pt-6 border-t border-gray-100 space-y-3">
                            <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Options IA & Transcription</h4>
                            
                            <a href="{{ route('organization.sessions.download-transcription', $session) }}"
                                class="w-full inline-flex justify-center items-center gap-2 px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-100 transition shadow-sm">
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                Transcription PDF
                                <span class="ml-1 px-1.5 py-0.5 bg-indigo-500 text-white rounded text-[10px]">
                                    {{ app(\App\Services\WalletService::class)->getFeatureCost('transcription_download', 5) }} créd.
                                </span>
                            </a>

                            <form action="{{ route('organization.sessions.prefill-report', $session) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="w-full inline-flex justify-center items-center gap-2 px-4 py-3 bg-indigo-50 border border-indigo-100 rounded-xl text-sm font-bold text-indigo-700 hover:bg-indigo-100 transition shadow-sm">
                                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                    Générer avec l'IA
                                    <span class="ml-1 px-1.5 py-0.5 bg-indigo-500 text-white rounded text-[10px]">
                                        {{ app(\App\Services\WalletService::class)->getFeatureCost('ai_report_generation', 5) }} créd.
                                    </span>
                                </button>
                            </form>
                        </div>
                        @elseif(($session->status === 'completed' || $session->scheduled_at->isPast()) && !$session->has_transcription)
                        <div class="pt-6 border-t border-gray-100">
                             <div class="p-3 bg-red-50 rounded-lg border border-dashed border-red-200">
                                <p class="text-[11px] text-red-600 text-center leading-tight">
                                    Transcription non disponible pour cette séance.
                                </p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection