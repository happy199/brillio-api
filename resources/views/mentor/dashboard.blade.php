@extends('layouts.mentor')

@section('title', 'Mon espace mentor')

@section('content')
<div class="space-y-8">
    <!-- Welcome Header -->
    <div class="bg-gradient-to-r from-orange-500 via-red-500 to-pink-500 rounded-3xl p-8 text-white">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <h1 class="text-3xl font-bold">Bienvenue, {{ explode(' ', $user->name)[0] }} !</h1>
                <p class="text-white/80 mt-2">Merci de partager votre parcours avec la jeunesse africaine</p>
            </div>
            <div class="flex gap-3">
                @if(!$stats['profile_complete'])
                <a href="{{ route('mentor.profile') }}"
                   class="px-5 py-3 bg-white text-orange-600 font-semibold rounded-xl hover:bg-gray-50 transition shadow-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Completer mon profil
                </a>
                @endif
                <a href="{{ route('mentor.roadmap') }}"
                   class="px-5 py-3 bg-white/20 text-white font-semibold rounded-xl hover:bg-white/30 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    Editer mon parcours
                </a>
            </div>
        </div>
    </div>

    <!-- Profile Status -->
    @if(!$stats['is_published'])
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <h3 class="font-bold text-amber-800">Votre profil n'est pas encore visible</h3>
                <p class="text-amber-700 text-sm mt-1">
                    Pour etre visible par les jeunes, completez votre profil (bio, poste, specialisation) et ajoutez au moins une etape a votre parcours.
                </p>
                <div class="mt-4 flex flex-wrap gap-2">
                    @if(!$profile || !$profile->bio)
                    <span class="px-3 py-1 bg-amber-100 text-amber-700 text-xs rounded-full">Bio manquante</span>
                    @endif
                    @if(!$profile || !$profile->current_position)
                    <span class="px-3 py-1 bg-amber-100 text-amber-700 text-xs rounded-full">Poste manquant</span>
                    @endif
                    @if(!$profile || !$profile->specialization)
                    <span class="px-3 py-1 bg-amber-100 text-amber-700 text-xs rounded-full">Specialisation manquante</span>
                    @endif
                    @if($stats['roadmap_steps'] === 0)
                    <span class="px-3 py-1 bg-amber-100 text-amber-700 text-xs rounded-full">Parcours vide</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="bg-green-50 border border-green-200 rounded-2xl p-6">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h3 class="font-bold text-green-800">Votre profil est visible !</h3>
                <p class="text-green-700 text-sm">Les jeunes peuvent voir votre parcours et s'en inspirer.</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 shadow-sm card-hover">
            <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </div>
            <p class="text-sm text-gray-500">Vues du profil</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['profile_views'] }}</p>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm card-hover">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <p class="text-sm text-gray-500">Etapes parcours</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['roadmap_steps'] }}</p>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm card-hover">
            <div class="w-12 h-12 {{ $stats['is_published'] ? 'bg-green-100' : 'bg-gray-100' }} rounded-xl flex items-center justify-center mb-3">
                <svg class="w-6 h-6 {{ $stats['is_published'] ? 'text-green-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <p class="text-sm text-gray-500">Statut</p>
            <p class="text-lg font-bold {{ $stats['is_published'] ? 'text-green-600' : 'text-gray-500' }}">
                {{ $stats['is_published'] ? 'Publie' : 'Brouillon' }}
            </p>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm card-hover">
            <div class="w-12 h-12 {{ $stats['profile_complete'] ? 'bg-green-100' : 'bg-yellow-100' }} rounded-xl flex items-center justify-center mb-3">
                <svg class="w-6 h-6 {{ $stats['profile_complete'] ? 'text-green-600' : 'text-yellow-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <p class="text-sm text-gray-500">Profil</p>
            <p class="text-lg font-bold {{ $stats['profile_complete'] ? 'text-green-600' : 'text-yellow-600' }}">
                {{ $stats['profile_complete'] ? 'Complet' : 'A completer' }}
            </p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid md:grid-cols-2 gap-6">
        <!-- Editer profil -->
        <a href="{{ route('mentor.profile') }}" class="bg-white rounded-2xl p-6 shadow-sm card-hover group">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-orange-500 to-red-500 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-900">Mon profil public</h3>
                    <p class="text-gray-500 text-sm mt-1">Modifiez votre bio, poste actuel, entreprise et specialisation pour vous faire connaitre.</p>
                    <div class="mt-3 flex items-center text-orange-600 font-medium text-sm">
                        Editer mon profil
                        <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </div>
        </a>

        <!-- Editer parcours -->
        <a href="{{ route('mentor.roadmap') }}" class="bg-white rounded-2xl p-6 shadow-sm card-hover group">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-900">Mon parcours</h3>
                    <p class="text-gray-500 text-sm mt-1">Racontez votre histoire etape par etape pour inspirer les jeunes qui cherchent leur voie.</p>
                    <div class="mt-3 flex items-center text-blue-600 font-medium text-sm">
                        Gerer mon parcours
                        <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Tips -->
    <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-2xl p-6">
        <h3 class="font-bold text-gray-900 mb-4">Conseils pour un profil impactant</h3>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center shadow-sm flex-shrink-0">
                    <span class="text-lg">1</span>
                </div>
                <p class="text-sm text-gray-600">Redigez une bio authentique qui raconte votre motivation</p>
            </div>
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center shadow-sm flex-shrink-0">
                    <span class="text-lg">2</span>
                </div>
                <p class="text-sm text-gray-600">Detaillez chaque etape cle de votre parcours professionnel</p>
            </div>
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center shadow-sm flex-shrink-0">
                    <span class="text-lg">3</span>
                </div>
                <p class="text-sm text-gray-600">Partagez vos defis et comment vous les avez surmontes</p>
            </div>
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center shadow-sm flex-shrink-0">
                    <span class="text-lg">4</span>
                </div>
                <p class="text-sm text-gray-600">Donnez des conseils concrets aux jeunes qui debutent</p>
            </div>
        </div>
    </div>
</div>
@endsection
