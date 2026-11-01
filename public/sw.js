/**
 * Service Worker Brillio PWA
 * Version: 1.1.0
 * Fonctionnalités : Preload navigation, Offline fallback, Stale-While-Revalidate,
 * Background Sync, Web Push & Caching sécurisé.
 */

const CACHE_NAME = 'brillio-pwa-v1.2.0';
const OFFLINE_URL = '/offline.html';

// Ressources critiques pré-mises en cache à l'installation
const PRECACHE_ASSETS = [
    '/',
    '/app',
    OFFLINE_URL,
    '/manifest.json',
    '/favicon-32x32.png',
    '/favicon-16x16.png',
    '/apple-touch-icon.png',
    '/android-chrome-192x192.png',
    '/android-chrome-512x512.png',
    '/LOGOBRILLIONOIR.png',
    '/images/onboarding/students.jpg',
    '/images/onboarding/mentor.jpg',
    '/images/onboarding/start.jpg'
];

// Routes à ne JAMAIS mettre en cache
const NEVER_CACHE_PATTERNS = [
    /\/api\//i,
    /\/moneroo/i,
    /\/webhook/i,
    /\/stripe/i,
    /\/auth\/callback/i,
    /\/logout/i,
    /\/broadcasting\//i
];

// Installation : mise en cache du offline fallback et des assets essentiels
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS);
        }).then(() => {
            return self.skipWaiting();
        })
    );
});

// Activation : nettoyage des anciens caches & activation du Navigation Preload
self.addEventListener('activate', (event) => {
    event.waitUntil(
        Promise.all([
            // Nettoyage anciens caches
            caches.keys().then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((name) => {
                        if (name !== CACHE_NAME) {
                            return caches.delete(name);
                        }
                    })
                );
            }),
            // Activation du Navigation Preload (si supporté par le navigateur)
            (async () => {
                if (self.registration && self.registration.navigationPreload) {
                    await self.registration.navigationPreload.enable();
                }
            })(),
            // Prise de contrôle immédiate des clients
            self.clients.claim()
        ])
    );
});

// Réception de messages depuis l'application (ex: forcer skipWaiting)
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

// Gestionnaire Background Sync
self.addEventListener('sync', (event) => {
    if (event.tag === 'pwa-sync-queue') {
        event.waitUntil(Promise.resolve());
    }
});

// Gestionnaire des notifications Push
self.addEventListener('push', (event) => {
    let data = { title: 'Brillio', body: 'Vous avez une nouvelle notification sur Brillio.' };
    try {
        if (event.data) {
            data = event.data.json();
        }
    } catch {
        data.body = event.data.text();
    }

    const options = {
        body: data.body,
        icon: '/android-chrome-192x192.png',
        badge: '/favicon-32x32.png',
        data: { url: data.url || '/' },
        actions: [
            { action: 'open', title: 'Ouvrir' }
        ]
    };

    event.waitUntil(self.registration.showNotification(data.title || 'Brillio', options));
});

// Clic sur une notification Push
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const targetUrl = (event.notification.data && event.notification.data.url) || '/';

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            for (const client of windowClients) {
                if (client.url === targetUrl && 'focus' in client) {
                    return client.focus();
                }
            }
            if (self.clients.openWindow) {
                return self.clients.openWindow(targetUrl);
            }
        })
    );
});

// Stratégie de capture des requêtes réseau
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Ignorer les requêtes non-GET
    if (request.method !== 'GET') {
        return;
    }

    // Ignorer les extensions de navigateur ou protocoles non HTTP/HTTPS
    if (!url.protocol.startsWith('http')) {
        return;
    }

    // Ignorer les routes sensibles (API, paiements, auth)
    for (const pattern of NEVER_CACHE_PATTERNS) {
        if (pattern.test(url.pathname)) {
            return;
        }
    }

    // 1. NAVIGATION REQUESTS (Pages HTML Laravel) : Network-First avec Navigation Preload & fallback Offline
    if (request.mode === 'navigate' || (request.headers.get('accept') && request.headers.get('accept').includes('text/html'))) {
        event.respondWith(
            (async () => {
                try {
                    // Utiliser en priorité la réponse préchargée si disponible
                    const preloadResponse = await event.preloadResponse;
                    if (preloadResponse) {
                        return preloadResponse;
                    }

                    // Requête réseau en direct
                    const networkResponse = await fetch(request);
                    if (networkResponse && networkResponse.status === 200) {
                        return networkResponse;
                    }
                    return networkResponse;
                } catch {
                    // Échec réseau : tentative de récupération en cache ou affichage de la page offline
                    const cachedResponse = await caches.match(request);
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    const offlineFallback = await caches.match(OFFLINE_URL);
                    return offlineFallback || new Response('Hors ligne', {
                        status: 503,
                        statusText: 'Service Unavailable',
                        headers: { 'Content-Type': 'text/plain; charset=utf-8' }
                    });
                }
            })()
        );
        return;
    }

    // 2. ASSETS STATIQUES IMMUTABLES (Vite /build/*, Fonts, Images, CSS, JS) : Stale-While-Revalidate
    const isStaticAsset = (
        url.pathname.startsWith('/build/') ||
        url.hostname.includes('fonts.googleapis.com') ||
        url.hostname.includes('fonts.gstatic.com') ||
        url.hostname.includes('cdnjs.cloudflare.com') ||
        /\.(?:js|css|woff2?|ttf|eot|png|jpe?g|gif|svg|webp|ico)$/i.test(url.pathname)
    );

    if (isStaticAsset) {
        event.respondWith(
            caches.open(CACHE_NAME).then(async (cache) => {
                const cachedResponse = await cache.match(request);

                const fetchPromise = fetch(request)
                    .then((networkResponse) => {
                        if (networkResponse && networkResponse.status === 200) {
                            cache.put(request, networkResponse.clone());
                        }
                        return networkResponse;
                    })
                    .catch(() => {
                        return null;
                    });

                // Si on a l'asset en cache, on le retourne immédiatement pendant que la mise à jour s'effectue en arrière-plan
                return cachedResponse || fetchPromise;
            })
        );
    }
});
