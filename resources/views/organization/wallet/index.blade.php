@extends('layouts.organization')

@section('title', 'Mon Portefeuille')

@section('content')
<div class="space-y-10">
    <!-- Header -->
    <div class="text-center max-w-3xl mx-auto space-y-4">
        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
            Mon <span class="text-pink-600">Portefeuille</span>
        </h1>
        <p class="text-lg text-gray-500">
            Gérez vos crédits et suivez vos investissements pour les jeunes.
        </p>
    </div>

    <!-- Stats Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Balance -->
            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-200">
                <div class="px-6 py-8 text-center md:text-left flex items-center justify-between">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 uppercase tracking-wider">Solde actuel</dt>
                        <dd class="mt-2 text-4xl font-black text-gray-900">{{
                            number_format($organization->credits_balance) }} <span
                                class="text-lg font-normal text-gray-400">Crédits</span></dd>
                    </div>
                    <div class="hidden md:block bg-pink-50 p-4 rounded-full">
                        <i class="fas fa-wallet text-pink-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Credit Value Info -->
            <div class="bg-gradient-to-br from-gray-900 to-gray-800 overflow-hidden shadow-lg rounded-2xl text-white">
                <div class="px-6 py-8">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-bold uppercase tracking-widest text-gray-400">Valeur Fiduciaire</span>
                        <i class="fas fa-info-circle text-gray-500"></i>
                    </div>
                    <p class="text-sm text-gray-300 mb-2">1 Crédit Organisation =</p>
                    <p class="text-3xl font-bold italic">{{ number_format($creditPrice) }} FCFA</p>
                    <p class="mt-4 text-xs text-gray-400 leading-relaxed">
                        Cette valeur est utilisée pour calculer l'équivalent financier de vos dépenses dans vos rapports
                        d'export.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900">Dernières Opérations</h3>
                <div class="flex gap-2">
                    <a href="{{ route('organization.wallet.export-pdf') }}"
                        class="inline-flex items-center justify-center text-xs font-bold text-red-600 hover:text-red-700 bg-red-50 px-3 py-1.5 rounded-lg transition-colors">
                        <i class="fas fa-file-pdf mr-1.5"></i> PDF
                    </a>
                    <a href="{{ route('organization.wallet.history') }}"
                        class="inline-flex items-center justify-center text-xs font-bold text-pink-600 hover:text-pink-700 bg-pink-50 px-3 py-1.5 rounded-lg transition-colors">
                        Tout voir <i class="fas fa-arrow-right ml-1.5"></i>
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">
                                Date</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">
                                Description</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">
                                Crédits</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">
                                Valeur (FCFA)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recentTransactions as $transaction)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $transaction->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                                @php
                                $typeLabel = match(strtolower($transaction->type)) {
                                'purchase', 'recharge' => 'Achat',
                                'subscription' => 'Abonnement',
                                'expense' => 'Ressource',
                                'distribution' => 'Distribution',
                                default => ucfirst($transaction->type)
                                };
                                @endphp
                                <span
                                    class="text-gray-400 text-[10px] font-bold uppercase mr-2 border border-gray-200 px-1.5 py-0.5 rounded">{{
                                    $typeLabel }}</span>
                                {{ $transaction->description }}
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold {{ $transaction->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $transaction->amount > 0 ? '+' : '' }}{{ number_format($transaction->amount) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 font-black">
                                {{ number_format($transaction->amount * $creditPrice) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400 italic">Aucune opération récente.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Credit Packs Section -->
    <div class="pt-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">Recharger mon compte</h2>
            <p class="text-gray-500 mt-2">Choisissez un pack de crédits adapté à vos besoins de parrainage.</p>
        </div>

        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            @foreach($creditPacks as $pack)
            <div
                class="relative flex flex-col rounded-2xl border {{ $pack->is_popular ? 'border-pink-500 ring-2 ring-pink-500/10' : 'border-gray-200' }} bg-white p-6 shadow-xl hover:shadow-2xl transition-all group overflow-hidden">
                @if($pack->is_popular)
                <div
                    class="absolute top-0 right-0 bg-pink-500 text-white text-[10px] font-black px-4 py-1 rounded-bl-xl uppercase tracking-widest">
                    Populaire</div>
                @endif

                <div class="mb-6">
                    <h3 class="text-xl font-black text-gray-900 group-hover:text-pink-600 transition-colors">{{
                        $pack->name }}</h3>
                    <div class="mt-4 flex items-baseline">
                        <span class="text-5xl font-black tracking-tight text-gray-900">{{ number_format($pack->credits)
                            }}</span>
                        <span class="ml-1 text-sm font-bold text-gray-400 uppercase tracking-widest">Crédits</span>
                    </div>
                    <p class="mt-2 text-2xl font-bold text-pink-600">{{ number_format($pack->price) }} <span
                            class="text-sm">FCFA</span></p>
                </div>

                <ul class="space-y-4 mb-8 flex-1">
                    @if(isset($pack->features) && is_array($pack->features))
                    @foreach($pack->features as $feature)
                    <li class="flex items-start text-sm text-gray-600">
                        <div
                            class="flex-shrink-0 w-5 h-5 bg-pink-50 rounded-full flex items-center justify-center mr-3 mt-0.5">
                            <i class="fas fa-check text-pink-600 text-[10px]"></i>
                        </div>
                        {{ $feature }}
                    </li>
                    @endforeach
                    @endif
                </ul>

                <form action="{{ route('organization.wallet.purchase') }}" method="POST">
                    @csrf
                    <input type="hidden" name="pack_id" value="{{ $pack->id }}">
                    <button type="submit"
                        class="w-full rounded-xl py-4 text-sm font-black uppercase tracking-widest shadow-lg transition-all {{ $pack->is_popular ? 'bg-pink-600 text-white hover:bg-pink-700 shadow-pink-200' : 'bg-gray-900 text-white hover:bg-black shadow-gray-200' }}">
                        Acheter
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection