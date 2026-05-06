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
            <p class="text-sm font-medium text-gray-500 truncate">Intérêts (30j)</p>
            <p class="mt-2 text-4xl font-bold text-gray-900">{{ number_format($recentInterests) }}</p>
            <div class="mt-4 flex items-center text-sm text-gray-600">
                <span class="bg-organization-100 text-organization-700 px-2 py-0.5 rounded-full text-xs font-bold mr-2">
                    <i class="fas fa-plus mr-1"></i> Nouveau
                </span>
                <span>Derniers 30 jours</span>
            </div>
        </div>
    </div>

    <!-- Main Content with Tabs -->
    <div x-data="{ tab: 'interests' }">
        <!-- Tabs Navigation -->
        <div class="flex border-b border-gray-200 mb-6">
            <button @click="tab = 'interests'" 
                :class="tab === 'interests' ? 'border-organization-500 text-organization-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="whitespace-nowrap py-4 px-6 border-b-2 font-bold text-sm transition-colors">
                Manifestations d'intérêt ({{ $totalInterests }})
            </button>
            <button @click="tab = 'clicks'" 
                :class="tab === 'clicks' ? 'border-organization-500 text-organization-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="whitespace-nowrap py-4 px-6 border-b-2 font-bold text-sm transition-colors">
                Clics & Vues ({{ $totalClicks }})
            </button>
        </div>

        <!-- Tab: Interests -->
        <div x-show="tab === 'interests'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="bg-white shadow-sm rounded-2xl border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Étudiant</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Localisation</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Contacts</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Type d'intérêt</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Détails formulaire</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($interests as $interest)
                            @php $u = $interest->user; @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $interest->created_at->translatedFormat('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            @if($u->avatar_url)
                                            <img class="h-10 w-10 rounded-full object-cover border border-gray-100" src="{{ $u->avatar_url }}" alt="">
                                            @else
                                            <div class="h-10 w-10 rounded-full bg-organization-100 flex items-center justify-center text-organization-600 font-bold border border-organization-200">
                                                {{ substr($u->name, 0, 1) }}
                                            </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-bold text-gray-900">{{ $u->name }}</div>
                                            <div class="text-xs text-indigo-600 font-medium">MBTI: {{ $u->personalityTest?->personality_type ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 font-medium">{{ $u->country ?? 'Non précisé' }}</div>
                                    <div class="text-xs text-gray-500">{{ $u->city ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 font-medium">{{ $u->email }}</div>
                                    <div class="text-xs text-gray-500">{{ $u->phone ?? 'Pas de numéro' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($interest->type === 'precise')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                        <i class="fas fa-list-ul mr-1.5"></i> Précis
                                    </span>
                                    @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                        <i class="fas fa-bolt mr-1.5"></i> Rapide
                                    </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    @if($interest->form_data)
                                        <div class="space-y-1">
                                            @foreach($interest->form_data as $key => $value)
                                                @if($value)
                                                <div>
                                                    <span class="font-bold text-gray-700 capitalize">{{ str_replace('_', ' ', $key) }}:</span>
                                                    <span class="text-gray-600">{{ is_array($value) ? implode(', ', $value) : $value }}</span>
                                                </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="italic text-gray-400">Aucun détail</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if($u->jeuneProfile && $u->jeuneProfile->is_public)
                                    <a href="{{ route('jeune.public.show', $u->jeuneProfile->public_slug) }}" target="_blank" class="text-organization-600 hover:text-organization-900 bg-organization-50 hover:bg-organization-100 px-3 py-1 rounded-lg transition-colors inline-flex items-center">
                                        <i class="fas fa-user-graduate mr-1.5"></i> Profil
                                    </a>
                                    @else
                                    <span class="text-xs text-gray-400 italic">Profil privé</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    Aucune manifestation d'intérêt pour le moment.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($interests->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $interests->links() }}
                </div>
                @endif
            </div>
        </div>

        <!-- Tab: Clicks -->
        <div x-show="tab === 'clicks'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="bg-white shadow-sm rounded-2xl border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Dernière visite</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Étudiant</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Localisation</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Clics</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Type MBTI</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($clicks as $prospect)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($prospect->last_click_at)->translatedFormat('d/m/Y H:i') }}
                                </td>
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
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 font-medium">{{ $prospect->country ?? 'Non précisé' }}</div>
                                    <div class="text-xs text-gray-500">{{ $prospect->city ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-800 border border-gray-200">
                                        {{ $prospect->clicks_count }}
                                    </span>
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
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if($prospect->jeuneProfile && $prospect->jeuneProfile->is_public)
                                    <a href="{{ route('jeune.public.show', $prospect->jeuneProfile->public_slug) }}" target="_blank" class="text-organization-600 hover:text-organization-900 bg-organization-50 hover:bg-organization-100 px-3 py-1 rounded-lg transition-colors inline-flex items-center">
                                        <i class="fas fa-user-graduate mr-1.5"></i> Profil
                                    </a>
                                    @else
                                    <span class="text-xs text-gray-400 italic">Profil privé</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    Aucun clic enregistré pour le moment.
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
        </div>
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
