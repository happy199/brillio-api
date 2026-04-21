@extends('layouts.organization')

@section('title', 'Ajouter un formateur / invité')

@section('content')
<div class="max-w-4xl mx-auto" x-data="{ 
    steps: [{ title: '', institution: '', year: new Date().getFullYear() }],
    addStep() {
        this.steps.push({ title: '', institution: '', year: new Date().getFullYear() });
    },
    removeStep(index) {
        this.steps.splice(index, 1);
    }
}">
    <div class="md:flex md:items-center md:justify-between mb-6">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Ajouter un formateur / invité
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Créez manuellement un profil pour une personnalité publique ou un intervenant externe.
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('organization.guests.store') }}" class="space-y-8">
        @csrf

        <!-- Section 1 : Informations de base -->
        <div class="bg-white shadow sm:rounded-lg overflow-hidden">
            <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Informations personnelles</h3>
                <p class="mt-1 text-sm text-gray-500">Ces informations seront utilisées pour créer le compte "invité" et les invitations.</p>
            </div>
            <div class="p-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                <div class="sm:col-span-3">
                    <label for="name" class="block text-sm font-medium text-gray-700">Nom complet</label>
                    <div class="mt-1">
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="shadow-sm focus:ring-organization-500 focus:border-organization-500 block w-full sm:text-sm border-gray-300 rounded-md py-3 px-4">
                    </div>
                </div>

                <div class="sm:col-span-3">
                    <label for="email" class="block text-sm font-medium text-gray-700">Adresse email</label>
                    <div class="mt-1">
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                            class="shadow-sm focus:ring-organization-500 focus:border-organization-500 block w-full sm:text-sm border-gray-300 rounded-md py-3 px-4">
                    </div>
                </div>

                <div class="sm:col-span-3">
                    <label for="phone" class="block text-sm font-medium text-gray-700">Téléphone (optionnel)</label>
                    <div class="mt-1">
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                            class="shadow-sm focus:ring-organization-500 focus:border-organization-500 block w-full sm:text-sm border-gray-300 rounded-md py-3 px-4">
                    </div>
                </div>

                <div class="sm:col-span-3">
                    <label for="website_url" class="block text-sm font-medium text-gray-700">Site Web / LinkedIn (optionnel)</label>
                    <div class="mt-1">
                        <input type="url" name="website_url" id="website_url" value="{{ old('website_url') }}"
                            class="shadow-sm focus:ring-organization-500 focus:border-organization-500 block w-full sm:text-sm border-gray-300 rounded-md py-3 px-4">
                    </div>
                </div>

                <div class="sm:col-span-6">
                    <label for="bio" class="block text-sm font-medium text-gray-700">Biographie courte</label>
                    <div class="mt-1">
                        <textarea id="bio" name="bio" rows="4"
                            class="shadow-sm focus:ring-organization-500 focus:border-organization-500 block w-full sm:text-sm border-gray-300 rounded-md py-3 px-4">{{ old('bio') }}</textarea>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">Décrivez brièvement le parcours et l'expertise de l'invité.</p>
                </div>
            </div>
        </div>

        <!-- Section 2 : Parcours académique / professionnel -->
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
                <div x-show="steps.length === 0" class="text-center py-4 text-gray-500 italic text-sm">
                    Aucune étape ajoutée pour le moment.
                </div>
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
                Créer le formateur invité
            </button>
        </div>
    </form>
</div>
@endsection
