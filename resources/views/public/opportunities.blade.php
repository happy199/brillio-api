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

                                @php
                                    $scores = $cvAnalysis->criteria_scores ?? [];
                                    $scoreStructure = $scores['structure'] ?? 70;
                                    $scoreClarite   = $scores['clarite']   ?? 70;
                                    $scoreExperiences = $scores['experiences'] ?? 70;
                                    $scoreCompetences = $scores['competences'] ?? 70;
                                    $scoreImpact    = $scores['impact']    ?? 50;

                                    // Helper : retourne les classes CSS selon le niveau du score
                                    $pilierClasses = function(int $s): array {
                                        if ($s >= 75) return ['bg-emerald-100', 'text-emerald-600', '✓', 'text-emerald-700'];
                                        if ($s >= 50) return ['bg-amber-100',   'text-amber-600',   '!', 'text-amber-700'];
                                        return                ['bg-red-100',     'text-red-600',     '✕', 'text-red-700'];
                                    };

                                    [$structBg, $structIcon, $structSym, $structVal] = $pilierClasses($scoreStructure);
                                    [$expBg,    $expIcon,    $expSym,    $expVal]    = $pilierClasses($scoreExperiences);
                                    [$impBg,    $impIcon,    $impSym,    $impVal]    = $pilierClasses($scoreImpact);

                                    $structLabel = $scoreStructure >= 75 ? $scoreStructure . '%' : ($scoreStructure >= 50 ? 'À renforcer' : 'Insuffisant');
                                    $expLabel    = $scoreExperiences >= 75 ? 'Solide' : ($scoreExperiences >= 50 ? 'À muscler' : 'Incomplet');
                                    $impLabel    = $scoreImpact >= 75 ? 'Quantifié' : ($scoreImpact >= 50 ? 'À muscler' : 'Manquant');
                                @endphp
                                <div class="space-y-2 text-xs">
                                    {{-- Structure / Taux de Parse --}}
                                    <div class="p-2.5 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-4 h-4 rounded-full {{ $structBg }} {{ $structIcon }} flex items-center justify-center font-bold text-[10px]">{{ $structSym }}</span>
                                            <span class="font-medium text-gray-800">Structure & Parse ATS</span>
                                        </div>
                                        <span class="{{ $structVal }} font-bold">{{ $structLabel }}</span>
                                    </div>

                                    {{-- Expériences --}}
                                    <div class="p-2.5 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-4 h-4 rounded-full {{ $expBg }} {{ $expIcon }} flex items-center justify-center font-bold text-[10px]">{{ $expSym }}</span>
                                            <span class="font-medium text-gray-800">Expériences & Parcours</span>
                                        </div>
                                        <span class="{{ $expVal }} font-bold">{{ $expLabel }}</span>
                                    </div>

                                    {{-- Impact / Quantification --}}
                                    <div class="p-2.5 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-4 h-4 rounded-full {{ $impBg }} {{ $impIcon }} flex items-center justify-center font-bold text-[10px]">{{ $impSym }}</span>
                                            <span class="font-medium text-gray-800">Quantification des missions</span>
                                        </div>
                                        <span class="{{ $impVal }} font-bold">{{ $impLabel }}</span>
                                    </div>

                                    {{-- Signaux RH – verrouillé --}}
                                    <div class="p-2.5 rounded-xl bg-gray-50/70 border border-dashed border-gray-200 flex items-center justify-between text-gray-400">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            <span>Signaux d'alerte RH</span>
                                        </div>
                                        <span class="text-[10px] font-semibold bg-gray-200 text-gray-600 px-2 py-0.5 rounded">Verrouillé</span>
                                    </div>

                                    {{-- Mots-clés – verrouillé --}}
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

                            <!-- 2. VUE VERSION OPTIMISÉE BRILLIO (Conditionnée à l'inscription) -->
                            <div x-show="currentVersionTab === 'enhanced'" class="space-y-4">
                                <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Modèle testé pour les filtres ATS : hiérarchie normalisée, sections isolées et mots-clés valorisés.</span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-md bg-emerald-200/60 text-emerald-900 text-[10px] font-bold uppercase">Réservé membres</span>
                                </div>

                                <!-- Rendu avec Déblocage et Flou partiel -->
                                <div class="relative rounded-2xl overflow-hidden border border-gray-200 bg-white">
                                    
                                    <!-- Entête Candidat Visible -->
                                    <div class="p-6 sm:p-8 pb-4 border-b border-gray-100 flex items-start justify-between gap-4">
                                        <div>
                                            <h2 class="text-xl sm:text-2xl font-black text-gray-900 uppercase tracking-tight">
                                                {{ $cvAnalysis->candidate_name ?? 'Candidat Brillio' }}
                                            </h2>
                                            <p class="text-sm font-bold text-primary-600 mt-0.5">
                                                {{ $cvAnalysis->candidate_title ?? 'Profil & Spécialité' }}
                                            </p>
                                            <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 mt-2">
                                                <span>📞 Coordonnées normalisées</span>
                                                <span>✉️ Email certifié</span>
                                                <span>📍 Localisation standardisée</span>
                                            </div>
                                        </div>

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

                                    <!-- Corps Flouté du CV (Protégé côté serveur : faux squelette / dummy text pour empêcher l'inspection DevTools) -->
                                    <div class="p-6 sm:p-8 space-y-6 select-none pointer-events-none filter blur-[6px] opacity-30" aria-hidden="true">
                                        <!-- Profil squelette -->
                                        <div class="space-y-2">
                                            <div class="h-3 w-32 bg-gray-900 rounded"></div>
                                            <div class="h-3 w-full bg-gray-300 rounded"></div>
                                            <div class="h-3 w-5/6 bg-gray-300 rounded"></div>
                                            <div class="h-3 w-2/3 bg-gray-300 rounded"></div>
                                        </div>

                                        <!-- Compétences squelette -->
                                        <div class="space-y-2">
                                            <div class="h-3 w-40 bg-gray-900 rounded"></div>
                                            <div class="flex flex-wrap gap-2 pt-1">
                                                <div class="h-6 w-24 bg-gray-200 rounded-md"></div>
                                                <div class="h-6 w-32 bg-gray-200 rounded-md"></div>
                                                <div class="h-6 w-20 bg-gray-200 rounded-md"></div>
                                                <div class="h-6 w-28 bg-gray-200 rounded-md"></div>
                                            </div>
                                        </div>

                                        <!-- Expériences squelette avec puces -->
                                        <div class="space-y-3">
                                            <div class="h-3 w-48 bg-gray-900 rounded"></div>
                                            <div class="space-y-2">
                                                <div class="h-3 w-full bg-gray-300 rounded"></div>
                                                <div class="h-3 w-11/12 bg-gray-300 rounded"></div>
                                                <div class="h-3 w-4/5 bg-gray-300 rounded"></div>
                                            </div>
                                            <div class="space-y-2 pt-2">
                                                <div class="h-3 w-full bg-gray-300 rounded"></div>
                                                <div class="h-3 w-3/4 bg-gray-300 rounded"></div>
                                            </div>
                                        </div>

                                        <!-- Formation squelette -->
                                        <div class="space-y-2">
                                            <div class="h-3 w-36 bg-gray-900 rounded"></div>
                                            <div class="h-3 w-2/3 bg-gray-300 rounded"></div>
                                        </div>

                                        <!-- Certifications squelette -->
                                        <div class="space-y-2">
                                            <div class="h-3 w-44 bg-gray-900 rounded"></div>
                                            <div class="h-3 w-1/2 bg-gray-300 rounded"></div>
                                        </div>

                                        <!-- Langues squelette -->
                                        <div class="space-y-2">
                                            <div class="h-3 w-28 bg-gray-900 rounded"></div>
                                            <div class="flex gap-2">
                                                <div class="h-5 w-20 bg-gray-200 rounded"></div>
                                                <div class="h-5 w-24 bg-gray-200 rounded"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Overlay de Déblocage (Conforme Image 4 & 5) -->
                                    <div class="absolute inset-0 z-10 flex items-center justify-center p-4 bg-white/40 backdrop-blur-[2px]">
                                        <div class="max-w-md w-full p-6 sm:p-8 rounded-3xl bg-white shadow-2xl border border-gray-100 text-center space-y-4">
                                            <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-br from-primary-500 to-secondary-500 text-white flex items-center justify-center shadow-md">
                                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            </div>
                                            
                                            <div class="space-y-1.5">
                                                <h4 class="text-lg sm:text-xl font-black text-gray-900">Débloquez le Modèle Brillio Pro ATS</h4>
                                                <p class="text-xs text-gray-600 leading-relaxed">
                                                    Créez votre compte gratuit pour accéder au modèle complet restructuré (avec <strong>réalisations quantifiées</strong>, <strong>formation</strong>, <strong>certifications</strong> et <strong>langues</strong>) et le télécharger en PDF.
                                                </p>
                                            </div>

                                            <div class="pt-2">
                                                <a href="{{ route('auth.jeune.register') }}"
                                                   class="inline-flex w-full items-center justify-center gap-2 py-3.5 px-6 rounded-xl bg-gradient-to-r from-primary-600 to-secondary-600 hover:from-primary-700 hover:to-secondary-700 text-white font-black text-sm shadow-md hover:shadow-lg transition">
                                                    <span>Créer mon compte & Débloquer mon CV Pro</span>
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                                </a>
                                                <p class="text-[11px] text-gray-400 mt-2">100% gratuit · Accès immédiat dans votre Espace Jeune</p>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>

                        {{-- ===== BANNIÈRE CERTIFICATION ATS BRILLIO ===== --}}
                        @php
                            $globalScore = $cvAnalysis->global_score ?? 75;
                            $isCertified = $globalScore >= 65;
                        @endphp
                        <div class="rounded-2xl p-5 border flex flex-col sm:flex-row sm:items-center gap-4 {{ $isCertified ? 'bg-gradient-to-r from-emerald-50 to-teal-50 border-emerald-200' : 'bg-gradient-to-r from-amber-50 to-orange-50 border-amber-200' }}">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 {{ $isCertified ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600' }}">
                                @if($isCertified)
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                                @else
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-black {{ $isCertified ? 'text-emerald-900' : 'text-amber-900' }}">
                                    {{ $isCertified ? '✅ Votre CV est compatible ATS' : '⚠️ Optimisation ATS recommandée' }}
                                </h4>
                                <p class="text-xs mt-0.5 {{ $isCertified ? 'text-emerald-700' : 'text-amber-700' }}">
                                    {{ $isCertified
                                        ? 'Votre CV atteint le seuil de compatibilité Brillio ATS (' . $globalScore . '/100). Inscrivez-vous pour télécharger la version Pro optimisée.'
                                        : 'Votre score (' . $globalScore . '/100) est en dessous du seuil recommandé. Le modèle Brillio Pro corrige automatiquement ces points faibles.' }}
                                </p>
                            </div>
                            <a href="{{ route('auth.jeune.register') }}"
                               class="flex-shrink-0 px-4 py-2.5 rounded-xl text-white text-xs font-bold shadow-sm hover:shadow-md transition {{ $isCertified ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-gradient-to-r from-primary-600 to-secondary-600 hover:from-primary-700 hover:to-secondary-700' }}">
                                {{ $isCertified ? 'Télécharger mon CV Pro' : 'Optimiser maintenant' }}
                            </a>
                        </div>

                        <!-- ========================================================================= -->
                        <!-- MODULES D'AUDIT CONTENU & ATS DÉTAILLÉS (Conforme aux maquettes 2, 3, 4)   -->
                        <!-- ========================================================================= -->
                        <div class="space-y-6">
                            
                            <!-- Module 1 : TAUX DE PARSE ATS (Dynamique) -->
                            @php
                                $parseScore = $scores['structure'] ?? 70;
                                $missedPct  = 100 - $parseScore;
                                $parseLabel = $parseScore >= 75 ? $parseScore . '% Lisible' : ($parseScore >= 50 ? $parseScore . '% Partiel' : $parseScore . '% Faible');
                                $parseLabelClass = $parseScore >= 75 ? 'text-emerald-600' : ($parseScore >= 50 ? 'text-amber-600' : 'text-red-600');
                                $parseBarClass   = $parseScore >= 75 ? 'bg-emerald-500' : ($parseScore >= 50 ? 'bg-amber-400' : 'bg-red-400');
                                $parseMsgClass   = $parseScore >= 75 ? 'text-emerald-700' : ($parseScore >= 50 ? 'text-amber-700' : 'text-red-700');
                                $parseMsg = $parseScore >= 75
                                    ? 'Excellente structure ! Vos rubriques sont bien reconnues par les robots ATS.'
                                    : ($parseScore >= 50
                                        ? 'Quelques zones de votre CV risquent d\'être mal lues. Le modèle Brillio Pro corrige automatiquement ces points.'
                                        : 'Attention : une part importante de vos informations risque d\'être ignorée par les ATS. Une restructuration est fortement recommandée.');
                            @endphp
                            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100 space-y-4">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-2.5 h-2.5 rounded-full bg-primary-600"></div>
                                        <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Taux de Parse ATS</h4>
                                    </div>
                                    <span class="text-xs font-bold {{ $parseLabelClass }}">{{ $parseLabel }}</span>
                                </div>
                                <p class="text-xs text-gray-600 leading-relaxed">
                                    Les recruteurs utilisent des Systèmes de Suivi des Candidatures (ATS) pour scanner les CV à grande échelle. Un taux élevé signifie que l'algorithme extrait parfaitement vos compétences.
                                </p>
                                <div class="space-y-2 pt-2">
                                    <div class="w-full bg-gray-100 rounded-full h-3 flex overflow-hidden">
                                        <div class="{{ $parseBarClass }} h-3 rounded-l-full" style="width: {{ $parseScore }}%"></div>
                                        <div class="bg-red-400/50 h-3 rounded-r-full" style="width: {{ $missedPct }}%"></div>
                                    </div>
                                    <div class="flex items-center justify-between text-[11px] text-gray-500">
                                        <span class="{{ $parseMsgClass }} font-bold">{{ $parseScore }}% lus par l'ATS</span>
                                        <span class="text-red-600 font-bold">{{ $missedPct }}% manqués</span>
                                    </div>
                                </div>
                                <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100 text-center text-xs {{ $parseMsgClass }} font-medium">
                                    {{ $parseMsg }}
                                </div>
                            </div>

                            <!-- Module 2 : QUANTIFIER L'IMPACT (Dynamique) -->
                            @php
                                $impactScore = $scores['impact'] ?? 50;
                                $impactGood  = $impactScore >= 75;
                                $impactOk    = $impactScore >= 50;

                                $impactBadgeClass = $impactGood ? 'bg-emerald-100 text-emerald-800' : ($impactOk ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800');
                                $impactBadgeText  = $impactGood ? '✓ Bien quantifié' : ($impactOk ? 'À muscler' : 'Insuffisant');

                                $impactAlertClass = $impactGood
                                    ? 'bg-emerald-50/70 border-emerald-200 text-emerald-800'
                                    : ($impactOk ? 'bg-amber-50/70 border-amber-200 text-amber-800' : 'bg-red-50/70 border-red-200 text-red-800');
                                $impactAlertIcon  = $impactGood ? '✓' : '✕';
                                $impactAlertIconClass = $impactGood ? 'text-emerald-500' : ($impactOk ? 'text-amber-500' : 'text-red-500');
                                $impactAlertMsg = $impactGood
                                    ? 'Excellent ! Vos expériences contiennent des résultats mesurables qui renforcent votre crédibilité.'
                                    : ($impactOk
                                        ? 'Votre section d\'expérience manque de quelques données chiffrées. Ajoutez des % ou montants pour booster votre profil.'
                                        : 'Votre section d\'expérience manque d\'accomplissements chiffrés sur vos postes précédents.');
                            @endphp
                            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100 space-y-4">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-2.5 h-2.5 rounded-full bg-accent-600"></div>
                                        <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Quantifier l'Impact</h4>
                                    </div>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $impactBadgeClass }}">{{ $impactBadgeText }}</span>
                                </div>
                                <p class="text-xs text-gray-600 leading-relaxed">
                                    Un bon CV démontre l'impact avec des chiffres (ex: % de croissance, temps gagné, budget géré). Quantifiez vos résultats pour doubler vos invitations en entretien.
                                </p>

                                <div class="p-4 rounded-2xl border {{ $impactAlertClass }} text-xs flex items-center gap-2.5">
                                    <span class="{{ $impactAlertIconClass }} font-bold text-base">{{ $impactAlertIcon }}</span>
                                    <span>{{ $impactAlertMsg }}</span>
                                </div>

                                <!-- Suggestions floutées avec CTA Brillio Pro -->
                                <div class="relative rounded-2xl overflow-hidden border border-gray-200 p-6 space-y-3 bg-gray-50/50">
                                    <div class="select-none pointer-events-none filter blur-xs opacity-35 space-y-2 text-xs text-gray-700">
                                        <p class="flex items-center gap-2"><span class="text-emerald-500">✓</span> Réalisé une progression de 35% des flux en automatisant la chaîne CI/CD.</p>
                                        <p class="flex items-center gap-2"><span class="text-emerald-500">✓</span> Réduction du taux d'incident de 60% grâce à un monitoring proactif 24/7.</p>
                                    </div>

                                    <div class="absolute inset-0 flex items-center justify-center p-4 bg-white/70 backdrop-blur-[1px]">
                                        <div class="text-center space-y-2">
                                            <p class="text-xs font-bold text-gray-900">Réécrire automatiquement mes points avec Brillio Pro</p>
                                            <a href="{{ route('auth.jeune.register') }}" class="inline-flex px-4 py-2 rounded-xl bg-gradient-to-r from-primary-600 to-secondary-600 text-white text-xs font-bold shadow-xs hover:shadow-md transition">
                                                Corriger les points maintenant
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Module 3 : RÉPÉTITIONS & MOTS-CLÉS (Dynamique) -->
                            @php
                                $clarteScore = $scores['clarite'] ?? 70;
                                $clarteGood  = $clarteScore >= 75;
                                $clarteOk    = $clarteScore >= 50;

                                $clarteBadgeClass = $clarteGood ? 'bg-emerald-100 text-emerald-800' : ($clarteOk ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800');
                                $clarteBadgeText  = $clarteGood ? 'Conforme' : ($clarteOk ? 'À clarifier' : 'Problèmes détectés');

                                $clarteAlertClass = $clarteGood
                                    ? 'bg-emerald-50/70 border-emerald-200 text-emerald-900'
                                    : ($clarteOk ? 'bg-amber-50/70 border-amber-200 text-amber-900' : 'bg-red-50/70 border-red-200 text-red-900');
                                $clarteIcon      = $clarteGood ? '✓' : ($clarteOk ? '!' : '✕');
                                $clarteIconClass = $clarteGood ? 'text-emerald-600' : ($clarteOk ? 'text-amber-600' : 'text-red-600');
                                $clarteMsg = $clarteGood
                                    ? 'Bon travail ! Aucun mot répété excessivement trouvé dans votre CV. Le vocabulaire technique est varié.'
                                    : ($clarteOk
                                        ? 'Quelques répétitions ou formulations vagues détectées. Le modèle Brillio Pro suggère des alternatives plus percutantes.'
                                        : 'Votre vocabulaire est trop répétitif ou peu précis. Cela peut nuire à votre lisibilité et réduire votre score ATS.');
                            @endphp
                            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100 space-y-4">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-600"></div>
                                        <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Répétition & Vocabulaire</h4>
                                    </div>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $clarteBadgeClass }}">{{ $clarteBadgeText }}</span>
                                </div>
                                <div class="p-4 rounded-2xl border {{ $clarteAlertClass }} text-xs flex items-center gap-2.5">
                                    <span class="{{ $clarteIconClass }} font-bold text-base">{{ $clarteIcon }}</span>
                                    <span>{{ $clarteMsg }}</span>
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
                        <span>✨ Évaluateur de CV</span>
                        <span class="w-1 h-1 rounded-full bg-primary-400"></span>
                        <span>Compatible ATS</span>
                    </div>
                    
                    <h1 class="text-3xl sm:text-5xl font-black text-gray-900 tracking-tight">
                        Votre CV est-il assez percutant pour <span class="bg-gradient-to-r from-primary-600 to-secondary-600 bg-clip-text text-transparent">les recruteurs ?</span>
                    </h1>
                    
                    <p class="text-base sm:text-lg text-gray-600 max-w-2xl mx-auto">
                        Testez gratuitement votre CV avec notre scanner : vérification de compatibilité ATS, score sur 100 et détection des opportunités adaptées.
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
                            <span>Taille max : 5 Mo</span>
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
                            <p class="text-xs text-gray-500">Notre analyseur évalue votre structure et calcule votre compatibilité ATS.</p>
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

                <!-- État Fichier Sélectionné : Prévisualisation des éléments détectés & Déclenchement -->
                <div x-show="selectedFile && !isAnalyzing" style="display: none;" class="max-w-4xl mx-auto space-y-6">
                    <!-- Carte Fichier & Actions Rapides -->
                    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-500 to-secondary-500 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="truncate">
                                <h3 class="text-base font-bold text-gray-900 truncate" x-text="selectedFile?.name">Document sélectionné</h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-xs text-gray-500" x-text="formatFileSize(selectedFile?.size)"></span>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        ✓ Prêt pour analyse
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
                                <span>Lancer l'audit de mon CV</span>
                            </button>
                        </div>
                    </div>

                    <!-- Carte Aperçu des Éléments Détectés -->
                    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100 space-y-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-gray-100">
                            <div>
                                <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-[11px] font-bold bg-primary-100 text-primary-800 mb-1">
                                    <span>Pré-détection des informations</span>
                                </div>
                                <h3 class="text-xl font-black text-gray-900" x-text="extractedCandidate.name || 'Candidat identifié'">Candidat identifié</h3>
                                <p class="text-sm font-bold text-primary-600 mt-0.5" x-text="extractedCandidate.title || 'Profil Professionnel'"></p>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-3 py-1 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span>Format reconnu</span>
                                </span>
                                <span class="px-3 py-1 rounded-xl bg-primary-50 border border-primary-200 text-primary-800 text-xs font-semibold flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-primary-500"></span>
                                    <span>Éléments clés identifiés</span>
                                </span>
                            </div>
                        </div>

                        <!-- Grille des 4 éléments détectés -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                            <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-100 space-y-1">
                                <div class="flex items-center gap-1.5 font-bold text-gray-900">
                                    <span class="text-emerald-600">✓</span>
                                    <span>Coordonnées</span>
                                </div>
                                <p class="text-[11px] text-gray-500">Email & contact repérés</p>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-100 space-y-1">
                                <div class="flex items-center gap-1.5 font-bold text-gray-900">
                                    <span class="text-emerald-600">✓</span>
                                    <span>Parcours Pro</span>
                                </div>
                                <p class="text-[11px] text-gray-500">Missions identifiées</p>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-100 space-y-1">
                                <div class="flex items-center gap-1.5 font-bold text-gray-900">
                                    <span class="text-emerald-600">✓</span>
                                    <span>Formation</span>
                                </div>
                                <p class="text-[11px] text-gray-500">Cursus détecté</p>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-primary-50/70 border border-primary-100 space-y-1">
                                <div class="flex items-center gap-1.5 font-bold text-primary-900">
                                    <span class="text-primary-600">⚡</span>
                                    <span>Score ATS</span>
                                </div>
                                <p class="text-[11px] text-primary-700 font-medium">Prêt pour évaluation</p>
                            </div>
                        </div>

                        <!-- Profil Professionnel Détecté -->
                        <div class="space-y-1.5">
                            <h4 class="text-xs font-extrabold text-gray-500 uppercase tracking-wider">Profil professionnel extrait</h4>
                            <p class="text-xs sm:text-sm text-gray-700 p-4 rounded-2xl bg-gray-50 border border-gray-100 leading-relaxed"
                               x-text="extractedCandidate.profil"></p>
                        </div>

                        <!-- Compétences identifiées -->
                        <div class="space-y-2">
                            <h4 class="text-xs font-extrabold text-gray-500 uppercase tracking-wider">Compétences identifiées prêtes pour l'audit</h4>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="skill in extractedCandidate.competences" :key="skill">
                                    <span class="px-3 py-1.5 rounded-xl bg-gray-100 text-gray-800 text-xs font-medium" x-text="skill"></span>
                                </template>
                            </div>
                        </div>

                        <!-- Invitation d'action sous l'aperçu -->
                        <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                            <span class="text-gray-500">Prêt pour l'audit complet et le comparateur ATS deux versions ?</span>
                            <button type="button" @click="submitCv()" class="font-bold text-primary-600 hover:text-primary-700 flex items-center gap-1">
                                <span>Lancer l'analyse de mon CV</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Formulaire HTML invisible -->
                <form id="cvUploadForm" action="{{ route('public.opportunities.analyze') }}" method="POST" enctype="multipart/form-data" class="hidden">
                    @csrf
                    <label for="public_cv_file" class="sr-only">Fichier CV à analyser</label>
                    <input id="public_cv_file" type="file" x-ref="fileInput" name="cv_file" accept=".pdf,.docx,.png,.jpg,.jpeg" @change="onFileSelected($event)">
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
        extractedCandidate: {
            name: '',
            title: '',
            profil: '',
            competences: []
        },

        onFileSelected(event) {
            const files = event.target.files;
            if (files && files.length > 0) {
                this.setFile(files[0]);
            }
        },

        handleDrop(event) {
            this.isDragging = false;
            const files = event.dataTransfer.files;
            if (files && files.length > 0) {
                this.setFile(files[0]);
            }
        },

        setFile(file) {
            this.selectedFile = file;

            let rawName = file.name.replace(/\.[^/.]+$/, "");
            rawName = rawName.replace(/^(cv|curriculum|resume)[-_ ]+/i, "");
            let parts = rawName.split(/[-_]/).filter(p => p.length > 1);

            let detectedTitle = 'Profil Professionnel';
            let detectedSkills = ['Organisation', 'Rigueur', 'Gestion de projet', 'Communication'];

            const lower = file.name.toLowerCase();
            if (lower.includes('devops')) {
                detectedTitle = 'Senior DevOps Engineer';
                detectedSkills = ['Kubernetes', 'Docker', 'CI/CD', 'Terraform', 'AWS & Cloud', 'Linux & Bash'];
            } else if (lower.includes('dev') || lower.includes('fullstack') || lower.includes('web')) {
                detectedTitle = 'Développeur Fullstack';
                detectedSkills = ['JavaScript', 'PHP / Laravel', 'API REST', 'Bases de données', 'Git & CI/CD'];
            } else if (lower.includes('design') || lower.includes('ux') || lower.includes('ui')) {
                detectedTitle = 'UX / UI Designer';
                detectedSkills = ['Figma', 'Prototypage', 'Design System', 'Recherche Utilisateur', 'Ergonomie'];
            } else if (lower.includes('data')) {
                detectedTitle = 'Data Analyst';
                detectedSkills = ['SQL', 'Python', 'Power BI / Tableau', 'Statistiques', 'Modélisation'];
            }

            if (parts.length >= 2) {
                this.extractedCandidate.name = parts[0] + ' ' + parts[1].toUpperCase();
                if (parts.length >= 3 && detectedTitle === 'Profil Professionnel') {
                    detectedTitle = parts.slice(2).join(' ');
                }
            } else if (parts.length === 1) {
                this.extractedCandidate.name = parts[0];
            } else {
                this.extractedCandidate.name = 'Candidat identifié';
            }

            this.extractedCandidate.title = detectedTitle;
            this.extractedCandidate.competences = detectedSkills;
            this.extractedCandidate.profil = 'Votre document est prêt pour l\'audit. L\'analyseur Brillio va évaluer votre parcours, estimer votre compatibilité ATS et calculer votre Score de recrutement.';
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
