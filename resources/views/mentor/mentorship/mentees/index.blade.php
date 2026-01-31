@extends('layouts.mentor')

@section('title', 'Mes Mentés')

@section('content')
<div class="container mx-auto px-4 py-8" x-data="{ activeTab: 'pending' }">
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gestion de mes mentorats</h1>
            <p class="text-gray-500 mt-1">Gérez vos demandes et suivez vos mentés.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- Tabs -->
    <div class="flex border-b border-gray-200 mb-6 overflow-x-auto">
        <button 
            @click="activeTab = 'pending'" 
            :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'pending', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'pending' }"
            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors flex items-center gap-2"
        >
            Demandes en attente
            @if($pendingRequests->count() > 0)
                <span class="bg-indigo-100 text-indigo-600 py-0.5 px-2.5 rounded-full text-xs font-bold">{{ $pendingRequests->count() }}</span>
            @endif
        </button>

        <button 
            @click="activeTab = 'active'" 
            :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'active', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'active' }"
            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors flex items-center gap-2"
        >
            Mentés actifs
            @if($activeMentees->count() > 0)
                <span class="bg-green-100 text-green-600 py-0.5 px-2.5 rounded-full text-xs font-bold">{{ $activeMentees->count() }}</span>
            @endif
        </button>

        <button 
            @click="activeTab = 'history'" 
            :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'history', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'history' }"
            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors"
        >
            Historique
        </button>
    </div>

    <!-- Tab Contents -->
    
    <!-- PENDING REQUESTS -->
    <div x-show="activeTab === 'pending'" x-transition.opacity>
        @if($pendingRequests->isEmpty())
            <div class="text-center py-12 bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="bg-gray-50 dark:bg-gray-800 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4 text-gray-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Aucune demande en attente</h3>
                <p class="text-gray-500 mt-1">Vous n'avez pas de nouvelles demandes de mentorat pour le moment.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($pendingRequests as $mentorship)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center gap-4 mb-4">
                            <img src="{{ $mentorship->mentee->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($mentorship->mentee->name) }}" alt="{{ $mentorship->mentee->name }}" class="w-12 h-12 rounded-full object-cover bg-gray-100">
                            <div>
                                <h3 class="font-bold text-gray-900">{{ $mentorship->mentee->name }}</h3>
                                <p class="text-sm text-gray-500">Demande envoyée le {{ $mentorship->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-4 mb-6 text-sm text-gray-600 italic">
                            "{{ Str::limit($mentorship->request_message, 150) }}"
                        </div>

                        <div class="flex gap-3">
                            <form action="{{ route('mentor.mentorship.accept', $mentorship) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg text-sm font-medium transition">
                                    Accepter
                                </button>
                            </form>
                            
                            <button 
                                onclick="document.getElementById('refuse-modal-{{ $mentorship->id }}').showModal()"
                                class="flex-1 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 py-2 px-4 rounded-lg text-sm font-medium transition">
                                Refuser
                            </button>
                        </div>
                    </div>

                    <!-- REFUSE MODAL -->
                    <dialog id="refuse-modal-{{ $mentorship->id }}" class="modal bg-white rounded-xl shadow-xl p-0 w-full max-w-md backdrop:bg-gray-900/50">
                        <div class="p-6">
                            <h3 class="font-bold text-lg mb-4 text-gray-900">Refuser la demande ?</h3>
                            <p class="text-gray-600 mb-4 text-sm">Veuillez expliquer pourquoi vous ne pouvez pas accepter cette demande. Ce message sera envoyé à {{ $mentorship->mentee->name }}.</p>
                            
                            <form action="{{ route('mentor.mentorship.refuse', $mentorship) }}" method="POST">
                                @csrf
                                <textarea name="refusal_reason" rows="4" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 mb-4 text-sm" placeholder="Raison du refus..." required></textarea>
                                
                                <div class="flex justify-end gap-3">
                                    <button type="button" onclick="document.getElementById('refuse-modal-{{ $mentorship->id }}').close()" class="text-gray-500 hover:text-gray-700 text-sm font-medium px-4 py-2">Annuler</button>
                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Confirmer le refus</button>
                                </div>
                            </form>
                        </div>
                    </dialog>
                @endforeach
            </div>
        @endif
    </div>

    <!-- ACTIVE MENTEES -->
    <div x-show="activeTab === 'active'" x-cloak>
        @if($activeMentees->isEmpty())
             <div class="text-center py-12 bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="bg-indigo-50 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4 text-indigo-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Aucun menté actif</h3>
                <p class="text-gray-500 mt-1">Vous n'accompagnez personne actuellement.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($activeMentees as $mentorship)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col h-full">
                        <div class="flex items-center gap-4 mb-6">
                            <img src="{{ $mentorship->mentee->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($mentorship->mentee->name) }}" alt="{{ $mentorship->mentee->name }}" class="w-14 h-14 rounded-full object-cover bg-gray-100 border-2 border-indigo-100">
                            <div>
                                <h3 class="font-bold text-gray-900 text-lg">{{ $mentorship->mentee->name }}</h3>
                                <p class="text-sm text-gray-500 flex items-center gap-1">
                                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Actif depuis {{ $mentorship->updated_at->format('M Y') }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-auto pt-6 border-t border-gray-100 flex gap-3">
                             <a href="#" class="flex-1 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 py-2 px-4 rounded-lg text-sm font-medium transition text-center">
                                Voir profil
                            </a>
                            <button 
                                onclick="document.getElementById('disconnect-modal-{{ $mentorship->id }}').showModal()"
                                class="bg-white border border-gray-200 text-gray-600 hover:text-red-600 hover:border-red-200 py-2 px-3 rounded-lg transition" title="Mettre fin au mentorat">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                            </button>
                        </div>
                    </div>

                    <!-- DISCONNECT MODAL -->
                    <dialog id="disconnect-modal-{{ $mentorship->id }}" class="modal bg-white rounded-xl shadow-xl p-0 w-full max-w-md backdrop:bg-gray-900/50">
                        <div class="p-6">
                            <h3 class="font-bold text-lg mb-4 text-gray-900">Mettre fin au mentorat ?</h3>
                            <p class="text-gray-600 mb-4 text-sm">Vous êtes sur le point de mettre fin à votre accompagnement avec {{ $mentorship->mentee->name }}. Cette action est irréversible.</p>
                            
                            <form action="{{ route('mentor.mentorship.disconnect', $mentorship) }}" method="POST">
                                @csrf
                                <textarea name="diction_reason" rows="4" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-red-500 focus:ring-red-500 mb-4 text-sm" placeholder="Raison de la fin de l'accompagnement..." required></textarea>
                                
                                <div class="flex justify-end gap-3">
                                    <button type="button" onclick="document.getElementById('disconnect-modal-{{ $mentorship->id }}').close()" class="text-gray-500 hover:text-gray-700 text-sm font-medium px-4 py-2">Annuler</button>
                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Confirmer la fin</button>
                                </div>
                            </form>
                        </div>
                    </dialog>
                @endforeach
            </div>
        @endif
    </div>

    <!-- HISTORY -->
    <div x-show="activeTab === 'history'" x-cloak>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
             @if($history->isEmpty())
                <div class="p-8 text-center text-gray-500">
                    Aucun historique disponible.
                </div>
            @else
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jeune</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Raison</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($history as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->mentee->name }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $item->updated_at->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($item->status == 'refused')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Refusé</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Déconnecté</span>
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
@endsection
