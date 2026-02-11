@extends('layouts.guest')

@section('title', 'Connexion Organisation')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <h2 class="text-3xl font-extrabold text-gray-900">
                Connexion Organisation
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Accédez à votre espace partenaire
            </p>
        </div>

        <form class="mt-8 space-y-6 bg-white p-8 rounded-xl shadow-lg" method="POST"
            action="{{ route('organization.login.submit') }}">
            @csrf

            @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
                {{ $errors->first() }}
            </div>
            @endif

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input id="email" name="email" type="email" required autofocus
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
                    value="{{ old('email') }}">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
                <input id="password" name="password" type="password" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500">
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox"
                        class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                    <label for="remember" class="ml-2 block text-sm text-gray-900">
                        Se souvenir de moi
                    </label>
                </div>
            </div>

            <div>
                <button type="submit"
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                    Se connecter
                </button>
            </div>

            <div class="text-center">
                <p class="text-sm text-gray-600">
                    Pas encore inscrit ?
                    <a href="{{ route('organization.register') }}"
                        class="font-medium text-green-600 hover:text-green-500">
                        Créer un compte organisation
                    </a>
                </p>
            </div>
        </form>
    </div>
</div>
@endsection