@extends('layouts.public')

@php $forceScrolled = true; @endphp

@section('title', isset($isScoreView) && $isScoreView ? 'Mon score Career - Brillio' : 'Opportunités & Analyseur de CV - Brillio')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-primary-50/30 via-white to-gray-50/50 pt-28 pb-20" x-data="cvUploaderApp">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Fil d'Ariane stylisé -->
        <nav class="flex items-center text-xs sm:text-sm font-medium text-gray-500 mb-8 space-x-2">
            <a href="{{ route('home') }}" class="hover:text-primary-600 transition">Accueil</a>
            <span>/</span>
            <a href="{{ route('public.opportunities') }}" class="hover:text-primary-600 transition">Opportunités</a>
            <span>/</span>
            <span class="text-primary-700 font-semibold">{{ isset($isScoreView) && $isScoreView ? 'Score Career' : 'Évaluateur de CV' }}</span>
        </nav>

        @if(isset($isScoreView) && $isScoreView)
            <!-- ========================================================================= -->
            <!-- VUE RÉSULTAT : SCORE CAREER BRILLIO (Charte Violet / Rose / Orangé)       -->
            <!-- ========================================================================= -->
            <div class="space-y-10">
                
                <!-- En-tête Score -->
                <div class="text-center max-w-3xl mx-auto space-y-3">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold bg-gradient-to-r from-primary-500 via-secondary-500 to-accent-500 text-white shadow-sm">
                        <span>✨ Diagnostic IA Terminé</span>
                    </div>
                    <h1 class="text-3xl sm:text-5xl font-extrabold text-gray-900 tracking-tight">
                        Votre Score Career <span class="bg-gradient-to-r from-primary-600 via-secondary-600 to-accent-500 bg-clip-text text-transparent">Brillio</span>
                    </h1>
                    <p class="text-base sm:text-lg text-gray-600">
                        Estimation de l'impact de votre CV et de votre compétitivité sur le marché du travail.
                    </p>
                </div>

                <!-- Carte Centrale du Score (Design Sombre & Épuré avec Gradients Brillio) -->
                <div class="relative bg-gradient-to-br from-gray-900 via-indigo-950 to-primary-950 rounded-3xl p-8 sm:p-12 text-white shadow-2xl overflow-hidden border border-indigo-900/50">
                    <!-- Effets de lueur arrière-plan -->
                    <div class="absolute -right-20 -top-20 w-80 h-80 bg-secondary-500/20 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -left-20 -bottom-20 w-80 h-80 bg-accent-500/20 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-8 md:gap-12">
                        
                        <!-- Jauge SVG aux couleurs du Dégradé Brillio -->
                        <div class="relative w-48 h-48 sm:w-56 sm:h-56 flex-shrink-0 flex items-center justify-center">
                            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 120 120">
                                <defs>
                                    <linearGradient id="brillioScoreGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#6366f1" />
                                        <stop offset="50%" stop-color="#d946ef" />
                                        <stop offset="100%" stop-color="#f97316" />
                                    </linearGradient>
                                </defs>
                                <circle cx="60" cy="60" r="50" fill="none" stroke="rgba(255, 255, 255, 0.1)" stroke-width="10" />
                                <circle cx="60" cy="60" r="50" fill="none" stroke="url(#brillioScoreGradient)" stroke-width="10"
                                        stroke-linecap="round"
                                        stroke-dasharray="314.159"
                                        stroke-dashoffset="{{ 314.159 - (314.159 * ($cvAnalysis->global_score ?? 66) / 100) }}" />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                                <span class="text-5xl sm:text-6xl font-black tracking-tight text-white">{{ $cvAnalysis->global_score ?? 66 }}</span>
                                <span class="text-xs sm:text-sm font-semibold text-gray-300 -mt-1 tracking-widest uppercase">sur 100</span>
                            </div>
                        </div>

                        <!-- Analyse & Verrouillage Visiteur -->
                        <div class="flex-1 text-center md:text-left space-y-4">
                            <div class="flex items-center justify-center md:justify-start gap-3 flex-wrap">
                                <span class="px-3.5 py-1 rounded-full text-xs font-bold bg-white/10 backdrop-blur-md border border-white/20 text-white">
                                    Évaluation Globale
                                </span>
                                <span class="px-3.5 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-secondary-500 to-accent-500 text-white">
                                    {{ $cvAnalysis->status_label ?? 'Très prometteur' }}
                                </span>
                            </div>

                            <h2 class="text-2xl sm:text-3xl font-bold text-white tracking-wide uppercase">
                                {{ $cvAnalysis->candidate_name ?? 'Candidat Brillio' }}
                            </h2>

                            <p class="text-sm sm:text-base text-gray-300 leading-relaxed max-w-xl">
                                Ce score analyse la lisibilité, l'équilibre de la structure, la valorisation de vos compétences et votre adéquation avec les attentes actuelles des recruteurs.
                            </p>

                            <!-- Badge indicateur de verrouillage -->
                            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-amber-300 text-xs font-medium backdrop-blur-xs">
                                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <span>Rapport complet et recommandations verrouillés pour les visiteurs</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bannière de Déblocage (Gradient signature Brillio Violet / Rose / Orangé) -->
                <div class="relative bg-gradient-to-r from-primary-600 via-secondary-600 to-accent-500 rounded-3xl p-8 sm:p-12 text-white shadow-xl overflow-hidden">
                    <div class="max-w-3xl space-y-4 relative z-10">
                        <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white shadow-inner">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>

                        <h3 class="text-2xl sm:text-4xl font-extrabold tracking-tight">
                            Débloquez votre audit complet & vos opportunités
                        </h3>

                        <p class="text-sm sm:text-base text-white/90 leading-relaxed">
                            Créez votre compte en <strong>30 secondes</strong> pour accéder gratuitement au détail complet de votre évaluation : vos <strong>points forts</strong>, vos <strong>axes d'amélioration prioritaires</strong>, les <strong>conseils sur-mesure du Coach IA</strong> et les opportunités d'emploi qui vous correspondent.
                        </p>

                        <div class="pt-4 flex flex-col sm:flex-row items-center gap-4">
                            <a href="{{ route('auth.jeune.register') }}"
                               class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-white text-gray-900 font-extrabold text-base shadow-lg hover:shadow-2xl hover:bg-gray-50 hover:scale-[1.02] transition-all text-center flex items-center justify-center gap-2">
                                <span>Créer mon compte & débloquer mon audit</span>
                                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>

                            <a href="{{ route('public.opportunities') }}" class="w-full sm:w-auto px-6 py-4 rounded-2xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold text-sm transition text-center">
                                Tester un autre CV
                            </a>
                        </div>

                        <p class="text-xs text-white/80 pt-2 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Votre CV et ce score seront automatiquement transférés et synchronisés dans votre espace personnel.</span>
                        </p>
                    </div>
                </div>

            </div>

        @else
            <!-- ========================================================================= -->
            <!-- VUE IMPORT & AUDIT CV : DESIGN BRILLIO STUDIO ORIGINAL                     -->
            <!-- ========================================================================= -->
            <div class="space-y-10">
                
                <!-- Hero Header -->
                <div class="text-center max-w-3xl mx-auto space-y-3">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold bg-gradient-to-r from-primary-500 via-secondary-500 to-accent-500 text-white shadow-sm">
                        <span>🚀 Studio Carrière IA</span>
                        <span class="w-1 h-1 rounded-full bg-white"></span>
                        <span>100% Gratuit</span>
                    </div>
                    <h1 class="text-3xl sm:text-5xl font-black text-gray-900 tracking-tight">
                        Évaluez l'impact de votre CV avec <span class="bg-gradient-to-r from-primary-600 via-secondary-600 to-accent-500 bg-clip-text text-transparent">l'IA Brillio</span>
                    </h1>
                    <p class="text-base sm:text-lg text-gray-600 max-w-2xl mx-auto">
                        Importez votre CV pour obtenir une note sur 100, mesurer votre attrait auprès des recruteurs et identifier les opportunités de stages et d'emplois adaptées.
                    </p>
                </div>

                @if ($errors->any())
                    <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 max-w-2xl mx-auto">
                        <div class="flex items-center gap-2 font-semibold text-sm">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    </div>
                @endif

                <!-- Conteneur Central d'Upload (État Sans Fichier) -->
                <div x-show="!selectedFile" class="max-w-3xl mx-auto">
                    <div @dragover.prevent="isDragging = true"
                         @dragleave.prevent="isDragging = false"
                         @drop.prevent="handleDrop($event)"
                         :class="isDragging ? 'border-secondary-500 bg-secondary-50/30 scale-[1.01]' : 'border-gray-200 hover:border-primary-400 bg-white'"
                         class="relative rounded-3xl border-2 border-dashed p-8 sm:p-14 text-center shadow-xl shadow-primary-500/5 transition-all cursor-pointer group"
                         @click="$refs.fileInput.click()">
                        
                        <!-- Icône avec gradient Brillio -->
                        <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-gradient-to-br from-primary-100 via-secondary-100 to-accent-100 text-primary-600 flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-transform shadow-inner">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>

                        <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Glissez-déposez votre CV ici</h2>
                        <p class="text-sm text-gray-500 mt-2">ou cliquez n'importe où pour sélectionner votre document</p>

                        <!-- Bouton Parcourir stylisé -->
                        <div class="mt-6">
                            <span class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-primary-600 via-secondary-600 to-accent-500 text-white font-bold text-sm shadow-md group-hover:shadow-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Parcourir mes fichiers</span>
                            </span>
                        </div>

                        <!-- Formats & Limites -->
                        <div class="mt-8 pt-6 border-t border-gray-100 flex flex-wrap items-center justify-center gap-3 text-xs text-gray-500">
                            <span class="px-2.5 py-1 rounded-lg bg-gray-100 font-semibold text-gray-700">PDF</span>
                            <span class="px-2.5 py-1 rounded-lg bg-gray-100 font-semibold text-gray-700">DOCX</span>
                            <span class="px-2.5 py-1 rounded-lg bg-gray-100 font-semibold text-gray-700">PNG</span>
                            <span class="px-2.5 py-1 rounded-lg bg-gray-100 font-semibold text-gray-700">JPG</span>
                            <span>•</span>
                            <span>Taille max : 10 Mo</span>
                        </div>
                    </div>

                    <!-- 3 Piliers d'Évaluation Brillio sous l'upload -->
                    <div class="grid sm:grid-cols-3 gap-5 mt-8">
                        <div class="p-5 rounded-2xl bg-white border border-primary-100 shadow-2xs space-y-2">
                            <div class="w-8 h-8 rounded-xl bg-primary-100 text-primary-600 flex items-center justify-center font-bold text-sm">1</div>
                            <h3 class="font-bold text-sm text-gray-900">Structure & Clarté</h3>
                            <p class="text-xs text-gray-500 leading-relaxed">Vérification de la lisibilité, de la hiérarchie des rubriques et de l'équilibre visuel.</p>
                        </div>

                        <div class="p-5 rounded-2xl bg-white border border-secondary-100 shadow-2xs space-y-2">
                            <div class="w-8 h-8 rounded-xl bg-secondary-100 text-secondary-600 flex items-center justify-center font-bold text-sm">2</div>
                            <h3 class="font-bold text-sm text-gray-900">Pertinence Métier</h3>
                            <p class="text-xs text-gray-500 leading-relaxed">Détection des compétences clés et adéquation avec les offres d'emploi en Afrique.</p>
                        </div>

                        <div class="p-5 rounded-2xl bg-white border border-accent-100 shadow-2xs space-y-2">
                            <div class="w-8 h-8 rounded-xl bg-accent-100 text-accent-600 flex items-center justify-center font-bold text-sm">3</div>
                            <h3 class="font-bold text-sm text-gray-900">Recommandations IA</h3>
                            <p class="text-xs text-gray-500 leading-relaxed">Conseils d'amélioration concrets pour maximiser votre taux de retour recruteur.</p>
                        </div>
                    </div>
                </div>

                <!-- État Avec Fichier Sélectionné : Console Interactive Brillio -->
                <div x-show="selectedFile" style="display: none;" class="space-y-6">
                    
                    <!-- Barre de Contrôle Supérieure -->
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex flex-col md:flex-row items-center justify-between gap-6">
                        <div class="flex items-center gap-4 w-full md:w-auto">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-500 via-secondary-500 to-accent-500 text-white flex items-center justify-center flex-shrink-0 shadow-md">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="truncate">
                                <h3 class="text-base font-bold text-gray-900 truncate" x-text="selectedFile?.name"></h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-xs text-gray-500 font-medium" x-text="formatFileSize(selectedFile?.size)"></span>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary-100 text-primary-800">
                                        Prêt pour analyse
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Actions : Analyser & Remplacer -->
                        <div class="flex items-center gap-3 w-full md:w-auto justify-end">
                            <button type="button" @click="$refs.fileInput.click()" class="px-4 py-3 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-50 font-semibold text-xs transition">
                                Remplacer
                            </button>

                            <button type="button"
                                    @click="submitCv()"
                                    :disabled="isAnalyzing"
                                    class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-primary-600 via-secondary-600 to-accent-500 hover:from-primary-700 hover:via-secondary-700 hover:to-accent-600 text-white font-extrabold text-sm shadow-lg shadow-primary-500/25 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-75">
                                <template x-if="!isAnalyzing">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                        <span>Lancer l'audit IA</span>
                                    </div>
                                </template>
                                <template x-if="isAnalyzing">
                                    <div class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>Analyse IA en cours...</span>
                                    </div>
                                </template>
                            </button>
                        </div>
                    </div>

                    <!-- Fiche Profil Extrait (Carte d'identité professionnelle stylisée) -->
                    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 space-y-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-100">
                            <div>
                                <span class="text-xs font-bold text-primary-600 uppercase tracking-wider">Profil Détecté</span>
                                <h2 class="text-2xl font-black text-gray-900 mt-0.5" x-text="extractedCandidate.name || 'Candidat Brillio'"></h2>
                                <p class="text-sm font-semibold text-secondary-600" x-text="extractedCandidate.title || 'Talent & Professionnel'"></p>
                            </div>

                            <div class="flex items-center gap-3 text-xs text-gray-600 flex-wrap">
                                <span class="px-3 py-1.5 rounded-xl bg-gray-50 border border-gray-200 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    <span x-text="extractedCandidate.contact?.phone || 'Téléphone détecté'"></span>
                                </span>
                                <span class="px-3 py-1.5 rounded-xl bg-gray-50 border border-gray-200 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <span x-text="extractedCandidate.contact?.email || 'Email détecté'"></span>
                                </span>
                            </div>
                        </div>

                        <!-- Résumé & Pitch -->
                        <div class="space-y-2">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Synthèse initiale</h3>
                            <div class="p-4 rounded-2xl bg-gradient-to-r from-primary-50/50 to-secondary-50/50 border border-primary-100 text-sm text-gray-700 leading-relaxed">
                                <p x-text="extractedCandidate.profil"></p>
                            </div>
                        </div>

                        <!-- Compétences identifiées -->
                        <div class="space-y-3">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Compétences détectées pour le matching</h3>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="skill in extractedCandidate.competences" :key="skill">
                                    <span class="px-3 py-1.5 rounded-xl bg-primary-50 text-primary-700 border border-primary-200 text-xs font-semibold" x-text="skill"></span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulaire caché -->
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
        extractedCandidate: {
            name: '',
            title: '',
            profil: '',
            contact: { phone: '', email: '' },
            competences: ['Compétences analytiques', 'Rigueur opérationnelle', 'Gestion de projet', 'Communication']
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
            
            if (parts.length >= 2) {
                this.extractedCandidate.name = (parts[0] + ' ' + parts[1].toUpperCase());
                if (parts.length >= 3) {
                    this.extractedCandidate.title = parts.slice(2).join(' ');
                } else {
                    this.extractedCandidate.title = 'Talent & Professionnel';
                }
            } else if (parts.length === 1) {
                this.extractedCandidate.name = parts[0];
                this.extractedCandidate.title = 'Talent & Professionnel';
            } else {
                this.extractedCandidate.name = 'Candidat Brillio';
                this.extractedCandidate.title = 'Profil Professionnel';
            }

            this.extractedCandidate.profil = 'Votre document est prêt pour l\'audit IA. Le modèle Brillio va analyser votre parcours, estimer votre potentiel recruteur et calculer votre Score Career.';
        },

        removeFile() {
            this.selectedFile = null;
            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
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
                    alert(data.message || 'Une erreur est survenue lors de l\'analyse.');
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
