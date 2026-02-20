@extends('layouts.organization')

@section('title', 'Nos Offres et Abonnements')

@section('content')
<div class="space-y-12">
    <!-- Header -->
    <div class="text-center max-w-3xl mx-auto space-y-4">
        <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">
            Choisissez le plan adapté à votre <span class="text-pink-600">impact</span>
        </h1>
        <p class="text-xl text-gray-500">
            Des solutions flexibles pour toutes les organisations, du démarrage à l'expansion.
            Changez ou annulez à tout moment.
        </p>

        <!-- Toggle Monthly/Yearly (UI Logic via Alpine) -->
        <div x-data="{ annual: false, showDowngradeModal: false }" class="mt-8">
            <div class="flex items-center justify-center space-x-4">
                <span :class="!annual ? 'text-gray-900 font-medium' : 'text-gray-500'">Mensuel</span>
                <button type="button"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2"
                    :class="annual ? 'bg-pink-600' : 'bg-gray-200'" @click="annual = !annual">
                    <span class="sr-only">Use setting</span>
                    <span aria-hidden="true"
                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                        :class="annual ? 'translate-x-5' : 'translate-x-0'">
                    </span>
                </button>
                <span :class="annual ? 'text-gray-900 font-medium' : 'text-gray-500'">
                    Annuel <span class="text-pink-600 text-xs font-bold bg-pink-100 px-2 py-0.5 rounded-full">-2 mois
                        offerts</span>
                </span>
            </div>

            <!-- Pricing Cards -->
            <div class="mt-12 grid gap-8 lg:grid-cols-3 lg:gap-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Free Plan -->
                @php
                $isFree = auth()->user()->organization->subscription_plan === \App\Models\Organization::PLAN_FREE;
                $isPro = auth()->user()->organization->isPro();
                $isEnterprise = auth()->user()->organization->isEnterprise();
                @endphp
                <div
                    class="relative flex flex-col rounded-2xl border border-gray-200 bg-white p-8 shadow-sm hover:shadow-lg transition-shadow">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold leading-6 text-gray-900">Standard</h3>
                        <p class="mt-4 text-sm leading-6 text-gray-500">Pour démarrer et parrainer sans limite.</p>
                        <p class="mt-8 flex flex-wrap items-baseline gap-x-2">
                            <span class="text-4xl font-bold tracking-tight text-gray-900">Gratuit</span>
                        </p>
                    </div>
                    <ul role="list" class="mb-8 space-y-4 text-sm leading-6 text-gray-600 flex-1">
                        <li class="flex gap-x-3">
                            <i class="fas fa-check text-green-500 h-6 w-5 flex-none"></i>
                            Parrainage illimité de jeunes
                        </li>
                        <li class="flex gap-x-3">
                            <i class="fas fa-check text-green-500 h-6 w-5 flex-none"></i>
                            Tableau de bord standard
                        </li>
                        <li class="flex gap-x-3">
                            <i class="fas fa-check text-green-500 h-6 w-5 flex-none"></i>
                            Gestion de liste des jeunes
                        </li>
                    </ul>
                    @if($isFree)
                    <div
                        class="mt-8 block w-full rounded-md bg-pink-50 px-3 py-2 text-center text-sm font-semibold text-pink-600">
                        Votre plan actuel
                    </div>
                    @else
                    <button type="button" @click="showDowngradeModal = true"
                        class="mt-8 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-center text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                        Rétrograder vers Standard
                    </button>
                    @endif
                </div>

                <!-- Pro Plan -->
                @php
                $proPlan = $monthlyPlans->where('target_plan', 'pro')->first();
                @endphp
                <div
                    class="relative flex flex-col rounded-2xl border-2 {{ $isPro ? 'border-pink-600 ring-2 ring-pink-600 ring-opacity-50' : 'border-pink-600' }} bg-white p-8 shadow-xl transition-all duration-300">
                    <div
                        class="absolute -top-5 left-0 right-0 mx-auto w-32 rounded-full bg-gradient-to-r from-pink-600 to-purple-600 px-3 py-1 text-center text-xs font-semibold text-white shadow-sm">
                        Populaire
                    </div>
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold leading-6 text-pink-600">Professionnel</h3>
                        <p class="mt-4 text-sm leading-6 text-gray-500">Suivez l'impact et boostez l'engagement.</p>
                        <p class="mt-8 flex flex-wrap items-baseline gap-x-2">
                            <span class="text-3xl lg:text-4xl font-bold tracking-tight text-gray-900"
                                x-text="annual ? '{{ number_format(200000) }}' : '{{ number_format(20000) }}'"></span>
                            <span class="text-sm font-semibold leading-6 text-gray-600">FCFA</span>
                            <span class="text-sm text-gray-500">/ <span x-text="annual ? 'an' : 'mois'"></span></span>
                        </p>
                    </div>
                    @if($proPlan)
                    <ul role="list" class="mb-8 space-y-4 text-sm leading-6 text-gray-600 flex-1">
                        <li class="flex gap-x-3">
                            <i class="fas fa-check text-pink-600 h-6 w-5 flex-none"></i>
                            <strong>Tout du plan Standard</strong>
                        </li>
                        @if($proPlan->features && is_array($proPlan->features))
                        @foreach($proPlan->features as $feature)
                        <li class="flex gap-x-3">
                            <i class="fas fa-check text-green-500 h-6 w-5 flex-none"></i>
                            {{ $feature }}
                        </li>
                        @endforeach
                        @else
                        <li class="flex gap-x-3">
                            <i class="fas fa-check text-green-500 h-6 w-5 flex-none"></i>
                            Statistiques détaillées (Engagement)
                        </li>
                        <li class="flex gap-x-3">
                            <i class="fas fa-check text-green-500 h-6 w-5 flex-none"></i>
                            Calendrier global des séances
                        </li>
                        <li class="flex gap-x-3">
                            <i class="fas fa-check text-green-500 h-6 w-5 flex-none"></i>
                            Suivi des statuts de séances
                        </li>
                        @endif
                    </ul>

                    @if($isPro)
                    <div
                        class="mt-8 block w-full rounded-md bg-pink-50 px-3 py-2 text-center text-sm font-semibold text-pink-600 border border-pink-200">
                        Votre plan actuel
                    </div>
                    @else
                    <form action="{{ route('organization.subscriptions.subscribe', $proPlan->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="billing_cycle" :value="annual ? 'yearly' : 'monthly'">
                        <button type="submit"
                            class="mt-8 block w-full rounded-md bg-pink-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-pink-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pink-600">
                            Passer au plan Pro
                        </button>
                    </form>
                    @endif
                    @else
                    <button disabled
                        class="mt-8 block w-full rounded-md bg-gray-300 px-3 py-2 text-center text-sm font-semibold text-white cursor-not-allowed">
                        Non disponible
                    </button>
                    @endif
                </div>

                @php
                $entPlan = $monthlyPlans->where('target_plan', 'enterprise')->first();
                @endphp
                <div
                    class="relative flex flex-col rounded-2xl border border-gray-200 bg-white p-8 shadow-sm hover:shadow-lg transition-shadow">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold leading-6 text-gray-900">Entreprise</h3>
                        <p class="mt-4 text-sm leading-6 text-gray-500">{{ $entPlan->description ?? "Accompagnement
                            complet et impact max." }}</p>
                        <p class="mt-8 flex flex-wrap items-baseline gap-x-2">
                            <span class="text-3xl lg:text-4xl font-bold tracking-tight text-gray-900"
                                x-text="annual ? '{{ number_format(($yearlyPlans->where('target_plan', 'enterprise')->first()->price ?? 500000)) }}' : '{{ number_format(($entPlan->price ?? 50000)) }}'"></span>
                            <span class="text-sm font-semibold leading-6 text-gray-600">FCFA</span>
                            <span class="text-sm text-gray-500">/ <span x-text="annual ? 'an' : 'mois'"></span></span>
                        </p>
                    </div>
                    <ul role="list" class="mb-8 space-y-4 text-sm leading-6 text-gray-600 flex-1">
                        <li class="flex gap-x-3">
                            <i class="fas fa-check text-pink-600 h-6 w-5 flex-none"></i>
                            <strong>Tout du plan Pro</strong>
                        </li>
                        @if($entPlan && $entPlan->features && is_array($entPlan->features))
                        @foreach($entPlan->features as $feature)
                        <li class="flex gap-x-3">
                            <i class="fas fa-check text-green-500 h-6 w-5 flex-none"></i>
                            {{ $feature }}
                        </li>
                        @endforeach
                        @else
                        <li class="flex gap-x-3">
                            <i class="fas fa-check text-green-500 h-6 w-5 flex-none"></i>
                            Marque Blanche (Logo & Couleurs)
                        </li>
                        <li class="flex gap-x-3">
                            <i class="fas fa-check text-green-500 h-6 w-5 flex-none"></i>
                            Nom de domaine personnalisé
                        </li>
                        <li class="flex gap-x-3">
                            <i class="fas fa-check text-green-500 h-6 w-5 flex-none"></i>
                            Centre d'Export (PDF, Excel, CSV)
                        </li>
                        <li class="flex gap-x-3">
                            <i class="fas fa-check text-green-500 h-6 w-5 flex-none"></i>
                            Support dédié
                        </li>
                        <li class="flex gap-x-3">
                            <i class="fas fa-check text-green-500 h-6 w-5 flex-none"></i>
                            50 Crédits/mois offerts
                        </li>
                        @endif
                    </ul>
                    <a href="mailto:contact@brillio.africa?subject=Demande%20Plan%20Entreprise%20-%20{{ auth()->user()->organization->name ?? '' }}"
                        class="mt-8 block w-full rounded-md bg-gray-900 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600">
                        Contacter les ventes
                    </a>
                </div>
            </div>

            <!-- Custom Downgrade Modal -->
            <div x-show="showDowngradeModal" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;"
                x-cloak>
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <!-- Backdrop -->
                    <div x-show="showDowngradeModal" x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                        @click="showDowngradeModal = false"></div>

                    <!-- Modal panel -->
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
                                    <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">
                                        Confirmer la rétrogradation</h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500 leading-relaxed">
                                            Êtes-vous sûr de vouloir repasser au plan <span
                                                class="font-bold">Standard</span> ?
                                            Votre accès aux fonctionnalités <span
                                                class="text-pink-600 font-bold">Pro</span>
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
                                @click="showDowngradeModal = false">
                                Annuler
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection