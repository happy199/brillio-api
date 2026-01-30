@extends('layouts.admin')

@section('title', 'Retraits Mentors')
@section('header', 'Retraits des Mentors')

@section('content')
    <!-- Filtres -->
    <div class="bg-white rounded-xl shadow p-6 mb-6">
        <form method="GET" action="{{ route('admin.payouts.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                <select name="status" id="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Tous les statuts</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>En cours</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Complété</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Échoué</option>
                </select>
            </div>

            <div class="flex-1 min-w-[200px]">
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Date début</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex-1 min-w-[200px]">
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Date fin</label>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex-1 min-w-[200px]">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Rechercher un mentor</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                       placeholder="Nom ou email..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex items-end">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Filtrer
                </button>
                @if(request()->hasAny(['status', 'start_date', 'end_date', 'search']))
                    <a href="{{ route('admin.payouts.index') }}" class="ml-2 px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Réinitialiser
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tableau des payouts -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mentor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Frais</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Net</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Méthode</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Téléphone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($payouts as $payout)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $payout->mentorProfile->user->name }}</div>
                            <div class="text-sm text-gray-500">{{ $payout->mentorProfile->user->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($payout->amount, 0, ',', ' ') }} FCFA
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ number_format($payout->fee, 0, ',', ' ') }} FCFA
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                            {{ number_format($payout->net_amount, 0, ',', ' ') }} FCFA
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ strtoupper($payout->payment_method) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $payout->phone_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @switch($payout->status)
                                @case('pending')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        En attente
                                    </span>
                                    @break
                                @case('processing')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        En cours
                                    </span>
                                    @break
                                @case('completed')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Complété
                                    </span>
                                    @break
                                @case('failed')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        Échoué
                                    </span>
                                    @break
                            @endswitch
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $payout->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('admin.payouts.show', $payout) }}" class="text-blue-600 hover:text-blue-800">
                                Détails
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-lg font-medium">Aucun retrait trouvé</p>
                                <p class="text-sm mt-1">Les demandes de retrait des mentors apparaîtront ici</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        @if($payouts->hasPages())
            <div class="border-t border-gray-200 px-6 py-4">
                {{ $payouts->links() }}
            </div>
        @endif
    </div>
@endsection
