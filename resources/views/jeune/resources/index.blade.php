@extends('layouts.jeune')

@section('title', 'Ressources Pédagogiques')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Ressources Pédagogiques</h1>
        <p class="text-gray-600">Des contenus sélectionnés spécialement pour toi.</p>
    </div>

    @if($resources->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-50 mb-4">
                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucune ressource trouvée</h3>
            <p class="text-gray-500">Nous n'avons pas trouvé de ressources correspondant exactement à ton profil pour le moment. Reviens plus tard !</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($resources as $resource)
                <article class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition group flex flex-col h-full">
                    <!-- Image -->
                    <a href="{{ route('jeune.resources.show', $resource) }}" class="block aspect-video bg-gray-100 relative overflow-hidden group cursor-pointer">
                        @if($resource->preview_image_path)
                            <img src="{{ Storage::url($resource->preview_image_path) }}" 
                                 alt="{{ $resource->title }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif
                        
                        <!-- Badges -->
                        <div class="absolute top-3 left-3 flex gap-2 z-10">
                            @if($resource->is_premium)
                                <span class="bg-purple-600 text-white text-xs font-bold px-2 py-1 rounded-full shadow-sm">
                                    Premium
                                </span>
                            @else
                                <span class="bg-green-600 text-white text-xs font-bold px-2 py-1 rounded-full shadow-sm">
                                    Gratuit
                                </span>
                            @endif
                            <span class="bg-gray-900/80 text-white text-xs font-medium px-2 py-1 rounded-full shadow-sm backdrop-blur-sm">
                                {{ ucfirst($resource->type) }}
                            </span>
                        </div>
                    </a>

                    <!-- Contenu -->
                    <div class="p-5 flex-1 flex flex-col">
                        <div class="mb-4">
                            <h3 class="text-xl font-bold text-gray-900 line-clamp-2 mb-2 group-hover:text-indigo-600 transition">
                                <a href="{{ route('jeune.resources.show', $resource) }}">
                                    {{ $resource->title }}
                                </a>
                            </h3>
                            <p class="text-gray-600 text-sm line-clamp-3">
                                {{ $resource->description }}
                            </p>
                        </div>

                        <!-- Footer -->
                        <div class="mt-auto pt-4 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-gray-200 overflow-hidden">
                                     @if($resource->user->profile_photo_path)
                                        <img src="{{ Storage::url($resource->user->profile_photo_path) }}" class="w-full h-full object-cover">
                                     @else
                                        <div class="w-full h-full flex items-center justify-center bg-indigo-100 text-indigo-600 text-xs font-bold">
                                            {{ substr($resource->user->name, 0, 1) }}
                                        </div>
                                     @endif
                                </div>
                                <span class="truncate max-w-[100px]">{{ $resource->user->name }}</span>
                            </div>
                            <span>{{ $resource->created_at->format('d M Y') }}</span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection
