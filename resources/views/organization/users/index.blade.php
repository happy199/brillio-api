@extends('layouts.organization')

@section('title', 'Jeunes Parrainés')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Jeunes Parrainés</h1>
            <p class="mt-2 text-sm text-gray-700">
                Liste des {{ $users->total() }} jeunes inscrits via votre organisation.
            </p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('organization.invitations.create') }}"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-organization-600 hover:bg-organization-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-organization-500">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Inviter des jeunes
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route('organization.users.index') }}" method="GET"
            class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
            <!-- Search -->
            <div class="sm:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700">Recherche</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Nom, email..."
                        class="focus:ring-organization-500 focus:border-organization-500 block w-full pl-3 sm:text-sm border-gray-300 rounded-md">
                </div>
            </div>

            <!-- Status -->
            <div class="sm:col-span-2">
                <label for="status" class="block text-sm font-medium text-gray-700">Statut</label>
                <select name="status" id="status"
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-organization-500 focus:border-organization-500 sm:text-sm rounded-md">
                    <option value="">Tous les statuts</option>
                    <option value="active" {{ request('status')==='active' ? 'selected' : '' }}>Actif (30j)</option>
                    <option value="inactive" {{ request('status')==='inactive' ? 'selected' : '' }}>Inactif</option>
                </select>
            </div>

            <!-- Test Status -->
            <div class="sm:col-span-2">
                <label for="test_status" class="block text-sm font-medium text-gray-700">Test Personnalité</label>
                <select name="test_status" id="test_status"
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-organization-500 focus:border-organization-500 sm:text-sm rounded-md">
                    <option value="">Tous</option>
                    <option value="completed" {{ request('test_status')==='completed' ? 'selected' : '' }}>Test complété
                    </option>
                    <option value="pending" {{ request('test_status')==='pending' ? 'selected' : '' }}>Non fait</option>
                </select>
            </div>

            <!-- Submit -->
            <div class="sm:col-span-6 flex justify-end">
                <button type="submit"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-organization-600 hover:bg-organization-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-organization-500">
                    Filtrer
                </button>
                @if(request()->hasAny(['search', 'status', 'test_status']))
                <a href="{{ route('organization.users.index') }}"
                    class="ml-3 inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-organization-500">
                    Réinitialiser
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- User Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($users as $user)
        <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        @if($user->avatar_url)
                        <img class="h-12 w-12 rounded-full object-cover" src="{{ $user->avatar_url }}"
                            alt="{{ $user->name }}">
                        @else
                        <div
                            class="h-12 w-12 rounded-full bg-organization-100 flex items-center justify-center text-organization-600 font-bold text-lg">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        @endif
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900 truncate">{{ $user->name }}</h3>
                        <p class="text-sm text-gray-500 truncate">{{ $user->email }}</p>
                    </div>
                </div>

                <div class="mt-4 border-t border-gray-100 pt-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Statut</span>
                        @if($user->last_login_at && $user->last_login_at->gte(now()->subDays(30)))
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            Actif
                        </span>
                        @else
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                            Inactif
                        </span>
                        @endif
                    </div>
                    <div class="flex justify-between text-sm mt-2">
                        <span class="text-gray-500">Personnalité</span>
                        @if($user->personalityTest && $user->personalityTest->completed_at)
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                            {{ $user->personalityTest->personality_type }}
                        </span>
                        @else
                        <span class="text-gray-400 italic">Non fait</span>
                        @endif
                    </div>
                    <div class="flex justify-between text-sm mt-2">
                        <span class="text-gray-500">Inscrit le</span>
                        <span class="text-gray-900">{{ $user->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-3 border-t border-gray-100">
                <a href="{{ route('organization.users.show', $user) }}"
                    class="text-sm font-medium text-organization-600 hover:text-organization-500 flex items-center justify-center">
                    Voir le profil détaillé
                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12 bg-white rounded-lg shadow">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun utilisateur trouvé</h3>
            <p class="mt-1 text-sm text-gray-500">Commencez par inviter des jeunes ou ajustez vos filtres.</p>
            <div class="mt-6">
                <a href="{{ route('organization.invitations.create') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-organization-600 hover:bg-organization-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-organization-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Inviter des jeunes
                </a>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $users->links() }}
    </div>
</div>
@endsection