@php
    $theme = $theme ?? 'jeune';

    // Theme Configuration
    $themeConfig = [
        'jeune' => [
            'primary_gradient' => 'bg-purple-600',
            'secondary_gradient' => 'bg-pink-600',
            'primary_text' => 'text-purple-700',
            'secondary_text' => 'text-pink-600',
            'primary_bg' => 'bg-purple-600',
            'secondary_bg' => 'bg-pink-600',
            'primary_border' => 'border-purple-600',
            'secondary_border' => 'border-pink-600',
            'primary_light_ring' => 'ring-purple-200',
            'secondary_light_ring' => 'ring-pink-200',
            'primary_light_bg' => 'bg-purple-500',
            'secondary_light_bg' => 'bg-pink-500',
            'primary_border_light' => 'border-purple-300',
            'secondary_border_light' => 'border-pink-300',
            'loading_border' => 'border-purple-500',
            'button_bg' => 'bg-white',
            'button_text' => 'text-purple-600',
        ],
        'mentor' => [
            'primary_gradient' => 'bg-orange-600',
            'secondary_gradient' => 'bg-red-600',
            'primary_text' => 'text-orange-700',
            'secondary_text' => 'text-red-600',
            'primary_bg' => 'bg-orange-600',
            'secondary_bg' => 'bg-red-600',
            'primary_border' => 'border-orange-600',
            'secondary_border' => 'border-red-600',
            'primary_light_ring' => 'ring-orange-200',
            'secondary_light_ring' => 'ring-red-200',
            'primary_light_bg' => 'bg-orange-500',
            'secondary_light_bg' => 'bg-red-500',
            'primary_border_light' => 'border-orange-300',
            'secondary_border_light' => 'border-red-300',
            'loading_border' => 'border-orange-500',
            'button_bg' => 'bg-white',
            'button_text' => 'text-orange-600',
        ],
    ];

    $colors = $themeConfig[$theme];
    $mbtiTypes = \App\Models\PersonalityTest::PERSONALITY_TYPES;

    // Default Routes
    $questionsUrl = $questionsUrl ?? ($theme === 'mentor' ? route('mentor.personality.questions') : route('jeune.personality.questions'));
    $submitUrl = $submitUrl ?? ($theme === 'mentor' ? route('mentor.personality.submit') : route('jeune.personality.submit'));

    // Type Colors (Grid & Result)
    if ($theme === 'mentor') {
        // Mentor: All Orange/Red variants
        $mbtiColors = array_fill_keys(array_keys($mbtiTypes), 'bg-orange-600');
    } else {
        // Jeune: Varied Spectrum
        $mbtiColors = [
            'INTJ' => 'bg-purple-600',
            'INTP' => 'bg-blue-600',
            'ENTJ' => 'bg-purple-700',
            'ENTP' => 'bg-orange-500',
            'INFJ' => 'bg-teal-600',
            'INFP' => 'bg-cyan-600',
            'ENFJ' => 'bg-emerald-600',
            'ENFP' => 'bg-yellow-500',
            'ISTJ' => 'bg-indigo-700',
            'ISFJ' => 'bg-cyan-700',
            'ESTJ' => 'bg-blue-800',
            'ESFJ' => 'bg-blue-600',
            'ISTP' => 'bg-yellow-600',
            'ISFP' => 'bg-orange-600',
            'ESTP' => 'bg-red-600',
            'ESFP' => 'bg-pink-600',
        ];
    }
@endphp

<div class="space-y-8" x-data="personalityTest()" x-cloak>
    @if($personalityTest && $personalityTest->completed_at)
        @php
            $typeLabel = $mbtiTypes[$personalityTest->personality_type] ?? $personalityTest->personality_type;
            $colorClass = $mbtiColors[$personalityTest->personality_type] ?? $colors['primary_gradient'];
        @endphp
        <!-- Large Header Card -->
        <div class="{{ $colorClass }} rounded-3xl p-6 md:p-12 text-white shadow-xl">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-6 md:gap-8">
                <!-- Type Badge -->
                <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6 flex flex-shrink-0 items-center justify-center shadow-lg w-fit">
                    <span class="text-5xl md:text-6xl font-extrabold">{{ $personalityTest->personality_type }}</span>
                </div>

                <!-- Content -->
                <div class="flex-1 text-center md:text-left">
                    <h1 class="text-3xl md:text-5xl font-bold mb-4">{{ $typeLabel }}</h1>
                    <p class="text-white/95 text-base md:text-lg leading-relaxed mb-4">
                        {{ $personalityTest->personality_description }}
                    </p>
                    <p class="text-white/70 text-xs md:text-sm">Test passé le {{ $personalityTest->completed_at->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>

        @if($personalityTest->traits_scores)
            <!-- Dimensions Section -->
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
                <h2 class="text-2xl font-bold text-gray-900 mb-8">Tes dimensions de personnalité</h2>
                <div class="space-y-8">
                    @php
                        $dimensions = [
                            'E-I' => ['left' => 'Extraversion (E)', 'right' => 'Introversion (I)', 'color' => 'bg-blue-500'],
                            'S-N' => ['left' => 'Sensation (S)', 'right' => 'Intuition (N)', 'color' => 'bg-green-500'],
                            'T-F' => ['left' => 'Pensée (T)', 'right' => 'Sentiment (F)', 'color' => 'bg-pink-500'],
                            'J-P' => ['left' => 'Jugement (J)', 'right' => 'Perception (P)', 'color' => 'bg-yellow-500'],
                        ];
                    @endphp

                    @foreach($dimensions as $key => $dimension)
                        @php
                            // Get the scores for this dimension
                            $leftLetter = substr($key, 0, 1);
                            $rightLetter = substr($key, 2, 1);
                            $leftScore = $personalityTest->traits_scores[$leftLetter] ?? 50;
                            $rightScore = $personalityTest->traits_scores[$rightLetter] ?? 50;

                            // Determine which side is dominant
                            $dominantSide = $leftScore > $rightScore ? 'left' : 'right';
                            $percentage = $dominantSide === 'left' ? $leftScore : $rightScore;
                        @endphp

                        <div>
                            <!-- Labels -->
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-sm font-semibold text-gray-700">{{ $dimension['left'] }}</span>
                                <span class="text-sm font-semibold text-gray-700">{{ $dimension['right'] }}</span>
                            </div>

                            <!-- Progress Bar Container -->
                            <div class="relative h-3 bg-gray-200 rounded-full overflow-hidden">
                                @if($dominantSide === 'left')
                                    <!-- Fill from left -->
                                    <div class="{{ $dimension['color'] }} h-full rounded-full transition-all duration-500"
                                        style="width: {{ $percentage }}%"></div>
                                @else
                                    <!-- Fill from right -->
                                    <div class="{{ $dimension['color'] }} h-full rounded-full transition-all duration-500 ml-auto"
                                        style="width: {{ $percentage }}%"></div>
                                @endif
                            </div>

                            <!-- Percentage Labels -->
                            <div class="flex justify-between items-center mt-2">
                                <span
                                    class="text-xs text-gray-500">{{ $dominantSide === 'left' ? round($percentage) : 100 - round($percentage) }}%</span>
                                <span
                                    class="text-xs text-gray-500">{{ $dominantSide === 'right' ? round($percentage) : 100 - round($percentage) }}%</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Recommended Careers Section -->
        @if(isset($personalityTest->recommended_careers) && !empty($personalityTest->recommended_careers))
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Métiers recommandés pour ton profil</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($personalityTest->recommended_careers as $career)
                        <button type="button"
                            class="text-left w-full border border-gray-100 rounded-xl p-4 hover:shadow-md transition cursor-pointer group hover:border-blue-200 block"
                            style="cursor: pointer; display: block;" data-career="{{ json_encode($career) }}"
                            @click="viewCareerDetails(JSON.parse($el.dataset.career))">
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <h3 class="font-bold text-lg text-gray-800 group-hover:text-blue-600 transition">
                                    {{ $career['title'] ?? 'Métier' }}</h3>
                                <svg class="w-5 h-5 text-gray-300 group-hover:text-blue-500 transition-all transform group-hover:translate-x-1"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                            <p class="text-sm text-gray-600 mb-3">{{ $career['description'] ?? '' }}</p>
                            <div class="flex items-start gap-2 text-xs text-blue-600 bg-blue-50 p-2 rounded-lg">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Parfait pour ton profil</span>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Actions Buttons -->
        @if($theme === 'jeune')
            <div class="space-y-4 mt-8">
                <div class="flex flex-col md:flex-row gap-4">
                    <button @click="discussWithAI()"
                        class="flex-1 bg-primary-600 text-white rounded-xl py-4 px-6 font-bold text-center hover:bg-primary-700 transition flex items-center justify-center gap-2 shadow-md">
                        <span>Discuter avec l'IA sur mes résultats</span>
                    </button>
                    @if(Route::has('jeune.mentors'))
                        <a href="{{ route('jeune.mentors', ['for_profile' => 'true']) }}"
                            class="flex-1 bg-white border border-gray-200 text-gray-700 rounded-xl py-4 px-6 font-bold text-center hover:bg-gray-50 transition flex items-center justify-center gap-2 shadow-sm">
                            <span>Voir des mentors dans mes domaines</span>
                        </a>
                    @endif
                </div>

                @php
                    $exportPdfRoute = $theme === 'jeune' ? 'jeune.personality.export-pdf' : 'mentor.personality.export-pdf';
                @endphp
                <a href="{{ route($exportPdfRoute) }}"
                    class="w-full bg-red-500 text-white rounded-xl py-4 px-6 font-bold text-center hover:bg-red-600 transition flex items-center justify-center gap-2 shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                        </path>
                    </svg>
                    <span>Télécharger mon test en PDF</span>
                </a>

                <!-- SECTION RECOMMANDATIONS : ÉTABLISSEMENTS (AJOUT DYNAMIQUE) -->
                @if($theme === 'jeune')
                <div x-data="recommendationsSystem()" x-init="init()" class="mt-12 py-10 bg-gray-50/80 -mx-6 px-6 sm:-mx-8 sm:px-8 border-t border-gray-100 shadow-inner rounded-b-[2rem]">
                    <style>
                        .no-scrollbar::-webkit-scrollbar { display: none; }
                        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
                    </style>

                    <!-- HEADER CARROUSEL 1 -->
                    <div class="flex items-end justify-between mb-8">
                        <div>
                            <span class="inline-block py-1 px-3 bg-indigo-100 text-indigo-700 text-[10px] font-black uppercase tracking-widest rounded-full mb-3">Opportunités d'études</span>
                            <h2 class="text-3xl font-black text-gray-900 tracking-tight">Formations recommandées</h2>
                            <p class="text-gray-500 mt-1">Établissements au Bénin et en Afrique adaptés à ton profil <span class="font-bold text-indigo-600 uppercase">{{ $personalityTest->personality_type }}</span></p>
                        </div>
                        <div class="flex gap-2 pb-1">
                            <button @click="scrollCarousel('formCarousel', -1)" class="w-12 h-12 rounded-2xl bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:border-indigo-200 transition shadow-sm group">
                                <i class="fas fa-chevron-left group-hover:-translate-x-1 transition-transform"></i>
                            </button>
                            <button @click="scrollCarousel('formCarousel', 1)" class="w-12 h-12 rounded-2xl bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:border-indigo-200 transition shadow-sm group">
                                <i class="fas fa-chevron-right group-hover:translate-x-1 transition-transform"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Carousel Établissements -->
                    <div x-ref="formCarousel" class="flex gap-6 overflow-x-auto pb-10 snap-x no-scrollbar scroll-smooth">
                        <template x-for="est in establishments" :key="est.id">
                            <div class="flex-none w-80 snap-start">
                                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-2xl hover:-translate-y-2 transition duration-500 group flex flex-col h-full bg-gradient-to-br from-white to-gray-50/50">
                                    <!-- Photo -->
                                    <div class="relative h-48 overflow-hidden cursor-pointer" @click="$dispatch('open-details', { est })">
                                        <img :src="est.photo_path ? '/storage/'+est.photo_path : 'https://images.unsplash.com/photo-1541339907198-e08756ebafe3?q=80&w=800'" 
                                            class="w-full h-full object-cover group-hover:scale-110 transition duration-1000">
                                        <div class="absolute inset-0 bg-gradient-to-t from-gray-900/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                                        <div class="absolute top-4 left-4">
                                            <span class="px-3 py-1.5 bg-white/95 backdrop-blur-sm rounded-xl text-[10px] font-black uppercase text-indigo-700 shadow-sm border border-white" x-text="est.type === 'university' ? '🎓 Université' : '⚡ Formation Pro'"></span>
                                        </div>
                                    </div>

                                    <!-- Body -->
                                    <div class="p-6 flex flex-col flex-1">
                                        <h3 class="font-black text-gray-900 text-lg mb-1 line-clamp-1" x-text="est.name"></h3>
                                        <div class="flex items-center gap-2 mb-4">
                                            <i class="fas fa-map-marker-alt text-rose-500 text-xs"></i>
                                            <span class="text-xs font-bold text-gray-500 uppercase tracking-tighter" x-text="(est.city ? est.city + ', ' : '') + est.country"></span>
                                        </div>
                                        
                                        <!-- Clic rapide / Collecte Tel -->
                                        <div class="mt-auto space-y-3">
                                            <!-- Widget Saisie Tel if missing -->
                                            <div x-show="!userHasPhone && activePhoneInput === est.id" 
                                                x-transition:enter="transition ease-out duration-300"
                                                x-transition:enter-start="opacity-0 translate-y-4"
                                                class="p-4 bg-rose-50 rounded-2xl border border-rose-100">
                                                <p class="text-xs font-black text-rose-600 uppercase mb-2">Il manque ton numéro :</p>
                                                <div class="flex gap-2">
                                                    <input type="tel" x-model="tempPhone" placeholder="ex: 97000000" 
                                                        class="flex-1 text-sm border-white rounded-xl py-2 px-3 focus:ring-rose-500 focus:border-rose-500 shadow-inner">
                                                    <button @click="handleInterest(est)" class="p-2 bg-rose-600 text-white rounded-xl hover:bg-rose-700 shadow-md">
                                                        <i class="fas fa-arrow-right"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <button @click="handleInterest(est)" 
                                                class="w-full py-4 rounded-2xl font-black text-sm transition-all duration-300 flex items-center justify-center gap-2 overflow-hidden relative group"
                                                :class="est.user_has_interest ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-gray-900 text-white hover:bg-indigo-600 shadow-xl shadow-gray-200'">
                                                <span x-show="!est.user_has_interest" class="flex items-center gap-2">
                                                    <span x-text="(!userHasPhone && activePhoneInput === est.id) ? 'Valider le numéro' : 'Je suis intéressé'"></span>
                                                    <i class="fas fa-heart text-white group-hover:scale-125 transition-transform" x-show="!(!userHasPhone && activePhoneInput === est.id)"></i>
                                                </span>
                                                <span x-show="est.user_has_interest" class="flex items-center gap-2 scale-110">
                                                    <i class="fas fa-check-circle animate-bounce"></i> Brillio te recontacte !
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Skeleton si chargement -->
                        <template x-if="loading">
                             <div class="flex gap-6 w-full overflow-hidden">
                                <template x-for="i in 4">
                                    <div class="w-80 h-96 bg-gray-200 animate-pulse rounded-3xl"></div>
                                </template>
                             </div>
                        </template>

                        <!-- Si vide -->
                        <template x-if="!loading && establishments.length === 0">
                             <div class="w-full py-12 text-center bg-white rounded-3xl border-2 border-dashed border-gray-200">
                                <i class="fas fa-search text-gray-300 text-4xl mb-4"></i>
                                <p class="text-gray-500 font-bold">L'IA prépare de nouvelles opportunités pour toi...</p>
                             </div>
                        </template>
                    </div>


                </div>

                <!-- SIDE PANEL (DRAWER) -->
                <div x-data="recommendationsSystem()" @open-details.window="openDetails($event.detail.est)" x-show="sidebarOpen" class="fixed inset-0 z-[100] overflow-hidden" x-cloak>
                    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" 
                        x-show="sidebarOpen" x-transition:enter="duration-500 ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" 
                        x-transition:leave="duration-500 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                        @click="sidebarOpen = false"></div>

                    <div class="absolute inset-y-0 right-0 max-w-full flex">
                        <div class="w-screen max-w-xl transform transition ease-in-out duration-700"
                            x-show="sidebarOpen" x-transition:enter="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="translate-x-full">
                            
                            <div class="h-full flex flex-col bg-white shadow-2xl rounded-l-[3rem] overflow-hidden">
                                <!-- Banner Details -->
                                <div class="relative h-80 flex-none group">
                                    <img :src="estDetails?.photo_path ? '/storage/'+estDetails.photo_path : 'https://images.unsplash.com/photo-1541339907198-e08756ebafe3?q=80&w=800'" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-gradient-to-t from-white via-white/20 to-transparent"></div>
                                    <button @click="sidebarOpen = false" class="absolute top-6 left-6 w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-xl text-white flex items-center justify-center hover:bg-white hover:text-gray-900 transition-all duration-300 shadow-xl border border-white/30">
                                        <i class="fas fa-times text-xl"></i>
                                    </button>
                                    <div class="absolute bottom-8 left-8 right-8">
                                        <h2 class="text-4xl font-black text-gray-900 leading-tight" x-text="estDetails?.name"></h2>
                                        <div class="flex items-center gap-3 mt-4">
                                            <span class="px-4 py-1.5 bg-indigo-600 text-white rounded-full text-xs font-black uppercase shadow-lg shadow-indigo-100" x-text="estDetails?.type === 'university' ? '🎓 Université' : '⚡ Formation'"></span>
                                            <span class="text-gray-600 font-bold flex items-center gap-2">
                                                <i class="fas fa-map-marker-alt text-rose-500"></i>
                                                <span x-text="estDetails?.city + ', ' + estDetails?.country"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex-1 overflow-y-auto p-10 space-y-12 no-scrollbar">
                                    <!-- A propos -->
                                    <section>
                                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.3em] mb-6 flex items-center gap-3">
                                            <span class="w-8 h-[2px] bg-indigo-600"></span> Présentation
                                        </h3>
                                        <p class="text-xl text-gray-700 leading-relaxed font-medium" x-text="estDetails?.description"></p>
                                        
                                        <template x-if="estDetails?.google_maps_url">
                                            <div class="mt-6">
                                                <a :href="estDetails?.google_maps_url" target="_blank" class="inline-flex items-center gap-2 bg-red-50 text-red-600 px-4 py-2 rounded-xl text-sm font-bold hover:bg-red-100 transition">
                                                    <i class="fas fa-map-marker-alt"></i> Voir sur Google Maps
                                                </a>
                                            </div>
                                        </template>
                                    </section>

                                    <!-- Grid Info -->
                                    <div class="grid grid-cols-1 gap-6">
                                        <div class="bg-gray-50/80 p-6 rounded-[2rem] border border-gray-100">
                                            <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-2">Frais de scolarité</p>
                                            <p class="text-2xl font-black text-gray-900 flex items-baseline gap-2">
                                                <span x-text="estDetails?.tuition_min ? 'À partir de ' + new Intl.NumberFormat().format(estDetails.tuition_min) + ' FCFA' : 'Sur demande'"></span>
                                                <template x-if="estDetails?.tuition_max">
                                                    <span class="text-sm font-bold text-gray-400 uppercase tracking-widest ml-2" x-text="'Jusqu\'à ' + new Intl.NumberFormat().format(estDetails.tuition_max) + ' FCFA'"></span>
                                                </template>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- GALERIE -->
                                    <template x-if="estDetails?.gallery && estDetails.gallery.length > 0">
                                        <section>
                                            <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.3em] mb-6 flex items-center gap-3">
                                                <span class="w-8 h-[2px] bg-sky-500"></span> Galerie Photo
                                            </h3>
                                            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                                                <template x-for="(image, index) in estDetails.gallery" :key="index">
                                                    <a :href="'/storage/' + image" target="_blank" class="relative block h-28 rounded-2xl overflow-hidden cursor-pointer group shadow-sm">
                                                        <img :src="'/storage/' + image" class="w-full h-full object-cover group-hover:scale-110 transition duration-700">
                                                        <div class="absolute inset-0 bg-gray-900/10 group-hover:bg-transparent transition duration-300"></div>
                                                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                            <div class="w-10 h-10 bg-white/30 backdrop-blur-md rounded-full flex items-center justify-center text-white">
                                                                <i class="fas fa-external-link-alt"></i>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </template>
                                            </div>
                                        </section>
                                    </template>

                                    <!-- BROCHURES -->
                                    <template x-if="estDetails?.brochures && estDetails.brochures.length > 0">
                                        <section>
                                            <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.3em] mb-6 flex items-center gap-3">
                                                <span class="w-8 h-[2px] bg-orange-500"></span> Fichiers
                                            </h3>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                <template x-for="(brochure, index) in estDetails.brochures" :key="index">
                                                    <a :href="'/storage/' + brochure" target="_blank" class="flex items-center gap-4 p-4 border border-gray-100 rounded-2xl bg-white hover:bg-orange-50 hover:border-orange-100 transition group shadow-sm">
                                                        <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                                            <i class="fas fa-file-alt"></i>
                                                        </div>
                                                        <div class="flex-1 overflow-hidden">
                                                            <p class="font-bold text-sm text-gray-900 truncate" x-text="'Brochure ' + (index+1)"></p>
                                                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">Ouvrir</p>
                                                        </div>
                                                        <i class="fas fa-external-link-alt text-gray-300 group-hover:text-orange-400 transition"></i>
                                                    </a>
                                                </template>
                                            </div>
                                        </section>
                                    </template>

                                    <!-- VIDEOS -->
                                    <template x-if="estDetails?.presentation_videos && estDetails.presentation_videos.length > 0">
                                        <section>
                                            <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.3em] mb-6 flex items-center gap-3">
                                                <span class="w-8 h-[2px] bg-red-600"></span> Vidéos
                                            </h3>
                                            <div class="flex flex-col gap-6">
                                                <template x-for="(video, index) in estDetails.presentation_videos" :key="index">
                                                    <div class="w-full rounded-2xl overflow-hidden shadow-sm border border-gray-100 aspect-video bg-gray-100 relative">
                                                        <iframe title="Vidéo de présentation de l'établissement" class="absolute inset-0 w-full h-full" :src="formatYoutubeUrl(video)" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                                    </div>
                                                </template>
                                            </div>
                                        </section>
                                    </template>

                                    <!-- FORMULAIRE PRECIS -->
                                    <template x-if="estDetails?.has_precise_form">
                                        <section class="bg-gradient-to-br from-indigo-600 to-slate-900 p-10 rounded-[3rem] text-white shadow-2xl relative overflow-hidden">
                                            <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
                                            <div class="relative">
                                                <h3 class="text-3xl font-black mb-2">Postuler maintenant</h3>
                                                <p class="text-indigo-200 mb-8 font-medium">Laisse tes coordonnées pour être contacté en priorité.</p>
                                                
                                                <form @submit.prevent="submitPreciseInterest" class="space-y-5">
                                                    <template x-for="(field, idx) in estDetails?.precise_form_config" :key="idx">
                                                        <div>
                                                            <label class="block text-xs font-black text-indigo-100 uppercase tracking-widest mb-2 ml-1" x-text="field.label"></label>
                                                            
                                                            <template x-if="field.type === 'text'">
                                                                <input type="text" x-model="formData[field.label]" required class="w-full bg-white/10 border border-white/20 rounded-2xl py-4 px-5 text-white placeholder-white/40 focus:bg-white/20 focus:ring-0 backdrop-blur-md transition-all">
                                                            </template>

                                                            <template x-if="field.type === 'select'">
                                                                <select x-model="formData[field.label]" required class="w-full bg-white/10 border border-white/20 rounded-2xl py-4 px-5 text-white focus:bg-white/20 focus:ring-0 backdrop-blur-md">
                                                                    <option value="" class="bg-slate-800 text-white">Choisir...</option>
                                                                    <template x-for="opt in (field.options || '').split(',')">
                                                                        <option :value="opt.trim()" x-text="opt.trim()" class="bg-slate-800 text-white"></option>
                                                                    </template>
                                                                </select>
                                                            </template>
                                                        </div>
                                                    </template>
                                                    
                                                    <!-- Champ téléphone manquant rouge -->
                                                    <template x-if="!userHasPhone">
                                                        <div class="p-4 bg-rose-50 rounded-2xl border border-rose-100 mt-2 mb-4 shadow-sm">
                                                            <p class="text-[11px] font-black text-rose-600 mb-2 uppercase tracking-widest flex items-center gap-2">
                                                                Il manque ton numéro :
                                                            </p>
                                                            <input type="tel" x-model="tempPhone" placeholder="ex: 97000000" class="w-full bg-white border-rose-200 rounded-xl text-sm px-4 py-3 font-bold focus:ring-2 focus:ring-rose-500 text-gray-900" required>
                                                        </div>
                                                    </template>

                                                    <button type="submit" class="w-full py-5 bg-white text-gray-900 rounded-2xl font-black text-lg hover:scale-[1.02] transition duration-300 shadow-xl mt-4">
                                                        Envoyer ma candidature <i class="fas fa-paper-plane ml-2"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </section>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
                @endif

                <!-- Centered Retake Button -->
                <div class="flex justify-center pt-2">
                    <button @click="retakeTest()"
                        class="px-8 py-3 bg-white border-2 border-primary-500 text-primary-600 rounded-xl font-bold hover:bg-primary-50 transition flex items-center gap-2 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Refaire le test
                    </button>
                </div>
            </div>
        @else
            <!-- Mentor specific buttons -->
            <div class="space-y-4 mt-8">
                @php
                    $exportPdfRoute = 'mentor.personality.export-pdf';
                @endphp
                <a href="{{ route($exportPdfRoute) }}"
                    class="w-full bg-red-500 text-white rounded-xl py-4 px-6 font-bold text-center hover:bg-red-600 transition flex items-center justify-center gap-2 shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                        </path>
                    </svg>
                    <span>Télécharger mon test en PDF</span>
                </a>
                <div class="flex justify-center pt-2">
                    <button @click="retakeTest()"
                        class="px-8 py-3 bg-white border-2 border-orange-500 text-orange-600 rounded-xl font-bold hover:bg-orange-50 transition flex items-center gap-2 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Refaire le test
                    </button>
                </div>
            </div>
        @endif

        <!-- History Section -->
        @if(isset($testHistory) && count($testHistory) > 0)
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 mt-8">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Historique de tes tests</h2>
                <p class="text-sm text-gray-500 mb-4">Voici l'évolution de ta personnalité au fil du temps</p>
                <div class="space-y-4">
                    @foreach($testHistory as $historyTest)
                        @php
                            // Map personality type to solid color for badge
                            $badgeColor = match ($historyTest->personality_type) {
                                'INTJ', 'INTP', 'ENTJ', 'ENTP' => 'bg-purple-600',
                                'INFJ', 'INFP', 'ENFJ', 'ENFP' => 'bg-green-600',
                                'ISTJ', 'ISFJ', 'ESTJ', 'ESFJ' => 'bg-blue-600',
                                'ISTP', 'ISFP', 'ESTP', 'ESFP' => 'bg-orange-600',
                                default => 'bg-gray-500',
                            };
                        @endphp
                        <div class="flex items-center justify-between p-4 border border-gray-100 rounded-xl hover:bg-gray-50 transition"
                            x-data="{
                                historyData: {
                                    personality_type: @js($historyTest->personality_type),
                                    personality_label: @js($mbtiTypes[$historyTest->personality_type] ?? $historyTest->personality_type),
                                    personality_description: @js($historyTest->personality_description ?? ''),
                                    completed_at: @js($historyTest->completed_at),
                                    traits_scores: @js($historyTest->traits_scores ?? []),
                                    recommended_careers: @js($historyTest->recommended_careers ?? [])
                                }
                            }">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 rounded-lg flex items-center justify-center font-bold text-white text-sm {{ $badgeColor }}">
                                    {{ $historyTest->personality_type }}
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900">
                                        {{ $mbtiTypes[$historyTest->personality_type] ?? $historyTest->personality_type }}
                                    </h4>
                                    <p class="text-xs text-gray-500">{{ $historyTest->completed_at->format('d/m/Y à H:i') }}</p>
                                </div>
                            </div>
                            <button @click="viewHistoryDetails(historyData)"
                                class="text-blue-600 text-sm font-medium hover:underline">Voir détails</button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- History Details Modal -->
        <div x-show="showHistoryModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak style="display: none;"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="closeHistoryModal()"></div>

            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-3xl overflow-hidden">
                    <!-- Header -->
                    <div class="{{ $colors['primary_gradient'] }} px-8 py-6 text-white flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold">Détails du test</h2>
                            <p class="text-white/80 text-sm mt-1"
                                x-text="selectedHistory?.completed_at ? new Date(selectedHistory.completed_at).toLocaleDateString('fr-FR', {year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'}) : ''">
                            </p>
                        </div>
                        <button @click="closeHistoryModal()"
                            class="p-2 text-white/80 hover:text-white rounded-lg hover:bg-white/10 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Content -->
                    <div class="p-8 max-h-[70vh] overflow-y-auto">
                        <template x-if="selectedHistory">
                            <div class="space-y-6">
                                <!-- Type Info -->
                                <div class="flex flex-col md:flex-row items-center md:items-start gap-4 text-center md:text-left">
                                    <div
                                        class="w-20 h-20 rounded-2xl flex flex-shrink-0 items-center justify-center font-bold text-white text-3xl {{ $colors['primary_gradient'] }}">
                                        <span x-text="selectedHistory.personality_type"></span>
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-bold text-gray-900"
                                            x-text="selectedHistory.personality_label || selectedHistory.personality_type">
                                        </h3>
                                        <p class="text-gray-600 mt-1" x-text="selectedHistory.personality_description"></p>
                                    </div>
                                </div>

                                <!-- Traits Scores -->
                                <template x-if="selectedHistory.traits_scores">
                                    <div>
                                        <h4 class="text-lg font-bold text-gray-900 mb-4">Dimensions de personnalité</h4>
                                        <div class="space-y-4">
                                            <template x-for="(score, trait) in selectedHistory.traits_scores" :key="trait">
                                                <div>
                                                    <div
                                                        class="flex justify-between text-sm font-medium text-gray-700 mb-2">
                                                        <span x-text="trait"></span>
                                                        <span x-text="Math.round(score) + '%'"></span>
                                                    </div>
                                                    <div class="h-3 bg-gray-200 rounded-full overflow-hidden">
                                                        <div class="h-full {{ $colors['primary_gradient'] }} rounded-full transition-all"
                                                            :style="'width: ' + score + '%'"></div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                <!-- Recommended Careers -->
                                <template
                                    x-if="selectedHistory.recommended_careers && selectedHistory.recommended_careers.length > 0">
                                    <div>
                                        <h4 class="text-lg font-bold text-gray-900 mb-4">Métiers recommandés</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <template x-for="career in selectedHistory.recommended_careers"
                                                :key="career.title">
                                                <div class="border border-gray-200 rounded-lg p-3">
                                                    <h5 class="font-bold text-gray-800" x-text="career.title"></h5>
                                                    <p class="text-sm text-gray-600 mt-1" x-text="career.description"></p>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <!-- Footer -->
                    <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                        <button @click="closeHistoryModal()"
                            class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-medium transition">
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>

    @else
        <!-- Intro Text if no test taken -->
        <div class="{{ $colors['primary_gradient'] }} rounded-3xl p-8 text-white text-center shadow-lg">
            <h1 class="text-3xl font-bold mb-4">Découvrez votre type
                {{ $theme === 'mentor' ? 'de mentor' : 'de personnalité' }}
            </h1>
            <p class="text-white/90 text-lg mb-8 max-w-2xl mx-auto">
                Le test MBTI vous aidera à mieux connaître vos atouts.
                Cela prend environ 10 minutes.
            </p>
            <button @click="startTest()"
                class="px-8 py-4 {{ $colors['button_bg'] }} {{ $colors['button_text'] }} rounded-xl font-bold text-lg hover:shadow-lg hover:scale-105 transition transform shadow-md">
                Commencer le test
            </button>
        </div>

        <!-- 16 Types Grid Info -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Les 16 types de personnalités</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach($mbtiTypes as $type => $label)
                    @php $gridColor = $mbtiColors[$type] ?? $colors['primary_gradient']; @endphp
                    <div
                        class="{{ $gridColor }} rounded-xl p-4 text-white hover:scale-105 transition duration-300 cursor-default shadow-md">
                        <span class="font-extrabold text-xl block mb-1">{{ $type }}</span>
                        <span class="text-white/90 text-sm font-medium">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Modal du Test -->
    <div x-show="showTest" class="fixed inset-0 z-50 overflow-y-auto" x-cloak style="display: none;"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="closeTest()"></div>

        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div
                class="relative bg-white rounded-3xl shadow-2xl w-full max-w-5xl overflow-hidden flex flex-col max-h-[90vh] {{ $theme === 'mentor' ? 'border-t-4 border-orange-500' : 'border-t-4 border-purple-500' }}">

                <!-- Header Modal -->
                <div
                    class="bg-gray-50 px-8 py-4 border-b border-gray-100 flex justify-between items-center sticky top-0 z-10">
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Test de
                            personnalité</span>
                        <h2 class="text-xl font-bold text-gray-900">Question <span
                                x-text="currentQuestion + 1"></span>/<span x-text="questions.length"></span></h2>
                    </div>
                    <button @click.stop="closeTest()"
                        class="relative overflow-hidden group transition-all duration-300 rounded-xl flex items-center justify-center min-w-[40px] h-10 px-2"
                        :class="confirmClose ? 'bg-red-50 border border-red-100 shadow-sm' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-100'">
                        
                        <div class="flex items-center gap-2">
                            <span x-show="confirmClose" 
                                  x-transition:enter="transition ease-out duration-200"
                                  x-transition:enter-start="opacity-0 -translate-x-2"
                                  x-transition:enter-end="opacity-100 translate-x-0"
                                  class="text-sm font-bold text-red-600 whitespace-nowrap">
                                Quitter ?
                            </span>
                            <svg class="w-6 h-6 transition-transform duration-300" 
                                 :class="confirmClose ? 'text-red-600 scale-90' : 'group-hover:rotate-90'"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                    </button>
                </div>

                <!-- Progress Bar -->
                <div class="h-1 bg-gray-100 w-full">
                    <div class="h-full {{ $colors['primary_bg'] }} transition-all duration-300 ease-out"
                        :style="'width: ' + ((Object.keys(answers).length / questions.length) * 100) + '%'"></div>
                </div>

                <!-- Loading State -->
                <div x-show="loading" class="flex-1 flex items-center justify-center p-12">
                    <div class="flex flex-col items-center max-w-md text-center">
                        <div class="relative mb-6">
                            <div
                                class="animate-spin rounded-full h-16 w-16 border-4 {{ $colors['loading_border'] }} border-t-transparent">
                            </div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <svg class="w-6 h-6 {{ $colors['primary_text'] }} animate-pulse" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9l-.707.707M12 21v-1m4.243-4.243l.707.707M16 9.5a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2" x-text="loadingTitle"></h3>
                        <p class="text-gray-500" x-text="loadingSubtitle"></p>

                        <!-- Tip -->
                        <div class="mt-8 p-4 bg-blue-50 rounded-2xl border border-blue-100 flex gap-3 text-left"
                            x-show="isPersonalizing">
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-1">Le savais-tu ?</p>
                                <p class="text-sm text-blue-800">Brillio adapte chaque question à ton profil actuel pour
                                    que tes résultats soient les plus précis possibles.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Questions -->
                <div x-show="!loading && testStarted" class="flex-1 overflow-y-auto p-8 lg:p-12">
                    <div class="max-w-3xl mx-auto">
                        <template x-if="questions[currentQuestion]">
                            <div class="space-y-12">
                                <h3 class="text-2xl md:text-3xl font-bold text-gray-900 text-center leading-tight min-h-[120px] flex items-center justify-center p-4 bg-gray-50 rounded-2xl"
                                    x-text="questions[currentQuestion].text || questions[currentQuestion].question_text">
                                </h3>

                                <!-- Semantic Differential Scale (5 Options) -->
                                <div class="relative py-8">
                                    <!-- Labels -->
                                    <div class="flex justify-between items-center mb-8 px-2 md:px-6">
                                        <span
                                            class="text-lg font-bold {{ $colors['primary_text'] }} w-1/3 text-left leading-tight"
                                            x-text="questions[currentQuestion].left_trait || questions[currentQuestion].left_trait_fr || 'Pas d\'accord'"></span>
                                        <span class="text-sm font-semibold text-gray-400">Neutre</span>
                                        <span
                                            class="text-lg font-bold {{ $colors['secondary_text'] }} w-1/3 text-right leading-tight"
                                            x-text="questions[currentQuestion].right_trait || questions[currentQuestion].right_trait_fr || 'D\'accord'"></span>
                                    </div>

                                    <!-- Track Line -->
                                    <div
                                        class="absolute top-1/2 left-0 right-0 h-1 bg-gray-100 -z-10 transform -translate-y-1/2 rounded-full mx-6 md:mx-12">
                                    </div>

                                    <!-- Options -->
                                    <div class="flex justify-between items-center max-w-2xl mx-auto px-4 gap-2">
                                        <!-- Option 1 (Strongly Left - Primary) Big -->
                                        <button @click="selectAnswer(1); setTimeout(() => nextQuestion(), 350)"
                                            class="group relative w-16 h-16 rounded-full flex items-center justify-center transition-all duration-200 focus:outline-none"
                                            :class="answers[questions[currentQuestion].id] === 1 ? 'scale-110 ring-4 {{ $colors['primary_light_ring'] }}' : 'hover:scale-110'">
                                            <div class="w-12 h-12 rounded-full border-4 transition-all duration-200"
                                                :class="answers[questions[currentQuestion].id] === 1 ? '{{ $colors['primary_border'] }} {{ $colors['primary_bg'] }}' : '{{ $colors['primary_border_light'] }} group-hover:{{ $colors['primary_border'] }} bg-white'">
                                            </div>
                                        </button>

                                        <!-- Option 2 (Med Left) Med -->
                                        <button @click="selectAnswer(2); setTimeout(() => nextQuestion(), 350)"
                                            class="group relative w-12 h-12 rounded-full flex items-center justify-center transition-all duration-200 focus:outline-none"
                                            :class="answers[questions[currentQuestion].id] === 2 ? 'scale-110 ring-4 {{ $colors['primary_light_ring'] }}' : 'hover:scale-110'">
                                            <div class="w-9 h-9 rounded-full border-4 transition-all duration-200"
                                                :class="answers[questions[currentQuestion].id] === 2 ? '{{ $colors['primary_border'] }} {{ $colors['primary_light_bg'] }}' : '{{ $colors['primary_border_light'] }} group-hover:{{ $colors['primary_border'] }} bg-white'">
                                            </div>
                                        </button>

                                        <!-- Option 3 (Neutral) Smallest -->
                                        <button @click="selectAnswer(3); setTimeout(() => nextQuestion(), 350)"
                                            class="group relative w-10 h-10 rounded-full flex items-center justify-center transition-all duration-200 focus:outline-none"
                                            :class="answers[questions[currentQuestion].id] === 3 ? 'scale-110 ring-4 ring-gray-200 shadow-md' : 'hover:scale-110'">
                                            <div class="w-6 h-6 rounded-full border-2 transition-all duration-200"
                                                :class="answers[questions[currentQuestion].id] === 3 ? 'border-gray-400 bg-gray-400' : 'border-gray-300 group-hover:border-gray-400 bg-white'">
                                            </div>
                                        </button>

                                        <!-- Option 4 (Med Right) Med -->
                                        <button @click="selectAnswer(4); setTimeout(() => nextQuestion(), 350)"
                                            class="group relative w-12 h-12 rounded-full flex items-center justify-center transition-all duration-200 focus:outline-none"
                                            :class="answers[questions[currentQuestion].id] === 4 ? 'scale-110 ring-4 {{ $colors['secondary_light_ring'] }}' : 'hover:scale-110'">
                                            <div class="w-9 h-9 rounded-full border-4 transition-all duration-200"
                                                :class="answers[questions[currentQuestion].id] === 4 ? '{{ $colors['secondary_border'] }} {{ $colors['secondary_light_bg'] }}' : '{{ $colors['secondary_border_light'] }} group-hover:{{ $colors['secondary_border'] }} bg-white'">
                                            </div>
                                        </button>

                                        <!-- Option 5 (Strongly Right) Big -->
                                        <button @click="selectAnswer(5); setTimeout(() => nextQuestion(), 350)"
                                            class="group relative w-16 h-16 rounded-full flex items-center justify-center transition-all duration-200 focus:outline-none"
                                            :class="answers[questions[currentQuestion].id] === 5 ? 'scale-110 ring-4 {{ $colors['secondary_light_ring'] }}' : 'hover:scale-110'">
                                            <div class="w-12 h-12 rounded-full border-4 transition-all duration-200"
                                                :class="answers[questions[currentQuestion].id] === 5 ? '{{ $colors['secondary_border'] }} {{ $colors['secondary_bg'] }}' : '{{ $colors['secondary_border_light'] }} group-hover:{{ $colors['secondary_border'] }} bg-white'">
                                            </div>
                                        </button>
                                    </div>

                                    <div class="flex justify-between items-start max-w-2xl mx-auto px-4 gap-2 mt-2">
                                        <span
                                            class="w-16 text-center text-[10px] leading-tight font-medium text-gray-500">Toujours
                                            comme ça</span>
                                        <span
                                            class="w-12 text-center text-[10px] leading-tight font-medium text-gray-500">Souvent
                                            comme ça</span>
                                        <span
                                            class="w-10 text-center text-[10px] leading-tight font-medium text-gray-500">Ça
                                            dépend</span>
                                        <span
                                            class="w-12 text-center text-[10px] leading-tight font-medium text-gray-500">Souvent
                                            comme ça</span>
                                        <span
                                            class="w-16 text-center text-[10px] leading-tight font-medium text-gray-500">Toujours
                                            comme ça</span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Footer Controls -->
                <div
                    class="bg-gray-50 px-8 py-4 border-t border-gray-100 flex justify-between items-center sticky bottom-0 z-10">
                    <button @click="previousQuestion()" :disabled="currentQuestion === 0"
                        class="px-6 py-3 rounded-xl font-bold flex items-center gap-2 transition disabled:opacity-30 disabled:cursor-not-allowed hover:bg-gray-200 text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Précédent
                    </button>

                    <div x-show="currentQuestion === questions.length - 1">
                        <button @click="submitTest()" :disabled="!allAnswered || submitting"
                            class="px-8 py-3 {{ $colors['secondary_gradient'] }} text-white rounded-xl font-bold hover:shadow-lg hover:scale-105 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                            <span x-show="submitting"
                                class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span x-text="submitting ? 'Analyse en cours...' : 'Voir mes résultats'"></span>
                        </button>
                    </div>
                    <div x-show="currentQuestion < questions.length - 1">
                        <button @click="nextQuestion()" :disabled="!answers[questions[currentQuestion]?.id]"
                            class="px-6 py-3 bg-gray-900 text-white rounded-xl font-bold flex items-center gap-2 hover:bg-gray-800 transition disabled:opacity-50 disabled:cursor-not-allowed">
                            Suivant
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Détails Métier -->
    <div x-show="showCareerModal" class="fixed inset-0 z-[60] overflow-y-auto" x-cloak style="display: none;"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        <div class="fixed inset-0 bg-black/60 backdrop-blur-md" @click="closeCareerModal()"></div>

        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div
                class="relative bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden transform transition-all">
                <!-- Close Button -->
                <button @click="closeCareerModal()"
                    class="absolute top-4 right-4 z-10 p-2 bg-white/10 hover:bg-gray-100 text-gray-400 hover:text-gray-600 rounded-full transition shadow-sm border border-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Loading State -->
                <div x-show="loadingCareer" class="p-12 flex flex-col items-center justify-center space-y-4">
                    <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-500 border-t-transparent">
                    </div>
                    <p class="text-gray-500 font-medium">Récupération de la fiche métier...</p>
                </div>

                <!-- Career Content -->
                <template x-if="!loadingCareer && selectedCareer">
                    <div class="flex flex-col">
                        <!-- Header with dynamic background -->
                        <div class="relative bg-gradient-to-br from-blue-600 to-indigo-700 px-8 py-10 text-white">
                            <div
                                class="flex items-center gap-2 text-blue-100 text-sm font-bold uppercase tracking-wider mb-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <span>Fiche Métier</span>
                            </div>
                            <h2 class="text-3xl md:text-4xl font-extrabold" x-text="selectedCareer.title"></h2>

                            <!-- AI Impact Badge (Header) -->
                            <template x-if="selectedCareer.ai_impact_level">
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1.5"
                                        :class="{
                                              'bg-emerald-500/20 text-emerald-100 border border-emerald-500/30': selectedCareer.ai_impact_level === 'low',
                                              'bg-amber-500/20 text-amber-100 border border-amber-500/30': selectedCareer.ai_impact_level === 'medium',
                                              'bg-rose-500/20 text-rose-100 border border-rose-500/30': selectedCareer.ai_impact_level === 'high'
                                          }">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1a1 1 0 112 0v1a1 1 0 11-2 0zM13.536 14.243a1 1 0 011.414 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707zM6.464 14.95a1 1 0 11-1.414 1.414l-.707-.707a1 1 0 011.414-1.414l.707.707z" />
                                        </svg>
                                        <span
                                            x-text="'IA : ' + (selectedCareer.ai_impact_level === 'low' ? 'Profil Stable' : (selectedCareer.ai_impact_level === 'medium' ? 'Profil Assisté' : 'Profil Challengé'))"></span>
                                    </span>

                                    <template x-if="selectedCareer.demand_level">
                                        <span
                                            class="px-3 py-1 bg-white/10 border border-white/20 rounded-full text-xs font-bold text-white"
                                            x-text="'Demande : ' + selectedCareer.demand_level"></span>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <!-- Content Body -->
                        <div class="p-8 space-y-8 max-h-[60vh] overflow-y-auto bg-gray-50/50">
                            <!-- Description -->
                            <section>
                                <h4
                                    class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                    En quelques mots
                                </h4>
                                <p class="text-gray-700 leading-relaxed" x-text="selectedCareer.description"></p>
                            </section>

                            <!-- Africa Context -->
                            <template x-if="selectedCareer.african_context">
                                <section class="bg-blue-50 rounded-2xl p-6 border border-blue-100">
                                    <h4 class="text-blue-900 font-bold mb-3 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 002 2 2 2 0 012 2v.654M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Pourquoi en Afrique ?
                                    </h4>
                                    <p class="text-blue-800 leading-relaxed text-sm"
                                        x-text="selectedCareer.african_context"></p>
                                </section>
                            </template>

                            <!-- Future Prospects -->
                            <template x-if="selectedCareer.future_prospects">
                                <section>
                                    <h4
                                        class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Perspectives d'avenir
                                    </h4>
                                    <p class="text-gray-700 leading-relaxed text-sm"
                                        x-text="selectedCareer.future_prospects"></p>
                                </section>
                            </template>

                            <!-- AI Explanation -->
                            <template x-if="selectedCareer.ai_impact_explanation">
                                <section class="border-t border-gray-100 pt-6">
                                    <h4
                                        class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                        Le regard de Brillio sur l'IA
                                    </h4>
                                    <p class="text-gray-600 italic text-sm"
                                        x-text="selectedCareer.ai_impact_explanation"></p>
                                </section>
                            </template>

                            <!-- Empty State / Fallback Notice -->
                            <template x-if="selectedCareer.is_fallback">
                                <section class="border-t border-gray-100 pt-6">
                                    <p class="text-center text-xs text-gray-400">Cette fiche métier sera enrichie
                                        prochainement avec plus de détails contextuels.</p>
                                </section>
                            </template>
                        </div>

                        <!-- Footer -->
                        <div class="px-8 py-4 bg-white border-t border-gray-100 flex justify-between items-center">
                            <span class="text-xs text-gray-400">© Brillio - Orientation Intelligente</span>
                            <button @click="closeCareerModal()"
                                class="px-6 py-2 bg-gray-900 hover:bg-black text-white rounded-xl font-bold transition shadow-md">
                                J'ai compris
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>


    @push('scripts')
        <script nonce="{{ request()->attributes->get('csp_nonce') }}">
            function recommendationsSystem() {
                return {
                    loading: true,
                    establishments: [],
                    mbtiType: '',
                    userHasPhone: {{ auth()->user()->phone ? 'true' : 'false' }},
                    activePhoneInput: null,
                    tempPhone: '',
                    sidebarOpen: false,
                    estDetails: null,
                    formData: {},

                    formatYoutubeUrl(url) {
                        if (!url) return '';
                        if (url.includes('youtu.be/')) {
                            return url.replace('youtu.be/', 'youtube.com/embed/');
                        }
                        if (url.includes('watch?v=')) {
                            return url.replace('watch?v=', 'embed/');
                        }
                        return url;
                    },

                    init() {
                        if ('{{ $theme }}' !== 'jeune') return;
                        
                        fetch('{{ route("jeune.establishments.recommended") }}')
                            .then(res => res.json())
                            .then(data => {
                                this.establishments = data.establishments;
                                this.mbtiType = data.mbti_type;
                                this.loading = false;
                            });
                    },

                    scrollCarousel(ref, dir) {
                        this.$refs[ref].scrollBy({ left: dir * 320, behavior: 'smooth' });
                    },

                    handleInterest(est) {
                        if (est.user_has_interest) return;

                        if (!this.userHasPhone) {
                            if (this.activePhoneInput === est.id) {
                                if (!this.tempPhone || this.tempPhone.length < 8) {
                                    alert('Veuillez entrer un numéro de téléphone valide.');
                                    return;
                                }
                                this.submitInterest(est, this.tempPhone);
                            } else {
                                this.activePhoneInput = est.id;
                            }
                            return;
                        }

                        this.submitInterest(est);
                    },

                    submitInterest(est, phone = null) {
                        let payload = {};
                        if (phone) payload.phone = phone;

                        fetch(`/espace-jeune/establishments/${est.id}/interest-quick`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(payload)
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                est.user_has_interest = true;
                                this.userHasPhone = true;
                                this.activePhoneInput = null;
                                
                                // Dispatch toast event
                                window.dispatchEvent(new CustomEvent('toast', { 
                                    detail: { message: data.message, type: 'success' } 
                                }));
                            } else {
                                alert(data.message);
                            }
                        });
                    },

                    openDetails(est) {
                        this.estDetails = est;
                        this.formData = {};
                        this.sidebarOpen = true;
                    },

                    submitPreciseInterest() {
                        if (!this.userHasPhone && (!this.tempPhone || this.tempPhone.length < 8)) {
                            alert('Veuillez entrer un numéro de téléphone valide.');
                            return;
                        }

                        fetch(`/espace-jeune/establishments/${this.estDetails.id}/interest-precise`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ 
                                form_data: this.formData,
                                phone: this.tempPhone 
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                this.estDetails.user_has_interest = true;
                                this.userHasPhone = true;
                                this.sidebarOpen = false;
                                window.dispatchEvent(new CustomEvent('toast', { 
                                    detail: { message: data.message, type: 'success' } 
                                }));
                            }
                        });
                    }
                }
            }

            function personalityTest() {
                return {
                    showTest: false, testStarted: false, loading: false, submitting: false, questions: [], answers: {}, currentQuestion: 0,
                    showHistoryModal: false, selectedHistory: null,
                    showCareerModal: false, selectedCareer: null, loadingCareer: false,
                    isPersonalizing: false,
                    loadingTitle: 'Chargement...',
                    loadingSubtitle: 'Veuillez patienter quelques instants.',
                    confirmClose: false,
                    confirmTimeout: null,

                    get allAnswered() {
                        return this.questions.length > 0 && Object.keys(this.answers).length === this.questions.length;
                    },

                    startTest() {
                        this.showTest = true;
                        if (this.questions.length === 0) {
                            this.loadQuestions();
                        } else {
                            this.testStarted = true;
                        }
                    },

                    closeTest() {
                        // If test hasn't started or no answers yet or finished, close directly
                        if (!this.testStarted || Object.keys(this.answers).length === 0 || this.allAnswered) {
                            this.showTest = false;
                            this.confirmClose = false;
                            return;
                        }

                        if (this.confirmClose) {
                            // Second click: Close and Reset
                            this.showTest = false;
                            this.resetTest();
                            this.confirmClose = false;
                            if (this.confirmTimeout) clearTimeout(this.confirmTimeout);
                        } else {
                            // First click: Request confirmation
                            this.confirmClose = true;
                            
                            // Auto-reset after 3 seconds if no action
                            if (this.confirmTimeout) clearTimeout(this.confirmTimeout);
                            this.confirmTimeout = setTimeout(() => {
                                this.confirmClose = false;
                            }, 3000);
                        }
                    },

                    resetTest() {
                        this.testStarted = false;
                        this.questions = [];
                        this.answers = {};
                        this.currentQuestion = 0;
                    },

                    retakeTest() {
                        this.resetTest();
                        this.startTest();
                    },

                    async loadQuestions() {
                        const isJeune = '{{ $theme }}' === 'jeune';
                        const cacheKey = 'mbti_questions_cache';

                        // 1. Tentative de chargement instantané depuis le cache local
                        if (isJeune) {
                            const cached = localStorage.getItem(cacheKey);
                            if (cached) {
                                try {
                                    const cachedData = JSON.parse(cached);
                                    if (cachedData.questions && cachedData.questions.length > 0) {
                                        this.questions = cachedData.questions;
                                        this.testStarted = true;
                                        this.loading = false;
                                        return; // Sortie immédiate : chargement instantané
                                    }
                                } catch (e) {
                                    localStorage.removeItem(cacheKey);
                                }
                            }
                        }

                        this.loading = true;
                        
                        // Si c'est un jeune, on active l'état de personnalisation
                        if (isJeune) {
                            this.isPersonalizing = true;
                            this.loadingTitle = "Personnalisation du test";
                            this.loadingSubtitle = "Brillio adapte les questions à ton profil...";
                        } else {
                            this.loadingTitle = "Chargement des questions";
                            this.loadingSubtitle = "Préparation du test MBTI...";
                        }

                        try {
                            // On tente d'abord l'endpoint dynamique pour les jeunes
                            let url = '{!! $questionsUrl ?? route("jeune.personality.questions") !!}';
                            
                            if (isJeune) {
                                url = '{!! route("jeune.personality.questions.dynamic") !!}';
                            }

                            // Bust cache with timestamp
                            url += (url.includes('?') ? '&' : '?') + 't=' + new Date().getTime();

                            const response = await fetch(url, {
                                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                            });
                            
                            const data = await response.json();
                            
                            if (data.success && data.questions) {
                                this.questions = data.questions;
                                this.testStarted = true;

                                // Mettre à jour le cache local pour la prochaine fois
                                if (isJeune) {
                                    localStorage.setItem(cacheKey, JSON.stringify({
                                        questions: data.questions,
                                        timestamp: new Date().getTime()
                                    }));
                                }
                            } else {
                                // Fallback aux questions standards si l'IA échoue
                                if (isJeune && url.includes('dynamic')) {
                                    await this.loadStandardQuestions();
                                } else {
                                    alert('Erreur: ' + (data.message || 'Format invalide'));
                                }
                            }
                        } catch (e) {
                            console.error(e);
                            if (isJeune) {
                                await this.loadStandardQuestions();
                            } else {
                                alert('Erreur connexion. Vérifiez votre connexion internet.');
                            }
                        } finally {
                            this.loading = false;
                        }
                    },

                    async loadStandardQuestions() {
                        this.loadingTitle = "Chargement...";
                        this.loadingSubtitle = "Récupération des questions standards.";
                        try {
                            const url = '{!! route("jeune.personality.questions") !!}' + '?t=' + new Date().getTime();
                            const response = await fetch(url, {
                                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                            });
                            const data = await response.json();
                            if (data.success && data.questions) {
                                this.questions = data.questions;
                                this.testStarted = true;
                            }
                        } catch (e) {
                            console.error('Fallback failed:', e);
                            alert('Erreur critique lors du chargement des questions.');
                        }
                    },

                    selectAnswer(value) {
                        const qid = this.questions[this.currentQuestion]?.id;
                        if (qid) this.answers[qid] = value;
                    },

                    nextQuestion() {
                        if (this.currentQuestion < this.questions.length - 1 && this.answers[this.questions[this.currentQuestion]?.id]) {
                            this.currentQuestion++;
                        }
                    },

                    previousQuestion() {
                        if (this.currentQuestion > 0) this.currentQuestion--;
                    },

                    async submitTest() {
                        if (!this.allAnswered) return;
                        this.submitting = true;
                        try {
                            const url = '{!! $submitUrl ?? route("jeune.personality.submit") !!}';
                            const response = await fetch(url, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                                body: JSON.stringify({ responses: this.answers })
                            });
                            const data = await response.json();
                            if (data.success) {
                                window.location.reload();
                            } else {
                                alert(data.message || 'Erreur soumission.');
                                this.submitting = false;
                            }
                        } catch (e) {
                            console.error(e);
                            alert('Erreur connexion.');
                            this.submitting = false;
                        }
                    },

                    discussWithAI() {
                        try {
                            // Préparer le message avec le contexte du test
                            const personalityType = {!! json_encode($personalityTest->personality_type ?? '') !!};
                            const personalityLabel = {!! json_encode($mbtiTypes[$personalityTest->personality_type ?? ''] ?? '') !!};
                            const description = {!! json_encode($personalityTest->personality_description ?? '') !!};

                            if (!personalityType) {
                                alert('Aucun test de personnalité trouvé.');
                                return;
                            }

                            const message = `Bonjour ! Je viens de passer un test de personnalité MBTI et j'ai obtenu le type ${personalityType} (${personalityLabel}).\n\nVoici ma description : ${description}\n\nPeux-tu m'aider à mieux comprendre mon profil et me donner des conseils pour mon développement personnel et professionnel ?`;

                            // Rediriger vers le chat avec le message pré-rempli
                            const chatUrl = {!! json_encode(route('jeune.chat')) !!};
                            window.location.href = chatUrl + '?prefill=' + encodeURIComponent(message);
                        } catch (error) {
                            console.error('Erreur discussWithAI:', error);
                            // Fallback: rediriger vers le chat sans message
                            window.location.href = {!! json_encode(route('jeune.chat')) !!};
                        }
                    },

                    viewHistoryDetails(test) {
                        this.selectedHistory = test;
                        this.showHistoryModal = true;
                    },

                    closeHistoryModal() {
                        this.showHistoryModal = false;
                        this.selectedHistory = null;
                    },

                    async viewCareerDetails(career) {
                        if (!career || !career.title) return;

                        this.loadingCareer = true;
                        this.selectedCareer = null;
                        this.showCareerModal = true;

                        try {
                            const response = await fetch('{!! route("careers.details-by-title") !!}?title=' + encodeURIComponent(career.title));
                            const data = await response.json();

                            if (data.success && data.career) {
                                this.selectedCareer = data.career;
                            } else {
                                // Fallback if career not in DB
                                this.selectedCareer = {
                                    title: career.title,
                                    description: career.description || '',
                                    is_fallback: true
                                };
                            }
                        } catch (e) {
                            console.error('Error fetching career details:', e);
                            this.selectedCareer = {
                                title: career.title,
                                description: career.description || '',
                                is_fallback: true
                            };
                        } finally {
                            this.loadingCareer = false;
                        }
                    },

                    closeCareerModal() {
                        this.showCareerModal = false;
                        setTimeout(() => { this.selectedCareer = null; }, 300);
                    }
                }
            }
        </script>
    @endpush