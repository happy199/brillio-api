@extends('layouts.jeune')

@section('title', 'Ressources Pédagogiques')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="space-y-6">
        <!-- Header & Filters -->
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm space-y-4">
            <!-- Top Row: Title, Search, Source Tabs -->
            <div class="flex flex-col lg:flex-row gap-4 justify-between items-start lg:items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Ressources</h1>
                    <p class="text-sm text-gray-500">Explorez et apprenez.</p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto flex-1 lg:justify-end">
                    <!-- Search -->
                    <form action="{{ route('jeune.resources.index') }}" method="GET"
                        class="relative group w-full sm:w-64">
                        <!-- Preserve other params -->
                        @foreach(request()->except(['search', 'page']) as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400 group-focus-within:text-indigo-500 transition" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-gray-50 placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition"
                            placeholder="Rechercher...">
                    </form>

                    <!-- Source Tabs (Pills) -->
                    <div class="flex bg-gray-100 p-1 rounded-lg self-start sm:self-auto overflow-x-auto max-w-full">
                        <a href="{{ route('jeune.resources.index', array_merge(request()->except(['source', 'filter', 'page']), ['filter' => 'suggestions'])) }}"
                            class="px-3 py-1.5 rounded-md text-sm font-medium whitespace-nowrap transition {{ $currentFilter === 'suggestions' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                            ✨ Pour toi
                        </a>
                        <a href="{{ route('jeune.resources.index', array_merge(request()->except(['source', 'filter', 'page']), ['filter' => 'all'])) }}"
                            class="px-3 py-1.5 rounded-md text-sm font-medium whitespace-nowrap transition {{ $currentFilter === 'all' && !request('source') ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                            Tout
                        </a>
                        <a href="{{ route('jeune.resources.index', array_merge(request()->except(['filter', 'page']), ['filter' => 'all', 'source' => 'mentor'])) }}"
                            class="px-3 py-1.5 rounded-md text-sm font-medium whitespace-nowrap transition {{ request('source') === 'mentor' ? 'bg-white text-purple-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                            Mentors
                        </a>
                        <a href="{{ route('jeune.resources.index', array_merge(request()->except(['filter', 'page']), ['filter' => 'all', 'source' => 'brillio'])) }}"
                            class="px-3 py-1.5 rounded-md text-sm font-medium whitespace-nowrap transition {{ request('source') === 'brillio' ? 'bg-white text-orange-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                            Brillio
                        </a>
                    </div>
                </div>
            </div>

            <div class="h-px bg-gray-200"></div>

            <!-- Bottom Row: Detailed Filters -->
            <form action="{{ route('jeune.resources.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                <!-- Preserve params -->
                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                @if(request('filter')) <input type="hidden" name="filter" value="{{ request('filter') }}"> @endif
                @if(request('source')) <input type="hidden" name="source" value="{{ request('source') }}"> @endif

                <!-- Type Dropdown -->
                <div class="relative">
                    <select name="type" onchange="this.form.submit()"
                        class="appearance-none pl-3 pr-8 py-2 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-700 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 hover:bg-gray-50 cursor-pointer">
                        <option value="">Tous les types</option>
                        <option value="article" {{ request('type')==='article' ? 'selected' : '' }}>📄 Article</option>
                        <option value="video" {{ request('type')==='video' ? 'selected' : '' }}>🎥 Vidéo</option>
                        <option value="tool" {{ request('type')==='tool' ? 'selected' : '' }}>🔧 Outil</option>
                        <option value="exercise" {{ request('type')==='exercise' ? 'selected' : '' }}>📝 Exercice
                        </option>
                        <option value="template" {{ request('type')==='template' ? 'selected' : '' }}>📋 Modèle</option>
                        <option value="script" {{ request('type')==='script' ? 'selected' : '' }}>📜 Script</option>
                        <option value="ad" {{ request('type')==='ad' ? 'selected' : '' }}>📢 Partenariat</option>
                    </select>
                </div>

                <!-- Ownership Toggle (Mine vs New) -->
                <div class="bg-gray-100 rounded-lg p-1 flex text-xs font-medium">
                    <button type="submit" name="ownership" value="new"
                        class="px-3 py-1.5 rounded-md transition {{ request('ownership', 'new') === 'new' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}"
                        title="Ressources jamais consultées">
                        Nouveautés
                    </button>
                    <button type="submit" name="ownership" value="mine"
                        class="px-3 py-1.5 rounded-md transition {{ request('ownership') === 'mine' ? 'bg-white text-emerald-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}"
                        title="Ressources consultées ou achetées">
                        Mes Ressources
                    </button>
                    <!-- <button type="submit" name="ownership" value="all" ...> -> Optionnel, mais 'Tout' est couvert par le fait de choisir 'toutes les catégories'. Ici on focalise sur le statut. -->
                </div>

                <!-- Price Toggle -->
                <div class="bg-gray-100 rounded-lg p-1 flex text-xs font-medium">
                    <button type="submit" name="price" value=""
                        class="px-3 py-1.5 rounded-md transition {{ !request('price') ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                        Tous prix
                    </button>
                    <button type="submit" name="price" value="free"
                        class="px-3 py-1.5 rounded-md transition {{ request('price') === 'free' ? 'bg-white text-green-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                        Gratuit
                    </button>
                    <button type="submit" name="price" value="premium"
                        class="px-3 py-1.5 rounded-md transition {{ request('price') === 'premium' ? 'bg-white text-purple-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                        Payant
                    </button>
                </div>

                <!-- MBTI Filter -->
                <div class="relative min-w-[200px]">
                    <select name="mbti" onchange="this.form.submit()"
                        class="w-full appearance-none pl-3 pr-8 py-2 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-700 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 hover:bg-gray-50 cursor-pointer">
                        <option value="">Toutes personnalités</option>
                        @if(isset($mbtiGroups))
                        @foreach($mbtiGroups as $group => $types)
                        <optgroup label="{{ $group }}">
                            @foreach($types as $code => $label)
                            <option value="{{ $code }}" {{ request('mbti')===$code ? 'selected' : '' }}>{{ $label }}
                            </option>
                            @endforeach
                        </optgroup>
                        @endforeach
                        @else
                        <!-- Fallback if not passed (should not happen if controller fix works) -->
                        <optgroup label="Analystes">
                            <option value="INTJ">INTJ - Architecte</option>
                            <option value="INTP">INTP - Logicien</option>
                            <option value="ENTJ">ENTJ - Commandant</option>
                            <option value="ENTP">ENTP - Innovateur</option>
                        </optgroup>
                        @endif
                    </select>
                </div>

                <!-- Reset Filters Link (if any filter active) -->
                @if(request()->anyFilled(['search', 'type', 'price', 'mbti', 'source']))
                <a href="{{ route('jeune.resources.index') }}"
                    class="text-sm text-red-500 hover:text-red-700 underline ml-auto">
                    Réinitialiser
                </a>
                @endif
            </form>
        </div>

        @if($resources->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-50 mb-4">
                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucune ressource trouvée</h3>
            <p class="text-gray-500">Nous n'avons pas trouvé de ressources correspondant à tes filtres pour le moment.
                Essaie autre chose !</p>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($resources as $resource)
            <article
                class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition group flex flex-col h-full">
                <!-- Image -->
                <a href="{{ route('jeune.resources.show', $resource) }}"
                    class="block aspect-video bg-gray-100 relative overflow-hidden group cursor-pointer">
                    @if($resource->preview_image_path)
                    <img src="{{ Storage::url($resource->preview_image_path) }}" alt="{{ $resource->title }}"
                        class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                    @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    @endif

                    <!-- Badges -->
                    <div class="absolute top-3 left-3 flex gap-2 z-10 flex-wrap">
                        @if($resource->is_premium)
                        <span class="bg-purple-600 text-white text-xs font-bold px-2 py-1 rounded-full shadow-sm">
                            Premium
                        </span>
                        @else
                        <span class="bg-green-600 text-white text-xs font-bold px-2 py-1 rounded-full shadow-sm">
                            Gratuit
                        </span>
                        @endif
                        <span
                            class="bg-gray-900/80 text-white text-xs font-medium px-2 py-1 rounded-full shadow-sm backdrop-blur-sm">
                            {{ ucfirst($resource->type) }}
                        </span>
                    </div>
                </a>

                <!-- Contenu -->
                <div class="p-5 flex-1 flex flex-col">
                    <div class="mb-4">
                        <h3
                            class="text-xl font-bold text-gray-900 line-clamp-2 mb-2 group-hover:text-indigo-600 transition">
                            <a href="{{ route('jeune.resources.show', $resource) }}">
                                {{ $resource->title }}
                            </a>
                        </h3>
                        <p class="text-gray-600 text-sm line-clamp-3">
                            {{ $resource->description }}
                        </p>
                    </div>

                    <!-- Footer -->
                    <div
                        class="mt-auto pt-4 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
                        <div class="flex items-center gap-2">
                            @php
                            $creatorProfileUrl = $resource->user->isMentor() && $resource->user->mentorProfile ?
                            route('jeune.mentors.show', $resource->user->mentorProfile) : '#';
                            @endphp
                            <a href="{{ $creatorProfileUrl }}"
                                class="w-8 h-8 rounded-full bg-gray-200 overflow-hidden ring-2 ring-white shadow-sm flex-shrink-0 {{ $creatorProfileUrl !== '#' ? 'hover:scale-110 transition-transform' : 'cursor-default' }}">
                                @if($resource->user->profile_photo_path)
                                <img src="{{ Storage::url($resource->user->profile_photo_path) }}"
                                    class="w-full h-full object-cover">
                                @else
                                <div
                                    class="w-full h-full flex items-center justify-center bg-indigo-100 text-indigo-600 text-xs font-bold">
                                    {{ substr($resource->user->name, 0, 1) }}
                                </div>
                                @endif
                            </a>
                            <div class="flex flex-col min-w-0">
                                <a href="{{ $creatorProfileUrl }}"
                                    class="truncate font-medium text-gray-900 {{ $creatorProfileUrl !== '#' ? 'hover:text-indigo-600' : 'cursor-default' }}">
                                    {{ $resource->user->name }}
                                </a>

                                <!-- Badge Créateur -->
                                @if($resource->user->is_admin)
                                <span class="text-[10px] font-bold text-indigo-600 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Team Brillio
                                </span>
                                @elseif($resource->user->isMentor())
                                <span class="text-[10px] font-bold text-purple-600 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 14l9-5-9-5-9 5 9 5z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z">
                                        </path>
                                    </svg>
                                    Mentor
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <div class="flex items-center gap-2 text-xs">
                                <span class="flex items-center gap-1 text-gray-400"
                                    title="{{ $resource->views_count }} vues">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    {{ $resource->views_count }}
                                </span>
                                @if($resource->is_premium && $resource->sales_count >= 10)
                                <span class="flex items-center gap-1 text-purple-600 font-medium"
                                    title="{{ $resource->sales_count }} achats">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                    {{ $resource->sales_count }}
                                </span>
                                @endif
                            </div>
                            <span class="text-xs">{{ $resource->created_at->format('d M') }}</span>
                        </div>
                    </div>
                </div>
            </article>
            @endforeach
        </div>
        @endif
    </div>
    @endsection