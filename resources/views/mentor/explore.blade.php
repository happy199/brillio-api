@extends('layouts.mentor')

@section('title', 'Explorer les profils')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Talents Brillio</h1>
                <p class="text-gray-500">Découvrez les jeunes talents à la recherche de mentorat.</p>
            </div>
        </div>

        <div class="grid lg:grid-cols-4 gap-6">
            <!-- Sidebar Filtres -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-2xl p-5 shadow-sm sticky top-6">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Filtres
                    </h3>

                    @php
                        $userTest = auth()->user()->personalityTest;
                    @endphp

                    @if($userTest && $userTest->completed_at)
                        <div class="mb-6 pb-6 border-b border-gray-100">
                            <a href="{{ route('mentor.explore', array_merge(request()->except('matching'), ['matching' => request('matching') ? null : '1'])) }}"
                                class="w-full py-3 px-4 rounded-xl flex items-center justify-center gap-2 font-bold transition {{ request('matching') ? 'bg-purple-600 text-white shadow-md' : 'bg-purple-50 text-purple-700 hover:bg-purple-100' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                {{ request('matching') ? 'Mode Matching Activé' : 'Voir compatibilités' }}
                            </a>
                            @if(request('matching'))
                                <p class="text-xs text-center text-gray-500 mt-2">Basé sur votre type
                                    <strong>{{ $userTest->personality_type }}</strong></p>
                            @endif
                        </div>
                    @else
                        <div class="mb-6 pb-6 border-b border-gray-100">
                            <div class="bg-gray-50 rounded-xl p-4 text-center">
                                <p class="text-sm text-gray-600 mb-3">Passez le test pour voir les candidats compatibles !</p>
                                <a href="{{ route('mentor.personality') }}"
                                    class="block w-full py-2 bg-purple-600 text-white text-sm font-bold rounded-lg hover:bg-purple-700 transition">
                                    Passer le test
                                </a>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('mentor.explore') }}" method="GET" class="space-y-4">
                        @if(request('matching'))
                            <input type="hidden" name="matching" value="1">
                        @endif

                        <!-- MBTI -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Type de
                                Personnalité</label>
                            <select name="mbti"
                                class="w-full rounded-xl border-gray-200 text-sm focus:border-purple-500 focus:ring-purple-500">
                                <option value="">Tous les types</option>
                                @foreach(\App\Models\PersonalityTest::PERSONALITY_TYPES as $code => $label)
                                    <option value="{{ $code }}" {{ request('mbti') == $code ? 'selected' : '' }}>
                                        {{ $code }} - {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Situation -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Situation</label>
                            <select name="current_situation"
                                class="w-full rounded-xl border-gray-200 text-sm focus:border-purple-500 focus:ring-purple-500">
                                <option value="">Toutes situations</option>
                                <option value="etudiant" {{ request('current_situation') == 'etudiant' ? 'selected' : '' }}>
                                    Étudiant</option>
                                <option value="recherche_emploi" {{ request('current_situation') == 'recherche_emploi' ? 'selected' : '' }}>Recherche d'emploi</option>
                                <option value="emploi" {{ request('current_situation') == 'emploi' ? 'selected' : '' }}>En
                                    poste</option>
                                <option value="entrepreneur" {{ request('current_situation') == 'entrepreneur' ? 'selected' : '' }}>Entrepreneur</option>
                            </select>
                        </div>

                        <!-- Niveau Etude -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Niveau d'étude</label>
                            <select name="education_level"
                                class="w-full rounded-xl border-gray-200 text-sm focus:border-purple-500 focus:ring-purple-500">
                                <option value="">Tous niveaux</option>
                                <option value="bac" {{ request('education_level') == 'bac' ? 'selected' : '' }}>Bac</option>
                                <option value="licence" {{ request('education_level') == 'licence' ? 'selected' : '' }}>
                                    Licence (Bac+3)</option>
                                <option value="master" {{ request('education_level') == 'master' ? 'selected' : '' }}>Master
                                    (Bac+5)</option>
                                <option value="doctorat" {{ request('education_level') == 'doctorat' ? 'selected' : '' }}>
                                    Doctorat</option>
                            </select>
                        </div>

                        <button type="submit"
                            class="w-full bg-purple-600 text-white font-bold py-2 rounded-xl text-sm hover:bg-purple-700 transition">
                            Appliquer les filtres
                        </button>

                        @if(request()->anyFilled(['mbti', 'current_situation', 'education_level', 'interest']))
                            <a href="{{ route('mentor.explore') }}"
                                class="block text-center text-xs text-purple-600 hover:underline">
                                Réinitialiser
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Grid Résultats -->
            <div class="lg:col-span-3">
                @if($jeunes->isEmpty())
                    <div class="bg-white rounded-2xl p-12 text-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">Aucun profil trouvé</h3>
                        <p class="text-gray-500">Essayez de modifier vos filtres.</p>
                    </div>
                @else
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($jeunes as $jeune)
                            <div
                                class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-md transition border border-gray-100 flex flex-col items-center text-center">
                                <div
                                    class="w-20 h-20 bg-gradient-to-br from-purple-100 to-pink-100 rounded-full flex items-center justify-center text-2xl font-bold text-purple-600 mb-4">
                                    {{ substr($jeune->name, 0, 1) }}
                                </div>

                                <h3 class="font-bold text-gray-900 text-lg mb-1">{{ $jeune->name }}</h3>
                                <p class="text-sm text-gray-500 mb-4">{{ $jeune->city }}, {{ $jeune->country }}</p>

                                @if($jeune->personalityTest)
                                    <div class="bg-purple-50 text-purple-700 px-3 py-1 rounded-full text-xs font-bold mb-4">
                                        {{ $jeune->personalityTest->personality_type }}
                                    </div>
                                @endif

                                <p class="text-sm text-gray-600 mb-6 line-clamp-2">
                                    {{ $jeune->jeuneProfile->bio ?? 'Aucune bio renseignée.' }}
                                </p>

                                <a href="{{ route('jeune.public.show', $jeune->jeuneProfile->public_slug) }}" target="_blank"
                                    class="w-full py-2 border-2 border-purple-600 text-purple-600 font-bold rounded-xl hover:bg-purple-50 transition text-sm">
                                    Voir le profil
                                </a>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-8">
                        {{ $jeunes->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection