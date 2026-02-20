@extends('layouts.auth')

@section('title', 'Connexion - Brillio')
@section('heading', 'Bon retour parmi nous !')
@section('subheading', 'Choisissez votre type de compte pour vous connecter')

@section('content')
<div class="space-y-6">
    <!-- Jeune Card -->
    <a href="{{ route('auth.jeune.login') }}"
        class="block p-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl border-2 border-transparent hover:border-primary-500 transition-all duration-300 group">
        <div class="flex items-center gap-4">
            <div
                class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-xl font-bold text-gray-900">Je suis un Jeune</h3>
                <p class="text-gray-600 text-sm mt-1">
                    Connectez-vous pour continuer votre exploration et decouvrir votre voie.
                </p>
            </div>
            <svg class="w-6 h-6 text-gray-400 group-hover:text-primary-500 group-hover:translate-x-1 transition-all"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </div>
    </a>

    <!-- Divider -->
    <div class="relative">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-200"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="px-4 bg-white text-gray-500">ou</span>
        </div>
    </div>

    <!-- Mentor Card -->
    <a href="{{ route('auth.mentor.login') }}"
        class="block p-6 bg-gradient-to-r from-amber-50 to-orange-50 rounded-2xl border-2 border-transparent hover:border-orange-500 transition-all duration-300 group">
        <div class="flex items-center gap-4">
            <div
                class="w-16 h-16 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-xl font-bold text-gray-900">Je suis un Mentor</h3>
                <p class="text-gray-600 text-sm mt-1">
                    Connectez-vous via LinkedIn pour acceder a votre espace mentor.
                </p>
            </div>
            <svg class="w-6 h-6 text-gray-400 group-hover:text-orange-500 group-hover:translate-x-1 transition-all"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </div>
    </a>

    <!-- Divider -->
    <div class="relative">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-200"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="px-4 bg-white text-gray-500">ou</span>
        </div>
    </div>

    <!-- Organization Card -->
    <a href="{{ route('organization.login') }}"
        class="block p-6 bg-gradient-to-r from-purple-50 to-pink-50 rounded-2xl border-2 border-transparent hover:border-purple-500 transition-all duration-300 group">
        <div class="flex items-center gap-4">
            <div
                class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-xl font-bold text-gray-900">Je suis une Organisation</h3>
                <p class="text-gray-600 text-sm mt-1">
                    Accédez a votre tableau de bord pour gérer vos offres et suivre vos talents.
                </p>
            </div>
            <svg class="w-6 h-6 text-gray-400 group-hover:text-purple-500 group-hover:translate-x-1 transition-all"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </div>
    </a>

    <!-- LinkedIn Badge for Mentors -->
    <div class="text-center">
        <p class="text-xs text-gray-500">
            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 24 24">
                <path
                    d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
            </svg>
            Mentors : Connexion exclusive via LinkedIn pour verifier votre profil professionnel
        </p>
    </div>
</div>
@endsection

@section('footer')
<p class="text-white/80 text-sm">
    Pas encore inscrit ?
    <a href="{{ route('auth.choice') }}" class="text-white font-semibold hover:underline">Creer un compte</a>
</p>
@endsection