@extends('layouts.auth')

@section('title', 'Vérifiez votre e-mail')
@section('heading', 'Vérification de votre compte')
@section('subheading', 'Merci de vous être inscrit ! Avant de commencer, pourriez-vous vérifier votre adresse e-mail en
cliquant sur le lien que nous venons de vous envoyer ?')

@section('content')
<div class="space-y-6">
    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    Si vous n'avez pas reçu l'e-mail, nous vous en enverrons un autre avec plaisir.
                </p>
            </div>
        </div>
    </div>

    @if (session('success'))
    <div class="bg-organization-50 border-l-4 border-organization-400 p-4 rounded-r-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-organization-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-organization-700">
                    {{ session('success') }}
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Formulaire d'entrée manuelle de code à 6 chiffres -->
    <div class="bg-gray-50 border border-gray-200 rounded-2xl p-5 text-center space-y-3">
        <p class="text-sm font-semibold text-gray-800">
            Le lien ne fonctionne pas ? Utilisez votre code à 6 chiffres
        </p>
        <label for="org_verification_code" class="text-xs text-gray-500 block">
            Saisissez le code à 6 chiffres reçu dans votre boîte e-mail :
        </label>

        <form method="POST" action="{{ route('organization.verification.verify-code') }}" class="space-y-3">
            @csrf
            <div class="flex justify-center">
                <input id="org_verification_code" type="text" name="code" maxlength="6" pattern="[0-9]{6}" placeholder="123456" required
                    class="w-44 text-center text-xl font-mono font-bold tracking-widest px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-accent-500 focus:border-accent-500 bg-white">
            </div>
            @error('code')
                <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
            @enderror

            <button type="submit"
                class="w-full py-2.5 px-4 bg-gray-900 hover:bg-black text-white text-xs font-bold rounded-xl transition-all shadow">
                Valider le code
            </button>
        </form>
    </div>

    <div class="flex flex-col space-y-4 pt-2">
        <form method="POST" action="{{ route('organization.verification.resend') }}">
            @csrf
            <button type="submit"
                class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold text-white bg-gradient-to-r from-accent-500 to-organization-600 hover:from-accent-600 hover:to-organization-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent-500 transition-all transform hover:scale-[1.01]">
                Renvoyer l'e-mail avec code
            </button>
        </form>

        <form method="POST" action="{{ route('organization.logout') }}">
            @csrf
            <button type="submit"
                class="w-full text-center text-sm text-gray-500 hover:text-gray-700 font-medium transition-colors">
                Se déconnecter
            </button>
        </form>
    </div>
</div>
@endsection