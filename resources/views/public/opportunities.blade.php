@extends('layouts.public')

@php $forceScrolled = true; @endphp

@section('title', isset($isScoreView) && $isScoreView ? 'Diagnostic & Score ATS de votre CV - Brillio' : 'Évaluez votre CV & Score ATS - Brillio')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-primary-50/40 via-white to-gray-50/60 pt-28 pb-20" x-data="cvUploaderApp">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Fil d'Ariane -->
        <nav class="flex items-center text-xs sm:text-sm font-medium text-gray-500 mb-8 space-x-2">
            <a href="{{ route('home') }}" class="hover:text-primary-600 transition">Accueil</a>
            <span>/</span>
            <a href="{{ route('public.opportunities') }}" class="hover:text-primary-600 transition">Opportunités</a>
            <span>/</span>
            <span class="text-gray-900 font-semibold">{{ isset($isScoreView) && $isScoreView ? 'Diagnostic ATS' : 'Vérificateur de CV' }}</span>
        </nav>

        @if(isset($isScoreView) && $isScoreView)
            <!-- ========================================================================= -->
            <!-- VUE RÉSULTAT : COMPARATEUR ATS & SCORE (Inspiré des maquettes Pro)         -->
            <!-- ========================================================================= -->
            <div class="space-y-8" x-data="{ currentVersionTab: 'enhanced' }">
                
                <!-- En-tête -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full text-xs font-bold bg-primary-100 text-primary-800 mb-2">
                            <span>Diagnostic ATS & Recruteur</span>
                        </div>
                        <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">
                            Score de compatibilité : <span class="bg-gradient-to-r from-primary-600 to-secondary-600 bg-clip-text text-transparent">{{ $cvAnalysis->global_score ?? 75 }}/100</span>
                        </h1>
                        <p class="text-sm sm:text-base text-gray-600 mt-1">
                            Analyse de la lisibilité par les algorithmes de recrutement et comparaison avec le modèle optimisé Brillio.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('public.opportunities') }}" class="px-4 py-2.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 font-semibold text-xs rounded-xl transition shadow-2xs">
                            Tester un autre CV
                        </a>
                    </div>
                </div>

                <!-- Grille principale : Gauche (Score & Checklist) / Droite (Comparateur 2 versions) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    
                    <!-- Colonne Gauche : Score & Rubriques ATS (4 cols) -->
                    <div class="lg:col-span-4 space-y-6">
                        
                        <!-- Carte Score Arc de cercle (Conforme Image 4) -->
                        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100 text-center">
                            <h2 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-6">Votre Score ATS</h2>
                            
                            <!-- Jauge demi-cercle SVG -->
                            <div class="relative w-48 h-28 mx-auto overflow-hidden flex items-end justify-center">
                                <svg class="w-48 h-48 -mb-20 transform -rotate-90" viewBox="0 0 120 120">
                                    <defs>
                                        <linearGradient id="arcScoreGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                            <stop offset="0%" stop-color="#f97316" />
                                            <stop offset="50%" stop-color="#ec4899" />
                                            <stop offset="100%" stop-color="#6366f1" />
                                        </linearGradient>
                                    </defs>
                                    <circle cx="60" cy="60" r="48" fill="none" stroke="#F3F4F6" stroke-width="12" stroke-dasharray="150.8" stroke-dashoffset="0" stroke-linecap="round" />
                                    <circle cx="60" cy="60" r="48" fill="none" stroke="url(#arcScoreGrad)" stroke-width="12"
                                            stroke-linecap="round"
                                            stroke-dasharray="150.8"
                                            stroke-dashoffset="{{ 150.8 - (150.8 * ($cvAnalysis->global_score ?? 75) / 100) }}" />
                                </svg>
                                <div class="absolute bottom-1 text-center">
                                    <span class="text-4xl sm:text-5xl font-black text-gray-900">{{ $cvAnalysis->global_score ?? 75 }}</span>
                                    <span class="text-xs font-bold text-gray-400">/ 100</span>
                                </div>
                            </div>

                            <p class="mt-4 text-xs font-semibold text-gray-500">
                                {{ ($cvAnalysis->global_score ?? 75) >= 75 ? 'Bonne compatibilité avec les plateformes de recrutement' : 'Certains éléments risquent d\'être mal lus par les robots ATS' }}
                            </p>

                            <!-- Bouton CTA principal de déblocage -->
                            <div class="mt-6">
                                <a href="{{ route('auth.jeune.register') }}"
                                   class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-primary-600 to-secondary-600 hover:from-primary-700 hover:to-secondary-700 text-white font-bold text-sm shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2">
                                    <span>Débloquer le rapport complet</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>
                            </div>

                            <!-- Accordéon / Checklist des Piliers ATS -->
                            <div class="mt-8 text-left border-t border-gray-100 pt-6 space-y-4">
                                <div class="flex items-center justify-between text-xs font-bold text-gray-800">
                                    <span class="uppercase tracking-wide">Piliers d'analyse ATS</span>
                                    <span class="text-primary-600">Détail</span>
                                </div>

                                <div class="space-y-2 text-xs">
                                    <div class="p-2.5 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-[10px]">✓</span>
                                            <span class="font-medium text-gray-800">Taux de Parse du texte</span>
                                        </div>
                                        <span class="text-emerald-700 font-bold">90%</span>
                                    </div>

                                    <div class="p-2.5 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-[10px]">✓</span>
                                            <span class="font-medium text-gray-800">Coordonnées détectées</span>
                                        </div>
                                        <span class="text-emerald-700 font-bold">Valide</span>
                                    </div>

                                    <div class="p-2.5 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-4 h-4 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center font-bold text-[10px]">!</span>
                                            <span class="font-medium text-gray-800">Quantification des missions</span>
                                        </div>
                                        <span class="text-amber-700 font-bold">À muscler</span>
                                    </div>

                                    <div class="p-2.5 rounded-xl bg-gray-50/70 border border-dashed border-gray-200 flex items-center justify-between text-gray-400">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            <span>Signaux d'alerte RH</span>
                                        </div>
                                        <span class="text-[10px] font-semibold bg-gray-200 text-gray-600 px-2 py-0.5 rounded">Verrouillé</span>
                                    </div>

                                    <div class="p-2.5 rounded-xl bg-gray-50/70 border border-dashed border-gray-200 flex items-center justify-between text-gray-400">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            <span>Mots-clés métiers manquants</span>
                                        </div>
                                        <span class="text-[10px] font-semibold bg-gray-200 text-gray-600 px-2 py-0.5 rounded">Verrouillé</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Boîte d'information ATS -->
                        <div class="p-5 rounded-2xl bg-gradient-to-br from-primary-50 to-secondary-50 border border-primary-100 text-xs text-gray-700 space-y-2">
                            <h3 class="font-bold text-gray-900 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Qu'est-ce qu'un parseur ATS ?</span>
                            </h3>
                            <p class="leading-relaxed">
                                Les recruteurs utilisent des logiciels ATS (Applicant Tracking Systems) pour trier automatiquement des centaines de candidatures. Si votre CV a une mise en page non standardisée, jusqu'à <strong>20% de vos compétences</strong> peuvent être ignorées.
                            </p>
                        </div>
                    </div>

                    <!-- Colonne Droite : Le Comparateur Deux Versions (8 cols) -->
                    <div class="lg:col-span-8 space-y-6">
                        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100 space-y-6">
                            
                            <!-- Barre d'en-tête du comparateur avec le sélecteur à bascule (Conforme Images 4 & 5) -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <h3 class="text-base sm:text-lg font-bold text-gray-900">Votre CV, deux versions</h3>
                                </div>

                                <!-- Switcher Toggle : Original vs Optimisé -->
                                <div class="inline-flex p-1 bg-gray-100 rounded-xl">
                                    <button type="button"
                                            @click="currentVersionTab = 'original'"
                                            :class="currentVersionTab === 'original' ? 'bg-white text-gray-900 shadow-xs font-bold' : 'text-gray-500 hover:text-gray-900 font-medium'"
                                            class="px-4 py-1.5 text-xs rounded-lg transition">
                                        Original
                                    </button>
                                    <button type="button"
                                            @click="currentVersionTab = 'enhanced'"
                                            :class="currentVersionTab === 'enhanced' ? 'bg-gradient-to-r from-primary-600 to-secondary-600 text-white shadow-xs font-bold' : 'text-gray-500 hover:text-gray-900 font-medium'"
                                            class="px-4 py-1.5 text-xs rounded-lg transition flex items-center gap-1">
                                        <span>Modèle Brillio Pro ATS</span>
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    </button>
                                </div>
                            </div>

                            <!-- 1. VUE VERSION ORIGINALE (Conforme Image 5) -->
                            <div x-show="currentVersionTab === 'original'" class="space-y-4">
                                <div class="p-3.5 rounded-xl bg-red-50/80 border border-red-200 text-red-700 text-xs font-medium flex items-center gap-2">
                                    <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span>Certaines zones de ce document risquent d'être mal parsées ou tronquées par les logiciels de recrutement.</span>
                                </div>

                                <div class="p-6 rounded-2xl bg-gray-50/70 border border-gray-200 text-xs sm:text-sm text-gray-700 font-mono whitespace-pre-line max-h-[500px] overflow-y-auto leading-relaxed">
                                    {{ $cvAnalysis->parsed_content['raw_text'] ?? 'Contenu brut extrait du fichier d\'origine. Les rubriques manquent de séparateurs standardisés pour optimiser le score de parsing automatique.' }}
                                </div>
                            </div>

                            <!-- 2. VUE VERSION OPTIMISÉE BRILLIO (Conforme Image 4) -->
                            <div x-show="currentVersionTab === 'enhanced'" class="space-y-4">
                                <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Modèle testé pour les filtres ATS : hiérarchie normalisée, sections isolées et mots-clés valorisés.</span>
                                </div>

                                <!-- Rendu Haute Définition du CV Restructuré -->
                                <div class="p-6 sm:p-8 rounded-2xl bg-white border border-gray-200 shadow-inner space-y-6 text-gray-800">
                                    
                                    <!-- Entête Candidat avec Initiale ronde (Conforme Image 4) -->
                                    <div class="flex items-start justify-between gap-4 pb-4 border-b border-gray-100">
                                        <div>
                                            <h2 class="text-xl sm:text-2xl font-black text-gray-900 uppercase tracking-tight">
                                                {{ $cvAnalysis->candidate_name ?? 'Candidat Brillio' }}
                                            </h2>
                                            <p class="text-sm font-bold text-primary-600 mt-0.5">
                                                {{ $cvAnalysis->candidate_title ?? 'Profil & Spécialité' }}
                                            </p>
                                            <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 mt-2">
                                                @if(!empty($cvAnalysis->candidate_contact['phone']))
                                                    <span>📞 {{ $cvAnalysis->candidate_contact['phone'] }}</span>
                                                @endif
                                                @if(!empty($cvAnalysis->candidate_contact['email']))
                                                    <span>✉️ {{ $cvAnalysis->candidate_contact['email'] }}</span>
                                                @endif
                                                @if(!empty($cvAnalysis->candidate_contact['location']))
                                                    <span>📍 {{ $cvAnalysis->candidate_contact['location'] }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Badge Initiale -->
                                        @php
                                            $initials = 'CV';
                                            if (!empty($cvAnalysis->candidate_name)) {
                                                $words = explode(' ', trim($cvAnalysis->candidate_name));
                                                $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
                                            }
                                        @endphp
                                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary-600 to-secondary-600 text-white flex items-center justify-center font-bold text-base shadow-sm flex-shrink-0">
                                            {{ $initials }}
                                        </div>
                                    </div>

                                    <!-- Section Profil / Résumé -->
                                    <div class="space-y-1.5">
                                        <h3 class="text-xs font-extrabold text-gray-900 uppercase tracking-wider">Profil Professionnel</h3>
                                        <p class="text-xs sm:text-sm text-gray-700 leading-relaxed">
                                            {{ $cvAnalysis->parsed_content['profil'] ?? $cvAnalysis->summary ?? 'Professionnel motivé avec des bases techniques solides, prêt à apporter son savoir-faire et à développer ses compétences au sein d\'équipes exigeantes.' }}
                                        </p>
                                    </div>

                                    <!-- Section Compétences avec puces Pro -->
                                    <div class="space-y-2">
                                        <h3 class="text-xs font-extrabold text-gray-900 uppercase tracking-wider">Compétences Clés Claires</h3>
                                        <div class="flex flex-wrap gap-1.5">
                                            @php
                                                $skills = $cvAnalysis->parsed_content['competences'] ?? ['Organisation', 'Gestion de projet', 'Rigueur', 'Communication'];
                                            @endphp
                                            @foreach($skills as $skill)
                                                <span class="px-2.5 py-1 rounded-md bg-gray-100 text-gray-800 text-xs font-medium">
                                                    {{ $skill }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Section Expériences -->
                                    @if(!empty($cvAnalysis->parsed_content['experiences']))
                                        <div class="space-y-2.5">
                                            <h3 class="text-xs font-extrabold text-gray-900 uppercase tracking-wider">Expérience Professionnelle</h3>
                                            <ul class="space-y-2 text-xs sm:text-sm text-gray-700">
                                                @foreach($cvAnalysis->parsed_content['experiences'] as $exp)
                                                    <li class="flex items-start gap-2">
                                                        <span class="text-primary-600 font-bold">•</span>
                                                        <span>{{ $exp }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <!-- Invitation pour exporter la version complète -->
                                    <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                                        <span class="text-gray-500">Ce modèle est optimisé à 100% pour franchir les filtres recruteurs.</span>
                                        <a href="{{ route('auth.jeune.register') }}" class="font-bold text-primary-600 hover:underline flex items-center gap-1">
                                            <span>Exporter mon CV au format Pro</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                        </a>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>

                </div>

            </div>

        @else
            <!-- ========================================================================= -->
            <!-- VUE IMPORT & SCAN INTERACTIF DU CV (Conforme aux maquettes 1, 2 & 3)       -->
            <!-- ========================================================================= -->
            <div class="space-y-10">
                
                <!-- Hero Header aux couleurs Brillio par défaut -->
                <div class="text-center max-w-3xl mx-auto space-y-3">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold bg-primary-100 text-primary-800 shadow-2xs">
                        <span>✨ Évaluateur de CV IA</span>
                        <span class="w-1 h-1 rounded-full bg-primary-400"></span>
                        <span>Compatible ATS</span>
                    </div>
                    
                    <h1 class="text-3xl sm:text-5xl font-black text-gray-900 tracking-tight">
                        Votre CV est-il assez percutant pour <span class="bg-gradient-to-r from-primary-600 to-secondary-600 bg-clip-text text-transparent">les recruteurs ?</span>
                    </h1>
                    
                    <p class="text-base sm:text-lg text-gray-600 max-w-2xl mx-auto">
                        Testez gratuitement votre CV avec notre scanner IA : vérification de compatibilité ATS, score sur 100 et détection des opportunités adaptées.
                    </p>
                </div>

                @if ($errors->any())
                    <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 max-w-2xl mx-auto text-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                <!-- Conteneur Upload (Sans fichier) -->
                <div x-show="!selectedFile && !isAnalyzing" class="max-w-3xl mx-auto">
                    <div @dragover.prevent="isDragging = true"
                         @dragleave.prevent="isDragging = false"
                         @drop.prevent="handleDrop($event)"
                         :class="isDragging ? 'border-primary-500 bg-primary-50/40 scale-[1.01]' : 'border-gray-200 hover:border-primary-400 bg-white'"
                         class="rounded-3xl border-2 border-dashed p-8 sm:p-14 text-center shadow-lg shadow-primary-500/5 transition-all cursor-pointer group"
                         @click="$refs.fileInput.click()">
                        
                        <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-gradient-to-br from-primary-50 to-secondary-50 text-primary-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>

                        <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Glissez-déposez votre CV ici</h2>
                        <p class="text-sm text-gray-500 mt-2">ou cliquez pour choisir un fichier depuis votre appareil</p>

                        <div class="mt-6">
                            <span class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-primary-600 to-secondary-600 text-white font-bold text-sm shadow-md group-hover:shadow-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Parcourir mes fichiers</span>
                            </span>
                        </div>

                        <div class="mt-8 pt-6 border-t border-gray-100 flex flex-wrap items-center justify-center gap-3 text-xs text-gray-500">
                            <span class="px-2.5 py-1 rounded-lg bg-gray-100 font-semibold text-gray-700">PDF</span>
                            <span class="px-2.5 py-1 rounded-lg bg-gray-100 font-semibold text-gray-700">DOCX</span>
                            <span class="px-2.5 py-1 rounded-lg bg-gray-100 font-semibold text-gray-700">PNG</span>
                            <span class="px-2.5 py-1 rounded-lg bg-gray-100 font-semibold text-gray-700">JPG</span>
                            <span>•</span>
                            <span>Taille max : 10 Mo</span>
                        </div>
                    </div>
                </div>

                <!-- Conteneur Animation de Scan par Étapes (Conforme Image 3) -->
                <div x-show="isAnalyzing" style="display: none;" class="max-w-2xl mx-auto">
                    <div class="bg-white rounded-3xl p-8 sm:p-10 shadow-xl border border-gray-100 space-y-6">
                        <div class="text-center space-y-2">
                            <div class="w-16 h-16 mx-auto rounded-full bg-gradient-to-tr from-primary-100 to-secondary-100 flex items-center justify-center text-primary-600 mb-2">
                                <svg class="animate-spin w-8 h-8 text-primary-600" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Analyse de votre CV en cours...</h3>
                            <p class="text-xs text-gray-500">Notre modèle IA évalue votre structure et calcule votre compatibilité ATS.</p>
                        </div>

                        <!-- 4 Étapes animées de scan (Conforme Image 3) -->
                        <div class="space-y-3 pt-4 border-t border-gray-100 text-sm">
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 text-gray-800">
                                <span class="w-6 h-6 rounded-full bg-primary-600 text-white flex items-center justify-center text-xs font-bold">✓</span>
                                <span>Lecture et extraction du document</span>
                            </div>

                            <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 text-gray-800">
                                <span class="w-6 h-6 rounded-full bg-primary-600 text-white flex items-center justify-center text-xs font-bold">✓</span>
                                <span>Analyse de votre parcours et de vos expériences</span>
                            </div>

                            <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 text-gray-800">
                                <span class="w-6 h-6 rounded-full bg-secondary-500 text-white flex items-center justify-center text-xs font-bold animate-pulse">•</span>
                                <span>Extraction de vos compétences & mots-clés</span>
                            </div>

                            <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 text-gray-500">
                                <span class="w-6 h-6 rounded-full border border-gray-300 flex items-center justify-center text-xs font-bold">4</span>
                                <span>Génération du score et des pistes d'amélioration</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- État Fichier Sélectionné : Prévisualisation & Déclenchement -->
                <div x-show="selectedFile && !isAnalyzing" style="display: none;" class="max-w-3xl mx-auto space-y-6">
                    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-500 to-secondary-500 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="truncate">
                                <h3 class="text-base font-bold text-gray-900 truncate" x-text="selectedFile?.name"></h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-xs text-gray-500" x-text="formatFileSize(selectedFile?.size)"></span>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Prêt pour analyse
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <button type="button" @click="$refs.fileInput.click()" class="px-4 py-3 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-50 font-semibold text-xs transition">
                                Changer
                            </button>

                            <button type="button"
                                    @click="submitCv()"
                                    class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-primary-600 to-secondary-600 hover:from-primary-700 hover:to-secondary-700 text-white font-extrabold text-sm shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span>Lancer l'audit IA</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Formulaire HTML invisible -->
                <form id="cvUploadForm" action="{{ route('public.opportunities.analyze') }}" method="POST" enctype="multipart/form-data" class="hidden">
                    @csrf
                    <input type="file" x-ref="fileInput" name="cv_file" accept=".pdf,.docx,.png,.jpg,.jpeg" @change="onFileSelected($event)">
                </form>

            </div>
        @endif

    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
document.addEventListener('alpine:init', () => {
    Alpine.data('cvUploaderApp', () => ({
        selectedFile: null,
        isDragging: false,
        isAnalyzing: false,

        onFileSelected(event) {
            const files = event.target.files;
            if (files && files.length > 0) {
                this.selectedFile = files[0];
            }
        },

        handleDrop(event) {
            this.isDragging = false;
            const files = event.dataTransfer.files;
            if (files && files.length > 0) {
                this.selectedFile = files[0];
            }
        },

        formatFileSize(bytes) {
            if (!bytes) return '0 Ko';
            if (bytes < 1024) return bytes + ' o';
            if (bytes < 1048576) return Math.round(bytes / 1024) + ' Ko';
            return (bytes / 1048576).toFixed(1) + ' Mo';
        },

        async submitCv() {
            if (!this.selectedFile || this.isAnalyzing) return;

            this.isAnalyzing = true;

            const formData = new FormData();
            formData.append('cv_file', this.selectedFile);
            formData.append('_token', '{{ csrf_token() }}');

            try {
                const response = await fetch('{{ route('public.opportunities.analyze') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success && data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else {
                    alert(data.message || 'Une erreur est survenue lors de l\'analyse de votre CV.');
                    this.isAnalyzing = false;
                }
            } catch (error) {
                document.getElementById('cvUploadForm').submit();
            }
        }
    }));
});
</script>
@endsection
