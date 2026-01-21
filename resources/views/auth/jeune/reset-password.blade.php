@extends('layouts.auth')

@section('title', 'Réinitialiser le mot de passe')
@section('heading', 'Nouveau mot de passe')
@section('subheading', 'Choisissez un nouveau mot de passe sécurisé')

@section('content')
    <div class="space-y-6">
        <form action="{{ route('auth.jeune.password.update') }}" method="POST" class="space-y-4">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email ?? request('email') }}">

            <div>
                <label for="email_display" class="block text-sm font-medium text-gray-700 mb-1">Adresse email</label>
                <input type="email" id="email_display" value="{{ $email ?? request('email') }}" disabled
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-600">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe</label>
                <input type="password" id="password" name="password" required autofocus
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition-colors @error('password') border-red-500 @enderror"
                    placeholder="Minimum 8 caractères">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de
                    passe</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition-colors"
                    placeholder="Confirmez votre mot de passe">
            </div>

            @error('email')
                <div class="p-4 bg-red-50 border border-red-200 rounded-xl">
                    <p class="text-red-800 text-sm">{{ $message }}</p>
                </div>
            @enderror

            <button type="submit"
                class="w-full py-3 px-4 bg-gradient-to-r from-primary-600 to-accent-600 text-white font-semibold rounded-xl hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all shadow-lg">
                Réinitialiser le mot de passe
            </button>
        </form>
    </div>
@endsection

@section('footer')
    <p class="text-white/80 text-sm">
        <a href="{{ route('auth.jeune.login') }}" class="text-white font-semibold hover:underline">← Retour à la
            connexion</a>
    </p>
@endsection