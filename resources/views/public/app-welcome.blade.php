<!DOCTYPE html>
<html lang="fr" class="h-full bg-gray-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#5844E3">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Brillio">

    <title>Brillio - Ton avenir, ton choix</title>

    @include('partials.pwa-head')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        (function() {
            const originalWarn = console.warn;
            console.warn = function(...args) {
                if (args[0] && typeof args[0] === 'string' && args[0].includes('cdn.tailwindcss.com')) return;
                originalWarn.apply(console, args);
            };
        })();
    </script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}" src="https://cdn.tailwindcss.com"></script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#EEF0FD',
                            100: '#E0E3FC',
                            500: '#5844E3',
                            600: '#4B36D4',
                            700: '#3E29C2',
                        }
                    }
                }
            }
        };
    </script>

    <!-- Alpine.js -->
    <script nonce="{{ request()->attributes->get('csp_nonce') }}" defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.7/dist/cdn.min.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F8F9FA;
            color: #111827;
            -webkit-tap-highlight-color: transparent;
            touch-action: pan-y;
        }

        /* Safe area support for iOS notch & home bar */
        .safe-top {
            padding-top: max(env(safe-area-inset-top), 16px);
        }

        .safe-bottom {
            padding-bottom: max(env(safe-area-inset-bottom), 16px);
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="h-full flex flex-col justify-center items-center select-none bg-slate-900/5 sm:bg-slate-100">

    <div x-data="welcomeApp()" x-init="init()" class="w-full max-w-md h-full min-h-screen sm:min-h-[780px] sm:max-h-[860px] sm:rounded-[36px] bg-white sm:shadow-2xl overflow-hidden relative flex flex-col transition-all duration-300">

        <!-- ============================================================== -->
        <!-- SCREEN 1 : SPLASH SCREEN (Purple Figma Welcome)                -->
        <!-- ============================================================== -->
        <div x-show="step === 0" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="nextStep()"
             class="absolute inset-0 z-30 bg-gradient-to-b from-[#5844E3] via-[#513FDF] to-[#4332CE] flex flex-col justify-between items-center text-white p-8 cursor-pointer safe-top safe-bottom">
            
            <!-- Status Bar Mockup -->
            <div class="w-full flex items-center justify-between text-xs text-white/80 font-medium px-2 pt-1">
                <span>9:41</span>
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 4C7.31 4 3.07 5.9 0 8.98L12 21 24 8.98C20.93 5.9 16.69 4 12 4z"/></svg>
                    <svg class="w-5 h-4 fill-current" viewBox="0 0 24 24"><path d="M15.67 4H14V2h-4v2H8.33C7.6 4 7 4.6 7 5.33v15.33C7 21.4 7.6 22 8.33 22h7.33c.74 0 1.34-.6 1.34-1.34V5.33C17 4.6 16.4 4 15.67 4z"/></svg>
                </div>
            </div>

            <!-- Centered Branding -->
            <div class="flex flex-col items-center justify-center flex-1 my-auto animate-fade-in">
                <div class="w-20 h-20 bg-white/15 backdrop-blur-md rounded-2xl flex items-center justify-center shadow-2xl border border-white/20">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                        </path>
                    </svg>
                </div>
                <h1 class="text-3xl font-extrabold text-white mt-5 tracking-tight">Brillio</h1>
                <p class="text-white/85 text-sm font-medium mt-1 tracking-wide">Ton avenir, ton choix.</p>

                <!-- Animated Dots -->
                <div class="flex items-center gap-2 mt-8">
                    <span class="w-2 h-2 rounded-full bg-white/70 animate-pulse"></span>
                    <span class="w-2 h-2 rounded-full bg-white animate-pulse" style="animation-delay: 200ms;"></span>
                    <span class="w-2 h-2 rounded-full bg-white/70 animate-pulse" style="animation-delay: 400ms;"></span>
                </div>
            </div>

            <!-- Home indicator bar -->
            <div class="w-32 h-1 bg-white/40 rounded-full mb-1"></div>
        </div>

        <!-- ============================================================== -->
        <!-- ONBOARDING FLOW (SCREENS 2, 3, 4, 5)                           -->
        <!-- ============================================================== -->
        <div x-cloak x-show="step > 0" class="flex-1 flex flex-col justify-between p-6 safe-top safe-bottom bg-white"
             @touchstart="touchStart($event)" @touchend="touchEnd($event)">

            <!-- Top Bar -->
            <header class="flex items-center justify-between pb-3">
                <div class="flex items-center gap-2">
                    <template x-if="step === 4">
                        <button @click="step = 3" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-700 hover:bg-gray-200 transition mr-1" aria-label="Retour">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                    </template>
                    <div class="w-8 h-8 rounded-xl bg-[#5844E3] flex items-center justify-center text-white shadow-sm flex-shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                            </path>
                        </svg>
                    </div>
                    <span class="text-lg font-bold text-gray-900 tracking-tight">Brillio</span>
                </div>

                <template x-if="step >= 1 && step <= 3">
                    <button @click="step = 4" class="text-xs font-semibold text-gray-600 hover:text-gray-900 px-3 py-1.5 rounded-lg hover:bg-gray-100 transition">
                        Passer
                    </button>
                </template>
            </header>

            <!-- ============================================================== -->
            <!-- STEP 1 : BIENVENUE SUR BRILLIO                                 -->
            <!-- ============================================================== -->
            <div x-show="step === 1" 
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 translate-x-4"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 class="flex-1 flex flex-col justify-center my-auto">
                <div class="w-full max-w-[300px] aspect-square mx-auto rounded-3xl overflow-hidden shadow-sm border border-gray-100 bg-gray-50">
                    <img src="{{ asset('images/onboarding/students.png') }}" alt="Bienvenue sur Brillio" class="w-full h-full object-cover">
                </div>
                <div class="mt-6 text-center px-2">
                    <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Bienvenue sur Brillio</h2>
                    <p class="text-sm text-gray-500 mt-2.5 leading-relaxed">
                        La plateforme d'orientation professionnelle pour les jeunes africains.
                    </p>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- STEP 2 : INSPIRE-TOI DE MODÈLES                                -->
            <!-- ============================================================== -->
            <div x-show="step === 2" 
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 translate-x-4"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 class="flex-1 flex flex-col justify-center my-auto">
                <div class="w-full max-w-[300px] aspect-square mx-auto rounded-3xl overflow-hidden shadow-sm border border-gray-100 bg-gray-50">
                    <img src="{{ asset('images/onboarding/mentor.png') }}" alt="Inspire-toi de modèles" class="w-full h-full object-cover">
                </div>
                <div class="mt-6 text-center px-2">
                    <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Inspire-toi de modèles</h2>
                    <p class="text-sm text-gray-500 mt-2.5 leading-relaxed">
                        Explore les parcours de professionnels africains qui ont réussi dans ton domaine d'intérêt et apprends de leur expérience.
                    </p>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- STEP 3 : COMMENCE MAINTENANT                                   -->
            <!-- ============================================================== -->
            <div x-show="step === 3" 
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 translate-x-4"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 class="flex-1 flex flex-col justify-center my-auto">
                <div class="w-full max-w-[300px] aspect-square mx-auto rounded-3xl overflow-hidden shadow-sm border border-gray-100 bg-gray-50">
                    <img src="{{ asset('images/onboarding/start.png') }}" alt="Commence maintenant" class="w-full h-full object-cover">
                </div>
                <div class="mt-6 text-center px-2">
                    <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Commence maintenant</h2>
                    <p class="text-sm text-gray-500 mt-2.5 leading-relaxed">
                        Crée ton compte et découvre les métiers faits pour toi.
                    </p>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- STEP 4 : REJOINS LA COMMUNAUTÉ (ROLE SELECTION FIGMA)          -->
            <!-- ============================================================== -->
            <div x-show="step === 4" 
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 translate-x-4"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 class="flex-1 flex flex-col justify-center my-auto py-2">
                
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Rejoins maintenant<br>la communauté</h2>
                    <p class="text-sm text-gray-500 mt-2">Choisis ton profil pour commencer</p>
                </div>

                <div class="space-y-4">
                    <!-- Jeune Profile Option -->
                    <a href="{{ route('auth.jeune.register') }}" 
                       class="group block p-4 rounded-2xl bg-gray-50/80 hover:bg-indigo-50/50 border border-gray-100 hover:border-[#5844E3] transition-all duration-200 active:scale-[0.99] shadow-sm">
                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-xl bg-indigo-100/80 flex items-center justify-center text-[#5844E3] group-hover:scale-105 transition-transform flex-shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                    </path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-base font-bold text-gray-900 group-hover:text-[#5844E3] transition-colors">Je suis un Jeune</h3>
                                <p class="text-xs text-gray-500 mt-0.5 leading-snug">Découvrez votre personnalité, explorez des carrières et trouvez votre voie.</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-[#5844E3] group-hover:translate-x-0.5 transition-all flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>

                    <!-- Mentor Profile Option -->
                    <a href="{{ route('auth.mentor.login') }}" 
                       class="group block p-4 rounded-2xl bg-gray-50/80 hover:bg-indigo-50/50 border border-gray-100 hover:border-[#5844E3] transition-all duration-200 active:scale-[0.99] shadow-sm">
                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-xl bg-purple-100/80 flex items-center justify-center text-[#5844E3] group-hover:scale-105 transition-transform flex-shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-base font-bold text-gray-900 group-hover:text-[#5844E3] transition-colors">Je suis un Mentor</h3>
                                <p class="text-xs text-gray-500 mt-0.5 leading-snug">Partagez votre parcours professionnel et inspirez la prochaine génération de talents africains.</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-[#5844E3] group-hover:translate-x-0.5 transition-all flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                </div>

                <!-- Already Registered & Web Links -->
                <div class="mt-8 text-center space-y-2">
                    <p class="text-xs text-gray-500">
                        Déjà inscrit ? 
                        <a href="{{ route('auth.login') }}" class="font-bold text-[#5844E3] hover:underline ml-1">Se connecter</a>
                    </p>
                    <div>
                        <a href="{{ route('home', ['web' => 1]) }}" class="text-[11px] text-gray-400 hover:text-gray-600 transition underline">
                            Accéder au site web complet
                        </a>
                    </div>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- BOTTOM CONTROLS & PAGINATION (FOR STEPS 1-3)                   -->
            <!-- ============================================================== -->
            <div x-show="step >= 1 && step <= 3" class="pt-4 space-y-4">
                <!-- Pagination Dots -->
                <div class="flex items-center justify-center gap-1.5">
                    <span class="h-1.5 rounded-full transition-all duration-300" 
                          :class="step === 1 ? 'w-6 bg-[#5844E3]' : 'w-1.5 bg-indigo-200'"></span>
                    <span class="h-1.5 rounded-full transition-all duration-300" 
                          :class="step === 2 ? 'w-6 bg-[#5844E3]' : 'w-1.5 bg-indigo-200'"></span>
                    <span class="h-1.5 rounded-full transition-all duration-300" 
                          :class="step === 3 ? 'w-6 bg-[#5844E3]' : 'w-1.5 bg-indigo-200'"></span>
                </div>

                <!-- Navigation Buttons -->
                <div>
                    <!-- Step 1 Button -->
                    <div x-show="step === 1">
                        <button @click="step = 2" 
                                class="w-full py-3.5 bg-gradient-to-r from-[#5844E3] to-[#4534C6] hover:from-[#513ED9] hover:to-[#3E2EBF] text-white font-semibold rounded-full shadow-lg shadow-indigo-200 active:scale-[0.98] transition">
                            Suivant
                        </button>
                    </div>

                    <!-- Step 2 Buttons -->
                    <div x-show="step === 2" class="flex items-center justify-between gap-4">
                        <button @click="step = 1" 
                                class="text-sm font-semibold text-gray-700 hover:text-gray-900 px-4 py-3 transition">
                            Précédent
                        </button>
                        <button @click="step = 3" 
                                class="flex-1 py-3.5 bg-gradient-to-r from-[#5844E3] to-[#4534C6] hover:from-[#513ED9] hover:to-[#3E2EBF] text-white font-semibold rounded-full shadow-lg shadow-indigo-200 active:scale-[0.98] transition text-center">
                            Suivant
                        </button>
                    </div>

                    <!-- Step 3 Buttons -->
                    <div x-show="step === 3" class="flex items-center justify-between gap-4">
                        <button @click="step = 2" 
                                class="text-sm font-semibold text-gray-700 hover:text-gray-900 px-4 py-3 transition">
                            Précédent
                        </button>
                        <button @click="step = 4" 
                                class="flex-1 py-3.5 bg-gradient-to-r from-[#5844E3] to-[#4534C6] hover:from-[#513ED9] hover:to-[#3E2EBF] text-white font-semibold rounded-full shadow-lg shadow-indigo-200 active:scale-[0.98] transition text-center">
                            Commencer
                        </button>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        function welcomeApp() {
            return {
                step: 0,
                touchStartX: 0,
                touchEndX: 0,

                init() {
                    // Si l'utilisateur a déjà complété l'onboarding lors d'une session récente,
                    // on peut soit lui montrer le splash puis aller au choix, soit directement le splash
                    setTimeout(() => {
                        if (this.step === 0) {
                            this.step = 1;
                        }
                    }, 2200);
                },

                nextStep() {
                    if (this.step < 4) {
                        this.step++;
                    }
                },

                prevStep() {
                    if (this.step > 1) {
                        this.step--;
                    }
                },

                touchStart(e) {
                    this.touchStartX = e.changedTouches[0].screenX;
                },

                touchEnd(e) {
                    this.touchEndX = e.changedTouches[0].screenX;
                    this.handleSwipe();
                },

                handleSwipe() {
                    const diff = this.touchStartX - this.touchEndX;
                    if (Math.abs(diff) > 40) {
                        if (diff > 0 && this.step >= 1 && this.step <= 3) {
                            // Swipe gauche -> suivant
                            this.step++;
                        } else if (diff < 0 && this.step >= 2 && this.step <= 4) {
                            // Swipe droite -> précédent
                            this.step--;
                        }
                    }
                }
            };
        }
    </script>
</body>

</html>
