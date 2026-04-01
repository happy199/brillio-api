@extends('layouts.admin')

@section('title', 'Analytics')

@section('content')
<div class="space-y-6">
    <!-- Header avec filtres -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Analytics</h1>
                <p class="text-gray-600">Statistiques et métriques de la plateforme</p>
            </div>

            <!-- Filtres de date -->
            <form action="{{ route('admin.analytics.index') }}" method="GET" class="flex flex-wrap items-end gap-3">
                <!-- Préréglages -->
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Période</label>
                    <select name="preset" onchange="toggleCustomDates(this.value)"
                        class="rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="today" {{ ($dateRange['preset'] ?? '' )=='today' ? 'selected' : '' }}>Aujourd'hui
                        </option>
                        <option value="3days" {{ ($dateRange['preset'] ?? '' )=='3days' ? 'selected' : '' }}>3 derniers
                            jours</option>
                        <option value="week" {{ ($dateRange['preset'] ?? '' )=='week' ? 'selected' : '' }}>7 derniers
                            jours</option>
                        <option value="month" {{ ($dateRange['preset'] ?? 'month' )=='month' ? 'selected' : '' }}>30
                            derniers jours</option>
                        <option value="quarter" {{ ($dateRange['preset'] ?? '' )=='quarter' ? 'selected' : '' }}>3
                            derniers mois</option>
                        <option value="year" {{ ($dateRange['preset'] ?? '' )=='year' ? 'selected' : '' }}>Cette année
                        </option>
                        <option value="all" {{ ($dateRange['preset'] ?? '' )=='all' ? 'selected' : '' }}>Tout</option>
                        <option value="custom" {{ ($dateRange['preset'] ?? '' )=='custom' ? 'selected' : '' }}>
                            Personnalisé</option>
                    </select>
                </div>

                <!-- Dates personnalisées -->
                <div id="custom-dates"
                    class="{{ ($dateRange['preset'] ?? '') == 'custom' ? 'flex' : 'hidden' }} items-end gap-2">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Du</label>
                        <input type="date" name="start_date" value="{{ $dateRange['start']->format('Y-m-d') }}"
                            class="rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Au</label>
                        <input type="date" name="end_date" value="{{ $dateRange['end']->format('Y-m-d') }}"
                            class="rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <!-- Situation Multi-select -->
                <div class="space-y-1" x-data='{ 
                    open: false, 
                    selected: @json((array)request("situation", [])),
                    options: @json($allSituations),
                    toggle(val) {
                        const i = this.selected.indexOf(val);
                        if (i > -1) this.selected.splice(i, 1);
                        else this.selected.push(val);
                    },
                    get label() {
                        if (this.selected.length === 0) return "Toutes";
                        if (this.selected.length === 1) return this.options[this.selected[0]];
                        return this.selected.length + " sélectionnées";
                    }
                }' @click.away="open = false">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Situation</label>
                    <div class="relative">
                        <button type="button" @click="open = !open" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-left text-sm flex items-center justify-between hover:border-primary-400 transition shadow-sm">
                            <span x-text="label" class="truncate mr-2"></span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-transition class="absolute z-50 mt-2 w-64 bg-white border border-gray-200 rounded-2xl shadow-xl p-2 max-h-64 overflow-y-auto">
                            <template x-for="(label, value) in options" :key="value">
                                <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-xl cursor-pointer transition">
                                    <input type="checkbox" name="situation[]" :value="value" :checked="selected.includes(value)" @change="toggle(value)" class="rounded text-primary-600 focus:ring-primary-500 border-gray-300">
                                    <span class="text-sm text-gray-700" x-text="label"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Interet Multi-select -->
                <div class="space-y-1" x-data='{ 
                    open: false, 
                    selected: @json((array)request("interest", [])),
                    options: @json($allInterests),
                    toggle(val) {
                        const i = this.selected.indexOf(val);
                        if (i > -1) this.selected.splice(i, 1);
                        else this.selected.push(val);
                    },
                    get label() {
                        if (this.selected.length === 0) return "Tous";
                        if (this.selected.length === 1) return this.selected[0];
                        return this.selected.length + " sélectionnés";
                    }
                }' @click.away="open = false">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Centre d'intérêt</label>
                    <div class="relative">
                        <button type="button" @click="open = !open" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-left text-sm flex items-center justify-between hover:border-primary-400 transition shadow-sm">
                            <span x-text="label" class="truncate mr-2"></span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-transition class="absolute z-50 mt-2 w-64 bg-white border border-gray-200 rounded-2xl shadow-xl p-2 max-h-64 overflow-y-auto">
                            <template x-for="item in options" :key="item">
                                <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-xl cursor-pointer transition">
                                    <input type="checkbox" name="interest[]" :value="item" :checked="selected.includes(item)" @change="toggle(item)" class="rounded text-primary-600 focus:ring-primary-500 border-gray-300">
                                    <span class="text-sm text-gray-700" x-text="item"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>

                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
                    Filtrer
                </button>
                <a href="{{ route('admin.analytics.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 text-sm">
                    Reset
                </a>
            </form>
        </div>

        <!-- Boutons d'export -->
        <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t">
            <span class="text-sm text-gray-500 mr-2">Exporter en PDF :</span>
            <a href="{{ route('admin.analytics.export-pdf', array_merge(request()->query(), ['type' => 'general'])) }}"
                class="px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 text-sm font-medium">
                Rapport général
            </a>
            <a href="{{ route('admin.analytics.export-pdf', array_merge(request()->query(), ['type' => 'users'])) }}"
                class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 text-sm font-medium">
                Utilisateurs
            </a>
            <a href="{{ route('admin.analytics.export-pdf', array_merge(request()->query(), ['type' => 'personality'])) }}"
                class="px-3 py-1.5 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 text-sm font-medium">
                Personnalités
            </a>

            <div class="w-full md:w-auto h-px md:h-8 md:w-px bg-gray-200 my-2 md:my-0 md:mx-2"></div>

            <span class="text-sm text-gray-500 mr-2">Exporter en CSV (Large volume) :</span>
            <a href="{{ route('admin.analytics.export-csv', array_merge(request()->query(), ['type' => 'users'])) }}"
                class="px-3 py-1.5 bg-blue-50 text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-100 text-sm font-medium flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export Master CSV (Filtres appliqués)
            </a>
            <a href="{{ route('admin.analytics.export-csv', array_merge(request()->query(), ['type' => 'mentors'])) }}"
                class="px-3 py-1.5 bg-orange-50 text-orange-600 border border-orange-200 rounded-lg hover:bg-orange-100 text-sm font-medium">
                Mentors (CSV)
            </a>
        </div>

        <!-- Période actuelle -->
        <p class="text-sm text-gray-500 mt-3">
            Données du <strong>{{ $dateRange['start']->format('d/m/Y') }}</strong>
            au <strong>{{ $dateRange['end']->format('d/m/Y') }}</strong>
        </p>
    </div>

    <!-- Stats principales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Utilisateurs totaux</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_users']) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-green-600 font-medium">+{{ $stats['new_users_period'] }}</span>
                <span class="text-gray-500 ml-1">sur la période</span>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Tests complétés</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_tests']) }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-purple-600 font-medium">+{{ $stats['tests_period'] }}</span>
                <span class="text-gray-500 ml-1">sur la période ({{ $stats['test_completion_rate'] }}% taux)</span>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Messages chat</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_messages']) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-green-600 font-medium">+{{ $stats['messages_period'] }}</span>
                <span class="text-gray-500 ml-1">sur la période ({{ $stats['conversations_period'] }} conv.)</span>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Mentors actifs</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($stats['active_mentors']) }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-orange-600 font-medium">{{ $stats['youth_engagement']['mentorship_intent_rate'] }}%</span>
                <span class="text-gray-500 ml-1">veulent un mentor</span>
            </div>
        </div>
    </div>

    <!-- Graphique d'évolution -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Évolution sur la période</h3>
        <div class="h-64">
            <canvas id="evolutionChart"></canvas>
        </div>
    </div>

    <!-- NEW: Onboarding & Engagement Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chart Situation -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Répartition par Situation</h3>
            <div class="h-64">
                <canvas id="situationChart"></canvas>
            </div>
        </div>

        <!-- Chart Sources -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Canaux d'Acquisition</h3>
            <div class="h-64">
                <canvas id="sourceChart"></canvas>
            </div>
        </div>

        <!-- Chart Tuition -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Frais de Scolarité Annuel</h3>
            <div class="h-64">
                <canvas id="tuitionChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Heatmap Intérêts -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Top 10 Centres d'Intérêt</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
                @php
                    $interestLabels = [
                        'tech' => 'Technologie',
                        'design' => 'Design',
                        'business' => 'Business',
                        'marketing' => 'Marketing',
                        'communication' => 'Communication',
                        'science' => 'Sciences',
                        'arts' => 'Arts',
                        'health' => 'Santé',
                        'law' => 'Droit',
                        'finance' => 'Finance',
                        'education' => 'Education',
                        'autre' => 'Autre'
                    ];
                    $totalInterests = array_sum($stats['youth_engagement']['interests']) ?: 1;
                @endphp
                @forelse($stats['youth_engagement']['interests'] as $interest => $count)
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-600 w-24 truncate">{{ $interestLabels[$interest] ?? $interest }}</span>
                    <div class="flex-1 bg-gray-100 rounded-full h-2">
                        <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ ($count / $totalInterests) * 100 }}%"></div>
                    </div>
                    <span class="text-xs text-gray-400 w-8 text-right font-medium">{{ $count }}</span>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">Pas encore de données</p>
                @endforelse
            </div>
        </div>

        <!-- Objectifs / Motivations -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Objectifs à l'inscription</h3>
            <div class="space-y-3">
                @php
                    $goalLabels = [
                        'mentor' => 'Trouver un mentor',
                        'orientation' => 'Orientation scolaire',
                        'personnalite' => 'Test de personnalité',
                        'ia' => 'Conseiller IA',
                        'documents' => 'Gestion de documents',
                        'non_renseigne' => 'Non spécifié'
                    ];
                @endphp
                @foreach($stats['youth_engagement']['goals'] as $goal => $count)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">{{ $goalLabels[$goal] ?? $goal }}</span>
                    <span class="text-sm font-semibold px-2 py-0.5 bg-gray-100 rounded text-gray-700">{{ $count }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Distribution des types de personnalité -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Distribution des personnalités (période)</h3>
            <div class="space-y-3">
                @forelse($stats['personality_distribution'] as $type => $count)
                <div class="flex items-center gap-3">
                    <div class="w-16 text-sm font-medium text-gray-700">{{ $type }}</div>
                    <div class="flex-1 bg-gray-100 rounded-full h-4">
                        <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-4 rounded-full"
                            style="width: {{ $stats['tests_period'] > 0 ? ($count / $stats['tests_period'] * 100) : 0 }}%">
                        </div>
                    </div>
                    <div class="w-12 text-sm text-gray-500 text-right">{{ $count }}</div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">Aucune donnée sur cette période</p>
                @endforelse
            </div>
        </div>

        <!-- Distribution par pays -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Utilisateurs par pays (période)</h3>
            <div class="space-y-3">
                @forelse($stats['users_by_country'] as $country)
                <div class="flex items-center gap-3">
                    <div class="w-24 text-sm text-gray-700 truncate">{{ $country->country ?? 'Non renseigné' }}</div>
                    <div class="flex-1 bg-gray-100 rounded-full h-4">
                        <div class="bg-gradient-to-r from-green-500 to-teal-500 h-4 rounded-full"
                            style="width: {{ $stats['new_users_period'] > 0 ? ($country->total / $stats['new_users_period'] * 100) : 0 }}%">
                        </div>
                    </div>
                    <div class="w-12 text-sm text-gray-500 text-right">{{ $country->total }}</div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">Aucune donnée sur cette période</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Spécialisations des mentors -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Spécialisations des mentors</h3>
            <div class="space-y-3">
                @forelse($stats['mentors_by_specialization'] as $spec)
                <div class="flex items-center gap-3">
                    <div class="w-32 text-sm text-gray-700 truncate">
                        {{ $specializations[$spec->specialization] ?? $spec->specialization ?? 'Non défini' }}</div>
                    <div class="flex-1 bg-gray-100 rounded-full h-4">
                        <div class="bg-gradient-to-r from-orange-500 to-red-500 h-4 rounded-full"
                            style="width: {{ $stats['active_mentors'] > 0 ? ($spec->total / $stats['active_mentors'] * 100) : 0 }}%">
                        </div>
                    </div>
                    <div class="w-12 text-sm text-gray-500 text-right">{{ $spec->total }}</div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">Aucune donnée disponible</p>
                @endforelse
            </div>
        </div>

        <!-- Activité récente -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Inscriptions récentes (période)</h3>
            <div class="space-y-4 max-h-80 overflow-y-auto">
                @forelse($stats['recent_signups'] as $user)
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-full flex items-center justify-center {{ $user->user_type === 'mentor' ? 'bg-orange-100' : 'bg-blue-100' }}">
                        <span
                            class="{{ $user->user_type === 'mentor' ? 'text-orange-600' : 'text-blue-600' }} font-semibold">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $user->name }}</p>
                        <p class="text-xs text-gray-500">{{ $user->email }}</p>
                    </div>
                    <div class="text-right">
                        <span
                            class="text-xs px-2 py-1 rounded-full {{ $user->user_type === 'mentor' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ $user->user_type === 'mentor' ? 'Mentor' : 'Jeune' }}
                        </span>
                        <p class="text-xs text-gray-400 mt-1">{{ $user->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">Aucune inscription sur cette période</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Documents -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Documents académiques (période)</h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <p class="text-3xl font-bold text-gray-600">{{ $stats['documents']['total'] ?? 0 }}</p>
                <p class="text-sm text-gray-600 mt-1">Total (tous)</p>
            </div>
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <p class="text-3xl font-bold text-blue-600">{{ $stats['documents']['period'] ?? 0 }}</p>
                <p class="text-sm text-gray-600 mt-1">Sur la période</p>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <p class="text-3xl font-bold text-green-600">{{ $stats['documents']['bulletin'] ?? 0 }}</p>
                <p class="text-sm text-gray-600 mt-1">Bulletins</p>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <p class="text-3xl font-bold text-purple-600">{{ $stats['documents']['releve_notes'] ?? 0 }}</p>
                <p class="text-sm text-gray-600 mt-1">Relevés</p>
            </div>
            <div class="text-center p-4 bg-orange-50 rounded-lg">
                <p class="text-3xl font-bold text-orange-600">{{ $stats['documents']['diplome'] ?? 0 }}</p>
                <p class="text-sm text-gray-600 mt-1">Diplômes</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}" src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    function toggleCustomDates(value) {
        const customDates = document.getElementById('custom-dates');
        const startDateInput = document.querySelector('input[name="start_date"]');
        const endDateInput = document.querySelector('input[name="end_date"]');

        if (value === 'custom') {
            customDates.classList.remove('hidden');
            customDates.classList.add('flex');
        } else {
            customDates.classList.add('hidden');
            customDates.classList.remove('flex');

            // Calculer et remplir les dates pour les presets
            const today = new Date();
            const endDate = new Date(today);
            let startDate = new Date(today);

            switch (value) {
                case 'today':
                    startDate = new Date(today);
                    break;
                case '3days':
                    startDate.setDate(today.getDate() - 3);
                    break;
                case 'week':
                    startDate.setDate(today.getDate() - 7);
                    break;
                case 'month':
                    startDate.setDate(today.getDate() - 30);
                    break;
                case 'quarter':
                    startDate.setMonth(today.getMonth() - 3);
                    break;
                case 'year':
                    startDate.setFullYear(today.getFullYear() - 1);
                    break;
                case 'all':
                    startDate = new Date('2020-01-01');
                    break;
            }

            // Formater en YYYY-MM-DD
            const formatDate = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };

            startDateInput.value = formatDate(startDate);
            endDateInput.value = formatDate(endDate);
        }
    }

    // Graphique d'évolution
    const ctx = document.getElementById('evolutionChart').getContext('2d');
    const dailySignups = @json($stats['daily_signups']);
    const dailyTests = @json($stats['daily_tests']);
    const dailyMessages = @json($stats['daily_messages']);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: Object.keys(dailySignups),
            datasets: [
                {
                    label: 'Inscriptions',
                    data: Object.values(dailySignups),
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Tests MBTI',
                    data: Object.values(dailyTests),
                    borderColor: '#8B5CF6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Messages',
                    data: Object.values(dailyMessages),
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                x: {
                    display: true,
                    grid: {
                        display: false
                    },
                    ticks: {
                        maxTicksLimit: 10
                    }
                },
                y: {
                    display: true,
                    beginAtZero: true,
                    grid: {
                        color: '#f3f4f6'
                    }
                }
            }
        }
    });

    // Chart Tuition
    const tuiCtx = document.getElementById('tuitionChart').getContext('2d');
    const tuitions = @json($stats['youth_engagement']['tuition_ranges']);
    const tuiLabels = {
        'under_200': '- 200k',
        '200_500': '200k-500k',
        '500_1m': '500k-1M',
        '1m_2m': '1M-2M',
        'over_2m': '+ 2M',
        'non_renseigne': 'N/C'
    };

    const tuiData = ['under_200', '200_500', '500_1m', '1m_2m', 'over_2m', 'non_renseigne'];

    new Chart(tuiCtx, {
        type: 'bar',
        data: {
            labels: tuiData.map(k => tuiLabels[k]),
            datasets: [{
                label: 'Nombre de jeunes',
                data: tuiData.map(k => tuitions[k] || 0),
                backgroundColor: ['#3B82F6', '#6366F1', '#8B5CF6', '#EC4899', '#F97316', '#94A3B8'],
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { color: '#f3f4f6' },
                    ticks: { 
                        stepSize: 1,
                        precision: 0
                    }
                },
                x: { grid: { display: false } }
            }
        }
    });

    // Chart Situation
    const sitCtx = document.getElementById('situationChart').getContext('2d');
    const situations = @json($stats['youth_engagement']['situations']);
    const sitLabels = {
        'college': 'Collège',
        'lycee': 'Lycée',
        'etudiant': 'Université',
        'recherche_emploi': 'En recherche',
        'emploi': 'En poste',
        'entrepreneur': 'Entrepreneur',
        'non_renseigne': 'N/C'
    };

    new Chart(sitCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(situations).map(k => sitLabels[k] || k),
            datasets: [{
                data: Object.values(situations),
                backgroundColor: ['#3B82F6', '#8B5CF6', '#10B981', '#F59E0B', '#EF4444', '#6B7280', '#9CA3AF']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 10, font: { size: 10 } } } }
        }
    });

    // Chart Sources
    const srcCtx = document.getElementById('sourceChart').getContext('2d');
    const sources = @json($stats['youth_engagement']['sources']);
    const srcLabels = {
        'social_media': 'Réseaux Sociaux',
        'friend': 'Ami',
        'school': 'École',
        'search': 'Google',
        'event': 'Event',
        'other': 'Autre',
        'non_renseigne': 'N/C'
    };

    new Chart(srcCtx, {
        type: 'pie',
        data: {
            labels: Object.keys(sources).map(k => srcLabels[k] || k),
            datasets: [{
                data: Object.values(sources),
                backgroundColor: ['#6366F1', '#EC4899', '#FACC15', '#14B8A6', '#F97316', '#94A3B8', '#D1D5DB']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 10, font: { size: 10 } } } }
        }
    });
</script>
@endpush
@endsection