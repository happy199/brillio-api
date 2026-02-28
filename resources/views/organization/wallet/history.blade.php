@extends('layouts.organization')

@section('title', 'Historique des Transactions')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Historique des <span class="text-pink-600">Transactions</span>
            </h1>
            <p class="text-gray-500">Consultez et exportez l'ensemble de vos mouvements de crédits.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('organization.wallet.export-csv', request()->all()) }}"
                class="inline-flex items-center justify-center min-w-[80px] px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                <i class="fas fa-file-csv mr-2 text-green-600"></i> CSV
            </a>
            <a href="{{ route('organization.wallet.export-pdf', request()->all()) }}"
                class="inline-flex items-center justify-center min-w-[80px] px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                <i class="fas fa-file-pdf mr-2 text-red-600"></i> PDF
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
        <form action="{{ route('organization.wallet.history') }}" method="GET"
            class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Du</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Au</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm">
            </div>
            <div class="flex gap-2">
                <button type="submit"
                    class="flex-1 bg-pink-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-pink-700">
                    Filtrer
                </button>
                @if(request()->anyFilled(['date_from', 'date_to']))
                <a href="{{ route('organization.wallet.history') }}"
                    class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 underline">Effacer</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white shadow overflow-hidden sm:rounded-xl border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Description</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Crédits</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Valeur (FCFA)</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($transactions as $t)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $t->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                            $badgeClass = match(strtolower($t->type)) {
                            'recharge', 'purchase', 'subscription' => 'bg-green-100 text-green-800',
                            'expense' => 'bg-orange-100 text-orange-800',
                            'distribution' => 'bg-blue-100 text-blue-800',
                            default => $t->amount > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                            };
                            $typeLabel = match(strtolower($t->type)) {
                            'recharge', 'purchase' => 'Achat',
                            'expense' => 'Ressource',
                            'distribution' => 'Distribution',
                            'subscription' => 'Abonnement',
                            default => ucfirst($t->type)
                            };
                            @endphp
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                {{ $typeLabel }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $t->description }}
                        </td>
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium {{ $t->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $t->amount > 0 ? '+' : '' }}{{ number_format($t->amount) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 font-bold">
                            {{ number_format($t->amount * $creditPrice) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-receipt text-gray-200 text-4xl mb-4"></i>
                                <p>Aucune transaction trouvée.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>

    <!-- Info Box -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex gap-4">
        <i class="fas fa-info-circle text-blue-500 text-xl mt-0.5"></i>
        <div class="text-sm text-blue-800">
            <p class="font-bold mb-1">Calcul de la valeur financière</p>
            <p>La valeur en FCFA est calculée sur la base du prix actuel du crédit pour organisation : <strong>{{
                    number_format($creditPrice) }} FCFA / crédit</strong>. Cela vous permet de justifier aussi bien vos
                achats de crédits que vos redistributions.</p>
        </div>
    </div>
</div>
@endsection