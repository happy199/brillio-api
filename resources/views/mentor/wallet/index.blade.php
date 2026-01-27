@extends('layouts.mentor')

@section('title', 'Mon Portefeuille')

@section('content')
    <div x-data="{ activeTab: 'earnings' }" class="space-y-8">
        <!-- Header Global -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Mon Portefeuille</h1>
                <p class="text-gray-600">Gérez vos revenus et vos crédits Brillio.</p>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button @click="activeTab = 'earnings'"
                    :class="activeTab === 'earnings' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Mes Revenus & Ventes
                </button>
                <button @click="activeTab = 'recharge'"
                    :class="activeTab === 'recharge' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    Rechargement & Dépenses
                </button>
            </nav>
        </div>

        <!-- Section 1: Revenus (Earnings) -->
        <div x-show="activeTab === 'earnings'" class="space-y-6">
            <!-- Stats Revenus -->
            <div
                class="bg-gradient-to-r from-indigo-600 to-blue-500 rounded-2xl p-6 text-white shadow-lg flex flex-col md:flex-row items-center justify-between gap-6">
                <div>
                    <h2 class="text-indigo-50 text-sm font-medium uppercase tracking-wide mb-1">Total Revenus Générés</h2>
                    <div class="flex items-baseline gap-2">
                        <span class="text-4xl font-extrabold">{{ number_format($totalCreditsEarned) }}</span>
                        <span class="text-sm font-bold opacity-80">Crédits</span>
                    </div>
                    <p class="text-indigo-100 text-sm mt-2">≈ {{ number_format($estimatedValueFcfa, 0, ',', ' ') }} FCFA
                        (Valeur Estimée)</p>
                </div>
                <div class="bg-white/10 p-4 rounded-xl backdrop-blur-sm border border-white/20">
                    <p class="text-xs text-indigo-50 mb-2 font-semibold uppercase">À venir</p>
                    <p class="text-sm">Le convertisseur de crédits en FCFA (payout) sera disponible prochainement.</p>
                </div>
            </div>

            <!-- Historique des Ventes -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-900">Historique des Ventes</h3>
                    <span class="bg-indigo-100 text-indigo-800 text-xs font-bold px-2.5 py-0.5 rounded-full">Ressources &
                        Services</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-6 py-3 font-semibold">Date</th>
                                <th class="px-6 py-3 font-semibold">Description</th>
                                <th class="px-6 py-3 font-semibold">Acheteur / Détail</th>
                                <th class="px-6 py-3 font-semibold text-right">Montant</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($incomeTransactions as $transaction)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                                        {{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 text-gray-900 font-medium">{{ $transaction->description }}</td>
                                    <td class="px-6 py-4">
                                        @if($transaction->related && $transaction->related instanceof \App\Models\Purchase)
                                            <div class="flex items-center gap-2">
                                                <div
                                                    class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-bold text-indigo-600">
                                                    {{ substr($transaction->related->user->name ?? '?', 0, 1) }}
                                                </div>
                                                <div class="flex flex-col">
                                                    <span
                                                        class="font-medium text-gray-900">{{ $transaction->related->user->name ?? 'Utilisateur Inconnu' }}</span>
                                                    <span class="text-xs text-gray-500">Acheté le
                                                        {{ $transaction->related->created_at->format('d/m/Y') }}</span>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-gray-400 italic">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-green-600">+{{ $transaction->amount }} Cr.
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500 italic">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <p>Aucune vente enregistrée pour le moment.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="p-4 bg-gray-50 border-t border-gray-100">
                    {{ $incomeTransactions->appends(['wallet_page' => request()->wallet_page, 'active_tab' => 'earnings'])->links() }}
                </div>
            </div>
        </div>

        <!-- Section 2: Rechargement & Dépenses (Recharge) -->
        <div x-show="activeTab === 'recharge'" class="space-y-6" style="display: none;">
            <!-- Solde Dépenses -->
            <div
                class="bg-gradient-to-r from-orange-500 to-red-600 rounded-2xl p-6 text-white shadow-lg flex flex-col md:flex-row items-center justify-between gap-6">
                <div>
                    <h2 class="text-orange-50 text-sm font-medium uppercase tracking-wide mb-1">Solde Disponible (Dépenses)
                    </h2>
                    <div class="flex items-baseline gap-2">
                        <span class="text-4xl font-extrabold">{{ number_format($user->credits_balance) }}</span>
                        <span class="text-sm font-bold opacity-80">Crédits</span>
                    </div>
                    <p class="text-orange-100 text-sm mt-2">Utilisez ce solde pour promouvoir vos ressources et accéder aux
                        outils premium.</p>
                </div>
                <div class="bg-white/20 p-3 rounded-xl backdrop-blur-sm">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Packs Recharge -->
                <div class="lg:col-span-2 space-y-6">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Acheter des crédits
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($packs as $pack)
                            <div
                                class="bg-white border rounded-xl p-5 hover:border-orange-500 hover:shadow-md transition relative group overflow-hidden">
                                @if($pack['bonus'] > 0)
                                    <div
                                        class="absolute top-0 right-0 bg-green-500 text-white text-[10px] font-bold px-2 py-1 rounded-bl-lg">
                                        -{{ $pack['bonus'] }}%
                                    </div>
                                @endif

                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <span class="block text-2xl font-black text-gray-900">{{ $pack['credits'] }}</span>
                                        <span class="text-xs font-semibold text-gray-500 uppercase">Crédits</span>
                                    </div>
                                    <div class="text-right">
                                        <span
                                            class="block text-lg font-bold text-orange-600">{{ number_format($pack['price'], 0, ',', ' ') }}
                                            F</span>
                                    </div>
                                </div>

                                <form action="{{ route('mentor.wallet.purchase') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="amount" value="{{ $pack['credits'] }}">
                                    <button type="submit"
                                        class="w-full py-2 px-3 bg-gray-50 hover:bg-orange-600 text-gray-700 hover:text-white font-semibold rounded-lg transition text-sm">
                                        Acheter maintenant
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>

                    <!-- Coupon -->
                    <div class="bg-orange-50 rounded-xl p-6 border border-orange-100 mt-6">
                        <h3 class="font-bold text-orange-900 mb-2">Code Promo</h3>
                        <form action="{{ route('mentor.wallet.redeem') }}" method="POST" class="flex gap-2">
                            @csrf
                            <input type="text" name="code" placeholder="Entrez votre code"
                                class="flex-1 bg-white border-0 text-sm rounded-lg focus:ring-2 focus:ring-orange-500 p-3">
                            <button type="submit"
                                class="bg-orange-600 text-white font-bold px-6 py-2 rounded-lg hover:bg-orange-700 transition">
                                Valider
                            </button>
                        </form>
                        @error('code') <p class="text-red-600 text-sm mt-2 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Historique Recharge/Dépenses -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden sticky top-24">
                        <div class="p-4 border-b border-gray-100 bg-gray-50">
                            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Historique Transactions</h2>
                        </div>
                        <div class="divide-y divide-gray-100 max-h-[500px] overflow-y-auto">
                            @forelse($walletTransactions as $transaction)
                                <div class="p-4 hover:bg-gray-50 transition">
                                    <div class="flex justify-between items-center mb-1">
                                        <span
                                            class="text-xs font-medium text-gray-500">{{ $transaction->created_at->format('d M H:i') }}</span>
                                        <span
                                            class="font-bold text-sm {{ $transaction->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $transaction->amount > 0 ? '+' : '' }}{{ $transaction->amount }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-700 truncate" title="{{ $transaction->description }}">
                                        {{ $transaction->description }}
                                    </p>
                                </div>
                            @empty
                                <div class="p-6 text-center text-sm text-gray-500 italic">
                                    Aucune transaction récente.
                                </div>
                            @endforelse
                        </div>
                        <!-- Pagination -->
                        <div class="p-4 bg-gray-50 border-t border-gray-100">
                            {{ $walletTransactions->appends(['income_page' => request()->income_page, 'active_tab' => 'recharge'])->links('pagination::simple-tailwind') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection