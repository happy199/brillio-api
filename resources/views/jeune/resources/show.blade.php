@extends('layouts.jeune')

@section('title', $resource->title)

@push('styles')
<style>
    .resource-content {
        line-height: 1.75;
        color: #374151;
    }

    .resource-content h2 {
        font-size: 1.875rem;
        font-weight: 700;
        color: #111827;
        margin-top: 2rem;
        margin-bottom: 1rem;
        line-height: 1.25;
    }

    .resource-content h3 {
        font-size: 1.5rem;
        font-weight: 600;
        color: #111827;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
        line-height: 1.3;
    }

    .resource-content p {
        margin-bottom: 1.25rem;
    }

    .resource-content strong {
        font-weight: 700;
        color: #111827;
    }

    .resource-content em {
        font-style: italic;
    }

    .resource-content ul,
    .resource-content ol {
        margin-top: 1rem;
        margin-bottom: 1.25rem;
        padding-left: 1.625rem;
    }

    .resource-content ul {
        list-style-type: disc;
    }

    .resource-content ol {
        list-style-type: decimal;
    }

    .resource-content li {
        margin-bottom: 0.5rem;
        padding-left: 0.375rem;
    }

    .resource-content blockquote {
        border-left: 4px solid #6366f1;
        padding-left: 1rem;
        margin: 1.5rem 0;
        font-style: italic;
        color: #4b5563;
        background: #f9fafb;
        padding: 1rem 1rem 1rem 1.5rem;
        border-radius: 0.5rem;
    }

    .resource-content a {
        color: #6366f1;
        text-decoration: underline;
        font-weight: 500;
    }

    .resource-content a:hover {
        color: #4f46e5;
    }

    .resource-content code {
        background: #f3f4f6;
        padding: 0.125rem 0.375rem;
        border-radius: 0.25rem;
        font-family: monospace;
        font-size: 0.875em;
        color: #ef4444;
    }

    .resource-content pre {
        background: #1f2937;
        color: #f9fafb;
        padding: 1rem;
        border-radius: 0.5rem;
        overflow-x: auto;
        margin: 1.5rem 0;
    }

    .resource-content pre code {
        background: transparent;
        padding: 0;
        color: inherit;
    }
</style>
@endpush

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
            <header class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ $resource->title }}</h1>
                    <div class="flex items-center gap-4 text-gray-500 text-sm">
                        <div class="flex items-center gap-2">
                            @php
                            $creatorProfileUrl = $resource->user->isMentor() && $resource->user->mentorProfile ?
                            route('jeune.mentors.show', $resource->user->mentorProfile) : '#';
                            @endphp
                            <a href="{{ $creatorProfileUrl }}"
                                class="w-8 h-8 rounded-full bg-gray-200 overflow-hidden {{ $creatorProfileUrl !== '#' ? 'hover:scale-110 transition-transform' : 'cursor-default' }}">
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
                            <a href="{{ $creatorProfileUrl }}"
                                class="font-medium text-gray-900 {{ $creatorProfileUrl !== '#' ? 'hover:text-indigo-600 transition-colors' : 'cursor-default' }}">
                                {{ $resource->user->name }}
                            </a>
                        </div>
                        <span>•</span>
                        <time datetime="{{ $resource->created_at->toIso8601String() }}">
                            Publié le {{ $resource->created_at->format('d/m/Y') }}
                        </time>
                        <span>•</span>
                        <div class="flex items-center gap-1" title="{{ $resource->views_count }} vues">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span>{{ $resource->views_count }}</span>
                        </div>

                        @if($resource->is_premium && $resource->sales_count >= 10)
                        <span>•</span>
                        <div class="flex items-center gap-1 text-purple-600 font-medium"
                            title="{{ $resource->sales_count }} achats">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            <span>{{ $resource->sales_count }} achats</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Share Button -->
                <button onclick="shareResource()"
                    class="flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition font-medium text-sm border border-indigo-100 shadow-sm shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                    </svg>
                    Partager
                </button>
            </header>

            <!-- Description -->
            <div class="prose prose-lg prose-indigo max-w-none text-gray-600 mb-12">
                <p class="lead">{{ $resource->description }}</p>
            </div>

            <!-- Main Content -->
            <!-- Main Content -->
            @if(($isLocked ?? false))
            <!-- Contenu Verrouillé -->
            <div class="bg-gray-50 border border-gray-200 rounded-2xl p-8 text-center space-y-6">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>

                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Contenu Premium Verrouillé</h3>
                    <p class="text-gray-600 max-w-md mx-auto">Cette ressource contient du contenu exclusif réservé aux
                        membres. Débloquez-la dès maintenant pour y accéder !</p>
                </div>

                <div class="bg-white inline-block px-6 py-3 rounded-xl border border-gray-200 shadow-sm">
                    <span class="text-sm text-gray-500 uppercase font-bold tracking-wider">Coût de déblocage</span>
                    <div class="flex items-center justify-center gap-2 mt-1">
                        <span class="text-3xl font-extrabold text-purple-600">{{ $unlockCost }}</span>
                        <span class="text-sm font-bold text-gray-400">Crédits</span>
                    </div>
                </div>

                <form action="{{ route('jeune.resources.unlock', $resource) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-bold py-4 px-8 rounded-xl hover:shadow-lg hover:scale-105 transition transform duration-200 w-full sm:w-auto">
                        Débloquer le contenu
                    </button>
                    <p class="text-xs text-gray-400 mt-4">Votre solde : {{ auth()->user()->credits_balance }} crédits
                    </p>
                </form>
            </div>
            @else
            <!-- Contenu Débloqué -->
            @if($resource->content)
            <div class="resource-content text-lg max-w-none mb-12">
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
            @endif
        </div>
    </article>
</div>
<script>
    function shareResource() {
        if (navigator.share) {
            navigator.share({
                title: '{{ $resource->title }} - Ressource Brillio',
                text: 'Découvre cette ressource pédagogique sur Brillio !',
                url: window.location.href
            }).catch(console.error);
        } else {
            navigator.clipboard.writeText(window.location.href).then(() => {
                window.showToast('Lien copié dans le presse-papier !');
            }).catch(() => {
                window.showToast('Impossible de copier le lien', 'error');
            });
        }
    }
</script>
@endsection