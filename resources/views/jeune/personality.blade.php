@extends('layouts.jeune')

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
                            <button type="button" 
                                 class="text-left w-full border-2 border-gray-100 rounded-xl p-4 hover:border-primary-300 hover:shadow-md transition cursor-pointer group"
                                 style="cursor: pointer;"
                                 data-career="{{ json_encode($career) }}"
                                 @click="viewCareerDetails(JSON.parse($el.dataset.career))">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-bold text-gray-900 group-hover:text-primary-600 transition">{{ $career['title'] }}</h3>
                                    <svg class="w-5 h-5 text-gray-300 group-hover:text-primary-500 transition-transform transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $career['description'] }}</p>
                                <div class="flex items-start gap-2 bg-primary-50 p-2 rounded-lg">
                                    <svg class="w-4 h-4 text-primary-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-xs text-primary-700 font-medium">{{ $career['match_reason'] }}</p>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex flex-col sm:flex-row gap-4">
                <a href="{{ route('jeune.chat', ['prefill' => 'Je viens de passer le test de personnalité et mon profil est ' . $personalityTest->personality_type . ' (' . ($mbtiTypes[$personalityTest->personality_type]['name'] ?? $personalityTest->personality_label) . '). Peux-tu m\'aider à mieux comprendre ce profil et me donner des conseils pour mon orientation professionnelle ?']) }}"
                    class="flex-1 bg-primary-600 text-white text-center py-4 rounded-xl font-semibold hover:bg-primary-700 transition">Discuter
                    avec l'IA sur mes resultats</a>
                <a href="{{ route('jeune.mentors') }}"
                    class="flex-1 bg-white border-2 border-gray-200 text-gray-700 text-center py-4 rounded-xl font-semibold hover:border-primary-500 hover:text-primary-600 transition">Voir
                    des mentors dans mes domaines</a>
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
                    <p class="text-sm text-gray-600 mb-4">Voici l'évolution de ta personnalité au fil du temps</p>
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
                        <button @click="closeTest()"
                                :class="confirmClose ? 'bg-red-50 text-red-600 px-4 py-2 rounded-xl text-sm font-bold border border-red-200 animate-pulse' : 'p-2 hover:bg-gray-100 rounded-full'"
                                class="transition-all duration-300">
                            <template x-if="!confirmClose">
                                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </template>
                            <template x-if="confirmClose">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    Voulez-vous vraiment quitter ?
                                </span>
                            </template>
                        </button>
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
                    <div x-show="!loading && !submitting" class="space-y-6 sm:space-y-8">
                        <!-- Question text -->
                        <div class="text-center mb-6 sm:mb-8">
                            <p class="text-lg sm:text-2xl text-gray-900 font-semibold leading-relaxed"
                                x-text="questions[currentQuestion]?.text"></p>
                        </div>

                        <!-- Scale with numbered squares -->
                        <div class="py-4 sm:py-8">
                            <!-- Trait labels -->
                            <div class="flex justify-between items-center mb-6 sm:mb-8 px-2 sm:px-4">
                                <div class="text-left">
                                    <p class="text-sm sm:text-lg font-semibold text-teal-500"
                                        x-text="questions[currentQuestion]?.left_trait || 'Trait gauche'"></p>
                                </div>
                                <div class="text-center">
                                    <p class="text-xs sm:text-sm font-medium text-gray-400 uppercase tracking-wider">Contre
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm sm:text-lg font-semibold text-purple-500"
                                        x-text="questions[currentQuestion]?.right_trait || 'Trait droit'"></p>
                                </div>
                            </div>

                            <!-- Numbered square buttons -->
                            <div class="flex justify-center items-center gap-2 sm:gap-4">
                                <template x-for="option in answerOptions" :key="option.value">
                                    <div class="flex flex-col items-center gap-2 sm:gap-3">
                                        <button @click="selectAnswer(option.value)"
                                            :class="answers[questions[currentQuestion]?.id] === option.value
                                                                ? (option.value <= 2 ? 'bg-teal-500 border-teal-600 text-white'
                                                                    : option.value === 3 ? 'bg-gray-500 border-gray-600 text-white'
                                                                    : 'bg-purple-500 border-purple-600 text-white')
                                                                : (option.value <= 2 ? 'border-teal-500 text-teal-500 hover:bg-teal-50'
                                                                    : option.value === 3 ? 'border-gray-400 text-gray-400 hover:bg-gray-50'
                                                                    : 'border-purple-500 text-purple-500 hover:bg-purple-50')"
                                            class="w-12 h-12 sm:w-20 sm:h-20 border-2 rounded-lg font-bold text-xl sm:text-3xl transition-all duration-200 hover:scale-105 flex items-center justify-center">
                                            <span x-text="option.value"></span>
                                        </button>
                                        <p class="text-[10px] sm:text-xs text-gray-500 text-center max-w-[60px] sm:max-w-[100px] leading-tight"
                                            x-text="option.label">
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
                        <div class="bg-primary-600 rounded-2xl p-6 text-white">
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
                                                    <div class="absolute right-1/2 bg-primary-600 h-3 rounded-l-full"
                                                        style="width: 25%"></div>
                                                    <div class="absolute left-1/2 bg-primary-600 h-3 rounded-r-full"
                                                        style="width: 25%"></div>
                                                </div>
                                            </template>
                                            <template x-if="(historyTest?.traits_scores?.[dim.left] || 50) != 50">
                                                <div class="absolute bg-primary-600 h-3 rounded-full"
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
        <!-- Career Details Modal -->
        <div x-show="showCareerModal" class="fixed inset-0 z-[60] overflow-y-auto" x-cloak style="display: none;"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="fixed inset-0 bg-black/60 backdrop-blur-md" @click="closeCareerModal()"></div>

            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden transform transition-all">
                    <!-- Close Button -->
                    <button @click="closeCareerModal()" class="absolute top-4 right-4 z-10 p-2 bg-white/10 hover:bg-gray-100 text-gray-400 hover:text-gray-600 rounded-full transition shadow-sm border border-gray-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <!-- Loading State -->
                    <div x-show="loadingCareer" class="p-12 flex flex-col items-center justify-center space-y-4">
                        <div class="animate-spin rounded-full h-12 w-12 border-4 border-primary-500 border-t-transparent"></div>
                        <p class="text-gray-500 font-medium">Récupération de la fiche métier...</p>
                    </div>

                    <!-- Career Content -->
                    <template x-if="!loadingCareer && selectedCareer">
                        <div class="flex flex-col">
                            <!-- Header -->
                            <div class="relative bg-gradient-to-br from-primary-600 to-indigo-700 px-8 py-10 text-white">
                                <div class="flex items-center gap-2 text-primary-100 text-sm font-bold uppercase tracking-wider mb-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <span>Fiche Métier</span>
                                </div>
                                <h2 class="text-3xl md:text-4xl font-extrabold" x-text="selectedCareer.title"></h2>
                                
                                <!-- AI Impact & Demand Badges -->
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <template x-if="selectedCareer.ai_impact_level">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1.5"
                                              :class="{
                                                  'bg-emerald-500/20 text-emerald-100 border border-emerald-500/30': selectedCareer.ai_impact_level === 'low',
                                                  'bg-amber-500/20 text-amber-100 border border-amber-500/30': selectedCareer.ai_impact_level === 'medium',
                                                  'bg-rose-500/20 text-rose-100 border border-rose-500/30': selectedCareer.ai_impact_level === 'high'
                                              }">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1a1 1 0 112 0v1a1 1 0 11-2 0zM13.536 14.243a1 1 0 011.414 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707zM6.464 14.95a1 1 0 11-1.414 1.414l-.707-.707a1 1 0 011.414-1.414l.707.707z" />
                                            </svg>
                                            <span x-text="'IA : ' + (selectedCareer.ai_impact_level === 'low' ? 'Profil Stable' : (selectedCareer.ai_impact_level === 'medium' ? 'Profil Assisté' : 'Profil Challengé'))"></span>
                                        </span>
                                    </template>
                                    
                                    <template x-if="selectedCareer.demand_level">
                                        <span class="px-3 py-1 bg-white/10 border border-white/20 rounded-full text-xs font-bold text-white uppercase tracking-wider" 
                                              x-text="'Demande : ' + (selectedCareer.demand_level === 'high' ? 'Très forte' : (selectedCareer.demand_level === 'medium' ? 'Modérée' : 'Stable'))"></span>
                                    </template>
                                </div>
                            </div>

                            <!-- Content Body -->
                            <div class="p-8 space-y-8 max-h-[60vh] overflow-y-auto bg-gray-50/50">
                                <!-- Description -->
                                <section>
                                    <h4 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                                        En quelques mots
                                    </h4>
                                    <p class="text-gray-700 leading-relaxed" x-text="selectedCareer.description"></p>
                                </section>

                                <!-- Africa Context -->
                                <template x-if="selectedCareer.african_context">
                                    <section class="bg-primary-50 rounded-2xl p-6 border border-primary-100">
                                        <h4 class="text-primary-900 font-bold mb-3 flex items-center gap-2">
                                            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 002 2 2 2 0 012 2v.654M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Pourquoi en Afrique ?
                                        </h4>
                                        <p class="text-primary-800 leading-relaxed text-sm" x-text="selectedCareer.african_context"></p>
                                    </section>
                                </template>

                                <!-- Future Prospects -->
                                <template x-if="selectedCareer.future_prospects">
                                    <section>
                                        <h4 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Perspectives d'avenir
                                        </h4>
                                        <p class="text-gray-700 leading-relaxed text-sm" x-text="selectedCareer.future_prospects"></p>
                                    </section>
                                </template>

                                <!-- AI Explanation -->
                                <template x-if="selectedCareer.ai_impact_explanation">
                                    <section class="border-t border-gray-100 pt-6">
                                        <h4 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                            Le regard de Brillio sur l'IA
                                        </h4>
                                        <p class="text-gray-600 italic text-sm" x-text="selectedCareer.ai_impact_explanation"></p>
                                    </section>
                                </template>

                                <!-- Fallback Notice -->
                                <template x-if="selectedCareer.is_fallback">
                                    <section class="border-t border-gray-100 pt-6 text-center">
                                        <p class="text-xs text-gray-400 italic">Cette fiche métier sera enrichie prochainement avec plus de détails contextuels.</p>
                                    </section>
                                </template>
                            </div>

                            <!-- Footer -->
                            <div class="px-8 py-4 bg-white border-t border-gray-100 flex justify-between items-center">
                                <span class="text-xs text-gray-400">© Brillio</span>
                                <button @click="closeCareerModal()" class="px-6 py-2 bg-gray-900 hover:bg-black text-white rounded-xl font-bold transition shadow-md">
                                    Fermer
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script nonce="{{ request()->attributes->get('csp_nonce') }}">
            function personalityTest() {
                return {
                    showTest: false, testStarted: false, loading: false, submitting: false, questions: [], answers: {}, currentQuestion: 0, confirmClose: false,
                    showCareerModal: false, selectedCareer: null, loadingCareer: false,
                    answerOptions: [{ value: 1, label: 'Toujours comme ça' }, { value: 2, label: 'Souvent comme ça' }, { value: 3, label: 'Cela dépend de la situation' }, { value: 4, label: 'Souvent comme ça' }, { value: 5, label: 'Toujours comme ça' }],
                    get allAnswered() { return this.questions.length > 0 && Object.keys(this.answers).length === this.questions.length; },
                    startTest() {
                        console.log('startTest() called - opening modal');
                        this.showTest = true;
                        this.loadQuestions(); // Auto-load questions
                    },
                    closeTest() {
                        if (this.testStarted && Object.keys(this.answers).length > 0 && !this.confirmClose) {
                            this.confirmClose = true;
                            // Reset confirmation after 5 seconds if not clicked
                            setTimeout(() => { this.confirmClose = false; }, 5000);
                            return;
                        }
                        this.showTest = false;
                        this.resetTest();
                    },
                    resetTest() { this.testStarted = false; this.questions = []; this.answers = {}; this.currentQuestion = 0; this.confirmClose = false; },
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
                            const response = await fetch(`/espace-jeune/test-personnalite/history/${testId}`, {
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
                            const response = await fetch('/espace-jeune/test-personnalite/questions', {
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
                                    this.confirmClose = false; // Reset confirmation if user continues
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
                            const response = await fetch('/espace-jeune/test-personnalite/submit', {
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
                    },

                    async viewCareerDetails(career) {
                        if (!career || !career.title) return;

                        this.loadingCareer = true;
                        this.selectedCareer = null;
                        this.showCareerModal = true;

                        try {
                            const response = await fetch('{{ route("careers.details-by-title") }}?title=' + encodeURIComponent(career.title));
                            const data = await response.json();

                            if (data.success && data.career) {
                                this.selectedCareer = data.career;
                            } else {
                                // Fallback if career not in DB
                                this.selectedCareer = {
                                    title: career.title,
                                    description: career.description || '',
                                    is_fallback: true
                                };
                            }
                        } catch (e) {
                            console.error('Error fetching career details:', e);
                            this.selectedCareer = {
                                title: career.title,
                                description: career.description || '',
                                is_fallback: true
                            };
                        } finally {
                            this.loadingCareer = false;
                        }
                    },

                    closeCareerModal() {
                        this.showCareerModal = false;
                        this.selectedCareer = null;
                    }
                }
            }
        </script>
    @endpush
@endsection