@extends('layouts.organization')

@section('title', 'Bibliothèque de Ressources')

@section('content')
<div class="space-y-6">
    <!-- Header & Filters -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Bibliothèque de Ressources</h1>
                <p class="text-sm text-gray-500 mt-1">Parcourez les ressources de la plateforme et offrez-les à vos
                    jeunes parrainés.</p>
            </div>

            <div class="flex items-center gap-3">
                <div
                    class="px-4 py-2 bg-organization-50 rounded-lg border border-organization-100 flex items-center gap-2">
                    <span class="text-organization-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <span class="text-sm font-bold text-organization-700">{{
                        number_format($organization->credits_balance) }} crédits disponibles</span>
                </div>
            </div>
        </div>

        <div class="h-px bg-gray-100"></div>

        <!-- Filters Form -->
        <form action="{{ route('organization.resources.index') }}" method="GET"
            class="flex flex-wrap items-center gap-4">
            <!-- Search -->
            <div class="relative flex-1 min-w-[280px]">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-gray-50 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-organization-500 focus:border-organization-500 sm:text-sm"
                    placeholder="Rechercher une ressource...">
            </div>

            <!-- Type Filter -->
            <select name="type" onchange="this.form.submit()"
                class="appearance-none pl-3 pr-8 py-2 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-700 focus:outline-none focus:ring-organization-500 focus:border-organization-500 hover:bg-gray-50 cursor-pointer">
                <option value="all">Tous les types</option>
                <option value="article" {{ request('type')==='article' ? 'selected' : '' }}>📄 Article</option>
                <option value="video" {{ request('type')==='video' ? 'selected' : '' }}>🎥 Vidéo</option>
                <option value="tool" {{ request('type')==='tool' ? 'selected' : '' }}>🔧 Outil</option>
                <option value="exercise" {{ request('type')==='exercise' ? 'selected' : '' }}>📝 Exercice</option>
                <option value="template" {{ request('type')==='template' ? 'selected' : '' }}>📋 Modèle</option>
                <option value="script" {{ request('type')==='script' ? 'selected' : '' }}>📜 Script</option>
            </select>

            <!-- Price Filter -->
            <div class="bg-gray-100 rounded-lg p-1 flex text-xs font-medium">
                <button type="submit" name="price" value=""
                    class="px-3 py-1.5 rounded-md transition {{ !request('price') ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    Tous les prix
                </button>
                <button type="submit" name="price" value="free"
                    class="px-3 py-1.5 rounded-md transition {{ request('price') === 'free' ? 'bg-white text-green-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    Gratuit
                </button>
                <button type="submit" name="price" value="premium"
                    class="px-3 py-1.5 rounded-md transition {{ request('price') === 'premium' ? 'bg-white text-organization-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    Payant
                </button>
            </div>

            @if(request()->anyFilled(['search', 'type', 'price']))
            <a href="{{ route('organization.resources.index') }}"
                class="text-sm text-red-500 hover:text-red-700 underline">
                Réinitialiser
            </a>
            @endif
        </form>
    </div>

    <!-- Resources Grid -->
    @if($resources->isEmpty())
    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900">Aucune ressource trouvée</h3>
        <p class="text-gray-500 mt-1">Réessayez avec des critères de recherche différents.</p>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($resources as $resource)
        <div
            class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition flex flex-col h-full group">
            <a href="{{ route('organization.resources.show', $resource) }}"
                class="block aspect-video bg-gray-100 relative overflow-hidden flex-shrink-0">
                @if($resource->preview_image_path)
                <img src="{{ Storage::url($resource->preview_image_path) }}"
                    class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                @else
                <div class="w-full h-full flex items-center justify-center text-gray-300">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                @endif

                <!-- Badges -->
                <div class="absolute top-3 left-3 flex flex-wrap gap-2">
                    @if($resource->is_premium)
                    <span
                        class="px-2 py-1 bg-organization-600 text-white text-[10px] font-bold rounded-lg uppercase shadow-sm">Premium</span>
                    @else
                    <span
                        class="px-2 py-1 bg-green-600 text-white text-[10px] font-bold rounded-lg uppercase shadow-sm">Gratuit</span>
                    @endif
                    <span
                        class="px-2 py-1 bg-gray-900/70 text-white text-[10px] font-bold rounded-lg uppercase backdrop-blur-sm">{{
                        $resource->type }}</span>
                </div>

                @if($giftedIds->contains($resource->id))
                <div class="absolute bottom-3 right-3">
                    <span
                        class="px-2 py-1 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded-lg border border-emerald-200 shadow-sm flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        DÉJÀ OFFERT
                    </span>
                </div>
                @endif
            </a>

            <div class="p-5 flex-1 flex flex-col">
                <h3 class="text-lg font-bold text-gray-900 group-hover:text-organization-600 transition truncate mb-2">
                    <a href="{{ route('organization.resources.show', $resource) }}">{{ $resource->title }}</a>
                </h3>
                <p class="text-sm text-gray-600 line-clamp-2 mb-4">{{ $resource->description }}</p>

                <div class="mt-auto pt-4 border-t border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div
                            class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-[10px] font-bold text-gray-500 overflow-hidden border border-gray-200">
                            @if($resource->user->profile_photo_path)
                            <img src="{{ Storage::url($resource->user->profile_photo_path) }}"
                                class="w-full h-full object-cover">
                            @else
                            {{ substr($resource->user->name, 0, 1) }}
                            @endif
                        </div>
                        <span class="text-xs text-gray-500 font-medium">{{ $resource->user->name }}</span>
                    </div>

                    <a href="{{ route('organization.resources.show', $resource) }}"
                        class="text-sm font-semibold text-organization-600 hover:text-organization-700 flex items-center gap-1">
                        Consulter
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $resources->links() }}
    </div>
    @endif
</div>
@endsection