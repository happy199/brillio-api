@extends('layouts.public')

@php $forceScrolled = true; @endphp

@section('title', isset($isScoreView) && $isScoreView ? 'Mon score Career - Analyse détaillée' : 'Opportunités & Mon CV - Analyse IA')

@section('content')
<div class="min-h-screen bg-gray-50/50 pt-28 pb-20" x-data="cvUploaderApp">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumb -->
        <nav class="flex items-center text-sm font-medium text-gray-500 mb-6 space-x-2">
            <a href="{{ route('home') }}" class="hover:text-primary-600 transition">Accueil</a>
            <span>/</span>
            <a href="{{ route('public.opportunities') }}" class="hover:text-primary-600 transition">Opportunités</a>
            <span>/</span>
            <span class="text-gray-900 font-semibold">{{ isset($isScoreView) && $isScoreView ? 'Mon score Career' : 'Mon CV' }}</span>
        </nav>

        @if(isset($isScoreView) && $isScoreView)
            <!-- ========================================================================= -->
            <!-- VUE 3 : RÉSULTAT « MON SCORE CAREER » (Image 3)                           -->
            <!-- ========================================================================= -->
            <div>
                <!-- Header du score -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Mon score Career</h1>
                        <p class="text-base sm:text-lg text-gray-600 mt-1">Analyse détaillée de votre profil et estimation du potentiel recruteur.</p>
                    </div>
                    <div class="self-start sm:self-auto">
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-100 text-emerald-800 font-bold text-sm">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Score global {{ $cvAnalysis->global_score ?? 66 }}/100
                        </span>
                    </div>
                </div>

                <!-- Carte principale avec la jauge circulaire (Image 3) -->
                <div class="bg-white rounded-3xl p-6 sm:p-10 shadow-sm border border-gray-100 mb-8">
                    <div class="flex flex-col md:flex-row items-center gap-8 md:gap-12">
                        
                        <!-- Jauge circulaire SVG -->
                        <div class="relative w-44 h-44 flex-shrink-0 flex items-center justify-center">
                            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 120 120">
                                <circle cx="60" cy="60" r="50" fill="none" stroke="#E5E7EB" stroke-width="10" />
                                <circle cx="60" cy="60" r="50" fill="none" stroke="#10B981" stroke-width="10"
                                        stroke-linecap="round"
                                        stroke-dasharray="314.159"
                                        stroke-dashoffset="{{ 314.159 - (314.159 * ($cvAnalysis->global_score ?? 66) / 100) }}" />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                                <span class="text-4xl font-extrabold text-gray-900">{{ $cvAnalysis->global_score ?? 66 }}</span>
                                <span class="text-xs font-semibold text-gray-400 -mt-1">/ 100</span>
                            </div>
                        </div>

                        <!-- Informations descriptives -->
                        <div class="flex-1 text-center md:text-left space-y-3">
                            <div class="flex items-center justify-center md:justify-start gap-3 flex-wrap">
                                <h2 class="text-xl font-bold text-gray-900">Score Omnhi RH</h2>
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                    {{ $cvAnalysis->status_label ?? 'Très bien' }}
                                </span>
                            </div>
                            <p class="text-lg font-bold text-gray-800 uppercase tracking-wide">{{ $cvAnalysis->candidate_name ?? 'Candidat Brillio' }}</p>
                            <p class="text-sm text-gray-600 max-w-2xl leading-relaxed">
                                Score estimé à partir de votre CV : structure, clarté, pertinence des expériences, adéquation des compétences avec le marché de l'emploi et lisibilité pour les recruteurs.
                            </p>

                            <!-- Indicateur de verrouillage pour les invités -->
                            <div class="pt-2 flex items-center justify-center md:justify-start gap-2 text-xs font-semibold text-gray-500">
                                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <span>Détail verrouillé en mode invité</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bannière d'appel à l'action menthe pour débloquer (Image 3) -->
                <div class="bg-gradient-to-br from-emerald-50 via-teal-50 to-emerald-100/60 border-2 border-emerald-200/80 rounded-3xl p-8 sm:p-10 shadow-sm relative overflow-hidden">
                    <div class="max-w-3xl space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shadow-md">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </div>

                        <h3 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Débloquez votre analyse complète</h3>
                        
                        <p class="text-sm sm:text-base text-gray-700 leading-relaxed">
                            Créez votre compte en 30 secondes pour voir le détail de votre score, les critères d'évaluation (structure, clarté, pertinence), vos <strong>points forts</strong>, vos <strong>axes d'amélioration</strong> et obtenir des <strong>recommandations personnalisées</strong> pour décrocher des opportunités.
                        </p>

                        <div class="pt-4 flex flex-col sm:flex-row items-center gap-4">
                            <a href="{{ route('auth.jeune.register') }}"
                               class="w-full sm:w-auto px-8 py-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white font-bold text-base shadow-lg hover:shadow-xl hover:scale-[1.02] transition-all text-center flex items-center justify-center gap-2">
                                <span>Créer mon compte pour voir le détail</span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>

                            <a href="{{ route('public.opportunities') }}" class="w-full sm:w-auto px-6 py-4 rounded-xl bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 font-semibold text-sm transition text-center">
                                Tester un autre CV
                            </a>
                        </div>

                        <p class="text-xs text-emerald-800/80 pt-2 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Votre CV et votre note seront automatiquement conservés dans votre nouvel espace Jeune.</span>
                        </p>
                    </div>
                </div>
            </div>

        @else
            <!-- ========================================================================= -->
            <!-- VUE 1 & 2 : IMPORT & APERÇU DU CV (Images 1 & 2)                          -->
            <!-- ========================================================================= -->
            <div>
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="px-3 py-1 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-full uppercase tracking-wider">Évaluation IA Gratuite</span>
                        <span class="px-3 py-1 bg-primary-100 text-primary-800 text-xs font-bold rounded-full uppercase tracking-wider">Opportunités de Carrière</span>
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Mon CV</h1>
                    <p class="text-base sm:text-lg text-gray-600 mt-1">Importez et gérez votre curriculum vitae pour découvrir votre score et vos opportunités.</p>
                </div>

                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700">
                        <div class="flex items-center gap-2 font-semibold">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Veuillez vérifier les points suivants :</span>
                        </div>
                        <ul class="list-disc list-inside mt-2 text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Grille principale 2 colonnes -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    
                    <!-- Colonne Gauche : Formulaire & Téléversement (5 cols) -->
                    <div class="lg:col-span-5 space-y-6">
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                            <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center justify-between">
                                <span>Importer un CV</span>
                                <span class="text-xs font-normal text-gray-400">Étape 1 sur 2</span>
                            </h2>

                            <!-- Zone de Drop / Sélection quand aucun fichier sélectionné -->
                            <div x-show="!selectedFile"
                                 @dragover.prevent="isDragging = true"
                                 @dragleave.prevent="isDragging = false"
                                 @drop.prevent="handleDrop($event)"
                                 :class="isDragging ? 'border-primary-500 bg-primary-50/50' : 'border-gray-200 hover:border-primary-400 bg-gray-50/60'"
                                 class="border-2 border-dashed rounded-2xl p-8 text-center transition-all cursor-pointer group"
                                 @click="$refs.fileInput.click()">
                                
                                <div class="w-16 h-16 mx-auto mb-4 bg-primary-100 rounded-full flex items-center justify-center text-primary-600 group-hover:scale-110 transition-transform">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                </div>

                                <p class="text-base font-bold text-gray-800">Glissez-déposez votre CV</p>
                                <p class="text-sm text-gray-500 mt-1">ou <span class="text-primary-600 font-semibold group-hover:underline">cliquez pour parcourir</span></p>

                                <div class="mt-4 flex items-center justify-center gap-1.5 flex-wrap">
                                    <span class="px-2 py-0.5 bg-white border border-gray-200 text-gray-600 text-xs font-medium rounded-md shadow-2xs">PDF</span>
                                    <span class="px-2 py-0.5 bg-white border border-gray-200 text-gray-600 text-xs font-medium rounded-md shadow-2xs">DOCX</span>
                                    <span class="px-2 py-0.5 bg-white border border-gray-200 text-gray-600 text-xs font-medium rounded-md shadow-2xs">JPG</span>
                                    <span class="px-2 py-0.5 bg-white border border-gray-200 text-gray-600 text-xs font-medium rounded-md shadow-2xs">PNG</span>
                                </div>

                                <p class="text-xs text-gray-400 mt-3">Taille maximale : 10 Mo</p>
                            </div>

                            <!-- Fichier sélectionné (État 2 conforme à l'image 2) -->
                            <div x-show="selectedFile" style="display: none;" class="space-y-4">
                                <div class="p-4 rounded-xl border border-gray-200 bg-gray-50 flex items-center justify-between">
                                    <div class="flex items-center gap-3 overflow-hidden">
                                        <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <div class="truncate">
                                            <p class="text-sm font-semibold text-gray-900 truncate" x-text="selectedFile?.name"></p>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-xs text-gray-500" x-text="formatFileSize(selectedFile?.size)"></span>
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Prêt
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" @click="removeFile()" class="w-8 h-8 rounded-full bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center transition flex-shrink-0" title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Bouton principal d'action : Analyser mon CV -->
                                <button type="button"
                                        @click="submitCv()"
                                        :disabled="isAnalyzing"
                                        class="w-full py-3.5 px-6 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold text-base shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-75">
                                    <template x-if="!isAnalyzing">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                            </svg>
                                            <span>Analyser mon CV</span>
                                        </div>
                                    </template>
                                    <template x-if="isAnalyzing">
                                        <div class="flex items-center gap-2">
                                            <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span>Analyse IA en cours...</span>
                                        </div>
                                    </template>
                                </button>

                                <div class="text-center pt-1">
                                    <button type="button" @click="$refs.fileInput.click()" class="text-xs font-semibold text-primary-600 hover:text-primary-700 flex items-center justify-center gap-1 mx-auto hover:underline">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        Remplacer le CV
                                    </button>
                                </div>
                            </div>

                            <!-- Input caché pour la sélection -->
                            <form id="cvUploadForm" action="{{ route('public.opportunities.analyze') }}" method="POST" enctype="multipart/form-data" class="hidden">
                                @csrf
                                <input type="file" x-ref="fileInput" name="cv_file" accept=".pdf,.docx,.png,.jpg,.jpeg" @change="onFileSelected($event)">
                            </form>
                        </div>

                        <!-- Conseils pour un bon CV (Conforme maquettes) -->
                        <div class="bg-blue-50/70 border border-blue-100 rounded-2xl p-5">
                            <div class="flex items-center gap-2.5 text-blue-900 font-bold text-sm mb-3">
                                <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                    </svg>
                                </div>
                                <span>Conseils pour un bon CV</span>
                            </div>
                            <ul class="space-y-2.5 text-xs text-blue-800">
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-500 font-bold">•</span>
                                    <span>Assurez-vous que votre CV contient vos <strong>coordonnées à jour</strong> (téléphone, email, ville).</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-500 font-bold">•</span>
                                    <span>Détaillez vos <strong>expériences les plus récentes</strong> en valorisant des missions concrètes.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-500 font-bold">•</span>
                                    <span>Mentionnez vos <strong>compétences clés</strong>, outils informatiques et langues parlées.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-500 font-bold">•</span>
                                    <span>Privilégiez un document natif lisible (PDF ou Word) pour une analyse IA optimale.</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Colonne Droite : Aperçu du CV (7 cols) -->
                    <div class="lg:col-span-7">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 min-h-[500px] flex flex-col">
                            <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
                                <h2 class="text-lg font-bold text-gray-900">Aperçu</h2>
                                <span class="text-xs text-gray-400" x-text="selectedFile ? 'Fichier chargé' : 'En attente'">En attente</span>
                            </div>

                            <!-- État vide : Aucun CV importé (Image 1) -->
                            <div x-show="!selectedFile" class="flex-1 flex flex-col items-center justify-center p-8 text-center my-auto">
                                <div class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center text-gray-400 mb-4">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-base font-bold text-gray-700">Aucun CV importé</h3>
                                <p class="text-sm text-gray-400 max-w-sm mt-1">L'aperçu de votre document et la structure extraite s'afficheront ici après importation.</p>
                            </div>

                            <!-- État avec fichier : Aperçu structuré (Image 2) -->
                            <div x-show="selectedFile" style="display: none;" class="flex-1 space-y-6">
                                <!-- En-tête candidat extrait -->
                                <div class="bg-gray-50 rounded-xl p-5 border border-gray-200/80">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                        <div>
                                            <h3 class="text-xl font-bold text-gray-900" x-text="extractedCandidate.name || 'Candidat Brillio'"></h3>
                                            <p class="text-sm font-medium text-emerald-600" x-text="extractedCandidate.title || 'Profil Professionnel'"></p>
                                        </div>
                                        <div class="text-xs text-gray-500 bg-white px-3 py-1.5 rounded-lg border border-gray-200 self-start">
                                            <span x-text="selectedFile?.name"></span>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex items-center gap-4 flex-wrap text-xs text-gray-600">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                            <span x-text="extractedCandidate.contact?.phone || 'Téléphone détecté'"></span>
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                            <span x-text="extractedCandidate.contact?.email || 'Email détecté'"></span>
                                        </span>
                                    </div>
                                </div>

                                <!-- Bloc Profil -->
                                <div>
                                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Profil professionnel</h4>
                                    <p class="text-sm text-gray-700 bg-gray-50/70 p-3.5 rounded-lg border border-gray-100" x-text="extractedCandidate.profil || 'Profil extrait du document. Cliquez sur « Analyser mon CV » pour obtenir le diagnostic complet de l\'IA.'"></p>
                                </div>

                                <!-- Bloc Compétences -->
                                <div>
                                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Compétences identifiées</h4>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="skill in extractedCandidate.competences" :key="skill">
                                            <span class="px-2.5 py-1 bg-gray-100 text-gray-800 text-xs font-medium rounded-md" x-text="skill"></span>
                                        </template>
                                    </div>
                                </div>

                                <!-- Invitation d'action sous l'aperçu -->
                                <div class="mt-auto pt-6 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                                    <span>Prêt pour l'évaluation ?</span>
                                    <button type="button" @click="submitCv()" class="text-emerald-600 font-bold hover:underline flex items-center gap-1">
                                        Lancer l'analyse IA
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
            competences: ['Compétences analytiques', 'Rigueur', 'Gestion de projet', 'Communication']
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
                    this.extractedCandidate.title = 'Candidat & Talent';
                }
            } else if (parts.length === 1) {
                this.extractedCandidate.name = parts[0];
                this.extractedCandidate.title = 'Candidat & Talent';
            } else {
                this.extractedCandidate.name = 'Candidat Brillio';
                this.extractedCandidate.title = 'Profil Professionnel';
            }

            this.extractedCandidate.profil = 'Document prêt pour l\'analyse IA. L\'intelligence artificielle de Brillio va évaluer votre structure, votre score Omnhi RH et détecter vos opportunités.';
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
