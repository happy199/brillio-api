@extends('layouts.admin')

@section('title', 'Gestion des Publicités')

@section('content')
<div class="space-y-6" x-data="{ tab: 'all' }">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Publicités & Annonces</h1>
            <p class="text-gray-600">Gérez les publicités publiques et validez les propositions des organisations.</p>
        </div>
        <a href="{{ route('admin.advertisements.create') }}"
           class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition flex items-center gap-2 font-semibold shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nouvelle Publicité
        </a>
    </div>

    <!-- Stats & Filters -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-wrap gap-4 items-center justify-between">
        <div class="flex gap-2">
            <button @click="tab = 'all'"
                    :class="tab === 'all' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                Toutes ({{ $advertisements->count() }})
            </button>
            <button @click="tab = 'pending'"
                    :class="tab === 'pending' ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                En attente ({{ $advertisements->where('status', 'pending')->count() }})
            </button>
            <button @click="tab = 'approved'"
                    :class="tab === 'approved' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                Validées ({{ $advertisements->where('status', 'approved')->count() }})
            </button>
            <button @click="tab = 'rejected'"
                    :class="tab === 'rejected' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                Rejetées ({{ $advertisements->where('status', 'rejected')->count() }})
            </button>
        </div>
    </div>

    <!-- Grid List -->
    @if($advertisements->isEmpty())
        <div class="bg-white p-12 text-center rounded-xl shadow-sm border border-gray-100">
            <span class="text-5xl block mb-4">📢</span>
            <h3 class="text-lg font-semibold text-gray-900">Aucune publicité enregistrée</h3>
            <p class="text-gray-500 mt-2">Aucune annonce n'a encore été proposée ou créée.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($advertisements as $ad)
                <div x-show="tab === 'all' || tab === '{{ $ad->status }}'"
                     x-transition
                     class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col justify-between hover:shadow-md transition-shadow">
                    
                    <!-- Preview Visuel -->
                    <div class="relative aspect-video w-full bg-gray-50 overflow-hidden border-b border-gray-100">
                        <img src="{{ asset('storage/' . $ad->image_path) }}" alt="{{ $ad->title ?? 'Visuel' }}" class="w-full h-full object-cover">
                        
                        <!-- Status tag overlay -->
                        <div class="absolute top-3 right-3">
                            @if($ad->status === 'approved')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 shadow-sm border border-green-200">
                                    En ligne
                                </span>
                            @elseif($ad->status === 'rejected')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 shadow-sm border border-red-200">
                                    Rejetée
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 shadow-sm border border-yellow-200">
                                    En attente
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 truncate mb-1">
                                {{ $ad->title ?? 'Sans titre' }}
                            </h3>
                            
                            <!-- Origin Info -->
                            <div class="flex items-center space-x-2 mb-3">
                                @if($ad->organization)
                                    <span class="text-xs bg-indigo-50 text-indigo-700 font-semibold px-2 py-0.5 rounded border border-indigo-150">
                                        Org : {{ $ad->organization->name }}
                                    </span>
                                @else
                                    <span class="text-xs bg-gray-100 text-gray-700 font-semibold px-2 py-0.5 rounded border border-gray-200">
                                        Brillio (Admin)
                                    </span>
                                @endif
                            </div>

                            @if($ad->link_url)
                                <a href="{{ $ad->link_url }}" target="_blank" class="inline-flex items-center text-xs text-indigo-600 hover:text-indigo-800 font-medium transition-colors">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                    Lien de destination
                                </a>
                            @else
                                <span class="text-xs text-gray-400">Aucun lien de destination</span>
                            @endif
                        </div>

                        <!-- Info/Creation details -->
                        <div class="text-xs text-gray-500 flex flex-col space-y-1">
                            <span>Soumis par : <strong class="text-gray-700">{{ $ad->creator?->name ?? 'Brillio System' }}</strong></span>
                            <span>Date : {{ $ad->created_at->format('d/m/Y H:i') }}</span>
                        </div>

                        <!-- Action buttons -->
                        <div class="pt-4 border-t border-gray-150 flex items-center justify-between">
                            <!-- Left: Approval buttons for pending items -->
                            <div class="flex space-x-2">
                                @if($ad->status === 'pending')
                                    <!-- Approve Form -->
                                    <form action="{{ route('admin.advertisements.approve', $ad) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-xs font-bold px-3 py-1.5 rounded transition-colors shadow-sm">
                                            Valider
                                        </button>
                                    </form>

                                    <!-- Reject Form -->
                                    <form action="{{ route('admin.advertisements.reject', $ad) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold px-3 py-1.5 rounded transition-colors border border-gray-300">
                                            Rejeter
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <!-- Right: Actions (Edit & Delete) -->
                            <div class="flex items-center space-x-4">
                                <a href="{{ route('admin.advertisements.edit', $ad) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-bold flex items-center gap-1 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Modifier
                                </a>

                                <form action="{{ route('admin.advertisements.destroy', $ad) }}" method="POST" onsubmit="return confirm('Supprimer définitivement cette publicité ? Cette action supprimera également le fichier.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-bold flex items-center gap-1 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
