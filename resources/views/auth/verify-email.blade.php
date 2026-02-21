@extends('layouts.auth')

@section('title', 'Vérifiez votre e-mail')
@section('heading', 'Presque fini !')
@section('subheading', 'Merci de faire partie de la communauté Brillio. Avant de découvrir votre potentiel, merci de
valider votre adresse e-mail en cliquant sur le lien que nous venons de vous envoyer.')

@section('content')
<div class="space-y-6 text-center">
    <div class="flex justify-center">
        <div class="w-20 h-20 bg-primary-100 rounded-full flex items-center justify-center">
            <svg class="w-10 h-10 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
        </div>
    </div>

    @if (session('success'))
    <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg text-left">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-green-700">
                    {{ session('success') }}
                </p>
            </div>
        </div>
    </div>
    @endif

    <div class="space-y-4">
        <form method="POST" action="{{ route('verification.resend') }}">
            @csrf
            <button type="submit"
                class="w-full flex justify-center py-4 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-gradient-to-r from-primary-500 to-indigo-600 hover:from-primary-600 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all transform hover:scale-[1.02]">
                Renvoyer l'e-mail de vérification
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-gray-500 hover:text-gray-700 font-medium transition-colors">
                Me déconnecter et réessayer plus tard
            </button>
        </form>
    </div>
</div>
@endsection