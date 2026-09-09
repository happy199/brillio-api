@extends('layouts.jeune')

@section('title', 'Outils - CV, Ressources & Documents')

@section('content')
<div class="space-y-8" x-data="outilsApp('{{ $tab ?? 'cv' }}')">

    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Outils</h1>
            <p class="text-sm sm:text-base text-gray-500 mt-0.5">Évaluez votre CV avec l'IA, explorez les ressources pédagogiques et gérez votre Drive documentaire.</p>
        </div>

        <!-- Action contextuelle selon l'onglet -->
        <div>
            <template x-if="currentTab === 'cv'">
                <button @click="showCvUploadModal = true"
                    class="px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition flex items-center gap-2 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                    <span>Analyser un nouveau CV</span>
                </button>
            </template>
            <template x-if="currentTab === 'drive'">
                <button @click="showUploadModal = true"
                    class="px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition flex items-center gap-2 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Ajouter un document</span>
                </button>
            </template>
        </div>
    </div>

    <!-- Alertes & Messages flash -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-primary-50 border border-primary-200 text-primary-900 flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-primary-100 flex items-center justify-center flex-shrink-0 text-primary-600">
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

    <!-- Barre des 3 Sous-onglets Outils : 1. CV (premier), 2. Ressources, 3. Documents -->
    <div class="border-b border-gray-200">
        <nav class="flex space-x-2 sm:space-x-8 overflow-x-auto pb-1" aria-label="Tabs">
            <!-- 1. CV (En premier, conforme demande) -->
            <button type="button"
                    @click="setTab('cv')"
                    :class="currentTab === 'cv' ? 'border-primary-600 text-primary-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
                    class="whitespace-nowrap py-3 px-3 sm:px-1 border-b-2 text-sm sm:text-base flex items-center gap-2 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>CV</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary-100 text-primary-800">
                    @if(isset($cvAnalyses) && $cvAnalyses->count() > 0)
                        {{ $cvAnalyses->first()->global_score }}/100
                    @else
                        Score IA
                    @endif
                </span>
            </button>

            <!-- 2. Ressources (Anciennement dans Mentorat, désormais dans Outils) -->
            <a href="{{ route('jeune.resources.index') }}"
               class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium whitespace-nowrap py-3 px-3 sm:px-1 border-b-2 text-sm sm:text-base flex items-center gap-2 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span>Ressources</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700">Pédagogie</span>
            </a>

            <!-- 3. Documents (Drive personnel) -->
            <button type="button"
                    @click="setTab('drive')"
                    :class="currentTab === 'drive' ? 'border-primary-600 text-primary-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
                    class="whitespace-nowrap py-3 px-3 sm:px-1 border-b-2 text-sm sm:text-base flex items-center gap-2 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z" />
                </svg>
                <span>Documents</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700">{{ $documents->count() }}</span>
            </button>
        </nav>
    </div>

    <!-- ========================================================================= -->
    <!-- CONTENU ONGLET 1 : CV (IA, JAUGE, SCORE & RAPPORT COMPLET)               -->
    <!-- ========================================================================= -->
    <div x-show="currentTab === 'cv'" x-cloak class="space-y-8">
        @if(!$activeCv)
            <!-- État vide : Aucun CV encore analysé -->
            <div class="bg-white rounded-3xl p-8 sm:p-12 border border-gray-100 shadow-sm text-center max-w-2xl mx-auto space-y-6">
                <div class="w-20 h-20 bg-primary-50 text-primary-600 rounded-3xl flex items-center justify-center mx-auto">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div class="space-y-2">
                    <h2 class="text-2xl font-bold text-gray-900">Vous n'avez pas encore évalué votre CV</h2>
                    <p class="text-sm text-gray-500 max-w-md mx-auto">Téléversez votre CV au format PDF, DOCX ou image pour obtenir immédiatement votre Score Career, un diagnostic ATS complet et votre CV reformaté prêt pour l'emploi.</p>
                </div>
                <button @click="showCvUploadModal = true"
                    class="px-6 py-3.5 bg-primary-600 text-white font-bold rounded-xl hover:bg-primary-700 transition shadow-sm inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span>Lancer l'évaluation IA de mon CV</span>
                </button>
            </div>
        @else
            <!-- Dashboard CV Débloqué -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm">
                <!-- En-tête CV actif & Sélecteur d'historique -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-100 mb-8">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Diagnostic Débloqué</span>
                            <span class="text-xs text-gray-400">• Évalué le {{ $activeCv->created_at->format('d/m/Y à H:i') }}</span>
                        </div>
                        <h2 class="text-2xl font-extrabold text-gray-900 mt-1">{{ $activeCv->candidate_name }}</h2>
                        <p class="text-sm text-primary-600 font-semibold">{{ $activeCv->candidate_title }}</p>
                    </div>

                    <div class="flex items-center gap-3">
                        @if($cvAnalyses->count() > 1)
                            <div class="relative" x-data="{ historyOpen: false }">
                                <button @click="historyOpen = !historyOpen" class="px-4 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Historique ({{ $cvAnalyses->count() }})</span>
                                </button>
                                <div x-show="historyOpen" @click.away="historyOpen = false" class="absolute right-0 mt-2 w-72 bg-white rounded-2xl shadow-xl border border-gray-100 p-2 z-20 space-y-1">
                                    @foreach($cvAnalyses as $cvItem)
                                        <a href="{{ route('jeune.outils', ['tab' => 'cv', 'cv_id' => $cvItem->id]) }}"
                                           class="flex items-center justify-between p-2.5 rounded-xl hover:bg-gray-50 transition text-xs {{ $cvItem->id === $activeCv->id ? 'bg-primary-50 text-primary-700 font-bold' : 'text-gray-700' }}">
                                            <div>
                                                <p class="truncate font-medium">{{ $cvItem->original_filename }}</p>
                                                <p class="text-gray-400 text-[10px]">{{ $cvItem->created_at->format('d/m/Y H:i') }}</p>
                                            </div>
                                            <span class="px-2 py-0.5 rounded-md text-xs font-bold {{ $cvItem->global_score >= 70 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">{{ $cvItem->global_score }}/100</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <button @click="showCvUploadModal = true" class="px-4 py-2 rounded-xl bg-primary-600 text-white text-sm font-semibold hover:bg-primary-700 transition flex items-center gap-2 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Réévaluer un CV</span>
                        </button>
                    </div>
                </div>

                <!-- Jauge & Synthèse globale avec animation au chargement -->
                <div class="flex flex-col md:flex-row items-center gap-8 md:gap-12 mb-10">
                    <div class="relative w-40 h-40 flex-shrink-0 flex items-center justify-center"
                         x-data="{
                             animatedScore: 0,
                             targetScore: {{ $activeCv->global_score }},
                             dashoffset: 314.159,
                             targetDashoffset: {{ 314.159 - (314.159 * $activeCv->global_score / 100) }}
                         }"
                         x-init="
                             setTimeout(() => {
                                 dashoffset = targetDashoffset;
                                 let duration = 1800;
                                 let startTime = null;
                                 function step(timestamp) {
                                     if (!startTime) startTime = timestamp;
                                     let progress = Math.min((timestamp - startTime) / duration, 1);
                                     animatedScore = Math.floor(progress * targetScore);
                                     if (progress < 1) {
                                         window.requestAnimationFrame(step);
                                     } else {
                                         animatedScore = targetScore;
                                     }
                                 }
                                 window.requestAnimationFrame(step);
                             }, 250);
                         ">
                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 120 120">
                            <circle cx="60" cy="60" r="50" fill="none" stroke="#E5E7EB" stroke-width="10" />
                            <circle cx="60" cy="60" r="50" fill="none" stroke="#6366f1" stroke-width="10"
                                    stroke-linecap="round"
                                    stroke-dasharray="314.159"
                                    :stroke-dashoffset="dashoffset"
                                    class="transition-all duration-[1800ms] ease-out" />
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                            <span class="text-4xl font-extrabold text-gray-900" x-text="animatedScore">0</span>
                            <span class="text-xs font-semibold text-gray-400 -mt-1">/ 100</span>
                        </div>
                    </div>

                    <div class="flex-1 space-y-3 text-center md:text-left">
                        <div class="flex items-center justify-center md:justify-start gap-3 flex-wrap">
                            <h3 class="text-xl font-bold text-gray-900">Score Career : {{ $activeCv->status_label }}</h3>
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-primary-100 text-primary-800">{{ $activeCv->global_score >= 70 ? 'Prêt pour le marché' : 'En progression' }}</span>
                        </div>
                        <p class="text-sm text-gray-700 leading-relaxed">{{ $activeCv->summary }}</p>
                    </div>
                </div>

                <!-- Critères Détaillés (Débloqués à 100%) -->
                <div class="space-y-4 mb-10">
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>Détail des 5 piliers d'évaluation</span>
                    </h3>

                    <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-4">
                        @php
                            $crit = $activeCv->criteria_scores ?? ['structure' => 65, 'clarite' => 70, 'experiences' => 60, 'competences' => 68, 'impact' => 62];
                        @endphp
                        @foreach($crit as $criterion => $val)
                            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100 space-y-2"
                                 x-data="{
                                     animatedVal: 0,
                                     targetVal: {{ $val }},
                                     barWidth: 0
                                 }"
                                 x-init="
                                     setTimeout(() => {
                                         barWidth = targetVal;
                                         let duration = 1500;
                                         let startTime = null;
                                         function step(timestamp) {
                                             if (!startTime) startTime = timestamp;
                                             let progress = Math.min((timestamp - startTime) / duration, 1);
                                             animatedVal = Math.floor(progress * targetVal);
                                             if (progress < 1) {
                                                 window.requestAnimationFrame(step);
                                             } else {
                                                 animatedVal = targetVal;
                                             }
                                         }
                                         window.requestAnimationFrame(step);
                                     }, 300);
                                 ">
                                <div class="flex items-center justify-between text-xs font-bold">
                                    <span class="text-gray-700 capitalize">{{ $criterion }}</span>
                                    <span class="text-primary-600"><span x-text="animatedVal">0</span>%</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-gray-200 overflow-hidden">
                                    <div class="h-full rounded-full bg-primary-600 transition-all duration-[1500ms] ease-out"
                                         :style="`width: ${barWidth}%`"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Points Forts, Axes d'Amélioration & Recommandations -->
                <div class="grid md:grid-cols-3 gap-6 mb-12">
                    <div class="p-6 rounded-2xl bg-emerald-50/60 border border-emerald-100 space-y-3">
                        <h4 class="font-bold text-emerald-900 text-sm flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Forces identifiées</span>
                        </h4>
                        <ul class="space-y-2 text-xs text-emerald-800">
                            @foreach($activeCv->strengths ?? [] as $st)
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-bold">•</span>
                                    <span>{{ $st }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="p-6 rounded-2xl bg-amber-50/60 border border-amber-100 space-y-3">
                        <h4 class="font-bold text-amber-900 text-sm flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Axes d'amélioration</span>
                        </h4>
                        <ul class="space-y-2 text-xs text-amber-800">
                            @foreach($activeCv->improvements ?? [] as $imp)
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-500 font-bold">•</span>
                                    <span>{{ $imp }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="p-6 rounded-2xl bg-blue-50/60 border border-blue-100 space-y-3">
                        <h4 class="font-bold text-blue-900 text-sm flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>Conseils de Recruteurs</span>
                        </h4>
                        <ul class="space-y-2 text-xs text-blue-800">
                            @foreach($activeCv->recommendations ?? [] as $rec)
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-500 font-bold">•</span>
                                    <span>{{ $rec }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- SECTION : CV OPTIMISÉ POUR L'EMPLOI (Format ATS Pro prêt à postuler) -->
                <div class="space-y-6 pt-6 border-t border-gray-100">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-primary-100 text-primary-800 uppercase tracking-wider">Version Pro Recommandée</span>
                            <h3 class="text-xl sm:text-2xl font-extrabold text-gray-900 mt-2">Votre CV Reformulé & Optimisé ATS</h3>
                            <p class="text-xs sm:text-sm text-gray-500">Cette version structure vos compétences avec des verbes d'action, standardise la typographie et maximise vos chances lors des screenings.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="printCvDocument()"
                                    class="px-4 py-2.5 rounded-xl bg-primary-600 text-white text-xs font-bold hover:bg-primary-700 transition flex items-center gap-2 shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                <span>Imprimer / PDF</span>
                            </button>
                            <a href="{{ route('jeune.opportunities', ['tab' => 'emploi']) }}"
                               class="px-4 py-2.5 rounded-xl bg-primary-600 text-white text-xs font-bold hover:bg-primary-700 transition flex items-center gap-1.5 shadow-sm">
                                <span>Postuler aux offres</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Fiche CV ATS Pro Rendue en HTML/A4 Prête à être imprimée -->
                    <div id="cvEnhancedPrintArea" class="bg-white rounded-3xl p-8 sm:p-12 border border-gray-200 shadow-md max-w-4xl mx-auto space-y-8 text-gray-900 font-sans">
                        <!-- En-tête CV -->
                        <div class="border-b-2 border-gray-900 pb-6 text-center space-y-2">
                            <h1 class="text-3xl font-black tracking-tight text-gray-900 uppercase">{{ $activeCv->candidate_name }}</h1>
                            <p class="text-base font-bold text-primary-700 tracking-wide">{{ $activeCv->candidate_title }}</p>
                            <div class="flex flex-wrap items-center justify-center gap-3 text-xs text-gray-600 pt-1">
                                @if(!empty($activeCv->candidate_contact['email']))
                                    <span>{{ $activeCv->candidate_contact['email'] }}</span>
                                @endif
                                @if(!empty($activeCv->candidate_contact['phone']))
                                    <span>• {{ $activeCv->candidate_contact['phone'] }}</span>
                                @endif
                                @if(!empty($activeCv->candidate_contact['location']))
                                    <span>• {{ $activeCv->candidate_contact['location'] }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Résumé professionnel -->
                        <div class="space-y-2">
                            <h2 class="text-xs font-black text-gray-900 uppercase tracking-widest border-b border-gray-200 pb-1">Profil Professionnel</h2>
                            <p class="text-xs leading-relaxed text-gray-700 text-justify">
                                {{ $activeCv->summary }}
                            </p>
                        </div>

                        <!-- Expériences professionnelles -->
                        @if(!empty($activeCv->parsed_content['experiences']))
                            <div class="space-y-4">
                                <h2 class="text-xs font-black text-gray-900 uppercase tracking-widest border-b border-gray-200 pb-1">Expériences Professionnelles</h2>
                                <div class="space-y-4">
                                    @foreach($activeCv->parsed_content['experiences'] as $exp)
                                        <div class="space-y-1">
                                            <div class="flex items-center justify-between text-xs">
                                                <h3 class="font-bold text-gray-900">{{ $exp['title'] ?? 'Poste occupé' }} — <span class="font-semibold text-gray-700">{{ $exp['company'] ?? 'Entreprise' }}</span></h3>
                                                <span class="text-gray-500 font-medium">{{ $exp['period'] ?? 'Récemment' }}</span>
                                            </div>
                                            @if(!empty($exp['description']))
                                                <p class="text-xs text-gray-600 leading-relaxed">{{ $exp['description'] }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Formations -->
                        @if(!empty($activeCv->parsed_content['education']))
                            <div class="space-y-4">
                                <h2 class="text-xs font-black text-gray-900 uppercase tracking-widest border-b border-gray-200 pb-1">Formations & Diplômes</h2>
                                <div class="space-y-3">
                                    @foreach($activeCv->parsed_content['education'] as $edu)
                                        <div class="flex items-center justify-between text-xs">
                                            <div>
                                                <h3 class="font-bold text-gray-900">{{ $edu['degree'] ?? 'Diplôme' }}</h3>
                                                <p class="text-gray-600">{{ $edu['school'] ?? 'Établissement' }}</p>
                                            </div>
                                            <span class="text-gray-500 font-medium">{{ $edu['year'] ?? '' }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Compétences clés -->
                        @if(!empty($activeCv->parsed_content['skills']))
                            <div class="space-y-2">
                                <h2 class="text-xs font-black text-gray-900 uppercase tracking-widest border-b border-gray-200 pb-1">Compétences Clés & Outils</h2>
                                <div class="flex flex-wrap gap-2 pt-1">
                                    @foreach($activeCv->parsed_content['skills'] as $skill)
                                        <span class="px-2.5 py-1 rounded-md bg-gray-100 text-gray-800 text-xs font-semibold">{{ $skill }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- ========================================================================= -->
    <!-- CONTENU ONGLET 3 : DOCUMENTS (DRIVE PERSONNEL)                            -->
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
            <button @click="driveFilter = 'certificat'"
                :class="driveFilter === 'certificat' ? 'bg-primary-500 text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200'"
                class="px-4 py-2 rounded-full text-sm font-medium transition">
                Certificats
            </button>
            <button @click="driveFilter = 'autre'"
                :class="driveFilter === 'autre' ? 'bg-primary-500 text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200'"
                class="px-4 py-2 rounded-full text-sm font-medium transition">
                Autres
            </button>
        </div>

        <!-- Documents List -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            @if($documents->isEmpty())
                <div class="text-center py-16">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Aucun document</h3>
                    <p class="text-gray-500 text-sm mb-4">Commencez par ajouter vos bulletins, diplômes ou certificats.</p>
                    <button @click="showUploadModal = true"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Ajouter un document</span>
                    </button>
                </div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($documents as $document)
                        <div x-show="driveFilter === 'all' || driveFilter === '{{ $document->document_type }}'"
                            class="p-4 sm:p-5 hover:bg-gray-50 transition flex items-center justify-between gap-4">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0
                                    @if($document->document_type === 'bulletin') bg-blue-50 text-blue-600
                                    @elseif($document->document_type === 'diplome') bg-green-50 text-green-600
                                    @elseif($document->document_type === 'certificat') bg-purple-50 text-purple-600
                                    @else bg-gray-50 text-gray-600 @endif">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h4 class="text-sm font-bold text-gray-900 truncate">{{ $document->title }}</h4>
                                        @if($document->is_verified)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                Vérifié
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-gray-500 mt-1 flex-wrap">
                                        <span>{{ $document->type_label }}</span>
                                        @if($document->academic_year)
                                            <span>• {{ $document->academic_year }}</span>
                                        @endif
                                        <span>• {{ $document->formatted_file_size }}</span>
                                        <span>• Ajouté le {{ $document->created_at->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <button @click="previewDocument({{ $document->id }}, '{{ $document->mime_type }}', '{{ addslashes($document->title) }}')"
                                    class="p-1.5 text-gray-500 hover:text-primary-600 rounded-lg hover:bg-gray-50 transition" title="Aperçu">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                                <a href="{{ route('jeune.documents.download', $document->id) }}" class="p-1.5 text-gray-500 hover:text-primary-600 rounded-lg hover:bg-gray-50 transition" title="Télécharger">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </a>
                                <button @click="deleteDocument({{ $document->id }})" class="p-1.5 text-gray-500 hover:text-red-600 rounded-lg hover:bg-gray-50 transition" title="Supprimer">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- MODAL : TÉLÉVERSER UN NOUVEAU CV IA -->
    <div x-show="showCvUploadModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showCvUploadModal = false">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs" @click="showCvUploadModal = false"></div>
            <div class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl z-10 space-y-6">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <h3 class="text-xl font-bold text-gray-900">Évaluer un CV avec l'IA</h3>
                    <button @click="showCvUploadModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <p class="text-xs text-gray-500 mb-6">Importez votre CV mis à jour pour comparer votre score, détecter les évolutions et obtenir de nouveaux conseils de recrutement.</p>

                <form action="{{ route('jeune.cv.analyze') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    <div>
                        <label for="cv_file" class="block text-sm font-semibold text-gray-700 mb-2">Fichier CV (PDF, DOCX, JPG ou PNG - max 5 Mo)</label>
                        <input id="cv_file" type="file" name="cv_file" required accept=".pdf,.docx,.png,.jpg,.jpeg" class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100" />
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="showCvUploadModal = false" class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-700 font-semibold text-sm hover:bg-gray-50">Annuler</button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-600 text-white font-bold text-sm hover:bg-primary-700 transition shadow-sm">Lancer l'analyse IA</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL : AJOUTER UN DOCUMENT DRIVE -->
    <div x-show="showUploadModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showUploadModal = false">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs" @click="showUploadModal = false"></div>
            <div class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl z-10 space-y-6">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <h3 class="text-xl font-bold text-gray-900">Ajouter un document au Drive</h3>
                    <button @click="showUploadModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form action="{{ route('jeune.documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label for="title" class="block text-sm font-semibold text-gray-700 mb-1">Titre du document</label>
                        <input id="title" type="text" name="title" required placeholder="Ex: Relevé de notes Semestre 2" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="document_type" class="block text-sm font-semibold text-gray-700 mb-1">Type de document</label>
                            <select id="document_type" name="document_type" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none">
                                <option value="bulletin">Bulletin</option>
                                <option value="diplome">Diplôme</option>
                                <option value="certificat">Certificat</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                        <div>
                            <label for="academic_year" class="block text-sm font-semibold text-gray-700 mb-1">Année académique</label>
                            <input id="academic_year" type="text" name="academic_year" placeholder="Ex: 2025-2026" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none" />
                        </div>
                    </div>

                    <div>
                        <label for="document" class="block text-sm font-semibold text-gray-700 mb-2">Fichier (PDF ou image max 10 Mo)</label>
                        <input id="document" type="file" name="document" required accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100" />
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="showUploadModal = false" class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-700 font-semibold text-sm hover:bg-gray-50">Annuler</button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-600 text-white font-bold text-sm hover:bg-primary-700 transition shadow-sm">Téléverser</button>
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
                <div class="w-full h-[70vh] bg-gray-50 rounded-2xl overflow-hidden flex items-center justify-center">
                    <template x-if="previewType.includes('pdf')">
                        <iframe :src="previewUrl" class="w-full h-full border-0"></iframe>
                    </template>
                    <template x-if="!previewType.includes('pdf')">
                        <img :src="previewUrl" class="max-w-full max-h-full object-contain" alt="Aperçu" />
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL : SUPPRESSION DOCUMENT DRIVE -->
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showDeleteModal = false">
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

@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
function outilsApp(initialTab) {
    return {
        currentTab: initialTab || 'cv',
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

function printCvDocument() {
    const printArea = document.getElementById('cvEnhancedPrintArea');
    if (!printArea) {
        window.print();
        return;
    }

    const iframe = document.createElement('iframe');
    iframe.style.position = 'fixed';
    iframe.style.right = '0';
    iframe.style.bottom = '0';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = '0';
    document.body.appendChild(iframe);

    const doc = iframe.contentWindow.document;
    doc.open();
    doc.write(`
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <title>CV_Optimise_Brillio</title>
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
            <script src="https://cdn.tailwindcss.com"><\/script>
            <style>
                @page {
                    size: A4 portrait;
                    margin: 10mm 12mm;
                }
                body {
                    font-family: 'Inter', sans-serif;
                    background: white !important;
                    color: #111827 !important;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }
                #cvContent {
                    background: white !important;
                }
            </style>
        </head>
        <body class="p-4 bg-white">
            <div id="cvContent">${printArea.innerHTML}</div>
        </body>
        </html>
    `);
    doc.close();

    setTimeout(() => {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
        setTimeout(() => {
            if (document.body.contains(iframe)) {
                document.body.removeChild(iframe);
            }
        }, 2000);
    }, 600);
}
</script>
@endpush

@push('styles')
<style>
@media print {
    body > * {
        display: none !important;
    }
    body {
        background: white !important;
    }
    #cvEnhancedPrintArea {
        display: block !important;
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 10mm !important;
        border: none !important;
        box-shadow: none !important;
    }
}
</style>
@endpush
@endsection
