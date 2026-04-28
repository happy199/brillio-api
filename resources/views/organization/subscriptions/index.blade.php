@extends('layouts.organization')

@section('title', 'Nos Offres et Abonnements')

@section('content')
@php
$isFree = auth()->user()->organization->subscription_plan === \App\Models\Organization::PLAN_FREE;
$isPro = auth()->user()->organization->isPro() && !auth()->user()->organization->isEnterprise() && !auth()->user()->organization->isEstablishment();
$isEnterprise = auth()->user()->organization->isEnterprise() && !auth()->user()->organization->isEstablishment();
$isEstablishment = auth()->user()->organization->isEstablishment();

$periods = [
30 => '1 mois',
90 => '3 mois',
180 => '6 mois',
270 => '9 mois',
365 => '1 an',
];
@endphp

<div x-data="{ period: 30, showDowngradeModal: false }" class="space-y-12">
    {{-- Header --}}
    <div class="text-center max-w-3xl mx-auto space-y-4">
        <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">
            Choisissez le plan adapté à votre <span class="text-organization-600">impact</span>
        </h1>
        <p class="text-xl text-gray-500">
            Des solutions flexibles pour toutes les organisations, du démarrage à l'expansion.
        </p>
    </div>

    {{-- Period Selector --}}
    <div class="flex items-center justify-center gap-2 flex-wrap">
        @foreach($periods as $days => $label)
        <button type="button" x-on:click="period = {{ $days }}" :class="period === {{ $days }}
                ? 'bg-organization-600 text-white shadow-sm'
                : 'bg-white text-gray-600 border border-gray-300 hover:border-organization-400'"
            class="px-4 py-2 rounded-full text-sm font-semibold transition-all duration-150 focus:outline-none">
            {{ $label }}
            @if($days === 365)
            <span class="ml-1 text-xs font-semibold opacity-80">★ 2 mois offerts</span>
            @elseif($days === 180 || $days === 270)
            <span class="ml-1 text-xs font-semibold opacity-80">★ 1 mois offert</span>
            @endif
        </button>
        @endforeach
    </div>

    {{-- Pricing Cards --}}
    <div class="grid gap-8 grid-cols-1 md:grid-cols-2 lg:grid-cols-3 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Standard (Free) --}}
        <div
            class="relative flex flex-col rounded-2xl border border-gray-200 bg-white p-8 shadow-sm hover:shadow-lg transition-shadow">
            <div class="mb-4 text-center">
                <h3 class="text-lg font-semibold leading-6 text-gray-900">{{ $freePlan?->name ?? 'Standard' }}</h3>
                <p class="mt-4 text-sm leading-6 text-gray-500">{{ $freePlan?->description ?? 'Pour démarrer et
                    parrainer sans limite.' }}</p>
                <p class="mt-8 flex flex-wrap items-baseline justify-center gap-x-2">
                    <span class="text-4xl font-bold tracking-tight text-gray-900">Gratuit</span>
                </p>
            </div>
            <ul role="list" class="mb-8 space-y-3 text-sm leading-6 text-gray-600 flex-1 text-center">
                @if($freePlan && $freePlan->features)
                @foreach($freePlan->features as $feature)
                <li>{{ $feature }}</li>
                @endforeach
                @else
                <li>Parrainage illimité de jeunes</li>
                <li>Tableau de bord standard</li>
                @endif
            </ul>
            @if($isFree)
            <div
                class="mt-8 block w-full rounded-md bg-organization-50 px-3 py-2 text-center text-sm font-semibold text-organization-600">
                Votre plan actuel
            </div>
            @else
            <button type="button" x-on:click="showDowngradeModal = true"
                class="mt-8 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-center text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                Rétrograder vers Standard
            </button>
            @endif
        </div>

        {{-- Pro --}}
        <div
            class="relative flex flex-col rounded-2xl border-2 {{ $isPro ? 'border-organization-600 ring-2 ring-organization-600 ring-opacity-50' : 'border-organization-600' }} bg-white p-8 shadow-xl transition-all duration-300">
            <div
                class="absolute -top-5 left-0 right-0 mx-auto w-32 rounded-full bg-gradient-to-r from-organization-600 to-organization-400 px-3 py-1 text-center text-xs font-semibold text-white shadow-sm">
                Populaire
            </div>
            <div class="mb-4 text-center">
                <h3 class="text-lg font-semibold leading-6 text-organization-600">Professionnel</h3>
                <p class="mt-4 text-sm leading-6 text-gray-500">Suivez l'impact et boostez l'engagement.</p>
                @foreach($periods as $days => $label)
                @php $proPlan = $proPlans->get($days); @endphp
                <p class="mt-8 flex flex-wrap items-baseline justify-center gap-x-2" x-show="period === {{ $days }}" {{
                    $days===30 ? '' : 'style=display:none' }}>
                    <span class="text-3xl lg:text-4xl font-bold tracking-tight text-gray-900">
                        {{ $proPlan ? number_format($proPlan->price) : '—' }}
                    </span>
                    <span class="text-sm font-semibold leading-6 text-gray-600">FCFA</span>
                    <span class="text-sm text-gray-500">/ {{ $label }}</span>
                </p>
                @endforeach
            </div>
            <ul role="list" class="mb-8 space-y-3 text-sm leading-6 text-gray-600 flex-1 text-center">
                <li><strong>Tout du plan Standard</strong></li>
                <li>Gestion de liste des jeunes et mentors</li>
                <li>Statistiques détaillées (Engagement)</li>
                <li>Calendrier global des séances</li>
                <li>Suivi des statuts de séances</li>
                <li>Exports PDF &amp; CSV</li>
            </ul>
            @if($isPro)
            <div class="mt-8 text-center">
                <div
                    class="block w-full rounded-md bg-organization-50 px-3 py-2 text-sm font-semibold text-organization-600 border border-organization-200">
                    Votre plan actuel
                </div>
                @php $org = auth()->user()->organization; @endphp
                @if($org->subscription_expires_at)
                <p class="mt-2 text-xs text-gray-500">
                    Expire le <span class="font-semibold text-gray-700">{{
                        $org->subscription_expires_at->translatedFormat('d F Y') }}</span>
                </p>
                @endif
            </div>
            @else
            @foreach($periods as $days => $label)
            @php $proPlan = $proPlans->get($days); @endphp
            @if($proPlan)
            <form action="{{ route('organization.subscriptions.subscribe', $proPlan->id) }}" method="POST"
                x-show="period === {{ $days }}" {{ $days===30 ? '' : 'style=display:none' }}>
                @csrf
                <button type="submit"
                    class="mt-8 flex flex-col items-center w-full rounded-md bg-organization-600 px-3 py-3 font-semibold text-white shadow-sm hover:bg-organization-500 transition-colors">
                    <span class="text-sm">Souscrire</span>
                    <span class="text-xs font-normal opacity-80">{{ $label }}</span>
                </button>
            </form>
            @endif
            @endforeach
            @endif
        </div>

        {{-- Enterprise --}}
        <div
            class="relative flex flex-col rounded-2xl border {{ $isEnterprise ? 'border-2 border-organization-500 ring-2 ring-organization-500 ring-opacity-50' : 'border-gray-200' }} bg-white p-8 shadow-sm hover:shadow-lg transition-shadow">
            <div class="mb-4 text-center">
                <h3 class="text-lg font-semibold leading-6 text-gray-900">Entreprise</h3>
                <p class="mt-4 text-sm leading-6 text-gray-500">Accompagnement complet et impact max.</p>
                @foreach($periods as $days => $label)
                @php $entPlan = $enterprisePlans->get($days); @endphp
                <p class="mt-8 flex flex-wrap items-baseline justify-center gap-x-2" x-show="period === {{ $days }}" {{
                    $days===30 ? '' : 'style=display:none' }}>
                    <span class="text-3xl lg:text-4xl font-bold tracking-tight text-gray-900">
                        {{ $entPlan ? number_format($entPlan->price) : '—' }}
                    </span>
                    <span class="text-sm font-semibold leading-6 text-gray-600">FCFA</span>
                    <span class="text-sm text-gray-500">/ {{ $label }}</span>
                </p>
                @endforeach
            </div>
            <ul role="list" class="mb-8 space-y-3 text-sm leading-6 text-gray-600 flex-1 text-center">
                <li><strong>Tout du plan Pro</strong></li>
                <li>Marque Blanche (Logo &amp; Couleurs)</li>
                <li>Sous-domaine personnalisé</li>
                <li>Centre d'Export (PDF, Excel, CSV)</li>
                <li>Support dédié prioritaire</li>
                <li class="font-semibold text-gray-800">★ 50 Crédits/mois offerts automatiquement</li>
            </ul>
            @if($isEnterprise)
            <div class="mt-8 text-center">
                <div
                    class="block w-full rounded-md bg-organization-50 px-3 py-2 text-sm font-semibold text-organization-600 border border-organization-200">
                    Votre plan actuel
                </div>
                @php $org = auth()->user()->organization; @endphp
                @if($org->subscription_expires_at)
                <p class="mt-2 text-xs text-gray-500">
                    Expire le <span class="font-semibold text-gray-700">{{
                        $org->subscription_expires_at->translatedFormat('d F Y') }}</span>
                </p>
                @endif
            </div>
            @else
            @foreach($periods as $days => $label)
            @php $entPlan = $enterprisePlans->get($days); @endphp
            @if($entPlan)
            <form action="{{ route('organization.subscriptions.subscribe', $entPlan->id) }}" method="POST"
                x-show="period === {{ $days }}" {{ $days===30 ? '' : 'style=display:none' }}>
                @csrf
                <button type="submit"
                    class="mt-8 flex flex-col items-center w-full rounded-md bg-gray-900 px-3 py-3 font-semibold text-white shadow-sm hover:bg-gray-800 transition-colors">
                    <span class="text-sm">Souscrire</span>
                    <span class="text-xs font-normal opacity-80">{{ $label }}</span>
                </button>
            </form>
            @endif
            @endforeach
            @endif
        </div>

        {{-- Establishment - Premium Lite Design --}}
        <div
            class="relative flex flex-col lg:col-span-3 rounded-[2.5rem] border-2 border-organization-200 bg-white p-8 lg:p-12 shadow-xl hover:shadow-2xl transition-all duration-700 overflow-hidden group">
            
            {{-- Subtle Background Accent --}}
            <div class="absolute -right-20 -top-20 w-96 h-96 bg-organization-50 rounded-full blur-[100px] group-hover:bg-organization-100/50 transition-colors duration-1000"></div>
            <div class="absolute left-0 top-0 w-2 h-full bg-organization-600"></div>

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center gap-12">
                
                {{-- Left: Identity --}}
                <div class="lg:w-1/3 text-center lg:text-left">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-organization-50 text-organization-600 rounded-2xl mb-6 shadow-sm">
                        <i class="fas fa-university text-3xl"></i>
                    </div>
                    <h3 class="text-3xl font-black text-gray-900 tracking-tight mb-2">Plan Établissement</h3>
                    <p class="text-gray-500 font-medium italic mb-8">Universités & Hautes Écoles</p>
                    
                    <div class="flex flex-col gap-1">
                        <span class="text-4xl font-black text-organization-600">Sur Devis</span>
                        <span class="text-[10px] uppercase tracking-[0.3em] text-gray-400 font-bold">Audit institutionnel inclus</span>
                    </div>
                </div>

                {{-- Middle: Features --}}
                <div class="flex-1 lg:border-x lg:border-gray-100 lg:px-12">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-8">
                        @php $activeEstPlan = $establishmentPlans->first(); @endphp
                        @php 
                            $features = ($activeEstPlan && $activeEstPlan->features) ? $activeEstPlan->features : [
                                'Tout du plan Entreprise',
                                'Fiche Établissement premium',
                                'Outils de prospection MBTI',
                                'Formulaires d\'intérêt IA',
                                'Publication d\'événements',
                                'Mise en avant prioritaire'
                            ];
                        @endphp

                        @foreach($features as $feature)
                            <div class="flex items-center gap-3 group/feat">
                                <div class="flex-shrink-0 w-5 h-5 rounded-full bg-organization-50 flex items-center justify-center group-hover/feat:bg-organization-600 transition-colors duration-300">
                                    <i class="fas fa-check text-[10px] text-organization-600 group-hover/feat:text-white"></i>
                                </div>
                                <span class="text-sm font-semibold text-gray-700">{{ $feature }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Right: CTA --}}
                <div class="lg:w-1/4 flex flex-col items-center gap-4">
                    @if($isEstablishment)
                        <div class="w-full rounded-2xl bg-gray-50 border border-gray-100 p-6 text-center">
                            <i class="fas fa-check-circle text-organization-600 text-2xl mb-2"></i>
                            <p class="text-sm font-black text-gray-900 uppercase tracking-widest leading-none">Actif</p>
                            <p class="text-[10px] text-gray-400 mt-1">Soutenu par Brillio</p>
                        </div>
                    @else
                        <form action="{{ route('organization.subscriptions.request-contact') }}" method="POST" class="w-full">
                            @csrf
                            <button type="submit"
                                class="w-full py-5 bg-organization-600 text-white rounded-2xl font-black text-sm tracking-widest hover:bg-organization-700 hover:shadow-lg hover:-translate-y-1 active:translate-y-0 transition-all duration-300">
                                <i class="fas fa-paper-plane mr-2"></i> ÊTRE RECONTACTÉ
                            </button>
                        </form>
                        <p class="text-[10px] text-gray-400 text-center font-bold uppercase tracking-tighter">Réponse sous 48h maximum</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    </div>

    {{-- Downgrade Modal --}}
    <div x-show="showDowngradeModal" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;" x-cloak>
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            {{-- Overlay --}}
            <div x-show="showDowngradeModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                x-on:click="showDowngradeModal = false"></div>

            {{-- Panel --}}
            <div x-show="showDowngradeModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-base font-semibold leading-6 text-gray-900">Confirmer la rétrogradation</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 leading-relaxed">
                                    Êtes-vous sûr de vouloir repasser au plan <span class="font-bold">Standard</span> ?
                                    Votre accès aux fonctionnalités <span
                                        class="text-organization-600 font-bold">Pro/Entreprise</span>
                                    restera actif jusqu'à la fin de la période de facturation en cours.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-3">
                    <form action="{{ route('organization.subscriptions.downgrade') }}" method="POST"
                        class="inline-block">
                        @csrf
                        <button type="submit"
                            class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:w-auto transition-colors">
                            Confirmer la rétrogradation
                        </button>
                    </form>
                    <button type="button"
                        class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors"
                        x-on:click="showDowngradeModal = false">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection