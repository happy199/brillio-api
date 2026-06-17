@extends('layouts.public')

@php $forceScrolled = true; @endphp

@section('title', 'Annonces & Publicités')


@section('content')
<div class="relative overflow-hidden bg-gradient-to-b from-primary-50 via-white to-white py-24 sm:py-32" x-data="{ 
    activeImage: null, 
    activeTitle: '', 
    activeUrl: '',
    close() {
        this.activeImage = null;
        this.activeTitle = '';
        this.activeUrl = '';
    }
}" @keydown.escape.window="close()">
    
    <!-- Background elements -->
    <div class="absolute inset-y-0 right-0 -z-10 w-full overflow-hidden ring-1 ring-gray-100 lg:row-span-4 lg:row-start-1 lg:bg-gray-100/10">
        <div class="absolute -right-40 -top-40 h-[600px] w-[600px] rounded-full bg-gradient-to-br from-primary-200 to-secondary-200 opacity-20 blur-3xl"></div>
        <div class="absolute -left-40 top-80 h-[600px] w-[600px] rounded-full bg-gradient-to-br from-indigo-200 to-primary-200 opacity-20 blur-3xl"></div>
    </div>

    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <!-- Header Section -->
        <div class="mx-auto max-w-3xl text-center mb-16">
            <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl bg-gradient-to-r from-primary-600 to-secondary-600 bg-clip-text text-transparent">
                Vitrine & Opportunités
            </h1>
            <p class="mt-4 text-lg leading-8 text-gray-600">
                Découvrez les offres exclusives, bourses, formations et événements de notre communauté de partenaires.
            </p>
        </div>

        @if($advertisements->isEmpty())
            <div class="text-center py-20 bg-white/50 backdrop-blur-sm rounded-3xl border border-gray-100 shadow-sm max-w-md mx-auto">
                <span class="text-5xl block mb-4">📢</span>
                <h3 class="text-lg font-semibold text-gray-900">Aucune annonce pour le moment</h3>
                <p class="mt-2 text-sm text-gray-500">Revenez bientôt pour découvrir de nouvelles opportunités.</p>
            </div>
        @else
            <!-- Masonry Grid -->
            <div class="columns-1 sm:columns-2 md:columns-3 lg:columns-4 gap-6 space-y-6 mx-auto">
                @foreach($advertisements as $ad)
                    <div class="break-inside-avoid group relative overflow-hidden rounded-2xl bg-white border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer"
                         @click="activeImage = '{{ asset('storage/' . $ad->image_path) }}'; activeTitle = '{{ $ad->title ?? 'Annonce Brillio' }}'; activeUrl = '{{ $ad->link_url }}'">
                        
                        <!-- Image Wrapper -->
                        <div class="relative overflow-hidden bg-gray-50">
                            <img src="{{ asset('storage/' . $ad->image_path) }}" 
                                 alt="{{ $ad->title ?? 'Publicité' }}" 
                                 class="w-full h-auto block group-hover:scale-103 transition-transform duration-500 ease-out">
                            
                            <!-- Overlay on hover -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-5">
                                @if($ad->title)
                                    <h3 class="text-white font-bold text-lg leading-tight mb-2">{{ $ad->title }}</h3>
                                @endif
                                @if($ad->link_url)
                                    <span class="inline-flex items-center text-sm font-semibold text-primary-300 hover:text-white transition-colors">
                                        En savoir plus 
                                        <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                        </svg>
                                    </span>
                                @else
                                    <span class="text-xs text-white/70">Agrandir le visuel</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Lightbox Overlay Modal -->
    <div x-show="activeImage" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-black/95 p-4 backdrop-blur-md" 
         style="display: none;">
        
        <!-- Close Button (Top Right) -->
        <button @click="close()" class="absolute top-6 right-6 text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-3 rounded-full transition-all duration-200">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <!-- Image Container -->
        <div class="relative max-w-4xl w-full flex flex-col items-center" @click.away="close()">
            <img :src="activeImage" :alt="activeTitle" class="max-h-[75vh] w-auto max-w-full rounded-lg shadow-2xl border border-white/10 object-contain">
            
            <!-- Metadata & Action -->
            <div class="w-full mt-6 text-center">
                <h3 class="text-xl font-bold text-white mb-3" x-text="activeTitle"></h3>
                
                <template x-if="activeUrl">
                    <a :href="activeUrl" target="_blank" 
                       class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-primary-600 to-secondary-600 text-white font-bold rounded-full shadow-lg hover:shadow-primary-500/20 hover:scale-105 transition-all duration-300">
                        <span>En savoir plus</span>
                        <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection
