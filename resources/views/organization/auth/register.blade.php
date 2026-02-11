@extends('layouts.guest')

@section('title', 'Inscription Organisation')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full space-y-8">
        <div class="text-center">
            <h2 class="text-3xl font-extrabold text-gray-900">
                Rejoignez-nous en tant qu'organisation partenaire
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Invitez et suivez les jeunes que vous accompagnez
            </p>
        </div>

        <form class="mt-8 space-y-6 bg-white p-8 rounded-xl shadow-lg" method="POST"
            action="{{ route('organization.register.submit') }}">
            @csrf

            <!-- Organization Info -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900">Informations de l'organisation</h3>

                <div>
                    <label for="organization_name" class="block text-sm font-medium text-gray-700">Nom de l'organisation
                        *</label>
                    <input id="organization_name" name="organization_name" type="text" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
                        value="{{ old('organization_name') }}">
                    @error('organization_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="sector" class="block text-sm font-medium text-gray-700">Secteur</label>
                        <select id="sector" name="sector"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500">
                            <option value="">Sélectionner...</option>
                            <option value="tech">Technologie</option>
                            <option value="education">Éducation</option>
                            <option value="ong">ONG</option>
                            <option value="gouvernement">Gouvernement</option>
                            <option value="entreprise">Entreprise</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Téléphone</label>
                        <input id="phone" name="phone" type="tel"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
                            value="{{ old('phone') }}">
                    </div>
                </div>

                <div>
                    <label for="website" class="block text-sm font-medium text-gray-700">Site web</label>
                    <input id="website" name="website" type="url"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
                        value="{{ old('website') }}" placeholder="https://example.com">
                    @error('website')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="description" name="description" rows="3"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500">{{ old('description') }}</textarea>
                </div>
            </div>

            <!-- Account Info -->
            <div class="space-y-4 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Informations du compte</h3>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nom complet *</label>
                    <input id="name" name="name" type="text" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
                        value="{{ old('name') }}">
                    @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email professionnel *</label>
                    <input id="email" name="email" type="email" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
                        value="{{ old('email') }}">
                    @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe *</label>
                        <input id="password" name="password" type="password" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500">
                        @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmer mot
                            de passe *</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>
            </div>

            <div>
                <button type="submit"
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                    Créer mon compte organisation
                </button>
            </div>

            <div class="text-center">
                <p class="text-sm text-gray-600">
                    Vous avez déjà un compte ?
                    <a href="{{ route('organization.login') }}" class="font-medium text-green-600 hover:text-green-500">
                        Se connecter
                    </a>
                </p>
            </div>
        </form>
    </div>
</div>
@endsection