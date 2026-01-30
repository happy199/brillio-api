@extends('layouts.jeune')

@section('title', 'Mon Profil')

@section('content')
    <div x-data="profileData()">
        <div class="space-y-8">
            <!-- Header -->
            <div
                class="bg-gradient-to-r from-primary-600 to-purple-600 rounded-3xl p-8 text-white flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">Mon Profil</h1>
                    <p class="text-white/80 mt-2">Gérez vos informations et votre visibilité.</p>
                </div>
                <div class="flex gap-3">
                    @if($profile->is_public && $profile->public_slug)
                        <a href="{{ route('jeune.public.show', $profile->public_slug) }}" target="_blank"
                            class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-xl text-sm font-semibold transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Voir mon profil public
                        </a>
                    @endif
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Colonne Gauche -->
                <div class="lg:col-span-2 space-y-8">

                    <!-- 1. Informations Personnelles (Affichage) -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm relative group">
                        <div class="flex justify-between items-start mb-6">
                            <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                                <div
                                    class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                Informations Personnelles
                            </h2>
                            <button @click="editPersonal = true"
                                class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>
                        </div>

                        <div class="grid md:grid-cols-2 gap-y-6 gap-x-12">
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Nom complet</p>
                                <p class="text-lg font-medium text-gray-900">{{ $user->name }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Email</p>
                                <p class="text-lg font-medium text-gray-900">{{ $user->email }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Date de naissance</p>
                                <p class="text-lg font-medium text-gray-900">
                                    {{ $user->date_of_birth ? $user->date_of_birth->format('d/m/Y') : 'Non renseignée' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Localisation</p>
                                <p class="text-lg font-medium text-gray-900">
                                    {{ $user->city ? $user->city . ', ' : '' }}{{ $user->country }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Parcours & Liens (Affichage) -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm relative group">
                        <div class="flex justify-between items-start mb-6">
                            <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                                <div
                                    class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center text-purple-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                Parcours & Liens
                            </h2>
                            <button @click="editProfessional = true"
                                class="p-2 text-gray-400 hover:text-purple-600 hover:bg-purple-50 rounded-xl transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-semibold mb-2">Bio / À propos</p>
                                @if($profile->bio)
                                    <p class="text-gray-700 whitespace-pre-line">{{ $profile->bio }}</p>
                                @else
                                    <p class="text-gray-400 italic">Aucune bio. Ajoutez une description pour vous présenter.</p>
                                @endif
                            </div>

                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <p class="text-xs text-gray-500 uppercase font-semibold mb-2">LinkedIn</p>
                                    @if($user->linkedin_url)
                                        <a href="{{ $user->linkedin_url }}" target="_blank"
                                            class="flex items-center gap-2 text-blue-600 hover:underline">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                                            </svg>
                                            Voir le profil
                                        </a>
                                    @else
                                        <span class="text-gray-400">Non renseigné</span>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase font-semibold mb-2">Portfolio</p>
                                    @if($profile->portfolio_url)
                                        <a href="{{ $profile->portfolio_url }}" target="_blank"
                                            class="flex items-center gap-2 text-purple-600 hover:underline">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                            </svg>
                                            {{ Str::limit($profile->portfolio_url, 30) }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">Non renseigné</span>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 uppercase font-semibold mb-2">Curriculum Vitae</p>
                                @if($profile->cv_path)
                                    <a href="{{ Storage::url($profile->cv_path) }}" target="_blank"
                                        class="inline-flex items-center gap-3 p-3 bg-red-50 text-red-700 rounded-xl hover:bg-red-100 transition border border-red-100">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        <div>
                                            <span class="font-bold block">Consulter mon CV</span>
                                            <span class="text-xs opacity-75">Format PDF</span>
                                        </div>
                                    </a>
                                @else
                                    <span class="text-gray-400">Aucun CV uploadé</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- 3. Résultats Tests & Onboarding (Lecture Seule) -->
                    <div class="grid md:grid-cols-2 gap-8">
                        <!-- Test de Personnalité -->
                        <div class="bg-white rounded-2xl p-6 shadow-sm">
                            <div class="flex items-center gap-3 mb-4">
                                <div
                                    class="w-10 h-10 bg-pink-100 rounded-xl flex items-center justify-center text-pink-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                    </svg>
                                </div>
                                <h3 class="font-bold text-gray-900">Personnalité</h3>
                            </div>

                            @if($user->personalityTest && $user->personalityTest->personality_type)
                                <div class="text-center py-4">
                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1 block">
                                        {{ $user->personalityTest->personality_type }}
                                    </span>
                                    <h3 class="text-xl font-bold text-gray-900 mb-2">
                                        {{ $user->personalityTest->personality_label ?? $user->personalityTest->personality_type }}
                                    </h3>
                                    <p class="text-sm text-gray-500 line-clamp-3 px-4">
                                        {{ $user->personalityTest->personality_description }}
                                    </p>
                                </div>
                            @else
                                <div class="text-center py-6">
                                    <p class="text-gray-500 text-sm mb-3">Test non passé</p>
                                    <a href="{{ route('jeune.personality') }}"
                                        class="text-purple-600 font-bold text-sm hover:underline">Passer le test</a>
                                </div>
                            @endif
                        </div>

                        <!-- Infos Onboarding -->
                        <div class="bg-white rounded-2xl p-6 shadow-sm">
                            <div class="flex items-center gap-3 mb-4">
                                <div
                                    class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center text-green-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h3 class="font-bold text-gray-900">Profil Onboarding</h3>
                            </div>

                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">Situation</p>
                                    <p class="font-medium text-gray-900">
                                        {{ ucfirst($user->onboarding_data['current_situation'] ?? 'Non défini') }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">Niveau d'étude</p>
                                    <p class="font-medium text-gray-900">
                                        {{ ucfirst($user->onboarding_data['education_level'] ?? 'Non défini') }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">Intérêts</p>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @if(isset($user->onboarding_data['interests']))
                                            @foreach($user->onboarding_data['interests'] as $interest)
                                                <span
                                                    class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs">{{ $interest }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-gray-400 text-sm">-</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Colonne Droite: Public Toggle -->
                <div>
                    <div class="bg-white rounded-2xl p-6 shadow-sm sticky top-6">
                        <h3 class="font-bold text-gray-900 mb-6 flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Visibilité
                        </h3>

                        <form action="{{ route('jeune.profile.update') }}" method="POST">
                            @csrf
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-900">Profil Public</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="is_public" value="0">
                                    <input type="checkbox" name="is_public" value="1" class="sr-only peer" {{ $profile->is_public ? 'checked' : '' }} onchange="this.form.submit()">
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500">
                                    </div>
                                </label>
                            </div>
                            <p class="text-xs text-gray-500 mb-6">Activez cette option pour être visible par les mentors.
                            </p>

                            <!-- Inputs hidden pour conserver les autres données lors du submit auto -->
                            <input type="hidden" name="name" value="{{ $user->name }}">
                            <!-- On n'envoie que is_public pour ce form -->
                        </form>

                        <hr class="border-gray-100 my-6">

                        <div class="text-center">
                            <p class="text-3xl font-bold text-gray-900">{{ $profile->profile_views }}</p>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Vues totales</p>
                        </div>
                    </div>

                    <!-- Zone de Danger -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-red-200 mt-6">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-900 mb-2">Zone de danger</h3>
                                <p class="text-xs text-gray-600 mb-4">
                                    Cliquer sur ce bouton archivera vos données. Si aucune connexion ne survient pendant
                                    30 jours, le système supprimera définitivement votre compte. Vous devrez alors vous
                                    réinscrire.
                                </p>
                                <button @click="showDeleteConfirm = true"
                                    class="w-full px-4 py-2.5 bg-red-600 text-white text-sm font-semibold rounded-xl hover:bg-red-700 transition flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Supprimer mon compte
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- MODAL 1: Infos Personnelles -->
        <div x-show="editPersonal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" x-cloak
            x-transition>
            <div class="bg-white rounded-2xl max-w-lg w-full p-6" @click.away="editPersonal = false">
                <h3 class="text-xl font-bold mb-4">Modifier mes informations</h3>
                <form action="{{ route('jeune.profile.update') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet</label>
                            <input type="text" name="name" value="{{ $user->name }}"
                                class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 focus:outline-none transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date de naissance</label>
                            <input type="date" name="date_of_birth" value="{{ $user->date_of_birth?->format('Y-m-d') }}"
                                class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 focus:outline-none transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                            <input type="text" name="city" value="{{ $user->city }}"
                                class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 focus:outline-none transition">
                        </div>

                        <!-- Hidden fields to preserve other data -->
                        <input type="hidden" name="bio" value="{{ $profile->bio }}">
                        <input type="hidden" name="linkedin_url" value="{{ $user->linkedin_url }}">
                        <input type="hidden" name="portfolio_url" value="{{ $profile->portfolio_url }}">
                        <input type="hidden" name="is_public" value="{{ $profile->is_public ? '1' : '0' }}">
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="editPersonal = false"
                            class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-xl transition">Annuler</button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 2: Parcours & Liens -->
        <div x-show="editProfessional" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" x-cloak
            x-transition>
            <div class="bg-white rounded-2xl max-w-lg w-full p-6" @click.away="editProfessional = false">
                <h3 class="text-xl font-bold mb-4">Modifier mon parcours</h3>
                <form action="{{ route('jeune.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bio / À propos</label>
                            <textarea name="bio" rows="4"
                                class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 focus:outline-none transition">{{ $profile->bio }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Profil LinkedIn (URL)</label>
                            <input type="url" name="linkedin_url" value="{{ $user->linkedin_url }}"
                                class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 focus:outline-none transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Portfolio (URL)</label>
                            <input type="url" name="portfolio_url" value="{{ $profile->portfolio_url }}"
                                class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 focus:outline-none transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Upload CV (PDF)</label>
                            <input type="file" name="cv" accept=".pdf"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                        </div>

                        <!-- Hidden fields -->
                        <input type="hidden" name="name" value="{{ $user->name }}">
                        <input type="hidden" name="date_of_birth" value="{{ $user->date_of_birth?->format('Y-m-d') }}">
                        <input type="hidden" name="city" value="{{ $user->city }}">
                        <input type="hidden" name="is_public" value="{{ $profile->is_public ? '1' : '0' }}">
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="editProfessional = false"
                            class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-xl transition">Annuler</button>
                        <button type="submit"
                            class="px-4 py-2 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 3: Confirmation initiale de suppression -->
        <div x-show="showDeleteConfirm" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" x-cloak
            x-transition>
            <div class="bg-white rounded-3xl max-w-md w-full p-6" @click.away="showDeleteConfirm = false">
                <div class="flex items-center justify-center w-14 h-14 bg-red-100 rounded-full mx-auto mb-4">
                    <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Êtes-vous sûr ?</h3>
                <p class="text-gray-600 text-center mb-6">
                    Votre compte sera archivé et vous serez déconnecté. Vous avez 30 jours pour le réactiver en vous
                    reconnectant. Au-delà, il sera définitivement supprimé.
                </p>

                <div class="flex gap-3">
                    <button @click="showDeleteConfirm = false"
                        class="flex-1 py-3 border border-gray-300 rounded-xl font-medium text-gray-700 hover:bg-gray-50 transition">
                        Annuler
                    </button>
                    <button @click="showDeleteConfirm = false; showDeleteCode = true; generateCode()"
                        class="flex-1 py-3 bg-red-600 text-white font-semibold rounded-xl hover:bg-red-700 transition">
                        Continuer
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL 4: Validation par code -->
        <div x-show="showDeleteCode" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" x-cloak
            x-transition>
            <div class="bg-white rounded-3xl max-w-md w-full p-6" @click.away="showDeleteCode = false">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Confirmer la suppression</h3>
                <p class="text-gray-600 mb-4">
                    Pour confirmer, veuillez taper le code suivant :
                </p>

                <div class="bg-gray-100 rounded-xl p-4 mb-4 text-center">
                    <p class="text-2xl font-bold text-gray-900 tracking-wider" x-text="confirmationCode"></p>
                </div>

                <form @submit.prevent="submitArchive">
                    <div class="mb-4">
                        <input type="text" x-model="codeInput" placeholder="Tapez le code ici"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-red-500 focus:ring-2 focus:ring-red-200 focus:outline-none transition text-center text-lg font-semibold tracking-wider uppercase">
                        <p x-show="codeError" class="text-red-600 text-sm mt-2" x-text="codeError"></p>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" @click="showDeleteCode = false; codeInput = ''; codeError = ''"
                            class="flex-1 py-3 border border-gray-300 rounded-xl font-medium text-gray-700 hover:bg-gray-50 transition">
                            Annuler
                        </button>
                        <button type="submit" :disabled="codeInput !== confirmationCode"
                            :class="codeInput === confirmationCode ? 'bg-red-600 hover:bg-red-700' : 'bg-gray-300 cursor-not-allowed'"
                            class="flex-1 py-3 text-white font-semibold rounded-xl transition">
                            Archiver mon compte
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function profileData() {
                return {
                    editPersonal: false,
                    editProfessional: false,
                    showDeleteConfirm: false,
                    showDeleteCode: false,
                    confirmationCode: '',
                    codeInput: '',
                    codeError: '',

                    async generateCode() {
                        try {
                            const response = await fetch('{{ route("jeune.account.confirmation-code") }}');
                            const data = await response.json();
                            this.confirmationCode = data.code;
                        } catch (error) {
                            console.error('Error generating code:', error);
                            this.confirmationCode = 'ERROR-0000';
                        }
                    },

                    async submitArchive() {
                        if (this.codeInput !== this.confirmationCode) {
                            this.codeError = 'Le code ne correspond pas';
                            return;
                        }

                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route("jeune.account.archive") }}';

                        const csrfToken = document.createElement('input');
                        csrfToken.type = 'hidden';
                        csrfToken.name = '_token';
                        csrfToken.value = '{{ csrf_token() }}';
                        form.appendChild(csrfToken);

                        const codeField = document.createElement('input');
                        codeField.type = 'hidden';
                        codeField.name = 'confirmation_code';
                        codeField.value = this.codeInput;
                        form.appendChild(codeField);

                        document.body.appendChild(form);
                        form.submit();
                    }
                }
            }
        </script>

    </div>
@endsection