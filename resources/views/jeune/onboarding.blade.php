<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bienvenue sur Brillio</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#f0f4ff',
                            100: '#e0e9ff',
                            500: '#6366f1',
                            600: '#5145e5',
                            700: '#4536ca',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        .step-indicator.active {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        }
        .step-indicator.completed {
            background: #10b981;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #d946ef 100%);
        }
        .chip {
            transition: all 0.2s ease;
        }
        .chip.selected {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            border-color: transparent;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50 min-h-screen" x-data="onboarding()">
    <!-- Header -->
    <div class="gradient-bg py-8 text-white text-center">
        <div class="max-w-2xl mx-auto px-4">
            <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center mx-auto shadow-lg">
                <span class="text-3xl font-bold text-primary-600">B</span>
            </div>
            <h1 class="text-3xl font-bold mt-4">Bienvenue, {{ $user->name }} !</h1>
            <p class="text-white/80 mt-2">Aidez-nous a mieux vous connaitre pour personnaliser votre experience</p>
        </div>
    </div>

    <!-- Progress Steps -->
    <div class="max-w-2xl mx-auto px-4 -mt-4">
        <div class="bg-white rounded-2xl shadow-lg p-4">
            <div class="flex items-center justify-between">
                <template x-for="(step, index) in steps" :key="index">
                    <div class="flex items-center">
                        <div class="flex flex-col items-center">
                            <div :class="[
                                'w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold transition-all',
                                currentStep > index ? 'step-indicator completed text-white' : '',
                                currentStep === index ? 'step-indicator active text-white' : '',
                                currentStep < index ? 'bg-gray-200 text-gray-500' : ''
                            ]">
                                <template x-if="currentStep > index">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </template>
                                <template x-if="currentStep <= index">
                                    <span x-text="index + 1"></span>
                                </template>
                            </div>
                            <span class="text-xs text-gray-500 mt-1 hidden sm:block" x-text="step"></span>
                        </div>
                        <template x-if="index < steps.length - 1">
                            <div :class="[
                                'w-12 sm:w-20 h-1 mx-2',
                                currentStep > index ? 'bg-green-500' : 'bg-gray-200'
                            ]"></div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('jeune.onboarding.complete') }}" method="POST" class="max-w-2xl mx-auto px-4 py-8">
        @csrf

        <!-- Step 1: Informations personnelles -->
        <div x-show="currentStep === 0" x-transition class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Informations personnelles</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date de naissance</label>
                    <input type="date"
                           name="birth_date"
                           x-model="formData.birth_date"
                           required
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200">
                    @error('birth_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pays</label>
                    <select name="country"
                            x-model="formData.country"
                            required
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200">
                        <option value="">Selectionnez votre pays</option>
                        @foreach($countries as $code => $name)
                            <option value="{{ $name }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                    <input type="text"
                           name="city"
                           x-model="formData.city"
                           placeholder="Votre ville"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200">
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button" @click="nextStep()" class="px-6 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition">
                    Continuer
                </button>
            </div>
        </div>

        <!-- Step 2: Situation actuelle -->
        <div x-show="currentStep === 1" x-transition class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Votre situation actuelle</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Niveau d'etudes</label>
                    <div class="grid grid-cols-2 gap-3">
                        <template x-for="level in educationLevels" :key="level.value">
                            <button type="button"
                                    @click="formData.education_level = level.value"
                                    :class="[
                                        'chip p-3 text-sm rounded-xl border-2 text-left',
                                        formData.education_level === level.value ? 'selected' : 'border-gray-200 hover:border-primary-300'
                                    ]">
                                <span x-text="level.label"></span>
                            </button>
                        </template>
                    </div>
                    <input type="hidden" name="education_level" x-model="formData.education_level">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Situation actuelle</label>
                    <div class="grid grid-cols-2 gap-3">
                        <template x-for="situation in situations" :key="situation.value">
                            <button type="button"
                                    @click="formData.current_situation = situation.value"
                                    :class="[
                                        'chip p-3 text-sm rounded-xl border-2 text-left',
                                        formData.current_situation === situation.value ? 'selected' : 'border-gray-200 hover:border-primary-300'
                                    ]">
                                <span x-text="situation.label"></span>
                            </button>
                        </template>
                    </div>
                    <input type="hidden" name="current_situation" x-model="formData.current_situation">
                </div>
            </div>

            <div class="mt-6 flex justify-between">
                <button type="button" @click="prevStep()" class="px-6 py-3 text-gray-600 font-medium hover:bg-gray-100 rounded-xl transition">
                    Retour
                </button>
                <button type="button" @click="nextStep()" class="px-6 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition">
                    Continuer
                </button>
            </div>
        </div>

        <!-- Step 3: Interets -->
        <div x-show="currentStep === 2" x-transition class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-2">Vos centres d'interet</h2>
            <p class="text-gray-500 text-sm mb-6">Selectionnez 1 a 5 domaines qui vous interessent</p>

            <div class="flex flex-wrap gap-2">
                <template x-for="interest in interestOptions" :key="interest">
                    <button type="button"
                            @click="toggleInterest(interest)"
                            :class="[
                                'chip px-4 py-2 text-sm rounded-full border-2',
                                formData.interests.includes(interest) ? 'selected' : 'border-gray-200 hover:border-primary-300'
                            ]">
                        <span x-text="interest"></span>
                    </button>
                </template>
            </div>
            <template x-for="interest in formData.interests" :key="interest">
                <input type="hidden" name="interests[]" :value="interest">
            </template>

            <div class="mt-6 flex justify-between">
                <button type="button" @click="prevStep()" class="px-6 py-3 text-gray-600 font-medium hover:bg-gray-100 rounded-xl transition">
                    Retour
                </button>
                <button type="button" @click="nextStep()" class="px-6 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition">
                    Continuer
                </button>
            </div>
        </div>

        <!-- Step 4: Objectifs -->
        <div x-show="currentStep === 3" x-transition class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-2">Vos objectifs sur Brillio</h2>
            <p class="text-gray-500 text-sm mb-6">Qu'esperez-vous trouver ici ? (max 3)</p>

            <div class="space-y-3">
                <template x-for="goal in goalOptions" :key="goal.value">
                    <button type="button"
                            @click="toggleGoal(goal.value)"
                            :class="[
                                'chip w-full p-4 text-left rounded-xl border-2 flex items-start gap-3',
                                formData.goals.includes(goal.value) ? 'selected' : 'border-gray-200 hover:border-primary-300'
                            ]">
                        <div :class="[
                            'w-6 h-6 rounded-full flex-shrink-0 flex items-center justify-center',
                            formData.goals.includes(goal.value) ? 'bg-white/30' : 'bg-gray-100'
                        ]">
                            <span x-text="goal.icon" class="text-lg"></span>
                        </div>
                        <div>
                            <span class="font-medium" x-text="goal.label"></span>
                            <p class="text-sm mt-0.5" :class="formData.goals.includes(goal.value) ? 'text-white/70' : 'text-gray-500'" x-text="goal.desc"></p>
                        </div>
                    </button>
                </template>
            </div>
            <template x-for="goal in formData.goals" :key="goal">
                <input type="hidden" name="goals[]" :value="goal">
            </template>

            <div class="mt-6 flex justify-between">
                <button type="button" @click="prevStep()" class="px-6 py-3 text-gray-600 font-medium hover:bg-gray-100 rounded-xl transition">
                    Retour
                </button>
                <button type="button" @click="nextStep()" class="px-6 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition">
                    Continuer
                </button>
            </div>
        </div>

        <!-- Step 5: Comment nous avez-vous trouve -->
        <div x-show="currentStep === 4" x-transition class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-2">Comment avez-vous decouvert Brillio ?</h2>
            <p class="text-gray-500 text-sm mb-6">Cela nous aide a mieux comprendre notre communaute</p>

            <div class="grid grid-cols-2 gap-3">
                <template x-for="source in sources" :key="source.value">
                    <button type="button"
                            @click="formData.how_found_us = source.value"
                            :class="[
                                'chip p-4 text-center rounded-xl border-2',
                                formData.how_found_us === source.value ? 'selected' : 'border-gray-200 hover:border-primary-300'
                            ]">
                        <span x-text="source.icon" class="text-2xl block mb-1"></span>
                        <span class="text-sm" x-text="source.label"></span>
                    </button>
                </template>
            </div>
            <input type="hidden" name="how_found_us" x-model="formData.how_found_us">

            <div class="mt-6 flex justify-between">
                <button type="button" @click="prevStep()" class="px-6 py-3 text-gray-600 font-medium hover:bg-gray-100 rounded-xl transition">
                    Retour
                </button>
                <button type="submit" class="px-8 py-3 bg-gradient-to-r from-primary-600 to-purple-600 text-white font-semibold rounded-xl hover:opacity-90 transition shadow-lg">
                    Commencer l'aventure !
                </button>
            </div>
        </div>
    </form>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        function onboarding() {
            return {
                currentStep: 0,
                steps: ['Profil', 'Situation', 'Interets', 'Objectifs', 'Source'],

                formData: {
                    birth_date: '',
                    country: '',
                    city: '',
                    education_level: '',
                    current_situation: '',
                    interests: [],
                    goals: [],
                    how_found_us: '',
                },

                educationLevels: [
                    { value: 'college', label: 'College' },
                    { value: 'lycee', label: 'Lycee' },
                    { value: 'bac', label: 'Baccalaureat' },
                    { value: 'licence', label: 'Licence / Bachelor' },
                    { value: 'master', label: 'Master' },
                    { value: 'doctorat', label: 'Doctorat' },
                ],

                situations: [
                    { value: 'etudiant', label: 'Etudiant(e)' },
                    { value: 'recherche_emploi', label: 'En recherche d\'emploi' },
                    { value: 'emploi', label: 'En emploi' },
                    { value: 'entrepreneur', label: 'Entrepreneur' },
                    { value: 'autre', label: 'Autre' },
                ],

                interestOptions: [
                    'Technologie', 'Finance', 'Marketing', 'Design', 'Sante',
                    'Education', 'Ingenierie', 'Droit', 'Arts', 'Sciences',
                    'Entrepreneuriat', 'Communication', 'Agriculture', 'Environnement', 'Sport'
                ],

                goalOptions: [
                    { value: 'orientation', label: 'M\'orienter dans ma carriere', desc: 'Decouvrir les metiers qui me correspondent', icon: '🧭' },
                    { value: 'personnalite', label: 'Mieux me connaitre', desc: 'Passer le test de personnalite MBTI', icon: '🧠' },
                    { value: 'mentor', label: 'Trouver un mentor', desc: 'Me faire accompagner par un professionnel', icon: '🤝' },
                    { value: 'ia', label: 'Discuter avec l\'IA', desc: 'Obtenir des conseils personnalises', icon: '🤖' },
                    { value: 'documents', label: 'Gerer mes documents', desc: 'Centraliser mes diplomes et certificats', icon: '📁' },
                ],

                sources: [
                    { value: 'social_media', label: 'Reseaux sociaux', icon: '📱' },
                    { value: 'friend', label: 'Un ami', icon: '👥' },
                    { value: 'school', label: 'Ecole/Universite', icon: '🎓' },
                    { value: 'search', label: 'Recherche Google', icon: '🔍' },
                    { value: 'event', label: 'Evenement', icon: '🎪' },
                    { value: 'other', label: 'Autre', icon: '✨' },
                ],

                nextStep() {
                    if (this.currentStep < this.steps.length - 1) {
                        this.currentStep++;
                    }
                },

                prevStep() {
                    if (this.currentStep > 0) {
                        this.currentStep--;
                    }
                },

                toggleInterest(interest) {
                    const index = this.formData.interests.indexOf(interest);
                    if (index > -1) {
                        this.formData.interests.splice(index, 1);
                    } else if (this.formData.interests.length < 5) {
                        this.formData.interests.push(interest);
                    }
                },

                toggleGoal(goal) {
                    const index = this.formData.goals.indexOf(goal);
                    if (index > -1) {
                        this.formData.goals.splice(index, 1);
                    } else if (this.formData.goals.length < 3) {
                        this.formData.goals.push(goal);
                    }
                },
            }
        }
    </script>
</body>
</html>
