@extends('layouts.admin')

@section('title', 'Dashboard')
@section('header', 'Tableau de bord')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total utilisateurs -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-3 bg-indigo-100 rounded-lg">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Utilisateurs</h3>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_users']) }}</p>
                <p class="text-xs text-gray-500">{{ $stats['total_jeunes'] }} jeunes, {{ $stats['total_mentors'] }} mentors</p>
            </div>
        </div>
    </div>

    <!-- Tests complétés -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-lg">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Tests personnalité</h3>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_personality_tests']) }}</p>
                <p class="text-xs text-gray-500">Tests complétés</p>
            </div>
        </div>
    </div>

    <!-- Conversations chat -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Messages chat</h3>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_chat_messages']) }}</p>
                <p class="text-xs text-gray-500">{{ $stats['total_conversations'] }} conversations</p>
            </div>
        </div>
    </div>

    <!-- Mentors publiés -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-3 bg-purple-100 rounded-lg">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Mentors publiés</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['published_mentors'] }}</p>
                <p class="text-xs text-gray-500">{{ $stats['pending_mentors'] }} en attente</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Graphique distribution personnalités -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Distribution des personnalités</h3>
        <canvas id="personalityChart" height="200"></canvas>
    </div>

    <!-- Graphique inscriptions -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Inscriptions (30 derniers jours)</h3>
        <canvas id="registrationChart" height="200"></canvas>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Utilisateurs récents -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Utilisateurs récents</h3>
        <div class="space-y-4">
            @forelse($recentUsers as $user)
                <div class="flex items-center justify-between border-b pb-3">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                            <span class="text-indigo-600 font-semibold">{{ substr($user->name, 0, 1) }}</span>
                        </div>
                        <div class="ml-3">
                            <p class="font-medium text-gray-800">{{ $user->name }}</p>
                            <p class="text-sm text-gray-500">{{ $user->email }}</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 text-xs rounded-full {{ $user->user_type === 'mentor' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ ucfirst($user->user_type) }}
                    </span>
                </div>
            @empty
                <p class="text-gray-500">Aucun utilisateur récent</p>
            @endforelse
        </div>
        <a href="{{ route('admin.users.index') }}" class="mt-4 inline-block text-indigo-600 hover:text-indigo-800 text-sm">
            Voir tous les utilisateurs &rarr;
        </a>
    </div>

    <!-- Mentors en attente -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Mentors en attente de validation</h3>
        <div class="space-y-4">
            @forelse($pendingMentors as $mentor)
                <div class="flex items-center justify-between border-b pb-3">
                    <div>
                        <p class="font-medium text-gray-800">{{ $mentor->user->name }}</p>
                        <p class="text-sm text-gray-500">{{ $mentor->current_position ?? 'Poste non renseigné' }}</p>
                    </div>
                    <a href="{{ route('admin.mentors.show', $mentor) }}"
                       class="px-3 py-1 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">
                        Voir
                    </a>
                </div>
            @empty
                <p class="text-gray-500">Aucun mentor en attente</p>
            @endforelse
        </div>
        <a href="{{ route('admin.mentors.index', ['published' => 0]) }}" class="mt-4 inline-block text-indigo-600 hover:text-indigo-800 text-sm">
            Voir tous les mentors en attente &rarr;
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Graphique distribution personnalités
const personalityData = @json($personalityDistribution);
const personalityLabels = Object.keys(personalityData);
const personalityValues = Object.values(personalityData);

new Chart(document.getElementById('personalityChart'), {
    type: 'doughnut',
    data: {
        labels: personalityLabels,
        datasets: [{
            data: personalityValues,
            backgroundColor: [
                '#6366f1', '#8b5cf6', '#ec4899', '#f43f5e',
                '#f97316', '#eab308', '#22c55e', '#14b8a6',
                '#06b6d4', '#3b82f6', '#6366f1', '#8b5cf6',
                '#ec4899', '#f43f5e', '#f97316', '#eab308'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'right',
            }
        }
    }
});

// Graphique inscriptions
const registrationData = @json($registrationTrend);
const registrationLabels = Object.keys(registrationData);
const registrationValues = Object.values(registrationData);

new Chart(document.getElementById('registrationChart'), {
    type: 'line',
    data: {
        labels: registrationLabels,
        datasets: [{
            label: 'Inscriptions',
            data: registrationValues,
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
@endpush
