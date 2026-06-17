@extends('layouts.organization')

@section('title', 'Gestion des Publicités')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                📢 Mes Publicités & Annonces
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Gérez vos visuels publicitaires et suivez leur statut de validation par l'administration Brillio.
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <a href="{{ route('organization.advertisements.create') }}"
               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-organization-600 hover:bg-organization-700 focus:outline-none transition-colors">
                <i class="fas fa-plus mr-2"></i> Proposer une publicité
            </a>
        </div>
    </div>

    <!-- Ads Grid -->
    @if($advertisements->isEmpty())
        <div class="text-center py-16 bg-white shadow sm:rounded-lg border border-gray-100">
            <span class="text-5xl block mb-4">📢</span>
            <h3 class="text-lg font-semibold text-gray-900">Aucune publicité proposée</h3>
            <p class="mt-2 text-sm text-gray-500">Vous n'avez pas encore soumis de visuels publicitaires pour affichage public.</p>
            <div class="mt-6">
                <a href="{{ route('organization.advertisements.create') }}"
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-organization-600 hover:bg-organization-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i> Commencer maintenant
                </a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($advertisements as $ad)
                <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200 flex flex-col justify-between">
                    <!-- Image Preview -->
                    <div class="relative aspect-video w-full bg-gray-100 overflow-hidden border-b border-gray-200">
                        <img src="{{ asset('storage/' . $ad->image_path) }}" alt="{{ $ad->title ?? 'Visuel' }}" class="w-full h-full object-cover">
                        
                        <!-- Status Badge -->
                        <div class="absolute top-3 right-3">
                            @if($ad->status === \App\Models\Advertisement::STATUS_APPROVED)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                    <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-green-500"></span> Validé
                                </span>
                            @elseif($ad->status === \App\Models\Advertisement::STATUS_REJECTED)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                    <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-red-500"></span> Rejeté
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                    <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-yellow-500"></span> En attente
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 truncate mb-1">
                                {{ $ad->title ?? 'Sans titre' }}
                            </h3>
                            
                            @if($ad->link_url)
                                <a href="{{ $ad->link_url }}" target="_blank" class="inline-flex items-center text-xs font-medium text-organization-600 hover:text-organization-800 mb-4 transition-colors">
                                    <i class="fas fa-external-link-alt mr-1"></i> Lien cible
                                </a>
                            @else
                                <span class="text-xs text-gray-400 block mb-4">Aucun lien spécifié</span>
                            @endif
                        </div>

                        <!-- Date and Actions -->
                        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                            <span class="text-xs text-gray-500">
                                Soumis le {{ $ad->created_at->format('d/m/Y') }}
                            </span>
                            
                            <!-- Delete Button -->
                            <form action="{{ route('organization.advertisements.destroy', $ad) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette publicité ? Cette action est irréversible.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-800 transition-colors">
                                    <i class="fas fa-trash-alt mr-1"></i> Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
