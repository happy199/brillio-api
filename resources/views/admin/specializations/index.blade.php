@extends('layouts.admin')

@section('title', 'Gestion des Domaines d\'Expertise')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Domaines d'Expertise</h1>
            <div class="flex gap-3">
                <a href="{{ route('admin.specializations.moderate') }}"
                    class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg">
                    Modérer les suggestions
                </a>
                <a href="{{ route('admin.specializations.create') }}"
                    class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg">
                    + Ajouter un domaine
                </a>
            </div>
        </div>

        <!-- Filtres -->
        <form method="GET" class="bg-white p-4 rounded-lg shadow mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom du domaine..."
                        class="w-full border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                    <select name="status" class="w-full border-gray-300 rounded-lg">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Tous</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archivé</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg w-full">
                        Filtrer
                    </button>
                </div>
            </div>
        </form>

        <!-- Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('warning'))
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
                {{ session('warning') }}
            </div>
        @endif

        <!-- Tableau -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mentors</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Types MBTI</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($specializations as $spec)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $spec->name }}</div>
                                <div class="text-sm text-gray-500">{{ $spec->slug }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 max-w-xs truncate">{{ $spec->description ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($spec->status === 'active')
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Actif</span>
                                @elseif($spec->status === 'pending')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">En
                                        attente</span>
                                @else
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Archivé</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $spec->mentor_profiles_count }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($spec->mbtiTypes as $mbti)
                                        <span
                                            class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">{{ $mbti->mbti_type_code }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.specializations.edit', $spec) }}"
                                    class="text-orange-600 hover:text-orange-900 mr-3">Éditer</a>
                                <form action="{{ route('admin.specializations.destroy', $spec) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Êtes-vous sûr ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        {{ $spec->mentor_profiles_count > 0 ? 'Archiver' : 'Supprimer' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                Aucun domaine d'expertise trouvé
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $specializations->links() }}
        </div>
    </div>
@endsection