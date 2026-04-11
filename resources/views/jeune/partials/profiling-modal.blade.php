<div x-data="{ 
    show: {{ $user->needsProfilingNudge() ? 'true' : 'false' }}, 
    step: 1,
    rating: 0,
    hoverRating: 0,
    comment: '',
    situation: '{{ $user->onboarding_data['current_situation'] ?? 'autre' }}',
    educationLevel: '{{ $user->onboarding_data['education_level'] ?? '' }}',
    details: {},
    loading: false,
    success: false,
    needsFeedback: {{ $user->needsFeedbackNudge() ? 'true' : 'false' }},
    needsSituation: {{ $user->needsSituationNudge() ? 'true' : 'false' }},

    init() {
        if (!this.needsFeedback && this.needsSituation) {
            this.step = 2;
        } else if (this.needsFeedback) {
            this.step = 1;
        }
    },

    get isSchoolLevel() {
        return ['college', 'lycee'].includes(this.educationLevel) || ['college', 'lycee'].includes(this.situation);
    },

    get isUniversityLevel() {
        return this.situation === 'etudiant' && !['college', 'lycee'].includes(this.educationLevel);
    },

    get isWorking() {
        return ['emploi', 'entrepreneur'].includes(this.situation);
    },

    get isJobSeeker() {
        return this.situation === 'recherche_emploi';
    },

    get cityLabel() {
        if (this.isSchoolLevel) return 'Dans quelle ville se situe ton collège ou lycée ?';
        if (this.isUniversityLevel) return 'Dans quelle ville se situe ton université / ton école ?';
        if (this.isWorking) return 'Dans quelle ville se situe ton entreprise / ton activité ?';
        if (this.isJobSeeker) return 'Dans quelle ville recherches-tu du travail ?';
        return 'Dans quelle ville te situes-tu actuellement ?';
    },

    async submitFeedback() {
        if (this.rating === 0) return;
        this.loading = true;
        try {
            const response = await fetch('{{ route('jeune.profiling.feedback') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ rating: this.rating, comment: this.comment })
            });
            const data = await response.json();
            if (data.needs_situation) {
                this.step = 2;
            } else {
                this.finish();
            }
        } catch (e) {
            console.error(e);
        } finally {
            this.loading = false;
        }
    },

    async submitSituation() {
        this.loading = true;
        try {
            await fetch('{{ route('jeune.profiling.situation') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ data: this.details })
            });
            this.finish();
        } catch (e) {
            console.error(e);
        } finally {
            this.loading = false;
        }
    },

    async skip() {
        const route = this.step === 1 ? '{{ route('jeune.profiling.feedback.skip') }}' : '{{ route('jeune.profiling.situation.skip') }}';
        try {
            await fetch(route, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
        } catch (e) {
            console.error(e);
        } finally {
            this.show = false;
        }
    },

    finish() {
        this.success = true;
        this.step = 3;
        setTimeout(() => { this.show = false; }, 3000);
    }
}" 
x-show="show" 
x-transition:enter="transition ease-out duration-300"
x-transition:enter-start="opacity-0"
x-transition:enter-end="opacity-100"
x-transition:leave="transition ease-in duration-200"
x-transition:leave-start="opacity-100"
x-transition:leave-end="opacity-0"
x-on:open-profiling-modal.window="show = true; step = 2"
class="fixed inset-0 z-[70] overflow-y-auto" 
style="display: none;"
x-cloak>
    <div class="flex items-center justify-center min-h-screen p-4">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm"></div>

        <!-- Modal Content -->
        <div 
            x-show="show" 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            class="relative bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden transform transition-all border border-gray-100 min-h-[350px] flex flex-col justify-center"
        >
            
            <!-- Close Button -->
            <button @click="skip()" class="absolute top-5 right-5 text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Step 1: Feedback -->
            <div x-show="step === 1" class="p-8">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-primary-100 text-primary-600 rounded-2xl mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Comment trouves-tu Brillio ?</h2>
                    <p class="text-gray-500 mt-2">Ton avis nous aide à rendre la plateforme encore meilleure pour toi.</p>
                </div>

                <div class="mt-8 flex justify-center gap-2">
                    <template x-for="i in 5">
                        <button 
                            @click="rating = i" 
                            @mouseenter="hoverRating = i" 
                            @mouseleave="hoverRating = 0"
                            class="transition-transform hover:scale-110 focus:outline-none"
                        >
                            <svg class="w-10 h-10" :class="(hoverRating || rating) >= i ? 'text-yellow-400 fill-current' : 'text-gray-300'" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </button>
                    </template>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Un petit commentaire ? (Obligatoire)</label>
                    <textarea 
                        x-model="comment" 
                        class="w-full rounded-2xl border-gray-200 focus:border-primary-500 focus:ring-primary-500 text-sm p-4" 
                        placeholder="Qu'est-ce qu'on pourrait améliorer ou que penses-tu de la plateforme ?"
                        rows="3"
                        required
                    ></textarea>
                </div>

                <button 
                    @click="submitFeedback" 
                    :disabled="rating === 0 || comment.trim() === '' || loading"
                    class="w-full mt-8 py-4 bg-primary-600 text-white font-bold rounded-2xl shadow-lg shadow-primary-200 hover:bg-primary-700 transition disabled:opacity-50 disabled:shadow-none flex items-center justify-center gap-2"
                >
                    <span x-show="!loading">Continuer</span>
                    <svg x-show="loading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>

            <!-- Step 2: Detailed Situation -->
            <div x-show="step === 2" class="p-8">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Dis-nous en plus sur toi</h2>
                    <p class="text-gray-500 mt-1">Ces infos nous aident à te proposer des mentors adaptés.</p>
                </div>

                <div class="space-y-4 max-h-[60vh] overflow-y-auto px-1">
                    <!-- Dynamic Form Sections -->
                    
                    <!-- If School Level (College/Lycee combined) -->
                    <template x-if="isSchoolLevel">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nom de ton établissement</label>
                                <input type="text" x-model="details.institution" class="w-full rounded-xl border-gray-200 text-sm p-3" placeholder="Nom du collège ou lycée">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Ta classe</label>
                                    <select x-model="details.class_level" class="w-full rounded-xl border-gray-200 text-sm p-3">
                                        <option value="">Choisir...</option>
                                        <template x-for="c in ['6ème', '5ème', '4ème', '3ème', '2nde', '1ère', 'Terminale']">
                                            <option :value="c" x-text="c"></option>
                                        </template>
                                    </select>
                                </div>
                                <template x-if="['3ème', '2nde', '1ère', 'Terminale'].includes(details.class_level)">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Série / Spécialité</label>
                                        <input type="text" x-model="details.specialization" class="w-full rounded-xl border-gray-200 text-sm p-3" placeholder="Ex: S, L, G, F...">
                                    </div>
                                </template>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Prochain diplôme préparé</label>
                                <select x-model="details.target_diploma" class="w-full rounded-xl border-gray-200 text-sm p-3">
                                    <option value="">Choisir un diplôme...</option>
                                    <option value="BEPC">BEPC</option>
                                    <option value="CAP">CAP</option>
                                    <option value="Probatoire">Probatoire</option>
                                    <option value="BAC">BAC</option>
                                    <option value="BT">Brevet de Technicien (BT)</option>
                                    <option value="Autre">Autre</option>
                                </select>
                            </div>
                            <template x-if="details.target_diploma === 'Autre'">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Précisez le diplôme</label>
                                    <input type="text" x-model="details.target_diploma_other" class="w-full rounded-xl border-gray-200 text-sm p-3" placeholder="Nom du diplôme...">
                                </div>
                            </template>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Intervalle de la scolarité annuelle (FCFA)</label>
                                <select x-model="details.tuition_range" class="w-full rounded-xl border-gray-200 text-sm p-3">
                                    <option value="">Choisir un intervalle...</option>
                                    <option value="-200000">Moins de 200.000</option>
                                    <option value="200000-500000">200.000 - 500.000</option>
                                    <option value="500000-1000000">500.000 - 1.000.000</option>
                                    <option value="1000000-2000000">1.000.000 - 2.000.000</option>
                                    <option value="+2000000">Plus de 2.000.000</option>
                                </select>
                            </div>
                        </div>
                    </template>

                    <!-- If University Student -->
                    <template x-if="isUniversityLevel">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Université ou École</label>
                                <input type="text" x-model="details.institution" class="w-full rounded-xl border-gray-200 text-sm p-3" placeholder="Nom de l'établissement">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Filière</label>
                                    <input type="text" x-model="details.field" class="w-full rounded-xl border-gray-200 text-sm p-3" placeholder="Ex: Informatique">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Année</label>
                                    <select x-model="details.year" class="w-full rounded-xl border-gray-200 text-sm p-3">
                                        <option value="">Choisir...</option>
                                        <template x-for="y in ['L1', 'L2', 'L3', 'M1', 'M2', 'Doctorat']">
                                            <option :value="y" x-text="y"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Niveau de satisfaction de ta formation</label>
                                <input type="range" min="1" max="5" x-model="details.satisfaction" class="w-full accent-primary-600">
                                <div class="flex justify-between text-[10px] text-gray-400 px-1">
                                    <span>Déçu</span>
                                    <span>Très satisfait</span>
                                </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Intervalle de la scolarité annuelle (FCFA)</label>
                                <select x-model="details.tuition_range" class="w-full rounded-xl border-gray-200 text-sm p-3">
                                    <option value="">Choisir un intervalle...</option>
                                    <option value="-200000">Moins de 200.000</option>
                                    <option value="200000-500000">200.000 - 500.000</option>
                                    <option value="500000-1000000">500.000 - 1.000.000</option>
                                    <option value="1000000-2000000">1.000.000 - 2.000.000</option>
                                    <option value="+2000000">Plus de 2.000.000</option>
                                </select>
                            </div>
                        </div>
                    </template>

                    <!-- If Working -->
                    <template x-if="isWorking">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nom de ton entreprise / activité</label>
                                <input type="text" x-model="details.company" class="w-full rounded-xl border-gray-200 text-sm p-3" placeholder="Ex: Brillio, Freelance...">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ton poste actuel</label>
                                <input type="text" x-model="details.position" class="w-full rounded-xl border-gray-200 text-sm p-3" placeholder="Ex: Développeur, Manager...">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Secteur</label>
                                    <input type="text" x-model="details.sector" class="w-full rounded-xl border-gray-200 text-sm p-3" placeholder="Ex: Marketing">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Expérience (années)</label>
                                    <input type="number" x-model="details.experience" class="w-full rounded-xl border-gray-200 text-sm p-3">
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tranche de salaire mensuel (FCFA)</label>
                                    <select x-model="details.salary_range" class="w-full rounded-xl border-gray-200 text-sm p-3">
                                        <option value="">Choisir une tranche...</option>
                                        <option value="-50000">Moins de 50.000</option>
                                        <option value="50000-100000">50.000 - 100.000</option>
                                        <option value="100000-250000">100.000 - 250.000</option>
                                        <option value="250000-500000">250.000 - 500.000</option>
                                        <option value="500000-1000000">500.000 - 1.000.000</option>
                                        <option value="1000000-3000000">1.000.000 - 3.000.000</option>
                                        <option value="+3000000">Plus de 3.000.000</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- If Job Seeker -->
                    <template x-if="isJobSeeker">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Dernière formation ou diplôme obtenu</label>
                                <input type="text" x-model="details.last_education" class="w-full rounded-xl border-gray-200 text-sm p-3" placeholder="Ex: Master en Gestion, Bac...">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Dans quel domaine recherches-tu du travail ?</label>
                                <input type="text" x-model="details.target_field" class="w-full rounded-xl border-gray-200 text-sm p-3" placeholder="Ex: Comptabilité, Vente...">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Salaire cible mensuel (FCFA)</label>
                                <select x-model="details.salary_range" class="w-full rounded-xl border-gray-200 text-sm p-3">
                                    <option value="">Choisir une tranche...</option>
                                    <option value="-50000">Moins de 50.000</option>
                                    <option value="50000-100000">50.000 - 100.000</option>
                                    <option value="100000-250000">100.000 - 250.000</option>
                                    <option value="250000-500000">250.000 - 500.000</option>
                                    <option value="500000-1000000">500.000 - 1.000.000</option>
                                    <option value="1000000-3000000">1.000.000 - 3.000.000</option>
                                    <option value="+3000000">Plus de 3.000.000</option>
                                </select>
                            </div>
                        </div>
                    </template>

                    <!-- Common Activity City (Dynamic Label) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" x-text="cityLabel"></label>
                        <input type="text" x-model="details.city" class="w-full rounded-xl border-gray-200 text-sm p-3" placeholder="Ex: Dakar, Abidjan, Bamako...">
                    </div>
                </div>

                <div class="mt-8 flex gap-3">
                    <button @click="skip()" class="flex-1 py-4 bg-gray-100 text-gray-600 font-bold rounded-2xl hover:bg-gray-200 transition">Plus tard</button>
                    <button 
                        @click="submitSituation" 
                        :disabled="loading"
                        class="flex-[2] py-4 bg-primary-600 text-white font-bold rounded-2xl shadow-lg shadow-primary-200 hover:bg-primary-700 transition flex items-center justify-center gap-2"
                    >
                        <span x-show="!loading">Enregistrer</span>
                        <svg x-show="loading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Step 3: Success -->
            <div x-show="step === 3" class="p-12 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 text-green-600 rounded-full mb-6">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Parfait, merci !</h2>
                <p class="text-gray-500 mt-2">Tes informations ont bien été mises à jour.</p>
            </div>

        </div>
    </div>
</div>
