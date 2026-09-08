@extends('layouts.organization')

@section('title', 'Bibliothèque de Ressources')

@section('content')
<div class="space-y-6" x-data="{}">
    <!-- Header & Filters -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Bibliothèque de Ressources</h1>
                <p class="text-sm text-gray-500 mt-1">Créez vos ressources internes et explorez les ressources disponibles pour vos jeunes.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('organization.resources.create') }}"
                    class="px-4 py-2 bg-organization-600 hover:bg-organization-700 text-white rounded-lg font-semibold text-sm shadow-sm transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Créer une ressource
                </a>

                <div class="px-4 py-2 bg-organization-50 rounded-lg border border-organization-100 flex items-center gap-2">
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

        @if($organization->hide_external_resources)
        <div class="p-3 bg-indigo-50 border border-indigo-200 rounded-lg flex items-center justify-between text-xs text-indigo-700">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span><strong>Masquage des ressources externes actif</strong> : Seules vos ressources internes sont affichées dans votre espace et pour vos jeunes.</span>
            </div>
            <a href="{{ route('organization.profile.edit') }}" class="underline font-semibold hover:text-indigo-900">Gérer</a>
        </div>
        @endif

        <!-- Tabs Navigation -->
        <div class="flex flex-wrap items-center gap-2 border-b border-gray-200 pb-4">
            @if(!$organization->hide_external_resources)
            <a href="{{ route('organization.resources.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'all'])) }}"
                class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $tab === 'all' ? 'bg-organization-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Toutes les ressources <span class="ml-1 text-xs opacity-80">({{ $internalCount + $externalCount }})</span>
            </a>
            @endif

            <a href="{{ route('organization.resources.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'internal'])) }}"
                class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $tab === 'internal' ? 'bg-organization-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                🏛️ Ressources Internes <span class="ml-1 text-xs opacity-80">({{ $internalCount }})</span>
            </a>

            @if(!$organization->hide_external_resources)
            <a href="{{ route('organization.resources.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'external'])) }}"
                class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $tab === 'external' ? 'bg-organization-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                🌐 Ressources Externes <span class="ml-1 text-xs opacity-80">({{ $externalCount }})</span>
            </a>
            @endif
        </div>

        <!-- Filters Form -->
        <form action="{{ route('organization.resources.index') }}" method="GET"
            class="flex flex-wrap items-center gap-4">
            <input type="hidden" name="tab" value="{{ $tab }}">

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
                <option value="book" {{ request('type')==='book' ? 'selected' : '' }}>📚 Livre / PDF</option>
                <option value="podcast" {{ request('type')==='podcast' ? 'selected' : '' }}>🎧 Podcast</option>
                <option value="webinar" {{ request('type')==='webinar' ? 'selected' : '' }}>📺 Webinaire</option>
                <option value="guide" {{ request('type')==='guide' ? 'selected' : '' }}>🧭 Guide</option>
                <option value="case_study" {{ request('type')==='case_study' ? 'selected' : '' }}>📊 Étude de cas</option>
                <option value="course" {{ request('type')==='course' ? 'selected' : '' }}>🎓 Formation</option>
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
            <a href="{{ route('organization.resources.index', ['tab' => $tab]) }}"
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
        <p class="text-gray-500 mt-1">
            @if($tab === 'internal')
            Vous n'avez pas encore créé de ressource interne pour votre organisation.
            @else
            Réessayez avec des critères de recherche différents.
            @endif
        </p>
        @if($tab === 'internal')
        <div class="mt-4">
            <a href="{{ route('organization.resources.create') }}"
                class="inline-flex items-center px-4 py-2 bg-organization-600 hover:bg-organization-700 text-white text-sm font-semibold rounded-lg shadow-sm transition gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Créer votre première ressource
            </a>
        </div>
        @endif
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($resources as $resource)
        @php
            $isOwnResource = $resource->organization_id === $organization->id;
        @endphp
        <div
            class="bg-white rounded-xl border {{ $isOwnResource ? 'border-organization-200 ring-1 ring-organization-100' : 'border-gray-200' }} overflow-hidden hover:shadow-md transition flex flex-col h-full group">
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
                <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
                    @if($isOwnResource)
                    <span class="px-2 py-1 bg-organization-700 text-white text-[10px] font-bold rounded-lg uppercase shadow-sm flex items-center gap-1">
                        🏛️ Interne
                    </span>
                    @else
                    <span class="px-2 py-1 bg-gray-900/70 text-white text-[10px] font-bold rounded-lg uppercase backdrop-blur-sm">
                        Externe
                    </span>
                    @endif

                    @if($resource->is_premium)
                    <span
                        class="px-2 py-1 bg-purple-600 text-white text-[10px] font-bold rounded-lg uppercase shadow-sm">Premium</span>
                    @else
                    <span
                        class="px-2 py-1 bg-green-600 text-white text-[10px] font-bold rounded-lg uppercase shadow-sm">Gratuit</span>
                    @endif

                    <span
                        class="px-2 py-1 bg-gray-800/80 text-white text-[10px] font-bold rounded-lg uppercase backdrop-blur-sm">{{
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
                        @if($isOwnResource)
                        <span class="text-xs font-semibold text-organization-700 flex items-center gap-1">
                            🏛️ {{ $organization->name }}
                        </span>
                        @else
                        <div
                            class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-[10px] font-bold text-gray-500 overflow-hidden border border-gray-200">
                            @if($resource->user && $resource->user->profile_photo_path)
                            <img src="{{ Storage::url($resource->user->profile_photo_path) }}"
                                class="w-full h-full object-cover">
                            @else
                            {{ substr($resource->user->name ?? 'B', 0, 1) }}
                            @endif
                        </div>
                        <span class="text-xs text-gray-500 font-medium truncate max-w-[120px]">{{ $resource->user->name ?? 'Brillio' }}</span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        @if($isOwnResource)
                        <a href="{{ route('organization.resources.edit', $resource) }}"
                            class="text-xs font-semibold text-gray-600 hover:text-organization-600 px-2 py-1 bg-gray-100 hover:bg-organization-50 rounded transition">
                            Modifier
                        </a>
                        <form action="{{ route('organization.resources.destroy', $resource) }}" method="POST"
                            onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette ressource interne ?');"
                            class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700 p-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                        @endif

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