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

                    <form action="{{ route('organization.subscriptions.subscribe', $proPlan->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="billing_cycle" :value="annual ? 'yearly' : 'monthly'">
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
                            Centre d'Export (PDF, Excel, CSV)
                        </li>
                        <!-- ... original support text ... -->
                        @endif
                    </ul>
                    <a href="mailto:contact@brillio.africa?subject=Demande%20Plan%20Entreprise%20-%20{{ auth()->user()->organization->name ?? '' }}"
                        class="mt-8 block w-full rounded-md bg-gray-900 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600">
                        Contacter les ventes
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection