<!-- Bannière d'installation PWA Mobile (Android & Navigateurs compatibles) -->
<div id="pwa-install-banner" data-pwa-install-banner class="hidden fixed bottom-20 md:bottom-6 left-4 right-4 md:left-auto md:right-6 md:max-w-md z-[70] animate-fade-in-up">
    <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl border border-gray-200/80 dark:border-gray-800 rounded-3xl p-4 shadow-2xl flex items-center gap-3.5 ring-1 ring-black/5">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 p-0.5 flex-shrink-0 shadow-md">
            <div class="w-full h-full bg-white dark:bg-gray-900 rounded-[14px] flex items-center justify-center overflow-hidden">
                <img src="{{ asset('android-chrome-192x192.png') }}" alt="Brillio" width="32" height="32" loading="lazy" class="w-8 h-8 rounded-lg object-contain">
            </div>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-1.5">
                <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate">Installer Brillio</h4>
                <span class="inline-flex items-center px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">App</span>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Accès instantané et fluide depuis votre écran d'accueil.</p>
        </div>
        <div class="flex items-center gap-1.5 flex-shrink-0">
            <button type="button" data-pwa-install class="px-3.5 py-2 bg-gradient-to-r from-indigo-600 to-pink-600 hover:from-indigo-700 hover:to-pink-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95">
                Installer
            </button>
            <button type="button" data-pwa-dismiss class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-xl transition" aria-label="Fermer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
</div>

<!-- Modal Guide d'installation iOS Safari -->
<dialog id="pwa-ios-modal" class="hidden fixed inset-0 z-[10000] overflow-y-auto bg-transparent border-0 p-0 m-0 w-full h-full max-w-none max-h-none" aria-modal="true" aria-labelledby="pwa-ios-title">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" data-pwa-close-ios></div>

    <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-900 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md w-full border border-gray-100 dark:border-gray-800">
            <!-- Header avec dégradé -->
            <div class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 p-6 text-white text-center relative">
                <button type="button" data-pwa-close-ios class="absolute top-4 right-4 w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition" aria-label="Fermer la fenêtre">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <div class="w-16 h-16 mx-auto bg-white rounded-2xl p-1.5 shadow-xl mb-3 flex items-center justify-center">
                    <img src="{{ asset('apple-touch-icon.png') }}" alt="Brillio" width="48" height="48" loading="lazy" class="w-12 h-12 rounded-xl object-contain">
                </div>
                <h3 id="pwa-ios-title" class="text-xl font-extrabold">Installer Brillio sur iPhone</h3>
                <p class="text-xs text-white/90 mt-1">Suivez ces 3 étapes simples pour ajouter l'app à votre écran d'accueil.</p>
            </div>

            <!-- Étapes -->
            <div class="p-6 space-y-4">
                <div class="flex items-start gap-3.5 p-3 rounded-2xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800">
                    <div class="w-8 h-8 rounded-xl bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm flex-shrink-0">
                        1
                    </div>
                    <div class="text-sm">
                        <p class="font-semibold text-gray-900 dark:text-white">Appuyez sur Partager</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Touchez l'icône de partage <span class="inline-block px-1.5 py-0.5 bg-gray-200 dark:bg-gray-700 rounded text-[11px] font-mono">⎋</span> en bas de l'écran dans Safari.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3.5 p-3 rounded-2xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800">
                    <div class="w-8 h-8 rounded-xl bg-purple-100 dark:bg-purple-950 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-sm flex-shrink-0">
                        2
                    </div>
                    <div class="text-sm">
                        <p class="font-semibold text-gray-900 dark:text-white">Sélectionnez "Sur l'écran d'accueil"</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Faites défiler le menu vers le bas puis appuyez sur <span class="font-semibold text-gray-700 dark:text-gray-300">« Sur l'écran d'accueil »</span> (+).</p>
                    </div>
                </div>

                <div class="flex items-start gap-3.5 p-3 rounded-2xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800">
                    <div class="w-8 h-8 rounded-xl bg-pink-100 dark:bg-pink-950 text-pink-600 dark:text-pink-400 flex items-center justify-center font-bold text-sm flex-shrink-0">
                        3
                    </div>
                    <div class="text-sm">
                        <p class="font-semibold text-gray-900 dark:text-white">Appuyez sur "Ajouter"</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">En haut à droite de l'écran, confirmez en touchant <span class="font-semibold text-indigo-600 dark:text-indigo-400">Ajouter</span>.</p>
                    </div>
                </div>

                <button type="button" data-pwa-close-ios class="w-full py-3 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-2xl font-bold text-sm hover:opacity-90 transition">
                    C'est compris !
                </button>
            </div>
        </div>
    </div>
</dialog>

<!-- Modal Guide d'installation Ordinateur (Chrome / Edge / Brave / Safari Desktop) -->
<dialog id="pwa-desktop-modal" class="hidden fixed inset-0 z-[10000] overflow-y-auto bg-transparent border-0 p-0 m-0 w-full h-full max-w-none max-h-none" aria-modal="true" aria-labelledby="pwa-desktop-title">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" data-pwa-close-desktop></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center">
        <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-900 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md w-full border border-gray-100 dark:border-gray-800">
            <!-- Header avec dégradé -->
            <div class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 p-6 text-white text-center relative">
                <button type="button" data-pwa-close-desktop class="absolute top-4 right-4 w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition" aria-label="Fermer la fenêtre">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <div class="w-16 h-16 mx-auto bg-white rounded-2xl p-1.5 shadow-xl mb-3 flex items-center justify-center">
                    <img src="{{ asset('android-chrome-192x192.png') }}" alt="Brillio" width="48" height="48" loading="lazy" class="w-12 h-12 rounded-xl object-contain">
                </div>
                <h3 id="pwa-desktop-title" class="text-xl font-extrabold">Installer Brillio sur Ordinateur</h3>
                <p class="text-xs text-white/90 mt-1">Application de bureau ultra-rapide et autonome.</p>
            </div>

            <!-- Explication -->
            <div class="p-6 space-y-4">
                <div class="p-4 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-100 dark:border-indigo-900/50 flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="text-sm">
                        <p class="font-bold text-gray-900 dark:text-white">Icône dans la barre d'adresse</p>
                        <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">
                            Regardez tout en haut à droite de votre navigateur, dans la barre d'adresse (à côté de l'étoile des favoris). Cliquez sur l'icône <span class="font-semibold text-indigo-600 dark:text-indigo-400">« Installer Brillio »</span> (ordinateur avec flèche ou +).
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                    <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Fonctionne sur Chrome, Edge, Brave et Safari (macOS Sonoma+)</span>
                </div>

                <button type="button" data-pwa-close-desktop class="w-full py-3 bg-gradient-to-r from-indigo-600 to-pink-600 text-white rounded-2xl font-bold text-sm hover:opacity-90 transition shadow-lg">
                    J'ai compris !
                </button>
            </div>
        </div>
    </div>
</dialog>
