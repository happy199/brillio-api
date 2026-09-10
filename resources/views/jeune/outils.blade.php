@extends('layouts.jeune')

@section('title', 'Outils - CV, Ressources & Documents')

@section('content')
<div class="space-y-8" x-data="outilsApp('{{ $tab ?? 'cv' }}', {{ $activeCv ? $activeCv->id : 'null' }}, {{ json_encode($templateCosts ?? [0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5]) }})">

    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Outils</h1>
            <p class="text-sm sm:text-base text-gray-500 mt-0.5">Évaluez votre CV, explorez les ressources pédagogiques et gérez votre Drive documentaire.</p>
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
                        Score ATS
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
    <!-- CONTENU ONGLET 1 : CV (ATS, JAUGE, SCORE & RAPPORT COMPLET)              -->
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
                    <span>Évaluer mon CV</span>
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
                                <div x-show="historyOpen" @click.away="historyOpen = false" class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-gray-100 p-2 z-20 space-y-1">
                                    @foreach($cvAnalyses as $cvItem)
                                        @php
                                            $itemScore = $cvItem->global_score;
                                            $itemBadgeClass = $itemScore >= 75 ? 'bg-emerald-100 text-emerald-800' : ($itemScore >= 50 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800');
                                        @endphp
                                        <a href="{{ route('jeune.outils', ['tab' => 'cv', 'cv_id' => $cvItem->id]) }}"
                                           class="flex items-center justify-between gap-3 p-2.5 rounded-xl hover:bg-gray-50 transition text-xs {{ $cvItem->id === $activeCv->id ? 'bg-primary-50 text-primary-700 font-bold' : 'text-gray-700' }}">
                                            <div class="min-w-0 flex-1">
                                                <p class="truncate font-semibold">{{ $cvItem->original_filename }}</p>
                                                <p class="text-gray-400 text-[10px]">{{ $cvItem->created_at->format('d/m/Y H:i') }}</p>
                                            </div>
                                            <span class="flex-shrink-0 px-2.5 py-1 rounded-md text-xs font-bold whitespace-nowrap {{ $itemBadgeClass }}">{{ $itemScore }}/100</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                @php
                    $mainScore = $activeCv->global_score;
                    $gaugeColor = $mainScore >= 75 ? '#10b981' : ($mainScore >= 50 ? '#f59e0b' : '#ef4444');
                    $scoreBadgeClass = $mainScore >= 75 ? 'bg-emerald-100 text-emerald-800' : ($mainScore >= 50 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800');
                @endphp

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
                            <circle cx="60" cy="60" r="50" fill="none" stroke="{{ $gaugeColor }}" stroke-width="10"
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
                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $scoreBadgeClass }}">{{ $activeCv->global_score >= 70 ? 'Prêt pour le marché' : 'En progression' }}</span>
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
                                 @php
                                     $pColor = $val >= 75 ? 'bg-emerald-500' : ($val >= 50 ? 'bg-amber-500' : 'bg-red-500');
                                     $pTextColor = $val >= 75 ? 'text-emerald-600' : ($val >= 50 ? 'text-amber-600' : 'text-red-600');
                                 @endphp
                                <div class="flex items-center justify-between text-xs font-bold">
                                    <span class="text-gray-700 capitalize">{{ $criterion }}</span>
                                    <span class="{{ $pTextColor }}"><span x-text="animatedVal">0</span>%</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-gray-200 overflow-hidden">
                                    <div class="h-full rounded-full {{ $pColor }} transition-all duration-[1500ms] ease-out"
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

                <!-- SECTION : TOGGLE & AFFICHAGE (CV RESTRUCTURÉ ATS VS CV ORIGINAL) -->
                <div class="space-y-6 pt-6 border-t border-gray-100">
                    <!-- Switcher de vue : CV ATS vs CV Original -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <div class="inline-flex p-1 bg-gray-100 rounded-2xl border border-gray-200 mb-2">
                                <button type="button"
                                        @click="cvViewMode = 'ats'"
                                        :class="cvViewMode === 'ats' ? 'bg-white text-gray-900 shadow-xs font-bold' : 'text-gray-500 hover:text-gray-900 font-semibold'"
                                        class="px-4 py-2 rounded-xl text-xs sm:text-sm transition flex items-center gap-2">
                                    <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>CV Restructuré ATS</span>
                                </button>
                                <button type="button"
                                        @click="cvViewMode = 'original'"
                                        :class="cvViewMode === 'original' ? 'bg-white text-gray-900 shadow-xs font-bold' : 'text-gray-500 hover:text-gray-900 font-semibold'"
                                        class="px-4 py-2 rounded-xl text-xs sm:text-sm transition flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>CV Original Téléversé</span>
                                </button>
                            </div>
                            <h3 class="text-xl sm:text-2xl font-extrabold text-gray-900" x-text="cvViewMode === 'ats' ? 'Votre CV Reformulé & Optimisé ATS' : 'Document CV Original Téléversé'">Votre CV Reformulé &amp; Optimisé ATS</h3>
                            <p class="text-xs sm:text-sm text-gray-500" x-text="cvViewMode === 'ats' ? 'Cette version structure vos compétences avec des verbes d\'action, standardise la typographie et maximise vos chances lors des screenings.' : 'Visualisez le document original tel qu\'il a été transmis pour l\'analyse.'">Cette version structure vos compétences avec des verbes d'action, standardise la typographie et maximise vos chances lors des screenings.</p>
                        </div>

                        <!-- Actions ATS (Copier, Télécharger, Postuler) -->
                        <div x-show="cvViewMode === 'ats'" class="flex flex-wrap items-center gap-2.5">
                            <!-- Bouton Copier le texte ATS -->
                            <button type="button"
                                    @click="triggerCvAction('copy')"
                                    :disabled="isProcessingCvAction"
                                    class="px-4 py-2.5 rounded-xl bg-gray-900 text-white text-xs font-bold hover:bg-gray-800 disabled:opacity-50 transition flex items-center gap-2 shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                </svg>
                                <span>
                                    @if(isset($cvCopyCost) && $cvCopyCost > 0)
                                        Copier le CV ATS ({{ $cvCopyCost }} {{ $cvCopyCost > 1 ? 'crédits' : 'crédit' }})
                                    @else
                                        Copier le CV ATS (Gratuit)
                                    @endif
                                </span>
                            </button>

                            <!-- Bouton Télécharger le CV ATS (Ouvre le choix du format PDF ou Word) -->
                            <button type="button"
                                    @click="showDownloadFormatModal = true"
                                    :disabled="isProcessingCvAction"
                                    class="px-4 py-2.5 rounded-xl bg-primary-600 text-white text-xs font-bold hover:bg-primary-700 disabled:opacity-50 transition flex items-center gap-2 shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span x-text="downloadButtonLabel">Télécharger mon CV</span>
                                <svg class="w-3.5 h-3.5 opacity-80 -mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Bouton Postuler avec ce CV (Ouvre l'onglet Opportunités) -->
                            <a href="{{ route('jeune.opportunities') }}"
                               class="px-4 py-2.5 rounded-xl bg-white border border-gray-200 text-gray-800 text-xs font-bold hover:bg-gray-50 transition flex items-center gap-2 shadow-xs">
                                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <span>Postuler avec ce CV</span>
                            </a>
                        </div>
                    </div>

                    <!-- VUE 1 : CV ORIGINAL TÉLÉVERSÉ -->
                    <div x-show="cvViewMode === 'original'" x-cloak class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200 shadow-md max-w-4xl mx-auto space-y-6">
                        <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-gray-100">
                            <div>
                                <h4 class="text-sm font-bold text-gray-900">{{ $activeCv->original_filename }}</h4>
                                <p class="text-xs text-gray-500">{{ round($activeCv->file_size / 1024, 1) }} Ko • Transmis le {{ $activeCv->created_at->format('d/m/Y à H:i') }}</p>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold border {{ $activeCv->file_format_badge_color }}">
                                    {{ $activeCv->file_format_label }}
                                </span>
                                @if($activeCv->has_original_file)
                                    <a href="{{ route('jeune.cv.download-original', $activeCv->id) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gray-900 text-white text-xs font-bold hover:bg-gray-800 shadow-xs transition"
                                       title="Télécharger exactement le document CV d'origine transmis">
                                        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        <span>Télécharger le CV original</span>
                                    </a>
                                @endif
                            </div>
                        </div>

                        @if(str_contains(strtolower($activeCv->mime_type ?? ''), 'pdf'))
                            <div class="w-full h-[750px] rounded-2xl overflow-hidden border border-gray-100 bg-gray-50 shadow-inner">
                                <iframe src="{{ route('jeune.cv.view-original', $activeCv->id) }}" class="w-full h-full border-0" title="Aperçu du CV original au format PDF"></iframe>
                            </div>
                        @elseif(str_contains(strtolower($activeCv->mime_type ?? ''), 'image'))
                            <div class="flex justify-center bg-gray-50 p-4 rounded-2xl border border-gray-100">
                                <img src="{{ route('jeune.cv.view-original', $activeCv->id) }}" alt="CV Original" class="max-h-[800px] object-contain rounded-xl shadow-sm" />
                            </div>
                        @else
                            <div class="p-6 bg-gray-50 rounded-2xl border border-gray-200 text-left space-y-4">
                                <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-gray-200">
                                    <div class="space-y-0.5">
                                        <p class="text-xs font-bold text-gray-800 uppercase tracking-wider">Contenu texte extrait de votre document</p>
                                        <p class="text-[11px] text-gray-500">Les documents Word (.docx) sont analysés et retranscrits textuellement ici pour préserver la fidélité de votre profil.</p>
                                    </div>
                                    @if($activeCv->has_original_file)
                                        <a href="{{ route('jeune.cv.download-original', $activeCv->id) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-gray-300 text-xs font-bold text-gray-700 hover:bg-gray-50 hover:text-gray-900 shadow-xs transition"
                                           title="Télécharger exactement votre document Word d'origine">
                                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            <span>Télécharger le document original</span>
                                        </a>
                                    @endif
                                </div>
                                <pre class="whitespace-pre-wrap font-sans text-xs text-gray-800 bg-white p-4 rounded-xl border border-gray-200 max-h-[500px] overflow-y-auto leading-relaxed shadow-xs">{{ $activeCv->parsed_content['raw_text'] ?? 'Contenu non disponible.' }}</pre>
                            </div>
                        @endif
                    </div>

                    <!-- VUE 2 : CV RESTRUCTURÉ ATS + SÉLECTION DE 5 TEMPLATES -->
                    <div x-show="cvViewMode === 'ats'" class="space-y-6">
                        <!-- Sélecteur de templates ATS (0 Défaut gratuit + 5 Templates au choix tarifés) -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Sélectionnez un modèle de CV ATS :</h3>
                                <span class="text-xs text-gray-500">Le modèle par défaut est gratuit. Les modèles avancés valorisent vos compétences clés.</span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                                <!-- Template 0 : Basic ATS (Simple & Inclus) -->
                                <button type="button"
                                        @click="selectedTemplate = 0"
                                        :class="selectedTemplate === 0 ? 'ring-2 ring-primary-600 border-primary-600 bg-primary-50/40 text-primary-950 font-bold' : 'border-gray-200 bg-white hover:bg-gray-50 text-gray-800'"
                                        class="relative p-3 rounded-2xl border text-left transition flex flex-col justify-between shadow-xs">
                                    <div>
                                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-gray-100 text-gray-600">Inclus</span>
                                        <p class="text-xs font-bold mt-1.5 truncate">Basic ATS</p>
                                    </div>
                                    <p class="text-[11px] font-semibold text-emerald-600 mt-2">Gratuit (0 cr.)</p>
                                </button>

                                <!-- Template 1 : Standard Classique -->
                                <button type="button"
                                        @click="selectedTemplate = 1"
                                        :class="selectedTemplate === 1 ? 'ring-2 ring-primary-600 border-primary-600 bg-primary-50/40 text-primary-950 font-bold' : 'border-gray-200 bg-white hover:bg-gray-50 text-gray-800'"
                                        class="relative p-3 rounded-2xl border text-left transition flex flex-col justify-between shadow-xs">
                                    <div>
                                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-blue-100 text-blue-700">Classique</span>
                                        <p class="text-xs font-bold mt-1.5 truncate">Standard Classique</p>
                                    </div>
                                    <p class="text-[11px] font-semibold text-primary-700 mt-2">{{ $templateCosts[1] ?? 1 }} {{ ($templateCosts[1] ?? 1) > 1 ? 'crédits' : 'crédit' }}</p>
                                </button>

                                <!-- Template 2 : Standard Minimaliste -->
                                <button type="button"
                                        @click="selectedTemplate = 2"
                                        :class="selectedTemplate === 2 ? 'ring-2 ring-primary-600 border-primary-600 bg-primary-50/40 text-primary-950 font-bold' : 'border-gray-200 bg-white hover:bg-gray-50 text-gray-800'"
                                        class="relative p-3 rounded-2xl border text-left transition flex flex-col justify-between shadow-xs">
                                    <div>
                                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-700">Clean</span>
                                        <p class="text-xs font-bold mt-1.5 truncate">Standard Minimal</p>
                                    </div>
                                    <p class="text-[11px] font-semibold text-primary-700 mt-2">{{ $templateCosts[2] ?? 2 }} {{ ($templateCosts[2] ?? 2) > 1 ? 'crédits' : 'crédit' }}</p>
                                </button>

                                <!-- Template 3 : Professionnel Élite (BEST-SELLER) -->
                                <button type="button"
                                        @click="selectedTemplate = 3"
                                        :class="selectedTemplate === 3 ? 'ring-2 ring-primary-600 border-primary-600 bg-primary-50/40 text-primary-950 font-bold' : 'border-gray-200 bg-white hover:bg-gray-50 text-gray-800'"
                                        class="relative p-3 rounded-2xl border text-left transition flex flex-col justify-between shadow-xs">
                                    <span class="absolute -top-2.5 right-2 px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-amber-500 text-white shadow-xs">⭐ Best-Seller</span>
                                    <div>
                                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-purple-100 text-purple-700">2 Colonnes</span>
                                        <p class="text-xs font-bold mt-1.5 truncate">Professionnel Élite</p>
                                    </div>
                                    <p class="text-[11px] font-semibold text-primary-700 mt-2">{{ $templateCosts[3] ?? 3 }} {{ ($templateCosts[3] ?? 3) > 1 ? 'crédits' : 'crédit' }}</p>
                                </button>

                                <!-- Template 4 : Expert Moderne -->
                                <button type="button"
                                        @click="selectedTemplate = 4"
                                        :class="selectedTemplate === 4 ? 'ring-2 ring-primary-600 border-primary-600 bg-primary-50/40 text-primary-950 font-bold' : 'border-gray-200 bg-white hover:bg-gray-50 text-gray-800'"
                                        class="relative p-3 rounded-2xl border text-left transition flex flex-col justify-between shadow-xs">
                                    <div>
                                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-700">Moderne</span>
                                        <p class="text-xs font-bold mt-1.5 truncate">Expert Moderne</p>
                                    </div>
                                    <p class="text-[11px] font-semibold text-primary-700 mt-2">{{ $templateCosts[4] ?? 4 }} {{ ($templateCosts[4] ?? 4) > 1 ? 'crédits' : 'crédit' }}</p>
                                </button>

                                <!-- Template 5 : Avancé Cadre -->
                                <button type="button"
                                        @click="selectedTemplate = 5"
                                        :class="selectedTemplate === 5 ? 'ring-2 ring-primary-600 border-primary-600 bg-primary-50/40 text-primary-950 font-bold' : 'border-gray-200 bg-white hover:bg-gray-50 text-gray-800'"
                                        class="relative p-3 rounded-2xl border text-left transition flex flex-col justify-between shadow-xs">
                                    <div>
                                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-rose-100 text-rose-700">Cadre</span>
                                        <p class="text-xs font-bold mt-1.5 truncate">Avancé International</p>
                                    </div>
                                    <p class="text-[11px] font-semibold text-primary-700 mt-2">{{ $templateCosts[5] ?? 5 }} {{ ($templateCosts[5] ?? 5) > 1 ? 'crédits' : 'crédit' }}</p>
                                </button>
                            </div>
                        </div>

                        @php
                            $norm = $activeCv->normalized_cv_data;
                            $labels = $norm['labels'] ?? [];

                            $formatPlaceholders = function (?string $text, string $fieldType = 'summary', ?int $expIndex = null, ?int $bulletIndex = null) use ($activeCv) {
                                if ($text === null || $text === '') {
                                    return '';
                                }

                                $cvId = $activeCv ? (int) $activeCv->id : 0;
                                $pattern = '/(\[rempli:[^|\]]+\|guide:[^\]]+\]|\[(?:À compléter|Compléter|Insérer|A completer|A renseigner)[^\]]*\]|\{[^\}]+\})/iu';
                                $parts = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
                                if (! $parts) {
                                    return e($text);
                                }

                                $html = '';
                                foreach ($parts as $part) {
                                    if (preg_match('/^\[rempli:([^|\]]+)\|guide:([^\]]+)\]$/u', $part, $m)) {
                                        $html .= view('jeune.partials.cv_placeholder_item', [
                                            'cvId' => $cvId,
                                            'fieldType' => $fieldType,
                                            'expIndex' => $expIndex,
                                            'bulletIndex' => $bulletIndex,
                                            'originalTag' => $m[2],
                                            'currentValue' => $m[1],
                                            'isFilled' => true,
                                        ])->render();
                                    } elseif (preg_match('/^(\[(?:À compléter|Compléter|Insérer|A completer|A renseigner)[^\]]*\]|\{[^\}]+\})$/iu', $part)) {
                                        $html .= view('jeune.partials.cv_placeholder_item', [
                                            'cvId' => $cvId,
                                            'fieldType' => $fieldType,
                                            'expIndex' => $expIndex,
                                            'bulletIndex' => $bulletIndex,
                                            'originalTag' => $part,
                                            'currentValue' => '',
                                            'isFilled' => false,
                                        ])->render();
                                    } else {
                                        $html .= e($part);
                                    }
                                }

                                return $html;
                            };
                        @endphp

                        <!-- Bannière explicative : Contenu optimisé & Balises à compléter -->
                        <div class="max-w-4xl mx-auto mb-4 bg-amber-50/90 border border-amber-200/80 rounded-2xl p-4 flex items-start gap-3 text-xs text-amber-900 shadow-2xs">
                            <div class="w-7 h-7 rounded-xl bg-amber-100 border border-amber-300 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-4 h-4 text-amber-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <p class="font-bold text-amber-950">Contenu restructuré & Recommandations ATS appliquées</p>
                                <p class="text-amber-800 leading-relaxed">
                                    Vos expériences ont été reformulées avec des verbes d'action puissants. <strong>Cliquez directement sur les pastilles jaunes</strong> <span class="inline-block px-1.5 py-0.5 rounded font-semibold bg-amber-200/80 text-amber-950 border border-amber-300">[À compléter : résultat...]</span> pour renseigner vos chiffres réels et les enregistrer instantanément dans votre CV (PDF et Word) !
                                </p>
                            </div>
                        </div>

                        <!-- Zone d'impression & d'aperçu du CV ATS sélectionné -->
                        <div id="cvEnhancedPrintArea"
                             class="select-none bg-white rounded-3xl p-8 sm:p-12 border border-gray-200 shadow-md max-w-4xl mx-auto text-gray-900 font-sans"
                             style="-webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none;">

                            <!-- ======================================================== -->
                            <!-- TEMPLATE 0 : BASIC ATS SIMPLE (Modèle par défaut gratuit) -->
                            <!-- ======================================================== -->
                            <div x-show="selectedTemplate === 0" class="space-y-6">
                                <div class="pb-4 border-b border-gray-300 space-y-1">
                                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ $activeCv->candidate_name }}</h1>
                                    <p class="text-sm font-semibold text-gray-700">{{ $activeCv->candidate_title }}</p>
                                    <p class="text-xs text-gray-600">
                                        {{ $activeCv->candidate_contact['email'] ?? '' }}
                                        @if(!empty($activeCv->candidate_contact['phone'])) | {{ $activeCv->candidate_contact['phone'] }} @endif
                                        @if(!empty($activeCv->candidate_contact['location'])) | {{ $activeCv->candidate_contact['location'] }} @endif
                                    </p>
                                </div>

                                <div class="space-y-1.5">
                                    <h2 class="text-xs font-bold text-gray-900 uppercase tracking-wide border-b border-gray-300 pb-0.5">{{ $labels['profile'] ?? 'PROFESSIONAL SUMMARY' }}</h2>
                                    <p class="text-xs leading-relaxed text-gray-800 text-justify">{!! $formatPlaceholders($norm['profile_summary']) !!}</p>
                                </div>

                                @if(!empty($norm['experiences']))
                                    <div class="space-y-3">
                                        <h2 class="text-xs font-bold text-gray-900 uppercase tracking-wide border-b border-gray-300 pb-0.5">{{ $labels['experience'] ?? 'PROFESSIONAL EXPERIENCE' }}</h2>
                                        @foreach($norm['experiences'] as $expIndex => $exp)
                                            <div class="space-y-1">
                                                <div class="flex justify-between text-xs font-semibold">
                                                    <span class="text-gray-900">{{ $exp['title'] }} @if(!empty($exp['company'])) — {{ $exp['company'] }} @endif</span>
                                                    <span class="text-gray-600">{{ $exp['period'] ?: ($labels['recently'] ?? 'Current role') }}</span>
                                                </div>
                                                @if(!empty($exp['bullets']))
                                                    <ul class="list-disc list-inside text-xs text-gray-700 space-y-0.5 pl-1">
                                                        @foreach($exp['bullets'] as $bulletIndex => $b)
                                                            <li class="leading-relaxed">{!! $formatPlaceholders($b, 'experience', $expIndex, $bulletIndex) !!}</li>
                                                        @endforeach
                                                    </ul>
                                                @elseif(!empty($exp['description']))
                                                    <p class="text-xs text-gray-700 leading-relaxed">{!! $formatPlaceholders($exp['description'], 'experience', $expIndex, 0) !!}</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if(!empty($norm['education']))
                                    <div class="space-y-2">
                                        <h2 class="text-xs font-bold text-gray-900 uppercase tracking-wide border-b border-gray-300 pb-0.5">{{ $labels['education'] ?? 'EDUCATION' }}</h2>
                                        @foreach($norm['education'] as $edu)
                                            <div class="flex justify-between text-xs">
                                                <span class="font-semibold text-gray-900">{{ $edu['degree'] }} @if(!empty($edu['school'])) — {{ $edu['school'] }} @endif</span>
                                                <span class="text-gray-600">{{ $edu['year'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if(!empty($norm['skills']))
                                    <div class="space-y-1.5">
                                        <h2 class="text-xs font-bold text-gray-900 uppercase tracking-wide border-b border-gray-300 pb-0.5">{{ $labels['skills'] ?? 'CORE SKILLS' }}</h2>
                                        <p class="text-xs text-gray-800 leading-relaxed">{{ implode(' • ', $norm['skills']) }}</p>
                                    </div>
                                @endif

                                @if(!empty($norm['certifications']))
                                    <div class="space-y-1.5">
                                        <h2 class="text-xs font-bold text-gray-900 uppercase tracking-wide border-b border-gray-300 pb-0.5">{{ $labels['certifications'] ?? 'CERTIFICATIONS' }}</h2>
                                        <ul class="list-disc list-inside text-xs text-gray-700 space-y-0.5 pl-1">
                                            @foreach($norm['certifications'] as $cert)
                                                <li>{{ is_array($cert) ? ($cert['name'] ?? implode(', ', $cert)) : $cert }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if(!empty($norm['languages']))
                                    <div class="space-y-1.5">
                                        <h2 class="text-xs font-bold text-gray-900 uppercase tracking-wide border-b border-gray-300 pb-0.5">{{ $labels['languages'] ?? 'LANGUAGES' }}</h2>
                                        <p class="text-xs text-gray-800 leading-relaxed">{{ implode('  •  ', array_map(fn($l) => is_array($l) ? ($l['language'] ?? implode(', ', $l)) : $l, $norm['languages'])) }}</p>
                                    </div>
                                @endif
                            </div>

                            <!-- ======================================================== -->
                            <!-- TEMPLATE 1 : STANDARD CLASSIQUE (1 Colonne centré)        -->
                            <!-- ======================================================== -->
                            <div x-show="selectedTemplate === 1" class="space-y-8">
                                <div class="border-b-2 border-gray-900 pb-6 text-center space-y-2">
                                    <h1 class="text-3xl font-black tracking-tight text-gray-900 uppercase">{{ $activeCv->candidate_name }}</h1>
                                    <p class="text-base font-bold text-primary-700 tracking-wide">{{ $activeCv->candidate_title }}</p>
                                    <div class="flex flex-wrap items-center justify-center gap-3 text-xs text-gray-600 pt-1">
                                        @if(!empty($activeCv->candidate_contact['email'])) <span>{{ $activeCv->candidate_contact['email'] }}</span> @endif
                                        @if(!empty($activeCv->candidate_contact['phone'])) <span>• {{ $activeCv->candidate_contact['phone'] }}</span> @endif
                                        @if(!empty($activeCv->candidate_contact['location'])) <span>• {{ $activeCv->candidate_contact['location'] }}</span> @endif
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <h2 class="text-xs font-black text-gray-900 uppercase tracking-widest border-b border-gray-200 pb-1">{{ $labels['profile'] ?? 'PROFESSIONAL SUMMARY' }}</h2>
                                    <p class="text-xs leading-relaxed text-gray-700 text-justify">{!! $formatPlaceholders($norm['profile_summary']) !!}</p>
                                </div>

                                @if(!empty($norm['experiences']))
                                    <div class="space-y-4">
                                        <h2 class="text-xs font-black text-gray-900 uppercase tracking-widest border-b border-gray-200 pb-1">{{ $labels['experience'] ?? 'PROFESSIONAL EXPERIENCE' }}</h2>
                                        <div class="space-y-4">
                                            @foreach($norm['experiences'] as $expIndex => $exp)
                                                <div class="space-y-1">
                                                    <div class="flex items-center justify-between text-xs">
                                                        <h3 class="font-bold text-gray-900">{{ $exp['title'] }} @if(!empty($exp['company'])) — <span class="font-semibold text-gray-700">{{ $exp['company'] }}</span> @endif</h3>
                                                        <span class="text-gray-500 font-medium">{{ $exp['period'] ?: ($labels['recently'] ?? 'Current role') }}</span>
                                                    </div>
                                                    @if(!empty($exp['bullets']))
                                                        <ul class="space-y-1 pt-1 text-xs text-gray-600">
                                                            @foreach($exp['bullets'] as $bulletIndex => $bullet)
                                                                <li class="flex items-start gap-2">
                                                                    <span class="text-gray-400 font-bold">•</span>
                                                                    <span class="leading-relaxed">{!! $formatPlaceholders($bullet, 'experience', $expIndex, $bulletIndex) !!}</span>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @elseif(!empty($exp['description']))
                                                        <p class="text-xs text-gray-600 leading-relaxed">{!! $formatPlaceholders($exp['description'], 'experience', $expIndex, 0) !!}</p>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($norm['education']))
                                    <div class="space-y-4">
                                        <h2 class="text-xs font-black text-gray-900 uppercase tracking-widest border-b border-gray-200 pb-1">{{ $labels['education'] ?? 'EDUCATION' }}</h2>
                                        <div class="space-y-3">
                                            @foreach($norm['education'] as $edu)
                                                <div class="flex items-center justify-between text-xs">
                                                    <div>
                                                        <h3 class="font-bold text-gray-900">{{ $edu['degree'] }}</h3>
                                                        <p class="text-gray-600">{{ $edu['school'] }}</p>
                                                    </div>
                                                    <span class="text-gray-500 font-medium">{{ $edu['year'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($norm['skills']))
                                    <div class="space-y-2">
                                        <h2 class="text-xs font-black text-gray-900 uppercase tracking-widest border-b border-gray-200 pb-1">{{ $labels['skills'] ?? 'CORE SKILLS' }}</h2>
                                        <div class="flex flex-wrap gap-2 pt-1">
                                            @foreach($norm['skills'] as $skill)
                                                <span class="px-2.5 py-1 rounded-md bg-gray-100 text-gray-800 text-xs font-semibold">{{ $skill }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($norm['certifications']))
                                    <div class="space-y-2">
                                        <h2 class="text-xs font-black text-gray-900 uppercase tracking-widest border-b border-gray-200 pb-1">{{ $labels['certifications'] ?? 'CERTIFICATIONS' }}</h2>
                                        <div class="space-y-1 text-xs">
                                            @foreach($norm['certifications'] as $cert)
                                                <p class="text-gray-800 font-medium">• {{ is_array($cert) ? ($cert['name'] ?? implode(', ', $cert)) : $cert }}</p>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- ======================================================== -->
                            <!-- TEMPLATE 2 : STANDARD MINIMALISTE (Bordures d'accent)    -->
                            <!-- ======================================================== -->
                            <div x-show="selectedTemplate === 2" class="space-y-7">
                                <div class="flex items-center justify-between pb-5 border-b border-gray-200">
                                    <div class="space-y-1">
                                        <h1 class="text-3xl font-extrabold text-gray-900">{{ $activeCv->candidate_name }}</h1>
                                        <p class="text-sm font-semibold text-indigo-600">{{ $activeCv->candidate_title }}</p>
                                    </div>
                                    <div class="text-right text-xs text-gray-500 space-y-0.5">
                                        <p>{{ $activeCv->candidate_contact['email'] ?? '' }}</p>
                                        <p>{{ $activeCv->candidate_contact['phone'] ?? '' }}</p>
                                        <p>{{ $activeCv->candidate_contact['location'] ?? '' }}</p>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <h2 class="text-xs font-black uppercase text-indigo-700 tracking-wider pl-2 border-l-4 border-indigo-600">{{ $labels['profile'] ?? 'PROFESSIONAL SUMMARY' }}</h2>
                                    <p class="text-xs text-gray-700 leading-relaxed text-justify">{!! $formatPlaceholders($norm['profile_summary']) !!}</p>
                                </div>

                                @if(!empty($norm['experiences']))
                                    <div class="space-y-4">
                                        <h2 class="text-xs font-black uppercase text-indigo-700 tracking-wider pl-2 border-l-4 border-indigo-600">{{ $labels['experience'] ?? 'PROFESSIONAL EXPERIENCE' }}</h2>
                                        <div class="space-y-4">
                                            @foreach($norm['experiences'] as $expIndex => $exp)
                                                <div class="space-y-1 bg-gray-50/70 p-3 rounded-xl border border-gray-100">
                                                    <div class="flex items-center justify-between text-xs">
                                                        <h3 class="font-bold text-gray-900">{{ $exp['title'] }} <span class="text-indigo-600 font-semibold">• {{ $exp['company'] }}</span></h3>
                                                        <span class="text-gray-500 font-medium text-[11px]">{{ $exp['period'] ?: ($labels['recently'] ?? 'Current role') }}</span>
                                                    </div>
                                                    @if(!empty($exp['bullets']))
                                                        <ul class="space-y-1 pt-1 text-xs text-gray-700">
                                                            @foreach($exp['bullets'] as $bulletIndex => $b)
                                                                <li class="flex items-start gap-1.5"><span class="text-indigo-500 font-bold">›</span><span>{!! $formatPlaceholders($b, 'experience', $expIndex, $bulletIndex) !!}</span></li>
                                                            @endforeach
                                                        </ul>
                                                    @elseif(!empty($exp['description']))
                                                        <p class="text-xs text-gray-600 leading-relaxed">{!! $formatPlaceholders($exp['description'], 'experience', $expIndex, 0) !!}</p>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($norm['education']))
                                    <div class="space-y-3">
                                        <h2 class="text-xs font-black uppercase text-indigo-700 tracking-wider pl-2 border-l-4 border-indigo-600">{{ $labels['education'] ?? 'EDUCATION' }}</h2>
                                        <div class="grid sm:grid-cols-2 gap-3">
                                            @foreach($norm['education'] as $edu)
                                                <div class="p-3 rounded-xl border border-gray-100 text-xs space-y-0.5">
                                                    <p class="font-bold text-gray-900">{{ $edu['degree'] }}</p>
                                                    <p class="text-gray-600">{{ $edu['school'] }}</p>
                                                    <p class="text-gray-400 text-[11px]">{{ $edu['year'] }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($norm['skills']))
                                    <div class="space-y-2">
                                        <h2 class="text-xs font-black uppercase text-indigo-700 tracking-wider pl-2 border-l-4 border-indigo-600">{{ $labels['skills'] ?? 'CORE SKILLS' }}</h2>
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach($norm['skills'] as $skill)
                                                <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-900 text-xs font-semibold">{{ $skill }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- ======================================================== -->
                            <!-- TEMPLATE 3 : PROFESSIONNEL ÉLITE (BEST-SELLER / MODÈLE)    -->
                            <!-- ======================================================== -->
                            <div x-show="selectedTemplate === 3" class="space-y-8">
                                <!-- En-tête avec Avatar initiales bleu vif -->
                                <div class="flex items-start justify-between gap-6 pb-6 border-b border-gray-200">
                                    <div class="space-y-1.5 flex-1">
                                        <h1 class="text-3xl font-black text-gray-900 uppercase tracking-tight">{{ $activeCv->candidate_name }}</h1>
                                        <p class="text-base font-bold text-blue-600">{{ $activeCv->candidate_title }}</p>
                                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-600 pt-1">
                                            @if(!empty($activeCv->candidate_contact['phone']))
                                                <span class="flex items-center gap-1 font-medium">📞 {{ $activeCv->candidate_contact['phone'] }}</span>
                                            @endif
                                            @if(!empty($activeCv->candidate_contact['email']))
                                                <span class="flex items-center gap-1 font-medium">✉️ {{ $activeCv->candidate_contact['email'] }}</span>
                                            @endif
                                            @if(!empty($activeCv->candidate_contact['location']))
                                                <span class="flex items-center gap-1 font-medium">📍 {{ $activeCv->candidate_contact['location'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="w-16 h-16 rounded-full bg-blue-600 text-white font-black text-2xl flex items-center justify-center flex-shrink-0 shadow-md">
                                        {{ $activeCv->initials }}
                                    </div>
                                </div>

                                <!-- Structure 2 Colonnes -->
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                                    <!-- Colonne Gauche (~60% / 7 cols) -->
                                    <div class="md:col-span-7 space-y-6">
                                        <!-- RÉSUMÉ -->
                                        <div class="space-y-2">
                                            <h2 class="text-xs font-black text-gray-900 uppercase tracking-wider pb-1 border-b border-gray-900">{{ $labels['profile'] ?? 'PROFESSIONAL SUMMARY' }}</h2>
                                            <p class="text-xs leading-relaxed text-gray-700 text-justify">{!! $formatPlaceholders($norm['profile_summary']) !!}</p>
                                        </div>

                                        <!-- EXPÉRIENCE -->
                                        @if(!empty($norm['experiences']))
                                            <div class="space-y-4">
                                                <h2 class="text-xs font-black text-gray-900 uppercase tracking-wider pb-1 border-b border-gray-900">{{ $labels['experience'] ?? 'PROFESSIONAL EXPERIENCE' }}</h2>
                                                <div class="space-y-5">
                                                    @foreach($norm['experiences'] as $expIndex => $exp)
                                                        <div class="space-y-1.5">
                                                            <div class="flex items-baseline justify-between text-xs">
                                                                <h3 class="font-bold text-gray-900">{{ $exp['title'] }}</h3>
                                                                <span class="text-gray-500 font-medium text-[11px]">{{ $exp['period'] ?: ($labels['recently'] ?? 'Current role') }}</span>
                                                            </div>
                                                            @if(!empty($exp['company']))
                                                                <p class="text-xs font-semibold text-blue-600">{{ $exp['company'] }}</p>
                                                            @endif
                                                            @if(!empty($exp['bullets']))
                                                                <ul class="space-y-1.5 pt-1 text-xs text-gray-700">
                                                                    @foreach($exp['bullets'] as $bulletIndex => $bullet)
                                                                        <li class="flex items-start gap-2">
                                                                            <span class="text-blue-500 font-bold">•</span>
                                                                            <span class="leading-relaxed">{!! $formatPlaceholders($bullet, 'experience', $expIndex, $bulletIndex) !!}</span>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            @elseif(!empty($exp['description']))
                                                                <p class="text-xs text-gray-700 leading-relaxed">{!! $formatPlaceholders($exp['description'], 'experience', $expIndex, 0) !!}</p>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <!-- ÉDUCATION -->
                                        @if(!empty($norm['education']))
                                            <div class="space-y-3">
                                                <h2 class="text-xs font-black text-gray-900 uppercase tracking-wider pb-1 border-b border-gray-900">{{ $labels['education'] ?? 'EDUCATION' }}</h2>
                                                <div class="space-y-3">
                                                    @foreach($norm['education'] as $edu)
                                                        <div class="text-xs">
                                                            <div class="flex items-baseline justify-between">
                                                                <h3 class="font-bold text-gray-900">{{ $edu['degree'] }}</h3>
                                                                <span class="text-gray-500 font-medium text-[11px]">{{ $edu['year'] }}</span>
                                                            </div>
                                                            @if(!empty($edu['school']))
                                                                <p class="text-blue-600 font-medium text-xs">{{ $edu['school'] }}</p>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Colonne Droite (~40% / 5 cols) -->
                                    <div class="md:col-span-5 space-y-6">
                                        <!-- COMPÉTENCES -->
                                        <div class="space-y-4">
                                            <h2 class="text-xs font-black text-gray-900 uppercase tracking-wider pb-1 border-b border-gray-900">{{ $labels['skills_simple'] ?? 'CORE SKILLS' }}</h2>
                                            @if(!empty($norm['categorized_skills']))
                                                <div class="space-y-4">
                                                    @foreach($norm['categorized_skills'] as $catName => $skills)
                                                        <div class="space-y-2">
                                                            <h3 class="text-xs font-bold text-blue-600 border-b border-dashed border-blue-200 pb-0.5">{{ $catName }}</h3>
                                                            <div class="flex flex-wrap gap-1.5">
                                                                @foreach($skills as $sk)
                                                                    <span class="px-2 py-0.5 rounded-md bg-gray-100 text-gray-800 text-[11px] font-medium">{{ $sk }}</span>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @elseif(!empty($norm['skills']))
                                                <div class="flex flex-wrap gap-1.5">
                                                    @foreach($norm['skills'] as $sk)
                                                        <span class="px-2 py-0.5 rounded-md bg-gray-100 text-gray-800 text-[11px] font-medium">{{ $sk }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                        <!-- CERTIFICATIONS -->
                                        @if(!empty($norm['certifications']))
                                            <div class="space-y-2.5">
                                                <h2 class="text-xs font-black text-gray-900 uppercase tracking-wider pb-1 border-b border-gray-900">{{ $labels['certifications'] ?? 'CERTIFICATIONS' }}</h2>
                                                <div class="space-y-2 text-xs">
                                                    @foreach($norm['certifications'] as $cert)
                                                        <div class="flex items-start gap-2">
                                                            <span class="text-amber-600 font-bold">📜</span>
                                                            <span class="font-semibold text-gray-800">{{ is_array($cert) ? ($cert['name'] ?? implode(', ', $cert)) : $cert }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <!-- LANGUES -->
                                        @if(!empty($norm['languages']))
                                            <div class="space-y-2.5">
                                                <h2 class="text-xs font-black text-gray-900 uppercase tracking-wider pb-1 border-b border-gray-900">{{ $labels['languages'] ?? 'LANGUAGES' }}</h2>
                                                <div class="space-y-2 text-xs">
                                                    @foreach($norm['languages'] as $lang)
                                                        <div class="flex items-center justify-between text-gray-800 font-medium">
                                                            <span>{{ is_array($lang) ? ($lang['language'] ?? implode(', ', $lang)) : $lang }}</span>
                                                            <span class="text-blue-600 font-bold">●●●●○</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- ======================================================== -->
                            <!-- TEMPLATE 4 : EXPERT MODERNE (Bandeau et badges)           -->
                            <!-- ======================================================== -->
                            <div x-show="selectedTemplate === 4" class="space-y-7">
                                <div class="bg-gray-900 text-white p-6 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="space-y-1">
                                        <h1 class="text-2xl sm:text-3xl font-black uppercase tracking-tight">{{ $activeCv->candidate_name }}</h1>
                                        <p class="text-sm font-semibold text-emerald-400">{{ $activeCv->candidate_title }}</p>
                                    </div>
                                    <div class="text-xs text-gray-300 space-y-0.5 sm:text-right">
                                        <p>{{ $activeCv->candidate_contact['email'] ?? '' }}</p>
                                        <p>{{ $activeCv->candidate_contact['phone'] ?? '' }}</p>
                                        <p>{{ $activeCv->candidate_contact['location'] ?? '' }}</p>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <h2 class="text-xs font-bold text-gray-900 uppercase tracking-wider border-b-2 border-emerald-500 pb-1">{{ $labels['executive_summary'] ?? 'EXECUTIVE SUMMARY' }}</h2>
                                    <p class="text-xs text-gray-700 leading-relaxed text-justify">{!! $formatPlaceholders($norm['profile_summary']) !!}</p>
                                </div>

                                @if(!empty($norm['experiences']))
                                    <div class="space-y-4">
                                        <h2 class="text-xs font-bold text-gray-900 uppercase tracking-wider border-b-2 border-emerald-500 pb-1">{{ $labels['achievements'] ?? 'KEY ACHIEVEMENTS' }}</h2>
                                        <div class="space-y-4">
                                            @foreach($norm['experiences'] as $expIndex => $exp)
                                                <div class="space-y-1.5 border-l-2 border-gray-200 pl-4">
                                                    <div class="flex items-center justify-between text-xs">
                                                        <h3 class="font-bold text-gray-900">{{ $exp['title'] }} — <span class="text-emerald-700">{{ $exp['company'] }}</span></h3>
                                                        <span class="text-gray-500 font-medium">{{ $exp['period'] ?: ($labels['recently'] ?? 'Current role') }}</span>
                                                    </div>
                                                    @if(!empty($exp['bullets']))
                                                        <ul class="space-y-1 text-xs text-gray-600">
                                                            @foreach($exp['bullets'] as $bulletIndex => $b)
                                                                <li class="flex items-start gap-2"><span class="text-emerald-500 font-bold">✔</span><span>{!! $formatPlaceholders($b, 'experience', $expIndex, $bulletIndex) !!}</span></li>
                                                            @endforeach
                                                        </ul>
                                                    @elseif(!empty($exp['description']))
                                                        <p class="text-xs text-gray-700 leading-relaxed">{!! $formatPlaceholders($exp['description'], 'experience', $expIndex, 0) !!}</p>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($norm['education']))
                                    <div class="space-y-3">
                                        <h2 class="text-xs font-bold text-gray-900 uppercase tracking-wider border-b-2 border-emerald-500 pb-1">{{ $labels['education'] ?? 'EDUCATION' }}</h2>
                                        <div class="grid sm:grid-cols-2 gap-3">
                                            @foreach($norm['education'] as $edu)
                                                <div class="p-3 bg-gray-50 rounded-xl text-xs space-y-0.5">
                                                    <p class="font-bold text-gray-900">{{ $edu['degree'] }}</p>
                                                    <p class="text-emerald-700 font-medium">{{ $edu['school'] }}</p>
                                                    <p class="text-gray-400 text-[11px]">{{ $edu['year'] }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($norm['skills']))
                                    <div class="space-y-2">
                                        <h2 class="text-xs font-bold text-gray-900 uppercase tracking-wider border-b-2 border-emerald-500 pb-1">{{ $labels['skills'] ?? 'CORE SKILLS' }}</h2>
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach($norm['skills'] as $sk)
                                                <span class="px-2.5 py-1 rounded-lg bg-gray-100 text-gray-800 text-xs font-medium">{{ $sk }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- ======================================================== -->
                            <!-- TEMPLATE 5 : AVANCÉ CADRE & INTERNATIONAL (Exécutif)      -->
                            <!-- ======================================================== -->
                            <div x-show="selectedTemplate === 5" class="space-y-7 font-serif">
                                <div class="text-center pb-6 border-b-4 border-double border-gray-900 space-y-2">
                                    <h1 class="text-3xl font-bold uppercase tracking-widest text-gray-900">{{ $activeCv->candidate_name }}</h1>
                                    <p class="text-sm italic font-semibold text-gray-700 font-sans tracking-wide">{{ $activeCv->candidate_title }}</p>
                                    <div class="flex items-center justify-center gap-4 text-xs font-sans text-gray-600">
                                        @if(!empty($activeCv->candidate_contact['email'])) <span>{{ $activeCv->candidate_contact['email'] }}</span> @endif
                                        @if(!empty($activeCv->candidate_contact['phone'])) <span>• {{ $activeCv->candidate_contact['phone'] }}</span> @endif
                                        @if(!empty($activeCv->candidate_contact['location'])) <span>• {{ $activeCv->candidate_contact['location'] }}</span> @endif
                                    </div>
                                </div>

                                <div class="space-y-2 font-sans">
                                    <h2 class="text-xs font-bold uppercase tracking-widest text-gray-900 border-b border-gray-300 pb-1 font-serif">{{ $labels['leadership'] ?? 'LEADERSHIP PROFILE' }}</h2>
                                    <p class="text-xs text-gray-700 leading-relaxed text-justify">{!! $formatPlaceholders($norm['profile_summary']) !!}</p>
                                </div>

                                @if(!empty($norm['experiences']))
                                    <div class="space-y-4 font-sans">
                                        <h2 class="text-xs font-bold uppercase tracking-widest text-gray-900 border-b border-gray-300 pb-1 font-serif">{{ $labels['experience'] ?? 'PROFESSIONAL EXPERIENCE' }}</h2>
                                        <div class="space-y-4">
                                            @foreach($norm['experiences'] as $expIndex => $exp)
                                                <div class="space-y-1">
                                                    <div class="flex items-center justify-between text-xs font-serif font-bold text-gray-900">
                                                        <span>{{ $exp['title'] }} — {{ $exp['company'] }}</span>
                                                        <span class="font-sans font-normal text-gray-600">{{ $exp['period'] ?: ($labels['recently'] ?? 'Current role') }}</span>
                                                    </div>
                                                    @if(!empty($exp['bullets']))
                                                        <ul class="space-y-1 pt-1 text-xs text-gray-700">
                                                            @foreach($exp['bullets'] as $bulletIndex => $b)
                                                                <li class="flex items-start gap-2">
                                                                    <span class="text-gray-400 font-bold">—</span>
                                                                    <span class="leading-relaxed">{!! $formatPlaceholders($b, 'experience', $expIndex, $bulletIndex) !!}</span>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @elseif(!empty($exp['description']))
                                                        <p class="text-xs text-gray-700 leading-relaxed">{!! $formatPlaceholders($exp['description'], 'experience', $expIndex, 0) !!}</p>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($norm['education']))
                                    <div class="space-y-3 font-sans">
                                        <h2 class="text-xs font-bold uppercase tracking-widest text-gray-900 border-b border-gray-300 pb-1 font-serif">{{ $labels['higher_education'] ?? 'EDUCATION' }}</h2>
                                        @foreach($norm['education'] as $edu)
                                            <div class="flex items-center justify-between text-xs">
                                                <div>
                                                    <h3 class="font-bold text-gray-900 font-serif">{{ $edu['degree'] }}</h3>
                                                    <p class="text-gray-600">{{ $edu['school'] }}</p>
                                                </div>
                                                <span class="text-gray-500 font-medium">{{ $edu['year'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if(!empty($norm['skills']))
                                    <div class="space-y-2 font-sans">
                                        <h2 class="text-xs font-bold uppercase tracking-widest text-gray-900 border-b border-gray-300 pb-1 font-serif">{{ $labels['skills'] ?? 'CORE SKILLS' }}</h2>
                                        <p class="text-xs text-gray-800 leading-relaxed">{{ implode('  |  ', $norm['skills']) }}</p>
                                    </div>
                                @endif
                            </div>

                        </div>
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
            <button @click="driveFilter = 'cv'"
                :class="driveFilter === 'cv' ? 'bg-primary-500 text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200'"
                class="px-4 py-2 rounded-full text-sm font-medium transition">
                CVs originaux
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
                                    @elseif($document->document_type === 'cv') bg-amber-50 text-amber-600
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

    <!-- MODAL : CHOIX DU FORMAT DE TÉLÉCHARGEMENT (PDF vs WORD) -->
    <div x-show="showDownloadFormatModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showDownloadFormatModal = false">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs" @click="showDownloadFormatModal = false"></div>
            <div class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl z-10 space-y-6">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-primary-50 flex items-center justify-center text-primary-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg sm:text-xl font-bold text-gray-900">Format d'export de votre CV</h3>
                            <p class="text-xs text-gray-500">Choisissez le format adapté à votre besoin</p>
                        </div>
                    </div>
                    <button type="button" @click="showDownloadFormatModal = false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Les 2 options de format -->
                <div class="space-y-4">
                    <!-- Option 1 : Fichier PDF (Recommandé) -->
                    <button type="button"
                            @click="showDownloadFormatModal = false; triggerCvAction('download_pdf')"
                            class="w-full p-4 rounded-2xl border-2 border-emerald-200 bg-emerald-50/40 hover:bg-emerald-50 hover:border-emerald-500 text-left transition group relative shadow-xs flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M7 2h7l5 5v13a2 2 0 01-2 2H7a2 2 0 01-2-2V4a2 2 0 012-2zm6 1.5V7h3.5L13 3.5zM8.5 13a1.5 1.5 0 00-1.5 1.5v3a1.5 1.5 0 003 0v-.5h-1v.5a.5.5 0 01-.5.5.5.5 0 01-.5-.5v-1h2a1 1 0 001-1v-.5a1.5 1.5 0 00-1.5-1.5h-2zm0 1h1.5a.5.5 0 01.5.5v.5h-2v-.5a.5.5 0 01.5-.5z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <h4 class="text-sm font-bold text-gray-900 group-hover:text-emerald-900">Format PDF (.pdf)</h4>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 uppercase tracking-wide">Recommandé</span>
                            </div>
                            <p class="text-xs text-gray-600 leading-relaxed mb-2">
                                <strong>Rendu visuel exact et soigné</strong>, 100% fidèle à l'aperçu affiché à l'écran. Idéal pour postuler directement auprès des recruteurs et franchir les filtres ATS sans risque de déformation.
                            </p>
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 group-hover:underline">
                                Télécharger en PDF
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </span>
                        </div>
                    </button>

                    <!-- Option 2 : Fichier Word (.docx) -->
                    <button type="button"
                            @click="showDownloadFormatModal = false; triggerCvAction('download_docx')"
                            class="w-full p-4 rounded-2xl border-2 border-indigo-200 bg-indigo-50/40 hover:bg-indigo-50 hover:border-indigo-500 text-left transition group relative shadow-xs flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 2l5 5h-5V4zm-1.8 14.5l-1.2-4.5-1.2 4.5H7.2L5.5 11h1.7l1.1 5.3 1.2-4.3h1.2l1.2 4.3 1.1-5.3h1.7l-1.7 7.5h-1.6z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <h4 class="text-sm font-bold text-gray-900 group-hover:text-indigo-900">Format Word (.docx)</h4>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-100 text-indigo-800 uppercase tracking-wide">Modifiable</span>
                            </div>
                            <p class="text-xs text-gray-600 leading-relaxed mb-2">
                                Fichier bureautique modifiable pour adapter vos expériences, ajouter des détails ou personnaliser le contenu à votre convenance.
                            </p>
                            <!-- Avertissement Word demandé par l'utilisateur -->
                            <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-200/70 text-[11px] text-amber-900 flex items-start gap-2 mb-2">
                                <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span><strong>Remarque importante :</strong> La mise en page dans Microsoft Word ou WPS peut différer légèrement de l'aperçu PDF, compte tenu des contraintes techniques et de gestion des styles propres à ces logiciels.</span>
                            </div>
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-700 group-hover:underline">
                                Télécharger en Word (.docx)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </span>
                        </div>
                    </button>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-500">
                        Coût pour ce template :
                        <span class="font-bold text-primary-700" x-text="templateCosts[selectedTemplate] > 0 ? `${templateCosts[selectedTemplate]} crédits` : 'Gratuit'"></span>
                    </p>
                    <button type="button" @click="showDownloadFormatModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-600 hover:bg-gray-100 transition">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL : TÉLÉVERSER UN NOUVEAU CV -->
    <div x-show="showCvUploadModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="if (!isUploadingCv) showCvUploadModal = false">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs" @click="if (!isUploadingCv) showCvUploadModal = false"></div>
            <div class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl z-10 space-y-6">
                <!-- État formulaire (avant validation) -->
                <div x-show="!isUploadingCv" class="space-y-6">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <h3 class="text-xl font-bold text-gray-900">Évaluer un nouveau CV</h3>
                        <button @click="showCvUploadModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <p class="text-xs text-gray-500">Importez votre CV mis à jour pour comparer votre score, détecter les évolutions et obtenir de nouveaux conseils de recrutement.</p>

                    <form action="{{ route('jeune.cv.analyze') }}" method="POST" enctype="multipart/form-data" @submit="isUploadingCv = true" class="space-y-5">
                        @csrf
                        <div>
                            <label for="cv_file" class="block text-sm font-semibold text-gray-700 mb-2">Fichier CV (PDF, DOCX, JPG ou PNG - max 5 Mo)</label>
                            <input id="cv_file" type="file" name="cv_file" required accept=".pdf,.docx,.png,.jpg,.jpeg" class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100" />
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                            <button type="button" @click="showCvUploadModal = false" class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-700 font-semibold text-sm hover:bg-gray-50">Annuler</button>
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-600 text-white font-bold text-sm hover:bg-primary-700 transition shadow-sm flex items-center gap-2">
                                <span>Lancer l'analyse</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- État de chargement avec loader animé -->
                <div x-show="isUploadingCv" class="py-8 text-center space-y-4">
                    <div class="w-16 h-16 rounded-full bg-primary-50 text-primary-600 flex items-center justify-center mx-auto">
                        <svg class="animate-spin w-8 h-8 text-primary-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-lg font-bold text-gray-900">Analyse de votre CV en cours...</h4>
                        <p class="text-xs text-gray-500 max-w-sm mx-auto">Veuillez patienter pendant la lecture, la notation et la restructuration du document.</p>
                    </div>
                    <div class="pt-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                            Ne fermez pas cette page
                        </span>
                    </div>
                </div>
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
                    <div class="flex items-center gap-2">
                        <a :href="previewDownloadUrl" download
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-primary-50 text-primary-700 text-xs font-bold hover:bg-primary-100 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Télécharger</span>
                        </a>
                        <button @click="showPreviewModal = false" class="text-gray-400 hover:text-gray-600 p-1">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>
                <div class="w-full h-[70vh] bg-gray-50 rounded-2xl overflow-hidden flex items-center justify-center">
                    <template x-if="previewType.includes('pdf') || previewType.includes('word') || previewType.includes('officedocument') || previewFileName.toLowerCase().endsWith('.docx') || previewFileName.toLowerCase().endsWith('.doc')">
                        <iframe :src="previewUrl" class="w-full h-full border-0 rounded-xl" title="Aperçu du document"></iframe>
                    </template>
                    <template x-if="!(previewType.includes('pdf') || previewType.includes('word') || previewType.includes('officedocument') || previewFileName.toLowerCase().endsWith('.docx') || previewFileName.toLowerCase().endsWith('.doc'))">
                        <img :src="previewUrl" class="max-w-full max-h-full object-contain" alt="Aperçu du document" />
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

    <!-- Toast Notification -->
    <div x-show="showToast"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-5 py-3 rounded-2xl shadow-xl text-sm font-semibold border"
         :class="toastType === 'error' ? 'bg-red-50 border-red-200 text-red-800' : 'bg-emerald-50 border-emerald-200 text-emerald-800'"
         x-cloak>
        <span x-text="toastMessage"></span>
    </div>

</div>

@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
function cvPlaceholderItem(config) {
    return {
        cvId: config.cvId,
        fieldType: config.fieldType,
        expIndex: config.expIndex,
        bulletIndex: config.bulletIndex,
        originalTag: config.originalTag || '',
        currentValue: config.currentValue || '',
        isFilled: Boolean(config.isFilled),
        isEditing: false,
        isSaving: false,
        tempValue: config.currentValue || '',

        get placeholderText() {
            const clean = (this.originalTag || '').replace(/^\[(?:À compléter|Compléter|Insérer)\s*:\s*/i, '').replace(/\]$/, '').trim();
            return clean ? `Ex: ${clean}` : 'Saisir une valeur...';
        },

        get syncKey() {
            return `${this.cvId}-${this.fieldType}-${this.expIndex}-${this.bulletIndex}-${this.originalTag}`;
        },

        init() {
            window.addEventListener('cv-placeholder-synced', (e) => {
                if (e.detail && e.detail.key === this.syncKey) {
                    this.currentValue = e.detail.newValue;
                    this.isFilled = e.detail.isFilled;
                    this.tempValue = e.detail.newValue;
                    this.isEditing = false;
                }
            });
        },

        startEdit() {
            this.tempValue = this.currentValue || '';
            this.isEditing = true;
            this.$nextTick(() => {
                const el = this.$refs.inputField;
                if (el) {
                    el.focus();
                    el.select();
                }
            });
        },

        cancel() {
            this.tempValue = this.currentValue || '';
            this.isEditing = false;
        },

        async save() {
            if (this.isSaving) return;
            this.isSaving = true;
            const val = this.tempValue.trim();

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                const response = await fetch('{{ route('jeune.cv.update-placeholder') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        cv_id: this.cvId,
                        field_type: this.fieldType,
                        exp_index: this.expIndex,
                        bullet_index: this.bulletIndex,
                        original_tag: this.originalTag,
                        new_value: val
                    })
                });

                const data = await response.json();
                if (data.success) {
                    this.currentValue = data.new_value;
                    this.isFilled = data.is_filled;
                    this.isEditing = false;

                    window.dispatchEvent(new CustomEvent('cv-placeholder-synced', {
                        detail: {
                            key: this.syncKey,
                            newValue: this.currentValue,
                            isFilled: this.isFilled
                        }
                    }));

                    if (window.outilsAppInstance && typeof window.outilsAppInstance.showToastNotification === 'function') {
                        window.outilsAppInstance.showToastNotification(data.message, 'success');
                    }
                } else {
                    alert(data.message || 'Erreur lors de l\'enregistrement.');
                }
            } catch (err) {
                console.error('Erreur sauvegarde placeholder:', err);
                alert('Une erreur est survenue lors de la sauvegarde.');
            } finally {
                this.isSaving = false;
            }
        }
    };
}

function outilsApp(initialTab, initialCvId, initialTemplateCosts) {
    return {
        currentTab: initialTab || 'cv',
        activeCvId: initialCvId || null,
        cvViewMode: 'ats',
        selectedTemplate: 0,
        templateCosts: initialTemplateCosts || { 0: 0, 1: 1, 2: 2, 3: 3, 4: 4, 5: 5 },
        isProcessingCvAction: false,
        toastMessage: '',
        toastType: 'success',
        showToast: false,
        driveFilter: 'all',
        showUploadModal: false,
        showCvUploadModal: false,
        showDownloadFormatModal: false,
        showPreviewModal: false,
        showDeleteModal: false,
        isUploadingCv: false,
        previewUrl: '',
        previewDownloadUrl: '',
        previewType: '',
        previewFileName: '',
        documentToDelete: null,

        selectedTemplateCostText() {
            const cost = this.templateCosts[this.selectedTemplate] ?? 0;
            if (cost <= 0) {
                return 'Télécharger mon CV (Gratuit)';
            }
            return `Télécharger mon CV (${cost} ${cost > 1 ? 'crédits' : 'crédit'})`;
        },

        downloadButtonLabel() {
            const cost = this.templateCosts[this.selectedTemplate] ?? 0;
            if (cost <= 0) {
                return 'Télécharger mon CV (Gratuit)';
            }
            return `Télécharger mon CV (${cost} ${cost > 1 ? 'crédits' : 'crédit'})`;
        },

        init() {
            window.outilsAppInstance = this;
            this.$nextTick(() => {
                const cvArea = document.getElementById('cvEnhancedPrintArea');
                if (cvArea) {
                    const preventCopyHandler = (e) => {
                        // Autoriser les interactions normales avec les champs de formulaire d'édition
                        if (e.target && (e.target.closest('input, button, [contenteditable="true"]') || e.target.tagName === 'INPUT' || e.target.tagName === 'BUTTON')) {
                            return true;
                        }
                        e.preventDefault();
                        return false;
                    };
                    ['copy', 'cut', 'contextmenu', 'selectstart', 'dragstart'].forEach((evt) => {
                        cvArea.addEventListener(evt, preventCopyHandler);
                    });
                }
            });
        },

        showToastNotification(msg, type = 'success') {
            this.toastMessage = msg;
            this.toastType = type;
            this.showToast = true;
            setTimeout(() => {
                this.showToast = false;
            }, 4000);
        },

        async triggerCvAction(action) {
            if (!this.activeCvId || this.isProcessingCvAction) return;
            this.isProcessingCvAction = true;
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                const response = await fetch('{{ route('jeune.cv.action') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        action: action,
                        cv_id: this.activeCvId,
                        template: this.selectedTemplate
                    })
                });

                const data = await response.json();

                if (response.status === 402 || data.redirect_to_wallet) {
                    window.location.href = data.wallet_url || '{{ route('jeune.wallet.index') }}';
                    return;
                }

                if (!response.ok || !data.success) {
                    this.showToastNotification(data.message || 'Une erreur est survenue.', 'error');
                    return;
                }

                if (action === 'copy') {
                    if (navigator.clipboard && window.isSecureContext) {
                        await navigator.clipboard.writeText(data.cv_text);
                    } else {
                        const textArea = document.createElement('textarea');
                        textArea.value = data.cv_text;
                        textArea.style.position = 'fixed';
                        textArea.style.opacity = '0';
                        document.body.appendChild(textArea);
                        textArea.focus();
                        textArea.select();
                        document.execCommand('copy');
                        document.body.removeChild(textArea);
                    }
                    this.showToastNotification('Texte du CV copié dans le presse-papier !', 'success');
                } else if (action === 'download' || action === 'download_docx') {
                    if (data.download_url) {
                        window.location.href = data.download_url;
                        this.showToastNotification('Votre CV Word (.docx) est en cours de téléchargement !', 'success');
                    } else {
                        this.showToastNotification('Impossible de préparer le fichier Word.', 'error');
                    }
                } else if (action === 'download_pdf') {
                    this.showToastNotification('Préparation de votre CV en PDF...', 'success');
                    setTimeout(() => {
                        window.print();
                    }, 350);
                }
            } catch (err) {
                console.error('CV Action error:', err);
                this.showToastNotification('Une erreur inattendue est survenue.', 'error');
            } finally {
                this.isProcessingCvAction = false;
            }
        },

        setTab(tabName) {
            this.currentTab = tabName;
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tabName);
            window.history.pushState({}, '', url.toString());
        },

        previewDocument(id, mimeType, fileName) {
            this.previewUrl = `/espace-jeune/documents/${id}/view`;
            this.previewDownloadUrl = `/espace-jeune/documents/${id}/download`;
            this.previewType = (mimeType || '').toLowerCase();
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
@endpush

@push('styles')
<style>
#cvEnhancedPrintArea, #cvEnhancedPrintArea * {
    -webkit-user-select: none !important;
    -moz-user-select: none !important;
    -ms-user-select: none !important;
    user-select: none !important;
}

@media print {
    @page {
        size: A4;
        margin: 8mm;
    }
    html, body {
        background: #fff !important;
        margin: 0 !important;
        padding: 0 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    body * {
        visibility: hidden !important;
    }
    #cvEnhancedPrintArea, #cvEnhancedPrintArea * {
        visibility: visible !important;
        -webkit-user-select: text !important;
        user-select: text !important;
    }
    #cvEnhancedPrintArea .no-print, #cvEnhancedPrintArea .no-print * {
        display: none !important;
        visibility: hidden !important;
    }
    .cv-filled-text {
        text-decoration: none !important;
        font-weight: inherit !important;
    }
    #cvEnhancedPrintArea {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 8mm !important;
        border: none !important;
        box-shadow: none !important;
        background: white !important;
    }
}
</style>
@endpush
@endsection
