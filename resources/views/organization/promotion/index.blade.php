@extends('layouts.organization')

@section('title', 'Suivi de Promotion')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Suivi de Promotion & Prospects</h1>
            <p class="mt-2 text-lg text-gray-600">Analysez l'impact de votre visibilité sur Brillio et suivez vos futurs étudiants.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('organization.promotion.export-csv') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-organization-500 transition-all">
                <i class="fas fa-file-csv mr-2"></i> Exporter CSV
            </a>
            <a href="{{ route('organization.promotion.export-pdf') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-xl text-white bg-organization-600 hover:bg-organization-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-organization-500 transition-all">
                <i class="fas fa-file-pdf mr-2"></i> Exporter PDF
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100 p-6 relative">
            <div class="absolute top-0 right-0 p-4 opacity-10">
                <i class="fas fa-mouse-pointer text-6xl text-organization-600"></i>
            </div>
            <p class="text-sm font-medium text-gray-500 truncate">Total des clics</p>
            <p class="mt-2 text-4xl font-bold text-gray-900">{{ number_format($totalClicks) }}</p>
            <div class="mt-4 flex items-center text-sm text-gray-600">
                <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs font-bold mr-2">
                    <i class="fas fa-chart-line mr-1"></i> Global
                </span>
                <span>Depuis la création</span>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100 p-6 relative">
            <div class="absolute top-0 right-0 p-4 opacity-10">
                <i class="fas fa-users text-6xl text-organization-600"></i>
            </div>
            <p class="text-sm font-medium text-gray-500 truncate">Prospects uniques</p>
            <p class="mt-2 text-4xl font-bold text-gray-900">{{ number_format($uniqueProspects) }}</p>
            <div class="mt-4 flex items-center text-sm text-gray-600">
                <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs font-bold mr-2">
                    <i class="fas fa-user-check mr-1"></i> Qualifiés
                </span>
                <span>Étudiants distincts</span>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100 p-6 relative">
            <div class="absolute top-0 right-0 p-4 opacity-10">
                <i class="fas fa-calendar-day text-6xl text-organization-600"></i>
            </div>
            <p class="text-sm font-medium text-gray-500 truncate">Clics récents</p>
            <p class="mt-2 text-4xl font-bold text-gray-900">{{ number_format($recentClicks) }}</p>
            <div class="mt-4 flex items-center text-sm text-gray-600">
                <span class="bg-organization-100 text-organization-700 px-2 py-0.5 rounded-full text-xs font-bold mr-2">
                    <i class="fas fa-history mr-1"></i> 30j
                </span>
                <span>Sur les 30 derniers jours</span>
            </div>
        </div>
    </div>

    <!-- Prospects Table -->
    <div class="bg-white shadow-sm rounded-2xl border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
            <h3 class="text-lg font-bold text-gray-900">Derniers clics et prospects</h3>
            <span class="text-sm text-gray-500">Liste des 50 dernières interactions</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Étudiant</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Localisation</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Type MBTI</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date du clic</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($clicks as $click)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img class="h-10 w-10 rounded-full object-cover border border-gray-100" src="{{ $click->user?->avatar_url }}" alt="">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-900">{{ $click->user?->name ?? 'Anonyme' }}</div>
                                    <div class="text-sm text-gray-500">{{ $click->user?->email ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $click->user?->city ?? 'Non précisé' }}</div>
                            <div class="text-sm text-gray-500">{{ $click->user?->country ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($click->user?->personalityTest)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800">
                                {{ $click->user->personalityTest->mbti_type }}
                            </span>
                            @else
                            <span class="text-xs text-gray-400 font-medium italic">Test non passé</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $click->created_at->translatedFormat('d M Y') }}
                            <div class="text-xs text-gray-400">{{ $click->created_at->format('H:i') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            @if($click->user)
                            <a href="{{ route('organization.users.show', $click->user) }}" class="text-organization-600 hover:text-organization-900 bg-organization-50 hover:bg-organization-100 px-3 py-1 rounded-lg transition-colors">
                                <i class="fas fa-eye mr-1"></i> Voir
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-mouse-pointer text-gray-200 text-5xl mb-4"></i>
                                <p class="text-gray-500 text-lg font-medium">Aucun clic enregistré pour le moment.</p>
                                <p class="text-gray-400 text-sm mt-1">Votre établissement n'a pas encore reçu de visites d'étudiants.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($clicks->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $clicks->links() }}
        </div>
        @endif
    </div>

    <!-- Info Box -->
    <div class="bg-gradient-to-r from-organization-500 to-organization-700 rounded-2xl p-8 text-white shadow-lg overflow-hidden relative">
        <div class="absolute top-0 right-0 -mr-16 -mt-16 opacity-10">
            <i class="fas fa-lightbulb text-[200px]"></i>
        </div>
        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between">
            <div class="max-w-2xl text-center md:text-left mb-6 md:mb-0">
                <h3 class="text-2xl font-bold mb-2">Maximisez votre visibilité</h3>
                <p class="text-organization-100 text-lg">
                    Chaque clic est un étudiant potentiel intéressé par votre établissement. Utilisez ces données pour comprendre quels profils sont attirés par votre offre et contactez-les pour les guider.
                </p>
            </div>
            <div class="flex-shrink-0">
                <a href="{{ route('organization.profile.edit') }}" class="bg-white text-organization-700 px-6 py-3 rounded-xl font-bold shadow-sm hover:bg-organization-50 transition-all inline-flex items-center">
                    <i class="fas fa-edit mr-2"></i> Optimiser mon profil
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
