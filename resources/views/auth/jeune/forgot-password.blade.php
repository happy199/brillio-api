@extends('layouts.auth')

@section('title', 'Mot de passe oublié')
@section('heading', 'Mot de passe oublié ?')
@section('subheading', 'Entrez votre email pour recevoir un lien de réinitialisation')

@section('content')
    <div class="space-y-6">
        @if (session('status'))
            <div class="p-4 bg-green-50 border border-green-200 rounded-xl">
                <p class="text-green-800 text-sm">{{ session('status') }}</p>
            </div>
        @endif

        <form action="{{ route('auth.jeune.password.email') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Adresse email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition-colors @error('email') border-red-500 @enderror"
                    placeholder="vous@exemple.com">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                class="w-full py-3 px-4 bg-gradient-to-r from-primary-600 to-accent-600 text-white font-semibold rounded-xl hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all shadow-lg">
                Envoyer le lien de réinitialisation
            </button>
        </form>

        <div class="text-center">
            <a href="{{ route('auth.jeune.login') }}" class="text-sm text-primary-600 hover:underline">
                ← Retour à la connexion
            </a>
        </div>
    </div>
@endsection

@section('footer')
    <p class="text-white/80 text-sm">
        Pas encore de compte ?
        <a href="{{ route('auth.jeune.register') }}" class="text-white font-semibold hover:underline">S'inscrire</a>
    </p>
@endsection