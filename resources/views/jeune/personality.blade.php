@extends('layouts.jeune')

@section('title', 'Test de personnalite MBTI')

@section('content')
<div class="space-y-8">
    @if($personalityTest && $personalityTest->completed_at)
        <!-- Results View -->
        <div class="bg-gradient-to-r from-purple-500 via-pink-500 to-orange-500 rounded-3xl p-8 text-white">
            <div class="flex flex-col md:flex-row md:items-center gap-6">
                <div class="w-24 h-24 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                    <span class="text-4xl font-bold">{{ $personalityTest->personality_type }}</span>
                </div>
                <div class="flex-1">
                    <h1 class="text-3xl font-bold mb-2">{{ $personalityTest->personality_label ?? $personalityTest->personality_type }}</h1>
                    <p class="text-white/90">{{ Str::limit($personalityTest->personality_description, 200) }}</p>
                    <p class="text-white/70 text-sm mt-2">Test passe le {{ $personalityTest->completed_at->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Traits Scores -->
        @if($personalityTest->traits_scores)
        <div class="bg-white rounded-2xl p-6 shadow-sm">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Tes dimensions de personnalite</h2>
            <div class="grid md:grid-cols-2 gap-6">
                @foreach($personalityTest->traits_scores as $trait => $score)
                <div>
                    <div class="flex justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">{{ $trait }}</span>
                        <span class="text-sm text-gray-500">{{ $score }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-gradient-to-r from-purple-500 to-pink-500 h-3 rounded-full transition-all duration-500" style="width: {{ $score }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Recommended Careers -->
        @if($personalityTest->recommended_careers)
        <div class="bg-white rounded-2xl p-6 shadow-sm">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Metiers recommandes pour toi</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($personalityTest->recommended_careers as $career)
                <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-4 hover:shadow-md transition">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mb-3">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="font-semibold text-gray-900">{{ $career }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-4">
            <a href="{{ route('jeune.chat') }}" class="flex-1 bg-gradient-to-r from-primary-500 to-secondary-500 text-white text-center py-4 rounded-xl font-semibold hover:shadow-lg transition">
                Discuter avec l'IA sur mes resultats
            </a>
            <a href="{{ route('jeune.mentors') }}" class="flex-1 bg-white border-2 border-gray-200 text-gray-700 text-center py-4 rounded-xl font-semibold hover:border-primary-500 hover:text-primary-600 transition">
                Voir des mentors dans mes domaines
            </a>
        </div>
    @else
        <!-- Test Not Started / In Progress -->
        <div class="bg-gradient-to-r from-primary-500 via-purple-500 to-pink-500 rounded-3xl p-8 text-white text-center">
            <div class="w-20 h-20 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold mb-4">Decouvre ton type de personnalite</h1>
            <p class="text-white/90 max-w-lg mx-auto mb-8">
                Le test MBTI t'aidera a mieux te connaitre et a decouvrir les metiers qui correspondent a ta personnalite.
            </p>
        </div>

        <!-- Test Info -->
        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl p-6 shadow-sm text-center">
                <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">10-15 minutes</h3>
                <p class="text-sm text-gray-500">Duree moyenne du test</p>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-sm text-center">
                <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">32 questions</h3>
                <p class="text-sm text-gray-500">Questions simples et rapides</p>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-sm text-center">
                <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">16 types</h3>
                <p class="text-sm text-gray-500">De personnalite possibles</p>
            </div>
        </div>

        <!-- MBTI Types Preview -->
        <div class="bg-white rounded-2xl p-6 shadow-sm">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Les 16 types de personnalite MBTI</h2>
            <div class="grid grid-cols-4 sm:grid-cols-8 gap-3">
                @php
                    $types = ['INTJ', 'INTP', 'ENTJ', 'ENTP', 'INFJ', 'INFP', 'ENFJ', 'ENFP', 'ISTJ', 'ISFJ', 'ESTJ', 'ESFJ', 'ISTP', 'ISFP', 'ESTP', 'ESFP'];
                @endphp
                @foreach($types as $type)
                <div class="aspect-square bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl flex items-center justify-center hover:from-purple-100 hover:to-pink-100 transition cursor-pointer">
                    <span class="font-bold text-gray-700 text-sm">{{ $type }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Start Test Button -->
        <div class="text-center" x-data="{ showTest: false }">
            <button @click="showTest = true" class="px-12 py-4 bg-gradient-to-r from-purple-500 to-pink-500 text-white font-bold text-lg rounded-full hover:shadow-xl transition-all duration-300">
                Commencer le test MBTI
            </button>
            <p class="text-sm text-gray-500 mt-3">Gratuit et sans engagement</p>

            <!-- Test Modal -->
            <div x-show="showTest" x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
                 x-transition>
                <div class="bg-white rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-hidden" @click.outside="showTest = false">
                    <div class="p-6 border-b">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-bold text-gray-900">Test de personnalite MBTI</h3>
                            <button @click="showTest = false" class="p-2 hover:bg-gray-100 rounded-full">
                                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="p-6 text-center">
                        <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900 mb-2">Version mobile recommandee</h4>
                        <p class="text-gray-600 mb-6">
                            Pour une meilleure experience, nous te recommandons de passer le test sur l'application mobile Brillio.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-3 justify-center">
                            <a href="#" class="px-6 py-3 bg-black text-white rounded-xl flex items-center justify-center gap-2">
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                                </svg>
                                App Store
                            </a>
                            <a href="#" class="px-6 py-3 bg-black text-white rounded-xl flex items-center justify-center gap-2">
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.6 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.16,10.81C20.5,11.08 20.75,11.5 20.75,12C20.75,12.5 20.53,12.9 20.18,13.18L17.89,14.5L15.39,12L17.89,9.5L20.16,10.81M6.05,2.66L16.81,8.88L14.54,11.15L6.05,2.66Z"/>
                                </svg>
                                Google Play
                            </a>
                        </div>
                        <div class="mt-6 pt-6 border-t">
                            <p class="text-sm text-gray-500 mb-3">Ou continue sur le web (beta)</p>
                            <button class="text-primary-600 font-medium hover:underline">
                                Passer le test sur navigateur
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
