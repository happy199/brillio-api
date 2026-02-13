@extends('layouts.admin')

@section('title', 'Utilisateurs')
@section('header', 'Gestion des utilisateurs')

@section('content')
<!-- Tabs Status -->
<div class="mb-6 flex space-x-1 p-1 bg-gray-100 rounded-xl w-fit">
    <a href="{{ route('admin.users.index') }}"
        class="px-4 py-2 rounded-lg text-sm font-medium transition {{ !request('archived') ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">
        Utilisateurs Actifs
    </a>
    <a href="{{ route('admin.users.index', ['archived' => 1]) }}"
        class="px-4 py-2 rounded-lg text-sm font-medium transition {{ request('archived') ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">
        Comptes Archivés
    </a>
</div>

<!-- Filtres -->
<div class="bg-white rounded-xl shadow-sm p-4 mb-6">
    <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
        @if(request('archived'))
        <input type="hidden" name="archived" value="1">
        @endif

        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom ou email..."
                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
        </div>

        <div class="w-40">
            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
            <select name="type" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                <option value="">Tous</option>
                <option value="jeune" {{ request('type')==='jeune' ? 'selected' : '' }}>Jeunes</option>
                <option value="mentor" {{ request('type')==='mentor' ? 'selected' : '' }}>Mentors</option>
            </select>
        </div>

        <div class="w-40">
            <label class="block text-sm font-medium text-gray-700 mb-1">Pays</label>
            <select name="country" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                <option value="">Tous</option>
                @foreach($countries as $country)
                <option value="{{ $country }}" {{ request('country')===$country ? 'selected' : '' }}>
                    {{ $country }}
                </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            Filtrer
        </button>

        @if(request()->hasAny(['search', 'type', 'country']))
        <a href="{{ route('admin.users.index', ['archived' => request('archived')]) }}"
            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
            Réinitialiser
        </a>
        @endif
    </form>
</div>

<!-- Table -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilisateur
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pays</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ request('archived') ? 'Archivé le' : 'Actions' }}
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($users as $user)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                            @if($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" alt="" class="w-10 h-10 rounded-full object-cover"
                                onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\'text-indigo-600 font-semibold\'>{{ substr($user->name, 0, 1) }}</span>';">
                            @else
                            <span class="text-indigo-600 font-semibold">{{ substr($user->name, 0, 1) }}</span>
                            @endif
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $user->name }}
                                @if($user->is_admin)
                                <span class="ml-1 px-1.5 py-0.5 text-xs bg-red-100 text-red-700 rounded">Admin</span>
                                @endif
                            </div>
                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                            @if($user->is_archived && $user->archived_reason)
                            <div class="text-xs text-red-500 mt-1 italic">"{{ Str::limit($user->archived_reason, 30) }}"
                            </div>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span
                        class="px-2 py-1 text-xs rounded-full {{ $user->user_type === 'mentor' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ ucfirst($user->user_type) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $user->country ?? '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    @if(request('archived'))
                    {{ $user->archived_at ? $user->archived_at->format('d/m/Y H:i') : '-' }}
                    @else
                    {{-- Placeholder or other info --}}
                    @if($user->personalityTest && $user->personalityTest->completed_at)
                    <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-full">
                        {{ $user->personalityTest->personality_type }}
                    </span>
                    @else
                    <span class="text-gray-400 text-sm">Non complété</span>
                    @endif
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                    <a href="{{ route('admin.users.show', $user) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                        Voir
                    </a>

                    @if($user->is_archived)
                    <form action="{{ route('admin.users.reactivate', $user) }}" method="POST" class="inline"
                        onsubmit="return confirm('Réactiver ce compte ?')">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="text-green-600 hover:text-green-900 mr-3 font-medium">
                            Réactiver
                        </button>
                    </form>

                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline"
                        onsubmit="return confirm('ATTENTION : Supprimer définitivement cet utilisateur et TOUTES ses données ? Cette action est irréversible.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-700 hover:text-red-900 font-bold">
                            Supprimer (Définitif)
                        </button>
                    </form>

                    @elseif($user->id !== auth()->id())
                    <form action="{{ route('admin.users.toggle-admin', $user) }}" method="POST" class="inline">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="text-yellow-600 hover:text-yellow-900 mr-3">
                            {{ $user->is_admin ? 'Retirer admin' : 'Rendre admin' }}
                        </button>
                    </form>
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline"
                        onsubmit="return confirm('Supprimer cet utilisateur ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">
                            Supprimer
                        </button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                    Aucun utilisateur trouvé
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($users->hasPages())
    <div class="px-6 py-4 border-t">
        {{ $users->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection