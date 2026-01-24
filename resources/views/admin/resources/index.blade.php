@extends('layouts.admin')

@section('title', 'Gestion des Ressources')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Ressources Pédagogiques</h1>
                <p class="text-gray-600">Gérez les articles, vidéos et outils pour les jeunes</p>
            </div>
            <a href="{{ route('admin.resources.create') }}"
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nouvelle Ressource
            </a>
        </div>

        <!-- Filtres -->
        <div
            class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-wrap gap-4 items-center justify-between">
            <div class="flex gap-2">
                <a href="{{ route('admin.resources.index') }}"
                    class="px-4 py-2 rounded-lg text-sm font-medium {{ !request('status') ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Tout
                </a>
                <a href="{{ route('admin.resources.index', ['status' => 'pending']) }}"
                    class="px-4 py-2 rounded-lg text-sm font-medium {{ request('status') === 'pending' ? 'bg-orange-100 text-orange-700' : 'bg-white border text-gray-600 hover:bg-gray-50' }}">
                    À valider
                </a>
                <a href="{{ route('admin.resources.index', ['status' => 'published']) }}"
                    class="px-4 py-2 rounded-lg text-sm font-medium {{ request('status') === 'published' ? 'bg-green-100 text-green-700' : 'bg-white border text-gray-600 hover:bg-gray-50' }}">
                    En ligne
                </a>
            </div>

            <form action="{{ route('admin.resources.index') }}" method="GET" class="flex gap-2 w-full md:w-auto">
                <input type="hidden" name="status" value="{{ request('status') }}">
                <select name="type"
                    class="rounded-lg border-gray-300 text-sm focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Tous les types</option>
                    <option value="article" {{ request('type') === 'article' ? 'selected' : '' }}>Articles</option>
                    <option value="video" {{ request('type') === 'video' ? 'selected' : '' }}>Vidéos</option>
                    <option value="tool" {{ request('type') === 'tool' ? 'selected' : '' }}>Outils</option>
                    <option value="exercise" {{ request('type') === 'exercise' ? 'selected' : '' }}>Exercices</option>
                    <option value="advertisement" {{ request('type') === 'advertisement' ? 'selected' : '' }}>Publicité</option>
                </select>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..."
                    class="rounded-lg border-gray-300 text-sm focus:ring-primary-500 focus:border-primary-500 w-full md:w-64">
                <button type="submit" class="p-2 bg-gray-100 rounded-lg hover:bg-gray-200 text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ressource
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Créateur
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($resources as $resource)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    @if($resource->preview_image_path)
                                        <img class="h-10 w-10 rounded object-cover mr-3"
                                            src="{{ Storage::url($resource->preview_image_path) }}" alt="">
                                    @else
                                        <div
                                            class="h-10 w-10 rounded bg-gray-100 flex items-center justify-center mr-3 text-gray-400">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="max-w-xs">
                                        <div class="text-sm font-medium text-gray-900 truncate" title="{{ $resource->title }}">
                                            {{ $resource->title }}</div>
                                        <div class="text-sm text-gray-500 truncate">{{ Str::limit($resource->description, 50) }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium uppercase
                                    @if($resource->type === 'article') bg-blue-100 text-blue-800
                                    @elseif($resource->type === 'video') bg-red-100 text-red-800
                                    @elseif($resource->type === 'advertisement') bg-purple-100 text-purple-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $resource->type === 'advertisement' ? 'Publicité' : $resource->type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($resource->price > 0)
                                    <span class="font-bold text-gray-900">{{ number_format($resource->price, 0, ',', ' ') }}
                                        F</span>
                                @else
                                    <span class="text-green-600 font-medium">Gratuit</span>
                                @endif
                                @if($resource->is_premium)
                                    <span class="ml-1 text-xs text-yellow-600 bg-yellow-100 px-1 rounded">Premium</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($resource->is_published && $resource->is_validated)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        En ligne
                                    </span>
                                @elseif(!$resource->is_validated)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        À valider
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Brouillon
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $resource->user->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.resources.edit', $resource) }}"
                                    class="text-primary-600 hover:text-primary-900 mr-3">Éditer</a>
                                <a href="{{ route('admin.resources.show', $resource) }}"
                                    class="text-gray-600 hover:text-gray-900">Détail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                Aucune ressource trouvée.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $resources->withQueryString()->links() }}
        </div>
    </div>
@endsection