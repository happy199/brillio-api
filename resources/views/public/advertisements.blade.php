@extends('layouts.public')

@php $forceScrolled = true; @endphp

@section('title', 'Annonces & Publicités')


@section('content')
<div class="relative overflow-hidden bg-slate-50 py-24 sm:py-32"
     x-data="advertisementGallery"
     @keydown.escape.window="close()"
     @keydown.left.window="prev()"
     @keydown.right.window="next()">

    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <!-- Header Section -->
        <div class="mx-auto max-w-3xl text-center mb-16">
            <span class="inline-block px-3.5 py-1.5 bg-brand-50 border border-brand-100 rounded-full text-xs font-semibold text-brand-700 mb-4">
                Opportunités & Partenariats
            </span>
            <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 sm:text-5xl">
                Vitrine & Opportunités
            </h1>
            <p class="mt-4 text-lg leading-8 text-slate-600">
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
                         @click="openAd({{ $loop->index }})">

                        <!-- Image Wrapper -->
                        <div class="relative overflow-hidden bg-gray-50">
                             <img src="{{ asset('storage/' . $ad->image_path) }}"
                                  alt="{{ $ad->title ?? 'Publicité' }}"
                                  class="w-full h-auto block group-hover:scale-103 transition-transform duration-500 ease-out">

                            <!-- Overlay on hover -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-5">
                                @if($ad->title)
                                    <h3 class="text-white font-bold text-lg leading-tight mb-2 line-clamp-2">{{ $ad->title }}</h3>
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
    <div x-show="activeIndex !== null"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-black/95 p-4 backdrop-blur-md"
         style="display: none;">

        <!-- Close Button (Top Right) -->
        <button @click.stop="close()" class="absolute top-6 right-6 text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-3 rounded-full transition-all duration-200 z-50 focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <!-- Left Nav Arrow (Vertically Centered) -->
        <button @click.stop="prev()"
                :disabled="activeIndex === 0"
                :class="activeIndex === 0 ? 'opacity-20 cursor-not-allowed' : 'opacity-100 hover:bg-white/20 hover:scale-105'"
                class="absolute left-4 md:left-8 top-1/2 -translate-y-1/2 text-white bg-white/15 p-3 md:p-4 rounded-full transition-all duration-200 z-50 focus:outline-none"
                title="Précédent">
            <svg class="w-6 h-6 md:w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>

        <!-- Right Nav Arrow (Vertically Centered) -->
        <button @click.stop="next()"
                :disabled="activeIndex === ads.length - 1"
                :class="activeIndex === ads.length - 1 ? 'opacity-20 cursor-not-allowed' : 'opacity-100 hover:bg-white/20 hover:scale-105'"
                class="absolute right-4 md:right-8 top-1/2 -translate-y-1/2 text-white bg-white/15 p-3 md:p-4 rounded-full transition-all duration-200 z-50 focus:outline-none"
                title="Suivant">
            <svg class="w-6 h-6 md:w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>

        <!-- Image Container -->
        <div class="relative max-w-4xl w-full flex flex-col items-center" @click.away="close()">
            <img :src="activeAd ? activeAd.image : ''" :alt="activeAd ? activeAd.title : ''" class="max-h-[75vh] w-auto max-w-full rounded-lg shadow-2xl border border-white/10 object-contain">

            <!-- Metadata & Action -->
            <div class="w-full mt-6 text-center px-4">
                <h3 class="text-xl font-bold text-white mb-3" x-text="activeAd ? activeAd.title : ''">Annonce</h3>

                <template x-if="activeAd && activeAd.url">
                    <a :href="activeAd.url" target="_blank"
                       class="inline-flex items-center justify-center px-6 py-3.5 bg-brand-500 hover:bg-brand-600 active:scale-[0.98] text-white font-bold rounded-xl shadow-xs hover:shadow transition-all text-sm">
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

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    document.addEventListener('alpine:init', () => {
        Alpine.data('advertisementGallery', () => ({
            ads: {!! json_encode($advertisements->map(function($ad) {
                return [
                    'id' => $ad->id,
                    'image' => asset('storage/' . $ad->image_path),
                    'title' => $ad->title ?? 'Annonce Brillio',
                    'url' => $ad->link_url,
                ];
            })->values(), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!},
            activeIndex: null,

            get activeAd() {
                return this.activeIndex !== null ? this.ads[this.activeIndex] : null;
            },

            openAd(index) {
                this.activeIndex = index;
                const ad = this.ads[index];
                if (ad && ad.id) {
                    fetch(`/publicite/${ad.id}/click`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }).catch(err => console.error('Failed to track click:', err));
                }
            },

            close() {
                this.activeIndex = null;
            },

            prev() {
                if (this.activeIndex > 0) {
                    this.activeIndex--;
                    const ad = this.ads[this.activeIndex];
                    if (ad && ad.id) {
                        fetch(`/publicite/${ad.id}/click`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        }).catch(err => console.error('Failed to track click:', err));
                    }
                }
            },

            next() {
                if (this.ads.length - 1 > this.activeIndex) {
                    this.activeIndex++;
                    const ad = this.ads[this.activeIndex];
                    if (ad && ad.id) {
                        fetch(`/publicite/${ad.id}/click`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        }).catch(err => console.error('Failed to track click:', err));
                    }
                }
            }
        }));
    });
</script>
@endsection
