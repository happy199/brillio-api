@extends('layouts.mentor')

@section('title', 'Activation requise')

@section('content')
    <div class="text-center py-12 max-w-4xl mx-auto">
        <!-- Icon -->
        <div class="bg-indigo-50 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>

        <h2 class="text-3xl font-bold text-gray-900 mb-4">Activez votre espace Mentorat</h2>
        <p class="text-lg text-gray-600 mb-10 leading-relaxed max-w-2xl mx-auto">
            Pour accéder aux outils de mentorat (Ressources, Calendrier, Séances, Mentés), vous devez d'abord publier votre
            profil.
            Cela garantit aux étudiants que vous êtes prêt à les accompagner.
        </p>

        <!-- Feature Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12 text-left">
            <!-- Feature 1: Ressources -->
            <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mb-4 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">Vendez vos Ressources</h3>
                <p class="text-gray-600 text-sm">Partagez votre expertise (cours, exercices, templates) et générez des
                    revenus passifs.</p>
            </div>

            <!-- Feature 2: Calendrier -->
            <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mb-4 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">Gérez vos Disponibilités</h3>
                <p class="text-gray-600 text-sm">Définissez vos créneaux horaires récurrents ou ponctuels pour recevoir des
                    demandes de coaching.</p>
            </div>

            <!-- Feature 3: Séances Vidéo -->
            <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mb-4 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">Salle de Conférence Sécurisée</h3>
                <p class="text-gray-600 text-sm">Réalisez vos séances de mentorat directement sur la plateforme via notre
                    outil vidéo intégré.</p>
            </div>

            <!-- Feature 4: Suivi Mentés -->
            <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition">
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mb-4 text-orange-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">Suivi des Étudiants</h3>
                <p class="text-gray-600 text-sm">Gardez une trace de vos échanges, notes et progressions pour chaque menté
                    accompagné.</p>
            </div>
        </div>

        <!-- Alert -->
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-8 inline-block text-left">
            <div class="flex gap-3">
                <svg class="w-6 h-6 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h3 class="font-bold text-amber-900 text-sm mb-1">Pourquoi publier ?</h3>
                    <p class="text-amber-800 text-sm">
                        Tant que votre profil est "Non publié" ou "Brouillon", vous ne pouvez pas interagir avec les
                        fonctionnalités publiques.
                        Vous pouvez modifier votre profil à tout moment.
                    </p>
                </div>
            </div>
        </div>

        <br>

        <a href="{{ route('mentor.profile') }}"
            class="inline-flex items-center justify-center px-8 py-4 text-lg font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
            Publier mon profil maintenant
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
            </svg>
        </a>
    </div>
@endsection