@extends('layouts.admin')

@section('header')
<div class="flex items-center">
    <a href="{{ route('admin.organizations.index') }}" class="text-gray-500 hover:text-gray-700 mr-4">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
    <h2 class="text-xl font-semibold text-gray-800">Détails de l'organisation</h2>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Informations Générales -->
    <div class="md:col-span-1">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex flex-col items-center">
                    @if($organization->logo_url)
                    <img class="h-32 w-32 rounded-full object-cover border-4 border-indigo-50 mb-4"
                        src="{{ $organization->logo_url }}" alt="{{ $organization->name }}">
                    @else
                    <div
                        class="h-32 w-32 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-4xl border-4 border-indigo-50 mb-4">
                        {{ $organization->initials }}
                    </div>
                    @endif
                    <h3 class="text-lg font-bold text-gray-900">{{ $organization->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $organization->sector ?? 'Secteur non défini' }}</p>

                    <div class="mt-4 flex gap-2">
                        @if($organization->status === 'active')
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            Active
                        </span>
                        @else
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                            Inactive
                        </span>
                        @endif
                    </div>

                    <div class="w-full mt-6 border-t pt-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-500">Email Contact</span>
                            <span class="text-sm font-medium">{{ $organization->contact_email }}</span>
                        </div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-500">Téléphone</span>
                            <span class="text-sm font-medium">{{ $organization->phone ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-500">Site Web</span>
                            @if($organization->website)
                            <a href="{{ $organization->website }}" target="_blank"
                                class="text-sm font-medium text-indigo-600 hover:text-indigo-800 truncate max-w-[150px]">
                                {{ parse_url($organization->website, PHP_URL_HOST) }}
                            </a>
                            @else
                            <span class="text-sm font-medium">-</span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Slug</span>
                            <span class="text-sm font-mono bg-gray-100 px-2 rounded">{{ $organization->slug }}</span>
                        </div>
                    </div>

                    <div class="w-full mt-6 pt-4 border-t">
                        <a href="{{ route('admin.organizations.edit', $organization) }}"
                            class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                            Modifier
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats rapides -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h4 class="font-semibold text-gray-700 mb-4">Statistiques</h4>
                <div class="grid grid-cols-2 gap-4 text-center">
                    <div class="bg-gray-50 p-3 rounded">
                        <div class="text-2xl font-bold text-indigo-600">{{ $organization->sponsoredUsers->count() }}
                        </div>
                        <div class="text-xs text-gray-500">Jeunes parrainés</div>
                    </div>
                    <div class="bg-gray-50 p-3 rounded">
                        <div class="text-2xl font-bold text-green-600">{{ $organization->active_users_count }}</div>
                        <div class="text-xs text-gray-500">Actifs (30j)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des Jeunes Parrainés -->
    <div class="md:col-span-2">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg h-full">
            <div class="p-6 bg-white border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Jeunes Parrainés</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nom</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Email</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Inscription</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Dernière connexion</th>
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($organization->sponsoredUsers as $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            @if($user->profile_photo_path)
                                            <img class="h-8 w-8 rounded-full object-cover"
                                                src="{{ Storage::url($user->profile_photo_path) }}"
                                                alt="{{ $user->name }}">
                                            @else
                                            <div
                                                class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs">
                                                {{ substr($user->name, 0, 2) }}
                                            </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $user->email }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $user->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $user->last_login_at ?
                                    \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() : 'Jamais' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('admin.users.show', $user) }}"
                                        class="text-indigo-600 hover:text-indigo-900">
                                        Voir
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                    Aucun jeune parrainé pour le moment.
                                </td>
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