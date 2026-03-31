<div x-data="{ 
    show: {{ $user->needsProfilingNudge() ? 'true' : 'false' }}, 
    step: 1,
    rating: 0,
    hoverRating: 0,
    comment: '',
    situation: '{{ $user->onboarding_data['current_situation'] ?? 'autre' }}',
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
        // Détermine quelle partie est sautée selon l'étape actuelle
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
            
            <!-- Close Button (only if not mandatory/first nudge) -->
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
                    <label class="block text-sm font-medium text-gray-700 mb-2">Un petit commentaire ? (Optionnel)</label>
                    <textarea 
                        x-model="comment" 
                        class="w-full rounded-2xl border-gray-200 focus:border-primary-500 focus:ring-primary-500 text-sm p-4" 
                        placeholder="Qu'est-ce qu'on pourrait améliorer ?"
                        rows="3"
                    ></textarea>
                </div>

                <button 
                    @click="submitFeedback" 
                    :disabled="rating === 0 || loading"
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
                    
                    <!-- If College/Lycée -->
                    <template x-if="['college', 'lycee'].includes(situation)">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">En quelle classe es-tu ?</label>
                                <select x-model="details.class_level" class="w-full rounded-xl border-gray-200 text-sm p-3">
                                    <option value="">Choisir...</option>
                                    <template x-if="situation === 'college'">
                                        <template x-for="c in ['6ème', '5ème', '4ème', '3ème']">
                                            <option :value="c" x-text="c"></option>
                                        </template>
                                    </template>
                                    <template x-if="situation === 'lycee'">
                                        <template x-for="c in ['2nde', '1ère', 'Terminale']">
                                            <option :value="c" x-text="c"></option>
                                        </template>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Quelle est ta série / spécialité ?</label>
                                <input type="text" x-model="details.specialization" class="w-full rounded-xl border-gray-200 text-sm p-3" placeholder="Ex: S1, L, G2, Mathématiques...">
                            </div>
                        </div>
                    </template>

                    <!-- If Student -->
                    <template x-if="situation === 'etudiant'">
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
                            </div>
                        </div>
                    </template>

                    <!-- If Working -->
                    <template x-if="['emploi', 'entrepreneur'].includes(situation)">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Secteur d'activité</label>
                                <input type="text" x-model="details.sector" class="w-full rounded-xl border-gray-200 text-sm p-3" placeholder="Ex: Marketing Digital">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Années d'expérience</label>
                                <input type="number" x-model="details.experience" class="w-full rounded-xl border-gray-200 text-sm p-3">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Es-tu ouvert à de nouvelles opportunités ?</label>
                                <select x-model="details.availability" class="w-full rounded-xl border-gray-200 text-sm p-3">
                                    <option value="none">Non, je suis bien ici</option>
                                    <option value="passive">À l'écoute (en veille)</option>
                                    <option value="active">Oui, je cherche activement</option>
                                </select>
                            </div>
                        </div>
                    </template>

                    <!-- Common: Activity City -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dans quelle ville se situe ton établissement / ton entreprise ?</label>
                        <input type="text" x-model="details.city" class="w-full rounded-xl border-gray-200 text-sm p-3" placeholder="Ex: Dakar, Abidjan, Lomé...">
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
