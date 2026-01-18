@extends('layouts.mentor')

@section('title', 'Mes statistiques')

@section('content')
    <div class="space-y-8">
        <!-- Header -->
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Mes statistiques</h1>
            <p class="text-gray-500">Suivez l'impact de votre profil sur la plateforme</p>
        </div>

        <!-- Profile Status -->
        @if(!$profile || !$profile->is_published)
            <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-5 flex items-start gap-4">
                <div class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-yellow-800">Profil non publie</h3>
                    <p class="text-yellow-700 text-sm mt-1">
                        Votre profil n'est pas encore visible par les jeunes. Publiez-le pour commencer a avoir des
                        statistiques.
                    </p>
                    <a href="{{ route('mentor.profile') }}"
                        class="inline-block mt-2 text-yellow-800 font-medium underline hover:no-underline">
                        Completer et publier mon profil
                    </a>
                </div>
            </div>
        @endif

        <!-- Stats Overview -->
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <div class="flex items-center gap-4">
                    <div
                        class="w-14 h-14 bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl flex items-center justify-center">
                        <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['profile_views'] ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Vues du profil</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t">
                    <p class="text-xs text-gray-400">Ce mois-ci</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <div class="flex items-center gap-4">
                    <div
                        class="w-14 h-14 bg-gradient-to-br from-orange-100 to-orange-200 rounded-xl flex items-center justify-center">
                        <svg class="w-7 h-7 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-gray-900">{{ $profile ? $profile->roadmapSteps()->count() : 0 }}
                        </p>
                        <p class="text-sm text-gray-500">Etapes roadmap</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t">
                    <a href="{{ route('mentor.roadmap') }}" class="text-xs text-orange-600 hover:underline">Gerer mon
                        parcours</a>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <div class="flex items-center gap-4">
                    <div
                        class="w-14 h-14 bg-gradient-to-br from-green-100 to-green-200 rounded-xl flex items-center justify-center">
                        <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-gray-900">{{ $profile && $profile->is_validated ? 'Oui' : 'Non' }}
                        </p>
                        <p class="text-sm text-gray-500">Profil verifie</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t">
                    @if($profile && $profile->is_validated)
                        <span class="text-xs text-green-600">Badge de verification actif</span>
                    @else
                        <span class="text-xs text-gray-400">En attente de verification</span>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <div class="flex items-center gap-4">
                    <div
                        class="w-14 h-14 bg-gradient-to-br from-purple-100 to-purple-200 rounded-xl flex items-center justify-center">
                        <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-gray-900">
                            {{ $profile && $profile->is_published ? 'Publie' : 'Brouillon' }}</p>
                        <p class="text-sm text-gray-500">Statut du profil</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t">
                    <a href="{{ route('mentor.profile') }}" class="text-xs text-purple-600 hover:underline">Modifier le
                        statut</a>
                </div>
            </div>
        </div>

        <!-- Profile Completeness -->
        <div class="bg-white rounded-2xl p-6 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900 mb-6">Completude du profil</h2>
            @php
                $completeness = 0;
                $items = [
                    ['label' => 'Bio professionnelle', 'done' => !empty($profile?->bio)],
                    ['label' => 'Poste actuel', 'done' => !empty($profile?->current_position)],
                    ['label' => 'Domaine d\'expertise', 'done' => !empty($profile?->specialization) || !empty($profile?->specialization_id)],
                    ['label' => 'Annees d\'experience', 'done' => !empty($profile?->years_of_experience)],
                    ['label' => 'Au moins une etape roadmap', 'done' => $profile?->roadmapSteps()?->count() > 0],
                    ['label' => 'Lien LinkedIn', 'done' => !empty($profile?->linkedin_url)],
                ];
                $completeness = count(array_filter($items, fn($i) => $i['done'])) / count($items) * 100;
            @endphp

            <div class="mb-6">
                <div class="flex justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Progression</span>
                    <span class="text-sm font-medium text-gray-900">{{ round($completeness) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="bg-gradient-to-r from-orange-500 to-red-500 h-3 rounded-full transition-all duration-500"
                        style="width: {{ $completeness }}%"></div>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                @foreach($items as $item)
                    <div class="flex items-center gap-3 p-3 {{ $item['done'] ? 'bg-green-50' : 'bg-gray-50' }} rounded-xl">
                        @if($item['done'])
                            <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-green-700 font-medium">{{ $item['label'] }}</span>
                        @else
                            <div class="w-6 h-6 bg-gray-300 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <span class="text-gray-600">{{ $item['label'] }}</span>
                        @endif
                    </div>
                @endforeach
            </div>

            @if($completeness < 100)
                <div class="mt-6 pt-6 border-t">
                    <a href="{{ route('mentor.profile') }}"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-gradient-to-r from-orange-500 to-red-500 text-white font-semibold rounded-xl hover:shadow-lg transition">
                        Completer mon profil
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            @endif
        </div>

        <!-- Tips -->
        <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-2xl p-6 border border-orange-200">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Conseils pour maximiser votre impact</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <span class="font-bold text-orange-600">1</span>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">Ajoutez une bio complete</p>
                        <p class="text-sm text-gray-600">Decrivez votre parcours et ce qui vous motive a aider les jeunes.
                        </p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <span class="font-bold text-orange-600">2</span>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">Detaillez votre roadmap</p>
                        <p class="text-sm text-gray-600">Plus vous ajoutez d'etapes, plus votre parcours sera inspirant.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <span class="font-bold text-orange-600">3</span>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">Ajoutez votre LinkedIn</p>
                        <p class="text-sm text-gray-600">Les jeunes pourront vous contacter directement.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <span class="font-bold text-orange-600">4</span>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">Partagez des conseils</p>
                        <p class="text-sm text-gray-600">Un conseil personnalise fait toute la difference.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection