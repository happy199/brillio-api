@extends('layouts.admin')

@section('title', 'Commerciaux')
@section('header', 'Annuaire des Commerciaux')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h2 class="text-xl font-bold text-gray-800">Liste des commerciaux</h2>
        <p class="text-sm text-gray-500">Gérez les accès commerciaux de la plateforme.</p>
    </div>
    @if(auth()->user()->isAdmin())
    <div x-data="{ open: false }">
        <button @click="open = true" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            <i class="fas fa-plus mr-2"></i> Ajouter un commercial
        </button>

        <!-- Modal Ajout -->
        <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="open = false">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form action="{{ route('admin.commercials.store') }}" method="POST">
                        @csrf
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Ajouter un commercial</h3>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Nom</label>
                                <input type="text" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" name="email" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Mot de passe</label>
                                <input type="password" name="password" required minlength="8" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                Créer
                            </button>
                            <button type="button" @click="open = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Annuler
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commercial</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date création</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($commercials as $commercial)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <img class="h-10 w-10 rounded-full object-cover" src="{{ $commercial->profile_photo_url }}" alt="">
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">{{ $commercial->name }}</div>
                            <div class="text-sm text-gray-500">{{ $commercial->email }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $commercial->created_at->format('d/m/Y') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    @if(auth()->user()->isAdmin())
                    <div class="flex items-center justify-end space-x-2">
                        <!-- Reset Password -->
                        <form action="{{ route('admin.commercials.reset-password', $commercial) }}" method="POST" class="inline"
                            onsubmit="return confirm('Réinitialiser le mot de passe et envoyer les nouveaux accès à ce commercial ?')">
                            @csrf
                            <button type="submit"
                                class="text-indigo-600 hover:text-indigo-900 px-3 py-1 rounded bg-indigo-50"
                                title="Réinitialiser le mot de passe">
                                <i class="fas fa-key mr-1"></i> Reset
                            </button>
                        </form>

                        <!-- Copy Credentials (AlpineJS) -->
                        <button type="button" 
                            @click="navigator.clipboard.writeText('{{ $commercial->email }}').then(() => alert('Email copié !'))"
                            class="text-gray-600 hover:text-gray-900 px-3 py-1 rounded bg-gray-100"
                            title="Copier l'email">
                            <i class="fas fa-copy mr-1"></i> Email
                        </button>

                        <!-- Revoke -->
                        <form action="{{ route('admin.commercials.destroy', $commercial) }}" method="POST" class="inline-block" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce commercial ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900 px-3 py-1 rounded bg-red-50">
                                <i class="fas fa-user-minus mr-1"></i> Révoquer
                            </button>
                        </form>
                    </div>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="p-4 border-t">
        {{ $commercials->links() }}
    </div>
</div>
@endsection