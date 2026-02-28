@extends('layouts.organization')

@section('title', $resource->title)

@push('styles')
<style>
    .resource-content {
        line-height: 1.7 layer(0);
        color: #374151;
    }

    .resource-content h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
        margin: 2rem 0 1rem;
    }

    .resource-content h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #111827;
        margin: 1.5rem 0 0.75rem;
    }

    .resource-content p {
        margin-bottom: 1.25rem;
    }

    .resource-content ul,
    .resource-content ol {
        margin: 1rem 0 1.25rem;
        padding-left: 1.5rem;
    }

    .resource-content ul {
        list-style-type: disc;
    }

    .resource-content ol {
        list-style-type: decimal;
    }

    .resource-content blockquote {
        border-left: 4px solid {
                {
                $organization->primary_color ?? '#f43f5e'
            }
        }

        ;
        padding: 1rem 1.5rem;
        margin: 1.5rem 0;
        background: #f9fafb;
        border-radius: 0.5rem;
        font-style: italic;
    }

    .resource-content img {
        border-radius: 0.75rem;
        margin: 2rem 0;
        max-width: 100%;
        height: auto;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="{ 
    selectedJeunes: [],
    creditCostPerJeune: {{ $creditCost }},
    get totalCost() { return this.selectedJeunes.length * this.creditCostPerJeune },
    get canAfford() { return this.totalCost <= {{ $organization->credits_balance }} }
}">
    <div class="flex items-center justify-between">
        <a href="{{ route('organization.resources.index') }}"
            class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-organization-600 transition">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Retour à la bibliothèque
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <article class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
                @if($resource->preview_image_path)
                <div class="aspect-video w-full bg-gray-100">
                    <img src="{{ Storage::url($resource->preview_image_path) }}" class="w-full h-full object-cover">
                </div>
                @endif

                <div class="p-8">
                    <header class="mb-8">
                        <div class="flex items-center gap-2 mb-4">
                            @if($resource->is_premium)
                            <span
                                class="px-2 py-1 bg-organization-600 text-white text-[10px] font-bold rounded-lg uppercase">Premium</span>
                            @else
                            <span
                                class="px-2 py-1 bg-green-600 text-white text-[10px] font-bold rounded-lg uppercase">Gratuit</span>
                            @endif
                            <span
                                class="px-2 py-1 bg-gray-100 text-gray-600 text-[10px] font-bold rounded-lg uppercase">{{
                                $resource->type }}</span>
                        </div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $resource->title }}</h1>
                        <div class="flex items-center gap-4 text-sm text-gray-500">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center font-bold overflow-hidden border">
                                    @if($resource->user->profile_photo_path)
                                    <img src="{{ Storage::url($resource->user->profile_photo_path) }}"
                                        class="w-full h-full object-cover">
                                    @else
                                    {{ substr($resource->user->name, 0, 1) }}
                                    @endif
                                </div>
                                <span class="font-medium text-gray-900">{{ $resource->user->name }}</span>
                            </div>
                            <span>•</span>
                            <span>{{ $resource->created_at->format('d/m/Y') }}</span>
                            <span>•</span>
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                {{ $resource->views_count }} vues
                            </span>
                        </div>
                    </header>

                    <div class="prose prose-organization max-w-none text-gray-600 mb-8 font-medium italic">
                        {{ $resource->description }}
                    </div>

                    <div class="h-px bg-gray-100 my-8"></div>

                    @if($isLocked)
                    <div class="bg-gray-50 border border-gray-200 rounded-2xl p-10 text-center space-y-6">
                        <div
                            class="w-16 h-16 bg-organization-100 rounded-full flex items-center justify-center mx-auto">
                            <svg class="w-8 h-8 text-organization-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Contenu Premium</h3>
                            <p class="text-gray-600 max-w-md mx-auto">Le contenu détaillé de cette ressource est réservé
                                aux bénéficiaires. Offrez-la à vos jeunes pour qu'ils puissent y accéder depuis leur
                                espace.</p>
                        </div>
                        <div
                            class="inline-block px-5 py-2 bg-white rounded-lg border border-gray-100 shadow-sm text-sm font-bold text-organization-600 uppercase tracking-widest">
                            {{ number_format($creditCost) }} CRÉDITS / JEUNE
                        </div>
                    </div>
                    @else
                    <div class="resource-content">
                        {!! $resource->content !!}
                    </div>

                    @if($resource->file_path)
                    <div
                        class="mt-12 p-6 bg-gray-50 rounded-xl border border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-white rounded-lg shadow-sm text-organization-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900">Fichier de la ressource</h4>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Format : {{
                                    pathinfo($resource->file_path, PATHINFO_EXTENSION) }}</p>
                            </div>
                        </div>
                        <a href="{{ Storage::url($resource->file_path) }}" download
                            class="px-6 py-2 bg-white border border-gray-200 rounded-lg text-sm font-bold text-gray-700 hover:bg-gray-50 transition shadow-sm">
                            Télécharger
                        </a>
                    </div>
                    @endif
                    @endif
                </div>
            </article>
        </div>

        <!-- Sidebar: Gifting -->
        @if(auth()->user()->organization_role === 'admin')
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm sticky top-24">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Offrir cette ressource</h3>

                @if(!$resource->is_premium)
                <div class="p-4 bg-green-50 rounded-lg border border-green-100 text-green-700 text-sm">
                    <p>Cette ressource est gratuite. Tous vos jeunes peuvent y accéder librement depuis leur espace sans
                        action de votre part.</p>
                </div>
                @else
                <form action="{{ route('organization.resources.gift', $resource) }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="space-y-3">
                        <label class="text-sm font-semibold text-gray-700">Choisir les bénéficiaires</label>
                        <div class="max-h-64 overflow-y-auto border border-gray-100 rounded-lg divide-y divide-gray-50">
                            @forelse($jeunes as $jeune)
                            @php $alreadyHas = in_array($jeune->id, $alreadyGiftedJeuneIds); @endphp
                            <label
                                class="flex items-center p-3 gap-3 {{ $alreadyHas ? 'opacity-50 cursor-not-allowed bg-gray-50' : 'hover:bg-gray-50 cursor-pointer' }}">
                                <input type="checkbox" name="jeune_ids[]" value="{{ $jeune->id }}"
                                    x-model="selectedJeunes" {{ $alreadyHas ? 'disabled' : '' }}
                                    class="w-4 h-4 text-organization-600 border-gray-300 rounded focus:ring-organization-500">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $jeune->name }}</p>
                                    @if($alreadyHas)
                                    <span class="text-[10px] text-emerald-600 font-bold uppercase">Déjà reçu</span>
                                    @endif
                                </div>
                            </label>
                            @empty
                            <p class="p-4 text-sm text-gray-500 italic">Aucun jeune parrainé trouvé.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-xl space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Sélection</span>
                            <span class="font-bold text-gray-900" x-text="selectedJeunes.length + ' jeune(s)'"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Coût par jeune</span>
                            <span class="font-bold text-gray-900">{{ number_format($creditCost) }} crédits</span>
                        </div>
                        <div class="h-px bg-gray-200"></div>
                        <div class="flex justify-between items-end pt-1">
                            <span class="text-sm font-bold text-gray-900 uppercase">Total à payer</span>
                            <div class="text-right">
                                <span class="text-2xl font-black text-organization-600"
                                    x-text="new Intl.NumberFormat().format(totalCost)"></span>
                                <span class="text-xs font-bold text-organization-500 ml-1">Credits</span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <button type="submit" :disabled="selectedJeunes.length === 0 || !canAfford"
                            :class="selectedJeunes.length === 0 || !canAfford ? 'opacity-50 cursor-not-allowed' : 'hover:scale-105 active:scale-95'"
                            class="w-full bg-organization-600 text-white font-bold py-4 rounded-xl shadow-lg transition transform duration-200 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            Confirmer le cadeau
                        </button>

                        <div class="flex items-center justify-center gap-2 text-xs">
                            <span class="text-gray-500">Votre solde :</span>
                            <span
                                class="font-bold px-2 py-0.5 rounded {{ $organization->credits_balance < 1 ? 'bg-red-100 text-red-600' : 'bg-organization-100 text-organization-700' }}">
                                {{ number_format($organization->credits_balance) }} crédits
                            </span>
                        </div>

                        <p x-show="!canAfford && selectedJeunes.length > 0"
                            class="text-center text-[10px] text-red-500 font-bold uppercase animate-pulse">
                            Solde insuffisant pour cette sélection
                        </p>
                    </div>
                </form>
                @endif
            </div>

            <div class="bg-organization-600 rounded-2xl p-6 text-white shadow-lg overflow-hidden relative">
                <div class="relative z-10">
                    <h4 class="font-bold mb-2 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Besoin de plus de crédits ?
                    </h4>
                    <p class="text-sm opacity-90 mb-4">Rechargez votre portefeuille pour continuer à offrir des
                        ressources premium à vos jeunes parrainés.</p>
                    <a href="{{ route('organization.wallet.index') }}"
                        class="inline-block px-4 py-2 bg-white text-organization-600 rounded-lg text-sm font-bold hover:bg-organization-50 transition">
                        Aller au portefeuille
                    </a>
                </div>
                <svg class="absolute -right-4 -bottom-4 w-32 h-32 text-white/10" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"
                        clip-rule="evenodd" />
                </svg>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection