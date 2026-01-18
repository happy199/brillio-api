@extends('layouts.mentor')

@section('title', 'Test de personnalite MBTI')

@php
    $mbtiTypes = [
        'INTJ' => ['name' => 'Architecte', 'color' => 'from-purple-500 to-indigo-600'],
        'INTP' => ['name' => 'Logicien', 'color' => 'from-purple-400 to-blue-500'],
        'ENTJ' => ['name' => 'Commandant', 'color' => 'from-purple-600 to-pink-500'],
        'ENTP' => ['name' => 'Innovateur', 'color' => 'from-orange-500 to-pink-500'],
        'INFJ' => ['name' => 'Avocat', 'color' => 'from-green-500 to-teal-500'],
        'INFP' => ['name' => 'Mediateur', 'color' => 'from-green-400 to-cyan-500'],
        'ENFJ' => ['name' => 'Protagoniste', 'color' => 'from-green-500 to-emerald-600'],
        'ENFP' => ['name' => 'Campaigner', 'color' => 'from-yellow-500 to-orange-500'],
        'ISTJ' => ['name' => 'Logisticien', 'color' => 'from-blue-600 to-indigo-700'],
        'ISFJ' => ['name' => 'Defenseur', 'color' => 'from-blue-500 to-cyan-600'],
        'ESTJ' => ['name' => 'Directeur', 'color' => 'from-blue-600 to-blue-800'],
        'ESFJ' => ['name' => 'Consul', 'color' => 'from-cyan-500 to-blue-600'],
        'ISTP' => ['name' => 'Virtuose', 'color' => 'from-amber-500 to-yellow-600'],
        'ISFP' => ['name' => 'Aventurier', 'color' => 'from-amber-400 to-orange-500'],
        'ESTP' => ['name' => 'Entrepreneur', 'color' => 'from-red-500 to-orange-600'],
        'ESFP' => ['name' => 'Amuseur', 'color' => 'from-pink-500 to-rose-600'],
    ];
@endphp

@section('content')
    <div class="space-y-8" x-data="personalityTest()">
        @if($personalityTest && $personalityTest->completed_at)
            @php
                $typeInfo = $mbtiTypes[$personalityTest->personality_type] ?? ['name' => $personalityTest->personality_label ?? $personalityTest->personality_type, 'color' => 'from-purple-500 to-pink-500'];
            @endphp
            <div class="bg-gradient-to-r {{ $typeInfo['color'] }} rounded-3xl p-8 text-white">
                <div class="flex flex-col md:flex-row md:items-center gap-6">
                    <div class="w-24 h-24 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                        <span class="text-4xl font-bold">{{ $personalityTest->personality_type }}</span>
                    </div>
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold mb-2">{{ $typeInfo['name'] }}</h1>
                        <p class="text-white/90">{{ $personalityTest->personality_description }}</p>
                        <p class="text-white/70 text-sm mt-2">Test passe le
                            {{ $personalityTest->completed_at->format('d/m/Y') }}
                        </p>
                    </div>
                </div>
            </div>

            @if($personalityTest->traits_scores)
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Tes dimensions de personnalite</h2>
                    <div class="space-y-6">
                        @php
                            $dimensions = [
                                ['left' => 'E', 'right' => 'I', 'leftName' => 'Extraversion', 'rightName' => 'Introversion', 'leftColor' => 'from-blue-400 to-blue-600', 'rightColor' => 'from-purple-400 to-purple-600'],
                                ['left' => 'S', 'right' => 'N', 'leftName' => 'Sensation', 'rightName' => 'Intuition', 'leftColor' => 'from-green-400 to-green-600', 'rightColor' => 'from-yellow-400 to-yellow-600'],
                                ['left' => 'T', 'right' => 'F', 'leftName' => 'Pensee', 'rightName' => 'Sentiment', 'leftColor' => 'from-teal-400 to-teal-600', 'rightColor' => 'from-pink-400 to-pink-600'],
                                ['left' => 'J', 'right' => 'P', 'leftName' => 'Jugement', 'rightName' => 'Perception', 'leftColor' => 'from-orange-400 to-orange-600', 'rightColor' => 'from-cyan-400 to-cyan-600'],
                            ];
                        @endphp
                        @foreach($dimensions as $dim)
                            <div>
                                <div class="flex justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-700">{{ $dim['leftName'] }} ({{ $dim['left'] }})</span>
                                    <span class="text-sm font-medium text-gray-700">{{ $dim['rightName'] }} ({{ $dim['right'] }})</span>
                                </div>
                                <div class="relative w-full bg-gray-200 rounded-full h-4">
                                    <div class="absolute left-1/2 w-px h-full bg-gray-400"></div>
                                    @php $leftScore = $personalityTest->traits_scores[$dim['left']] ?? 50; @endphp
                                    @if($leftScore == 50)
                                        <div class="absolute right-1/2 bg-gradient-to-l {{ $dim['leftColor'] }} h-4 rounded-l-full"
                                            style="width: 25%"></div>
                                        <div class="absolute left-1/2 bg-gradient-to-r {{ $dim['rightColor'] }} h-4 rounded-r-full"
                                            style="width: 25%"></div>
                                    @elseif($leftScore > 50)
                                        <div class="absolute right-1/2 bg-gradient-to-l {{ $dim['leftColor'] }} h-4 rounded-l-full"
                                            style="width: {{ min(($leftScore - 50), 50) }}%"></div>
                                    @else
                                        <div class="absolute left-1/2 bg-gradient-to-r {{ $dim['rightColor'] }} h-4 rounded-r-full"
                                            style="width: {{ min((50 - $leftScore), 50) }}%"></div>
                                    @endif
                                </div>
                                <div class="flex justify-between mt-1">
                                    <span class="text-xs text-gray-500">{{ round($leftScore) }}%</span>
                                    <span class="text-xs text-gray-500">{{ round(100 - $leftScore) }}%</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($personalityTest->recommended_careers)
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Métiers recommandés pour ton profil</h2>
                    <div class="grid md:grid-cols-2 gap-4">
                        @foreach($personalityTest->recommended_careers as $career)
                            <div class="border-2 border-gray-100 rounded-xl p-4 hover:border-primary-300 hover:shadow-md transition">
                                <h3 class="font-bold text-gray-900 mb-2">{{ $career['title'] }}</h3>
                                <p class="text-sm text-gray-600 mb-3">{{ $career['description'] }}</p>
                                <div class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-primary-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-xs text-primary-600">{{ $career['match_reason'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex flex-col sm:flex-row gap-4">
                <a href="{{ route('mentor.explore') }}"
                    class="flex-1 bg-gradient-to-r from-primary-500 to-secondary-500 text-white text-center py-4 rounded-xl font-semibold hover:shadow-lg transition">Voir
                    les talents compatibles</a>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 mt-4">
                <a href="{{ route('jeune.personality.export-pdf') }}"
                    class="flex-1 bg-red-500 text-white text-center py-4 rounded-xl font-semibold hover:shadow-lg transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Télécharger mon test en PDF
                </a>
                @if($testHistory && $testHistory->count() > 0)
                    <a href="{{ route('jeune.personality.export-history-pdf') }}"
                        class="flex-1 bg-purple-500 text-white text-center py-4 rounded-xl font-semibold hover:shadow-lg transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Télécharger l'historique complet
                    </a>
                @endif
            </div>

            @if($testHistory && $testHistory->count() > 0)
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Historique de tes tests</h2>
                    <p class="text-sm text-gray-600 mb-4">Voici l'évolution de votre personnalité au fil du temps</p>
                    <div class="space-y-3">
                        @foreach($testHistory as $test)
                            <div class="border-2 border-gray-100 rounded-xl p-4 hover:border-primary-200 transition">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="w-16 h-16 bg-gradient-to-r {{ $mbtiTypes[$test->personality_type]['color'] ?? 'from-gray-400 to-gray-600' }} rounded-xl flex items-center justify-center">
                                            <span class="text-white font-bold text-lg">{{ $test->personality_type }}</span>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-gray-900">{{ $test->personality_label }}</h3>
                                            <p class="text-sm text-gray-500">{{ $test->completed_at->format('d/m/Y à H:i') }}</p>
                                        </div>
                                    </div>
                                    <button @click="viewHistoryTest({{ $test->id }})"
                                        class="px-4 py-2 text-sm text-primary-600 hover:bg-primary-50 rounded-lg transition">
                                        Voir détails
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="text-center">
                <button @click="retakeTest()"
                    class="px-8 py-3 bg-white border-2 border-primary-500 text-primary-600 font-semibold rounded-xl hover:bg-primary-50 transition">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refaire le test
                </button>
            </div>
        @else
            <div class="bg-gradient-to-r from-primary-500 via-purple-500 to-pink-500 rounded-3xl p-8 text-white text-center">
                <div class="w-20 h-20 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold mb-4">Decouvre ton type de personnalite</h1>
                <p class="text-white/90 max-w-lg mx-auto">Le test MBTI t'aidera a mieux te connaitre et a decouvrir les metiers
                    qui correspondent a ta personnalite.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                <div class="bg-white rounded-2xl p-6 shadow-sm text-center">
                    <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center mx-auto mb-4"><svg
                            class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg></div>
                    <h3 class="font-bold text-gray-900 mb-2">10-15 minutes</h3>
                    <p class="text-sm text-gray-500">Duree moyenne du test</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm text-center">
                    <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-4"><svg
                            class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg></div>
                    <h3 class="font-bold text-gray-900 mb-2">32 questions</h3>
                    <p class="text-sm text-gray-500">Questions simples et rapides</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm text-center">
                    <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center mx-auto mb-4"><svg
                            class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg></div>
                    <h3 class="font-bold text-gray-900 mb-2">16 types</h3>
                    <p class="text-sm text-gray-500">De personnalite possibles</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Les 16 types de personnalite MBTI</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach($mbtiTypes as $type => $info)
                        <div
                            class="bg-gradient-to-br {{ $info['color'] }} rounded-xl p-3 text-white hover:scale-105 transition cursor-pointer">
                            <span class="font-bold text-lg block">{{ $type }}</span>
                            <span class="text-white/90 text-sm">{{ $info['name'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="text-center">
                <button @click="startTest()"
                    class="px-12 py-4 bg-gradient-to-r from-purple-500 to-pink-500 text-white font-bold text-lg rounded-full hover:shadow-xl transition-all duration-300">Commencer
                    le test MBTI</button>
                <p class="text-sm text-gray-500 mt-3">Gratuit et sans engagement</p>
            </div>
        @endif

        <!-- Modal du test -->
        <div x-show="showTest" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
            x-transition>
            <div class="bg-white rounded-3xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col"
                @click.outside="closeTest()">
                <!-- Header -->
                <div class="p-6 border-b flex-shrink-0">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900">Test de personnalité MBTI</h3>
                            <p class="text-sm text-gray-500" x-show="!loading">Question <span
                                    x-text="currentQuestion + 1"></span> sur <span x-text="questions.length"></span></p>
                        </div>
                        <button @click="closeTest()" class="p-2 hover:bg-gray-100 rounded-full"><svg
                                class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg></button>
                    </div>
                    <div x-show="!loading" class="mt-4">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-gradient-to-r from-purple-500 to-pink-500 h-2 rounded-full transition-all duration-300"
                                :style="'width: ' + ((currentQuestion + 1) / questions.length * 100) + '%'"></div>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-8 flex-1 overflow-y-auto">
                    <!-- Loading -->
                    <div x-show="loading" class="text-center py-12">
                        <div
                            class="w-16 h-16 border-4 border-purple-500 border-t-transparent rounded-full animate-spin mx-auto mb-4">
                        </div>
                        <p class="text-gray-600">Chargement des questions...</p>
                    </div>

                    <!-- Questions -->
                    <div x-show="!loading && !submitting" class="space-y-8">
                        <!-- Question text -->
                        <div class="text-center mb-8">
                            <p class="text-2xl text-gray-900 font-semibold leading-relaxed"
                                x-text="questions[currentQuestion]?.text"></p>
                        </div>

                        <!-- Scale with numbered squares -->
                        <div class="py-8">
                            <!-- Trait labels -->
                            <div class="flex justify-between items-center mb-8 px-4">
                                <div class="text-left">
                                    <p class="text-lg font-semibold text-teal-500"
                                        x-text="questions[currentQuestion]?.left_trait || 'Trait gauche'"></p>
                                </div>
                                <div class="text-center">
                                    <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">Contre</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-semibold text-purple-500"
                                        x-text="questions[currentQuestion]?.right_trait || 'Trait droit'"></p>
                                </div>
                            </div>

                            <!-- Numbered square buttons -->
                            <div class="flex justify-center items-center gap-4">
                                <template x-for="option in answerOptions" :key="option.value">
                                    <div class="flex flex-col items-center gap-3">
                                        <button @click="selectAnswer(option.value)"
                                            :class="answers[questions[currentQuestion]?.id] === option.value 
                                                                ? (option.value <= 2 ? 'bg-teal-500 border-teal-600 text-white' 
                                                                    : option.value === 3 ? 'bg-gray-500 border-gray-600 text-white'
                                                                    : 'bg-purple-500 border-purple-600 text-white')
                                                                : (option.value <= 2 ? 'border-teal-500 text-teal-500 hover:bg-teal-50' 
                                                                    : option.value === 3 ? 'border-gray-400 text-gray-400 hover:bg-gray-50'
                                                                    : 'border-purple-500 text-purple-500 hover:bg-purple-50')"
                                            class="w-20 h-20 border-2 rounded-lg font-bold text-3xl transition-all duration-200 hover:scale-105 flex items-center justify-center">
                                            <span x-text="option.value"></span>
                                        </button>
                                        <p class="text-xs text-gray-500 text-center max-w-[100px]" x-text="option.label">
                                        </p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Navigation arrows -->
                        <div class="flex justify-center gap-4 pt-4">
                            <button @click="previousQuestion()" :disabled="currentQuestion === 0"
                                :class="currentQuestion === 0 ? 'opacity-30 cursor-not-allowed' : 'hover:bg-gray-100'"
                                class="p-3 rounded-full border-2 border-gray-300 transition">
                                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <button @click="nextQuestion()"
                                :disabled="!answers[questions[currentQuestion]?.id] || currentQuestion === questions.length - 1"
                                :class="(!answers[questions[currentQuestion]?.id] || currentQuestion === questions.length - 1) ? 'opacity-30 cursor-not-allowed' : 'hover:bg-gray-100'"
                                class="p-3 rounded-full border-2 border-gray-300 transition">
                                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Submitting -->
                    <div x-show="submitting" class="text-center py-12">
                        <div
                            class="w-16 h-16 border-4 border-purple-500 border-t-transparent rounded-full animate-spin mx-auto mb-4">
                        </div>
                        <p class="text-gray-600">Calcul de ton profil en cours...</p>
                    </div>
                </div>

                <!-- Footer with submit button -->
                <div x-show="!loading && !submitting && currentQuestion === questions.length - 1 && allAnswered"
                    class="p-6 border-t flex-shrink-0">
                    <button @click="submitTest()"
                        class="w-full px-6 py-4 bg-gradient-to-r from-green-500 to-emerald-600 text-white font-semibold text-lg rounded-xl hover:shadow-lg transition">
                        Voir mes résultats
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Historique Test -->
        <div x-show="showHistoryModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
            x-transition>
            <div class="bg-white rounded-3xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col"
                @click.away="closeHistoryModal()">
                <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-900">Détails du test</h3>
                    <button @click="closeHistoryModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-6">
                    <div x-show="historyLoading" class="text-center py-12">
                        <div
                            class="w-16 h-16 border-4 border-purple-500 border-t-transparent rounded-full animate-spin mx-auto mb-4">
                        </div>
                        <p class="text-gray-600">Chargement des détails...</p>
                    </div>
                    <div x-show="!historyLoading && historyTest" class="space-y-6">
                        <!-- En-tête du type -->
                        <div class="bg-gradient-to-r from-purple-500 to-pink-500 rounded-2xl p-6 text-white">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-20 h-20 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                                    <span class="text-3xl font-bold" x-text="historyTest?.personality_type"></span>
                                </div>
                                <div>
                                    <h4 class="text-2xl font-bold" x-text="historyTest?.personality_label"></h4>
                                    <p class="text-white/80 text-sm" x-text="'Test passé le ' + historyTest?.completed_at">
                                    </p>
                                </div>
                            </div>
                            <p class="mt-4 text-white/90" x-text="historyTest?.personality_description"></p>
                        </div>

                        <!-- Dimensions -->
                        <div x-show="historyTest?.traits_scores" class="bg-gray-50 rounded-2xl p-6">
                            <h5 class="font-bold text-gray-900 mb-4">Dimensions de personnalité</h5>
                            <div class="space-y-4">
                                <template x-for="dim in [
                                                                                {left: 'E', right: 'I', leftName: 'Extraversion', rightName: 'Introversion'},
                                                                                {left: 'S', right: 'N', leftName: 'Sensation', rightName: 'Intuition'},
                                                                                {left: 'T', right: 'F', leftName: 'Pensée', rightName: 'Sentiment'},
                                                                                {left: 'J', right: 'P', leftName: 'Jugement', rightName: 'Perception'}
                                                                            ]" :key="dim.left">
                                    <div>
                                        <div class="flex justify-between mb-1 text-sm">
                                            <span class="text-gray-600"
                                                x-text="dim.leftName + ' (' + dim.left + ')'"></span>
                                            <span class="text-gray-600"
                                                x-text="dim.rightName + ' (' + dim.right + ')'"></span>
                                        </div>
                                        <div class="relative w-full bg-gray-200 rounded-full h-3">
                                            <div class="absolute left-1/2 w-px h-full bg-gray-400"></div>
                                            <template x-if="(historyTest?.traits_scores?.[dim.left] || 50) == 50">
                                                <div>
                                                    <div class="absolute right-1/2 bg-gradient-to-l from-purple-500 to-pink-500 h-3 rounded-l-full"
                                                        style="width: 25%"></div>
                                                    <div class="absolute left-1/2 bg-gradient-to-r from-purple-500 to-pink-500 h-3 rounded-r-full"
                                                        style="width: 25%"></div>
                                                </div>
                                            </template>
                                            <template x-if="(historyTest?.traits_scores?.[dim.left] || 50) != 50">
                                                <div class="absolute bg-gradient-to-r from-purple-500 to-pink-500 h-3 rounded-full"
                                                    :style="(historyTest?.traits_scores?.[dim.left] || 50) > 50 
                                                                                    ? 'right: 50%; width: ' + Math.min((historyTest?.traits_scores?.[dim.left] || 50) - 50, 50) + '%; border-radius: 9999px 0 0 9999px;'
                                                                                    : 'left: 50%; width: ' + Math.min(50 - (historyTest?.traits_scores?.[dim.left] || 50), 50) + '%; border-radius: 0 9999px 9999px 0;'">
                                                </div>
                                            </template>
                                        </div>
                                        <div class="flex justify-between mt-1 text-xs text-gray-500">
                                            <span
                                                x-text="Math.round(historyTest?.traits_scores?.[dim.left] || 50) + '%'"></span>
                                            <span
                                                x-text="Math.round(100 - (historyTest?.traits_scores?.[dim.left] || 50)) + '%'"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Métiers recommandés -->
                        <div x-show="historyTest?.recommended_careers?.length > 0">
                            <h5 class="font-bold text-gray-900 mb-4">Métiers recommandés</h5>
                            <div class="grid md:grid-cols-2 gap-3">
                                <template x-for="career in historyTest?.recommended_careers?.slice(0, 6)"
                                    :key="career.title">
                                    <div class="border border-gray-200 rounded-xl p-4 hover:border-purple-300 transition">
                                        <h6 class="font-semibold text-gray-900" x-text="career.title"></h6>
                                        <p class="text-sm text-gray-600 mt-1" x-text="career.description"></p>
                                        <div x-show="career.sectors?.length > 0" class="flex flex-wrap gap-1 mt-2">
                                            <template x-for="sector in career.sectors" :key="sector">
                                                <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full"
                                                    x-text="sector"></span>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function personalityTest() {
                return {
                    showTest: false, testStarted: false, loading: false, submitting: false, questions: [], answers: {}, currentQuestion: 0,
                    answerOptions: [{ value: 1, label: 'Toujours comme ça' }, { value: 2, label: 'Souvent comme ça' }, { value: 3, label: 'Cela dépend de la situation' }, { value: 4, label: 'Souvent comme ça' }, { value: 5, label: 'Toujours comme ça' }],
                    get allAnswered() { return this.questions.length > 0 && Object.keys(this.answers).length === this.questions.length; },
                    startTest() {
                        console.log('startTest() called - opening modal');
                        this.showTest = true;
                        this.loadQuestions(); // Auto-load questions
                    },
                    closeTest() { if (this.testStarted && Object.keys(this.answers).length > 0 && !confirm('Êtes-vous sûr de vouloir quitter ?')) return; this.showTest = false; this.resetTest(); },
                    resetTest() { this.testStarted = false; this.questions = []; this.answers = {}; this.currentQuestion = 0; },
                    retakeTest() {
                        this.resetTest();
                        this.showTest = true;
                        this.loadQuestions(); // Load questions when retaking test
                    },
                    showHistoryModal: false,
                    historyTest: null,
                    historyLoading: false,
                    async viewHistoryTest(testId) {
                        this.historyLoading = true;
                        this.showHistoryModal = true;
                        try {
                            const response = await fetch(`/espace-mentor/test-personnalite/history/${testId}`, {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                }
                            });
                            const data = await response.json();
                            if (data.success) {
                                this.historyTest = data.test;
                            } else {
                                alert(data.message || 'Erreur lors du chargement du test.');
                                this.showHistoryModal = false;
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('Erreur de connexion.');
                            this.showHistoryModal = false;
                        }
                        this.historyLoading = false;
                    },
                    closeHistoryModal() {
                        this.showHistoryModal = false;
                        this.historyTest = null;
                    },
                    async loadQuestions() {
                        console.log('loadQuestions called');
                        this.loading = true;
                        try {
                            const response = await fetch('/espace-mentor/test-personnalite/questions', {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                }
                            });
                            console.log('Response status:', response.status);
                            const data = await response.json();
                            console.log('Response data:', data);
                            if (data.success && data.questions) {
                                this.questions = data.questions;
                                this.testStarted = true;
                                console.log('Questions loaded:', this.questions.length);
                            } else {
                                console.error('Failed to load questions:', data);
                                alert('Erreur lors du chargement des questions.');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('Erreur de connexion.');
                        }
                        this.loading = false;
                    },
                    selectAnswer(value) {
                        console.log('selectAnswer called with value:', value);
                        const qid = this.questions[this.currentQuestion]?.id;
                        console.log('Question ID:', qid);
                        if (qid) {
                            this.answers[qid] = value;
                            console.log('Answer saved, advancing to next question');
                            // Auto-advance to next question after a short delay
                            if (this.currentQuestion < this.questions.length - 1) {
                                setTimeout(() => {
                                    this.currentQuestion++;
                                }, 300);
                            }
                        }
                    },
                    nextQuestion() { if (this.currentQuestion < this.questions.length - 1 && this.answers[this.questions[this.currentQuestion]?.id]) this.currentQuestion++; },
                    previousQuestion() { if (this.currentQuestion > 0) this.currentQuestion--; },
                    async submitTest() {
                        if (!this.allAnswered) return;
                        this.submitting = true;
                        try {
                            const response = await fetch('/espace-mentor/test-personnalite/submit', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({ responses: this.answers })
                            });
                            const data = await response.json();
                            if (data.success) {
                                window.location.reload();
                            } else {
                                alert(data.message || 'Erreur lors de la soumission.');
                                this.submitting = false;
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('Erreur de connexion.');
                            this.submitting = false;
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection