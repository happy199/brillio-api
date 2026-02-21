@extends('layouts.jeune')

@section('title', 'Explorer les mentors')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="bg-gradient-to-r from-orange-500 via-red-500 to-pink-500 rounded-3xl p-8 text-white">
        <h1 class="text-3xl font-bold mb-2">Decouvre des parcours inspirants</h1>
        <p class="text-white/90 max-w-lg">
            Explore les roadmaps de professionnels africains qui ont reussi et inspire-toi de leurs parcours.
        </p>
    </div>

    <!-- Search & Filters -->
    <div class="bg-white rounded-2xl p-6 shadow-sm" x-data="{ showFilters: false }">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1 relative">
                <svg class="w-5 h-5 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" id="searchInput" placeholder="Rechercher un mentor, un metier, une entreprise..."
                    class="w-full pl-12 pr-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <button @click="showFilters = !showFilters"
                class="px-5 py-3 border rounded-xl font-medium text-gray-700 hover:bg-gray-50 transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filtres
            </button>
        </div>

        <!-- Expandable Filters -->
        <div x-show="showFilters" x-collapse class="mt-4 pt-4 border-t">
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Specialisation</label>
                    <select id="filterSpecialization"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">Toutes</option>
                        @foreach($specializations ?? [] as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pays</label>
                    <select id="filterCountry"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">Tous les pays</option>
                        @foreach($countries ?? [] as $country)
                        <option value="{{ $country }}">{{ $country }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Experience</label>
                    <select id="filterExperience"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">Toute experience</option>
                        <option value="0-5">0-5 ans</option>
                        <option value="5-10">5-10 ans</option>
                        <option value="10+">10+ ans</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button onclick="applyFilters()"
                        class="w-full px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition">
                        Appliquer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Recommandation MBTI -->
    @if(isset($userMbtiType) && $userMbtiType)
    <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-2xl p-5 border border-purple-100">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div
                    class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center">
                    <span class="text-white font-bold">{{ $userMbtiType }}</span>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Ton profil : <span class="font-semibold text-gray-900">{{
                            $userMbtiLabel ?? $userMbtiType }}</span></p>
                    <p class="text-xs text-gray-500">Decouvre les mentors dans tes secteurs recommandes</p>
                </div>
            </div>
            <a href="{{ route('jeune.mentors', ['for_profile' => 'true']) }}"
                class="px-5 py-2.5 bg-gradient-to-r from-purple-500 to-pink-500 text-white text-sm font-semibold rounded-xl hover:shadow-lg transition flex items-center gap-2 {{ request('for_profile') === 'true' ? 'ring-2 ring-purple-300' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Pour mon profil
            </a>
        </div>
    </div>
    @endif

    <!-- Filtres rapides -->
    <div class="space-y-3">
        <!-- Secteurs MBTI -->
        @if(isset($sectors) && count($sectors) > 0)
        <div class="flex flex-wrap gap-2">
            <span class="text-sm text-gray-500 py-2">Secteurs :</span>
            <a href="{{ route('jeune.mentors') }}"
                class="px-4 py-2 rounded-full text-sm font-medium transition {{ !request('sector') && !request('specialization') && !request('for_profile') ? 'bg-primary-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border' }}">
                Tous
            </a>
            @foreach($sectors as $code => $sector)
            <a href="{{ route('jeune.mentors', ['sector' => $code]) }}"
                class="px-4 py-2 rounded-full text-sm font-medium transition {{ request('sector') === $code ? 'bg-primary-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border' }}">
                {{ $sector['name'] ?? $code }}
            </a>
            @endforeach
        </div>
        @endif

        <!-- Specialisations -->
        <div class="flex flex-wrap gap-2">
            <span class="text-sm text-gray-500 py-2">Specialisation :</span>
            @foreach($specializations ?? [] as $key => $label)
            <a href="{{ route('jeune.mentors', ['specialization' => $key]) }}"
                class="px-3 py-1.5 rounded-full text-xs font-medium transition {{ request('specialization') === $key ? 'bg-orange-500 text-white' : 'bg-orange-50 text-orange-700 hover:bg-orange-100' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>
    </div>

    <!-- Mentors Grid -->
    @if($mentors->count() > 0)
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($mentors as $mentor)
        <a href="{{ route('jeune.mentors.show', $mentor) }}"
            class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden group relative">
            <!-- Barre laterale decorative -->
            <div
                class="absolute left-0 top-0 bottom-0 w-1.5 bg-gradient-to-b from-orange-400 via-red-400 to-pink-400 rounded-l-2xl">
            </div>

            <div class="p-5 pl-6">
                <!-- Header avec photo/initiales et badge -->
                <div class="flex items-start gap-4 mb-4">
                    <!-- Photo ou initiales -->
                    <div
                        class="w-16 h-16 rounded-xl flex-shrink-0 overflow-hidden flex items-center justify-center shadow-md bg-white">
                        @if($mentor->user && $mentor->user->avatar_url)
                        <img src="{{ $mentor->user->avatar_url }}" alt="{{ $mentor->user->name }}"
                            class="w-full h-full object-cover"
                            onerror="this.onerror=null; this.parentElement.classList.add('bg-gradient-to-br', 'from-orange-400', 'via-red-400', 'to-pink-400'); this.parentElement.innerHTML='<span class=\'text-xl font-bold text-white\'>{{ strtoupper(substr($mentor->user->name ?? '?', 0, 2)) }}</span>';">
                        @else
                        <div
                            class="w-full h-full flex items-center justify-center bg-gradient-to-br from-orange-400 via-red-400 to-pink-400">
                            <span class="text-xl font-bold text-white">{{ strtoupper(substr($mentor->user->name ?? '?',
                                0, 2)) }}</span>
                        </div>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <h3
                                class="font-bold text-gray-900 text-lg group-hover:text-primary-600 transition truncate">
                                {{ $mentor->user->name ?? 'Mentor' }}</h3>
                            @if($mentor->is_validated)
                            <span
                                class="flex-shrink-0 w-5 h-5 bg-green-100 rounded-full flex items-center justify-center"
                                title="Profil verifie">
                                <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                            @endif
                        </div>
                        <p class="text-gray-600 text-sm truncate">{{ $mentor->current_position }}</p>
                        @if($mentor->current_company)
                        <p class="text-gray-500 text-sm truncate">{{ $mentor->current_company }}</p>
                        @endif
                    </div>
                </div>

                <!-- Tags -->
                <div class="flex flex-wrap gap-2 mb-4">
                    @if($mentor->specialization)
                    <span class="px-3 py-1 bg-orange-100 text-orange-700 text-xs font-medium rounded-full">
                        {{ $mentor->specialization_label }}
                    </span>
                    @endif
                    @if($mentor->years_of_experience)
                    <span class="px-3 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">
                        {{ $mentor->years_of_experience }} ans exp.
                    </span>
                    @endif
                    @if($mentor->user && $mentor->user->country)
                    <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs rounded-full">
                        {{ $mentor->user->country }}
                    </span>
                    @endif
                </div>

                <!-- Bio -->
                @if($mentor->bio)
                <p class="text-gray-500 text-sm line-clamp-2 mb-4">{{ $mentor->bio }}</p>
                @endif

                <!-- Footer -->
                <div class="pt-4 border-t flex items-center justify-between">
                    <div class="flex items-center gap-1 text-sm text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        {{ $mentor->roadmapSteps()->count() }} etapes
                    </div>
                    <span
                        class="text-primary-600 font-medium text-sm flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                        Voir le parcours
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </div>
            </div>
        </a>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $mentors->links('pagination.modern-pagination') }}
    </div>
    @else
    <!-- Empty State -->
    <div class="bg-white rounded-2xl p-12 text-center">
        <div class="w-20 h-20 bg-orange-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Aucun mentor trouve</h3>
        <p class="text-gray-500 mb-6">Essaie de modifier tes criteres de recherche.</p>
        <a href="{{ route('jeune.mentors') }}"
            class="px-6 py-3 bg-primary-500 text-white font-semibold rounded-xl hover:bg-primary-600 transition">
            Voir tous les mentors
        </a>
    </div>
    @endif
</div>

@push('scripts')
<script>
    function applyFilters() {
        const search = document.getElementById('searchInput').value;
        const specialization = document.getElementById('filterSpecialization').value;
        const country = document.getElementById('filterCountry').value;
        const experience = document.getElementById('filterExperience').value;

        const params = new URLSearchParams();
        if (search) params.set('search', search);
        if (specialization) params.set('specialization', specialization);
        if (country) params.set('country', country);
        if (experience) params.set('experience', experience);

        window.location.href = '{{ route('jeune.mentors') }}?' + params.toString();
    }

    // Search on enter
    document.getElementById('searchInput').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            applyFilters();
        }
    });
</script>
@endpush
@endsection