@extends('layouts.public')

@section('title', 'Ressources - Brillio')
@section('meta_description', 'Découvrez toutes les ressources publiées par nos mentors pour vous aider dans votre orientation et votre carrière.')

@section('content')
<!-- Hero Section -->
<section class="bg-slate-900 text-white pt-32 pb-20 relative overflow-hidden border-b border-slate-800">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-3xl mx-auto text-center text-white">
            <span class="inline-block px-3.5 py-1.5 bg-slate-800 border border-slate-700 rounded-full text-xs font-semibold text-slate-300 mb-6">
                Bibliothèque & Guides
            </span>
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-5 text-white">
                Ressources de nos Mentors
            </h1>
            <p class="text-lg sm:text-xl text-slate-300 max-w-2xl mx-auto font-normal leading-relaxed">
                Découvrez des guides, articles et conseils pratiques partagés par notre communauté de mentors pour vous accompagner dans votre parcours.
            </p>
        </div>
    </div>
</section>

<div class="bg-gray-50 py-16 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @if($resources->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($resources as $resource)
                    <a href="{{ route('auth.login') }}?redirect={{ urlencode(route('jeune.resources.show', $resource->slug)) }}" class="block group">
                        <div class="border border-slate-200 rounded-3xl overflow-hidden hover:border-brand-300 shadow-2xs card-interactive bg-white h-full flex flex-col relative">
                            @if($resource->thumbnail_url)
                                <div class="h-56 w-full overflow-hidden relative z-10 bg-slate-100">
                                    <img src="{{ $resource->thumbnail_url }}" alt="{{ $resource->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                </div>
                            @else
                                <div class="h-56 w-full bg-brand-50 flex items-center justify-center relative z-10">
                                    <svg class="w-16 h-16 text-brand-300 group-hover:scale-110 transition-transform duration-300" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                </div>
                            @endif

                            <div class="p-6 flex flex-col flex-1 relative z-10 bg-white">
                                <h3 class="font-bold text-xl text-slate-900 group-hover:text-brand-600 transition-colors mb-3 leading-snug">{{ $resource->title }}</h3>
                                <p class="text-slate-600 text-sm line-clamp-3 mb-4 flex-1 leading-relaxed">{{ Str::limit($resource->description ?? '', 120) }}</p>

                                <div class="mt-auto pt-4 border-t border-slate-100 flex items-center gap-3">
                                    @if($resource->user)
                                        @php
                                            $initials = strtoupper(substr($resource->user->first_name ?? $resource->user->name ?? 'A', 0, 1) . substr($resource->user->last_name ?? '', 0, 1));
                                            $colors = ['from-brand-500 to-brand-700', 'from-indigo-500 to-indigo-700', 'from-amber-500 to-amber-700', 'from-emerald-500 to-emerald-700'];
                                            $color = $colors[crc32($resource->user->id) % count($colors)];
                                        @endphp
                                        <div class="relative w-8 h-8 rounded-full overflow-hidden shrink-0 border border-slate-200">
                                            <div class="w-full h-full flex items-center justify-center text-xs font-bold text-white bg-gradient-to-br {{ $color }}">
                                                {{ $initials }}
                                            </div>
                                            @if($resource->user->profile_photo_path || $resource->user->profile_photo_url)
                                            <img src="{{ $resource->user->avatar_url }}" alt="{{ $resource->user->name }}" class="absolute inset-0 w-full h-full object-cover bg-white" onerror="this.style.display='none'">
                                            @endif
                                        </div>
                                        <span class="text-xs font-semibold text-slate-700">{{ $resource->user->name }}</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Overlay CTA on Hover -->
                            <div class="absolute inset-0 bg-white/70 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center z-20">
                                <span class="px-5 py-2.5 bg-brand-500 text-white font-bold rounded-2xl shadow-lg transform translate-y-3 group-hover:translate-y-0 transition-transform duration-300 flex items-center gap-2 text-sm btn-spring">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    Connectez-vous pour lire
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-12 flex justify-center">
                {{ $resources->withQueryString()->links() }}
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm p-12 text-center border border-gray-100 max-w-2xl mx-auto">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Aucune ressource pour le moment</h3>
                <p class="text-gray-500">
                    Nos mentors préparent du contenu enrichissant. Revenez bientôt pour découvrir de nouvelles ressources !
                </p>
            </div>
        @endif

    </div>
</div>
@endsection
