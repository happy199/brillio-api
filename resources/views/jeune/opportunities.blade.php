@extends('layouts.jeune')

@section('title', 'Opportunités - Emploi & Formation')

@section('content')
<div class="space-y-8" x-data="opportunitiesApp('{{ $tab ?? 'emploi' }}')">

    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Opportunités</h1>
            <p class="text-sm sm:text-base text-gray-500 mt-0.5">Explorez les offres d'emploi, stages qualifiés et opportunités de formation pour booster votre carrière.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('jeune.outils', ['tab' => 'cv']) }}"
                class="px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition flex items-center gap-2 shadow-sm text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Évaluer mon CV</span>
            </a>
        </div>
    </div>

    <!-- Barre des 2 Sous-onglets d'opportunités (Emploi & Formation) -->
    <div class="border-b border-gray-200">
        <nav class="flex space-x-2 sm:space-x-8 overflow-x-auto pb-1" aria-label="Tabs">
            <!-- 1. Emploi -->
            <button type="button"
                    @click="setTab('emploi')"
                    :class="currentTab === 'emploi' ? 'border-primary-600 text-primary-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
                    class="whitespace-nowrap py-3 px-3 sm:px-1 border-b-2 text-sm sm:text-base flex items-center gap-2 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span>Emploi</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">Recrutement</span>
            </button>

            <!-- 2. Formation -->
            <button type="button"
                    @click="setTab('formation')"
                    :class="currentTab === 'formation' ? 'border-primary-600 text-primary-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
                    class="whitespace-nowrap py-3 px-3 sm:px-1 border-b-2 text-sm sm:text-base flex items-center gap-2 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                </svg>
                <span>Formation</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800">Bourses</span>
            </button>
        </nav>
    </div>

    <!-- CONTENU ONGLET 1 : EMPLOI -->
    <div x-show="currentTab === 'emploi'" x-cloak class="space-y-6">
        <!-- Bannière d'introduction -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-3xl p-6 sm:p-8 text-white shadow-sm flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="space-y-2 max-w-2xl">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-white/20 uppercase tracking-wider">Passerelle Professionnelle</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Opportunités d'emploi & Stages</h2>
                <p class="text-sm sm:text-base text-blue-100">Découvrez des postes qualifiés, stages d'immersion et missions freelances adaptés aux jeunes talents d'Afrique francophone.</p>
            </div>
            <div class="flex-shrink-0">
                <a href="{{ route('jeune.outils', ['tab' => 'cv']) }}" class="inline-flex px-6 py-3 bg-white text-blue-700 font-bold rounded-xl shadow hover:bg-blue-50 transition">
                    Optimiser mon CV pour postuler
                </a>
            </div>
        </div>

        <!-- Liste des offres d'emploi -->
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Offre 1 -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-lg">Stage pré-embauche</span>
                        <span class="text-xs text-gray-400">Abidjan / Hybride</span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Développeur Fullstack Junior</h3>
                    <p class="text-xs font-medium text-primary-600 mb-3">Partenaire Tech Brillio</p>
                    <p class="text-xs text-gray-600 leading-relaxed line-clamp-3">Participez au développement d'applications mobiles et web dans un environnement agile avec encadrement par un lead tech expérimenté.</p>
                </div>
                <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-900">Indemnité mensuelle</span>
                    <a href="{{ route('jeune.outils', ['tab' => 'cv']) }}" class="text-xs font-bold text-primary-600 hover:underline">Postuler avec mon CV</a>
                </div>
            </div>

            <!-- Offre 2 -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <span class="px-2.5 py-1 bg-purple-50 text-purple-700 text-xs font-bold rounded-lg">CDD 12 mois</span>
                        <span class="text-xs text-gray-400">Dakar / Télétravail</span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Chargé(e) de Communication Digitale</h3>
                    <p class="text-xs font-medium text-primary-600 mb-3">Agence Média Panafricaine</p>
                    <p class="text-xs text-gray-600 leading-relaxed line-clamp-3">Animation de communautés, création de contenus visuels et rédaction d'articles percutants à destination de la jeunesse.</p>
                </div>
                <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-900">Temps plein</span>
                    <a href="{{ route('jeune.outils', ['tab' => 'cv']) }}" class="text-xs font-bold text-primary-600 hover:underline">Postuler avec mon CV</a>
                </div>
            </div>

            <!-- Offre 3 -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <span class="px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-bold rounded-lg">Stage de fin d'études</span>
                        <span class="text-xs text-gray-400">Lomé / Présentiel</span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Assistant Analyste Financier</h3>
                    <p class="text-xs font-medium text-primary-600 mb-3">Cabinet de Conseil Stratégique</p>
                    <p class="text-xs text-gray-600 leading-relaxed line-clamp-3">Assistance sur les études de marché, la modélisation financière et les reportings économiques pour les PME régionales.</p>
                </div>
                <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-900">Convention requise</span>
                    <a href="{{ route('jeune.outils', ['tab' => 'cv']) }}" class="text-xs font-bold text-primary-600 hover:underline">Postuler avec mon CV</a>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTENU ONGLET 2 : FORMATION -->
    <div x-show="currentTab === 'formation'" x-cloak class="space-y-6">
        <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-3xl p-6 sm:p-8 text-white shadow-sm flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="space-y-2 max-w-2xl">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-white/20 uppercase tracking-wider">Excellence Académique</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Bourses & Formations Certifiantes</h2>
                <p class="text-sm sm:text-base text-purple-100">Accédez aux programmes de bourses d'études internationales, masters d'excellence et bootcamps de formation accélérée.</p>
            </div>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
                <span class="px-2.5 py-1 bg-purple-100 text-purple-800 text-xs font-bold rounded-md">Bourse complète</span>
                <h3 class="text-lg font-bold text-gray-900">Bourses d'Excellence Master 2026</h3>
                <p class="text-xs text-gray-500">Prise en charge des frais de scolarité et allocation mensuelle pour les filières STEM et Management.</p>
                <div class="pt-4 border-t border-gray-100 flex items-center justify-between text-xs">
                    <span class="text-gray-400">Date limite : 31 Décembre</span>
                    <a href="{{ route('jeune.chat') }}" class="font-bold text-purple-600 hover:underline">Demander conseil à l'IA</a>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
                <span class="px-2.5 py-1 bg-pink-100 text-pink-800 text-xs font-bold rounded-md">Bootcamp 100% en ligne</span>
                <h3 class="text-lg font-bold text-gray-900">Certification Cloud & Intelligence Artificielle</h3>
                <p class="text-xs text-gray-500">Programme intensif de 12 semaines pour maîtriser les outils cloud modernes et le déploiement d'agents IA.</p>
                <div class="pt-4 border-t border-gray-100 flex items-center justify-between text-xs">
                    <span class="text-gray-400">Certificat inclus</span>
                    <a href="{{ route('jeune.resources.index') }}" class="font-bold text-pink-600 hover:underline">Découvrir les cours</a>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
                <span class="px-2.5 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-md">Mentorat dédié</span>
                <h3 class="text-lg font-bold text-gray-900">Préparation aux Concours d'Ingénieurs</h3>
                <p class="text-xs text-gray-500">Sessions hebdomadaires avec des mentors diplômés des plus grandes écoles polytechniques.</p>
                <div class="pt-4 border-t border-gray-100 flex items-center justify-between text-xs">
                    <span class="text-gray-400">Accompagnement 1-on-1</span>
                    <a href="{{ route('jeune.mentors') }}" class="font-bold text-amber-600 hover:underline">Trouver un mentor</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
function opportunitiesApp(initialTab = 'emploi') {
    return {
        currentTab: ['emploi', 'formation'].includes(initialTab) ? initialTab : 'emploi',
        setTab(tab) {
            this.currentTab = tab;
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.pushState({}, '', url);
        }
    };
}
</script>
@endsection
