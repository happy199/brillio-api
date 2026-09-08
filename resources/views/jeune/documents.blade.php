@extends('layouts.jeune')

@section('title', 'Opportunités & Documents')

@section('content')
<div class="space-y-8" x-data="opportunitiesHubApp('{{ $tab ?? 'drive' }}')">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Opportunités</h1>
            <p class="text-sm sm:text-base text-gray-500 mt-0.5">Explorez les offres d'emploi, les formations, gérez votre Drive et évaluez votre CV avec l'IA.</p>
        </div>

        <!-- Action contextuelle selon l'onglet -->
        <div>
            <template x-if="currentTab === 'drive'">
                <button @click="showUploadModal = true"
                    class="px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition flex items-center gap-2 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Ajouter un document</span>
                </button>
            </template>
            <template x-if="currentTab === 'cv'">
                <button @click="showCvUploadModal = true"
                    class="px-5 py-2.5 bg-emerald-600 text-white font-semibold rounded-xl hover:bg-emerald-700 transition flex items-center gap-2 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                    <span>Analyser un nouveau CV</span>
                </button>
            </template>
        </div>
    </div>

    <!-- Alertes & Messages flash -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0 text-emerald-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <p class="text-sm font-medium">{{ session('success') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0 text-red-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-sm font-medium">{{ $errors->first() }}</p>
        </div>
    @endif

    <!-- Barre des 4 Sous-onglets d'opportunités (Conforme voix & maquette) -->
    <div class="border-b border-gray-200">
        <nav class="flex space-x-2 sm:space-x-8 overflow-x-auto pb-1" aria-label="Tabs">
            <!-- 1. Emploi -->
            <button type="button"
                    @click="setTab('emploi')"
                    :class="currentTab === 'emploi' ? 'border-primary-600 text-primary-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
                    class="whitespace-nowrap py-3 px-3 sm:px-1 border-b-2 text-sm sm:text-base flex items-center gap-2 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span>Emploi</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">Recrutement</span>
            </button>

            <!-- 2. Formation -->
            <button type="button"
                    @click="setTab('formation')"
                    :class="currentTab === 'formation' ? 'border-primary-600 text-primary-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
                    class="whitespace-nowrap py-3 px-3 sm:px-1 border-b-2 text-sm sm:text-base flex items-center gap-2 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                </svg>
                <span>Formation</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800">Bourses</span>
            </button>

            <!-- 3. Drive (L'ancien Mes documents) -->
            <button type="button"
                    @click="setTab('drive')"
                    :class="currentTab === 'drive' ? 'border-primary-600 text-primary-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
                    class="whitespace-nowrap py-3 px-3 sm:px-1 border-b-2 text-sm sm:text-base flex items-center gap-2 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z" />
                </svg>
                <span>Drive</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700">{{ $documents->count() }}</span>
            </button>

            <!-- 4. CV (Historique, Scores et IA) -->
            <button type="button"
                    @click="setTab('cv')"
                    :class="currentTab === 'cv' ? 'border-emerald-600 text-emerald-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
                    class="whitespace-nowrap py-3 px-3 sm:px-1 border-b-2 text-sm sm:text-base flex items-center gap-2 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>CV</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                    @if(isset($cvAnalyses) && $cvAnalyses->count() > 0)
                        {{ $cvAnalyses->first()->global_score }}/100
                    @else
                        Score IA
                    @endif
                </span>
            </button>
        </nav>
    </div>

    <!-- ========================================================================= -->
    <!-- CONTENU ONGLET 1 : EMPLOI                                                -->
    <!-- ========================================================================= -->
    <div x-show="currentTab === 'emploi'" x-cloak class="space-y-6">
        <!-- Bannière d'introduction -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-3xl p-6 sm:p-8 text-white shadow-sm flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="space-y-2 max-w-2xl">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-white/20 uppercase tracking-wider">Passerelle Professionnelle</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Opportunités d'emploi & Stages</h2>
                <p class="text-sm sm:text-base text-blue-100">Découvrez des postes qualifiés, stages d'immersion et missions freelances adaptés aux jeunes talents d'Afrique francophone.</p>
            </div>
            <div class="flex-shrink-0">
                <button type="button" @click="setTab('cv')" class="px-6 py-3 bg-white text-blue-700 font-bold rounded-xl shadow hover:bg-blue-50 transition">
                    Optimiser mon CV pour postuler
                </button>
            </div>
        </div>

        <!-- Liste des opportunités (avec état de lancement) -->
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Offre 1 -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-lg">Stage pré-embauche</span>
                        <span class="text-xs text-gray-400">Abidjan / Hybride</span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Développeur Fullstack Junior</h3>
                    <p class="text-xs font-medium text-primary-600 mb-3">Partenaire Tech Brillio</p>
                    <p class="text-xs text-gray-600 leading-relaxed line-clamp-3">Participez au développement d'applications mobiles et web dans un environnement agile avec encadrement par un lead tech expérimenté.</p>
                </div>
                <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-900">Indemnité mensuelle</span>
                    <button type="button" @click="setTab('cv')" class="text-xs font-bold text-primary-600 hover:underline">Postuler avec mon CV</button>
                </div>
            </div>

            <!-- Offre 2 -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <span class="px-2.5 py-1 bg-purple-50 text-purple-700 text-xs font-bold rounded-lg">CDD 12 mois</span>
                        <span class="text-xs text-gray-400">Dakar / Télétravail</span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Chargé(e) de Communication Digitale</h3>
                    <p class="text-xs font-medium text-primary-600 mb-3">Agence Média Panafricaine</p>
                    <p class="text-xs text-gray-600 leading-relaxed line-clamp-3">Animation de communautés, création de contenus visuels et rédaction d'articles percutants à destination de la jeunesse.</p>
                </div>
                <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-900">Temps plein</span>
                    <button type="button" @click="setTab('cv')" class="text-xs font-bold text-primary-600 hover:underline">Postuler avec mon CV</button>
                </div>
            </div>

            <!-- Offre 3 -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <span class="px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-bold rounded-lg">Stage de fin d'études</span>
                        <span class="text-xs text-gray-400">Lomé / Présentiel</span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Assistant Analyste Financier</h3>
                    <p class="text-xs font-medium text-primary-600 mb-3">Cabinet de Conseil Stratégique</p>
                    <p class="text-xs text-gray-600 leading-relaxed line-clamp-3">Assistance sur les études de marché, la modélisation financière et les reportings économiques pour les PME régionales.</p>
                </div>
                <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-900">Convention requise</span>
                    <button type="button" @click="setTab('cv')" class="text-xs font-bold text-primary-600 hover:underline">Postuler avec mon CV</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- CONTENU ONGLET 2 : FORMATION                                             -->
    <!-- ========================================================================= -->
    <div x-show="currentTab === 'formation'" x-cloak class="space-y-6">
        <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-3xl p-6 sm:p-8 text-white shadow-sm flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="space-y-2 max-w-2xl">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-white/20 uppercase tracking-wider">Excellence Académique</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Bourses & Formations Certifiantes</h2>
                <p class="text-sm sm:text-base text-purple-100">Accédez aux programmes de bourses d'études internationales, masters d'excellence et bootcamps de formation accélérée.</p>
            </div>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
                <span class="px-2.5 py-1 bg-purple-100 text-purple-800 text-xs font-bold rounded-md">Bourse complète</span>
                <h3 class="text-lg font-bold text-gray-900">Bourses d'Excellence Master 2026</h3>
                <p class="text-xs text-gray-500">Prise en charge des frais de scolarité et allocation mensuelle pour les filières STEM et Management.</p>
                <div class="pt-4 border-t border-gray-100 flex items-center justify-between text-xs">
                    <span class="text-gray-400">Date limite : 31 Décembre</span>
                    <a href="{{ route('jeune.chat') }}" class="font-bold text-purple-600 hover:underline">Demander conseil à l'IA</a>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
                <span class="px-2.5 py-1 bg-pink-100 text-pink-800 text-xs font-bold rounded-md">Bootcamp 100% en ligne</span>
                <h3 class="text-lg font-bold text-gray-900">Certification Cloud & Intelligence Artificielle</h3>
                <p class="text-xs text-gray-500">Programme intensif de 12 semaines pour maîtriser les outils cloud modernes et le déploiement d'agents IA.</p>
                <div class="pt-4 border-t border-gray-100 flex items-center justify-between text-xs">
                    <span class="text-gray-400">Certificat inclus</span>
                    <a href="{{ route('jeune.resources.index') }}" class="font-bold text-pink-600 hover:underline">Découvrir les cours</a>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
                <span class="px-2.5 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-md">Mentorat dédié</span>
                <h3 class="text-lg font-bold text-gray-900">Préparation aux Concours d'Ingénieurs</h3>
                <p class="text-xs text-gray-500">Sessions hebdomadaires avec des mentors diplômés des plus grandes écoles polytechniques.</p>
                <div class="pt-4 border-t border-gray-100 flex items-center justify-between text-xs">
                    <span class="text-gray-400">Accompagnement 1-on-1</span>
                    <a href="{{ route('jeune.mentors') }}" class="font-bold text-amber-600 hover:underline">Trouver un mentor</a>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- CONTENU ONGLET 3 : DRIVE (Ancien Mes documents)                           -->
    <!-- ========================================================================= -->
    <div x-show="currentTab === 'drive'" x-cloak class="space-y-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $documents->count() }}</p>
                <p class="text-sm text-gray-500">Total documents</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $documents->where('is_verified', true)->count() }}</p>
                <p class="text-sm text-gray-500">Vérifiés</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $documents->where('document_type', 'bulletin')->count() }}</p>
                <p class="text-sm text-gray-500">Bulletins</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <p class="text-2xl font-bold text-gray-900">
                    {{ number_format($documents->sum('file_size') / 1024 / 1024, 1) }} Mo
                </p>
                <p class="text-sm text-gray-500">Stockage utilisé</p>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="flex flex-wrap gap-2">
            <button @click="driveFilter = 'all'"
                :class="driveFilter === 'all' ? 'bg-primary-500 text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200'"
                class="px-4 py-2 rounded-full text-sm font-medium transition">
                Tous
            </button>
            <button @click="driveFilter = 'bulletin'"
                :class="driveFilter === 'bulletin' ? 'bg-primary-500 text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200'"
                class="px-4 py-2 rounded-full text-sm font-medium transition">
                Bulletins
            </button>
            <button @click="driveFilter = 'diplome'"
                :class="driveFilter === 'diplome' ? 'bg-primary-500 text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200'"
                class="px-4 py-2 rounded-full text-sm font-medium transition">
                Diplômes
            </button>
            <button @click="driveFilter = 'attestation'"
                :class="driveFilter === 'attestation' ? 'bg-primary-500 text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200'"
                class="px-4 py-2 rounded-full text-sm font-medium transition">
                Attestations
            </button>
            <button @click="driveFilter = 'autre'"
                :class="driveFilter === 'autre' ? 'bg-primary-500 text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200'"
                class="px-4 py-2 rounded-full text-sm font-medium transition">
                Autres
            </button>
        </div>

        <!-- Documents Grid -->
        @if($documents->count() > 0)
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($documents as $document)
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition group"
                        x-show="driveFilter === 'all' || driveFilter === '{{ $document->document_type }}'">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0
                                {{ $document->document_type === 'bulletin' ? 'bg-purple-100 text-purple-600' : '' }}
                                {{ $document->document_type === 'diplome' ? 'bg-yellow-100 text-yellow-600' : '' }}
                                {{ $document->document_type === 'attestation' ? 'bg-blue-100 text-blue-600' : '' }}
                                {{ $document->document_type === 'autre' ? 'bg-gray-100 text-gray-600' : '' }}">
                                @if($document->mime_type === 'application/pdf')
                                    <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M13,9V3.5L18.5,9H13M10.3,14.4L9.6,16.9H8.1L9.9,11H11.8L13.6,16.9H12L11.4,14.4H10.3M10.5,13.5H11.2L10.9,12.2L10.5,13.5Z" />
                                    </svg>
                                @else
                                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-gray-900 truncate">{{ $document->file_name }}</h3>
                                <div class="flex items-center gap-2 mt-1 text-xs text-gray-500">
                                    <span class="capitalize">{{ $document->document_type }}</span>
                                    <span>•</span>
                                    <span>{{ number_format($document->file_size / 1024, 0) }} Ko</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-gray-50 flex items-center justify-between text-xs text-gray-500">
                            <span>{{ $document->created_at->format('d/m/Y') }}</span>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="previewDocument('{{ $document->id }}', '{{ $document->mime_type }}', '{{ addslashes($document->file_name) }}')" class="p-1.5 text-gray-500 hover:text-primary-600 rounded-lg hover:bg-gray-50 transition" title="Aperçu">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                <a href="{{ route('jeune.documents.download', $document->id) }}" class="p-1.5 text-gray-500 hover:text-primary-600 rounded-lg hover:bg-gray-50 transition" title="Télécharger">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                </a>
                                <button type="button" @click="deleteDocument('{{ $document->id }}')" class="p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition" title="Supprimer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-3xl p-12 text-center border border-gray-100">
                <div class="w-16 h-16 bg-primary-100 rounded-2xl flex items-center justify-center text-primary-600 mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Votre Drive est vide</h3>
                <p class="text-sm text-gray-500 max-w-md mx-auto mt-1 mb-6">Ajoutez vos bulletins, attestations et diplômes pour les garder en sécurité et les partager facilement avec vos mentors.</p>
                <button @click="showUploadModal = true" class="px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition">
                    Ajouter mon premier document
                </button>
            </div>
        @endif
    </div>

    <!-- ========================================================================= -->
    <!-- CONTENU ONGLET 4 : CV (Historique, Scores complets et Analyseur IA)       -->
    <!-- ========================================================================= -->
    <div x-show="currentTab === 'cv'" x-cloak class="space-y-8">
        @if(isset($activeCv) && $activeCv)
            <!-- Carte Résultat 100% Débloqué pour l'utilisateur connecté -->
            <div class="bg-white rounded-3xl p-6 sm:p-10 shadow-sm border border-gray-100">
                
                <!-- En-tête avec sélecteur de version si plusieurs CV -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 mb-6 border-b border-gray-100">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-3 py-1 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-full">Analyse Débloquée</span>
                            <span class="text-xs text-gray-400">Analysé le {{ $activeCv->created_at->format('d/m/Y à H:i') }}</span>
                        </div>
                        <h2 class="text-2xl font-extrabold text-gray-900">{{ $activeCv->original_filename }}</h2>
                    </div>

                    <div class="flex items-center gap-3">
                        @if($cvAnalyses->count() > 1)
                            <div class="relative" x-data="{ openVersions: false }">
                                <button @click="openVersions = !openVersions" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-semibold flex items-center gap-2 transition">
                                    <span>Historique ({{ $cvAnalyses->count() }})</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="openVersions" @click.away="openVersions = false" class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-20">
                                    @foreach($cvAnalyses as $cvItem)
                                        <a href="{{ route('jeune.documents', ['tab' => 'cv', 'cv_id' => $cvItem->id]) }}"
                                           class="px-4 py-2 text-xs flex items-center justify-between hover:bg-gray-50 {{ $cvItem->id === $activeCv->id ? 'bg-emerald-50 text-emerald-700 font-bold' : 'text-gray-700' }}">
                                            <span class="truncate">{{ $cvItem->original_filename }}</span>
                                            <span class="font-bold">{{ $cvItem->global_score }}/100</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <button @click="showCvUploadModal = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold flex items-center gap-1.5 transition shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Réévaluer un CV</span>
                        </button>
                    </div>
                </div>

                <!-- Jauge & Synthèse globale -->
                <div class="flex flex-col md:flex-row items-center gap-8 md:gap-12 mb-10">
                    <div class="relative w-40 h-40 flex-shrink-0 flex items-center justify-center">
                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 120 120">
                            <circle cx="60" cy="60" r="50" fill="none" stroke="#E5E7EB" stroke-width="10" />
                            <circle cx="60" cy="60" r="50" fill="none" stroke="#10B981" stroke-width="10"
                                    stroke-linecap="round"
                                    stroke-dasharray="314.159"
                                    stroke-dashoffset="{{ 314.159 - (314.159 * $activeCv->global_score / 100) }}" />
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                            <span class="text-4xl font-extrabold text-gray-900">{{ $activeCv->global_score }}</span>
                            <span class="text-xs font-semibold text-gray-400 -mt-1">/ 100</span>
                        </div>
                    </div>

                    <div class="flex-1 space-y-3 text-center md:text-left">
                        <div class="flex items-center justify-center md:justify-start gap-3 flex-wrap">
                            <h3 class="text-xl font-bold text-gray-900">Score Omnhi RH : {{ $activeCv->status_label }}</h3>
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">{{ $activeCv->global_score >= 70 ? 'Prêt pour le marché' : 'À perfectionner' }}</span>
                        </div>
                        <p class="text-sm text-gray-700 leading-relaxed">{{ $activeCv->summary }}</p>
                    </div>
                </div>

                <!-- Critères Détaillés (Débloqués à 100%) -->
                <div class="space-y-4 mb-10">
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>Détail des 5 piliers d'évaluation</span>
                    </h3>

                    <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-4">
                        @php
                            $crit = $activeCv->criteria_scores ?? ['structure' => 65, 'clarite' => 70, 'experiences' => 60, 'competences' => 68, 'impact' => 62];
                        @endphp
                        @foreach($crit as $criterion => $val)
                            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100 space-y-2">
                                <div class="flex items-center justify-between text-xs font-bold">
                                    <span class="capitalize text-gray-700">{{ $criterion }}</span>
                                    <span class="text-emerald-700">{{ $val }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $val }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Deux Colonnes : Points Forts & Axes d'Amélioration -->
                <div class="grid sm:grid-cols-2 gap-6 mb-10">
                    <!-- Points Forts -->
                    <div class="p-6 rounded-2xl bg-emerald-50/70 border border-emerald-100 space-y-4">
                        <div class="flex items-center gap-2.5 text-emerald-900 font-bold text-sm">
                            <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span>Points forts de votre CV</span>
                        </div>
                        <ul class="space-y-3 text-xs sm:text-sm text-emerald-950">
                            @foreach($activeCv->strengths ?? [] as $strength)
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-600 font-bold">•</span>
                                    <span>{{ $strength }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Axes d'Amélioration -->
                    <div class="p-6 rounded-2xl bg-amber-50/70 border border-amber-100 space-y-4">
                        <div class="flex items-center gap-2.5 text-amber-900 font-bold text-sm">
                            <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <span>Axes d'amélioration prioritaires</span>
                        </div>
                        <ul class="space-y-3 text-xs sm:text-sm text-amber-950">
                            @foreach($activeCv->improvements ?? [] as $improvement)
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 font-bold">•</span>
                                    <span>{{ $improvement }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Recommandations Personnalisées du Coach IA -->
                <div class="p-6 sm:p-8 rounded-2xl bg-gradient-to-r from-indigo-50 via-purple-50 to-blue-50 border border-indigo-100 space-y-4">
                    <div class="flex items-center gap-2.5 text-indigo-900 font-bold text-base">
                        <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <span>Conseils concrets du Coach IA Brillio</span>
                    </div>

                    <div class="grid sm:grid-cols-3 gap-4 pt-2">
                        @foreach($activeCv->recommendations ?? [] as $rec)
                            <div class="p-4 rounded-xl bg-white/80 border border-indigo-100/60 text-xs sm:text-sm text-gray-700 leading-relaxed shadow-2xs">
                                {{ $rec }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <!-- État vide : Aucun CV encore analysé dans le profil -->
            <div class="bg-white rounded-3xl p-12 text-center border border-gray-100">
                <div class="w-16 h-16 bg-emerald-100 rounded-2xl flex items-center justify-center text-emerald-600 mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Évaluez votre premier CV avec l'IA</h3>
                <p class="text-sm text-gray-500 max-w-md mx-auto mt-1 mb-6">Importez votre CV pour obtenir un diagnostic instantané, vos points forts, vos axes d'amélioration et booster votre employabilité.</p>
                <button @click="showCvUploadModal = true" class="px-6 py-3 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition shadow">
                    Analyser mon CV maintenant
                </button>
            </div>
        @endif
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL : AJOUT DE DOCUMENT ACADÉMIQUE (DRIVE)                              -->
    <!-- ========================================================================= -->
    <div x-show="showUploadModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
        @keydown.escape.window="showUploadModal = false">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs" @click="showUploadModal = false"></div>
            <div class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl z-10">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900">Ajouter un document au Drive</h3>
                    <button @click="showUploadModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form action="{{ route('jeune.documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Type de document</label>
                        <select name="document_type" required class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-hidden">
                            <option value="bulletin">Bulletin de notes</option>
                            <option value="diplome">Diplôme</option>
                            <option value="attestation">Attestation de réussite</option>
                            <option value="autre">Autre document</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Fichier (PDF ou image max 10 Mo)</label>
                        <input type="file" name="document" required accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100" />
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="showUploadModal = false" class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-700 font-semibold text-sm hover:bg-gray-50">Annuler</button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-600 text-white font-bold text-sm hover:bg-primary-700 transition">Téléverser</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL : ANALYSER UN NOUVEAU CV (ONGLET CV)                                -->
    <!-- ========================================================================= -->
    <div x-show="showCvUploadModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
        @keydown.escape.window="showCvUploadModal = false">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs" @click="showCvUploadModal = false"></div>
            <div class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl z-10">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Analyser un CV avec l'IA</h3>
                    <button @click="showCvUploadModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <p class="text-xs text-gray-500 mb-6">Importez votre CV mis à jour pour comparer votre score, détecter les évolutions et obtenir de nouveaux conseils de recrutement.</p>

                <form action="{{ route('jeune.cv.analyze') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Fichier CV (PDF, DOCX, JPG ou PNG)</label>
                        <input type="file" name="cv_file" required accept=".pdf,.docx,.png,.jpg,.jpeg" class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100" />
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="showCvUploadModal = false" class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-700 font-semibold text-sm hover:bg-gray-50">Annuler</button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 text-white font-bold text-sm hover:bg-emerald-700 transition">Lancer l'analyse IA</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL : APERÇU DOCUMENT DRIVE -->
    <div x-show="showPreviewModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showPreviewModal = false">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs" @click="showPreviewModal = false"></div>
            <div class="relative bg-white rounded-3xl p-6 max-w-4xl w-full shadow-2xl z-10 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <h3 class="font-bold text-gray-900 truncate" x-text="previewFileName">Aperçu</h3>
                    <button @click="showPreviewModal = false" class="text-gray-400 hover:text-gray-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                </div>
                <div class="h-[65vh] flex items-center justify-center bg-gray-50 rounded-2xl overflow-hidden">
                    <iframe :src="previewUrl" class="w-full h-full border-0"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL : CONFIRMATION SUPPRESSION DOCUMENT DRIVE -->
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs" @click="showDeleteModal = false"></div>
            <div class="relative bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl z-10 text-center space-y-4">
                <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></div>
                <h3 class="text-lg font-bold text-gray-900">Supprimer ce document ?</h3>
                <p class="text-xs text-gray-500">Cette action est irréversible et supprimera définitivement le fichier de votre Drive.</p>
                <div class="flex justify-center gap-3 pt-2">
                    <button type="button" @click="showDeleteModal = false" class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-700 font-semibold text-sm">Annuler</button>
                    <button type="button" @click="confirmDelete()" class="px-5 py-2.5 rounded-xl bg-red-600 text-white font-bold text-sm hover:bg-red-700">Supprimer</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function opportunitiesHubApp(initialTab) {
    return {
        currentTab: initialTab || 'drive',
        driveFilter: 'all',
        showUploadModal: false,
        showCvUploadModal: false,
        showPreviewModal: false,
        showDeleteModal: false,
        previewUrl: '',
        previewType: '',
        previewFileName: '',
        documentToDelete: null,

        setTab(tabName) {
            this.currentTab = tabName;
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tabName);
            window.history.pushState({}, '', url.toString());
        },

        previewDocument(id, mimeType, fileName) {
            this.previewUrl = `/espace-jeune/documents/${id}/view`;
            this.previewType = mimeType;
            this.previewFileName = fileName;
            this.showPreviewModal = true;
        },

        deleteDocument(id) {
            this.documentToDelete = id;
            this.showDeleteModal = true;
        },

        async confirmDelete() {
            if (!this.documentToDelete) return;
            try {
                const response = await fetch(`/espace-jeune/documents/${this.documentToDelete}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                if (response.ok) {
                    window.location.reload();
                }
            } catch (e) {
                console.error('Delete error', e);
            }
        }
    };
}
</script>
@endsection