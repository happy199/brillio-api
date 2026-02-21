@extends('layouts.organization')

@section('title', 'Tableau de bord')

@section('content')
<div class="space-y-8">
    <!-- Welcome Header -->
    <div class="bg-gradient-to-r from-organization-500 to-organization-600 rounded-xl p-8 text-white">
        <h1 class="text-3xl font-bold">{{ $organization->name }}</h1>
        <p class="mt-2 text-organization-100">Bienvenue dans votre espace partenaire</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
        <!-- Total Invited -->
        <div class="bg-white overflow-hidden rounded-lg shadow">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-organization-100 rounded-md p-3">
                        <svg class="h-6 w-6 text-organization-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Invitations envoyées</dt>
                            <dd class="text-3xl font-semibold text-gray-900">{{ $stats['total_invited'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Registered -->
        <div class="bg-white overflow-hidden rounded-lg shadow">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-organization-100 rounded-md p-3">
                        <svg class="h-6 w-6 text-organization-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Jeunes inscrits</dt>
                            <dd class="text-3xl font-semibold text-gray-900">{{ $stats['total_registered'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Jeunes suivis par un mentor (Avec Mentor) -->
        <div class="bg-white overflow-hidden rounded-lg shadow">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Avec Mentor</dt>
                            <dd class="text-3xl font-semibold text-gray-900">{{ $stats['users_with_mentors'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sessions Réalisées -->
        <div class="bg-white overflow-hidden rounded-lg shadow">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-indigo-100 rounded-md p-3">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Sessions réalisées</dt>
                            <dd class="text-3xl font-semibold text-gray-900">{{ $stats['mentoring_sessions_count'] }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Users (last 30 days) -->
        <div class="bg-white overflow-hidden rounded-lg shadow">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Utilisateurs actifs</dt>
                            <dd class="text-3xl font-semibold text-gray-900">{{ $stats['active_users'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personality Tests Stats -->
        <div class="bg-white overflow-hidden rounded-lg shadow sm:col-span-1 border border-purple-100">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dt class="text-sm font-medium text-gray-500 truncate">Tests de personnalité</dt>
                        <dd class="text-3xl font-semibold text-gray-900">{{ $stats['personality_tests_completed'] }}
                        </dd>
                    </div>
                </div>

                @if(isset($stats['top_personalities']) && count($stats['top_personalities']) > 0)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Top Profils</h4>
                    <div class="space-y-2">
                        @foreach($stats['top_personalities'] as $type)
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-medium text-gray-700">{{ $type->personality_type }}</span>
                            <span class="text-gray-500">{{ $type->count }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Documents & Onboarding (Platform Engagement) -->
        <div class="bg-white overflow-hidden rounded-lg shadow sm:col-span-2 lg:col-span-3">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0 bg-organization-100 rounded-md p-3">
                        <svg class="h-6 w-6 text-organization-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dt class="text-sm font-medium text-gray-500 truncate">Documents partagés</dt>
                        <dd class="text-3xl font-semibold text-gray-900">{{ $stats['documents_count'] }}</dd>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500 font-medium">Profils complétés à 100% (Onboarding)</span>
                        <span class="font-bold text-gray-900">{{ $stats['onboarding_completed_count'] }}</span>
                    </div>
                    @if($stats['total_registered'] > 0)
                    <div class="mt-2 w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-organization-500 h-1.5 rounded-full transition-all duration-1000"
                            style="width: {{ ($stats['onboarding_completed_count'] / $stats['total_registered']) * 100 }}%">
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-400">
                        {{ $stats['onboarding_completed_count'] }} sur {{ $stats['total_registered'] }} jeunes ont un
                        profil complet.
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions rapides</h2>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <a href="{{ route('organization.invitations.create') }}"
            class="flex items-center justify-center px-4 py-3 border border-organization-300 rounded-md shadow-sm text-sm font-medium text-organization-700 bg-white hover:bg-organization-50 transition-colors">
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Créer une invitation
        </a>
        <a href="{{ route('organization.users.index') }}"
            class="flex items-center justify-center px-4 py-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Voir les jeunes
        </a>
        <a href="{{ route('organization.exports.index') }}"
            class="flex items-center justify-center px-4 py-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Exporter un rapport
        </a>
    </div>
</div>


</div>
<!-- Activity Chart -->
<div class="mt-8 bg-white rounded-lg shadow p-6 relative overflow-hidden">
    <div class="mb-6 bg-gray-50 rounded-xl p-4 border border-gray-100">
        <form action="{{ route('organization.dashboard') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label for="period"
                    class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Période</label>
                <select name="period" id="period" onchange="toggleCustomDates(this.value)"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-organization-500 focus:ring-organization-500 text-sm">
                    <option value="7_days" {{ $period=='7_days' ? 'selected' : '' }}>7 derniers jours</option>
                    <option value="30_days" {{ $period=='30_days' ? 'selected' : '' }}>30 derniers jours</option>
                    <option value="this_month" {{ $period=='this_month' ? 'selected' : '' }}>Ce mois-ci</option>
                    <option value="last_month" {{ $period=='last_month' ? 'selected' : '' }}>Mois dernier</option>
                    <option value="this_year" {{ $period=='this_year' ? 'selected' : '' }}>Cette année</option>
                    <option value="custom" {{ $period=='custom' ? 'selected' : '' }}>Personnalisé</option>
                </select>
            </div>

            <div id="custom-dates" class="flex gap-4 {{ $period != 'custom' ? 'hidden' : '' }}">
                <div>
                    <label for="start_date"
                        class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Début</label>
                    <input type="date" name="start_date" id="start_date" value="{{ $startDate->format('Y-m-d') }}"
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-organization-500 focus:ring-organization-500 text-sm">
                </div>
                <div>
                    <label for="end_date"
                        class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Fin</label>
                    <input type="date" name="end_date" id="end_date" value="{{ $endDate->format('Y-m-d') }}"
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-organization-500 focus:ring-organization-500 text-sm">
                </div>
            </div>

            <button type="submit"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-organization-600 hover:bg-organization-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-organization-500 transition-colors">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filtrer
            </button>
        </form>
    </div>

    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900">
            @if($period == '7_days') Activité sur les 7 derniers jours
            @elseif($period == '30_days') Activité sur les 30 derniers jours
            @elseif($period == 'this_month') Activité de ce mois-ci
            @elseif($period == 'last_month') Activité du mois dernier
            @elseif($period == 'this_year') Activité de cette année
            @else Activité du {{ $startDate->format('d/m/Y') }} au {{ $endDate->format('d/m/Y') }}
            @endif
        </h3>
        @if(!$isPro)
        <span class="inline-flex items-center rounded-full bg-pink-100 px-2.5 py-0.5 text-xs font-medium text-pink-800">
            <svg class="mr-1.5 h-3 w-3 text-pink-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                    clip-rule="evenodd" />
            </svg>
            Pro
        </span>
        @endif
    </div>

    <div class="h-80 {{ !$isPro ? 'filter blur-sm select-none' : '' }}">
        <canvas id="activityChart"></canvas>
    </div>

    @if(!$isPro)
    <div class="absolute inset-0 flex items-center justify-center bg-white/50 backdrop-blur-[1px]">
        <div class="text-center p-6 bg-white rounded-xl shadow-2xl border border-pink-100 max-w-md mx-auto">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-pink-100 mb-4">
                <svg class="h-6 w-6 text-pink-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Débloquez les statistiques détaillées</h3>
            <p class="text-sm text-gray-500 mb-6">
                Suivez l'engagement quotidien, les inscriptions et l'impact de vos programmes avec le plan Pro.
            </p>
            <a href="{{ route('organization.subscriptions.index') }}"
                class="inline-flex items-center rounded-md bg-pink-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-pink-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pink-600">
                Passer au plan Pro
            </a>
        </div>
    </div>
    @endif
</div>

<!-- Demographics & Documents -->
<div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-8 relative">
    <!-- Overlay for non-Pro -->
    @if(!$isPro)
    <div
        class="absolute inset-0 z-10 flex items-center justify-center bg-gray-50/50 backdrop-blur-[1px] rounded-lg border-2 border-dashed border-gray-300">
        <div class="text-center">
            <h3 class="text-sm font-medium text-gray-900">Analyses démographiques & Documents</h3>
            <p class="mt-1 text-sm text-gray-500">Disponibles dans l'offre Pro</p>
        </div>
    </div>
    @endif

    <!-- Demographics -->
    <div class="bg-white rounded-lg shadow p-6 {{ !$isPro ? 'filter blur-sm opacity-50 pointer-events-none' : '' }}">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Démographie</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Top Cities -->
            <div>
                <h4 class="text-sm font-medium text-gray-500 mb-3">Top Villes</h4>
                <ul class="space-y-3">
                    @forelse($cityStats as $city)
                    <li class="flex items-center justify-between">
                        <div class="flex items-center">
                            <span class="w-2 h-2 bg-organization-400 rounded-full mr-2"></span>
                            <span class="text-sm text-gray-700">{{ $city->city }}</span>
                        </div>
                        <span class="text-sm font-semibold text-gray-900">{{ $city->count }}</span>
                    </li>
                    @empty
                    <li class="text-sm text-gray-500">Aucune donnée géographique</li>
                    @endforelse
                </ul>
            </div>

            <!-- Age Distribution -->
            <div>
                <h4 class="text-sm font-medium text-gray-500 mb-3">Tranches d'âge</h4>
                <div class="relative pt-1">
                    @foreach($ageStats as $range => $count)
                    @if($stats['total_registered'] > 0)
                    <div class="mb-4">
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="text-gray-700 font-medium">{{ $range }} ans</span>
                            <span class="text-gray-900 font-semibold">{{ $count }}</span>
                        </div>
                        <div class="overflow-hidden h-2 text-xs flex rounded bg-organization-100">
                            <div style="width: {{ ($count / $stats['total_registered']) * 100 }}%"
                                class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-organization-500">
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                    @if($stats['total_registered'] == 0)
                    <p class="text-sm text-gray-500">Aucune donnée d'âge</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Documents Breakdown -->
    <div class="bg-white rounded-lg shadow p-6 {{ !$isPro ? 'filter blur-sm opacity-50 pointer-events-none' : '' }}">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Répartition des documents</h3>
        <div class="space-y-4">
            @forelse($documentStats as $doc)
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <span
                        class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-blue-100 text-blue-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </span>
                </div>
                <div class="ml-4 flex-1">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-900">
                            {{ ucfirst(str_replace('_', ' ', $doc->type)) }}
                        </p>
                        <p class="text-sm text-gray-500">{{ $doc->count }}</p>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                        <div class="bg-blue-500 h-1.5 rounded-full"
                            style="width: {{ $stats['documents_count'] > 0 ? ($doc->count / $stats['documents_count']) * 100 : 0 }}%">
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-500 text-center py-4">Aucun document partagé</p>
            @endforelse
        </div>
    </div>
</div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('activityChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($activityLabels),
                datasets: [
                    {
                        label: 'Inscriptions',
                        data: @json($activityData['signups']),
                        borderColor: '#e11d48', // organization-600
                        backgroundColor: 'rgba(225, 29, 72, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Tests MBTI',
                        data: @json($activityData['tests']),
                        borderColor: '#9333ea', // purple-600
                        backgroundColor: 'rgba(147, 51, 234, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Sessions Mentorat',
                        data: @json($activityData['sessions']),
                        borderColor: '#4f46e5', // indigo-600
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Connexions',
                        data: @json($activityData['connections']),
                        borderColor: '#06b6d4', // cyan-500
                        backgroundColor: 'rgba(6, 182, 212, 0.1)',
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
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [2, 4],
                            color: '#e5e7eb'
                        },
                        ticks: {
                            stepSize: 1
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
    });

    function toggleCustomDates(value) {
        const customDates = document.getElementById('custom-dates');
        if (value === 'custom') {
            customDates.classList.remove('hidden');
        } else {
            customDates.classList.add('hidden');
        }
    }
</script>
@endpush
@endsection