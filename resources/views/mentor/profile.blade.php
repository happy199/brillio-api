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
        <a href="{{ route('public.mentor', $profile) }}" target="_blank"
           class="px-5 py-2 border border-gray-300 rounded-xl font-medium text-gray-700 hover:bg-gray-50 transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div>
            <h3 class="font-semibold text-yellow-800">Profil incomplet</h3>
            <p class="text-yellow-700 text-sm mt-1">Completez votre profil pour qu'il soit visible par les jeunes.</p>
        </div>
    </div>
    @endif

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
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Domaine d'expertise *</label>
                    <select name="specialization" required
                            class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <option value="">Selectionnez un domaine</option>
                        @foreach($specializations as $key => $label)
                        <option value="{{ $key }}" {{ old('specialization', $profile->specialization ?? '') === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Links -->
        <div class="bg-white rounded-2xl p-6 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900 mb-6">Liens</h2>
            <div class="grid sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Profil LinkedIn</label>
                    <div class="relative">
                        <svg class="w-5 h-5 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
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
                        <svg class="w-5 h-5 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
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
                <input type="checkbox" name="is_published" value="1"
                       {{ old('is_published', $profile->is_published ?? false) ? 'checked' : '' }}
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
