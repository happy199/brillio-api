@extends('layouts.admin')

@section('title', 'Historique Complet - Comptabilité')
@section('header', 'Historique des Transactions')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-medium text-gray-900">Historique global des mouvements financiers</h2>
        <a href="{{ route('admin.accounting.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">
            <i class="fas fa-arrow-left mr-1"></i> Retour au Dashboard
        </a>
    </div>

    <!-- Filtres & Téléchargement ZIP -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-4 rounded-lg shadow">
        <form action="{{ route('admin.accounting.history') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center space-x-2">
                <label for="start_date" class="text-xs font-medium text-gray-500 uppercase">Du</label>
                <input type="date" id="start_date" name="start_date" value="{{ $startDate ? $startDate->format('Y-m-d') : '' }}"
                    class="border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
            </div>
            <div class="flex items-center space-x-2">
                <label for="end_date" class="text-xs font-medium text-gray-500 uppercase">Au</label>
                <input type="date" id="end_date" name="end_date" value="{{ $endDate ? $endDate->format('Y-m-d') : '' }}"
                    class="border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
            </div>
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-filter mr-2 text-gray-400"></i> Filtrer
            </button>
            @if($startDate || $endDate)
                <a href="{{ route('admin.accounting.history') }}" class="text-sm text-gray-500 hover:text-gray-700 underline">
                    Réinitialiser
                </a>
            @endif
        </form>

        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.accounting.download-invoices-zip', request()->all()) }}"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-file-archive mr-2"></i> Télécharger toutes les factures (ZIP)
            </a>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Référence
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Utilisateur
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Type
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Libellé
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Montant
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($transactions as $transaction)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $transaction['date']->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                            {{ $transaction['reference'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="ml-0">
                                    <div class="text-sm font-medium text-gray-900">
                                        @if(($transaction['user']->user_type ?? '') === 'organization' &&
                                        $transaction['user']->organization)
                                        {{ $transaction['user']->organization->name }}
                                        @else
                                        {{ $transaction['user']->name ?? 'Utilisateur inconnu' }}
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $transaction['user']->email ?? '' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($transaction['type'] === 'in')
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Recette
                            </span>
                            @else
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                Dépense
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $transaction['label'] }}
                        </td>
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold {{ $transaction['type'] === 'in' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $transaction['type'] === 'in' ? '+' : '-' }}{{ number_format($transaction['amount'], 0,
                            ',', ' ') }}
                            FCFA
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                            @if($transaction['type'] === 'in')
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('admin.accounting.view-invoice', $transaction['id']) }}" target="_blank"
                                    class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
                                    title="Voir la facture (PDF)">
                                    <i class="fas fa-eye mr-1"></i> Voir
                                </a>
                                <a href="{{ route('admin.accounting.download-invoice', $transaction['id']) }}"
                                    class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
                                    title="Télécharger la facture (PDF)">
                                    <i class="fas fa-download mr-1"></i> Télécharger
                                </a>
                                <form action="{{ route('admin.accounting.resend-invoice', $transaction['id']) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center px-2.5 py-1.5 border border-indigo-600 text-xs font-medium rounded text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
                                        title="Renvoyer la facture par email">
                                        <i class="fas fa-paper-plane mr-1"></i> Renvoyer
                                    </button>
                                </form>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            Aucune transaction trouvée.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection