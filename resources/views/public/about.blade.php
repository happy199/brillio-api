@extends('layouts.public')

{{-- SEO Meta Tags --}}
<x-seo-meta page="about" />

@section('content')
<!-- Hero Section -->
<section class="gradient-hero pt-32 pb-20 relative overflow-hidden">
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 -left-40 w-96 h-96 bg-secondary-500/20 rounded-full blur-3xl"></div>
    </div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-3xl mx-auto text-center text-white">
            <span class="inline-block px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full text-sm font-medium mb-6"
                data-aos="fade-up">
                Notre histoire
            </span>
            <h1 class="text-4xl sm:text-5xl font-bold mb-6" data-aos="fade-up" data-aos-delay="100">
                Construire l'avenir de la jeunesse africaine
            </h1>
            <p class="text-xl text-white/90" data-aos="fade-up" data-aos-delay="200">
                Brillio est né d'un constat simple : trop de jeunes africains sont perdus face à leur avenir.
                Notre mission est de changer cela.
            </p>
        </div>
    </div>
</section>

<!-- Mission Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            <div data-aos="fade-right">
                <span class="text-primary-600 font-semibold text-sm uppercase tracking-wider">Notre mission</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mt-4 mb-6">
                    Démocratiser l'accès à l'orientation professionnelle en Afrique
                </h2>
                <p class="text-lg text-gray-600 mb-6">
                    <!-- TODO: Personnaliser le texte de mission -->
                    En Afrique, moins de 5% des jeunes ont accès à un conseiller d'orientation. Brillio veut
                    combler ce fossé en offrant à chaque jeune africain, où qu'il soit, les outils et
                    l'accompagnement nécessaires pour construire son avenir professionnel.
                </p>
                <p class="text-lg text-gray-600 mb-8">
                    Nous croyons que chaque jeune mérite de connaître ses talents, d'explorer les possibilités
                    qui s'offrent à lui et d'être inspiré par des modèles qui lui ressemblent.
                </p>

                <!-- Mission pillars -->
                <div class="space-y-4">
                    <div class="flex items-start space-x-4">
                        <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Accessibilité</h4>
                            <p class="text-gray-600">Gratuit et disponible pour tous, même sans connexion permanente.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-4">
                        <div
                            class="w-10 h-10 bg-secondary-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-secondary-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Pertinence locale</h4>
                            <p class="text-gray-600">Des contenus et conseils adaptés au contexte africain.</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-4">
                        <div class="w-10 h-10 bg-accent-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Innovation</h4>
                            <p class="text-gray-600">L'IA au service de l'orientation personnalisée.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div data-aos="fade-left" class="relative group">
                <div
                    class="absolute inset-0 bg-gradient-to-br from-primary-600/10 to-secondary-600/10 rounded-3xl -rotate-3 group-hover:rotate-0 transition-transform duration-500">
                </div>
                <img src="{{ asset('images/about/mission-graduation.png') }}" alt="Mission Brillio - Réussite"
                    class="relative z-10 w-full h-full object-cover rounded-3xl shadow-2xl transition-all duration-500 group-hover:scale-[1.01]">

                <!-- Brillio Branding Overlay -->
                <div
                    class="absolute bottom-6 right-6 z-20 bg-white/95 backdrop-blur-md px-4 py-2 rounded-2xl shadow-xl flex items-center gap-3 border border-white/20">
                    <div
                        class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-600 to-secondary-600 flex items-center justify-center shadow-lg shadow-primary-500/30">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                            </path>
                        </svg>
                    </div>
                    <span class="text-gray-900 font-bold text-sm tracking-tight">Brillio</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto text-center mb-16" data-aos="fade-up">
            <span class="text-primary-600 font-semibold text-sm uppercase tracking-wider">Nos valeurs</span>
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mt-4 mb-6">
                Ce qui nous guide au quotidien
            </h2>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Value 1 -->
            <div class="bg-white rounded-2xl p-8 text-center shadow-sm" data-aos="fade-up" data-aos-delay="100">
                <div
                    class="w-16 h-16 mx-auto mb-6 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Bienveillance</h3>
                <p class="text-gray-600">
                    <!-- TODO: Personnaliser la description -->
                    Nous accompagnons chaque jeune avec empathie et encouragement, sans jugement.
                </p>
            </div>

            <!-- Value 2 -->
            <div class="bg-white rounded-2xl p-8 text-center shadow-sm" data-aos="fade-up" data-aos-delay="200">
                <div
                    class="w-16 h-16 mx-auto mb-6 bg-gradient-to-br from-secondary-500 to-secondary-600 rounded-2xl flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Intégrité</h3>
                <p class="text-gray-600">
                    <!-- TODO: Personnaliser la description -->
                    Nous donnons des conseils honnêtes et transparents, même quand c'est difficile.
                </p>
            </div>

            <!-- Value 3 -->
            <div class="bg-white rounded-2xl p-8 text-center shadow-sm" data-aos="fade-up" data-aos-delay="300">
                <div
                    class="w-16 h-16 mx-auto mb-6 bg-gradient-to-br from-accent-500 to-accent-600 rounded-2xl flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Inclusion</h3>
                <p class="text-gray-600">
                    <!-- TODO: Personnaliser la description -->
                    Nous servons tous les jeunes, peu importe leur origine, leur genre ou leur situation.
                </p>
            </div>

            <!-- Value 4 -->
            <div class="bg-white rounded-2xl p-8 text-center shadow-sm" data-aos="fade-up" data-aos-delay="400">
                <div
                    class="w-16 h-16 mx-auto mb-6 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Impact</h3>
                <p class="text-gray-600">
                    <!-- TODO: Personnaliser la description -->
                    Nous mesurons notre succès par le nombre de jeunes que nous aidons à trouver leur voie.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Vision / Objectives Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto text-center mb-16" data-aos="fade-up">
            <span class="text-primary-600 font-semibold text-sm uppercase tracking-wider">Notre vision</span>
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mt-4 mb-6">
                Nos objectifs pour l'avenir
            </h2>
            <p class="text-xl text-gray-600">
                <!-- TODO: Personnaliser la vision -->
                Nous voulons devenir la référence de l'orientation professionnelle en Afrique francophone
                d'ici 2030.
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <!-- Objective 1 -->
            <div class="relative" data-aos="fade-up" data-aos-delay="100">
                <div
                    class="absolute -top-4 -left-4 w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center">
                    <span class="text-2xl font-bold text-primary-600">1M</span>
                </div>
                <div class="bg-gray-50 rounded-2xl p-8 pt-16">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">1 million de jeunes</h3>
                    <p class="text-gray-600">
                        <!-- TODO: Personnaliser l'objectif -->
                        Accompagner 1 million de jeunes africains dans leur orientation d'ici 2027.
                    </p>
                </div>
            </div>

            <!-- Objective 2 -->
            <div class="relative" data-aos="fade-up" data-aos-delay="200">
                <div
                    class="absolute -top-4 -left-4 w-16 h-16 bg-secondary-100 rounded-full flex items-center justify-center">
                    <span class="text-2xl font-bold text-secondary-600">25</span>
                </div>
                <div class="bg-gray-50 rounded-2xl p-8 pt-16">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">25 pays africains</h3>
                    <p class="text-gray-600">
                        <!-- TODO: Personnaliser l'objectif -->
                        Être présent dans 25 pays africains avec des contenus localisés.
                    </p>
                </div>
            </div>

            <!-- Objective 3 -->
            <div class="relative" data-aos="fade-up" data-aos-delay="300">
                <div
                    class="absolute -top-4 -left-4 w-16 h-16 bg-accent-100 rounded-full flex items-center justify-center">
                    <span class="text-2xl font-bold text-accent-600">5K</span>
                </div>
                <div class="bg-gray-50 rounded-2xl p-8 pt-16">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">5 000 mentors</h3>
                    <p class="text-gray-600">
                        <!-- TODO: Personnaliser l'objectif -->
                        Constituer un réseau de 5 000 mentors africains dans tous les secteurs.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto text-center mb-16" data-aos="fade-up">
            <span class="text-primary-600 font-semibold text-sm uppercase tracking-wider">L'équipe</span>
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mt-4 mb-6">
                Les personnes derrière Brillio
            </h2>
            <p class="text-xl text-gray-600">
                <!-- TODO: Personnaliser la description de l'équipe -->
                Une équipe passionnée par l'éducation et l'avenir de la jeunesse africaine.
            </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Team Member 1: Happy Tidjani -->
            <div class="bg-white rounded-2xl p-6 text-center shadow-sm hover:shadow-md transition-shadow"
                data-aos="fade-up" data-aos-delay="100">
                <div class="w-24 h-24 mx-auto mb-4 relative">
                    <img src="{{ asset('images/team/happy.jpg') }}" alt="Happy Tidjani"
                        class="w-full h-full object-cover rounded-full shadow-md border-2 border-primary-50">
                </div>
                <h3 class="text-lg font-bold text-gray-900 leading-tight">Happy Tidjani</h3>
                <p class="text-primary-600 text-sm font-medium mb-3">Fondateur & CEO</p>
                <div class="flex justify-center space-x-3 mt-4">
                    <a href="https://www.linkedin.com/in/happytidjani/" target="_blank"
                        class="text-gray-400 hover:text-primary-600 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                        </svg>
                    </a>
                    <a href="https://www.facebook.com/happy.tidjani.1" target="_blank"
                        class="text-gray-400 hover:text-blue-600 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z" />
                        </svg>
                    </a>
                    <a href="http://happytidjani.com/" target="_blank"
                        class="text-gray-400 hover:text-accent-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9-9H3m9 9V3m0 18v-9" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Team Member 2: Emmanuella Ahouanse -->
            <div class="bg-white rounded-2xl p-6 text-center shadow-sm hover:shadow-md transition-shadow"
                data-aos="fade-up" data-aos-delay="200">
                <div class="w-24 h-24 mx-auto mb-4 relative">
                    <img src="{{ asset('images/team/emmanuella.jpeg') }}" alt="Emmanuella Ahouanse"
                        class="w-full h-full object-cover rounded-full shadow-md border-2 border-primary-50">
                </div>
                <h3 class="text-lg font-bold text-gray-900 leading-tight">Emmanuella Ahouanse</h3>
                <p class="text-primary-600 text-sm font-medium mb-3">Co-fondatrice & COO</p>
                <div class="flex justify-center space-x-3 mt-4">
                    <a href="https://www.linkedin.com/in/ahouanse-mahutin-emmanuella-b31947205/" target="_blank"
                        class="text-gray-400 hover:text-primary-600 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                        </svg>
                    </a>
                    <a href="https://www.facebook.com/emmanuella.ahouanse.3" target="_blank"
                        class="text-gray-400 hover:text-blue-600 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Team Member 3: Melvina Kouton -->
            <div class="bg-white rounded-2xl p-6 text-center shadow-sm hover:shadow-md transition-shadow"
                data-aos="fade-up" data-aos-delay="300">
                <div class="w-24 h-24 mx-auto mb-4 relative">
                    <img src="{{ asset('images/team/melvina.jpg') }}" alt="Melvina Kouton"
                        class="w-full h-full object-cover rounded-full shadow-md border-2 border-primary-50">
                </div>
                <h3 class="text-lg font-bold text-gray-900 leading-tight">Melvina Kouton</h3>
                <p class="text-primary-600 text-sm font-medium mb-3">Conseillère Juridique</p>
                <div class="flex justify-center space-x-3 mt-4">
                    <a href="https://www.facebook.com/Melvina.Kouton" target="_blank"
                        class="text-gray-400 hover:text-blue-600 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Team Member 4: Ornella Vidjannagni -->
            <div class="bg-white rounded-2xl p-6 text-center shadow-sm hover:shadow-md transition-shadow"
                data-aos="fade-up" data-aos-delay="400">
                <div class="w-24 h-24 mx-auto mb-4 relative">
                    <img src="{{ asset('images/team/ornella.jpeg') }}" alt="Ornella Vidjannagni"
                        class="w-full h-full object-cover rounded-full shadow-md border-2 border-primary-50">
                </div>
                <h3 class="text-lg font-bold text-gray-900 leading-tight">Ornella Vidjannagni</h3>
                <p class="text-primary-600 text-sm font-medium mb-3 whitespace-nowrap">Responsable Produit & UX/UI</p>
                <div class="flex justify-center space-x-3 mt-4">
                    <a href="https://www.linkedin.com/in/ornellavidjannagni/" target="_blank"
                        class="text-gray-400 hover:text-primary-600 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                        </svg>
                    </a>
                    <a href="https://www.facebook.com/profile.php?id=100008320955940" target="_blank"
                        class="text-gray-400 hover:text-blue-600 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z" />
                        </svg>
                    </a>
                    <a href="https://www.designbyornella.com/" target="_blank"
                        class="text-gray-400 hover:text-accent-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9-9H3m9 9V3m0 18v-9" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Team Member 5: Jérémie Atcho -->
            <div class="bg-white rounded-2xl p-6 text-center shadow-sm hover:shadow-md transition-shadow"
                data-aos="fade-up" data-aos-delay="100">
                <div class="w-24 h-24 mx-auto mb-4 relative">
                    <img src="{{ asset('images/team/jeremie.jpeg') }}" alt="Jérémie Atcho"
                        class="w-full h-full object-cover rounded-full shadow-md border-2 border-primary-50">
                </div>
                <h3 class="text-lg font-bold text-gray-900 leading-tight">Jérémie Atcho</h3>
                <p class="text-primary-600 text-sm font-medium mb-3">Consultant Com Digitale</p>
                <div class="flex justify-center space-x-3 mt-4">
                    <a href="https://www.linkedin.com/in/j%C3%A9r%C3%A9mie-atcho-30252a213/" target="_blank"
                        class="text-gray-400 hover:text-primary-600 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                        </svg>
                    </a>
                    <a href="https://www.facebook.com/jeremie.dieudonne.atcho" target="_blank"
                        class="text-gray-400 hover:text-blue-600 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Team Member 6: Aminath Tidjani -->
            <div class="bg-white rounded-2xl p-6 text-center shadow-sm hover:shadow-md transition-shadow"
                data-aos="fade-up" data-aos-delay="200">
                <div class="w-24 h-24 mx-auto mb-4 relative">
                    <img src="{{ asset('images/team/aminath.jpeg') }}" alt="Aminath Tidjani"
                        class="w-full h-full object-cover rounded-full shadow-md border-2 border-primary-50">
                </div>
                <h3 class="text-lg font-bold text-gray-900 leading-tight">Aminath Tidjani</h3>
                <p class="text-primary-600 text-sm font-medium mb-3">Recherche Scientifique & Impact</p>
                <div class="flex justify-center space-x-3 mt-4">
                    <a href="https://www.linkedin.com/in/aminathf%C3%A8mietidjani/" target="_blank"
                        class="text-gray-400 hover:text-primary-600 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                        </svg>
                    </a>
                    <a href="https://www.facebook.com/aminath.tidjani.58" target="_blank"
                        class="text-gray-400 hover:text-blue-600 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Team Member 7: Conseiller Pédagogique -->
            <div class="bg-white rounded-2xl p-6 text-center shadow-sm hover:shadow-md transition-shadow"
                data-aos="fade-up" data-aos-delay="300">
                <div
                    class="w-24 h-24 mx-auto mb-4 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center text-2xl font-extrabold shadow-inner">
                    CP
                </div>
                <h3 class="text-lg font-bold text-gray-400 leading-tight italic">À venir</h3>
                <p class="text-gray-400 text-sm font-medium mb-3">Conseiller Pédagogique</p>
                <div class="flex justify-center space-x-3 mt-4 opacity-30">
                    <a href="https://linkedin.com" target="_blank" class="text-gray-400">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                        </svg>
                    </a>
                    <a href="https://facebook.com" target="_blank" class="text-gray-400">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Team Member 8: Jude Dossou -->
            <div class="bg-white rounded-2xl p-6 text-center shadow-sm hover:shadow-md transition-shadow"
                data-aos="fade-up" data-aos-delay="400">
                <div class="w-24 h-24 mx-auto mb-4 relative">
                    <img src="{{ asset('images/team/jude.jpeg') }}" alt="Jude Dossou"
                        class="w-full h-full object-cover rounded-full shadow-md border-2 border-primary-50">
                </div>
                <h3 class="text-lg font-bold text-gray-900 leading-tight">Jude Dossou</h3>
                <p class="text-primary-600 text-sm font-medium mb-3">Designer Graphique</p>
                <div class="flex justify-center space-x-3 mt-4">
                    <a href="https://www.linkedin.com/in/jude-dossou-a48140225/" target="_blank"
                        class="text-gray-400 hover:text-primary-600 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Partners Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto text-center mb-16" data-aos="fade-up">
            <span class="text-primary-600 font-semibold text-sm uppercase tracking-wider">Nos partenaires</span>
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mt-4 mb-6">
                Ils soutiennent notre mission
            </h2>
        </div>

        <div class="relative overflow-hidden mb-16" data-aos="fade-up">
            @if($partners->count() > 0)
            <div class="flex {{ $partners->count() >= 6 ? 'animate-scroll' : 'justify-center' }} whitespace-nowrap">
                <!-- First set of logos -->
                <div class="flex space-x-12 items-center mx-6">
                    @foreach($partners as $partner)
                    <div
                        class="flex-shrink-0 bg-gray-50 rounded-xl p-4 flex items-center justify-center w-48 h-24 shadow-sm hover:shadow-md transition-shadow grayscale hover:grayscale-0">
                        <img src="{{ $partner->logo_url }}" alt="{{ $partner->name }}"
                            class="max-w-full max-h-full object-contain">
                    </div>
                    @endforeach
                </div>

                @if($partners->count() >= 6)
                <!-- Duplicate set for infinite loop (only if enough items) -->
                <div class="flex space-x-12 items-center mx-6">
                    @foreach($partners as $partner)
                    <div
                        class="flex-shrink-0 bg-gray-50 rounded-xl p-4 flex items-center justify-center w-48 h-24 shadow-sm hover:shadow-md transition-shadow grayscale hover:grayscale-0">
                        <img src="{{ $partner->logo_url }}" alt="{{ $partner->name }}"
                            class="max-w-full max-h-full object-contain">
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @else
            <div class="text-center text-gray-500 italic">
                <p>Devenez notre premier partenaire institutionnel !</p>
            </div>
            @endif
        </div>

        <!-- Organization Value Proposition Block -->
        <div class="mt-24 mb-16" data-aos="fade-up">
            <div
                class="bg-gradient-to-br from-primary-900 to-primary-800 rounded-3xl overflow-hidden shadow-2xl relative">
                <!-- Decorative background elements -->
                <div
                    class="absolute top-0 right-0 -mt-20 -mr-20 w-64 h-64 bg-primary-700 rounded-full opacity-20 blur-3xl">
                </div>
                <div
                    class="absolute bottom-0 left-0 -mb-20 -ml-20 w-64 h-64 bg-primary-600 rounded-full opacity-20 blur-3xl">
                </div>

                <div class="px-8 py-16 md:px-16 md:py-20 relative z-10">
                    <div class="text-center mb-16">
                        <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-6">Pourquoi rejoindre Brillio en
                            tant qu'organisation ?</h2>
                        <p class="text-primary-100 text-lg max-w-2xl mx-auto">Offrez à vos membres un accompagnement
                            d'élite et transformez votre impact grâce à une plateforme conçue pour l'excellence
                            institutionnelle.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-16">
                        <!-- Feature 1 -->
                        <div
                            class="bg-white/10 backdrop-blur-md p-6 rounded-2xl border border-white/10 hover:bg-white/15 transition-all group">
                            <div
                                class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-white font-bold mb-2">Suivi en temps réel</h3>
                            <p class="text-primary-100 text-sm">Monitorez la progression de vos jeunes et l'activité de
                                vos mentors sur un tableau de bord unifié.</p>
                        </div>

                        <!-- Feature 2 -->
                        <div
                            class="bg-white/10 backdrop-blur-md p-6 rounded-2xl border border-white/10 hover:bg-white/15 transition-all group">
                            <div
                                class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-white font-bold mb-2">Gestion des Crédits</h3>
                            <p class="text-primary-100 text-sm">Distribuez facilement des crédits de mentorat à vos
                                membres pour financer leurs séances de coaching.</p>
                        </div>

                        <!-- Feature 3 -->
                        <div
                            class="bg-white/10 backdrop-blur-md p-6 rounded-2xl border border-white/10 hover:bg-white/15 transition-all group">
                            <div
                                class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-7.714 2.143L11 21l-2.286-6.857L1 12l7.714-2.143L11 3z">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-white font-bold mb-2">Marque Blanche</h3>
                            <p class="text-primary-100 text-sm">Proposez une expérience 100% à vos couleurs avec votre
                                propre logo, domaine et identité visuelle.</p>
                        </div>

                        <!-- Feature 4 -->
                        <div
                            class="bg-white/10 backdrop-blur-md p-6 rounded-2xl border border-white/10 hover:bg-white/15 transition-all group">
                            <div
                                class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-white font-bold mb-2">Rapports d'Impact</h3>
                            <p class="text-primary-100 text-sm">Générez des rapports détaillés pour mesurer concrètement
                                l'évolution et la réussite de vos programmes.</p>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-center items-center gap-6">
                        <a href="{{ route('organization.register') }}"
                            class="px-10 py-5 bg-white text-primary-900 font-extrabold rounded-full hover:bg-primary-50 hover:shadow-xl transition-all transform hover:-translate-y-1 text-center w-full sm:w-auto">
                            Rejoindre en tant qu'organisation
                        </a>
                        <a href="{{ route('contact') }}"
                            class="px-10 py-5 border-2 border-white/30 text-white font-bold rounded-full hover:bg-white/10 transition-all text-center w-full sm:w-auto group">
                            Demander une démo
                            <svg class="w-5 h-5 ml-2 inline-block transition-transform group-hover:translate-x-1"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 8l4 4m0 0l-4 4m4-4H3">
                                </path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <style>
            @keyframes scroll {
                0% {
                    transform: translateX(0);
                }

                100% {
                    transform: translateX(-50%);
                }
            }

            .animate-scroll {
                display: flex;
                width: max-content;
                animation: scroll 30s linear infinite;
            }

            .animate-scroll:hover {
                animation-play-state: paused;
            }
        </style>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 gradient-hero relative overflow-hidden">
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 -left-40 w-96 h-96 bg-secondary-500/20 rounded-full blur-3xl"></div>
    </div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-3xl mx-auto text-center text-white">
            <h2 class="text-3xl sm:text-4xl font-bold mb-6" data-aos="fade-up">
                Rejoins le mouvement
            </h2>
            <p class="text-xl text-white/90 mb-8" data-aos="fade-up" data-aos-delay="100">
                Télécharge Brillio et fais partie des milliers de jeunes africains qui construisent leur avenir.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4" data-aos="fade-up" data-aos-delay="200">
                <a href="{{ route('home') }}#telecharger"
                    class="px-8 py-4 bg-white text-primary-600 font-bold rounded-full hover:bg-gray-100 hover:shadow-xl transition-all duration-300">
                    Télécharger l'app
                </a>
                <a href="{{ route('contact') }}"
                    class="px-8 py-4 border-2 border-white/50 text-white font-semibold rounded-full hover:bg-white/10 transition-all duration-300">
                    Nous contacter
                </a>
            </div>
        </div>
    </div>
</section>
@endsection