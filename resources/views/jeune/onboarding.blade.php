<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bienvenue sur Brillio</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

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
        <div class="bg-white rounded-2xl shadow-lg p-3 sm:p-4 overflow-x-auto">
            <div class="flex items-center justify-between min-w-max sm:min-w-0">
                <template x-for="(step, index) in steps" :key="index">
                    <div class="flex items-center">
                        <div class="flex flex-col items-center">
                            <div :class="[
                                'w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center text-xs sm:text-sm font-semibold transition-all',
                                currentStep > index ? 'step-indicator completed text-white' : '',
                                currentStep === index ? 'step-indicator active text-white' : '',
                                currentStep < index ? 'bg-gray-200 text-gray-500' : ''
                            ]">
                                <template x-if="currentStep > index">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </template>
                                <template x-if="currentStep <= index">
                                    <span x-text="index + 1"></span>
                                </template>
                            </div>
                            <span class="text-[10px] sm:text-xs text-gray-500 mt-1 hidden sm:block whitespace-nowrap"
                                x-text="step"></span>
                        </div>
                        <template x-if="index < steps.length - 1">
                            <div :class="[
                                'w-6 sm:w-12 md:w-20 h-1 mx-1 sm:mx-2',
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
                    <input type="date" name="birth_date" x-model="formData.birth_date" @change="validateAge()"
                        :max="maxDate" :min="minDate" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200">
                    @error('birth_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <!-- Alerte pour utilisateurs trop jeunes -->
                    <div x-show="ageError" x-transition class="mt-3 p-4 bg-red-50 border border-red-200 rounded-xl">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-red-800">Tu es trop jeune pour utiliser Brillio</p>
                                <p class="text-sm text-red-700 mt-1">Cette application est réservée aux personnes de 10
                                    ans et plus. Merci de te rapprocher d'un adulte pour obtenir de l'aide.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pays</label>
                    <select name="country" x-model="formData.country" @change="onCountryChange()" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200">
                        <option value="">Selectionnez votre pays</option>
                        @foreach($countries as $code => $name)
                            <option value="{{ $name }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ville <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="city" x-model="formData.city" @input="filterCities()"
                        @focus="showCitySuggestions = true" placeholder="Commencez à taper votre ville"
                        autocomplete="off" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200">

                    <!-- City suggestions dropdown -->
                    <div x-show="showCitySuggestions && filteredCities.length > 0"
                        @click.away="showCitySuggestions = false"
                        class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                        <template x-for="city in filteredCities" :key="city">
                            <button type="button" @click="selectCity(city)"
                                class="w-full px-4 py-2 text-left hover:bg-gray-50 transition text-sm" x-text="city">
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Error message for step 0 -->
            <div x-show="errors.step0" x-transition class="mt-4 p-3 bg-red-50 border border-red-200 rounded-xl">
                <p class="text-sm text-red-700 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    <span x-text="errors.step0"></span>
                </p>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button" @click="nextStep()" :disabled="!canProceedStep0"
                    :class="!canProceedStep0 ? 'opacity-50 cursor-not-allowed bg-gray-400' : 'bg-primary-600 hover:bg-primary-700'"
                    class="px-6 py-3 text-white font-semibold rounded-xl transition">
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
                            <button type="button" @click="formData.education_level = level.value" :class="[
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
                            <button type="button" @click="formData.current_situation = situation.value" :class="[
                                        'chip p-3 text-sm rounded-xl border-2 text-left',
                                        formData.current_situation === situation.value ? 'selected' : 'border-gray-200 hover:border-primary-300'
                                    ]">
                                <span x-text="situation.label"></span>
                            </button>
                        </template>
                    </div>
                    <input type="hidden" name="current_situation" x-model="formData.current_situation">

                    <!-- Champ personnalisé si 'Autre' est sélectionné -->
                    <div x-show="formData.current_situation === 'autre'" x-transition class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Précisez votre situation</label>
                        <input type="text" name="current_situation_other" x-model="formData.current_situation_other"
                            placeholder="Décrivez votre situation actuelle"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200">
                    </div>
                </div>
            </div>

            <!-- Error message for step 1 -->
            <div x-show="errors.step1" x-transition class="mt-4 p-3 bg-red-50 border border-red-200 rounded-xl">
                <p class="text-sm text-red-700 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    <span x-text="errors.step1"></span>
                </p>
            </div>

            <div class="mt-6 flex justify-between">
                <button type="button" @click="prevStep()"
                    class="px-6 py-3 text-gray-600 font-medium hover:bg-gray-100 rounded-xl transition">
                    Retour
                </button>
                <button type="button" @click="nextStep()" :disabled="!canProceedStep1"
                    :class="!canProceedStep1 ? 'opacity-50 cursor-not-allowed bg-gray-400' : 'bg-primary-600 hover:bg-primary-700'"
                    class="px-6 py-3 text-white font-semibold rounded-xl transition">
                    Continuer
                </button>
            </div>
        </div>

        <!-- Step 3: Interets -->
        <div x-show="currentStep === 2" x-transition class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-2">Vos centres d'interet</h2>
            <p class="text-gray-500 text-sm mb-4">Selectionnez <strong>exactement 5 domaines</strong> qui vous
                interessent</p>
            <div class="mb-4 p-2 bg-primary-50 border border-primary-200 rounded-xl">
                <p class="text-sm text-primary-700 text-center">
                    <span class="font-bold" x-text="formData.interests.length"></span> / 5 sélectionné(s)
                    <span x-show="formData.interests.length === 5" class="ml-2">✓</span>
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <template x-for="interest in interestOptions" :key="interest">
                    <button type="button" @click="toggleInterest(interest)" :class="[
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

            <!-- Error message for step 2 -->
            <div x-show="errors.step2" x-transition class="mt-4 p-3 bg-red-50 border border-red-200 rounded-xl">
                <p class="text-sm text-red-700 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    <span x-text="errors.step2"></span>
                </p>
            </div>

            <div class="mt-6 flex justify-between">
                <button type="button" @click="prevStep()"
                    class="px-6 py-3 text-gray-600 font-medium hover:bg-gray-100 rounded-xl transition">
                    Retour
                </button>
                <button type="button" @click="nextStep()" :disabled="!canProceedStep2"
                    :class="!canProceedStep2 ? 'opacity-50 cursor-not-allowed bg-gray-400' : 'bg-primary-600 hover:bg-primary-700'"
                    class="px-6 py-3 text-white font-semibold rounded-xl transition">
                    Continuer
                </button>
            </div>
        </div>

        <!-- Step 4: Objectifs -->
        <div x-show="currentStep === 3" x-transition class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-2">Vos objectifs sur Brillio</h2>
            <p class="text-gray-500 text-sm mb-4">Qu'esperez-vous trouver ici ? (max 3)</p>
            <div class="mb-4 p-2 bg-primary-50 border border-primary-200 rounded-xl">
                <p class="text-sm text-primary-700 text-center">
                    <span class="font-bold" x-text="formData.goals.length"></span> / 3 sélectionné(s)
                </p>
            </div>

            <div class="space-y-3">
                <template x-for="goal in goalOptions" :key="goal.value">
                    <button type="button" @click="toggleGoal(goal.value)" :class="[
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
                            <p class="text-sm mt-0.5"
                                :class="formData.goals.includes(goal.value) ? 'text-white/70' : 'text-gray-500'"
                                x-text="goal.desc"></p>
                        </div>
                    </button>
                </template>
            </div>
            <template x-for="goal in formData.goals" :key="goal">
                <input type="hidden" name="goals[]" :value="goal">
            </template>

            <!-- Error message for step 3 -->
            <div x-show="errors.step3" x-transition class="mt-4 p-3 bg-red-50 border border-red-200 rounded-xl">
                <p class="text-sm text-red-700 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    <span x-text="errors.step3"></span>
                </p>
            </div>

            <div class="mt-6 flex justify-between">
                <button type="button" @click="prevStep()"
                    class="px-6 py-3 text-gray-600 font-medium hover:bg-gray-100 rounded-xl transition">
                    Retour
                </button>
                <button type="button" @click="nextStep()" :disabled="!canProceedStep3"
                    :class="!canProceedStep3 ? 'opacity-50 cursor-not-allowed bg-gray-400' : 'bg-primary-600 hover:bg-primary-700'"
                    class="px-6 py-3 text-white font-semibold rounded-xl transition">
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
                    <button type="button" @click="formData.how_found_us = source.value" :class="[
                                'chip p-4 text-center rounded-xl border-2',
                                formData.how_found_us === source.value ? 'selected' : 'border-gray-200 hover:border-primary-300'
                            ]">
                        <span x-text="source.icon" class="text-2xl block mb-1"></span>
                        <span class="text-sm" x-text="source.label"></span>
                    </button>
                </template>
            </div>
            <input type="hidden" name="how_found_us" x-model="formData.how_found_us">

            <!-- Champ personnalisé si 'Autre' est sélectionné -->
            <div x-show="formData.how_found_us === 'other'" x-transition class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Comment nous avez-vous découvert ?</label>
                <input type="text" name="how_found_us_other" x-model="formData.how_found_us_other"
                    placeholder="Précisez comment vous avez découvert Brillio"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200">
            </div>

            <!-- Error message for step 4 -->
            <div x-show="errors.step4" x-transition class="mt-4 p-3 bg-red-50 border border-red-200 rounded-xl">
                <p class="text-sm text-red-700 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    <span x-text="errors.step4"></span>
                </p>
            </div>

            <div class="mt-6 flex justify-between">
                <button type="button" @click="prevStep()"
                    class="px-6 py-3 text-gray-600 font-medium hover:bg-gray-100 rounded-xl transition">
                    Retour
                </button>
                <button type="submit" :disabled="!canProceedStep4"
                    :class="!canProceedStep4 ? 'opacity-50 cursor-not-allowed' : 'hover:opacity-90'"
                    class="px-8 py-3 bg-gradient-to-r from-primary-600 to-purple-600 text-white font-semibold rounded-xl transition shadow-lg">
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
                errors: {
                    step0: '',
                    step1: '',
                    step2: '',
                    step3: '',
                    step4: '',
                },

                formData: {
                    birth_date: '',
                    country: '',
                    city: '',
                    education_level: '',
                    current_situation: '',
                    current_situation_other: '',
                    interests: [],
                    goals: [],
                    how_found_us: '',
                    how_found_us_other: '',
                },

                showCitySuggestions: false,
                filteredCities: [],
                ageError: false,
                maxDate: new Date().toISOString().split('T')[0], // Aujourd'hui
                minDate: new Date(new Date().setFullYear(new Date().getFullYear() - 100)).toISOString().split('T')[0], // 100 ans en arrière

                // Base de données des principales villes par pays
                citiesByCountry: {
                    'Benin': ['Cotonou', 'Porto-Novo', 'Parakou', 'Abomey-Calavi', 'Djougou', 'Bohicon', 'Kandi', 'Lokossa', 'Ouidah', 'Abomey'],
                    'Senegal': ['Dakar', 'Thies', 'Kaolack', 'Saint-Louis', 'Ziguinchor', 'Diourbel', 'Louga', 'Tambacounda', 'Mbour', 'Rufisque'],
                    'Cote d\'Ivoire': ['Abidjan', 'Bouake', 'Daloa', 'Yamoussoukro', 'San-Pedro', 'Korhogo', 'Man', 'Divo', 'Gagnoa', 'Abengourou'],
                    'Togo': ['Lome', 'Sokode', 'Kara', 'Kpalime', 'Atakpame', 'Bassar', 'Tsevie', 'Aneho', 'Sansanne-Mango', 'Dapaong'],
                    'Burkina Faso': ['Ouagadougou', 'Bobo-Dioulasso', 'Koudougou', 'Ouahigouya', 'Banfora', 'Dedougou', 'Kaya', 'Tenkodogo', 'Fada N\'Gourma', 'Houndé'],
                    'Mali': ['Bamako', 'Sikasso', 'Mopti', 'Koutiala', 'Kayes', 'Segou', 'Gao', 'Kati', 'Tombouctou', 'Markala'],
                    'Niger': ['Niamey', 'Zinder', 'Maradi', 'Agadez', 'Tahoua', 'Dosso', 'Tillaberi', 'Diffa', 'Arlit', 'Birni N\'Konni'],
                    'Ghana': ['Accra', 'Kumasi', 'Tamale', 'Sekondi-Takoradi', 'Ashaiman', 'Sunyani', 'Cape Coast', 'Obuasi', 'Teshie', 'Tema'],
                    'Nigeria': ['Lagos', 'Kano', 'Ibadan', 'Abuja', 'Port Harcourt', 'Benin City', 'Kaduna', 'Enugu', 'Zaria', 'Warri'],
                    'Cameroun': ['Douala', 'Yaounde', 'Garoua', 'Bamenda', 'Bafoussam', 'Maroua', 'Nkongsamba', 'Ngaoundere', 'Bertoua', 'Loum'],
                    'Gabon': ['Libreville', 'Port-Gentil', 'Franceville', 'Oyem', 'Moanda', 'Mouila', 'Lambarene', 'Tchibanga', 'Koulamoutou', 'Makokou'],
                    'Congo': ['Brazzaville', 'Pointe-Noire', 'Dolisie', 'Nkayi', 'Impfondo', 'Ouesso', 'Madingou', 'Owando', 'Sibiti', 'Mossendjo'],
                    'RD Congo': ['Kinshasa', 'Lubumbashi', 'Mbuji-Mayi', 'Kananga', 'Kisangani', 'Bukavu', 'Tshikapa', 'Kolwezi', 'Likasi', 'Goma'],
                    'Maroc': ['Casablanca', 'Rabat', 'Fes', 'Marrakech', 'Agadir', 'Tanger', 'Meknes', 'Oujda', 'Kenitra', 'Tetouan'],
                    'Algerie': ['Alger', 'Oran', 'Constantine', 'Annaba', 'Blida', 'Batna', 'Djelfa', 'Setif', 'Sidi Bel Abbes', 'Biskra'],
                    'Tunisie': ['Tunis', 'Sfax', 'Sousse', 'Kairouan', 'Bizerte', 'Gabes', 'Ariana', 'Gafsa', 'Monastir', 'Ben Arous'],
                    'Kenya': ['Nairobi', 'Mombasa', 'Kisumu', 'Nakuru', 'Eldoret', 'Ruiru', 'Kikuyu', 'Kangundo-Tala', 'Malindi', 'Naivasha'],
                    'Tanzanie': ['Dar es Salaam', 'Mwanza', 'Arusha', 'Dodoma', 'Mbeya', 'Morogoro', 'Tanga', 'Kahama', 'Tabora', 'Zanzibar'],
                    'Ouganda': ['Kampala', 'Gulu', 'Lira', 'Mbarara', 'Jinja', 'Bwizibwera', 'Mbale', 'Mukono', 'Kasese', 'Masaka'],
                    'Rwanda': ['Kigali', 'Butare', 'Gitarama', 'Ruhengeri', 'Gisenyi', 'Byumba', 'Cyangugu', 'Kibungo', 'Kibuye', 'Rwamagana'],
                    'Ethiopie': ['Addis-Abeba', 'Dire Dawa', 'Mekele', 'Gondar', 'Awasa', 'Bahir Dar', 'Dessie', 'Jimma', 'Jijiga', 'Shashamane'],
                    'Afrique du Sud': ['Johannesburg', 'Le Cap', 'Durban', 'Pretoria', 'Port Elizabeth', 'Bloemfontein', 'East London', 'Polokwane', 'Nelspruit', 'Kimberley'],
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

                // Validation pour chaque étape
                get canProceedStep0() {
                    return this.formData.birth_date !== '' &&
                        this.formData.country !== '' &&
                        this.formData.city !== '' &&
                        !this.ageError;
                },

                get canProceedStep1() {
                    if (!this.formData.education_level || !this.formData.current_situation) {
                        return false;
                    }
                    // Si "autre" est sélectionné, le champ personnalisé doit être rempli
                    if (this.formData.current_situation === 'autre' && !this.formData.current_situation_other?.trim()) {
                        return false;
                    }
                    return true;
                },

                get canProceedStep2() {
                    return this.formData.interests.length === 5;
                },

                get canProceedStep3() {
                    return this.formData.goals.length >= 1 && this.formData.goals.length <= 3;
                },

                get canProceedStep4() {
                    if (!this.formData.how_found_us) {
                        return false;
                    }
                    // Si "autre" est sélectionné, le champ personnalisé doit être rempli
                    if (this.formData.how_found_us === 'other' && !this.formData.how_found_us_other?.trim()) {
                        return false;
                    }
                    return true;
                },

                validateStep() {
                    this.errors['step' + this.currentStep] = '';

                    switch (this.currentStep) {
                        case 0:
                            if (!this.formData.birth_date) {
                                this.errors.step0 = 'Veuillez sélectionner votre date de naissance';
                                return false;
                            }
                            if (!this.formData.country) {
                                this.errors.step0 = 'Veuillez sélectionner votre pays';
                                return false;
                            }
                            if (!this.formData.city) {
                                this.errors.step0 = 'Veuillez saisir votre ville';
                                return false;
                            }
                            if (this.ageError) {
                                this.errors.step0 = 'Vous devez avoir au moins 10 ans pour utiliser Brillio';
                                return false;
                            }
                            return true;

                        case 1:
                            if (!this.formData.education_level) {
                                this.errors.step1 = 'Veuillez sélectionner votre niveau d\'études';
                                return false;
                            }
                            if (!this.formData.current_situation) {
                                this.errors.step1 = 'Veuillez sélectionner votre situation actuelle';
                                return false;
                            }
                            if (this.formData.current_situation === 'autre' && !this.formData.current_situation_other?.trim()) {
                                this.errors.step1 = 'Veuillez préciser votre situation actuelle';
                                return false;
                            }
                            return true;

                        case 2:
                            if (this.formData.interests.length !== 5) {
                                this.errors.step2 = 'Vous devez sélectionner exactement 5 centres d\'intérêt';
                                return false;
                            }
                            return true;

                        case 3:
                            if (this.formData.goals.length < 1) {
                                this.errors.step3 = 'Veuillez sélectionner au moins 1 objectif';
                                return false;
                            }
                            if (this.formData.goals.length > 3) {
                                this.errors.step3 = 'Vous ne pouvez sélectionner que 3 objectifs maximum';
                                return false;
                            }
                            return true;

                        case 4:
                            if (!this.formData.how_found_us) {
                                this.errors.step4 = 'Veuillez indiquer comment vous avez découvert Brillio';
                                return false;
                            }
                            if (this.formData.how_found_us === 'other' && !this.formData.how_found_us_other?.trim()) {
                                this.errors.step4 = 'Veuillez préciser comment vous avez découvert Brillio';
                                return false;
                            }
                            return true;

                        default:
                            return true;
                    }
                },

                nextStep() {
                    if (!this.validateStep()) {
                        // L'erreur est déjà définie dans validateStep
                        return;
                    }

                    if (this.currentStep < this.steps.length - 1) {
                        this.currentStep++;
                    }
                },

                prevStep() {
                    if (this.currentStep > 0) {
                        this.errors['step' + this.currentStep] = '';
                        this.currentStep--;
                    }
                },

                onCountryChange() {
                    // Réinitialiser la ville quand le pays change
                    this.formData.city = '';
                    this.filteredCities = [];
                },

                filterCities() {
                    const input = this.formData.city.toLowerCase();

                    // Afficher les suggestions seulement après 2 caractères
                    if (input.length < 2) {
                        this.filteredCities = [];
                        return;
                    }

                    // Obtenir les villes du pays sélectionné
                    const countryCities = this.citiesByCountry[this.formData.country] || [];

                    // Filtrer les villes qui correspondent à la saisie
                    this.filteredCities = countryCities.filter(city =>
                        city.toLowerCase().includes(input)
                    ).slice(0, 10); // Limiter à 10 résultats
                },

                selectCity(city) {
                    this.formData.city = city;
                    this.showCitySuggestions = false;
                    this.filteredCities = [];
                },

                validateAge() {
                    if (!this.formData.birth_date) {
                        this.ageError = false;
                        return;
                    }

                    const birthDate = new Date(this.formData.birth_date);
                    const today = new Date();
                    const age = today.getFullYear() - birthDate.getFullYear();
                    const monthDiff = today.getMonth() - birthDate.getMonth();

                    // Calculer l'âge exact
                    const exactAge = monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())
                        ? age - 1
                        : age;

                    // Vérifier si l'utilisateur a moins de 10 ans
                    this.ageError = exactAge < 10;
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