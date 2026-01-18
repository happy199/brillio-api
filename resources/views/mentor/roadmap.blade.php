@extends('layouts.mentor')

@section('title', 'Mon parcours')

@section('content')
    <div class="space-y-8" x-data="roadmapApp()">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Mon parcours</h1>
                <p class="text-gray-500">Partagez les etapes cles de votre carriere</p>
            </div>
            <button @click="showAddModal = true"
                class="px-5 py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white font-semibold rounded-xl hover:shadow-lg transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Ajouter une etape
            </button>
        </div>

        <!-- Info Card -->
        <div class="bg-gradient-to-r from-orange-50 to-red-50 border border-orange-200 rounded-2xl p-5">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-orange-800">Construisez votre roadmap</h3>
                    <p class="text-orange-700 text-sm mt-1">
                        Ajoutez les etapes importantes de votre parcours : formations, premiers emplois, promotions, projets
                        cles...
                        Les jeunes pourront s'inspirer de votre trajectoire.
                    </p>
                </div>
            </div>
        </div>

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
                        <h3 class="font-bold text-blue-900">Importez vos expériences LinkedIn</h3>
                        <p class="text-sm text-blue-700">Remplissez automatiquement votre parcours professionnel</p>
                    </div>
                </div>
                <button
                    onclick="const m=document.getElementById('linkedinImportModal'); m.classList.remove('hidden'); m.style.display=''"
                    class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition whitespace-nowrap">
                    📥 Importer LinkedIn
                </button>
            </div>
        </div>

        <!-- Roadmap Timeline -->
        @if($steps->count() > 0)
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <div class="relative">
                    <!-- Timeline Line -->
                    <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gradient-to-b from-orange-500 via-red-500 to-pink-500">
                    </div>

                    <div class="space-y-6" id="roadmapSortable">
                        @foreach($steps as $index => $step)
                            <div class="relative pl-16 group" data-step-id="{{ $step->id }}">
                                <!-- Drag Handle -->
                                <div class="absolute left-0 top-4 cursor-move opacity-0 group-hover:opacity-100 transition">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                    </svg>
                                </div>

                                <!-- Timeline Dot -->
                                <div
                                    class="absolute left-4 w-5 h-5 bg-white border-4 border-orange-500 rounded-full transform -translate-x-1/2 mt-4">
                                </div>

                                <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-xl p-5 hover:shadow-md transition">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span
                                                    class="px-2 py-0.5 bg-orange-200 text-orange-800 text-xs font-medium rounded-full">
                                                    Etape {{ $index + 1 }}
                                                </span>
                                                @if($step->year_start || $step->year_end)
                                                    <span class="text-sm text-gray-500">
                                                        {{ $step->year_start }}{{ $step->year_end ? ' - ' . $step->year_end : ' - Present' }}
                                                    </span>
                                                @endif
                                            </div>
                                            <h3 class="font-bold text-gray-900 text-lg">{{ $step->title }}</h3>
                                            @if($step->institution_company)
                                                <p class="text-orange-600 font-medium">{{ $step->institution_company }}</p>
                                            @endif
                                            @if($step->description)
                                                <p class="text-gray-600 mt-2">{{ $step->description }}</p>
                                            @endif
                                            @if($step->skills && is_array($step->skills) && count($step->skills) > 0)
                                                <div class="flex flex-wrap gap-2 mt-3">
                                                    @foreach($step->skills as $skill)
                                                        <span
                                                            class="px-2 py-1 bg-white text-gray-600 text-xs rounded-full border">{{ $skill }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition">
                                            <button @click="editStep({{ $step->id }})"
                                                class="p-2 hover:bg-white rounded-lg transition" title="Modifier">
                                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button @click="deleteStep({{ $step->id }})"
                                                class="p-2 hover:bg-white rounded-lg transition" title="Supprimer">
                                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-2xl p-12 text-center">
                <div class="w-20 h-20 bg-orange-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Aucune etape dans votre parcours</h3>
                <p class="text-gray-500 mb-6">Commencez a partager votre trajectoire professionnelle avec les jeunes.</p>
                <button @click="showAddModal = true"
                    class="px-6 py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white font-semibold rounded-xl hover:shadow-lg transition">
                    Ajouter ma premiere etape
                </button>
            </div>
        @endif

        <!-- Add/Edit Modal -->
        <div x-show="showAddModal || showEditModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" x-transition>
            <div class="bg-white rounded-3xl max-w-lg w-full max-h-[90vh] overflow-y-auto" @click.outside="closeModals()">
                <div class="p-6 border-b sticky top-0 bg-white rounded-t-3xl">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-900"
                            x-text="showEditModal ? 'Modifier l\'etape' : 'Ajouter une etape'"></h3>
                        <button @click="closeModals()" class="p-2 hover:bg-gray-100 rounded-full">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <form @submit.prevent="submitStep()" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Titre de l'etape *</label>
                        <input type="text" x-model="stepForm.title" required
                            class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500"
                            placeholder="Ex: Licence en Informatique">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Organisation</label>
                        <input type="text" x-model="stepForm.organization"
                            class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500"
                            placeholder="Ex: Universite de Dakar, Google, etc.">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Annee de debut</label>
                            <input type="number" x-model="stepForm.year_start" min="1950" max="2030"
                                class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500"
                                placeholder="2015">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Annee de fin</label>
                            <input type="number" x-model="stepForm.year_end" min="1950" max="2030"
                                class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500"
                                placeholder="2018 (vide si present)">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea x-model="stepForm.description" rows="3"
                            class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 resize-none"
                            placeholder="Decrivez cette etape de votre parcours..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Competences acquises</label>
                        <input type="text" x-model="stepForm.skillsInput" @keydown.enter.prevent="addSkill()"
                            class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500"
                            placeholder="Tapez une competence et appuyez sur Entree">
                        <div class="flex flex-wrap gap-2 mt-2" x-show="stepForm.skills.length > 0">
                            <template x-for="(skill, index) in stepForm.skills" :key="index">
                                <span
                                    class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-sm flex items-center gap-1">
                                    <span x-text="skill"></span>
                                    <button type="button" @click="removeSkill(index)" class="hover:text-orange-900">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </span>
                            </template>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="closeModals()"
                            class="flex-1 py-3 border rounded-xl font-medium text-gray-700 hover:bg-gray-50 transition">
                            Annuler
                        </button>
                        <button type="submit"
                            class="flex-1 py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white font-semibold rounded-xl hover:shadow-lg transition">
                            <span x-text="showEditModal ? 'Enregistrer' : 'Ajouter'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function roadmapApp() {
                return {
                    showAddModal: false,
                    showEditModal: false,
                    editingStepId: null,
                    stepForm: {
                        title: '',
                        organization: '',
                        year_start: '',
                        year_end: '',
                        description: '',
                        skills: [],
                        skillsInput: ''
                    },

                    addSkill() {
                        if (this.stepForm.skillsInput.trim()) {
                            this.stepForm.skills.push(this.stepForm.skillsInput.trim());
                            this.stepForm.skillsInput = '';
                        }
                    },

                    removeSkill(index) {
                        this.stepForm.skills.splice(index, 1);
                    },

                    async editStep(stepId) {
                        try {
                            const response = await fetch(`/espace-mentor/parcours/${stepId}`);
                            const step = await response.json();

                            this.stepForm = {
                                title: step.title,
                                organization: step.institution_company || '',
                                year_start: step.start_date ? new Date(step.start_date).getFullYear() : '',
                                year_end: step.end_date ? new Date(step.end_date).getFullYear() : '',
                                description: step.description || '',
                                skills: step.skills || [],
                                skillsInput: ''
                            };
                            this.editingStepId = stepId;
                            this.showEditModal = true;
                        } catch (error) {
                            console.error('Error:', error);
                        }
                    },

                    async submitStep() {
                        const url = this.showEditModal
                            ? `/espace-mentor/parcours/${this.editingStepId}`
                            : '/espace-mentor/parcours';
                        const method = this.showEditModal ? 'PUT' : 'POST';

                        try {
                            const response = await fetch(url, {
                                method: method,
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify(this.stepForm)
                            });

                            if (response.ok) {
                                window.location.reload();
                            }
                        } catch (error) {
                            console.error('Error:', error);
                        }
                    },

                    async deleteStep(stepId) {
                        if (!confirm('Etes-vous sur de vouloir supprimer cette etape ?')) return;

                        try {
                            const response = await fetch(`/espace-mentor/parcours/${stepId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                }
                            });

                            if (response.ok) {
                                window.location.reload();
                            }
                        } catch (error) {
                            console.error('Error:', error);
                        }
                    },

                    closeModals() {
                        this.showAddModal = false;
                        this.showEditModal = false;
                        this.editingStepId = null;
                        this.stepForm = {
                            title: '',
                            organization: '',
                            year_start: '',
                            year_end: '',
                            description: '',
                            skills: [],
                            skillsInput: ''
                        };
                    }
                }
            }
        </script>
    @endpush
@endsection

@include('mentor.partials.linkedin-import-modal')