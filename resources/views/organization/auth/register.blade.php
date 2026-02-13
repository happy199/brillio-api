@extends('layouts.auth')

@section('title', 'Inscription Organisation')
@section('heading', 'Devener Partenaire')
@section('subheading', 'Rejoignez Brillio pour recruter et former les talents de demain')
@section('card_width', 'sm:max-w-2xl')

@section('content')
<form class="space-y-6" method="POST" action="{{ route('organization.register.submit') }}">
    @csrf

    <!-- Organization Info -->
    <div class="space-y-4">
        <h3 class="text-lg font-bold text-gray-900 border-b pb-2 mb-4">Informations de l'organisation</h3>

        <div>
            <label for="organization_name" class="block text-sm font-medium text-gray-700">Nom de l'organisation
                *</label>
            <input id="organization_name" name="organization_name" type="text" required
                class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-transparent transition-all"
                value="{{ old('organization_name') }}" placeholder="Ex: Tech Solutions Africa">
            @error('organization_name')
            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="sector" class="block text-sm font-medium text-gray-700">Secteur</label>
                <select id="sector" name="sector"
                    class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-transparent transition-all bg-white">
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
                    class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-transparent transition-all"
                    value="{{ old('phone') }}" placeholder="+225 ...">
            </div>
        </div>

        <div>
            <label for="website" class="block text-sm font-medium text-gray-700">Site web</label>
            <input id="website" name="website" type="url"
                class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-transparent transition-all"
                value="{{ old('website') }}" placeholder="https://example.com">
            @error('website')
            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea id="description" name="description" rows="3"
                class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-transparent transition-all"
                placeholder="Présentez brièvement votre structure...">{{ old('description') }}</textarea>
        </div>
    </div>

    <!-- Account Info -->
    <div class="space-y-4 pt-6">
        <h3 class="text-lg font-bold text-gray-900 border-b pb-2 mb-4">Informations du compte administrateur</h3>

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Nom complet *</label>
            <input id="name" name="name" type="text" required
                class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-transparent transition-all"
                value="{{ old('name') }}" placeholder="Jean Kouassi">
            @error('name')
            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email professionnel *</label>
            <input id="email" name="email" type="email" required
                class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-transparent transition-all"
                value="{{ old('email') }}" placeholder="admin@organisation.com">
            @error('email')
            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe *</label>
                <input id="password" name="password" type="password" required
                    class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-transparent transition-all"
                    placeholder="••••••••">
                @error('password')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmer *</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                    class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-transparent transition-all"
                    placeholder="••••••••">
            </div>
        </div>
    </div>

    <div class="pt-4">
        <button type="submit"
            class="w-full flex justify-center py-4 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-gradient-to-r from-accent-500 to-pink-600 hover:from-accent-600 hover:to-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent-500 transition-all transform hover:scale-[1.02]">
            Créer mon compte organisation
        </button>
    </div>
</form>
@endsection

@section('footer')
<p class="text-white/80 text-sm">
    Déjà partenaire ?
    <a href="{{ route('organization.login') }}" class="text-white font-semibold hover:underline">
        Se connecter
    </a>
</p>
@endsection