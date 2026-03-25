@extends('layouts.organization')

@section('title', 'Créer une relation de mentorat')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="mentorshipCreator({
    jeunes: {{ $jeunes->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'avatar' => $u->avatar_url])->toJson() }},
    mentors: {{ $mentors->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'avatar' => $u->avatar_url, 'position' => $u->mentorProfile->current_position ?? 'N/A'])->toJson() }}
})">
    <div class="flex items-center gap-4">
        <a href="{{ route('organization.mentorships.index') }}" class="p-2 hover:bg-gray-100 rounded-full transition">
            <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Nouvelle relation de mentorat</h1>
            <p class="text-sm text-gray-700">Liez manuellement un jeune parrainé avec un mentor de votre organisation.
            </p>
        </div>
    </div>

    <form action="{{ route('organization.mentorships.store') }}" method="POST" @submit="validateForm($event)">
        @csrf
        <input type="hidden" name="mentee_id" :value="selectedJeuneId">
        <input type="hidden" name="mentor_id" :value="selectedMentorId">

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Part 1: Select Jeune -->
            <div class="bg-white shadow rounded-lg flex flex-col">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <label class="block text-lg font-bold text-gray-900">1. Sélectionner le jeune <span
                                class="text-red-500">*</span></label>
                        <template x-if="selectedJeune">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Sélectionné
                            </span>
                        </template>
                    </div>

                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" x-model="jeuneSearch" placeholder="Rechercher un jeune (nom, email...)"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pl-10 py-2">
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto max-h-[500px] p-4 space-y-3 bg-gray-50">
                    <template x-for="jeune in filteredJeunes" :key="jeune.id">
                        <div @click="selectedJeuneId = jeune.id"
                            :class="{'ring-2 ring-indigo-500 bg-indigo-50 border-indigo-200': selectedJeuneId === jeune.id, 'bg-white border-transparent': selectedJeuneId !== jeune.id}"
                            class="p-3 rounded-xl border shadow-sm cursor-pointer hover:border-indigo-300 transition-all flex items-center gap-4">
                            <img :src="jeune.avatar" class="h-12 w-12 rounded-full object-cover">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-gray-900 truncate" x-text="jeune.name"></p>
                                <p class="text-xs text-gray-500 truncate" x-text="jeune.email"></p>
                            </div>
                            <div x-show="selectedJeuneId === jeune.id" class="text-indigo-600">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </template>
                    <div x-show="filteredJeunes.length === 0" class="text-center py-8 text-gray-500">
                        Aucun jeune trouvé pour cette recherche.
                    </div>
                </div>
            </div>

            <!-- Part 2: Select Mentor -->
            <div class="bg-white shadow rounded-lg flex flex-col">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <label class="block text-lg font-bold text-gray-900">2. Sélectionner le mentor <span
                                class="text-red-500">*</span></label>
                        <template x-if="selectedMentor">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Sélectionné
                            </span>
                        </template>
                    </div>

                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" x-model="mentorSearch" placeholder="Rechercher un mentor (nom, poste...)"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pl-10 py-2">
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto max-h-[500px] p-4 space-y-3 bg-gray-50">
                    <template x-for="mentor in filteredMentors" :key="mentor.id">
                        <div @click="selectedMentorId = mentor.id"
                            :class="{'ring-2 ring-indigo-500 bg-indigo-50 border-indigo-200': selectedMentorId === mentor.id, 'bg-white border-transparent': selectedMentorId !== mentor.id}"
                            class="p-3 rounded-xl border shadow-sm cursor-pointer hover:border-indigo-300 transition-all flex items-center gap-4">
                            <img :src="mentor.avatar" class="h-12 w-12 rounded-full object-cover">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-gray-900 truncate" x-text="mentor.name"></p>
                                <p class="text-xs text-gray-500 truncate" x-text="mentor.position"></p>
                            </div>
                            <div x-show="selectedMentorId === mentor.id" class="text-indigo-600">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </template>
                    <div x-show="filteredMentors.length === 0" class="text-center py-8 text-gray-500">
                        Aucun mentor trouvé pour cette recherche.
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary & Submit -->
        <div class="mt-8 bg-white shadow rounded-lg overflow-hidden border border-indigo-100">
            <div class="p-6 bg-indigo-50/50 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-2">
                        <template x-if="selectedJeune">
                            <div
                                class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-full border border-indigo-200 shadow-sm">
                                <img :src="selectedJeune.avatar" class="h-6 w-6 rounded-full">
                                <span class="text-sm font-bold text-indigo-900" x-text="selectedJeune.name"></span>
                            </div>
                        </template>
                        <template x-if="!selectedJeune">
                            <div
                                class="h-9 px-4 flex items-center text-sm text-gray-400 bg-gray-100 rounded-full border border-dashed border-gray-300">
                                Choisir un jeune...
                            </div>
                        </template>
                    </div>

                    <div class="text-indigo-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </div>

                    <div class="flex items-center gap-2">
                        <template x-if="selectedMentor">
                            <div
                                class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-full border border-indigo-200 shadow-sm">
                                <img :src="selectedMentor.avatar" class="h-6 w-6 rounded-full">
                                <span class="text-sm font-bold text-indigo-900" x-text="selectedMentor.name"></span>
                            </div>
                        </template>
                        <template x-if="!selectedMentor">
                            <div
                                class="h-9 px-4 flex items-center text-sm text-gray-400 bg-gray-100 rounded-full border border-dashed border-gray-300">
                                Choisir un mentor...
                            </div>
                        </template>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <a href="{{ route('organization.mentorships.index') }}"
                        class="text-sm font-medium text-gray-600 hover:text-gray-900">Annuler</a>
                    <button type="submit" :disabled="!selectedJeuneId || !selectedMentorId"
                        :class="{'opacity-50 cursor-not-allowed': !selectedJeuneId || !selectedMentorId}"
                        class="inline-flex justify-center py-2.5 px-8 border border-transparent shadow-sm text-sm font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                        Confirmer la création
                    </button>
                </div>
            </div>
            <div class="px-6 py-4 bg-white text-xs text-gray-500 border-t border-indigo-50 text-center">
                Un email de notification sera envoyé automatiquement aux deux parties après validation.
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    document.addEventListener('alpine:init', () => {
        Alpine.data('mentorshipCreator', (config) => ({
            jeunes: config.jeunes,
            mentors: config.mentors,
            jeuneSearch: '',
            mentorSearch: '',
            selectedJeuneId: null,
            selectedMentorId: null,

            get filteredJeunes() {
                if (!this.jeuneSearch) return this.jeunes;
                const s = this.jeuneSearch.toLowerCase();
                return this.jeunes.filter(j => j.name.toLowerCase().includes(s) || j.email.toLowerCase().includes(s));
            },

            get filteredMentors() {
                if (!this.mentorSearch) return this.mentors;
                const s = this.mentorSearch.toLowerCase();
                return this.mentors.filter(m => m.name.toLowerCase().includes(s) || m.position.toLowerCase().includes(s) || m.email.toLowerCase().includes(s));
            },

            get selectedJeune() {
                return this.jeunes.find(j => j.id === this.selectedJeuneId);
            },

            get selectedMentor() {
                return this.mentors.find(m => m.id === this.selectedMentorId);
            },

            validateForm(e) {
                if (!this.selectedJeuneId || !this.selectedMentorId) {
                    e.preventDefault();
                    alert('Veuillez sélectionner un jeune et un mentor.');
                }
            }
        }));
    });
</script>
@endpush
@endsection