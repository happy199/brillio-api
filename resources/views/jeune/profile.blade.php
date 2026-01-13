@extends('layouts.jeune')

@section('title', 'Mon profil')

@section('content')
    <div class="space-y-8">
        <!-- Profile Header -->
        <div class="bg-gradient-to-r from-primary-500 via-purple-500 to-pink-500 rounded-3xl p-8 text-white">
            <div class="flex flex-col md:flex-row md:items-center gap-6">
                <div class="relative">
                    <div
                        class="w-24 h-24 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center overflow-hidden">
                        @if($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" alt="" class="w-full h-full object-cover">
                        @else
                            <span class="text-4xl font-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        @endif
                    </div>
                    <button
                        class="absolute -bottom-2 -right-2 w-8 h-8 bg-white text-primary-600 rounded-full flex items-center justify-center shadow-lg hover:bg-gray-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </button>
                </div>
                <div class="flex-1">
                    <h1 class="text-2xl font-bold">{{ $user->name }}</h1>
                    <p class="text-white/80">{{ $user->email }}</p>
                    <div class="flex flex-wrap gap-2 mt-3">
                        @if($user->country)
                            <span class="px-3 py-1 bg-white/20 rounded-full text-sm">{{ $user->country }}</span>
                        @endif
                        @if($user->personalityTest && $user->personalityTest->completed_at)
                            <span
                                class="px-3 py-1 bg-white/20 rounded-full text-sm">{{ $user->personalityTest->personality_type }}</span>
                        @endif
                        <span class="px-3 py-1 bg-white/20 rounded-full text-sm">Membre depuis
                            {{ $user->created_at->format('M Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Left Column - Profile Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Personal Information -->
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-bold text-gray-900">Informations personnelles</h2>
                        <button onclick="document.getElementById('editProfileModal').classList.remove('hidden')"
                            class="text-primary-600 font-medium text-sm hover:underline">
                            Modifier
                        </button>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Nom complet</p>
                            <p class="font-medium text-gray-900">{{ $user->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Email</p>
                            <p class="font-medium text-gray-900">{{ $user->email }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Date de naissance</p>
                            <p class="font-medium text-gray-900">
                                {{ $user->birth_date ? $user->birth_date->format('d/m/Y') : 'Non renseigne' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Telephone</p>
                            <p class="font-medium text-gray-900">{{ $user->phone ?? 'Non renseigne' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Pays</p>
                            <p class="font-medium text-gray-900">{{ $user->country ?? 'Non renseigne' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Ville</p>
                            <p class="font-medium text-gray-900">{{ $user->city ?? 'Non renseigne' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Education & Interests -->
                @if($user->onboarding_data)
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-gray-900 mb-6">Parcours et interets</h2>
                        <div class="space-y-6">
                            @if(isset($user->onboarding_data['education_level']))
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Niveau d'etudes</p>
                                    <p class="font-medium text-gray-900">{{ $user->onboarding_data['education_level'] }}</p>
                                </div>
                            @endif
                            @if(isset($user->onboarding_data['current_situation']))
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Situation actuelle</p>
                                    <p class="font-medium text-gray-900">{{ $user->onboarding_data['current_situation'] }}</p>
                                </div>
                            @endif
                            @if(isset($user->onboarding_data['interests']) && is_array($user->onboarding_data['interests']))
                                <div>
                                    <p class="text-sm text-gray-500 mb-2">Centres d'interet</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($user->onboarding_data['interests'] as $interest)
                                            <span
                                                class="px-3 py-1 bg-primary-100 text-primary-700 text-sm rounded-full">{{ $interest }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            @if(isset($user->onboarding_data['goals']) && is_array($user->onboarding_data['goals']))
                                <div>
                                    <p class="text-sm text-gray-500 mb-2">Objectifs</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($user->onboarding_data['goals'] as $goal)
                                            <span class="px-3 py-1 bg-green-100 text-green-700 text-sm rounded-full">{{ $goal }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Personality Test -->
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-6">Test de personnalite</h2>
                    @if($user->personalityTest && $user->personalityTest->completed_at)
                        <div class="flex items-center gap-4">
                            <div
                                class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center">
                                <span class="text-xl font-bold text-white">{{ $user->personalityTest->personality_type }}</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900">
                                    {{ $user->personalityTest->personality_label ?? $user->personalityTest->personality_type }}
                                </p>
                                <p class="text-sm text-gray-500">Passe le
                                    {{ $user->personalityTest->completed_at->format('d/m/Y') }}</p>
                            </div>
                            <a href="{{ route('jeune.personality') }}" class="text-primary-600 font-medium hover:underline">
                                Voir les details
                            </a>
                        </div>
                    @else
                        <div class="text-center py-6">
                            <div class="w-16 h-16 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                            </div>
                            <p class="text-gray-600 mb-4">Tu n'as pas encore passe le test de personnalite</p>
                            <a href="{{ route('jeune.personality') }}"
                                class="px-6 py-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold rounded-lg hover:shadow-lg transition">
                                Passer le test MBTI
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Column - Stats & Settings -->
            <div class="space-y-6">
                <!-- Stats -->
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Statistiques</h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Conversations</span>
                            <span class="font-bold text-gray-900">{{ $user->chatConversations()->count() }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Messages</span>
                            <span
                                class="font-bold text-gray-900">{{ $user->chatConversations()->withCount('messages')->get()->sum('messages_count') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Documents</span>
                            <span class="font-bold text-gray-900">{{ $user->academicDocuments()->count() }}</span>
                        </div>
                    </div>
                </div>

                <!-- Account Settings -->
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Compte</h2>
                    <div class="space-y-3">
                        <a href="{{ route('jeune.password.change') }}"
                            class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                                <span class="text-gray-700">Changer le mot de passe</span>
                            </div>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="#" class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span class="text-gray-700">Notifications</span>
                            </div>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ route('privacy-policy') }}"
                            class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span class="text-gray-700">Politique de confidentialité</span>
                            </div>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-red-100">
                    <h2 class="text-lg font-bold text-red-600 mb-4">Zone de danger</h2>
                    <p class="text-sm text-gray-600 mb-4">Une fois supprime, ton compte et toutes tes donnees seront
                        definitivement perdus.</p>
                    <button
                        class="w-full py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50 transition font-medium">
                        Supprimer mon compte
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div id="editProfileModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-3xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b sticky top-0 bg-white rounded-t-3xl">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-900">Modifier mon profil</h3>
                    <button onclick="document.getElementById('editProfileModal').classList.add('hidden')"
                        class="p-2 hover:bg-gray-100 rounded-full">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <form action="{{ route('jeune.profile.update') }}" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nom complet</label>
                    <input type="text" name="name" value="{{ $user->name }}" required
                        class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date de naissance</label>
                    <input type="date" name="birth_date" value="{{ $user->birth_date?->format('Y-m-d') }}"
                        class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Telephone</label>
                    <input type="tel" name="phone" value="{{ $user->phone }}" placeholder="+221 77 000 00 00"
                        class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pays</label>
                        <input type="text" name="country" value="{{ $user->country }}"
                            class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ville</label>
                        <input type="text" name="city" value="{{ $user->city }}"
                            class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="document.getElementById('editProfileModal').classList.add('hidden')"
                        class="flex-1 py-3 border rounded-xl font-medium text-gray-700 hover:bg-gray-50 transition">
                        Annuler
                    </button>
                    <button type="submit"
                        class="flex-1 py-3 bg-gradient-to-r from-primary-500 to-secondary-500 text-white font-semibold rounded-xl hover:shadow-lg transition">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection