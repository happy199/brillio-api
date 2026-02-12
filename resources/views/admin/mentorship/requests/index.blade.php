@extends('layouts.admin')

@section('title', 'Activités de Mentorat')
@section('header', 'Activités de Mentorat (Mentorships)')

@section('content')
    <div class="space-y-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-sm font-medium text-gray-500">Total Demandes</h3>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-sm font-medium text-yellow-600">En Attente</h3>
                <p class="text-3xl font-bold text-yellow-700 mt-2">{{ $stats['pending'] }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-sm font-medium text-green-600">Actifs (Acceptés)</h3>
                <p class="text-3xl font-bold text-green-700 mt-2">{{ $stats['accepted'] }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-sm font-medium text-red-600">Rejetés</h3>
                <p class="text-3xl font-bold text-red-700 mt-2">{{ $stats['rejected'] }}</p>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
            <form action="{{ route('admin.mentorship.requests') }}" method="GET"
                class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <!-- Global Search -->
                <div class="col-span-1 md:col-span-4 mb-2">
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Filtres de Recherche</h4>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Recherche Globale</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        placeholder="Nom...">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">D'un Mentor (Nom)</label>
                    <input type="text" name="mentor_name" value="{{ request('mentor_name') }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        placeholder="Nom du mentor...">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">D'un Jeune (Nom)</label>
                    <input type="text" name="mentee_name" value="{{ request('mentee_name') }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        placeholder="Nom du jeune...">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Statut</label>
                    <select name="status"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Tous</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Accepté</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejeté</option>
                    </select>
                </div>

                <div class="md:col-span-4 flex justify-end gap-2 mt-2">
                    <a href="{{ route('admin.mentorship.requests') }}"
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
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jeune
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mentor
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date
                            Demande</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut
                        </th>
                        <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($requests as $request)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full object-cover"
                                                    src="{{ $request->mentee->avatar_url }}" alt="">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $request->mentee->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $request->mentee->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full object-cover"
                                                    src="{{ $request->mentor->avatar_url }}" alt="">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $request->mentor->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $request->mentor->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $request->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="{{ $request->message }}">
                                        {{ $request->message }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $request->status === 'accepted' ? 'bg-green-100 text-green-800' :
                        ($request->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                            {{ ucfirst($request->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('admin.mentorship.requests.show', $request) }}"
                                            class="text-indigo-600 hover:text-indigo-900 font-bold">Détails</a>
                                    </td>
                                </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Aucune demande de mentorat trouvée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $requests->links() }}
            </div>
        </div>
    </div>
@endsection