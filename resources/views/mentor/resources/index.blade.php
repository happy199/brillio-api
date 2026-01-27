@extends('layouts.mentor')

@section('title', 'Mes Ressources')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Mes Ressources Pédagogiques</h1>
                <p class="text-gray-600">Partagez vos connaissances avec la communauté</p>
            </div>
            <a href="{{ route('mentor.resources.create') }}"
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition flex items-center gap-2 shadow-sm hover:shadow-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nouvelle Ressource
            </a>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ressource</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($resources as $resource)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    @if($resource->preview_image_path)
                                        <img class="h-12 w-16 rounded object-cover mr-4 border border-gray-100"
                                            src="{{ Storage::url($resource->preview_image_path) }}" alt="">
                                    @else
                                        <div class="h-12 w-16 rounded bg-gray-100 flex items-center justify-center mr-4 text-gray-400 border border-gray-200">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="max-w-xs">
                                        <div class="text-sm font-bold text-gray-900 truncate" title="{{ $resource->title }}">
                                            {{ $resource->title }}</div>
                                        <div class="text-xs text-gray-500 truncate">{{ Str::limit($resource->description, 60) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium uppercase tracking-wide border
                                    @if($resource->type === 'article') bg-blue-50 text-blue-700 border-blue-100
                                    @elseif($resource->type === 'video') bg-red-50 text-red-700 border-red-100
                                    @elseif($resource->type === 'tool') bg-amber-50 text-amber-700 border-amber-100
                                    @else bg-gray-50 text-gray-700 border-gray-100 @endif">
                                    {{ ucfirst($resource->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if(!$resource->is_premium)
                                    <span class="text-green-600 font-semibold bg-green-50 px-2 py-1 rounded">Gratuit</span>
                                @else
                                    <span class="font-bold text-gray-900">{{ number_format($resource->price, 0, ',', ' ') }} F</span>
                                    <span class="ml-1 text-[10px] text-purple-600 bg-purple-100 px-1 rounded border border-purple-100">PREMIUM</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($resource->is_published && $resource->is_validated)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <span class="w-2 h-2 bg-green-500 rounded-full mr-1.5"></span>
                                        En ligne
                                    </span>
                                @elseif(!$resource->is_validated)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        <span class="w-2 h-2 bg-orange-500 rounded-full mr-1.5 animate-pulse"></span>
                                        En attente validation
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Brouillon
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('mentor.resources.edit', $resource) }}"
                                   class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1 rounded transition">Éditer</a>
                                <button onclick="navigator.clipboard.writeText('{{ route('jeune.resources.show', $resource) }}').then(() => window.showToast('Lien copié !')).catch(() => window.showToast('Erreur copie', 'error'))" 
                                        class="ml-2 text-teal-600 hover:text-teal-900 bg-teal-50 hover:bg-teal-100 px-3 py-1 rounded transition cursor-pointer" title="Copier le lien partageable">
                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                    <p class="text-lg font-medium text-gray-900">Aucune ressource pour le moment</p>
                                    <p class="mb-4">Commencez par partager votre première ressource pédagogique.</p>
                                    <a href="{{ route('mentor.resources.create') }}" class="text-indigo-600 font-semibold hover:underline">Créer une ressource</a>
                                </div>
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