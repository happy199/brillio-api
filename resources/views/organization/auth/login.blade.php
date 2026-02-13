@extends('layouts.auth')

@section('title', 'Connexion Organisation')
@section('heading', 'Espace Organisation')
@section('subheading', 'Accédez à votre espace partenaire et gérez vos offres')

@section('content')
<form class="space-y-6" method="POST" action="{{ route('organization.login.submit') }}">
    @csrf

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email professionnel</label>
        <input id="email" name="email" type="email" required autofocus
            class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-transparent transition-all"
            placeholder="votre@organisation.com" value="{{ old('email') }}">
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
        <input id="password" name="password" type="password" required
            class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-transparent transition-all"
            placeholder="••••••••">
    </div>

    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <input id="remember" name="remember" type="checkbox"
                class="h-4 w-4 text-accent-600 focus:ring-accent-500 border-gray-300 rounded">
            <label for="remember" class="ml-2 block text-sm text-gray-600">
                Se souvenir de moi
            </label>
        </div>
        <a href="#" class="text-sm font-medium text-accent-600 hover:text-accent-500">
            Mot de passe oublié ?
        </a>
    </div>

    <div>
        <button type="submit"
            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-gradient-to-r from-accent-500 to-pink-600 hover:from-accent-600 hover:to-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent-500 transition-all transform hover:scale-[1.02]">
            Se connecter
        </button>
    </div>
</form>
@endsection

@section('footer')
<p class="text-white/80 text-sm">
    Pas encore partenaire ?
    <a href="{{ route('organization.register') }}" class="text-white font-semibold hover:underline">
        Créer un compte organisation
    </a>
</p>
@endsection