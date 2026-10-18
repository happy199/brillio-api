@extends('layouts.admin')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto" x-data="{ currentTab: '{{ request('tab', 'automations') }}' }">

    <!-- En-tête de la page -->
    <div class="sm:flex sm:justify-between sm:items-center mb-6">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-indigo-600 text-white flex items-center justify-center shadow-md shadow-indigo-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Automatisations & Tâches Planifiées</h1>
                    <p class="text-sm text-slate-500 mt-0.5">Suivi en clair, contrôle en direct et historique d'exécution de toutes les automatisations de la plateforme.</p>
                </div>
            </div>
        </div>

        <!-- Bouton vers la gestion des exclusions SMTP -->
        <div class="mt-4 sm:mt-0 flex gap-2">
            <a href="{{ route('admin.audits.suppressions') }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 shadow-sm transition-colors">
                <svg class="w-4 h-4 mr-2 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
                Exclusions & Rejets d'e-mails
            </a>
        </div>
    </div>

    <!-- Messages flash -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-start gap-3 shadow-sm">
            <svg class="w-5 h-5 text-emerald-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="text-sm font-medium">{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl flex items-start gap-3 shadow-sm">
            <svg class="w-5 h-5 text-rose-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="text-sm font-medium">{{ session('error') }}</div>
        </div>
    @endif

    <!-- Cartes d'indicateurs de santé -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-semibold uppercase text-slate-400 tracking-wider">Automatisations</div>
                    <div class="text-2xl font-bold text-slate-800 mt-1">{{ $totalCount }}</div>
                    <div class="text-xs text-slate-500 mt-0.5">Processus configurés</div>
                </div>
                <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-semibold uppercase text-emerald-600 tracking-wider">En cours (Actives)</div>
                    <div class="text-2xl font-bold text-emerald-700 mt-1">{{ $activeCount }}</div>
                    <div class="text-xs text-emerald-600/80 mt-0.5">Prêtes à s'exécuter</div>
                </div>
                <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-semibold uppercase text-amber-600 tracking-wider">Mises en pause</div>
                    <div class="text-2xl font-bold text-amber-700 mt-1">{{ $pausedCount }}</div>
                    <div class="text-xs text-amber-600/80 mt-0.5">Envois stoppés</div>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-semibold uppercase text-slate-400 tracking-wider">Protection Serveur</div>
                    <div class="text-base font-bold text-indigo-700 mt-1 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Active
                    </div>
                    <div class="text-xs text-slate-500 mt-0.5">Antispam & Anti-bounces</div>
                </div>
                <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation par Onglets -->
    <div class="border-b border-slate-200 mb-6">
        <nav class="-mb-px flex space-x-6" aria-label="Tabs">
            <button type="button"
                @click="currentTab = 'automations'"
                :class="currentTab === 'automations' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                class="whitespace-nowrap py-3 px-1 border-b-2 font-semibold text-sm flex items-center gap-2 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                Automatisations & Contrôle ({{ $automations->count() }})
            </button>

            <button type="button"
                @click="currentTab = 'logs'"
                :class="currentTab === 'logs' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                class="whitespace-nowrap py-3 px-1 border-b-2 font-semibold text-sm flex items-center gap-2 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Historique & Logs techniques
            </button>
        </nav>
    </div>

    <!-- ============================================================== -->
    <!-- ONGLET 1 : LISTE CLAIRE DES AUTOMATISATIONS                    -->
    <!-- ============================================================== -->
    <div x-show="currentTab === 'automations'" class="space-y-6">

        <!-- Barre de recherche & filtres clairs -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <form method="GET" action="{{ route('admin.audits.crons') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                <input type="hidden" name="tab" value="automations">

                <!-- Recherche textuelle -->
                <div class="sm:col-span-5">
                    <label for="cron_search" class="sr-only">Rechercher une automatisation</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input id="cron_search" type="text" name="search" placeholder="Rechercher une automatisation ou une cible..." value="{{ request('search') }}"
                            class="w-full pl-9 pr-3 py-2 form-input bg-slate-50 border-slate-200 focus:bg-white focus:border-indigo-500 rounded-lg text-sm">
                    </div>
                </div>

                <!-- Filtre par catégorie -->
                <div class="sm:col-span-3">
                    <label for="cron_category" class="sr-only">Catégorie</label>
                    <select id="cron_category" name="category" class="w-full form-select bg-slate-50 border-slate-200 focus:bg-white focus:border-indigo-500 rounded-lg text-sm">
                        <option value="all">Toutes les catégories</option>
                        <option value="emails" {{ request('category') == 'emails' ? 'selected' : '' }}>✉️ E-mails & Relances</option>
                        <option value="mentorship" {{ request('category') == 'mentorship' ? 'selected' : '' }}>🤝 Mentorat & Séances</option>
                        <option value="security" {{ request('category') == 'security' ? 'selected' : '' }}>🛡️ Sécurité & Inscriptions</option>
                        <option value="system" {{ request('category') == 'system' ? 'selected' : '' }}>⚙️ Système & Données</option>
                        <option value="organizations" {{ request('category') == 'organizations' ? 'selected' : '' }}>🏢 Entreprises & Écoles</option>
                    </select>
                </div>

                <!-- Filtre par statut -->
                <div class="sm:col-span-2">
                    <label for="cron_status" class="sr-only">Statut</label>
                    <select id="cron_status" name="status" class="w-full form-select bg-slate-50 border-slate-200 focus:bg-white focus:border-indigo-500 rounded-lg text-sm">
                        <option value="all">Tous les statuts</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>🟢 Actives</option>
                        <option value="paused" {{ request('status') == 'paused' ? 'selected' : '' }}>⏸️ En pause</option>
                    </select>
                </div>

                <!-- Boutons d'action -->
                <div class="sm:col-span-2 flex gap-2">
                    <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-semibold transition-colors shadow-sm">
                        Filtrer
                    </button>
                    @if(request('search') || (request('category') && request('category') !== 'all') || (request('status') && request('status') !== 'all'))
                        <a href="{{ route('admin.audits.crons', ['tab' => 'automations']) }}" class="px-3 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 text-sm font-medium transition-colors" title="Réinitialiser">
                            ✕
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Liste des automatisations sous forme de cartes claires -->
        <div class="grid grid-cols-1 gap-4">
            @forelse($automations as $auto)
            <div class="bg-white rounded-xl border {{ $auto->is_enabled ? 'border-slate-200 shadow-sm hover:border-slate-300' : 'border-amber-200 bg-amber-50/20 shadow-none' }} p-5 transition-all">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

                    <!-- Colonne Principale : Nom, Catégorie, Description, Cible -->
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <!-- Badge Statut -->
                            @if($auto->is_enabled)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Active & Programmée
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    En pause (Désactivée)
                                </span>
                            @endif

                            <!-- Badge Catégorie -->
                            @php
                                $categoryBadges = [
                                    'emails' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'border' => 'border-indigo-100', 'label' => '✉️ E-mails & Relances'],
                                    'mentorship' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-700', 'border' => 'border-sky-100', 'label' => '🤝 Mentorat & Séances'],
                                    'security' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-100', 'label' => '🛡️ Sécurité & Inscriptions'],
                                    'system' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'border' => 'border-slate-200', 'label' => '⚙️ Système & Données'],
                                    'organizations' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-100', 'label' => '🏢 Entreprises & Écoles'],
                                ];
                                $catInfo = $categoryBadges[$auto->category] ?? ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'border' => 'border-slate-200', 'label' => $auto->category];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $catInfo['bg'] }} {{ $catInfo['text'] }} border {{ $catInfo['border'] }}">
                                {{ $catInfo['label'] }}
                            </span>

                            <!-- Fréquence d'exécution -->
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $auto->frequency_label }}
                            </span>
                        </div>

                        <!-- Titre clair de l'automatisation -->
                        <h2 class="text-lg font-bold text-slate-800 tracking-tight">
                            {{ $auto->name }}
                        </h2>

                        <!-- Description en français sans jargon -->
                        <p class="text-sm text-slate-600 mt-1 leading-relaxed">
                            {{ $auto->description }}
                        </p>

                        <!-- Métadonnées : Cible & Dernière exécution -->
                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                            <!-- Cible visée -->
                            <div class="flex items-start gap-1.5 text-slate-600 bg-slate-50 px-2.5 py-1.5 rounded-lg border border-slate-100">
                                <svg class="w-4 h-4 text-indigo-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <div>
                                    <span class="font-semibold text-slate-700">Public cible :</span>
                                    {{ $auto->target }}
                                </div>
                            </div>

                            <!-- Dernière exécution -->
                            <div class="flex items-start gap-1.5 text-slate-600 bg-slate-50 px-2.5 py-1.5 rounded-lg border border-slate-100">
                                <svg class="w-4 h-4 text-slate-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <div>
                                    <span class="font-semibold text-slate-700">Dernier passage :</span>
                                    @if($auto->last_run_at)
                                        <span class="text-slate-800 font-medium">{{ $auto->last_run_at->diffForHumans() }}</span>
                                        @if($auto->last_run_status === 'success')
                                            <span class="inline-flex items-center text-emerald-700 font-medium ml-1">✓ Succès ({{ number_format($auto->last_run_duration, 2) }}s)</span>
                                        @elseif($auto->last_run_status === 'failed')
                                            <span class="inline-flex items-center text-rose-700 font-medium ml-1">✕ Échec</span>
                                        @endif
                                    @else
                                        <span class="text-slate-400 italic">En attente de premier déclenchement</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Colonne Boutons d'Action (Arrêter / Relancer + Exécuter immédiatement) -->
                    <div class="flex sm:flex-row lg:flex-col items-stretch gap-2.5 pt-3 lg:pt-0 lg:border-l lg:border-slate-100 lg:pl-5 min-w-[200px]">

                        <!-- Bouton Activer / Désactiver -->
                        <form action="{{ route('admin.audits.crons.toggle', $auto->key) }}" method="POST" class="w-full">
                            @csrf
                            @if($auto->is_enabled)
                                <button type="submit"
                                    onclick="return confirm('Mettre en pause l\'automatisation « {{ addslashes($auto->name) }} » ? Elle ne s\'exécutera plus automatiquement jusqu\'à réactivation.');"
                                    class="w-full inline-flex items-center justify-center px-3.5 py-2 border border-amber-300 text-xs font-semibold rounded-lg text-amber-800 bg-amber-50 hover:bg-amber-100 hover:border-amber-400 transition-colors shadow-sm">
                                    <svg class="w-4 h-4 mr-1.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Mettre en pause
                                </button>
                            @else
                                <button type="submit"
                                    class="w-full inline-flex items-center justify-center px-3.5 py-2 border border-emerald-300 text-xs font-semibold rounded-lg text-emerald-800 bg-emerald-50 hover:bg-emerald-100 hover:border-emerald-400 transition-colors shadow-sm">
                                    <svg class="w-4 h-4 mr-1.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Relancer l'automatisation
                                </button>
                            @endif
                        </form>

                        <!-- Bouton Exécuter maintenant -->
                        <form action="{{ route('admin.audits.crons.run', $auto->key) }}" method="POST" class="w-full">
                            @csrf
                            <button type="submit"
                                onclick="return confirm('Déclencher immédiatement l\'exécution de « {{ addslashes($auto->name) }} » ?');"
                                class="w-full inline-flex items-center justify-center px-3.5 py-2 border border-slate-200 text-xs font-semibold rounded-lg text-slate-700 bg-white hover:bg-slate-50 hover:text-indigo-600 hover:border-indigo-300 transition-colors shadow-sm">
                                <svg class="w-4 h-4 mr-1.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                Exécuter maintenant
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl border border-slate-200 p-12 text-center text-slate-500">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <div class="font-semibold text-slate-700">Aucune automatisation trouvée</div>
                <p class="text-sm text-slate-400 mt-1">Aucune tâche ne correspond à vos filtres actuels.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- ONGLET 2 : HISTORIQUE TECHNIQUE & LOGS SYSTÈME                 -->
    <!-- ============================================================== -->
    <div x-show="currentTab === 'logs'" style="display: none;" class="space-y-6">

        <!-- Filtres des logs -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <form method="GET" action="{{ route('admin.audits.crons') }}" class="flex flex-col sm:flex-row gap-4">
                <input type="hidden" name="tab" value="logs">

                <div class="flex-grow">
                    <label for="log_search" class="sr-only">Rechercher par commande</label>
                    <input id="log_search" type="text" name="log_search" placeholder="Rechercher une commande brute (ex: messages:send-unread-reminders)..." value="{{ request('log_search') }}" class="w-full form-input bg-slate-50 border-slate-200 focus:bg-white focus:border-indigo-500 rounded-lg text-sm">
                </div>
                <div class="w-full sm:w-48">
                    <label for="log_status" class="sr-only">Statut du log</label>
                    <select id="log_status" name="log_status" class="w-full form-select bg-slate-50 border-slate-200 focus:bg-white focus:border-indigo-500 rounded-lg text-sm">
                        <option value="all">Tous les statuts</option>
                        <option value="success" {{ request('log_status') == 'success' ? 'selected' : '' }}>Succès</option>
                        <option value="failed" {{ request('log_status') == 'failed' ? 'selected' : '' }}>Échec</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-semibold flex-shrink-0 transition-colors shadow-sm">
                    Filtrer
                </button>
                @if(request('log_search') || (request('log_status') && request('log_status') !== 'all'))
                    <a href="{{ route('admin.audits.crons', ['tab' => 'logs']) }}" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 text-sm font-medium flex-shrink-0 transition-colors">Effacer</a>
                @endif
            </form>
        </div>

        <!-- Tableau des logs -->
        <div class="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-auto w-full">
                    <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 whitespace-nowrap"><div class="font-semibold text-left">Date d'Exécution</div></th>
                            <th class="px-4 py-3 whitespace-nowrap"><div class="font-semibold text-left">Commande Système</div></th>
                            <th class="px-4 py-3 whitespace-nowrap"><div class="font-semibold text-center">Durée</div></th>
                            <th class="px-4 py-3 whitespace-nowrap"><div class="font-semibold text-center">Statut</div></th>
                            <th class="px-4 py-3 whitespace-nowrap"><div class="font-semibold text-center">Logs Sortie</div></th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-200">
                        @forelse($logs as $log)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-left font-medium text-slate-800">{{ $log->run_at->format('d/m/Y H:i:s') }}</div>
                                <div class="text-xs text-slate-400">{{ $log->run_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-left font-mono font-medium text-slate-800 text-xs bg-slate-100 px-2 py-1 rounded inline-block">{{ $log->command }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <div class="font-medium text-slate-700">{{ number_format($log->duration, 2) }} s</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                @if($log->status === 'success')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        Succès
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                        Échec
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center" x-data="{ showModal: false }">
                                @if($log->output || $log->status === 'failed')
                                    <button type="button" class="inline-flex items-center px-3 py-1 border border-slate-200 text-xs font-medium rounded-lg text-slate-600 bg-white hover:bg-slate-50 transition-all shadow-sm" @click="showModal = true">
                                        <svg class="w-3.5 h-3.5 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Consulter
                                    </button>

                                    <!-- Modal d'affichage des logs -->
                                    <div x-show="showModal" x-cloak style="display: none;" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
                                        <div @click.outside="showModal = false" class="bg-white rounded-xl shadow-2xl overflow-hidden w-full max-w-4xl max-h-[90vh] flex flex-col border border-slate-200">
                                            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-2.5 h-2.5 rounded-full {{ $log->status === 'success' ? 'bg-emerald-500' : 'bg-rose-500' }}"></div>
                                                    <div class="font-bold text-slate-800 text-sm">Sortie console : {{ $log->command }}</div>
                                                </div>
                                                <button class="w-8 h-8 flex items-center justify-center rounded-full text-slate-400 hover:bg-slate-200 transition-colors" @click="showModal = false">&times;</button>
                                            </div>
                                            <div class="px-6 py-6 overflow-y-auto flex-1 bg-slate-950 font-mono text-xs leading-relaxed text-slate-300 whitespace-pre-wrap">
                                                <span class="text-slate-500 select-none">[{{ $log->run_at->format('Y-m-d H:i:s') }}]</span> <span class="text-indigo-400">STARTING</span> {{ $log->command }}...
                                                <br>
                                                @if($log->output)
                                                    {{ $log->output }}
                                                @elseif($log->status === 'failed')
                                                    <span class="text-rose-500">ERREUR: La commande s'est terminée avec une exception sans sortie texte standard.</span>
                                                @endif
                                                <br>
                                                <span class="text-slate-500 select-none">[{{ now()->format('Y-m-d H:i:s') }}]</span> <span class="{{ $log->status === 'success' ? 'text-emerald-400' : 'text-rose-400' }}">FINISHED</span> ({{ number_format($log->duration, 2) }}s)
                                            </div>
                                            <div class="px-6 py-3 border-t border-slate-100 flex justify-end bg-slate-50">
                                                <button class="px-4 py-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-bold transition-all shadow-sm" @click="showModal = false">Fermer</button>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic">Aucune sortie</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">Aucune trace d'exécution enregistrée.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="px-4 py-3 border-t border-slate-200">
                    {{ $logs->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
