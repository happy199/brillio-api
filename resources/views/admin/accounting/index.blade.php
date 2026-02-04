@extends('layouts.admin')

@section('title', 'Comptabilité')
@section('header', 'Comptabilité & Trésorerie')

@section('content')
    <div class="space-y-6">

        <!-- Filtres Période -->
        <div class="bg-white rounded-lg shadow p-4 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center space-x-2">
                <span class="text-gray-600 font-medium"><i class="fas fa-calendar-alt mr-2"></i>Période :</span>

                <a href="{{ route('admin.accounting.index', ['period' => 'today']) }}"
                    class="px-3 py-1 rounded-md text-sm {{ $period === 'today' ? 'bg-indigo-100 text-indigo-700 font-bold' : 'text-gray-600 hover:bg-gray-100' }}">
                    Aujourd'hui
                </a>
                <a href="{{ route('admin.accounting.index', ['period' => 'week']) }}"
                    class="px-3 py-1 rounded-md text-sm {{ $period === 'week' ? 'bg-indigo-100 text-indigo-700 font-bold' : 'text-gray-600 hover:bg-gray-100' }}">
                    Cette Semaine
                </a>
                <a href="{{ route('admin.accounting.index', ['period' => 'month']) }}"
                    class="px-3 py-1 rounded-md text-sm {{ $period === 'month' ? 'bg-indigo-100 text-indigo-700 font-bold' : 'text-gray-600 hover:bg-gray-100' }}">
                    Ce Mois
                </a>
                <a href="{{ route('admin.accounting.index', ['period' => 'year']) }}"
                    class="px-3 py-1 rounded-md text-sm {{ $period === 'year' ? 'bg-indigo-100 text-indigo-700 font-bold' : 'text-gray-600 hover:bg-gray-100' }}">
                    Cette Année
                </a>
            </div>

            <form action="{{ route('admin.accounting.index') }}" method="GET" class="flex items-center space-x-2">
                <input type="hidden" name="period" value="custom">
                <input type="date" name="start_date" value="{{ request('start_date', $startDate->format('Y-m-d')) }}"
                    class="border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                <span class="text-gray-400">-</span>
                <input type="date" name="end_date" value="{{ request('end_date', $endDate->format('Y-m-d')) }}"
                    class="border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                <button type="submit" class="bg-gray-800 text-white px-3 py-1 rounded-md text-sm hover:bg-gray-700">
                    Filtrer
                </button>
            </form>
        </div>

        <!-- Cartes Résumé -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Recettes (Cash In) -->
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-gray-500 text-sm font-medium uppercase">Recettes (Cash In)</h3>
                    <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full"><i
                            class="fas fa-arrow-down"></i> Entrées</span>
                </div>
                <p class="text-3xl font-bold text-gray-800">{{ number_format($revenue, 0, ',', ' ') }} <span
                        class="text-lg text-gray-500">FCFA</span></p>
                <p class="text-xs text-gray-500 mt-2">Achats de Packs Crédits</p>
            </div>

            <!-- Dépenses (Cash Out) -->
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-gray-500 text-sm font-medium uppercase">Dépenses (Cash Out)</h3>
                    <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full"><i class="fas fa-arrow-up"></i>
                        Sorties</span>
                </div>
                <p class="text-3xl font-bold text-gray-800">{{ number_format($payouts, 0, ',', ' ') }} <span
                        class="text-lg text-gray-500">FCFA</span></p>
                <p class="text-xs text-gray-500 mt-2">Retraits Mentors validés</p>
            </div>

            <!-- Solde Net -->
            <div
                class="bg-white rounded-lg shadow p-6 border-l-4 {{ $netIncome >= 0 ? 'border-indigo-500' : 'border-orange-500' }}">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-gray-500 text-sm font-medium uppercase">Solde Net (Cash Flow)</h3>
                    <i class="fas fa-wallet text-gray-300"></i>
                </div>
                <p class="text-3xl font-bold {{ $netIncome >= 0 ? 'text-indigo-600' : 'text-orange-600' }}">
                    {{ ($netIncome >= 0 ? '+' : '') . number_format($netIncome, 0, ',', ' ') }} <span
                        class="text-lg text-gray-500">FCFA</span>
                </p>
                <p class="text-xs text-gray-500 mt-2">Recettes - Dépenses</p>
            </div>

            <!-- Service Revenue (Credits) -->
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-gray-500 text-sm font-medium uppercase">Revenus Services (Ciblage)</h3>
                    <span class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded-full"><i
                            class="fas fa-chart-pie"></i> Conso</span>
                </div>
                <p class="text-3xl font-bold text-purple-600">{{ number_format($targetingRevenueCredits, 0, ',', ' ') }}
                    <span class="text-lg text-gray-500">Crédits</span></p>
                <p class="text-xs text-gray-500 mt-2">≈ {{ number_format($estimatedTargetingRevenueFcfa, 0, ',', ' ') }}
                    FCFA (valeur estimée)</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Graphique -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Évolution des Flux Financiers</h3>
                <div class="relative h-80">
                    <canvas id="cashFlowChart"></canvas>
                </div>
            </div>

            <!-- Transactions Récentes -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Dernières Opérations</h3>
                <div class="overflow-y-auto h-80">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b">
                                <th class="pb-2">Date</th>
                                <th class="pb-2">Type</th>
                                <th class="pb-2 text-right">Montant</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($recentTransactions as $transaction)
                                <tr>
                                    <td class="py-3">
                                        <div class="font-medium text-gray-800">{{ $transaction['date']->format('d/m H:i') }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ Str::limit($transaction['user']->name ?? 'Utilisateur inconnu', 15) }}</div>
                                    </td>
                                    <td class="py-3">
                                        @if($transaction['type'] === 'in')
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                Recette
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                Dépense
                                            </span>
                                        @endif
                                    </td>
                                    <td
                                        class="py-3 text-right font-bold {{ $transaction['type'] === 'in' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $transaction['type'] === 'in' ? '+' : '-' }}{{ number_format($transaction['amount'], 0, ',', ' ') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-4 text-center text-gray-500">Aucune transaction sur cette période
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 text-center">
                    <a href="{{ route('admin.accounting.history') }}" class="text-sm text-indigo-600 hover:text-indigo-800">Voir tout l'historique</a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const ctx = document.getElementById('cashFlowChart').getContext('2d');

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: @json($chartData['labels']),
                        datasets: [
                            {
                                label: 'Recettes (Cash In)',
                                data: @json($chartData['revenue']),
                                backgroundColor: '#10B981', // green-500
                                borderRadius: 4,
                            },
                            {
                                label: 'Dépenses (Cash Out)',
                                data: @json($chartData['payouts']),
                                backgroundColor: '#EF4444', // red-500
                                borderRadius: 4,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function (value) {
                                        return value.toLocaleString('fr-FR') + ' FCFA';
                                    }
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            label += context.parsed.y.toLocaleString('fr-FR') + ' FCFA';
                                        }
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endpush
@endsection