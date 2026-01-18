@extends('layouts.mentor')

@section('title', 'Mon profil mentor')

@section('content')
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Mon profil mentor</h1>
                <p class="text-gray-500">Gerez les informations visibles par les jeunes</p>
            </div>
            @if($profile && $profile->is_published)
                <a href="{{ route('public.mentor.profile', $profile->public_slug) }}" target="_blank"
                    class="px-5 py-2 border border-gray-300 rounded-xl font-medium text-gray-700 hover:bg-gray-50 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    Voir mon profil public
                </a>
            @endif
        </div>

        <!-- Profile Status Alert -->
        @if(!$profile || !$profile->isComplete())
            <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-5 flex items-start gap-4">
                <div class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-yellow-800">Profil incomplet</h3>
                    <p class="text-yellow-700 text-sm mt-1">Completez votre profil pour qu'il soit visible par les jeunes.</p>
                </div>
            </div>
        @endif

        <!-- LinkedIn Import Button -->
        <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-2xl p-5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-blue-900">Gagnez du temps !</h3>
                        <p class="text-sm text-blue-700">Importez votre profil LinkedIn pour remplir automatiquement vos
                            informations</p>
                    </div>
                </div>
                <button
                    onclick="const m=document.getElementById('linkedinImportModal'); m.classList.remove('hidden'); m.style.display=''"
                    class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition whitespace-nowrap">
                    📥 Importer LinkedIn
                </button>
            </div>
        </div>

        <form action="{{ route('mentor.profile.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Basic Info -->
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900 mb-6">Informations de base</h2>
                <div class="grid sm:grid-cols-2 gap-6">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bio professionnelle *</label>
                        <textarea name="bio" rows="4" required
                            class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 resize-none"
                            placeholder="Decrivez votre parcours et ce qui vous motive a aider les jeunes...">{{ old('bio', $profile->bio ?? '') }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Maximum 2000 caracteres</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Poste actuel *</label>
                        <input type="text" name="current_position" required
                            value="{{ old('current_position', $profile->current_position ?? '') }}"
                            class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500"
                            placeholder="Ex: Directeur Marketing">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Entreprise</label>
                        <input type="text" name="current_company"
                            value="{{ old('current_company', $profile->current_company ?? '') }}"
                            class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500"
                            placeholder="Ex: Google, Jumia, etc.">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Annees d'experience *</label>
                        <input type="number" name="years_of_experience" required min="0" max="50"
                            value="{{ old('years_of_experience', $profile->years_of_experience ?? '') }}"
                            class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500"
                            placeholder="10">
                    </div>
                    <div x-data="{ showNewSpecialization: false }">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Domaine d'expertise *</label>
                        <select name="specialization_id" required
                            x-on:change="showNewSpecialization = ($event.target.value === 'new')"
                            class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="">Sélectionnez un domaine</option>
                            @foreach($specializations as $spec)
                                <option value="{{ $spec->id }}" {{ old('specialization_id', $profile?->specialization_id) == $spec->id ? 'selected' : '' }}>
                                    {{ $spec->name }}
                                </option>
                            @endforeach
                            <option value="new" {{ old('specialization_id') === 'new' ? 'selected' : '' }}>
                                ➕ Autre (créer un nouveau domaine)
                            </option>
                        </select>

                        <!-- Champ conditionnel pour nouveau domaine -->
                        <div x-show="showNewSpecialization" x-cloak class="mt-3">
                            <input type="text" name="new_specialization_name"
                                placeholder="Nom du nouveau domaine d'expertise..."
                                value="{{ old('new_specialization_name') }}"
                                class="w-full px-4 py-3 border border-orange-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <p class="text-sm text-orange-600 mt-1">
                                ℹ️ Votre suggestion sera soumise à validation par un administrateur
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personality Test Section -->
            <div class="bg-white rounded-2xl p-6 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-10">
                    <svg class="w-24 h-24 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-gray-900 mb-6">Test de Personnalité (MBTI)</h2>

                @php
                    $pTest = auth()->user()->personalityTest;
                @endphp

                @if($pTest && $pTest->completed_at)
                    <div class="flex items-center gap-6">
                        <div
                            class="w-16 h-16 bg-purple-100 rounded-xl flex items-center justify-center text-purple-700 font-bold text-xl border-2 border-purple-200">
                            {{ $pTest->personality_type }}
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-900">{{ $pTest->personality_label ?? $pTest->personality_type }}</h3>
                            <p class="text-sm text-gray-500 line-clamp-2">{{ $pTest->personality_description }}</p>
                        </div>
                        <a href="{{ route('mentor.personality') }}"
                            class="px-4 py-2 bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100 transition font-medium text-sm">
                            Voir détails
                        </a>
                    </div>
                @else
                    <div class="flex flex-col sm:flex-row items-center gap-6">
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-900">Découvrez votre type de personnalité</h3>
                            <p class="text-sm text-gray-500 mt-1">
                                Le test MBTI vous aide à mieux comprendre vos forces en tant que mentor.
                                Cela enrichit votre profil pour les jeunes.
                            </p>
                        </div>
                        <a href="{{ route('mentor.personality') }}"
                            class="px-6 py-3 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition font-bold shadow-md hover:shadow-lg whitespace-nowrap">
                            Passer le test
                        </a>
                    </div>
                @endif
            </div>

            <!-- Links -->
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900 mb-6">Liens</h2>
                <div class="grid sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Profil LinkedIn</label>
                        <div class="relative">
                            <svg class="w-5 h-5 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2" fill="currentColor"
                                viewBox="0 0 24 24">
                                <path
                                    d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                            </svg>
                            <input type="url" name="linkedin_url"
                                value="{{ old('linkedin_url', $profile->linkedin_url ?? '') }}"
                                class="w-full pl-12 pr-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500"
                                placeholder="https://linkedin.com/in/votre-profil">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Site web personnel</label>
                        <div class="relative">
                            <svg class="w-5 h-5 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                            <input type="url" name="website_url"
                                value="{{ old('website_url', $profile->website_url ?? '') }}"
                                class="w-full pl-12 pr-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500"
                                placeholder="https://votre-site.com">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advice -->
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900 mb-6">Vos conseils</h2>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Un conseil pour les jeunes</label>
                    <textarea name="advice" rows="3"
                        class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 resize-none"
                        placeholder="Partagez un conseil qui vous a aide dans votre carriere...">{{ old('advice', $profile->advice ?? '') }}</textarea>
                </div>
            </div>

            <!-- Visibility -->
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900 mb-6">Visibilite</h2>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_published" value="1" {{ old('is_published', $profile->is_published ?? false) ? 'checked' : '' }}
                        class="w-5 h-5 text-orange-500 border-gray-300 rounded focus:ring-orange-500">
                    <div>
                        <p class="font-medium text-gray-900">Publier mon profil</p>
                        <p class="text-sm text-gray-500">Votre profil sera visible par tous les jeunes de la plateforme</p>
                    </div>
                </label>
            </div>

            <!-- Submit -->
            <div class="flex justify-end gap-4">
                <button type="submit"
                    class="px-8 py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white font-semibold rounded-xl hover:shadow-lg transition">
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
@endsection

@include('mentor.partials.linkedin-import-modal')