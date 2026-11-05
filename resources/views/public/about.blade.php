@extends('layouts.public')

@push('seo')
    <x-seo-meta page="about" />
@endpush

@section('content')
<!-- Hero Section -->
<section class="bg-slate-900 text-white pt-32 pb-20 relative overflow-hidden border-b border-slate-800">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-3xl mx-auto text-center text-white">
            <span class="inline-block px-3.5 py-1.5 bg-slate-800 border border-slate-700 rounded-full text-xs font-semibold text-slate-300 mb-6"
                data-aos="fade-up">
                Notre histoire
            </span>
            <h1 class="text-4xl sm:text-5xl font-extrabold mb-6 tracking-tight text-white" data-aos="fade-up" data-aos-delay="100">
                Construire l'avenir de la jeunesse africaine
            </h1>
            <p class="text-lg sm:text-xl text-slate-300 leading-relaxed font-normal" data-aos="fade-up" data-aos-delay="200">
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
                    class="absolute inset-0 bg-slate-100 rounded-3xl -rotate-2 group-hover:rotate-0 transition-transform duration-500 border border-slate-200">
                </div>
                <img src="{{ asset('images/about/mission-graduation.png') }}" alt="Mission Brillio - Réussite"
                    class="relative z-10 w-full h-full object-cover rounded-3xl shadow-xl transition-all duration-500 group-hover:scale-[1.01] border border-slate-200">

                <!-- Brillio Branding Overlay -->
                <div
                    class="absolute bottom-6 right-6 z-20 bg-white/95 backdrop-blur-md px-4 py-2.5 rounded-2xl shadow-lg flex items-center border border-slate-200">
                    <img src="{{ asset('images/logo/brillio-logo.png') }}" alt="Brillio" width="100" height="39" class="h-7 w-auto object-contain">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="py-20 bg-slate-50/60 border-b border-slate-200/80">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto text-center mb-16" data-aos="fade-up">
            <span class="text-brand-600 font-bold text-xs uppercase tracking-wider">Nos valeurs fondamentales</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mt-3 mb-4 tracking-tight">
                Ce qui nous guide au quotidien
            </h2>
            <p class="text-base sm:text-lg text-slate-600">
                Une boussole éthique pour accompagner la jeunesse africaine vers l'autonomie et l'excellence.
            </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Value 1 -->
            <div class="bg-white rounded-3xl p-8 text-center shadow-2xs border border-slate-200/90 card-interactive group" data-aos="fade-up" data-aos-delay="100">
                <div
                    class="w-14 h-14 mx-auto mb-6 bg-brand-50 text-brand-600 rounded-2xl flex items-center justify-center border border-brand-100 group-hover:scale-110 group-hover:rotate-6 transition-all duration-300">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2 group-hover:text-brand-600 transition-colors">Bienveillance</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Nous accompagnons chaque jeune avec empathie et encouragement, sans jugement.
                </p>
            </div>

            <!-- Value 2 -->
            <div class="bg-white rounded-3xl p-8 text-center shadow-2xs border border-slate-200/90 card-interactive group" data-aos="fade-up" data-aos-delay="200">
                <div
                    class="w-14 h-14 mx-auto mb-6 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center border border-emerald-100 group-hover:scale-110 group-hover:-rotate-6 transition-all duration-300">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2 group-hover:text-emerald-700 transition-colors">Intégrité</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Nous donnons des conseils honnêtes et transparents, même quand c'est difficile.
                </p>
            </div>

            <!-- Value 3 -->
            <div class="bg-white rounded-3xl p-8 text-center shadow-2xs border border-slate-200/90 card-interactive group" data-aos="fade-up" data-aos-delay="300">
                <div
                    class="w-14 h-14 mx-auto mb-6 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center border border-amber-100 group-hover:scale-110 group-hover:rotate-6 transition-all duration-300">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2 group-hover:text-amber-700 transition-colors">Inclusion</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Nous servons tous les jeunes, peu importe leur origine, leur genre ou leur situation.
                </p>
            </div>

            <!-- Value 4 -->
            <div class="bg-white rounded-3xl p-8 text-center shadow-2xs border border-slate-200/90 card-interactive group" data-aos="fade-up" data-aos-delay="400">
                <div
                    class="w-14 h-14 mx-auto mb-6 bg-indigo-50 text-brand-600 rounded-2xl flex items-center justify-center border border-indigo-100 group-hover:scale-110 group-hover:-rotate-6 transition-all duration-300">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2 group-hover:text-brand-600 transition-colors">Impact</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Nous mesurons notre succès par le nombre de jeunes que nous aidons à trouver leur voie.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Vision / Objectives Section -->
<section class="py-20 bg-white border-b border-slate-200/80">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto text-center mb-16" data-aos="fade-up">
            <span class="text-brand-600 font-bold text-xs uppercase tracking-wider">Notre vision d'avenir</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mt-3 mb-4 tracking-tight">
                Nos objectifs à horizon 2030
            </h2>
            <p class="text-lg text-slate-600">
                Nous voulons devenir la référence de l'orientation professionnelle en Afrique francophone d'ici 2030.
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <!-- Objective 1 -->
            <div class="relative pt-6" data-aos="fade-up" data-aos-delay="100">
                <div class="bg-[#FBFBFC] rounded-3xl p-8 pt-10 border border-slate-200 shadow-2xs card-interactive group h-full flex flex-col justify-between">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-14 h-14 bg-brand-50 border border-brand-100 rounded-2xl flex items-center justify-center text-xl font-extrabold text-brand-600 shadow-xs group-hover:scale-110 group-hover:bg-brand-500 group-hover:text-white transition-all">
                            1M
                        </div>
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-brand-600">Impact Jeunesse</span>
                            <h3 class="text-xl font-bold text-slate-900 group-hover:text-brand-600 transition-colors">1 million de talents</h3>
                        </div>
                    </div>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Accompagner 1 million de jeunes africains dans leur orientation et leur insertion professionnelle d'ici 2027.
                    </p>
                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span>Horizon 2027</span>
                        <span class="text-brand-600 font-bold">Objectif prioritaire</span>
                    </div>
                </div>
            </div>

            <!-- Objective 2 -->
            <div class="relative pt-6" data-aos="fade-up" data-aos-delay="200">
                <div class="bg-[#FBFBFC] rounded-3xl p-8 pt-10 border border-slate-200 shadow-2xs card-interactive group h-full flex flex-col justify-between">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-14 h-14 bg-emerald-50 border border-emerald-100 rounded-2xl flex items-center justify-center text-xl font-extrabold text-emerald-600 shadow-xs group-hover:scale-110 group-hover:bg-emerald-600 group-hover:text-white transition-all">
                            25
                        </div>
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Expansion Continentale</span>
                            <h3 class="text-xl font-bold text-slate-900 group-hover:text-emerald-700 transition-colors">25 pays africains</h3>
                        </div>
                    </div>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Déployer une présence active avec des contenus contextualisés et des réseaux de mentors locaux dans chaque pays.
                    </p>
                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span>Horizon 2028</span>
                        <span class="text-emerald-600 font-bold">Panafricain</span>
                    </div>
                </div>
            </div>

            <!-- Objective 3 -->
            <div class="relative pt-6" data-aos="fade-up" data-aos-delay="300">
                <div class="bg-[#FBFBFC] rounded-3xl p-8 pt-10 border border-slate-200 shadow-2xs card-interactive group h-full flex flex-col justify-between">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-14 h-14 bg-amber-50 border border-amber-100 rounded-2xl flex items-center justify-center text-xl font-extrabold text-amber-600 shadow-xs group-hover:scale-110 group-hover:bg-amber-500 group-hover:text-white transition-all">
                            5K
                        </div>
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Réseau d'Élite</span>
                            <h3 class="text-xl font-bold text-slate-900 group-hover:text-amber-700 transition-colors">5 000 mentors</h3>
                        </div>
                    </div>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Fédérer une communauté engagée de professionnels issus du continent et de la diaspora africaine mondiale.
                    </p>
                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span>Horizon 2030</span>
                        <span class="text-amber-600 font-bold">Diaspora & Local</span>
                    </div>
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

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Team Member 1: Happy Tidjani -->
            <div class="bg-white rounded-3xl p-6 text-center border border-slate-200/90 shadow-2xs card-interactive group"
                data-aos="fade-up" data-aos-delay="100">
                <div class="w-24 h-24 mx-auto mb-4 relative">
                    <img src="{{ asset('images/team/happy.jpg') }}" alt="Happy Tidjani"
                        class="w-full h-full object-cover rounded-full shadow-md border-2 border-brand-100 group-hover:scale-110 transition-transform duration-300">
                </div>
                <h3 class="text-lg font-bold text-slate-900 leading-tight group-hover:text-brand-600 transition-colors">Happy Tidjani</h3>
                <p class="text-brand-600 text-xs font-semibold mt-1 mb-3">Fondateur & CEO</p>
                <div class="flex justify-center space-x-3 mt-4">
                    <a href="https://www.linkedin.com/in/happytidjani/" target="_blank"
                        class="text-slate-400 hover:text-brand-600 hover:scale-110 transition-all p-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                        </svg>
                    </a>
                    <a href="https://www.facebook.com/happy.tidjani.1" target="_blank"
                        class="text-slate-400 hover:text-blue-600 hover:scale-110 transition-all p-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z" />
                        </svg>
                    </a>
                    <a href="http://happytidjani.com/" target="_blank"
                        class="text-slate-400 hover:text-brand-600 hover:scale-110 transition-all p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9-9H3m9 9V3m0 18v-9" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Team Member 2: Emmanuella Ahouanse -->
            <div class="bg-white rounded-3xl p-6 text-center border border-slate-200/90 shadow-2xs card-interactive group"
                data-aos="fade-up" data-aos-delay="200">
                <div class="w-24 h-24 mx-auto mb-4 relative">
                    <img src="{{ asset('images/team/emmanuella.jpeg') }}" alt="Emmanuella Ahouanse"
                        class="w-full h-full object-cover rounded-full shadow-md border-2 border-brand-100 group-hover:scale-110 transition-transform duration-300">
                </div>
                <h3 class="text-lg font-bold text-slate-900 leading-tight group-hover:text-brand-600 transition-colors">Emmanuella Ahouanse</h3>
                <p class="text-brand-600 text-xs font-semibold mt-1 mb-3">Co-fondatrice & COO</p>
                <div class="flex justify-center space-x-3 mt-4">
                    <a href="https://www.linkedin.com/in/ahouanse-mahutin-emmanuella-b31947205/" target="_blank"
                        class="text-slate-400 hover:text-brand-600 hover:scale-110 transition-all p-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                        </svg>
                    </a>
                    <a href="https://www.facebook.com/emmanuella.ahouanse.3" target="_blank"
                        class="text-slate-400 hover:text-blue-600 hover:scale-110 transition-all p-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Team Member 3: Melvina Kouton -->
            <div class="bg-white rounded-3xl p-6 text-center border border-slate-200/90 shadow-2xs card-interactive group"
                data-aos="fade-up" data-aos-delay="300">
                <div class="w-24 h-24 mx-auto mb-4 relative">
                    <img src="{{ asset('images/team/melvina.jpg') }}" alt="Melvina Kouton"
                        class="w-full h-full object-cover rounded-full shadow-md border-2 border-brand-100 group-hover:scale-110 transition-transform duration-300">
                </div>
                <h3 class="text-lg font-bold text-slate-900 leading-tight group-hover:text-brand-600 transition-colors">Melvina Kouton</h3>
                <p class="text-brand-600 text-xs font-semibold mt-1 mb-3">Conseillère Juridique</p>
                <div class="flex justify-center space-x-3 mt-4">
                    <a href="https://www.facebook.com/Melvina.Kouton" target="_blank"
                        class="text-slate-400 hover:text-blue-600 hover:scale-110 transition-all p-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Team Member 4: Ornella Vidjannagni -->
            <div class="bg-white rounded-3xl p-6 text-center border border-slate-200/90 shadow-2xs card-interactive group"
                data-aos="fade-up" data-aos-delay="400">
                <div class="w-24 h-24 mx-auto mb-4 relative">
                    <img src="{{ asset('images/team/ornella.jpeg') }}" alt="Ornella Vidjannagni"
                        class="w-full h-full object-cover rounded-full shadow-md border-2 border-brand-100 group-hover:scale-110 transition-transform duration-300">
                </div>
                <h3 class="text-lg font-bold text-slate-900 leading-tight group-hover:text-brand-600 transition-colors">Ornella Vidjannagni</h3>
                <p class="text-brand-600 text-xs font-semibold mt-1 mb-3 whitespace-nowrap">Responsable Produit & UX/UI</p>
                <div class="flex justify-center space-x-3 mt-4">
                    <a href="https://www.linkedin.com/in/ornellavidjannagni/" target="_blank"
                        class="text-slate-400 hover:text-brand-600 hover:scale-110 transition-all p-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                        </svg>
                    </a>
                    <a href="https://www.facebook.com/profile.php?id=100008320955940" target="_blank"
                        class="text-slate-400 hover:text-blue-600 hover:scale-110 transition-all p-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z" />
                        </svg>
                    </a>
                    <a href="https://www.designbyornella.com/" target="_blank"
                        class="text-slate-400 hover:text-brand-600 hover:scale-110 transition-all p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9-9H3m9 9V3m0 18v-9" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Team Member 5: Jérémie Atcho -->
            <div class="bg-white rounded-3xl p-6 text-center border border-slate-200/90 shadow-2xs card-interactive group"
                data-aos="fade-up" data-aos-delay="100">
                <div class="w-24 h-24 mx-auto mb-4 relative">
                    <img src="{{ asset('images/team/jeremie.jpg') }}" alt="Jérémie Atcho"
                        class="w-full h-full object-cover rounded-full shadow-md border-2 border-brand-100 group-hover:scale-110 transition-transform duration-300">
                </div>
                <h3 class="text-lg font-bold text-slate-900 leading-tight group-hover:text-brand-600 transition-colors">Jérémie Atcho</h3>
                <p class="text-brand-600 text-xs font-semibold mt-1 mb-3">Consultant Com Digitale</p>
                <div class="flex justify-center space-x-3 mt-4">
                    <a href="https://www.linkedin.com/in/j%C3%A9r%C3%A9mie-atcho-30252a213/" target="_blank"
                        class="text-slate-400 hover:text-brand-600 hover:scale-110 transition-all p-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                        </svg>
                    </a>
                    <a href="https://www.facebook.com/jeremie.dieudonne.atcho" target="_blank"
                        class="text-slate-400 hover:text-blue-600 hover:scale-110 transition-all p-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Team Member 6: Aminath Tidjani -->
            <div class="bg-white rounded-3xl p-6 text-center border border-slate-200/90 shadow-2xs card-interactive group"
                data-aos="fade-up" data-aos-delay="200">
                <div class="w-24 h-24 mx-auto mb-4 relative">
                    <img src="{{ asset('images/team/aminath.jpeg') }}" alt="Aminath Tidjani"
                        class="w-full h-full object-cover rounded-full shadow-md border-2 border-brand-100 group-hover:scale-110 transition-transform duration-300">
                </div>
                <h3 class="text-lg font-bold text-slate-900 leading-tight group-hover:text-brand-600 transition-colors">Aminath Tidjani</h3>
                <p class="text-brand-600 text-xs font-semibold mt-1 mb-3">Recherche Scientifique & Impact</p>
                <div class="flex justify-center space-x-3 mt-4">
                    <a href="https://www.linkedin.com/in/aminathf%C3%A8mietidjani/" target="_blank"
                        class="text-slate-400 hover:text-brand-600 hover:scale-110 transition-all p-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                        </svg>
                    </a>
                    <a href="https://www.facebook.com/aminath.tidjani.58" target="_blank"
                        class="text-slate-400 hover:text-blue-600 hover:scale-110 transition-all p-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Team Member 7: Conseiller Pédagogique -->
            <div class="bg-white rounded-3xl p-6 text-center border border-dashed border-slate-300 shadow-2xs card-interactive group"
                data-aos="fade-up" data-aos-delay="300">
                <div
                    class="w-24 h-24 mx-auto mb-4 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center text-xl font-extrabold shadow-inner group-hover:scale-105 transition-transform">
                    CP
                </div>
                <h3 class="text-lg font-bold text-slate-400 leading-tight italic">À venir</h3>
                <p class="text-slate-400 text-xs font-semibold mt-1 mb-3">Conseiller Pédagogique</p>
                <div class="flex justify-center space-x-3 mt-4 opacity-30">
                    <span class="text-xs text-slate-400 font-medium">Recrutement en cours</span>
                </div>
            </div>

            <!-- Team Member 8: Jude Dossou -->
            <div class="bg-white rounded-3xl p-6 text-center border border-slate-200/90 shadow-2xs card-interactive group"
                data-aos="fade-up" data-aos-delay="400">
                <div class="w-24 h-24 mx-auto mb-4 relative">
                    <img src="{{ asset('images/team/jude.jpeg') }}" alt="Jude Dossou"
                        class="w-full h-full object-cover rounded-full shadow-md border-2 border-brand-100 group-hover:scale-110 transition-transform duration-300">
                </div>
                <h3 class="text-lg font-bold text-slate-900 leading-tight group-hover:text-brand-600 transition-colors">Jude Dossou</h3>
                <p class="text-brand-600 text-xs font-semibold mt-1 mb-3">Designer Graphique</p>
                <div class="flex justify-center space-x-3 mt-4">
                    <a href="https://www.linkedin.com/in/jude-dossou-a48140225/" target="_blank"
                        class="text-slate-400 hover:text-brand-600 hover:scale-110 transition-all p-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
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
            <div class="flex {{ $partners->count() >= 6 ? 'animate-scroll' : 'justify-center flex-wrap gap-6' }} whitespace-nowrap">
                <!-- First set of logos -->
                <div class="flex space-x-12 items-center mx-6">
                    @foreach($partners as $partner)
                    <div
                        class="flex-shrink-0 bg-gray-50 rounded-xl p-4 flex items-center justify-center w-48 h-24 shadow-sm hover:shadow-md transition-shadow grayscale hover:grayscale-0">
                        @if(!empty($partner->website))
                            <a href="{{ $partner->website }}" target="_blank" rel="noopener noreferrer" title="{{ $partner->name }}" class="flex items-center justify-center w-full h-full">
                                <img src="{{ $partner->logo_url }}" alt="{{ $partner->name }}"
                                    class="max-w-full max-h-full object-contain">
                            </a>
                        @else
                            <img src="{{ $partner->logo_url }}" alt="{{ $partner->name }}"
                                class="max-w-full max-h-full object-contain" title="{{ $partner->name }}">
                        @endif
                    </div>
                    @endforeach
                </div>

                @if($partners->count() >= 6)
                <!-- Duplicate set for infinite loop (only if enough items) -->
                <div class="flex space-x-12 items-center mx-6">
                    @foreach($partners as $partner)
                    <div
                        class="flex-shrink-0 bg-gray-50 rounded-xl p-4 flex items-center justify-center w-48 h-24 shadow-sm hover:shadow-md transition-shadow grayscale hover:grayscale-0">
                        @if(!empty($partner->website))
                            <a href="{{ $partner->website }}" target="_blank" rel="noopener noreferrer" title="{{ $partner->name }}" class="flex items-center justify-center w-full h-full">
                                <img src="{{ $partner->logo_url }}" alt="{{ $partner->name }}"
                                    class="max-w-full max-h-full object-contain">
                            </a>
                        @else
                            <img src="{{ $partner->logo_url }}" alt="{{ $partner->name }}"
                                class="max-w-full max-h-full object-contain" title="{{ $partner->name }}">
                        @endif
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
                class="bg-slate-900 rounded-3xl overflow-hidden shadow-xl border border-slate-800 relative">

                <div class="px-8 py-16 md:px-16 md:py-20 relative z-10">
                    <div class="text-center mb-16">
                        <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-6">Pourquoi rejoindre Brillio en
                            tant qu'organisation ?</h2>
                        <p class="text-slate-300 text-lg max-w-2xl mx-auto">Offrez à vos membres un accompagnement
                            d'élite et transformez votre impact grâce à une plateforme conçue pour l'excellence
                            institutionnelle.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-16">
                        <!-- Feature 1 -->
                        <div
                            class="bg-white/10 backdrop-blur-md p-6 rounded-2xl border border-white/10 hover:bg-white/15 card-interactive group">
                            <div
                                class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-white font-bold mb-2">Suivi en temps réel</h3>
                            <p class="text-slate-300 text-xs leading-relaxed">Monitorez la progression de vos jeunes et l'activité de vos mentors sur un tableau de bord unifié.</p>
                        </div>

                        <!-- Feature 2 -->
                        <div
                            class="bg-white/10 backdrop-blur-md p-6 rounded-2xl border border-white/10 hover:bg-white/15 card-interactive group">
                            <div
                                class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-white font-bold mb-2">Gestion des Crédits</h3>
                            <p class="text-slate-300 text-xs leading-relaxed">Distribuez facilement des crédits de mentorat à vos membres pour financer leurs séances de coaching.</p>
                        </div>

                        <!-- Feature 3 -->
                        <div
                            class="bg-white/10 backdrop-blur-md p-6 rounded-2xl border border-white/10 hover:bg-white/15 card-interactive group">
                            <div
                                class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-7.714 2.143L11 21l-2.286-6.857L1 12l7.714-2.143L11 3z">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-white font-bold mb-2">Marque Blanche</h3>
                            <p class="text-slate-300 text-xs leading-relaxed">Proposez une expérience 100% à vos couleurs avec votre propre logo, domaine et identité visuelle.</p>
                        </div>

                        <!-- Feature 4 -->
                        <div
                            class="bg-white/10 backdrop-blur-md p-6 rounded-2xl border border-white/10 hover:bg-white/15 card-interactive group">
                            <div
                                class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-white font-bold mb-2">Rapports d'Impact</h3>
                            <p class="text-slate-300 text-xs leading-relaxed">Générez des rapports détaillés pour mesurer concrètement l'évolution et la réussite de vos programmes.</p>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-center items-center gap-6">
                        <a href="{{ route('organization.register') }}"
                            class="btn-spring px-10 py-5 bg-white text-slate-900 font-extrabold rounded-2xl hover:bg-slate-50 shadow-md text-center w-full sm:w-auto">
                            Rejoindre en tant qu'organisation
                        </a>
                        <a href="{{ route('contact') }}"
                            class="btn-spring px-10 py-5 border-2 border-white/30 text-white font-bold rounded-2xl hover:bg-white/10 text-center w-full sm:w-auto group">
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
<section class="py-20 bg-slate-900 text-white relative overflow-hidden border-t border-slate-800">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-3xl mx-auto text-center text-white">
            <h2 class="text-3xl sm:text-4xl font-extrabold mb-4 tracking-tight" data-aos="fade-up">
                Rejoins le mouvement
            </h2>
            <p class="text-lg text-slate-300 mb-8 max-w-xl mx-auto font-normal leading-relaxed" data-aos="fade-up" data-aos-delay="100">
                Télécharge Brillio et fais partie des milliers de jeunes africains qui construisent leur avenir.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4" data-aos="fade-up" data-aos-delay="200">
                <a href="{{ route('home') }}#telecharger"
                    class="px-8 py-3.5 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl transition-all shadow-sm">
                    Télécharger l'application
                </a>
                <a href="{{ route('contact') }}"
                    class="px-8 py-3.5 bg-slate-800 hover:bg-slate-700 text-white font-semibold rounded-xl border border-slate-700 transition-all">
                    Nous contacter
                </a>
            </div>
        </div>
    </div>
</section>
@endsection