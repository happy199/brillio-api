@extends('layouts.admin')

@section('title', 'Détail Ressource')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.resources.index') }}" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $resource->title }}</h1>
                    <p class="text-gray-600">Détail et Validation</p>
                </div>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.resources.edit', $resource) }}"
                    class="bg-white border text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition">
                    Modifier
                </a>
                @if(!$resource->is_validated || !$resource->is_published)
                    <form action="{{ route('admin.resources.approve', $resource) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit"
                            class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Valider & Publier
                        </button>
                    </form>
                @else
                    <form action="{{ route('admin.resources.reject', $resource) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="bg-red-100 text-red-700 px-4 py-2 rounded-lg hover:bg-red-200 transition">
                            Retirer de la publication
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Contenu -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Colonne Principale -->
            <div class="lg:col-span-2 space-y-6">
                @if($resource->preview_image_path)
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <img src="{{ Storage::url($resource->preview_image_path) }}" alt="" class="w-full h-64 object-cover">
                    </div>
                @endif

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
                    <div class="prose max-w-none text-gray-800">
                        <h2 class="text-xl font-bold mb-4">Description</h2>
                        <p class="mb-8 font-medium text-gray-600">{{ $resource->description }}</p>

                        <hr class="my-8 border-gray-100">

                        <h2 class="text-xl font-bold mb-4">Contenu</h2>
                        <!-- Attention: Affichage HTML brut si confiance -->
                        <div class="bg-gray-50 p-6 rounded-lg font-mono text-sm overflow-x-auto">
                            {{ $resource->content }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne Latérale -->
            <div class="space-y-6">
                <!-- Statut -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Informations</h3>

                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Statut</span>
                            @if($resource->is_published && $resource->is_validated)
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-bold">PUBLIÉ</span>
                            @else
                                <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs font-bold">À VALIDER</span>
                            @endif
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Type</span>
                            <span
                                class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs font-bold uppercase">{{ $resource->type }}</span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Prix</span>
                            <span class="font-bold {{ $resource->is_premium ? 'text-yellow-600' : 'text-green-600' }}">
                                {{ $resource->price > 0 ? $resource->price . ' FCFA' : 'Gratuit' }}
                            </span>
                        </div>

                        <div class="border-t pt-4">
                            <span class="text-gray-500 text-sm block mb-2">Créateur</span>
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center font-bold text-gray-600 text-xs">
                                    {{ substr($resource->user->name, 0, 2) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $resource->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $resource->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fichier -->
                @if($resource->file_path)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Fichier joint</h3>
                        <a href="{{ Storage::url($resource->file_path) }}" target="_blank"
                            class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                            <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                </path>
                            </svg>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">Télécharger le fichier</p>
                                <p class="text-xs text-gray-500">Document attaché</p>
                            </div>
                        </a>
                    </div>
                @endif

                <!-- Metadata -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Ciblage</h3>

                    @if(!empty($resource->mbti_types))
                        <div class="mb-4">
                            <p class="text-xs text-gray-500 mb-2">Types MBTI</p>
                            <div class="flex flex-wrap gap-1">
                                @foreach($resource->mbti_types as $type)
                                    <span class="bg-purple-100 text-purple-700 px-2 py-0.5 rounded text-xs">{{ $type }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(!empty($resource->tags))
                        <div>
                            <p class="text-xs text-gray-500 mb-2">Tags</p>
                            <div class="flex flex-wrap gap-1">
                                @foreach($resource->tags as $tag)
                                    <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs">#{{ trim($tag) }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection