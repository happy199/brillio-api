@extends('layouts.admin')

@section('title', 'Séances de Mentorat')
@section('header', 'Séances de Mentorat')

@section('content')
    <div class="space-y-6">

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-sm font-medium text-gray-500">Total Séances</h3>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-sm font-medium text-blue-600">À Venir</h3>
                <p class="text-3xl font-bold text-blue-700 mt-2">{{ $stats['upcoming'] }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-sm font-medium text-green-600">Terminées</h3>
                <p class="text-3xl font-bold text-green-700 mt-2">{{ $stats['completed'] }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-sm font-medium text-red-600">Annulées</h3>
                <p class="text-3xl font-bold text-red-700 mt-2">{{ $stats['cancelled'] }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
            <form action="{{ route('admin.mentorship.sessions') }}" method="GET"
                class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                <!-- Global Search -->
                <div class="col-span-1 md:col-span-5 mb-2">
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Filtres de Recherche</h4>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Recherche Globale</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        placeholder="Titre, nom...">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Mentor (Nom)</label>
                    <input type="text" name="mentor_name" value="{{ request('mentor_name') }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        placeholder="Nom du mentor...">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Menté (Nom)</label>
                    <input type="text" name="mentee_name" value="{{ request('mentee_name') }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        placeholder="Nom du jeune...">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Statut</label>
                    <select name="status"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Tous les statuts</option>
                        <option value="proposed" {{ request('status') == 'proposed' ? 'selected' : '' }}>Proposée</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Terminée</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulée</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Paiement</label>
                    <select name="is_paid"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Tous</option>
                        <option value="0" {{ request('is_paid') === '0' ? 'selected' : '' }}>Gratuit</option>
                        <option value="1" {{ request('is_paid') === '1' ? 'selected' : '' }}>Payant</option>
                    </select>
                </div>

                <div class="md:col-span-5 flex justify-end gap-2 mt-2">
                    <a href="{{ route('admin.mentorship.sessions') }}"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                        Réinitialiser
                    </a>
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Appliquer les filtres
                    </button>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Titre /
                            Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Participants</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Compte
                            Rendu</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($sessions as $session)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-900">{{ $session->title }}</div>
                                <div class="text-xs text-gray-500">{{ $session->scheduled_at->format('d/m/Y H:i') }}
                                    ({{ $session->duration_minutes }} min)</div>
                                <span
                                    class="inline-flex mt-1 items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    Source: {{ ucfirst($session->created_by) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center text-sm">
                                        <span class="w-16 text-xs text-gray-500">Mentor:</span>
                                        <span class="font-medium text-indigo-600">{{ $session->mentor->name }}</span>
                                    </div>
                                    <div class="flex items-center text-sm">
                                        <span class="w-16 text-xs text-gray-500">Menté(s):</span>
                                        <span>{{ $session->mentees->pluck('name')->join(', ') }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($session->is_paid)
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                        Payant ({{ number_format($session->price, 0, ',', ' ') }} FCFA)
                                    </span>
                                @else
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Gratuit
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($session->status === 'cancelled')
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Annulée</span>
                                @elseif($session->status === 'completed')
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Terminée</span>
                                @else
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($session->status) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($session->report_content)
                                    <span class="text-green-600 font-bold">Oui</span>
                                @else
                                    <span class="text-gray-400">Non</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.mentorship.sessions.show', $session) }}"
                                    class="text-indigo-600 hover:text-indigo-900 font-bold">Voir</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Aucune séance trouvée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $sessions->links() }}
            </div>
        </div>
    </div>
@endsection