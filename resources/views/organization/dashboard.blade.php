@extends('layouts.organization')

@section('title', 'Tableau de bord')

@section('content')
<div class="space-y-6">
    <!-- Welcome Header -->
    <div class="bg-gradient-to-r from-organization-500 to-organization-600 rounded-xl p-8 text-white">
        <h1 class="text-3xl font-bold">{{ $organization->name }}</h1>
        <p class="mt-2 text-organization-100">Bienvenue dans votre espace partenaire</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
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

        <!-- Jeunes suivis par un mentor -->
        <div class="bg-white overflow-hidden rounded-lg shadow">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Jeunes suivis</dt>
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
    </div>

    <!-- Personality Tests -->
    <div class="bg-white overflow-hidden rounded-lg shadow">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Tests de personnalité</dt>
                        <dd class="text-3xl font-semibold text-gray-900">{{ $stats['personality_tests_completed'] }}
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Users with Mentors -->
    <div class="bg-white overflow-hidden rounded-lg shadow">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        <a href="#"
            class="flex items-center justify-center px-4 py-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Exporter un rapport
        </a>
    </div>
</div>

<!-- Coming Soon Note -->
<div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                    clip-rule="evenodd" />
            </svg>
        </div>
        <div class="ml-3">
            <p class="text-sm text-blue-700">
                <strong>Sprint 1 terminé !</strong> Le système d'invitations et de suivi des jeunes sera disponible
                dans le prochain sprint.
            </p>
        </div>
    </div>
</div>
</div>
<!-- Charts -->
<div class="mt-8 bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Évolution des inscriptions (6 derniers mois)</h3>
    <div class="h-80">
        <canvas id="registrationChart"></canvas>
    </div>
</div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('registrationChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Nouvelles inscriptions',
                    data: @json($chartData),
                    borderColor: '#059669', // organization-600
                    backgroundColor: 'rgba(5, 150, 105, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#059669',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1f2937',
                        padding: 12,
                        titleFont: { size: 14 },
                        bodyFont: { size: 13 },
                        displayColors: false,
                        callbacks: {
                            label: function (context) {
                                return context.parsed.y + ' inscription(s)';
                            }
                        }
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
                            stepSize: 1,
                            font: { size: 12 }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: { size: 12 }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection