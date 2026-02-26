@extends('layouts.public')

{{-- SEO Meta Tags --}}
<x-seo-meta page="home" />


@section('content')
<!-- Alpine.js Data for App Modal -->
<div x-data="{ showAppModal: false }">

    <!-- Hero Section -->
    <section class="gradient-hero min-h-screen flex items-center relative overflow-hidden">
        <!-- Background decoration -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 -left-40 w-96 h-96 bg-secondary-500/20 rounded-full blur-3xl"></div>
            <div class="absolute bottom-20 right-1/4 w-64 h-64 bg-accent-500/20 rounded-full blur-3xl"></div>
        </div>

        <div class="container mx-auto px-4 sm:px-6 lg:px-8 pt-32 pb-20 relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Content -->
                <div class="text-white" data-aos="fade-right">
                    <span
                        class="inline-block px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full text-sm font-medium mb-6">
                        <!-- TODO: Ajouter badge promotionnel -->
                        Nouveau : Test de personnalité
                    </span>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold leading-tight mb-6">
                        Ton avenir,<br>
                        <span class="text-accent-300">ton choix.</span>
                    </h1>
                    <p class="text-xl text-white/90 mb-8 max-w-lg">
                        Découvre ta personnalité, explore les métiers qui te correspondent et connecte avec des mentors
                        africains qui ont réussi.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 mb-12">
                        <a href="{{ route('auth.choice') }}"
                            class="px-8 py-4 bg-white text-primary-600 font-bold rounded-full hover:bg-gray-100 hover:shadow-xl transition-all duration-300 text-center flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Explorer ta carrière
                        </a>
                        <button @click="showAppModal = true"
                            class="px-8 py-4 border-2 border-white/50 text-white font-semibold rounded-full hover:bg-white/10 transition-all duration-300 text-center">
                            Télécharger l'app mobile
                        </button>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-6">
                        <div>
                            <p class="text-3xl font-bold">{{ $jeunesCount }}+</p>
                            <p class="text-white/70 text-sm">Jeunes accompagnés</p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold">{{ $mentorsCount }}+</p>
                            <p class="text-white/70 text-sm">Mentors actifs</p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold">{{ $countriesCount }}+</p>
                            <p class="text-white/70 text-sm">Pays africains</p>
                        </div>
                    </div>
                </div>

                <!-- Hero Image / Phone mockup -->
                <div class="relative" data-aos="fade-left" data-aos-delay="200">
                    <div class="relative mx-auto w-72 sm:w-80">
                        <!-- Phone frame -->
                        <div class="relative bg-gray-900 rounded-[3rem] p-3 shadow-2xl float-animation">
                            <div class="bg-gray-800 rounded-[2.5rem] overflow-hidden">
                                <!-- TODO: Remplacer par vraie capture d'écran de l'app -->
                                <div
                                    class="aspect-[9/19] bg-gradient-to-b from-primary-100 to-secondary-100 flex items-center justify-center">
                                    <div class="text-center p-6">
                                        <div
                                            class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-primary-500 to-secondary-500 rounded-2xl flex items-center justify-center">
                                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                                </path>
                                            </svg>
                                        </div>
                                        <h3 class="text-xl font-bold text-gray-800">Brillio</h3>
                                        <p class="text-gray-600 text-sm mt-2">L'application mobile bientôt disponible
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <!-- Notch -->
                            <div
                                class="absolute top-3 left-1/2 transform -translate-x-1/2 w-24 h-6 bg-gray-900 rounded-full">
                            </div>
                        </div>
                        <!-- Decorative elements -->
                        <div class="absolute -top-8 -right-8 w-24 h-24 bg-accent-400 rounded-2xl rotate-12 opacity-80">
                        </div>
                        <div class="absolute -bottom-6 -left-6 w-20 h-20 bg-secondary-400 rounded-full opacity-80">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Wave separator -->
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M0 120L60 110C120 100 240 80 360 70C480 60 600 60 720 65C840 70 960 80 1080 85C1200 90 1320 90 1380 90L1440 90V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z"
                    fill="#F9FAFB" />
            </svg>
        </div>
    </section>


    <!-- Value Proposition Section -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-16" data-aos="fade-up">
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                        Pourquoi choisir Brillio ?
                    </h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                        Une plateforme complète pensée pour les jeunes africains, avec des outils adaptés à ton contexte
                        et
                        tes ambitions.
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Value 1 -->
                    <div class="group bg-gradient-to-br from-primary-50 to-secondary-50 rounded-2xl p-8 hover:shadow-xl transition-all duration-300"
                        data-aos="fade-up" data-aos-delay="100">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-primary-500 to-secondary-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Connais-toi mieux</h3>
                        <p class="text-gray-600">
                            Découvre ta personnalité unique avec notre test MBTI scientifique et reçois des
                            recommandations
                            de métiers adaptés à qui tu es vraiment.
                        </p>
                    </div>

                    <!-- Value 2 -->
                    <div class="group bg-gradient-to-br from-secondary-50 to-accent-50 rounded-2xl p-8 hover:shadow-xl transition-all duration-300"
                        data-aos="fade-up" data-aos-delay="200">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-secondary-500 to-accent-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Inspire-toi de modèles</h3>
                        <p class="text-gray-600">
                            Explore les parcours de professionnels africains qui ont réussi dans ton domaine d'intérêt
                            et
                            apprends de leur expérience.
                        </p>
                    </div>

                    <!-- Value 3 -->
                    <div class="group bg-gradient-to-br from-accent-50 to-primary-50 rounded-2xl p-8 hover:shadow-xl transition-all duration-300"
                        data-aos="fade-up" data-aos-delay="300">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-accent-500 to-primary-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Passe à l'action</h3>
                        <p class="text-gray-600">
                            Reçois des conseils personnalisés 24/7 de notre IA et construis ton plan d'action concret
                            pour
                            atteindre tes objectifs professionnels.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Problem / Solution Section -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center mb-16" data-aos="fade-up">
                <span class="text-primary-600 font-semibold text-sm uppercase tracking-wider">Le problème</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mt-4 mb-6">
                    L'orientation professionnelle en Afrique, un défi majeur
                </h2>
                <p class="text-xl text-gray-600">
                    Des millions de jeunes africains sont perdus face à leur avenir professionnel.
                    Manque de conseillers, absence de modèles, informations inadaptées au contexte local...
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 mb-16">
                <!-- Problem cards -->
                <div class="bg-red-50 border border-red-100 rounded-2xl p-8" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-14 h-14 bg-red-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Manque de conseillers</h3>
                    <p class="text-gray-600">
                        1 conseiller pour 10 000 élèves en moyenne en Afrique subsaharienne.
                    </p>
                </div>

                <div class="bg-orange-50 border border-orange-100 rounded-2xl p-8" data-aos="fade-up"
                    data-aos-delay="200">
                    <div class="w-14 h-14 bg-orange-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Absence de modèles</h3>
                    <p class="text-gray-600">
                        Difficile de se projeter quand on ne connaît personne qui a réussi dans le domaine visé.
                    </p>
                </div>

                <div class="bg-yellow-50 border border-yellow-100 rounded-2xl p-8" data-aos="fade-up"
                    data-aos-delay="300">
                    <div class="w-14 h-14 bg-yellow-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Informations inadaptées</h3>
                    <p class="text-gray-600">
                        Les ressources existantes sont souvent pensées pour les pays occidentaux, pas pour l'Afrique.
                    </p>
                </div>
            </div>

            <!-- Solution -->
            <div class="bg-gradient-to-r from-primary-600 to-secondary-600 rounded-3xl p-8 md:p-12 text-white"
                data-aos="fade-up">
                <div class="grid md:grid-cols-2 gap-8 items-center">
                    <div>
                        <span class="inline-block px-4 py-2 bg-white/20 rounded-full text-sm font-medium mb-4">La
                            solution</span>
                        <h3 class="text-3xl font-bold mb-4">Brillio : L'orientation réinventée pour l'Afrique</h3>
                        <p class="text-white/90 text-lg mb-6">
                            Une application mobile qui combine intelligence artificielle, test de personnalité et
                            mentorat pour offrir une orientation personnalisée et adaptée au contexte africain.
                        </p>
                        <a href="#fonctionnalites"
                            class="inline-flex items-center text-white font-semibold hover:underline">
                            Découvrir comment ça marche
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                            </svg>
                        </a>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6 text-center">
                            <p class="text-4xl font-bold">24/7</p>
                            <p class="text-white/80 text-sm">Disponible</p>
                        </div>
                        <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6 text-center">
                            <p class="text-4xl font-bold">🌍</p>
                            <p class="text-white/80 text-sm">Pour l'Afrique</p>
                        </div>
                        <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6 text-center">
                            <p class="text-4xl font-bold">IA</p>
                            <p class="text-white/80 text-sm">Personnalisée</p>
                        </div>
                        <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6 text-center">
                            <p class="text-4xl font-bold">FR</p>
                            <p class="text-white/80 text-sm">En français</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="fonctionnalites" class="py-20 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center mb-16" data-aos="fade-up">
                <span class="text-primary-600 font-semibold text-sm uppercase tracking-wider">Fonctionnalités</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mt-4 mb-6">
                    Tout ce dont tu as besoin pour construire ton avenir
                </h2>
                <p class="text-xl text-gray-600">
                    Des outils puissants et intuitifs pour t'accompagner dans ton parcours d'orientation.
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1: Test de personnalité -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-shadow duration-300"
                    data-aos="fade-up" data-aos-delay="100">
                    <div
                        class="w-14 h-14 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Test de personnalité</h3>
                    <p class="text-gray-600 mb-4">
                        Découvre ton type de personnalité parmi 16 profils et comprends tes forces,
                        tes motivations et les métiers qui te correspondent.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            32 questions scientifiques
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Résultats en français
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Métiers suggérés
                        </li>
                    </ul>
                </div>

                <!-- Feature 2: Chatbot IA -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-shadow duration-300"
                    data-aos="fade-up" data-aos-delay="200">
                    <div
                        class="w-14 h-14 bg-gradient-to-br from-secondary-500 to-secondary-600 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Conseiller IA 24/7</h3>
                    <p class="text-gray-600 mb-4">
                        Pose toutes tes questions sur l'orientation, les métiers et les formations à notre
                        assistant IA disponible jour et nuit.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Réponses personnalisées
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Contexte africain
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Historique sauvegardé
                        </li>
                    </ul>
                </div>

                <!-- Feature 3: Mentoring -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-shadow duration-300"
                    data-aos="fade-up" data-aos-delay="300">
                    <div
                        class="w-14 h-14 bg-gradient-to-br from-accent-500 to-accent-600 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Mentors africains</h3>
                    <p class="text-gray-600 mb-4">
                        Connecte avec des professionnels africains qui partagent leur parcours et leurs conseils
                        pour t'inspirer et te guider.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Parcours inspirants
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Roadmaps détaillées
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Tous secteurs
                        </li>
                    </ul>
                </div>

                <!-- Feature 4: Documents -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-shadow duration-300"
                    data-aos="fade-up" data-aos-delay="400">
                    <div
                        class="w-14 h-14 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Gestion des documents</h3>
                    <p class="text-gray-600 mb-4">
                        Centralise et organise tous tes documents académiques : bulletins, relevés de notes,
                        diplômes... Tout au même endroit.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Stockage sécurisé
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Accès hors-ligne
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Organisation facile
                        </li>
                    </ul>
                </div>

                <!-- Feature 5: Profil -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-shadow duration-300"
                    data-aos="fade-up" data-aos-delay="500">
                    <div
                        class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Profil personnalisé</h3>
                    <p class="text-gray-600 mb-4">
                        Crée ton profil et reçois des recommandations adaptées à ta situation,
                        ton pays et tes objectifs.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Suggestions IA
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Suivi de progression
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Données protégées
                        </li>
                    </ul>
                </div>

                <!-- Feature 6: Accessible -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-shadow duration-300"
                    data-aos="fade-up" data-aos-delay="600">
                    <div
                        class="w-14 h-14 bg-gradient-to-br from-pink-500 to-pink-600 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Accessible à tous</h3>
                    <p class="text-gray-600 mb-4">
                        Commence sans carte bancaire. Accède aux fonctionnalités essentielles
                        pour démarrer ton orientation professionnelle.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Essai complet
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Pas de carte requise
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Fonctionnalités premium
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Active Mentorship Section -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-6xl mx-auto">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <!-- Left: Content -->
                    <div data-aos="fade-right">
                        <span class="text-primary-600 font-semibold text-sm uppercase tracking-wider">
                            Mentorat Actif
                        </span>
                        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mt-4 mb-6">
                            Un accompagnement personnalisé avec des professionnels africains
                        </h2>
                        <p class="text-lg text-gray-600 mb-8">
                            Ne te contente pas de lire des parcours : échange directement avec des mentors
                            qui comprennent ton contexte et peuvent t'aider concrètement à atteindre tes objectifs.
                        </p>

                        <!-- Features list -->
                        <div class="space-y-4">
                            <div class="flex items-start space-x-4">
                                <div
                                    class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-1">Réservation simple</h4>
                                    <p class="text-gray-600 text-sm">
                                        Choisis ton mentor, consulte ses disponibilités et réserve une session en
                                        quelques clics.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-4">
                                <div
                                    class="w-12 h-12 bg-secondary-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-secondary-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-1">Visioconférence intégrée</h4>
                                    <p class="text-gray-600 text-sm">
                                        Rejoins tes sessions directement depuis l'application, pas besoin d'outils
                                        externes.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-4">
                                <div
                                    class="w-12 h-12 bg-accent-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-accent-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-1">Suivi de progression</h4>
                                    <p class="text-gray-600 text-sm">
                                        Garde une trace de tes sessions, objectifs et conseils reçus pour mesurer ton
                                        évolution.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('auth.choice') }}"
                            class="inline-flex items-center mt-8 px-6 py-3 bg-primary-600 text-white font-semibold rounded-full hover:bg-primary-700 transition-all duration-300">
                            Rejoindre Brillio
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </a>
                    </div>

                    <!-- Right: Visual -->
                    <div data-aos="fade-left">
                        <div class="bg-gradient-to-br from-primary-50 to-secondary-50 rounded-3xl p-8">
                            <!-- Illustration mentorat -->
                            <div class="text-center space-y-6">
                                <div class="grid grid-cols-2 gap-4">
                                    <!-- Session card preview -->
                                    <div class="bg-white rounded-xl p-4 shadow-sm text-left">
                                        <div
                                            class="w-12 h-12 bg-primary-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-700 mb-1">Session prévue</p>
                                        <p class="text-xs text-gray-500">Vendredi 14h00</p>
                                        <div class="mt-2 flex items-center gap-1">
                                            <div class="w-6 h-6 bg-gray-300 rounded-full"></div>
                                            <span class="text-xs text-gray-600">Dr. Koffi</span>
                                        </div>
                                    </div>

                                    <div class="bg-white rounded-xl p-4 shadow-sm text-left">
                                        <div
                                            class="w-12 h-12 bg-green-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-700 mb-1">Complétées</p>
                                        <p class="text-2xl font-bold text-gray-900">3</p>
                                        <p class="text-xs text-gray-500">sessions</p>
                                    </div>
                                </div>

                                <div class="bg-white rounded-xl p-4 shadow-sm">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-sm font-semibold text-gray-700">Ta progression</span>
                                        <span class="text-sm text-primary-600 font-semibold">75%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-gradient-to-r from-primary-500 to-secondary-500 h-2 rounded-full"
                                            style="width: 75%"></div>
                                    </div>
                                </div>

                                <div class="inline-flex items-center gap-2 bg-white rounded-full px-4 py-2 shadow-sm">
                                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                    <p class="text-sm font-medium text-gray-700">+500 mentors actifs</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How it Works Section -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center mb-16" data-aos="fade-up">
                <span class="text-primary-600 font-semibold text-sm uppercase tracking-wider">Comment ça marche</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mt-4 mb-6">
                    3 étapes simples pour démarrer
                </h2>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Step 1 -->
                <div class="text-center" data-aos="fade-up" data-aos-delay="100">
                    <div class="relative inline-block mb-6">
                        <div
                            class="w-20 h-20 bg-gradient-to-br from-primary-500 to-secondary-500 rounded-full flex items-center justify-center text-white text-3xl font-bold">
                            1
                        </div>
                        <div
                            class="hidden md:block absolute top-1/2 left-full w-full h-0.5 bg-gradient-to-r from-primary-500 to-transparent">
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Télécharge l'app</h3>
                    <p class="text-gray-600">
                        Disponible sur iOS et Android. Crée ton compte en quelques secondes.
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="text-center" data-aos="fade-up" data-aos-delay="200">
                    <div class="relative inline-block mb-6">
                        <div
                            class="w-20 h-20 bg-gradient-to-br from-secondary-500 to-accent-500 rounded-full flex items-center justify-center text-white text-3xl font-bold">
                            2
                        </div>
                        <div
                            class="hidden md:block absolute top-1/2 left-full w-full h-0.5 bg-gradient-to-r from-secondary-500 to-transparent">
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Passe le test</h3>
                    <p class="text-gray-600">
                        Réponds aux 32 questions du test de personnalité pour découvrir ton profil unique.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="text-center" data-aos="fade-up" data-aos-delay="300">
                    <div class="relative inline-block mb-6">
                        <div
                            class="w-20 h-20 bg-gradient-to-br from-accent-500 to-primary-500 rounded-full flex items-center justify-center text-white text-3xl font-bold">
                            3
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Explore ton avenir</h3>
                    <p class="text-gray-600">
                        Découvre les métiers qui te correspondent, discute avec l'IA et inspire-toi des mentors.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-20 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center mb-16" data-aos="fade-up">
                <span class="text-primary-600 font-semibold text-sm uppercase tracking-wider">Témoignages</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mt-4 mb-6">
                    Ce que disent nos utilisateurs
                </h2>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Testimonial 1 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm" data-aos="fade-up" data-aos-delay="100">
                    <div class="flex items-center mb-4">
                        @for ($i = 0; $i < 5; $i++) <svg class="w-5 h-5 text-yellow-400" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            @endfor
                    </div>
                    <p class="text-gray-600 mb-6 italic">
                        <!-- TODO: Ajouter vrai témoignage -->
                        "Grâce à Brillio, j'ai découvert que j'étais ENFJ et que les métiers du marketing me
                        correspondaient
                        parfaitement. Aujourd'hui je suis en stage dans une agence !"
                    </p>
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center text-gray-500">
                            <!-- TODO: Ajouter photo -->
                            AM
                        </div>
                        <div class="ml-4">
                            <p class="font-semibold text-gray-900">Aminata M.</p>
                            <p class="text-sm text-gray-500">Cotonou, Bénin</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 2 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm" data-aos="fade-up" data-aos-delay="200">
                    <div class="flex items-center mb-4">
                        @for ($i = 0; $i < 5; $i++) <svg class="w-5 h-5 text-yellow-400" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            @endfor
                    </div>
                    <p class="text-gray-600 mb-6 italic">
                        <!-- TODO: Ajouter vrai témoignage -->
                        "Le chatbot IA est incroyable ! Il m'a donné des conseils super adaptés à la situation au
                        Cameroun.
                        Je le recommande à tous mes amis."
                    </p>
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center text-gray-500">
                            <!-- TODO: Ajouter photo -->
                            JK
                        </div>
                        <div class="ml-4">
                            <p class="font-semibold text-gray-900">Jean-Pierre K.</p>
                            <p class="text-sm text-gray-500">Douala, Cameroun</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 3 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm" data-aos="fade-up" data-aos-delay="300">
                    <div class="flex items-center mb-4">
                        @for ($i = 0; $i < 5; $i++) <svg class="w-5 h-5 text-yellow-400" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            @endfor
                    </div>
                    <p class="text-gray-600 mb-6 italic">
                        <!-- TODO: Ajouter vrai témoignage -->
                        "Les profils de mentors m'ont vraiment inspiré. Voir des parcours de professionnels africains
                        qui
                        ont réussi, ça change tout !"
                    </p>
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center text-gray-500">
                            <!-- TODO: Ajouter photo -->
                            FD
                        </div>
                        <div class="ml-4">
                            <p class="font-semibold text-gray-900">Fatou D.</p>
                            <p class="text-sm text-gray-500">Abidjan, Côte d'Ivoire</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Web Platform CTA Section -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-5xl mx-auto">
                <div
                    class="bg-gradient-to-br from-primary-50 via-secondary-50 to-accent-50 rounded-3xl p-8 md:p-12 relative overflow-hidden">
                    <!-- Decorative elements -->
                    <div
                        class="absolute top-0 right-0 w-64 h-64 bg-primary-200 rounded-full opacity-20 blur-3xl transform translate-x-1/2 -translate-y-1/2">
                    </div>
                    <div
                        class="absolute bottom-0 left-0 w-48 h-48 bg-secondary-200 rounded-full opacity-20 blur-3xl transform -translate-x-1/2 translate-y-1/2">
                    </div>

                    <div class="relative z-10 grid md:grid-cols-2 gap-8 items-center">
                        <div data-aos="fade-right">
                            <span
                                class="inline-block px-4 py-2 bg-primary-100 text-primary-700 rounded-full text-sm font-medium mb-4">
                                Nouveau : Plateforme Web
                            </span>
                            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                                Accède à Brillio depuis ton navigateur
                            </h2>
                            <p class="text-lg text-gray-600 mb-6">
                                Pas envie de télécharger ? Utilise notre plateforme web pour passer le test de
                                personnalité,
                                discuter
                                avec notre IA et explorer les parcours de mentors africains.
                            </p>
                            <div class="flex flex-col sm:flex-row gap-4">
                                <a href="{{ route('auth.choice') }}"
                                    class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-gradient-to-r from-primary-600 to-secondary-600 text-white font-bold rounded-full hover:shadow-xl transition-all duration-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    Commencer maintenant
                                </a>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4" data-aos="fade-left">
                            <div class="bg-white rounded-2xl p-6 shadow-sm">
                                <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center mb-4">
                                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                    </svg>
                                </div>
                                <h4 class="font-bold text-gray-900">Test de personnalité</h4>
                                <p class="text-sm text-gray-500">Découvre ton profil</p>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-sm">
                                <div
                                    class="w-12 h-12 bg-secondary-100 rounded-xl flex items-center justify-center mb-4">
                                    <svg class="w-6 h-6 text-secondary-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                </div>
                                <h4 class="font-bold text-gray-900">Chat IA</h4>
                                <p class="text-sm text-gray-500">Conseils 24/7</p>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-sm">
                                <div class="w-12 h-12 bg-accent-100 rounded-xl flex items-center justify-center mb-4">
                                    <svg class="w-6 h-6 text-accent-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <h4 class="font-bold text-gray-900">Mentors</h4>
                                <p class="text-sm text-gray-500">Parcours inspirants</p>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-sm">
                                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-4">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h4 class="font-bold text-gray-900">Accessible</h4>
                                <p class="text-sm text-gray-500">Essai inclus</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA / Download Section -->
    <section id="telecharger" class="py-20 gradient-hero relative overflow-hidden">
        <!-- Background decoration -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-20 -left-40 w-96 h-96 bg-secondary-500/20 rounded-full blur-3xl"></div>
        </div>

        <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="max-w-4xl mx-auto text-center text-white">
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-6" data-aos="fade-up">
                    Prêt à construire ton avenir ?
                </h2>
                <p class="text-xl text-white/90 mb-10" data-aos="fade-up" data-aos-delay="100">
                    Télécharge Brillio ou utilise la plateforme web pour commencer ton voyage vers le métier de tes
                    rêves.
                </p>

                <!-- Download Buttons -->
                <div class="flex flex-col sm:flex-row justify-center gap-4 mb-12" data-aos="fade-up"
                    data-aos-delay="200">
                    <!-- App Store -->
                    <button @click="showAppModal = true"
                        class="flex items-center justify-center space-x-3 bg-black text-white rounded-xl px-8 py-4 hover:bg-gray-900 transition-colors">
                        <svg class="w-10 h-10" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z" />
                        </svg>
                        <div class="text-left">
                            <p class="text-xs text-gray-400">Télécharger sur</p>
                            <p class="text-lg font-semibold">App Store</p>
                        </div>
                    </button>
                    <!-- Google Play -->
                    <button @click="showAppModal = true"
                        class="flex items-center justify-center space-x-3 bg-black text-white rounded-xl px-8 py-4 hover:bg-gray-900 transition-colors">
                        <svg class="w-10 h-10" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.6 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.16,10.81C20.5,11.08 20.75,11.5 20.75,12C20.75,12.5 20.53,12.9 20.18,13.18L17.89,14.5L15.39,12L17.89,9.5L20.16,10.81M6.05,2.66L16.81,8.88L14.54,11.15L6.05,2.66Z" />
                        </svg>
                        <div class="text-left">
                            <p class="text-xs text-gray-400">Disponible sur</p>
                            <p class="text-lg font-semibold">Google Play</p>
                        </div>
                    </button>
                </div>

                <!-- Web platform CTA -->
                <div class="mt-8 pt-8 border-t border-white/20" data-aos="fade-up" data-aos-delay="300">
                    <p class="text-white/80 mb-4">Ou accède directement depuis ton navigateur</p>
                    <a href="{{ route('auth.choice') }}"
                        class="inline-flex items-center gap-2 px-8 py-4 bg-white/20 backdrop-blur-sm text-white font-semibold rounded-full hover:bg-white/30 transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                        Utiliser la version web
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Become a Mentor Section -->
    <section class="py-20 bg-gradient-to-br from-orange-50 via-red-50 to-pink-50">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-5xl mx-auto">
                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div data-aos="fade-right">
                        <span
                            class="inline-block px-4 py-2 bg-orange-100 text-orange-700 rounded-full text-sm font-medium mb-4">
                            Pour les professionnels
                        </span>
                        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                            Devenez mentor et inspirez la prochaine génération
                        </h2>
                        <p class="text-lg text-gray-600 mb-6">
                            Vous êtes un professionnel africain qui a réussi ? Partagez votre parcours, vos conseils et
                            inspirez des milliers de jeunes à suivre leurs rêves.
                        </p>
                        <ul class="space-y-4 mb-8">
                            <li class="flex items-start gap-3">
                                <div
                                    class="w-6 h-6 bg-orange-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">Partagez votre roadmap</p>
                                    <p class="text-sm text-gray-500">Decrivez les etapes cles de votre parcours
                                        professionnel</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <div
                                    class="w-6 h-6 bg-orange-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">Gagnez en visibilité</p>
                                    <p class="text-sm text-gray-500">Renforcez votre personal branding auprès de la
                                        jeunesse
                                        africaine</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <div
                                    class="w-6 h-6 bg-orange-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">Contribuez à l'Afrique</p>
                                    <p class="text-sm text-gray-500">Aidez à former les leaders de demain sur le
                                        continent
                                    </p>
                                </div>
                            </li>
                        </ul>
                        <a href="{{ route('mentor.login') }}"
                            class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-orange-500 to-red-500 text-white font-bold rounded-full hover:shadow-xl transition-all duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Devenir mentor via LinkedIn
                        </a>
                        <p class="text-sm text-gray-500 mt-3">Inscription rapide avec votre profil LinkedIn</p>
                    </div>
                    <div class="relative" data-aos="fade-left">
                        <!-- Mentor cards illustration -->
                        <div class="relative">
                            <div
                                class="bg-white rounded-2xl shadow-xl p-6 transform rotate-3 absolute top-0 right-0 w-64 opacity-60">
                                <div class="flex items-center gap-3 mb-4">
                                    <div
                                        class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center text-white font-bold">
                                        SK
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900">Sophie K.</p>
                                        <p class="text-xs text-gray-500">Directrice Marketing</p>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <span
                                        class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded-full">Marketing</span>
                                    <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">12 ans
                                        exp.</span>
                                </div>
                            </div>
                            <div
                                class="bg-white rounded-2xl shadow-xl p-6 transform -rotate-2 relative z-10 w-72 ml-8 mt-16">
                                <div class="flex items-center gap-3 mb-4">
                                    <div
                                        class="w-14 h-14 bg-gradient-to-br from-orange-400 to-red-500 rounded-xl flex items-center justify-center text-white font-bold text-lg">
                                        AM
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 text-lg">Adama M.</p>
                                        <p class="text-sm text-gray-500">CEO, Tech Startup</p>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600 mb-4">"Partager mon parcours avec les jeunes est une
                                    façon
                                    de redonner à ma communauté..."</p>
                                <div class="flex gap-2 flex-wrap">
                                    <span
                                        class="px-3 py-1 bg-orange-100 text-orange-700 text-xs rounded-full">Entrepreneuriat</span>
                                    <span
                                        class="px-3 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">Senegal</span>
                                    <span
                                        class="px-3 py-1 bg-green-100 text-green-700 text-xs rounded-full">Vérifié</span>
                                </div>
                            </div>
                            <div
                                class="bg-white rounded-2xl shadow-xl p-6 transform rotate-1 absolute bottom-0 left-0 w-56 opacity-70">
                                <div class="flex items-center gap-3 mb-3">
                                    <div
                                        class="w-10 h-10 bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl flex items-center justify-center text-white font-bold text-sm">
                                        FN
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 text-sm">Fatou N.</p>
                                        <p class="text-xs text-gray-500">Ingénieure Data</p>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <span
                                        class="px-2 py-1 bg-purple-100 text-purple-700 text-xs rounded-full">Tech</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section id="newsletter" class="py-16 bg-white">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mx-auto text-center" data-aos="fade-up">
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Reste informé</h3>
                <p class="text-gray-600 mb-8">
                    Inscris-toi à notre newsletter pour recevoir des conseils d'orientation et les dernières actualités.
                </p>
                <form action="{{ route('newsletter.subscribe') }}" method="POST"
                    class="flex flex-col sm:flex-row gap-4 max-w-md mx-auto">
                    @csrf
                    <input type="email" name="email" placeholder="Ton adresse email" required
                        class="flex-1 px-6 py-3 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <button type="submit"
                        class="px-8 py-3 bg-gradient-to-r from-primary-600 to-secondary-600 text-white font-semibold rounded-full hover:shadow-lg transition-all duration-300">
                        S'inscrire
                    </button>
                </form>
                <p class="text-xs text-gray-500 mt-4">
                    En t'inscrivant, tu acceptes notre <a href="{{ route('privacy-policy') }}"
                        class="underline">politique
                        de confidentialité</a>.
                </p>
            </div>
        </div>
    </section>
    <!-- Supabase OAuth Redirect Fix -->
    <script>
        (function () {
            if (window.location.hash && window.location.hash.includes('access_token=')) {
                if (window.location.pathname === '/' || window.location.pathname === '') {
                    const pendingProvider = "{{ session('oauth_provider') }}";
                    const pendingType = "{{ session('oauth_type') }}";

                    console.log("[Auth] Hash detected on home. Provider:", pendingProvider, "Type:", pendingType);

                    if (pendingProvider && pendingType === 'jeune') {
                        window.location.href = "/jeune/oauth/" + pendingProvider + "/callback" + window.location.hash;
                    } else if (pendingType === 'mentor') {
                        window.location.href = "/mentor/linkedin/callback" + window.location.hash;
                    }
                }
            }
        })();
    </script>

    <!-- App Download Marketing Modal -->
    <div x-show="showAppModal" x-cloak @click.away="showAppModal = false"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        style="display: none;" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div @click.stop
            class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90">



            <!-- Header with Gradient -->
            <div
                class="relative bg-gradient-to-br from-purple-600 via-pink-600 to-orange-500 px-8 pt-8 pb-8 text-white overflow-hidden flex-shrink-0">
                <!-- Close Button (sticky in header) -->
                <button @click="showAppModal = false"
                    class="absolute top-3 right-3 z-10 w-10 h-10 flex items-center justify-center bg-white/20 hover:bg-white/40 rounded-full transition-all duration-200">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <!-- Animated Background Circles -->
                <div
                    class="absolute top-0 right-0 w-32 h-32 bg-white/20 rounded-full -translate-y-1/2 translate-x-1/2 animate-pulse">
                </div>
                <div
                    class="absolute bottom-0 left-0 w-24 h-24 bg-white/20 rounded-full translate-y-1/2 -translate-x-1/2">
                </div>

                <!-- Emoji/Icon -->
                <div class="relative text-center mb-4">
                    <div class="inline-block animate-bounce">
                        <div class="text-7xl mb-2">🚀</div>
                    </div>
                </div>

                <!-- Title -->
                <h3 class="relative text-3xl font-bold text-center mb-3">
                    L'app arrive bientôt !
                </h3>

                <p class="relative text-center text-white/90 text-lg font-medium">
                    Aidons-nous à atteindre <span class="font-bold">500 utilisateurs</span>
                </p>
            </div>

            <!-- Content -->
            <div class="p-8 overflow-y-auto">
                <!-- Progress Section -->
                @php
                $totalUsers = $jeunesCount + $mentorsCount;
                $targetUsers = 500;
                $progressPercentage = min(($totalUsers / $targetUsers) * 100, 100);
                @endphp

                <div class="mb-8">
                    <div class="flex justify-between items-baseline mb-3">
                        <span
                            class="text-2xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                            {{ $totalUsers }} utilisateurs
                        </span>
                        <span class="text-gray-500 font-medium">Objectif : {{ $targetUsers }}</span>
                    </div>

                    <!-- Progress Bar -->
                    <div class="relative h-4 bg-gray-100 rounded-full overflow-hidden shadow-inner">
                        <div class="absolute inset-0 bg-gradient-to-r from-purple-500 via-pink-500 to-orange-500 rounded-full transition-all duration-1000 ease-out"
                            style="width: {{ $progressPercentage }}%">
                            <div
                                class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/30 to-white/0 animate-shimmer">
                            </div>
                        </div>
                    </div>

                    <p class="text-center text-sm text-gray-600 mt-3">
                        Plus que <span class="font-bold text-purple-600">{{ max($targetUsers - $totalUsers, 0) }}
                            utilisateurs</span> pour débloquer l'application mobile ! 🎉
                    </p>
                </div>

                <!-- Call to Action Text -->
                <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl p-6 mb-6 border border-purple-100">
                    <p class="text-gray-700 text-center leading-relaxed">
                        <span class="font-semibold text-purple-700">Partage Brillio</span> avec tes proches et tes amis
                        pour accélérer le lancement de l'application mobile !
                        <span class="inline-block animate-pulse">✨</span>
                    </p>
                </div>

                <!-- Share Buttons -->
                <div class="space-y-3">
                    <!-- Native Share -->
                    <button onclick="shareViaWebAPI()"
                        class="w-full flex items-center justify-center gap-3 px-6 py-4 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold rounded-xl hover:shadow-xl hover:scale-105 transition-all duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                        </svg>
                        Partager avec mes amis
                    </button>

                    <!-- Copy Link Button -->
                    <button onclick="copyShareLink()"
                        class="w-full flex items-center justify-center gap-3 px-6 py-4 bg-white border-2 border-purple-200 text-purple-700 font-semibold rounded-xl hover:bg-purple-50 hover:border-purple-300 transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        Copier le lien
                    </button>
                </div>

                <!-- Alternative: Web Platform -->
                <div class="mt-6 pt-6 border-t border-gray-200 text-center">
                    <p class="text-sm text-gray-600 mb-3">En attendant, utilise la version web 👇</p>
                    <a href="{{ route('auth.choice') }}"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Accéder à la plateforme web
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Sharing JavaScript -->
    <script>
        const shareMessage = "J'ai découvert cette nouvelle plateforme d'orientation de carrière et de mentorat et je suis sûr que ça va te plaire ! Inscris-toi et fais ton test de personnalité puis on en discute. 🚀";
        const shareUrl = window.location.origin;

        function shareViaWebAPI() {
            if (navigator.share) {
                navigator.share({
                    title: 'Brillio - Orientation professionnelle',
                    text: shareMessage,
                    url: shareUrl
                }).then(() => {
                    showToast('Merci d\'avoir partagé Brillio ! 🎉');
                }).catch((error) => {
                    if (error.name !== 'AbortError') {
                        console.log('Erreur de partage:', error);
                        fallbackShare();
                    }
                });
            } else {
                fallbackShare();
            }
        }

        function fallbackShare() {
            // Fallback: WhatsApp Web
            const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(shareMessage + ' ' + shareUrl)}`;
            window.open(whatsappUrl, '_blank');
        }

        function copyShareLink() {
            const textToCopy = shareMessage + '\n\n' + shareUrl;

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(textToCopy).then(() => {
                    showToast('Lien copié ! Partage-le avec tes amis 📋');
                }).catch(() => {
                    fallbackCopy(textToCopy);
                });
            } else {
                fallbackCopy(textToCopy);
            }
        }

        function fallbackCopy(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                showToast('Lien copié ! Partage-le avec tes amis 📋');
            } catch (err) {
                console.error('Erreur de copie:', err);
                showToast('Impossible de copier. Veuillez réessayer.', 'error');
            }
            document.body.removeChild(textarea);
        }

        function showToast(message, type = 'success') {
            // Create toast if window.showToast doesn't exist
            if (typeof window.showToast === 'function') {
                window.showToast(message, type);
            } else {
                alert(message);
            }
        }
    </script>

    <!-- Add shimmer animation CSS -->
    <style>
        @keyframes shimmer {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }

        .animate-shimmer {
            animation: shimmer 2s infinite;
        }
    </style>

</div> <!-- Close Alpine.js wrapper -->
@endsection