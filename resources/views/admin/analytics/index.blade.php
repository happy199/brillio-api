@extends('layouts.admin')

@section('title', 'Analytics')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Analytics</h1>
            <p class="text-gray-600">Statistiques et métriques de la plateforme</p>
        </div>
        <div class="flex gap-3">
            <select id="period-filter" class="rounded-lg border-gray-300 text-sm">
                <option value="7">7 derniers jours</option>
                <option value="30" selected>30 derniers jours</option>
                <option value="90">3 derniers mois</option>
                <option value="365">Cette année</option>
            </select>
        </div>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-green-600 font-medium">+{{ $stats['new_users_week'] }}</span>
                <span class="text-gray-500 ml-1">cette semaine</span>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-gray-500">Taux complétion:</span>
                <span class="text-purple-600 font-medium ml-1">{{ $stats['test_completion_rate'] }}%</span>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-gray-500">Conversations:</span>
                <span class="text-green-600 font-medium ml-1">{{ number_format($stats['total_conversations']) }}</span>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-gray-500">Étapes parcours:</span>
                <span class="text-orange-600 font-medium ml-1">{{ number_format($stats['total_roadmap_steps']) }}</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Distribution des types de personnalité -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Distribution des personnalités</h3>
            <div class="space-y-3">
                @forelse($stats['personality_distribution'] as $type => $count)
                <div class="flex items-center gap-3">
                    <div class="w-16 text-sm font-medium text-gray-700">{{ $type }}</div>
                    <div class="flex-1 bg-gray-100 rounded-full h-4">
                        <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-4 rounded-full"
                             style="width: {{ $stats['total_tests'] > 0 ? ($count / $stats['total_tests'] * 100) : 0 }}%"></div>
                    </div>
                    <div class="w-12 text-sm text-gray-500 text-right">{{ $count }}</div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">Aucune donnée disponible</p>
                @endforelse
            </div>
        </div>

        <!-- Distribution par pays -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Utilisateurs par pays</h3>
            <div class="space-y-3">
                @forelse($stats['users_by_country'] as $country)
                <div class="flex items-center gap-3">
                    <div class="w-24 text-sm text-gray-700 truncate">{{ $country->country ?? 'Non renseigné' }}</div>
                    <div class="flex-1 bg-gray-100 rounded-full h-4">
                        <div class="bg-gradient-to-r from-green-500 to-teal-500 h-4 rounded-full"
                             style="width: {{ $stats['total_users'] > 0 ? ($country->total / $stats['total_users'] * 100) : 0 }}%"></div>
                    </div>
                    <div class="w-12 text-sm text-gray-500 text-right">{{ $country->total }}</div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">Aucune donnée disponible</p>
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
                    <div class="w-32 text-sm text-gray-700 truncate">{{ $specializations[$spec->specialization] ?? $spec->specialization ?? 'Non défini' }}</div>
                    <div class="flex-1 bg-gray-100 rounded-full h-4">
                        <div class="bg-gradient-to-r from-orange-500 to-red-500 h-4 rounded-full"
                             style="width: {{ $stats['active_mentors'] > 0 ? ($spec->total / $stats['active_mentors'] * 100) : 0 }}%"></div>
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
            <h3 class="font-semibold text-gray-900 mb-4">Inscriptions récentes</h3>
            <div class="space-y-4">
                @forelse($stats['recent_signups'] as $user)
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $user->user_type === 'mentor' ? 'bg-orange-100' : 'bg-blue-100' }}">
                        <span class="{{ $user->user_type === 'mentor' ? 'text-orange-600' : 'text-blue-600' }} font-semibold">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $user->name }}</p>
                        <p class="text-xs text-gray-500">{{ $user->email }}</p>
                    </div>
                    <div class="text-right">
                        <span class="text-xs px-2 py-1 rounded-full {{ $user->user_type === 'mentor' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ $user->user_type === 'mentor' ? 'Mentor' : 'Jeune' }}
                        </span>
                        <p class="text-xs text-gray-400 mt-1">{{ $user->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">Aucune inscription récente</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Documents -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Documents académiques</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <p class="text-3xl font-bold text-blue-600">{{ $stats['documents']['total'] ?? 0 }}</p>
                <p class="text-sm text-gray-600 mt-1">Total documents</p>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <p class="text-3xl font-bold text-green-600">{{ $stats['documents']['bulletin'] ?? 0 }}</p>
                <p class="text-sm text-gray-600 mt-1">Bulletins</p>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <p class="text-3xl font-bold text-purple-600">{{ $stats['documents']['releve_notes'] ?? 0 }}</p>
                <p class="text-sm text-gray-600 mt-1">Relevés de notes</p>
            </div>
            <div class="text-center p-4 bg-orange-50 rounded-lg">
                <p class="text-3xl font-bold text-orange-600">{{ $stats['documents']['diplome'] ?? 0 }}</p>
                <p class="text-sm text-gray-600 mt-1">Diplômes</p>
            </div>
        </div>
    </div>
</div>
@endsection
