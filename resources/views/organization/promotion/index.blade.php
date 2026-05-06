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
            <h3 class="text-lg font-bold text-gray-900">Derniers prospects et interactions</h3>
            <span class="text-sm text-gray-500">Liste des étudiants ayant interagi avec vos fiches</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Étudiant</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Localisation</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Type MBTI</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Clics</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Intérêt</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Dernière interaction</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($prospects as $prospect)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    @if($prospect->avatar_url)
                                    <img class="h-10 w-10 rounded-full object-cover border border-gray-100" src="{{ $prospect->avatar_url }}" alt="">
                                    @else
                                    <div class="h-10 w-10 rounded-full bg-organization-100 flex items-center justify-center text-organization-600 font-bold border border-organization-200">
                                        {{ substr($prospect->name, 0, 1) }}
                                    </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-900">{{ $prospect->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $prospect->email }}</div>
                                    <div class="text-[11px] font-medium {{ $prospect->phone ? 'text-gray-600' : 'text-gray-400 italic' }}">
                                        <i class="fas fa-phone-alt mr-1 text-[10px]"></i> {{ $prospect->phone ?? 'Pas de numéro' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $prospect->jeuneProfile?->city ?? 'Non précisé' }}</div>
                            <div class="text-sm text-gray-500">{{ $prospect->jeuneProfile?->country ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($prospect->personalityTest)
                            @php
                                $type = $prospect->personalityTest->personality_type;
                                $info = $mbtiDescriptions[$type] ?? null;
                            @endphp
                            <span class="mbti-tooltip inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 cursor-help border border-indigo-200" 
                                  data-tippy-content="<strong>{{ $info['label'] ?? $type }}</strong><br/><br/>{{ $info['description'] ?? 'Description non disponible.' }}">
                                <i class="fas fa-brain mr-1.5 text-indigo-500"></i>
                                {{ $info['label'] ?? $type }}
                            </span>
                            @else
                            <span class="text-xs text-gray-400 font-medium italic">Test non passé</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-gray-100 text-gray-800 border border-gray-200">
                                {{ $prospect->clicks_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($prospect->has_interest)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-200">
                                <i class="fas fa-heart mr-1.5 text-green-500"></i> Manifesté
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-50 text-gray-400 border border-gray-100">
                                <i class="far fa-eye mr-1.5"></i> Visite seule
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($prospect->last_interaction_at)->translatedFormat('d M Y') }}
                            <div class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($prospect->last_interaction_at)->format('H:i') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('organization.users.show', $prospect) }}" class="text-organization-600 hover:text-organization-900 bg-organization-50 hover:bg-organization-100 px-3 py-1 rounded-lg transition-colors inline-flex items-center">
                                <i class="fas fa-user-graduate mr-1.5"></i> Profil
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-users text-gray-200 text-3xl"></i>
                                </div>
                                <p class="text-gray-500 text-lg font-medium">Aucun prospect enregistré pour le moment.</p>
                                <p class="text-gray-400 text-sm mt-1">Vos établissements n'ont pas encore reçu d'interactions d'étudiants.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($prospects->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $prospects->links() }}
        </div>
        @endif
    </div>

    @push('scripts')
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function() {
            tippy('.mbti-tooltip', {
                allowHTML: true,
                placement: 'top',
                theme: 'light-border',
                animation: 'shift-away',
                maxWidth: 300
            });
        });
    </script>
    @endpush

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
