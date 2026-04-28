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
                                    <template x-if="panelReady && estDetails?.presentation_videos && estDetails.presentation_videos.length > 0">
                                        <section>
                                            <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.3em] mb-6 flex items-center gap-3">
                                                <span class="w-8 h-[2px] bg-red-600"></span> Vidéos
                                            </h3>
                                            <div class="flex flex-col gap-6">
                                                <template x-for="(video, index) in estDetails.presentation_videos" :key="index">
                                                    <div class="w-full rounded-2xl overflow-hidden shadow-sm border border-gray-100 aspect-video bg-gray-100 relative">
                                                        <iframe class="absolute inset-0 w-full h-full" :src="formatYoutubeUrl(video)" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
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
