@extends('layouts.jeune')

@section('title', $resource->title)

@section('content')
    <div class="max-w-4xl mx-auto space-y-8">
        <!-- Navigation -->
        <a href="{{ route('jeune.resources.index') }}"
            class="inline-flex items-center text-gray-500 hover:text-indigo-600 transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Retour aux ressources
        </a>

        <article class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
            <!-- Header Image -->
            @if($resource->preview_image_path)
                <div class="w-full h-64 md:h-96 relative">
                    <img src="{{ Storage::url($resource->preview_image_path) }}" alt="{{ $resource->title }}"
                        class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 p-8 text-white">
                        <div class="flex items-center gap-3 mb-4">
                            @if($resource->is_premium)
                                <span class="bg-purple-600 text-white text-sm font-bold px-3 py-1 rounded-full shadow-sm">
                                    Premium
                                </span>
                            @else
                                <span class="bg-green-600 text-white text-sm font-bold px-3 py-1 rounded-full shadow-sm">
                                    Gratuit
                                </span>
                            @endif
                            <span
                                class="bg-white/20 text-white text-sm font-medium px-3 py-1 rounded-full backdrop-blur-md border border-white/30">
                                {{ ucfirst($resource->type) }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            <div class="p-8 md:p-12">
                <!-- Header Content -->
                <header class="mb-8">
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ $resource->title }}</h1>
                    <div class="flex items-center gap-4 text-gray-500 text-sm">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-gray-200 overflow-hidden">
                                @if($resource->user->profile_photo_path)
                                    <img src="{{ Storage::url($resource->user->profile_photo_path) }}"
                                        class="w-full h-full object-cover">
                                @else
                                    <div
                                        class="w-full h-full flex items-center justify-center bg-indigo-100 text-indigo-600 text-xs font-bold">
                                        {{ substr($resource->user->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <span class="font-medium text-gray-900">{{ $resource->user->name }}</span>
                        </div>
                        <span>•</span>
                        <time datetime="{{ $resource->created_at->toIso8601String() }}">
                            Publié le {{ $resource->created_at->format('d/m/Y') }}
                        </time>
                    </div>
                </header>

                <!-- Description -->
                <div class="prose prose-lg prose-indigo max-w-none text-gray-600 mb-12">
                    <p class="lead">{{ $resource->description }}</p>
                </div>

                <!-- Main Content -->
                @if($resource->content)
                    <div class="prose prose-lg prose-indigo max-w-none mb-12">
                        {!! $resource->content !!}
                    </div>
                @endif

                <!-- Attachments -->
                @if($resource->file_path)
                    <div class="bg-indigo-50 rounded-xl p-6 border border-indigo-100 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-white rounded-lg shadow-sm text-indigo-600">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900">Fichier joint</h3>
                                <p class="text-sm text-gray-500">Télécharge le fichier associé à cette ressource</p>
                            </div>
                        </div>
                        <a href="{{ Storage::url($resource->file_path) }}" download
                            class="bg-indigo-600 text-white font-bold py-2.5 px-6 rounded-lg hover:bg-indigo-700 transition shadow-md hover:shadow-lg">
                            Télécharger
                        </a>
                    </div>
                @endif
            </div>
        </article>
    </div>
@endsection