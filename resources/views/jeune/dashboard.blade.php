@extends('layouts.jeune')

@section('title', 'Mon espace')

@section('content')
<div class="space-y-8">
    <!-- Welcome Header -->
    <div class="bg-gradient-to-r from-primary-500 via-purple-500 to-pink-500 rounded-3xl p-8 text-white">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <h1 class="text-3xl font-bold">Bonjour, {{ explode(' ', $user->name)[0] }} !</h1>
                <p class="text-white/80 mt-2">Prêt à explorer de nouvelles opportunités aujourd'hui ?</p>
            </div>
            <div class="flex gap-3">
                @if(!$stats['personality_completed'])
                <a href="{{ route('jeune.personality') }}"
                    class="px-5 py-3 bg-white text-primary-600 font-semibold rounded-xl hover:bg-gray-50 transition shadow-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    <span class="hidden sm:inline">Passer le test MBTI</span>
                    <span class="sm:hidden">Test MBTI</span>
                </a>
                @endif
                <a href="{{ route('jeune.mentors') }}"
                    class="px-5 py-3 bg-white/20 text-white font-semibold rounded-xl hover:bg-white/30 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span class="hidden sm:inline">Trouver mon mentor</span>
                    <span class="sm:hidden">Mon mentor</span>
                </a>
                <a href="{{ route('jeune.chat') }}"
                    class="px-5 py-3 bg-white/20 text-white font-semibold rounded-xl hover:bg-white/30 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    Discuter avec l'IA
                </a>
            </div>
        </div>
    </div>


    <!-- Profile Status -->
    @if(!($user->jeuneProfile?->is_public))
    <div class="bg-purple-50 border border-purple-200 rounded-2xl p-6">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <h3 class="font-bold text-purple-800">Votre profil n'est pas encore visible</h3>
                <p class="text-purple-700 text-sm mt-1">
                    Pour être contacté par des mentors, complétez votre profil (bio, CV) et rendez-le visible.
                </p>
                <div class="mt-4 flex flex-wrap gap-2">
                    @if(!$user->jeuneProfile || !$user->jeuneProfile->bio)
                    <span class="px-3 py-1 bg-purple-100 text-purple-700 text-xs rounded-full">Bio manquante</span>
                    @endif
                    @if(!$user->jeuneProfile || !$user->jeuneProfile->cv_path)
                    <span class="px-3 py-1 bg-purple-100 text-purple-700 text-xs rounded-full">CV manquant</span>
                    @endif
                </div>

                <!-- Bouton Publier -->
                <form action="{{ route('jeune.profile.publish') }}" method="POST" class="mt-4">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 active:bg-purple-800 focus:outline-none focus:border-purple-800 focus:ring ring-purple-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Rendre mon profil visible
                    </button>
                </form>
            </div>
        </div>
    </div>
    @else
    <div x-data="{
        hasPhone: {{ !empty($user->phone) ? 'true' : 'false' }},
        showInput: false,
        phone: '{{ $user->phone ?? '' }}',
        submitting: false,
        error: '',
        async savePhone() {
            if (!this.phone.trim()) {
                this.error = 'Veuillez saisir un numéro de téléphone.';
                return;
            }
            this.submitting = true;
            this.error = '';
            try {
                const response = await fetch('{{ route('jeune.profile.update') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ phone: this.phone })
                });
                const data = await response.json();
                if (response.ok && data.success) {
                    this.hasPhone = true;
                } else {
                    this.error = data.message || 'Une erreur est survenue.';
                }
            } catch (e) {
                this.error = 'Erreur de connexion.';
            } finally {
                this.submitting = false;
            }
        }
    }">
        <!-- Green block: profile is public and phone number is set -->
        <div x-show="hasPhone" class="bg-green-50 border border-green-200 rounded-2xl p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-green-800">Votre profil est visible !</h3>
                    <p class="text-green-700 text-sm">Les mentors peuvent désormais consulter votre profil.</p>
                </div>
            </div>
        </div>

        <!-- Yellow block: profile is public but phone number is missing -->
        <div x-show="!hasPhone" class="bg-amber-50 border border-amber-200 rounded-2xl p-6" style="display: none;">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start gap-4 flex-1">
                    <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-amber-800">Il manque votre numéro de téléphone</h3>
                        <p class="text-amber-700 text-sm mt-1">
                            Il est utilisé pour vous proposer les meilleures offres.
                        </p>
                        <p x-show="error" class="text-red-600 text-xs mt-1 font-semibold" x-text="error" style="display: none;"></p>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-shrink-0">
                    <div x-show="showInput" class="flex items-center gap-2" style="display: none;">
                        <label for="phone_input" class="sr-only">Numéro de téléphone</label>
                        <input type="tel" id="phone_input" x-model="phone" placeholder="Ex: +22997000000"
                            class="px-3 py-2 border border-amber-300 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none text-gray-900 bg-white h-10 w-48"
                            @keydown.enter="savePhone()" :disabled="submitting">
                        <button @click="savePhone()" :disabled="submitting"
                            class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-xl shadow-sm transition disabled:opacity-50 h-10 flex items-center justify-center min-w-[100px]">
                            <span x-show="!submitting">Enregistrer</span>
                            <span x-show="submitting" style="display: none;">...</span>
                        </button>
                        <button @click="showInput = false" :disabled="submitting" class="text-amber-700 hover:text-amber-900 text-xs px-2 font-medium">
                            Annuler
                        </button>
                    </div>

                    <button x-show="!showInput" @click="showInput = true; $nextTick(() => $el.previousElementSibling.querySelector('input').focus())"
                        class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold rounded-xl shadow-md hover:shadow-lg transition-all h-10">
                        Je renseigne mon numéro
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 shadow-sm card-hover">
            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
            </div>
            <p class="text-sm text-gray-500">Test de personnalité</p>
            <p class="text-lg font-bold text-gray-900">
                @if($stats['personality_completed'])
                {{ $user->personalityTest->personality_type }}
                @else
                Non passe
                @endif
            </p>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm card-hover">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
            </div>
            <p class="text-sm text-gray-500">Conversations</p>
            <p class="text-lg font-bold text-gray-900">{{ $stats['conversations_count'] }}</p>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm card-hover">
            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <p class="text-sm text-gray-500">Documents</p>
            <p class="text-lg font-bold text-gray-900">{{ $stats['documents_count'] }}</p>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm card-hover">
            <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                </svg>
            </div>
            <p class="text-sm text-gray-500">Messages IA</p>
            <p class="text-lg font-bold text-gray-900">{{ $stats['messages_count'] }}</p>
        </div>
    </div>

    <!-- Visibilité Profil -->
    <div class="space-y-4">
        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            Visibilité de mon profil
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-5 shadow-sm card-hover border-l-4 border-purple-500">
                <p class="text-sm text-gray-500 mb-1">Vues Totales</p>
                <p class="text-3xl font-bold text-gray-900">{{ $stats['profile_views'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm card-hover border-l-4 border-pink-500">
                <p class="text-sm text-gray-500 mb-1">Vues par Mentors</p>
                <p class="text-3xl font-bold text-gray-900">{{ $stats['mentor_views'] }}</p>
            </div>
            <!-- Call to action modify profile -->
            <a href="{{ route('jeune.profile') }}"
                class="md:col-span-2 bg-gradient-to-r from-gray-900 to-gray-800 rounded-2xl p-5 shadow-sm text-white flex items-center justify-between group hover:shadow-lg transition">
                <div>
                    <p class="font-bold text-lg group-hover:text-purple-300 transition-colors">Gérer mon profil public
                    </p>
                    <p class="text-white/70 text-sm mt-1">Modifiez votre bio, CV et visibilité pour attirer les mentors.
                    </p>
                </div>
                <div
                    class="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center group-hover:bg-white/20 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid md:grid-cols-3 gap-6">
        <!-- Test de personnalite -->
        <a href="{{ route('jeune.personality') }}" class="bg-white rounded-2xl p-6 shadow-sm card-hover group">
            <div
                class="w-14 h-14 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900">Test de personnalité</h3>
            <p class="text-gray-500 text-sm mt-1">Découvrez votre type MBTI et les carrières qui vous correspondent</p>
            <div class="mt-4 flex items-center text-primary-600 font-medium text-sm">
                @if($stats['personality_completed'])
                Voir mon resultat
                @else
                Passer le test
                @endif
                <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </a>

        <!-- Chat IA -->
        <a href="{{ route('jeune.chat') }}" class="bg-white rounded-2xl p-6 shadow-sm card-hover group">
            <div
                class="w-14 h-14 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900">Assistant IA</h3>
            <p class="text-gray-500 text-sm mt-1">Posez vos questions sur l'orientation et obtenez des conseils
                personnalisés</p>
            <div class="mt-4 flex items-center text-primary-600 font-medium text-sm">
                Demarrer une discussion
                <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </a>

        <!-- Mentors -->
        <a href="{{ route('jeune.mentors') }}" class="bg-white rounded-2xl p-6 shadow-sm card-hover group">
            <div
                class="w-14 h-14 bg-gradient-to-br from-orange-500 to-red-500 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900">Explorer les mentors</h3>
            <p class="text-gray-500 text-sm mt-1">Découvrez les parcours inspirants de professionnels africains</p>
            <div class="mt-4 flex items-center text-primary-600 font-medium text-sm">
                Voir les mentors
                <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </a>

        <!-- Update Situation (Progressive Profiling CTA) -->
        <div x-data @click="$dispatch('open-profiling-modal')" class="bg-white rounded-2xl p-6 shadow-sm card-hover group text-left cursor-pointer">
            <div
                class="w-14 h-14 bg-gradient-to-br from-green-500 to-emerald-500 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900">Mettre à jour ma situation</h3>
            <p class="text-gray-500 text-sm mt-1">Ta vie change ? Tes infos aussi ! Ça nous aide à mieux t'orienter</p>
            <div class="mt-4 flex items-center text-primary-600 font-medium text-sm">
                Mettre à jour
                <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </div>
    </div>

    @include('jeune.partials.profiling-modal')

    <!-- Mentors recommandes -->
    @if($recommendedMentors->count() > 0)
    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-900">Mentors recommandes</h2>
            <a href="{{ route('jeune.mentors') }}" class="text-primary-600 font-medium text-sm hover:underline">Voir
                tous</a>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($recommendedMentors as $mentor)
            <a href="{{ route('jeune.mentors.show', $mentor->public_slug) }}"
                class="bg-white rounded-2xl p-5 shadow-sm card-hover block">
                <div class="flex items-center gap-3 mb-3">
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-orange-400 to-red-500 flex items-center justify-center">
                        @if($mentor->user && $mentor->user->avatar_url)
                        <img src="{{ $mentor->user->avatar_url }}" alt="" class="w-12 h-12 rounded-xl object-cover">
                        @else
                        <span class="text-white font-bold">{{ strtoupper(substr($mentor->user->name ?? '?', 0, 1))
                            }}</span>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-900 truncate">{{ $mentor->user->name ?? 'Mentor' }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $mentor->current_position ?? '' }}</p>
                    </div>
                </div>
                @if($mentor->specialization)
                <span class="inline-block px-2 py-1 bg-orange-100 text-orange-700 text-xs rounded-full">
                    {{ $mentor->specialization_label }}
                </span>
                @endif
            </a>
            @endforeach
        </div>
        </div>
    </div>
    @endif

    <!-- Ressources recommandées (Carrousel) -->
    @if(isset($recommendedResources) && $recommendedResources->count() > 0)
    <div class="mt-8 mb-8" x-data="{
        scrollResourceCarousel(dir) {
            const el = this.$refs.resCarousel;
            const scrollAmount = el.clientWidth * 0.8;
            el.scrollBy({ left: scrollAmount * dir, behavior: 'smooth' });
        }
    }">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Ressources pour vous</h2>
                <p class="text-sm text-gray-500">Sélection personnalisée basée sur votre profil</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('jeune.resources.index') }}" class="text-primary-600 font-medium text-sm hover:underline mr-2 hidden sm:block">Tout explorer</a>
                <div class="flex gap-2">
                    <button @click="scrollResourceCarousel(-1)" class="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:border-indigo-200 transition shadow-sm group">
                        <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </button>
                    <button @click="scrollResourceCarousel(1)" class="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:border-indigo-200 transition shadow-sm group">
                        <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                </div>
            </div>
        </div>

        <style>
            .no-scrollbar::-webkit-scrollbar { display: none; }
            .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        </style>

        <div x-ref="resCarousel" class="flex gap-4 overflow-x-auto pb-4 snap-x no-scrollbar scroll-smooth">
            @foreach($recommendedResources as $resource)
            <div class="flex-none w-[280px] snap-start">
                <a href="{{ route('jeune.resources.show', $resource->slug) }}" class="bg-white rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow group flex flex-col h-full border border-gray-100">
                    <div class="aspect-video bg-gray-100 rounded-xl mb-4 overflow-hidden relative">
                        @if($resource->preview_image_path)
                        <img src="{{ Storage::url($resource->preview_image_path) }}" alt="{{ $resource->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                        <div class="w-full h-full flex items-center justify-center bg-indigo-50">
                            <svg class="w-10 h-10 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        @endif
                        @if($resource->is_premium)
                        <div class="absolute top-2 right-2 bg-gradient-to-r from-amber-400 to-orange-500 text-white text-[10px] font-black uppercase px-2 py-1 rounded-lg shadow-sm">
                            Premium
                        </div>
                        @else
                        <div class="absolute top-2 right-2 bg-emerald-500 text-white text-[10px] font-black uppercase px-2 py-1 rounded-lg shadow-sm">
                            Gratuit
                        </div>
                        @endif
                    </div>
                    
                    <h3 class="font-bold text-gray-900 mb-1 line-clamp-2 group-hover:text-indigo-600 transition-colors text-sm">{{ $resource->title }}</h3>
                    
                    <div class="mt-auto pt-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            @if($resource->user->avatar_url)
                            <img src="{{ $resource->user->avatar_url }}" class="w-6 h-6 rounded-full object-cover">
                            @else
                            <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-[10px] font-bold text-gray-600">
                                {{ substr($resource->user->name, 0, 1) }}
                            </div>
                            @endif
                            <span class="text-xs font-medium text-gray-600 truncate max-w-[100px]">{{ $resource->user->name }}</span>
                        </div>
                        @if($resource->quizzes_count > 0)
                        <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-md flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Quiz
                        </span>
                        @endif
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Formations recommandées -->
    @if($stats['personality_completed'])
        @include('shared.partials.formations-carousel', ['mbtiType' => $user->personalityTest->personality_type])
        @include('shared.partials.formations-drawer')
    @endif
</div>
@push('scripts')
    @if($stats['personality_completed'])
        @include('shared.partials.formations-script')
    @endif
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        /**
         * Optimisation Brillio : Pré-chargement "Invisible" du test de personnalité.
         * Lance un appel API en arrière-plan pour que le test soit instantané au clic.
         */
        (function() {
            const prewarmMBTI = async () => {
                const cacheKey = 'mbti_questions_cache';
                
                // Si on a déjà les questions en cache local, on ne fait rien
                if (localStorage.getItem(cacheKey)) {
                    console.log('%c[Brillio Opti] Questions MBTI déjà présentes en cache local.', 'color: #6366f1; font-weight: bold;');
                    return;
                }

                // Délai de 5 secondes pour laisser le dashboard se charger tranquillement
                setTimeout(async () => {
                    console.log('%c[Brillio Opti] Lancement du pré-chargement invisible...', 'color: #6366f1; font-weight: bold;');
                    try {
                        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '{{ csrf_token() }}';
                        
                        const response = await fetch('{{ route("jeune.personality.questions.dynamic") }}', {
                            headers: { 
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        });
                        
                        const data = await response.json();
                        
                        if (data.success && data.questions) {
                            localStorage.setItem(cacheKey, JSON.stringify({
                                questions: data.questions,
                                timestamp: new Date().getTime()
                            }));
                            console.log('%c[Brillio Opti] Questions stockées en cache local avec succès.', 'color: #10b981; font-weight: bold;');
                        }
                    } catch (e) {
                        console.error('[Brillio Opti] Échec du pré-chargement:', e);
                    }
                }, 2000);
            };

            if (document.readyState === 'complete') {
                prewarmMBTI();
            } else {
                window.addEventListener('load', prewarmMBTI);
            }
        })();
    </script>
@endpush
@endsection