@extends('layouts.organization')

@section('title', 'Ajouter un membre')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="md:flex md:items-center md:justify-between mb-6">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Ajouter un membre d'équipe
            </h2>
        </div>
    </div>

    <div class="bg-white shadow sm:rounded-lg">
        <form method="POST" action="{{ route('organization.team.store') }}" class="space-y-6 p-6">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nom complet</label>
                <div class="mt-1">
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="shadow-sm focus:ring-organization-500 focus:border-organization-500 block w-full sm:text-sm border-gray-300 rounded-md px-4 py-3">
                </div>
                @error('name')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Adresse email</label>
                <div class="mt-1">
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                        class="shadow-sm focus:ring-organization-500 focus:border-organization-500 block w-full sm:text-sm border-gray-300 rounded-md px-4 py-3">
                </div>
                <p class="mt-2 text-xs text-gray-500">Un mot de passe aléatoire sera généré et devra être communiqué au
                    membre.</p>
                @error('email')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="role" class="block text-sm font-medium text-gray-700">Rôle</label>
                <div class="mt-1">
                    <select id="role" name="role" required
                        class="shadow-sm focus:ring-organization-500 focus:border-organization-500 block w-full sm:text-sm border-gray-300 rounded-md px-4 py-3">
                        <option value="admin">Administrateur (Accès complet)</option>
                        <option value="viewer">Observateur (Lecture seule)</option>
                    </select>
                </div>
                @error('role')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                <a href="{{ route('organization.team.index') }}"
                    class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                    Annuler
                </a>
                <button type="submit"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-organization-600 hover:bg-organization-700 focus:outline-none">
                    Créer le compte
                </button>
            </div>
        </form>
    </div>
</div>
@endsection