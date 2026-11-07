@extends('layouts.public')

@push('seo')
    <x-seo-meta page="home" />
@endpush

@section('content')
<div x-data="{ showAppModal: false }">

    <!-- ===================================================================== -->
    <!-- 1. HERO SECTION : Split-Screen Studio, Clair, Humain & Tangible       -->
    <!-- ===================================================================== -->
    <section class="relative pt-32 pb-20 lg:pt-36 lg:pb-28 bg-[#FBFBFC] border-b border-slate-200/80 overflow-hidden">
        <!-- Subtilité de texture : grille très discrète sans blob coloré -->
        <div class="absolute inset-0 border-grid-pattern opacity-60 pointer-events-none"></div>

        <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                
                <!-- Colonne Gauche : Pitch, Preuve & Actions (7 colonnes) -->
                <div class="lg:col-span-7 space-y-8" data-aos="fade-up">
                    
                    <!-- Badge éditorial pro -->
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white border border-slate-200/90 text-xs font-semibold text-slate-700 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                        <span>Plateforme panafricaine de mentorat & d'orientation</span>
                        <span class="text-slate-300">|</span>
                        <span class="text-brand-600 font-bold">Édition 2026</span>
                    </div>

                    <!-- Titre principal affirmé -->
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-slate-900 leading-[1.12]">
                        Construis ta carrière avec des mentors qui <span class="text-brand-500 underline decoration-brand-200 decoration-wavy decoration-2">ont réussi</span>.
                    </h1>

                    <!-- Sous-titre structuré -->
                    <p class="text-lg sm:text-xl text-slate-600 max-w-2xl font-normal leading-relaxed">
                        Brillio connecte la nouvelle génération de talents africains aux professionnels du continent et de la diaspora. Test de personnalité scientifique, orientation concrète et accompagnement 1:1.
                    </p>

                    <!-- Double CTA d'action directe & micro-interactions ludiques -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                        <a href="{{ route('auth.choice') }}"
                            class="btn-spring inline-flex items-center justify-center gap-2.5 px-7 py-4 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-2xl shadow-sm hover:shadow-md transition-all text-base text-center group">
                            <span>Commencer gratuitement</span>
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </a>

                        <button @click="showAppModal = true"
                            class="btn-spring inline-flex items-center justify-center gap-2.5 px-6 py-4 bg-white hover:bg-slate-50 border border-slate-200/90 text-slate-800 font-semibold rounded-2xl transition-all shadow-2xs text-base text-center group">
                            <svg class="w-5 h-5 text-slate-600 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Installer l'application mobile</span>
                        </button>
                    </div>

                    <!-- Métriques réelles de confiance -->
                    <div class="pt-6 border-t border-slate-200/70 grid grid-cols-3 gap-6 sm:gap-10">
                        <div>
                            <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ number_format($jeunesCount, 0, ',', ' ') }}+</p>
                            <p class="text-xs sm:text-sm font-medium text-slate-500 mt-0.5">Jeunes accompagnés</p>
                        </div>
                        <div>
                            <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ number_format($mentorsCount, 0, ',', ' ') }}+</p>
                            <p class="text-xs sm:text-sm font-medium text-slate-500 mt-0.5">Mentors certifiés</p>
                        </div>
                        <div>
                            <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $countriesCount }}+</p>
                            <p class="text-xs sm:text-sm font-medium text-slate-500 mt-0.5">Pays africains</p>
                        </div>
                    </div>
                </div>

                <!-- Colonne Droite : Interface Réelle Brillio Dynamique & Ludique (5 colonnes) -->
                <div class="lg:col-span-5 relative" data-aos="fade-left" data-aos-delay="150"
                     x-data="{
                         selectedTrait: 0,
                         traits: [
                             { code: 'ENTJ', title: 'Stratège & Leader', emoji: '⚡', tag: 'Tech & Management', match: '98%', desc: 'Orientation vers les fonctions de direction produit et tech.' },
                             { code: 'INFP', title: 'Innovateur Créatif', emoji: '🎨', tag: 'Design & Impact', match: '95%', desc: 'Orientation vers l\'UX, la création de contenu et le social.' },
                             { code: 'ESTP', title: 'Bâtisseur de Projets', emoji: '🚀', tag: 'Business & Ventes', match: '94%', desc: 'Orientation vers le business development et l\'entrepreneuriat.' }
                         ],
                         nextTrait() {
                             this.selectedTrait = (this.selectedTrait + 1) % this.traits.length;
                         }
                     }">
                    
                    <div class="relative mx-auto max-w-sm lg:max-w-none">
                        
                        <!-- Badge flottant 1 (Haut-Droit) : Match & Jeunes connectés -->
                        <div class="hidden sm:flex items-center gap-2.5 absolute -top-5 -right-4 bg-white/95 backdrop-blur-md border border-slate-200/90 rounded-2xl px-3.5 py-2.5 shadow-lg shadow-brand-500/10 z-30 animate-float-badge cursor-pointer card-interactive"
                             @click="nextTrait()">
                            <span class="flex h-2.5 w-2.5 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-brand-500"></span>
                            </span>
                            <div class="text-left">
                                <p class="text-[11px] font-bold text-slate-900 leading-tight">✨ Match personnalisé</p>
                                <p class="text-[10px] font-semibold text-brand-600" x-text="'Affinité ' + traits[selectedTrait].match + ' détectée'"></p>
                            </div>
                        </div>

                        <!-- Carte Produit Principale : Vue Interactive Talent Brillio -->
                        <div class="bg-white rounded-3xl border border-slate-200 shadow-xl shadow-slate-200/50 p-6 space-y-5 relative z-10 card-interactive">
                            
                            <!-- Header de l'interface simulation -->
                            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-brand-50 border border-brand-100 p-2 flex items-center justify-center flex-shrink-0 animate-pulse-subtle">
                                        <img src="{{ asset('images/logo/brillio-icon.png') }}" alt="Brillio" class="w-full h-full object-contain">
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-slate-500 leading-none">Tableau de bord</p>
                                        <p class="text-sm font-bold text-slate-900 mt-1">Espace Talent Brillio</p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold border border-emerald-200/60">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    En direct
                                </span>
                            </div>

                            <!-- Bloc Orientation & Test Interactif (Cycle au clic) -->
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 space-y-3 cursor-pointer group hover:bg-brand-50/40 hover:border-brand-200 transition-all"
                                 role="button"
                                 tabindex="0"
                                 @click="nextTrait()"
                                 @keydown.enter="nextTrait()"
                                 @keydown.space.prevent="nextTrait()"
                                 title="Clique pour découvrir un autre profil">
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold uppercase tracking-wider text-slate-500 text-[10px]">Profil de personnalité</span>
                                        <span class="text-[10px] text-brand-600 font-bold bg-white px-1.5 py-0.5 rounded border border-slate-200 group-hover:border-brand-300">clique pour tester ↻</span>
                                    </div>
                                    <span class="font-bold text-brand-600 bg-brand-50 px-2 py-0.5 rounded-md text-[11px]" x-text="traits[selectedTrait].code"></span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-xl shadow-2xs group-hover:scale-110 transition-transform"
                                         x-text="traits[selectedTrait].emoji">
                                        🎯
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-slate-900 leading-snug group-hover:text-brand-700 transition-colors" x-text="traits[selectedTrait].title">
                                            Test MBTI & Forces Motrices
                                        </h4>
                                        <p class="text-xs text-slate-500" x-text="traits[selectedTrait].desc">
                                            Recommandations de métiers selon ton profil
                                        </p>
                                    </div>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                                    <div class="bg-brand-500 h-2 rounded-full transition-all duration-500"
                                         :style="'width: ' + traits[selectedTrait].match"></div>
                                </div>
                            </div>

                            <!-- Bloc Mentor Preview Tangible avec hover motion -->
                            <div class="space-y-3">
                                <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                                    <span>Mentors connectés cette semaine</span>
                                    <a href="{{ route('auth.choice') }}" class="text-brand-600 hover:text-brand-700 text-xs font-semibold hover:underline">Explorer →</a>
                                </div>

                                <div class="space-y-2">
                                    <div class="flex items-center justify-between p-3 rounded-xl bg-white border border-slate-200 hover:border-brand-400 hover:shadow-xs transition-all transform hover:-translate-y-0.5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-amber-100 text-amber-800 font-bold text-xs flex items-center justify-center">
                                                DK
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-slate-900 leading-tight">Dr. Didier K.</p>
                                                <p class="text-[11px] text-slate-500">VP Engineering • Diaspora Bénin / France</p>
                                            </div>
                                        </div>
                                        <span class="text-[11px] font-semibold text-brand-700 bg-brand-50 px-2.5 py-1 rounded-lg">45 min</span>
                                    </div>

                                    <div class="flex items-center justify-between p-3 rounded-xl bg-white border border-slate-200 hover:border-brand-400 hover:shadow-xs transition-all transform hover:-translate-y-0.5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-800 font-bold text-xs flex items-center justify-center">
                                                AF
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-slate-900 leading-tight">Awa F.</p>
                                                <p class="text-[11px] text-slate-500">Lead Product Manager • Abidjan</p>
                                            </div>
                                        </div>
                                        <span class="text-[11px] font-semibold text-brand-700 bg-brand-50 px-2.5 py-1 rounded-lg">30 min</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer rapide du widget -->
                            <div class="pt-2 flex items-center justify-between text-xs text-slate-500 border-t border-slate-100">
                                <span class="flex items-center gap-1.5 font-medium text-emerald-700">
                                    <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Sessions certifiées gratuites
                                </span>
                                <span class="font-semibold text-slate-700">100% en ligne</span>
                            </div>

                        </div>

                        <!-- Badge flottant 2 (Bas-Gauche) : Assurance qualité avec micro-animation -->
                        <div class="hidden sm:flex items-center gap-3 absolute -bottom-5 -left-6 bg-white/95 backdrop-blur-md border border-slate-200/90 rounded-2xl p-3 shadow-lg shadow-slate-200/60 z-20 animate-float-subtle card-interactive">
                            <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center font-bold text-base">
                                🛡️
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-900 leading-tight">Mentorat Vérifié</p>
                                <p class="text-[11px] text-slate-500">Parcours & diplômes audités</p>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ===================================================================== -->
    <!-- 2. SECTION 3 PROFILS : 1 Plateforme pour Jeune, Mentor & Organisation -->
    <!-- ===================================================================== -->
    <section class="py-20 lg:py-24 bg-white border-b border-slate-200/80">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center mb-16" data-aos="fade-up">
                <p class="text-xs font-bold uppercase tracking-wider text-brand-600 mb-2">Un écosystème complet</p>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Conçu pour chaque acteur du développement africain
                </h2>
                <p class="text-base sm:text-lg text-slate-600 mt-4 leading-relaxed">
                    Que vous soyez étudiant en quête de repères, professionnel prêt à transmettre, ou institution cherchant à mesurer son impact.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                
                <!-- Profil 1 : Jeunes & Étudiants -->
                <div class="bg-[#FBFBFC] rounded-3xl p-8 border border-slate-200 hover:border-brand-300 card-interactive flex flex-col justify-between group" data-aos="fade-up" data-aos-delay="100">
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-brand-500 text-white flex items-center justify-center font-bold text-lg shadow-xs group-hover:scale-110 transition-transform">
                            🎓
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 group-hover:text-brand-600 transition-colors">Pour les Jeunes</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Arrêtez de choisir votre carrière au hasard. Découvrez votre personnalité, vos points forts et dialoguez directement avec des professionnels qui exercent vos métiers cibles.
                        </p>
                        <ul class="space-y-2.5 pt-2 text-xs font-medium text-slate-700">
                            <li class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                                Test de personnalité scientifique MBTI
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                                Simulateur et analyse de CV ATS gratuit
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                                Sessions de mentorat individuelles
                            </li>
                        </ul>
                    </div>
                    <div class="pt-8">
                        <a href="{{ route('auth.jeune.register') }}" class="inline-flex items-center gap-2 text-xs font-bold text-brand-600 hover:text-brand-700 group-hover:translate-x-1 transition-transform">
                            Rejoindre en tant que Jeune →
                        </a>
                    </div>
                </div>

                <!-- Profil 2 : Mentors -->
                <div class="bg-[#FBFBFC] rounded-3xl p-8 border border-slate-200 hover:border-amber-300 card-interactive flex flex-col justify-between group" data-aos="fade-up" data-aos-delay="200">
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center font-bold text-lg shadow-xs group-hover:scale-110 transition-transform">
                            🤝
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 group-hover:text-amber-700 transition-colors">Pour les Mentors</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Mettez votre expertise au service des leaders de demain. Gérez vos disponibilités en toute simplicité et suivez la progression de vos mentorés sur un espace dédié.
                        </p>
                        <ul class="space-y-2.5 pt-2 text-xs font-medium text-slate-700">
                            <li class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                Calendrier & créneaux 100% maîtrisés
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                Profil public vérifié et visibilité pro
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                Impact direct sur la jeunesse africaine
                            </li>
                        </ul>
                    </div>
                    <div class="pt-8">
                        <a href="{{ route('auth.mentor.login') }}" class="inline-flex items-center gap-2 text-xs font-bold text-amber-700 hover:text-amber-800 group-hover:translate-x-1 transition-transform">
                            Devenir Mentor certifié →
                        </a>
                    </div>
                </div>

                <!-- Profil 3 : Organisations & Entreprises -->
                <div class="bg-[#FBFBFC] rounded-3xl p-8 border border-slate-200 hover:border-slate-400 card-interactive flex flex-col justify-between group" data-aos="fade-up" data-aos-delay="300">
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-slate-900 text-white flex items-center justify-center font-bold text-lg shadow-xs group-hover:scale-110 transition-transform">
                            🏢
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 group-hover:text-slate-700 transition-colors">Pour les Organisations</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Écoles, universités, ONG et recruteurs : pilotez des cohortes de talents, mesurez l'insertion professionnelle et accédez à un vivier de profils qualifiés.
                        </p>
                        <ul class="space-y-2.5 pt-2 text-xs font-medium text-slate-700">
                            <li class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-900"></span>
                                Tableaux de bord d'impact & rapports
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-900"></span>
                                Gestion de cohortes et bourses d'études
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-900"></span>
                                Sourcing de profils pré-évalués
                            </li>
                        </ul>
                    </div>
                    <div class="pt-8">
                        <a href="{{ route('organization.register') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-900 hover:text-slate-700 group-hover:translate-x-1 transition-transform">
                            Espace Partenaire & Entreprise →
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ===================================================================== -->
    <!-- 3. VITRINE DES MENTORS : Des visages réels, du vécu, de la crédibilité -->
    <!-- ===================================================================== -->
    @if(isset($verifiedMentors) && $verifiedMentors->count() > 0)
    <section class="py-20 lg:py-24 bg-[#F8FAFC] border-b border-slate-200/80">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-12">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-brand-600 mb-2">Réseau d'excellence</p>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                        Rencontrez vos futurs mentors
                    </h2>
                    <p class="text-slate-600 text-sm sm:text-base mt-2">
                        Des experts issus d'entreprises panafricaines et internationales, prêts à partager leur parcours.
                    </p>
                </div>
                <div class="mt-4 md:mt-0">
                    <a href="{{ route('auth.choice') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-800 hover:bg-slate-50 shadow-2xs">
                        <span>Voir tout l'annuaire</span>
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

            <!-- Grille de cartes mentors denses et élégantes -->
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($verifiedMentors->take(4) as $mentor)
                @php
                    $initials = strtoupper(substr($mentor->user->first_name ?? 'M', 0, 1) . substr($mentor->user->last_name ?? '', 0, 1));
                    $nameParts = explode(' ', $mentor->user->name);
                    $firstName = $nameParts[0] ?? '';
                    $lastNameInit = isset($nameParts[1]) ? substr($nameParts[1], 0, 1) . '.' : '';
                @endphp
                <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-2xs hover:shadow-md card-interactive transition-all flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center gap-3.5 mb-4">
                            <div class="relative flex-shrink-0">
                                 <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 font-bold text-sm flex items-center justify-center overflow-hidden group-hover:scale-105 transition-transform">
                                    @if($mentor->user->avatar_url)
                                        <img src="{{ $mentor->user->avatar_url }}" alt="{{ $mentor->user->name }}" class="w-full h-full object-cover">
                                    @else
                                        <span>{{ $initials }}</span>
                                    @endif
                                </div>
                                <span class="absolute -bottom-1 -right-1 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full" title="Mentor vérifié"></span>
                            </div>
                            <div class="overflow-hidden">
                                <p class="text-sm font-bold text-slate-900 leading-tight truncate group-hover:text-brand-600 transition-colors">{{ $firstName }} {{ $lastNameInit }}</p>
                                <p class="text-xs text-slate-500 truncate mt-0.5">{{ $mentor->current_position ?? 'Expert métier' }}</p>
                            </div>
                        </div>

                        @if($mentor->current_company)
                            <div class="mb-3">
                                <span class="inline-block text-[11px] font-medium text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">
                                    {{ $mentor->current_company }}
                                </span>
                            </div>
                        @endif

                        @if($mentor->bio)
                            <p class="text-xs text-slate-600 line-clamp-3 leading-relaxed mb-4">
                                {{ Str::limit($mentor->bio, 110) }}
                            </p>
                        @endif
                    </div>

                    <div class="pt-4 border-t border-slate-100">
                        <a href="{{ route('public.mentor.profile', $mentor->public_slug) }}"
                            class="w-full inline-flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl bg-slate-50 hover:bg-brand-50 hover:text-brand-700 hover:border-brand-200 text-slate-800 text-xs font-semibold border border-slate-200/80 transition-all group-hover:shadow-2xs">
                            <span>Consulter la feuille de route</span>
                            <svg class="w-3.5 h-3.5 text-slate-500 group-hover:translate-x-1 group-hover:text-brand-600 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- ===================================================================== -->
    <!-- 4. SECTION PROCESSUS : Comment ça fonctionne en 3 étapes claires      -->
    <!-- ===================================================================== -->
    <section class="py-20 lg:py-24 bg-white border-b border-slate-200/80">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mx-auto text-center mb-16">
                <p class="text-xs font-bold uppercase tracking-wider text-brand-600 mb-2">Méthodologie</p>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    3 étapes pour lancer votre trajectoire
                </h2>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Étape 1 -->
                <div class="p-6 rounded-2xl bg-slate-50/70 border border-slate-200/80 card-interactive hover:border-brand-200 space-y-4 group">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-white font-extrabold text-sm flex items-center justify-center group-hover:scale-110 group-hover:bg-brand-500 transition-all">
                        01
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 group-hover:text-brand-600 transition-colors">Passez le diagnostic initial</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Évaluez vos compétences, vos affinités professionnelles et découvrez vos familles de métiers idéales grâce au test de personnalité.
                    </p>
                </div>

                <!-- Étape 2 -->
                <div class="p-6 rounded-2xl bg-slate-50/70 border border-slate-200/80 card-interactive hover:border-brand-200 space-y-4 group">
                    <div class="w-10 h-10 rounded-xl bg-brand-500 text-white font-extrabold text-sm flex items-center justify-center group-hover:scale-110 group-hover:bg-brand-600 transition-all">
                        02
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 group-hover:text-brand-600 transition-colors">Choisissez votre mentor</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Parcourez les parcours vérifiés de mentors de votre pays ou de la diaspora, et réservez une session vidéo en fonction de vos créneaux.
                    </p>
                </div>

                <!-- Étape 3 -->
                <div class="p-6 rounded-2xl bg-slate-50/70 border border-slate-200/80 card-interactive hover:border-emerald-200 space-y-4 group">
                    <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white font-extrabold text-sm flex items-center justify-center group-hover:scale-110 group-hover:bg-emerald-700 transition-all">
                        03
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 group-hover:text-emerald-700 transition-colors">Passez à l'action</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Optimisez votre CV, candidatez aux opportunités adaptées et suivez vos progrès étape par étape avec le support de la communauté.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================================================================== -->
    <!-- 5. BANDEAU DE CONVERSION FINAL : Sobre, direct et rassurant           -->
    <!-- ===================================================================== -->
    <section class="py-20 bg-slate-900 text-white relative overflow-hidden">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <div class="max-w-2xl mx-auto space-y-6">
                <span class="inline-block px-3 py-1 rounded-full bg-slate-800 text-slate-300 text-xs font-semibold border border-slate-700">
                    Accès immédiat • 100% gratuit
                </span>
                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">
                    Prêt à prendre le contrôle de votre avenir professionnel ?
                </h2>
                <p class="text-slate-400 text-base leading-relaxed">
                    Rejoignez des milliers de jeunes et de professionnels engagés pour la réussite en Afrique.
                </p>
                <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('auth.choice') }}"
                        class="w-full sm:w-auto px-8 py-4 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-2xl transition-all shadow-md text-base">
                        Créer mon compte
                    </a>
                    <button @click="showAppModal = true"
                        class="w-full sm:w-auto px-6 py-4 bg-slate-800 hover:bg-slate-700 text-white font-semibold rounded-2xl border border-slate-700 transition-all text-base">
                        Installer l'application mobile
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================================================================== -->
    <!-- MODAL MARKETING APPLICATION MOBILE (Design épuré & pro)               -->
    <!-- ===================================================================== -->
    <div x-show="showAppModal" x-cloak @click.away="showAppModal = false"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
        style="display: none;"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div @click.stop
            class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-200 flex flex-col"
            x-transition:enter="transition ease-out duration-200 transform"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150 transform"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <!-- Modal Header -->
            <div class="p-6 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-brand-50 border border-brand-100 p-1.5 flex items-center justify-center">
                        <img src="{{ asset('images/logo/brillio-icon.png') }}" alt="Brillio" class="w-full h-full object-contain">
                    </div>
                    <h3 class="font-bold text-slate-900 text-base">Application Mobile Brillio</h3>
                </div>
                <button @click="showAppModal = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-5">
                <p class="text-sm text-slate-600 leading-relaxed">
                    Installez Brillio directement sur votre écran d'accueil sans passer par l'App Store ou Google Play. Fonctionne même avec une connexion faible.
                </p>

                <div class="p-4 rounded-2xl bg-brand-50/70 border border-brand-100 space-y-2">
                    <p class="text-xs font-bold text-brand-900">✨ Avantages de l'application :</p>
                    <ul class="text-xs text-brand-800 space-y-1">
                        <li>• Accès instantané à vos sessions et messages</li>
                        <li>• Notifications pour vos rendez-vous de mentorat</li>
                        <li>• Fonctionnement optimisé hors-ligne</li>
                    </ul>
                </div>

                <div class="space-y-2 pt-2">
                    <button type="button" data-pwa-install @click="showAppModal = false"
                        class="w-full flex items-center justify-center gap-2 px-5 py-3.5 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl shadow-xs transition-all text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Installer sur mon appareil</span>
                    </button>
                    <button type="button" @click="showAppModal = false" class="w-full py-2.5 text-xs font-medium text-slate-500 hover:text-slate-800 transition-colors">
                        Continuer dans le navigateur
                    </button>
                </div>
            </div>

        </div>
    </div>

</div>

<!-- Supabase OAuth Redirect Fix -->
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    (function () {
        if (window.location.hash && window.location.hash.includes('access_token=')) {
            if (window.location.pathname === '/' || window.location.pathname === '') {
                const pendingProvider = "{{ session('oauth_provider') }}";
                const pendingType = "{{ session('oauth_type') }}";

                if (pendingProvider && pendingType === 'jeune') {
                    window.location.href = "/jeune/oauth/" + pendingProvider + "/callback" + window.location.hash;
                } else if (pendingType === 'mentor') {
                    window.location.href = "/mentor/linkedin/callback" + window.location.hash;
                }
            }
        }
    })();
</script>
@endsection