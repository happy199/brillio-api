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
                            <p class="text-gray-500 mt-1">Établissements au Bénin et en Afrique adaptés à ton profil <span class="font-bold text-indigo-600 uppercase">{{ $mbtiType ?? ($personalityTest->personality_type ?? '') }}</span></p>
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
