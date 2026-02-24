@extends('layouts.organization')

@section('title', 'Gestion de l\'équipe')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Équipe</h1>
            <p class="mt-1 text-sm text-gray-600">Gérez les membres qui ont accès à votre espace organisation.</p>
        </div>
        <a href="{{ route('organization.team.create') }}"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-organization-600 hover:bg-organization-700 transition-colors">
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Ajouter un membre
        </a>
    </div>

    @if(session('new_user_data'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
        <div class="flex items-center mb-4">
            <div class="flex-shrink-0 bg-green-100 rounded-full p-2">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="ml-3 text-lg font-medium text-green-900">Nouveau membre créé !</h3>
        </div>
        <p class="text-sm text-green-800 mb-4">
            Veuillez copier et partager ces accès de manière sécurisée. Le mot de passe ne sera plus affiché ensuite.
        </p>
        <div class="bg-white border border-green-100 rounded-md p-4 space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-500">Nom :</span>
                <span class="text-sm font-bold text-gray-900">{{ session('new_user_data')['name'] }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-500">Email :</span>
                <span class="text-sm font-mono text-gray-900">{{ session('new_user_data')['email'] }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-500">Mot de passe :</span>
                <span class="text-sm font-mono font-bold text-organization-600 bg-organization-50 px-2 py-1 rounded">{{
                    session('new_user_data')['password'] }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-500">Rôle :</span>
                <span class="text-sm font-medium text-gray-900">{{ session('new_user_data')['role'] }}</span>
            </div>
        </div>
        <div class="mt-4 flex flex-wrap gap-3">
            <button onclick="copyAccess()"
                class="inline-flex items-center px-3 py-2 border border-green-300 shadow-sm text-sm font-medium rounded-md text-green-700 bg-white hover:bg-green-50">
                Copier les accès
            </button>
            <a href="https://wa.me/?text={{ urlencode('Voici vos accès Brillio pour '.$organization->name.' : \nEmail : '.session('new_user_data')['email'].'\nMot de passe : '.session('new_user_data')['password'].'\nLien : '.route('organization.login')) }}"
                target="_blank"
                class="inline-flex items-center px-3 py-2 border border-green-300 shadow-sm text-sm font-medium rounded-md text-green-700 bg-white hover:bg-green-50">
                Partager sur WhatsApp
            </a>
        </div>
    </div>
    <script>
        function copyAccess() {
            const text = "Accès Brillio - {{ $organization->name }}\nEmail : {{ session('new_user_data')['email'] }}\nMot de passe : {{ session('new_user_data')['password'] }}\nLien : {{ route('organization.login') }}";
            navigator.clipboard.writeText(text).then(() => {
                window.dispatchEvent(new CustomEvent('copy-notification', {
                detail: { message: 'Accès copiés dans le presse-papiers !', type: 'success' }
     }));
            });
        }
    </script>
    @endif

    <!-- Members List -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        @if($members->count() > 0)
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Membre
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rôle</th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dernière
                        connexion</th>
                    <th scope="col" class="relative px-6 py-3">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($members as $member)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <img class="h-10 w-10 rounded-full" src="{{ $member->avatar_url }}" alt="">
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $member->name }}</div>
                                <div class="text-sm text-gray-500">{{ $member->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        @if($member->pivot->role === 'admin')
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            Administrateur
                        </span>
                        @else
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            Observateur
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $member->last_login_at ? $member->last_login_at->format('d/m/Y H:i') : 'Jamais' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        @if($member->id !== auth()->id())
                        <form action="{{ route('organization.team.destroy', $member) }}" method="POST"
                            onsubmit="return confirm('Êtes-vous sûr de vouloir retirer ce membre ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">Retirer</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun membre d'équipe</h3>
            <p class="mt-1 text-sm text-gray-500">Commencez par ajouter des administrateurs ou des observateurs.</p>
            <div class="mt-6">
                <a href="{{ route('organization.team.create') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-organization-600 hover:bg-organization-700">
                    Ajouter un membre
                </a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection