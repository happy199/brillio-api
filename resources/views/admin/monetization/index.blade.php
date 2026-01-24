@extends('layouts.admin')

@section('title', 'Monétisation')

@section('content')
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Monétisation & Crédits</h1>
                <p class="text-gray-600">Gérez l'économie de la plateforme</p>
            </div>
            <a href="{{ route('admin.monetization.coupons') }}"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition">
                Gérer les Coupons
            </a>
        </div>

        <!-- Stats Rapides -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Crédits Achetés (Total)</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($totalCreditsPurchased) }}</p>
                </div>
                <div class="p-3 bg-green-50 rounded-full">
                    <span class="text-2xl">💰</span>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-6 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Crédits Consommés</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($totalCreditsUsed) }}</p>
                </div>
                <div class="p-3 bg-blue-50 rounded-full">
                    <span class="text-2xl">📉</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Configuration -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl border border-gray-200 p-6 sticky top-24">
                    <h2 class="text-lg font-bold text-gray-900 mb-6 pb-2 border-b border-gray-100">Configuration</h2>

                    <form action="{{ route('admin.monetization.settings.update') }}" method="POST" class="space-y-5">
                        @csrf

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Prix d'un Crédit (FCFA)</label>
                            <div class="relative">
                                <input type="number" name="credit_price" value="{{ $creditPrice }}"
                                    class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 pr-12">
                                <div
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500 text-xs">
                                    FCFA</div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Coût Ciblage Avancé
                                (Crédits)</label>
                            <div class="relative">
                                <input type="number" name="feature_cost_advanced_targeting" value="{{ $targetingCost }}"
                                    class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 pr-12">
                                <div
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500 text-xs">
                                    Crédits</div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Débité lors de la création/maj d'une ressource avec
                                ciblage.</p>
                        </div>

                        <button type="submit"
                            class="w-full bg-gray-900 text-white font-bold py-2.5 rounded-lg hover:bg-black transition">
                            Enregistrer
                        </button>
                    </form>
                </div>
            </div>

            <!-- Historique Transactions -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h2 class="text-lg font-bold text-gray-900">Dernières Transactions</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="px-6 py-3 font-semibold">Date</th>
                                    <th class="px-6 py-3 font-semibold">Utilisateur</th>
                                    <th class="px-6 py-3 font-semibold">Type</th>
                                    <th class="px-6 py-3 font-semibold">Montant</th>
                                    <th class="px-6 py-3 font-semibold">Description</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($transactions as $transaction)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-3 whitespace-nowrap">
                                            {{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-3 font-medium text-gray-900">
                                            {{ $transaction->user->name ?? 'Utilisateur supprimé' }}</td>
                                        <td class="px-6 py-3">
                                            @if($transaction->amount > 0)
                                                <span
                                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Crédit</span>
                                            @else
                                                <span
                                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Débit</span>
                                            @endif
                                        </td>
                                        <td
                                            class="px-6 py-3 font-bold {{ $transaction->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $transaction->amount > 0 ? '+' : '' }}{{ $transaction->amount }}
                                        </td>
                                        <td class="px-6 py-3 text-xs text-gray-500 max-w-xs truncate"
                                            title="{{ $transaction->description }}">
                                            {{ $transaction->description }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500 italic">Aucune transaction
                                            enregistrée.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection