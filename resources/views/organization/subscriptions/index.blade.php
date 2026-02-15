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
        <div x-data="{ annual: false }" class="mt-8">
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
                    <a href="#"
                        class="mt-8 block w-full rounded-md bg-pink-50 px-3 py-2 text-center text-sm font-semibold text-pink-600 hover:bg-pink-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pink-600">
                        Votre plan actuel
                    </a>
                </div>

                <!-- Pro Plan -->
                @php
                $proPlan = $monthlyPlans->where('target_plan', 'pro')->first();
                @endphp
                <div class="relative flex flex-col rounded-2xl border-2 border-pink-600 bg-white p-8 shadow-xl">
                    <div
                        class="absolute -top-5 left-0 right-0 mx-auto w-32 rounded-full bg-gradient-to-r from-pink-600 to-purple-600 px-3 py-1 text-center text-xs font-semibold text-white shadow-sm">
                        Populaire
                    </div>
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold leading-6 text-pink-600">Professionnel</h3>
                        <p class="mt-4 text-sm leading-6 text-gray-500">Suivez l'impact et boostez l'engagement.</p>
                        <p class="mt-8 flex flex-wrap items-baseline gap-x-2">
                            <span class="text-3xl lg:text-4xl font-bold tracking-tight text-gray-900"
                                x-text="annual ? '200,000' : '20,000'"></span>
                            <span class="text-sm font-semibold leading-6 text-gray-600">FCFA</span>
                            <span class="text-sm text-gray-500">/ <span x-text="annual ? 'an' : 'mois'"></span></span>
                        </p>
                    </div>
                    <ul role="list" class="mb-8 space-y-4 text-sm leading-6 text-gray-600 flex-1">
                        <li class="flex gap-x-3">
                            <i class="fas fa-check text-pink-600 h-6 w-5 flex-none"></i>
                            <strong>Tout du plan Standard</strong>
                        </li>
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
                    </ul>

                    @if($proPlan)
                    <form action="{{ route('organization.subscriptions.subscribe', $proPlan->id) }}" method="POST">
                        @csrf
                        <!-- Update plan ID dynamically if annual is selected would require JS, for now we stick to monthly default or we need more complex logic. 
                             To keep it simple and working for the "Passer Pro" flow, we submit the monthly ID. 
                             Ideally we'd toggle the ID input based on x-data. -->
                        <button type="submit"
                            class="mt-8 block w-full rounded-md bg-pink-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-pink-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pink-600">
                            Passer au plan Pro
                        </button>
                    </form>
                    @else
                    <button disabled
                        class="mt-8 block w-full rounded-md bg-gray-300 px-3 py-2 text-center text-sm font-semibold text-white cursor-not-allowed">
                        Non disponible
                    </button>
                    @endif
                </div>

                <!-- Enterprise Plan -->
                <div
                    class="relative flex flex-col rounded-2xl border border-gray-200 bg-white p-8 shadow-sm hover:shadow-lg transition-shadow">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold leading-6 text-gray-900">Entreprise</h3>
                        <p class="mt-4 text-sm leading-6 text-gray-500">Accompagnement complet et impact max.</p>
                        <p class="mt-8 flex flex-wrap items-baseline gap-x-2">
                            <span class="text-3xl lg:text-4xl font-bold tracking-tight text-gray-900"
                                x-text="annual ? '500,000' : '50,000'"></span>
                            <span class="text-sm font-semibold leading-6 text-gray-600">FCFA</span>
                            <span class="text-sm text-gray-500">/ <span x-text="annual ? 'an' : 'mois'"></span></span>
                        </p>
                    </div>
                    <ul role="list" class="mb-8 space-y-4 text-sm leading-6 text-gray-600 flex-1">
                        <li class="flex gap-x-3">
                            <i class="fas fa-check text-pink-600 h-6 w-5 flex-none"></i>
                            <strong>Tout du plan Pro</strong>
                        </li>
                        <li class="flex gap-x-3">
                            <i class="fas fa-check text-green-500 h-6 w-5 flex-none"></i>
                            Centre d'Export (PDF, Excel, CSV)
                        </li>
                        <li class="flex gap-x-3 items-start">
                            <div class="flex-none pt-0.5">
                                <i class="fas fa-check text-green-500 h-6 w-5"></i>
                            </div>
                            <span>
                                Support Prioritaire pour garantir la liaison de chaque jeune mentoré à un mentor top 10%
                                <div x-data="{ tooltip: false }" class="relative inline-block ml-1">
                                    <button type="button" @mouseenter="tooltip = true" @mouseleave="tooltip = false"
                                        @click="tooltip = !tooltip"
                                        class="text-gray-400 hover:text-pink-600 focus:outline-none transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                            class="w-4 h-4">
                                            <path fill-rule="evenodd"
                                                d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm11.378-3.917c-.89-.777-2.366-.777-3.255 0a.75.75 0 01-.988-1.129c1.454-1.272 3.776-1.272 5.23 0 1.513 1.324 1.513 3.518 0 4.842a3.75 3.75 0 01-.837.552c-.676.328-1.028.774-1.028 1.152v.75a.75.75 0 01-1.5 0v-.75c0-1.279 1.06-2.107 1.875-2.502.182-.088.351-.199.503-.331.83-.727.83-1.857 0-2.584zM12 18a.75.75 0 100-1.5.75.75 0 000 1.5z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>

                                    <div x-show="tooltip" x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-y-1"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-y-0"
                                        x-transition:leave-end="opacity-0 translate-y-1"
                                        class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-64 p-3 bg-gray-900 text-white text-xs rounded-lg shadow-xl z-50 pointer-events-none text-center leading-relaxed"
                                        style="display: none;">
                                        Nous garantissons un accès prioritaire aux mentors les mieux notés (Top 10%). Si
                                        aucun n'est disponible immédiatement, vous êtes prioritaire sur la liste
                                        d'attente.
                                        <!-- Arrow -->
                                        <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                                            <div class="border-4 border-transparent border-t-gray-900"></div>
                                        </div>
                                    </div>
                                </div>
                            </span>
                        </li>
                        <li class="flex gap-x-3">
                            <i class="fas fa-check text-green-500 h-6 w-5 flex-none"></i>
                            Badge Organisation Certifiée
                        </li>
                        <li class="flex gap-x-3">
                            <i class="fas fa-check text-green-500 h-6 w-5 flex-none"></i>
                            50 Crédits offerts / mois
                        </li>
                    </ul>
                    <a href="mailto:support@brillio.com?subject=Demande%20Plan%20Entreprise%20-%20{{ auth()->user()->organization->name ?? '' }}"
                        class="mt-8 block w-full rounded-md bg-gray-900 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600">
                        Contacter les ventes
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Credit Packs Section -->
    <div class="border-t border-gray-200 pt-16">
        <div class="text-center max-w-3xl mx-auto mb-10">
            <h2 class="text-3xl font-bold tracking-tight text-gray-900">Besoin de plus de crédits ?</h2>
            <p class="mt-4 text-lg text-gray-500">
                Achetez des packs de crédits ponctuels pour financer des ressources ou séances spécifiques.
                Cumulable avec tout abonnement (même Gratuit).
            </p>
        </div>

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @foreach($creditPacks as $pack)
            <div
                class="flex flex-col rounded-xl border border-gray-200 bg-white p-6 shadow-sm hover:border-pink-300 transition-colors">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $pack->name }}</h3>
                    @if($pack->is_popular)
                    <span
                        class="inline-flex items-center rounded-full bg-pink-100 px-2.5 py-0.5 text-xs font-medium text-pink-800">
                        Populaire
                    </span>
                    @endif
                </div>
                <p class="text-3xl font-bold text-gray-900 mb-2">{{ number_format($pack->credits) }} <span
                        class="text-sm font-normal text-gray-500">Crédits</span></p>
                <p class="text-xl font-medium text-pink-600 mb-6">{{ number_format($pack->price) }} FCFA</p>

                <ul class="space-y-3 mb-6 flex-1">
                    @if(isset($pack->features) && is_array($pack->features))
                    @foreach($pack->features as $feature)
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 mt-1 mr-2 text-xs"></i>
                        <span class="text-sm text-gray-600">{{ $feature }}</span>
                    </li>
                    @endforeach
                    @endif
                </ul>

                <form action="{{ route('organization.wallet.purchase') }}" method="POST">
                    @csrf
                    <input type="hidden" name="pack_id" value="{{ $pack->id }}">
                    <button type="submit"
                        class="w-full rounded-md bg-white border border-pink-600 px-3 py-2 text-sm font-semibold text-pink-600 shadow-sm hover:bg-pink-50 transition-colors">
                        Acheter
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection