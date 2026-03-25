@extends('layouts.auth')

@section('content')
<div class="sm:mx-auto sm:w-full sm:max-w-md">
    <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <div class="text-center mb-6">
            <div class="bg-indigo-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Double Authentification</h2>
            <p class="mt-2 text-sm text-gray-600">
                Ouvrez votre application d'authentification (Google Authenticator) et saisissez le code à 6 chiffres.
            </p>
        </div>

        <form action="{{ route('admin.two_factor.verify') }}" method="POST">
            @csrf
            <div>
                <label for="code" class="block text-sm font-medium text-gray-700">Code de vérification</label>
                <div class="mt-1">
                    <input id="code" name="code" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="6" required autofocus
                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-lg text-center tracking-[1em]"
                        placeholder="000000">
                </div>
                @error('code')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6">
                <button type="submit"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Vérifier et se connecter
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
