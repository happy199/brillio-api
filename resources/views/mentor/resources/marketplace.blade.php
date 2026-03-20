@extends('layouts.mentor')

@section('title', 'Boutique de Ressources')

@section('content')
<div class="space-y-6">
    <!-- Navigation Tabs -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8">
            <a href="{{ route('mentor.resources.index') }}"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                Mes Ressources
            </a>
            <a href="{{ route('mentor.resources.marketplace') }}"
                class="border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                Boutique / Toutes les ressources
            </a>
        </nav>
    </div>

    <!-- Header & Search -->
    <div class="space-y-4">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Boutique de Ressources</h1>
                <p class="text-gray-600">Explorez les contenus partagés par la communauté pour vous inspirer</p>
            </div>
            
            <div class="text-right">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                    {{ $totalCount }} {{ Str::plural('ressource', $totalCount) }} {{ Str::plural('disponible', $totalCount) }}
                </span>
            </div>
        </div>

        <form action="{{ route('mentor.resources.marketplace') }}" method="GET" class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="relative md:col-span-1">
                    <input type="text" name="search" value="{{ request('search') }}" 
                        placeholder="Titre ou tag..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-sm text-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    @if(request('search') || request('type') || request('author') || request('price'))
                        <a href="{{ route('mentor.resources.marketplace') }}" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </a>
                    @endif
                </div>

                <!-- Type -->
                <select name="type" onchange="this.form.submit()" class="block w-full pl-3 pr-10 py-2 text-sm border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-lg shadow-sm">
                    <option value="">Tous les types</option>
                    <option value="article" {{ request('type') == 'article' ? 'selected' : '' }}>Articles</option>
                    <option value="video" {{ request('type') == 'video' ? 'selected' : '' }}>Vidéos</option>
                    <option value="pdf" {{ request('type') == 'pdf' ? 'selected' : '' }}>PDF / Documents</option>
                    <option value="podcast" {{ request('type') == 'podcast' ? 'selected' : '' }}>Podcasts</option>
                    <option value="tool" {{ request('type') == 'tool' ? 'selected' : '' }}>Outils</option>
                </select>

                <!-- Author -->
                <select name="author" onchange="this.form.submit()" class="block w-full pl-3 pr-10 py-2 text-sm border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-lg shadow-sm">
                    <option value="">Tous les auteurs</option>
                    <option value="brillio" {{ request('author') == 'brillio' ? 'selected' : '' }}>Brillio Team</option>
                    <option value="mentors" {{ request('author') == 'mentors' ? 'selected' : '' }}>Mes Confrères</option>
                </select>

                <!-- Price -->
                <select name="price" onchange="this.form.submit()" class="block w-full pl-3 pr-10 py-2 text-sm border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-lg shadow-sm">
                    <option value="">Tous les tarifs</option>
                    <option value="free" {{ request('price') == 'free' ? 'selected' : '' }}>Gratuit</option>
                    <option value="paid" {{ request('price') == 'paid' ? 'selected' : '' }}>Payant</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Stats Alert -->
    <div class="bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded-r-lg flex items-start gap-3">
        <svg class="w-6 h-6 text-indigo-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
            <p class="text-sm text-indigo-700">
                Vous voyez ici les ressources publiées par vos confrères et l'équipe Brillio. Utilisez cet espace pour ne pas créer de doublons et identifier les besoins non couverts.
            </p>
        </div>
    </div>

    <!-- Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($resources as $resource)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition">
            <div class="relative aspect-video bg-gray-100">
                @if($resource->preview_image_path)
                    <img src="{{ Storage::url($resource->preview_image_path) }}" alt="" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-300">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                @endif
                <div class="absolute top-2 right-2">
                    <span class="px-2 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider backdrop-blur-md bg-white/80 shadow-sm border border-white/20
                        @if($resource->type === 'article') text-blue-700
                        @elseif($resource->type === 'video') text-red-700
                        @elseif($resource->type === 'tool') text-amber-700
                        @else text-gray-700 @endif">
                        {{ ucfirst($resource->type) }}
                    </span>
                </div>
            </div>
            
            <div class="p-4 space-y-3">
                <div class="flex items-center gap-2 mb-1">
                    <img src="{{ $resource->user->avatar_url }}" alt="" class="w-5 h-5 rounded-full border border-gray-200">
                    <span class="text-xs text-gray-500 font-medium truncate">Par {{ $resource->user->name }}</span>
                </div>
                
                <h3 class="font-bold text-gray-900 leading-tight line-clamp-2 min-h-[3rem]" title="{{ $resource->title }}">
                    {{ $resource->title }}
                </h3>
                
                <p class="text-xs text-gray-500 line-clamp-2">
                    {{ Str::limit($resource->description, 100) }}
                </p>

                <div class="flex items-center justify-between pt-2 border-t border-gray-50">
                    <div class="flex items-center gap-2">
                        @if(!$resource->is_premium)
                            <span class="text-xs font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded">Gratuit</span>
                        @elseif(in_array($resource->id, $purchasedIds))
                            <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M2.166 4.9L10 1.554L17.834 4.9c.11.047.166.173.166.3v10.512c0 .127-.056.253-.166.3L10 19.446l-7.834-3.435a.332.332 0 01-.166-.3V5.2c0-.127.056-.253.166-.3zM10 3.172L4 5.738v8.524l6 2.566l6-2.566V5.738l-6-2.566z" clip-rule="evenodd" />
                                    <path fill-rule="evenodd" d="M14.707 7.293a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0l-3-3a1 1 0 111.414-1.414L8 12.586l5.293-5.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Débloqué
                            </span>
                        @else
                            <span class="text-xs font-bold text-gray-900">{{ number_format($resource->price, 0, ',', ' ') }} F</span>
                        @endif
                    </div>
                    
                    <a href="{{ route('mentor.resources.show', $resource) }}" 
                        class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                        Consulter
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full py-12 text-center text-gray-500 bg-white rounded-xl border border-dashed border-gray-300">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900">Aucune ressource trouvée</h3>
            <p>Essayez d'ajuster votre recherche pour trouver du contenu.</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $resources->withQueryString()->links() }}
    </div>
</div>
@endsection
