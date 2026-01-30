@extends('layouts.admin')

@section('title', 'Modifier le profil - ' . $mentor->user->name)

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Modifier le profil mentor</h1>
            <p class="text-gray-600 mt-1">{{ $mentor->user->name }}</p>
        </div>
        <a href="{{ route('admin.mentors.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
            ← Retour à la liste
        </a>
    </div>

    <!-- Messages de succès/erreur -->
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-md">
            <p class="text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
            <p class="text-red-800">{{ session('error') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
            <ul class="list-disc list-inside text-red-800">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.mentors.update', $mentor) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Colonne principale -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Informations personnelles -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Informations personnelles</h2>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet *</label>
                            <input type="text" name="name" value="{{ old('name', $mentor->user->name) }}" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                            <input type="email" name="email" value="{{ old('email', $mentor->user->email) }}" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                            <input type="text" name="phone" value="{{ old('phone', $mentor->user->phone) }}" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                            <input type="text" name="city" value="{{ old('city', $mentor->user->city) }}" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pays</label>
                            <input type="text" name="country" value="{{ old('country', $mentor->user->country) }}" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- Profil professionnel -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Profil professionnel</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bio / Présentation</label>
                            <textarea name="bio" rows="4" 
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">{{ old('bio', $mentor->bio) }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Décrivez le parcours et l'expertise du mentor</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Conseil / Message</label>
                            <textarea name="advice" rows="3" 
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">{{ old('advice', $mentor->advice) }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Un conseil que le mentor souhaite partager</p>
                        </div>

                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Poste actuel</label>
                                <input type="text" name="current_position" value="{{ old('current_position', $mentor->current_position) }}" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Entreprise actuelle</label>
                                <input type="text" name="current_company" value="{{ old('current_company', $mentor->current_company) }}" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Années d'expérience</label>
                                <input type="number" name="years_of_experience" value="{{ old('years_of_experience', $mentor->years_of_experience) }}" 
                                       min="0" max="60" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Spécialisation</label>
                                <select name="specialization_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <option value="">Aucune</option>
                                    @foreach($specializations as $spec)
                                        <option value="{{ $spec->id }}" {{ old('specialization_id', $mentor->specialization_id) == $spec->id ? 'selected' : '' }}>
                                            {{ $spec->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div x-data="{ skills: {{ json_encode(old('skills', $mentor->skills ?? [])) }} }">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Compétences</label>
                            <div class="flex flex-wrap gap-2 mb-2">
                                <template x-for="(skill, index) in skills" :key="index">
                                    <div class="flex items-center gap-1 bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-sm">
                                        <span x-text="skill"></span>
                                        <input type="hidden" :name="'skills[' + index + ']'" :value="skill">
                                        <button type="button" @click="skills.splice(index, 1)" class="text-indigo-500 hover:text-indigo-700">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                            <div class="flex gap-2">
                                <input type="text" x-ref="skillInput" @keydown.enter.prevent="
                                    if($refs.skillInput.value.trim()) {
                                        skills.push($refs.skillInput.value.trim());
                                        $refs.skillInput.value = '';
                                    }
                                " placeholder="Ajouter une compétence (Entrée pour valider)" 
                                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <button type="button" @click="
                                    if($refs.skillInput.value.trim()) {
                                        skills.push($refs.skillInput.value.trim());
                                        $refs.skillInput.value = '';
                                    }
                                " class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                    Ajouter
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Étapes du parcours (Roadmap) -->
                <div class="bg-white rounded-xl shadow-sm p-6" x-data="{ showAddStep: false, editingStep: null }">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-bold text-gray-900">Étapes du parcours</h2>
                        <button type="button" @click="showAddStep = !showAddStep" 
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span x-text="showAddStep ? 'Annuler' : 'Ajouter une étape'"></span>
                        </button>
                    </div>

                    <!-- Formulaire d'ajout -->
                    <div x-show="showAddStep" x-transition class="mb-6 p-4 bg-green-50 rounded-lg border border-green-200">
                        <h3 class="font-semibold text-gray-900 mb-3">Nouvelle étape</h3>
                        <form action="{{ route('admin.mentors.roadmap.store', $mentor) }}" method="POST" class="space-y-3">
                            @csrf
                            <div class="grid md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Type d'étape *</label>
                                    <select name="step_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                        <option value="">Sélectionner...</option>
                                        @foreach(\App\Models\RoadmapStep::STEP_TYPES as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Titre *</label>
                                    <input type="text" name="title" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Institution / Entreprise</label>
                                    <input type="text" name="institution_company" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Localisation</label>
                                    <input type="text" name="location" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date de début *</label>
                                    <input type="date" name="start_date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date de fin</label>
                                    <input type="date" name="end_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                    <p class="text-xs text-gray-500 mt-1">Laisser vide si en cours</p>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea name="description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="showAddStep = false" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg text-sm">
                                    Annuler
                                </button>
                                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                                    Ajouter l'étape
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Liste des étapes existantes -->
                    <div class="space-y-3">
                        @forelse($mentor->roadmapSteps as $step)
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-indigo-300 transition">
                                <!-- Affichage normal -->
                                <div x-show="editingStep !== {{ $step->id }}">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="px-2 py-0.5 bg-indigo-100 text-indigo-700 text-xs rounded font-medium">
                                                    {{ $step->step_type_label }}
                                                </span>
                                                <h4 class="font-semibold text-gray-900">{{ $step->title }}</h4>
                                            </div>
                                            @if($step->institution_company)
                                                <p class="text-sm text-gray-700">{{ $step->institution_company }}</p>
                                            @endif
                                            @if($step->location)
                                                <p class="text-sm text-gray-500">{{ $step->location }}</p>
                                            @endif
                                            <p class="text-sm text-gray-500 mt-1">
                                                {{ $step->start_date->format('M Y') }} - {{ $step->end_date?->format('M Y') ?? 'Présent' }}
                                            </p>
                                            @if($step->description)
                                                <p class="text-sm text-gray-600 mt-2">{{ $step->description }}</p>
                                            @endif
                                        </div>
                                        <div class="flex gap-2 ml-4">
                                            <button type="button" @click="editingStep = {{ $step->id }}" 
                                                    class="text-blue-600 hover:text-blue-800">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <form action="{{ route('admin.mentors.roadmap.delete', [$mentor, $step]) }}" method="POST" 
                                                  onsubmit="return confirm('Supprimer cette étape ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Formulaire d'édition -->
                                <div x-show="editingStep === {{ $step->id }}" x-transition>
                                    <form action="{{ route('admin.mentors.roadmap.update', [$mentor, $step]) }}" method="POST" class="space-y-3">
                                        @csrf
                                        @method('PUT')
                                        <div class="grid md:grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Type d'étape *</label>
                                                <select name="step_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                                    @foreach(\App\Models\RoadmapStep::STEP_TYPES as $key => $label)
                                                        <option value="{{ $key }}" {{ $step->step_type === $key ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Titre *</label>
                                                <input type="text" name="title" value="{{ $step->title }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Institution / Entreprise</label>
                                                <input type="text" name="institution_company" value="{{ $step->institution_company }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Localisation</label>
                                                <input type="text" name="location" value="{{ $step->location }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Date de début *</label>
                                                <input type="date" name="start_date" value="{{ $step->start_date?->format('Y-m-d') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Date de fin</label>
                                                <input type="date" name="end_date" value="{{ $step->end_date?->format('Y-m-d') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                            <textarea name="description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ $step->description }}</textarea>
                                        </div>
                                        <div class="flex justify-end gap-2">
                                            <button type="button" @click="editingStep = null" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg text-sm">
                                                Annuler
                                            </button>
                                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                                                Enregistrer
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p>Aucune étape de parcours. Cliquez sur "Ajouter une étape" pour commencer.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Liens -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Liens professionnels</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">LinkedIn URL</label>
                            <input type="url" name="linkedin_url" value="{{ old('linkedin_url', $mentor->linkedin_url) }}" 
                                   placeholder="https://linkedin.com/in/..." 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Site web</label>
                            <input type="url" name="website_url" value="{{ old('website_url', $mentor->website_url) }}" 
                                   placeholder="https://..." 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne latérale -->
            <div class="space-y-6">
                <!-- Photo de profil -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Photo de profil</h2>
                    
                    <div class="text-center mb-4">
                        @if($mentor->user->profile_photo)
                            <img src="{{ Storage::url($mentor->user->profile_photo) }}" 
                                 alt="{{ $mentor->user->name }}" 
                                 class="w-32 h-32 rounded-full mx-auto object-cover border-4 border-gray-100">
                        @else
                            <div class="w-32 h-32 rounded-full mx-auto bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-4xl font-bold">
                                {{ substr($mentor->user->name, 0, 1) }}
                            </div>
                        @endif
                    </div>

                    <form action="{{ route('admin.mentors.update-photo', $mentor) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="space-y-3">
                            <input type="file" name="profile_photo" accept="image/*" 
                                   class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                Mettre à jour la photo
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Statut et actions -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Statut du profil</h2>
                    
                    <div class="space-y-3">
                        <label class="flex items-center gap-3">
                            <input type="checkbox" name="is_published" value="1" 
                                   {{ old('is_published', $mentor->is_published) ? 'checked' : '' }}
                                   class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500">
                            <span class="text-sm font-medium text-gray-700">Profil publié</span>
                        </label>

                        <label class="flex items-center gap-3">
                            <input type="checkbox" name="is_validated" value="1" 
                                   {{ old('is_validated', $mentor->is_validated) ? 'checked' : '' }}
                                   class="w-5 h-5 text-green-600 rounded focus:ring-green-500">
                            <span class="text-sm font-medium text-gray-700">Profil validé</span>
                        </label>
                    </div>

                    @if($mentor->validated_at)
                        <p class="text-xs text-gray-500 mt-3">
                            Validé le {{ $mentor->validated_at->format('d/m/Y à H:i') }}
                        </p>
                    @endif
                </div>

                <!-- Statistiques rapides -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Statistiques</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Vues du profil</span>
                            <span class="font-semibold text-gray-900">{{ number_format($mentor->profile_views) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Étapes roadmap</span>
                            <span class="font-semibold text-gray-900">{{ $mentor->roadmapSteps->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Membre depuis</span>
                            <span class="font-semibold text-gray-900">{{ $mentor->created_at->format('M Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between bg-white rounded-xl shadow-sm p-6">
            <a href="{{ route('admin.mentors.show', $mentor) }}" class="text-gray-600 hover:text-gray-900">
                Voir le profil
            </a>
            <div class="flex gap-3">
                <a href="{{ route('admin.mentors.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    Annuler
                </a>
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enregistrer les modifications
                </button>
            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection
