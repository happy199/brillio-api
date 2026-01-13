@extends('layouts.public')

@section('title', 'Brillio - Ton avenir, ton choix | Orientation professionnelle pour jeunes africains')
@section('meta_description', 'Brillio est la plateforme d\'orientation professionnelle dédiée aux jeunes africains. Test de personnalité, mentoring, conseils IA et bien plus.')

@section('content')
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
                    <span class="inline-block px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full text-sm font-medium mb-6">
                        <!-- TODO: Ajouter badge promotionnel -->
                        Nouveau : Test de personnalité MBTI gratuit
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
                        <a href="#telecharger"
                            class="px-8 py-4 border-2 border-white/50 text-white font-semibold rounded-full hover:bg-white/10 transition-all duration-300 text-center">
                            Télécharger l'app mobile
                        </a>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-6">
                        <div>
                            <p class="text-3xl font-bold">10K+</p>
                            <p class="text-white/70 text-sm">Jeunes accompagnés</p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold">500+</p>
                            <p class="text-white/70 text-sm">Mentors actifs</p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold">15+</p>
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
                                        <p class="text-gray-600 text-sm mt-2">Capture d'écran de l'application</p>
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
                        <div class="absolute -bottom-6 -left-6 w-20 h-20 bg-secondary-400 rounded-full opacity-80"></div>
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

    <!-- Trusted By / Partners Section -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-gray-500 mb-8 font-medium">ILS NOUS FONT CONFIANCE</p>
            <div class="flex flex-wrap justify-center items-center gap-8 md:gap-16 opacity-60">
                <!-- TODO: Ajouter logos des partenaires -->
                <div class="h-12 w-32 bg-gray-300 rounded flex items-center justify-center text-gray-500 text-sm">Partenaire
                    1</div>
                <div class="h-12 w-32 bg-gray-300 rounded flex items-center justify-center text-gray-500 text-sm">Partenaire
                    2</div>
                <div class="h-12 w-32 bg-gray-300 rounded flex items-center justify-center text-gray-500 text-sm">Partenaire
                    3</div>
                <div class="h-12 w-32 bg-gray-300 rounded flex items-center justify-center text-gray-500 text-sm">Partenaire
                    4</div>
                <div class="h-12 w-32 bg-gray-300 rounded flex items-center justify-center text-gray-500 text-sm">Partenaire
                    5</div>
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

                <div class="bg-orange-50 border border-orange-100 rounded-2xl p-8" data-aos="fade-up" data-aos-delay="200">
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

                <div class="bg-yellow-50 border border-yellow-100 rounded-2xl p-8" data-aos="fade-up" data-aos-delay="300">
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
                            <p class="text-4xl font-bold">100%</p>
                            <p class="text-white/80 text-sm">Gratuit</p>
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
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Test de personnalité MBTI</h3>
                    <p class="text-gray-600 mb-4">
                        Découvre ton type de personnalité parmi les 16 profils MBTI et comprends tes forces,
                        tes motivations et les métiers qui te correspondent.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            32 questions scientifiques
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Résultats en français
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
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
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Réponses personnalisées
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Contexte africain
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
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
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Parcours inspirants
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Roadmaps détaillées
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
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
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Stockage sécurisé
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Accès hors-ligne
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
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
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Suggestions IA
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Suivi de progression
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Données protégées
                        </li>
                    </ul>
                </div>

                <!-- Feature 6: Gratuit -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-shadow duration-300"
                    data-aos="fade-up" data-aos-delay="600">
                    <div
                        class="w-14 h-14 bg-gradient-to-br from-pink-500 to-pink-600 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">100% Gratuit</h3>
                    <p class="text-gray-600 mb-4">
                        Toutes les fonctionnalités sont gratuites. Notre mission est d'aider le plus
                        grand nombre de jeunes africains.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Pas d'abonnement
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Pas de publicité
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Accès illimité
                        </li>
                    </ul>
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
                        Disponible gratuitement sur iOS et Android. Crée ton compte en quelques secondes.
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
                        Réponds aux 32 questions du test MBTI pour découvrir ton type de personnalité.
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
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        @endfor
                    </div>
                    <p class="text-gray-600 mb-6 italic">
                        <!-- TODO: Ajouter vrai témoignage -->
                        "Grâce à Brillio, j'ai découvert que j'étais ENFJ et que les métiers du marketing me correspondaient
                        parfaitement. Aujourd'hui je suis en stage dans une agence !"
                    </p>
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center text-gray-500">
                            <!-- TODO: Ajouter photo -->
                            AM
                        </div>
                        <div class="ml-4">
                            <p class="font-semibold text-gray-900">Aminata M.</p>
                            <p class="text-sm text-gray-500">Dakar, Sénégal</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 2 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm" data-aos="fade-up" data-aos-delay="200">
                    <div class="flex items-center mb-4">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        @endfor
                    </div>
                    <p class="text-gray-600 mb-6 italic">
                        <!-- TODO: Ajouter vrai témoignage -->
                        "Le chatbot IA est incroyable ! Il m'a donné des conseils super adaptés à la situation au Cameroun.
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
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        @endfor
                    </div>
                    <p class="text-gray-600 mb-6 italic">
                        <!-- TODO: Ajouter vrai témoignage -->
                        "Les profils de mentors m'ont vraiment inspiré. Voir des parcours de professionnels africains qui
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
                                Pas envie de télécharger ? Utilise notre plateforme web pour passer le test MBTI, discuter
                                avec notre IA et explorer les parcours de mentors africains.
                            </p>
                            <div class="flex flex-col sm:flex-row gap-4">
                                <a href="{{ route('auth.choice') }}"
                                    class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-gradient-to-r from-primary-600 to-secondary-600 text-white font-bold rounded-full hover:shadow-xl transition-all duration-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    Commencer gratuitement
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
                                <h4 class="font-bold text-gray-900">Test MBTI</h4>
                                <p class="text-sm text-gray-500">Decouvre ton profil</p>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-sm">
                                <div class="w-12 h-12 bg-secondary-100 rounded-xl flex items-center justify-center mb-4">
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
                                <h4 class="font-bold text-gray-900">100% Gratuit</h4>
                                <p class="text-sm text-gray-500">Sans engagement</p>
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
                    Pret a construire ton avenir ?
                </h2>
                <p class="text-xl text-white/90 mb-10" data-aos="fade-up" data-aos-delay="100">
                    Telecharge Brillio ou utilise la plateforme web pour commencer ton voyage vers le metier de tes reves.
                </p>

                <!-- Download Buttons -->
                <div class="flex flex-col sm:flex-row justify-center gap-4 mb-12" data-aos="fade-up" data-aos-delay="200">
                    <!-- App Store -->
                    <a href="#"
                        class="flex items-center justify-center space-x-3 bg-black text-white rounded-xl px-8 py-4 hover:bg-gray-900 transition-colors">
                        <svg class="w-10 h-10" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z" />
                        </svg>
                        <div class="text-left">
                            <p class="text-xs text-gray-400">Télécharger sur</p>
                            <p class="text-lg font-semibold">App Store</p>
                        </div>
                    </a>
                    <!-- Google Play -->
                    <a href="#"
                        class="flex items-center justify-center space-x-3 bg-black text-white rounded-xl px-8 py-4 hover:bg-gray-900 transition-colors">
                        <svg class="w-10 h-10" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.6 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.16,10.81C20.5,11.08 20.75,11.5 20.75,12C20.75,12.5 20.53,12.9 20.18,13.18L17.89,14.5L15.39,12L17.89,9.5L20.16,10.81M6.05,2.66L16.81,8.88L14.54,11.15L6.05,2.66Z" />
                        </svg>
                        <div class="text-left">
                            <p class="text-xs text-gray-400">Disponible sur</p>
                            <p class="text-lg font-semibold">Google Play</p>
                        </div>
                    </a>
                </div>

                <!-- Web platform CTA -->
                <div class="mt-8 pt-8 border-t border-white/20" data-aos="fade-up" data-aos-delay="300">
                    <p class="text-white/80 mb-4">Ou accede directement depuis ton navigateur</p>
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
                            Devenez mentor et inspirez la prochaine generation
                        </h2>
                        <p class="text-lg text-gray-600 mb-6">
                            Vous etes un professionnel africain qui a reussi ? Partagez votre parcours, vos conseils et
                            inspirez des milliers de jeunes a suivre leurs reves.
                        </p>
                        <ul class="space-y-4 mb-8">
                            <li class="flex items-start gap-3">
                                <div
                                    class="w-6 h-6 bg-orange-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">Gagnez en visibilite</p>
                                    <p class="text-sm text-gray-500">Renforcez votre personal branding aupres de la jeunesse
                                        africaine</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <div
                                    class="w-6 h-6 bg-orange-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">Contribuez a l'Afrique</p>
                                    <p class="text-sm text-gray-500">Aidez a former les leaders de demain sur le continent
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
                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded-full">Marketing</span>
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
                                <p class="text-sm text-gray-600 mb-4">"Partager mon parcours avec les jeunes est une facon
                                    de redonner a ma communaute..."</p>
                                <div class="flex gap-2 flex-wrap">
                                    <span
                                        class="px-3 py-1 bg-orange-100 text-orange-700 text-xs rounded-full">Entrepreneuriat</span>
                                    <span class="px-3 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">Senegal</span>
                                    <span class="px-3 py-1 bg-green-100 text-green-700 text-xs rounded-full">Verifie</span>
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
                                        <p class="text-xs text-gray-500">Ingenieure Data</p>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <span class="px-2 py-1 bg-purple-100 text-purple-700 text-xs rounded-full">Tech</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mx-auto text-center" data-aos="fade-up">
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Reste informé</h3>
                <p class="text-gray-600 mb-8">
                    Inscris-toi à notre newsletter pour recevoir des conseils d'orientation et les dernières actualités.
                </p>
                <form class="flex flex-col sm:flex-row gap-4 max-w-md mx-auto">
                    <input type="email" placeholder="Ton adresse email"
                        class="flex-1 px-6 py-3 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <button type="submit"
                        class="px-8 py-3 bg-gradient-to-r from-primary-600 to-secondary-600 text-white font-semibold rounded-full hover:shadow-lg transition-all duration-300">
                        S'inscrire
                    </button>
                </form>
                <p class="text-xs text-gray-500 mt-4">
                    En t'inscrivant, tu acceptes notre <a href="{{ route('privacy-policy') }}" class="underline">politique
                        de confidentialité</a>.
                </p>
            </div>
        </div>
    </section>
@endsection