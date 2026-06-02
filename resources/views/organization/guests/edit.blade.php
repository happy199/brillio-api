@extends('layouts.organization')

@section('title', 'Modifier le formateur / invité')

@section('content')
<div class="max-w-4xl mx-auto" x-data="{ 
    steps: {{ json_encode($guest->mentorProfile->roadmapSteps->map(fn($s) => ['title' => $s->title, 'institution' => $s->institution_company, 'year' => \Carbon\Carbon::parse($s->start_date)->year])) }},
    photoPreview: '{{ $guest->avatar_url }}',
    addStep() {
        this.steps.push({ title: '', institution: '', year: new Date().getFullYear() });
    },
    removeStep(index) {
        this.steps.splice(index, 1);
    },
    updatePreview(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => { this.photoPreview = e.target.result; };
            reader.readAsDataURL(file);
        }
    }
}">
    <div class="md:flex md:items-center md:justify-between mb-6">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Modifier : {{ $guest->name }}
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Mettez à jour les informations du profil invité.
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <form action="{{ route('organization.guests.destroy', $guest) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce formateur ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Supprimer l'invité
                </button>
            </form>
        </div>
    </div>

    <form method="POST" action="{{ route('organization.guests.update', $guest) }}" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- Section 1 : Photo de profil -->
        <div class="bg-white shadow sm:rounded-lg overflow-hidden border-t-4 border-organization-500">
            <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Photo de profil</h3>
            </div>
            <div class="p-6 flex flex-col items-center">
                <div class="relative">
                    <img :src="photoPreview" class="h-32 w-32 rounded-full object-cover border-4 border-white shadow-lg">
                    <label for="photo" class="absolute bottom-0 right-0 bg-white rounded-full p-2 shadow-md cursor-pointer hover:bg-gray-50 border border-gray-200">
                        <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <input type="file" name="photo" id="photo" class="hidden" accept="image/*" @change="updatePreview">
                    </label>
                </div>
                <p class="mt-3 text-xs text-gray-400">Cliquez sur l'icône pour changer la photo. Max 2Mo.</p>
            </div>
        </div>

        <!-- Section 2 : Informations de base -->
        <div class="bg-white shadow sm:rounded-lg overflow-hidden">
            <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Informations personnelles</h3>
            </div>
            <div class="p-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                <div class="sm:col-span-3">
                    <label for="name" class="block text-sm font-medium text-gray-700">Nom complet</label>
                    <div class="mt-1">
                        <input type="text" name="name" id="name" value="{{ old('name', $guest->name) }}" required
                            class="shadow-sm focus:ring-organization-500 focus:border-organization-500 block w-full sm:text-sm border-gray-300 rounded-md py-3 px-4">
                    </div>
                </div>

                <div class="sm:col-span-3">
                    <label for="email" class="block text-sm font-medium text-gray-700">Adresse email</label>
                    <div class="mt-1">
                        <input type="email" name="email" id="email" value="{{ old('email', $guest->email) }}" required
                            class="shadow-sm focus:ring-organization-500 focus:border-organization-500 block w-full sm:text-sm border-gray-300 rounded-md py-3 px-4">
                    </div>
                </div>

                <div class="sm:col-span-3">
                    <label for="phone" class="block text-sm font-medium text-gray-700">Téléphone</label>
                    <div class="mt-1">
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $guest->phone) }}"
                            class="shadow-sm focus:ring-organization-500 focus:border-organization-500 block w-full sm:text-sm border-gray-300 rounded-md py-3 px-4">
                    </div>
                </div>

                <div class="sm:col-span-3">
                    <label for="country" class="block text-sm font-medium text-gray-700">Pays de résidence</label>
                    <div class="mt-1">
                        <select name="country" id="country" 
                            class="shadow-sm focus:ring-organization-500 focus:border-organization-500 block w-full sm:text-sm border-gray-300 rounded-md py-3 px-4">
                            <option value="">Sélectionner un pays</option>
                            @foreach($countries as $country)
                                <option value="{{ $country }}" {{ old('country', $guest->country) == $country ? 'selected' : '' }}>
                                    {{ $country }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="sm:col-span-4" x-data="{ showCustom: {{ old('specialization_id') === 'other' ? 'true' : 'false' }} }">
                    <label for="specialization_id" class="block text-sm font-medium text-gray-700">Spécialisation</label>
                    <div class="mt-1">
                        <select name="specialization_id" id="specialization_id" 
                            @change="showCustom = ($event.target.value === 'other')"
                            class="shadow-sm focus:ring-organization-500 focus:border-organization-500 block w-full sm:text-sm border-gray-300 rounded-md py-3 px-4">
                            <option value="">Sélectionner une spécialisation</option>
                            @foreach($specializations as $spec)
                                <option value="{{ $spec->id }}" {{ old('specialization_id', $guest->mentorProfile->specialization_id) == $spec->id ? 'selected' : '' }}>
                                    {{ $spec->name }}
                                </option>
                            @endforeach
                            <option value="other" {{ old('specialization_id') === 'other' ? 'selected' : '' }}>Autre (préciser...)</option>
                        </select>
                    </div>

                    <div x-show="showCustom" x-cloak class="mt-3">
                        <input type="text" name="custom_specialization" id="custom_specialization" value="{{ old('custom_specialization') }}"
                            placeholder="Entrez la nouvelle spécialisation"
                            class="shadow-sm focus:ring-organization-500 focus:border-organization-500 block w-full sm:text-sm border-gray-300 rounded-md py-3 px-4 bg-orange-50 border-orange-200">
                        <p class="mt-1 text-xs text-orange-600 font-medium">Une nouvelle catégorie sera créée.</p>
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label for="years_of_experience" class="block text-sm font-medium text-gray-700">Années d'expérience</label>
                    <div class="mt-1">
                        <input type="number" name="years_of_experience" id="years_of_experience" value="{{ old('years_of_experience', $guest->mentorProfile->years_of_experience) }}" min="0" max="60"
                            class="shadow-sm focus:ring-organization-500 focus:border-organization-500 block w-full sm:text-sm border-gray-300 rounded-md py-3 px-4">
                    </div>
                </div>

                <div class="sm:col-span-6">
                    <label for="website_url" class="block text-sm font-medium text-gray-700">Lien LinkedIn ou Site Web</label>
                    <div class="mt-1">
                        <input type="url" name="website_url" id="website_url" value="{{ old('website_url', $guest->mentorProfile->website_url) }}"
                            class="shadow-sm focus:ring-organization-500 focus:border-organization-500 block w-full sm:text-sm border-gray-300 rounded-md py-3 px-4">
                    </div>
                </div>

                <div class="sm:col-span-6">
                    <label for="bio" class="block text-sm font-medium text-gray-700">Biographie</label>
                    <div class="mt-1">
                        <textarea id="bio" name="bio" rows="4"
                            class="shadow-sm focus:ring-organization-500 focus:border-organization-500 block w-full sm:text-sm border-gray-300 rounded-md py-3 px-4">{{ old('bio', $guest->mentorProfile->bio) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3 : Parcours académique / professionnel -->
        <div class="bg-white shadow sm:rounded-lg overflow-hidden">
            <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Parcours (Étapes clés)</h3>
                    <p class="mt-1 text-sm text-gray-500">Ajoutez les étapes majeures du parcours académique ou professionnel.</p>
                </div>
                <button type="button" @click="addStep()" 
                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-full shadow-sm text-white bg-organization-600 hover:bg-organization-700 focus:outline-none">
                    <svg class="-ml-0.5 mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Ajouter une étape
                </button>
            </div>
            <div class="p-6 space-y-4">
                <template x-for="(step, index) in steps" :key="index">
                    <div class="p-4 border border-gray-200 rounded-lg bg-gray-50 relative group">
                        <button type="button" @click="removeStep(index)" 
                            class="absolute top-2 right-2 text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                        <div class="grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-6">
                            <div class="sm:col-span-3">
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Titre / Diplôme</label>
                                <input type="text" :name="`academic_steps[${index}][title]`" x-model="step.title" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-organization-500 focus:border-organization-500 sm:text-sm py-3 px-4">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Institution / Entreprise</label>
                                <input type="text" :name="`academic_steps[${index}][institution]`" x-model="step.institution" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-organization-500 focus:border-organization-500 sm:text-sm py-3 px-4">
                            </div>
                            <div class="sm:col-span-1">
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Année</label>
                                <input type="number" :name="`academic_steps[${index}][year]`" x-model="step.year" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-organization-500 focus:border-organization-500 sm:text-sm py-3 px-4">
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Section validation -->
        <div class="flex justify-end space-x-3">
            <a href="{{ route('organization.guests.index') }}"
                class="inline-flex justify-center py-2 px-6 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                Annuler
            </a>
            <button type="submit"
                class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-bold rounded-md text-white bg-organization-600 hover:bg-organization-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-organization-500 transition-all active:scale-95">
                Mettre à jour le formateur invité
            </button>
        </div>
    </form>
</div>
@endsection
