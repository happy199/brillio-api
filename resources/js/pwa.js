/**
 * Gestionnaire Client PWA Brillio
 * Enregistrement Service Worker, Événements d'installation (Android, iOS & Desktop),
 * Détection de connectivité et Mises à jour en direct.
 * Conforme CSP Strict (zéro inline event handler).
 */

class BrillioPWA {
    constructor() {
        this.deferredPrompt = null;
        this.isStandalone = false;
        this.init();
    }

    init() {
        this.checkStandalone();
        this.registerServiceWorker();
        this.listenInstallPrompt();
        this.listenConnectivity();
        this.setupEventListeners();
        this.initStorageAndCapabilities();
    }

    checkStandalone() {
        this.isStandalone = (
            window.matchMedia('(display-mode: standalone)').matches ||
            window.navigator.standalone === true ||
            document.referrer.includes('android-app://')
        );

        if (this.isStandalone) {
            document.documentElement.classList.add('is-pwa-standalone');
            localStorage.setItem('brillio_pwa_installed', 'true');
        }
    }

    isIos() {
        const userAgent = window.navigator.userAgent.toLowerCase();
        return /iphone|ipad|ipod/.test(userAgent) && !window.MSStream;
    }

    isInstalled() {
        return this.isStandalone || localStorage.getItem('brillio_pwa_installed') === 'true';
    }

    registerServiceWorker() {
        if (!('serviceWorker' in navigator)) {
            return;
        }

        window.addEventListener('load', async () => {
            try {
                const registration = await navigator.serviceWorker.register('/sw.js', { scope: '/' });

                // Détection de nouvelle version en attente
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    if (!newWorker) return;

                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            this.notifyUpdateAvailable(registration);
                        }
                    });
                });

                // Rechargement automatique lors du skipWaiting
                let refreshing = false;
                navigator.serviceWorker.addEventListener('controllerchange', () => {
                    if (!refreshing) {
                        refreshing = true;
                        window.location.reload();
                    }
                });

            } catch (err) {
                console.debug('Service Worker status:', err);
            }
        });
    }

    notifyUpdateAvailable(registration) {
        if (document.getElementById('pwa-update-banner')) return;

        const updateBanner = document.createElement('div');
        updateBanner.id = 'pwa-update-banner';
        updateBanner.className = 'fixed bottom-4 right-4 z-[9999] max-w-sm bg-gray-900/95 backdrop-blur-md text-white p-4 rounded-2xl shadow-2xl border border-white/10 flex items-center justify-between gap-3 text-sm animate-bounce';
        updateBanner.innerHTML = `
            <div class="flex items-center gap-2.5">
                <span class="w-2.5 h-2.5 bg-indigo-400 rounded-full animate-ping"></span>
                <span>Nouvelle version disponible !</span>
            </div>
            <button id="pwa-update-btn" class="px-3 py-1.5 bg-gradient-to-r from-indigo-500 to-pink-500 hover:from-indigo-600 hover:to-pink-600 text-white font-semibold rounded-xl text-xs transition shadow-md">
                Actualiser
            </button>
        `;

        document.body.appendChild(updateBanner);

        document.getElementById('pwa-update-btn')?.addEventListener('click', () => {
            localStorage.setItem('brillio_pwa_update_acknowledged', Date.now().toString());
            if (registration.waiting) {
                registration.waiting.postMessage({ type: 'SKIP_WAITING' });
            }
        });
    }

    initStorageAndCapabilities() {
        // 1. Demande de persistance du stockage (éviter l'éviction par le navigateur)
        if (navigator.storage && navigator.storage.persist) {
            navigator.storage.persist().catch(() => {});
        }

        // 2. Monitoring du quota de stockage
        if (navigator.storage && navigator.storage.estimate) {
            navigator.storage.estimate().then(({ quota, usage }) => {
                if (quota && usage) {
                    console.debug(`PWA Quota: ${(usage / 1024 / 1024).toFixed(1)}MB / ${(quota / 1024 / 1024).toFixed(1)}MB`);
                }
            }).catch(() => {});
        }

        // 3. Network Information API (Progressive Enhancement)
        if ('connection' in navigator) {
            navigator.connection.addEventListener('change', () => {
                console.debug('Type de réseau:', navigator.connection.effectiveType);
            });
        }

        // 4. Badging API (Progressive Enhancement)
        if ('setAppBadge' in navigator) {
            window.setAppBadge = (count) => {
                try {
                    return count > 0 ? navigator.setAppBadge(count) : navigator.clearAppBadge();
                } catch {}
            };
        }
    }

    listenInstallPrompt() {
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            this.deferredPrompt = e;

            window.dispatchEvent(new CustomEvent('brillio:pwa-installable', {
                detail: { platform: 'native' }
            }));

            this.showInstallPrompts();
        });

        window.addEventListener('appinstalled', () => {
            this.deferredPrompt = null;
            localStorage.setItem('brillio_pwa_installed', 'true');
            window.dispatchEvent(new CustomEvent('brillio:pwa-installed'));
            this.hideAllInstallPrompts();
        });
    }

    async promptInstall() {
        if (this._isPrompting) return;
        this._isPrompting = true;

        try {
            // 1. Si l'événement natif avant-installation est disponible (Android / Chrome / Edge)
            if (this.deferredPrompt) {
                try {
                    this.deferredPrompt.prompt();
                    const choice = await this.deferredPrompt.userChoice;
                    if (choice && choice.outcome === 'accepted') {
                        localStorage.setItem('brillio_pwa_installed', 'true');
                    }
                    this.deferredPrompt = null;
                    this.hideAllInstallPrompts();
                    return choice ? choice.outcome : 'prompted';
                } catch (err) {
                    console.debug('Prompt error:', err);
                }
            }

            // 2. Si on est sur iOS (iPhone / iPad) dans Safari
            if (this.isIos()) {
                this.showIosGuideModal();
                return 'ios_guide_shown';
            }

            // 3. Si on est sur Desktop et que l'app est déjà installée
            if (this.isStandalone) {
                this.showConnectivityToast('already_installed');
                return 'already_installed';
            }

            // 4. Si sur Desktop Chrome/Edge sans prompt différé (ex: l'icône est dans la barre d'adresse)
            this.showDesktopGuideModal();
            return 'desktop_guide_shown';
        } finally {
            setTimeout(() => {
                this._isPrompting = false;
            }, 600);
        }
    }

    showInstallPrompts() {
        const lastDismissed = localStorage.getItem('brillio_pwa_prompt_dismissed');
        const now = Date.now();
        if (lastDismissed && (now - parseInt(lastDismissed, 10)) < 48 * 3600 * 1000) {
            return;
        }

        document.querySelectorAll('[data-pwa-install-banner]').forEach((el) => {
            el.classList.remove('hidden');
        });
    }

    dismissPrompt() {
        localStorage.setItem('brillio_pwa_prompt_dismissed', Date.now().toString());
        this.hideAllInstallPrompts();
    }

    hideAllInstallPrompts() {
        document.querySelectorAll('[data-pwa-install-banner]').forEach((el) => {
            el.classList.add('hidden');
        });
    }

    showIosGuideModal() {
        const modal = document.getElementById('pwa-ios-modal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    hideIosGuideModal() {
        const modal = document.getElementById('pwa-ios-modal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    showDesktopGuideModal() {
        const modal = document.getElementById('pwa-desktop-modal');
        if (modal) {
            modal.classList.remove('hidden');
        } else {
            alert("Pour installer Brillio sur votre ordinateur, cliquez sur l'icône d'installation située à droite dans la barre d'adresse de votre navigateur (icône ⊕ ou écran).");
        }
    }

    hideDesktopGuideModal() {
        const modal = document.getElementById('pwa-desktop-modal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    setupEventListeners() {
        // Délégation d'événements 100% conforme CSP (aucun inline onclick nécessaire)
        document.addEventListener('click', (e) => {
            const installBtn = e.target.closest('[data-pwa-install], [data-pwa-install-btn], .pwa-install-trigger');
            if (installBtn) {
                e.preventDefault();
                this.promptInstall();
                return;
            }

            const dismissBtn = e.target.closest('[data-pwa-dismiss]');
            if (dismissBtn) {
                e.preventDefault();
                this.dismissPrompt();
                return;
            }

            const closeIosBtn = e.target.closest('[data-pwa-close-ios]');
            if (closeIosBtn) {
                e.preventDefault();
                this.hideIosGuideModal();
                return;
            }

            const closeDesktopBtn = e.target.closest('[data-pwa-close-desktop]');
            if (closeDesktopBtn) {
                e.preventDefault();
                this.hideDesktopGuideModal();
                return;
            }
        });
    }

    listenConnectivity() {
        window.addEventListener('offline', () => {
            this.showConnectivityToast('offline');
        });

        window.addEventListener('online', () => {
            this.showConnectivityToast('online');
        });
    }

    showConnectivityToast(status) {
        let toast = document.getElementById('pwa-connectivity-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'pwa-connectivity-toast';
            document.body.appendChild(toast);
        }

        if (status === 'offline') {
            toast.className = 'fixed top-4 left-1/2 -translate-x-1/2 z-[9999] px-4 py-2 bg-amber-500 text-white rounded-full text-xs font-semibold shadow-lg flex items-center gap-2 transition-all duration-300';
            toast.innerHTML = `
                <span class="w-2 h-2 bg-white rounded-full animate-ping"></span>
                <span>Mode hors ligne activé</span>
            `;
            toast.style.display = 'flex';
        } else if (status === 'already_installed') {
            toast.className = 'fixed top-4 left-1/2 -translate-x-1/2 z-[9999] px-4 py-2 bg-indigo-600 text-white rounded-full text-xs font-semibold shadow-lg flex items-center gap-2 transition-all duration-300';
            toast.innerHTML = `
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <span>Brillio est déjà installée sur cet appareil !</span>
            `;
            toast.style.display = 'flex';
            setTimeout(() => {
                toast.style.display = 'none';
            }, 3000);
        } else {
            toast.className = 'fixed top-4 left-1/2 -translate-x-1/2 z-[9999] px-4 py-2 bg-emerald-500 text-white rounded-full text-xs font-semibold shadow-lg flex items-center gap-2 transition-all duration-300';
            toast.innerHTML = `
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <span>Connexion rétablie</span>
            `;
            toast.style.display = 'flex';
            setTimeout(() => {
                toast.style.display = 'none';
            }, 3000);
        }
    }
}

// Initialisation globale
window.brillioPWA = new BrillioPWA();

// Raccourcis globaux
window.installBrillioPwa = () => window.brillioPWA.promptInstall();
window.dismissBrillioPwa = () => window.brillioPWA.dismissPrompt();
