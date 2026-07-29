// Service worker BuildFlow — installabilité PWA + résilience réseau légère.
//
// Portée volontairement limitée : on ne précache pas l'intégralité des pages
// (l'app reste "en ligne d'abord" pour le back-office). Seules les pages déjà
// visitées et les ressources statiques (build Vite, icônes) sont mises en
// cache au fil de l'eau, pour un rendu instantané et une dégradation propre
// en cas de coupure réseau. La file d'attente hors-ligne du Kiosque pointage
// (Entrée/Sortie) est gérée côté page via IndexedDB, pas ici.

const CACHE_NAME = 'buildflow-v1';
const APP_SHELL = ['/manifest.webmanifest', '/pwa/icon-192.png', '/pwa/icon-512.png'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(APP_SHELL))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

function isStaticAsset(url) {
    return url.pathname.startsWith('/build/')
        || url.pathname.startsWith('/pwa/')
        || url.pathname.startsWith('/assets/')
        || url.pathname.startsWith('/storage/');
}

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return; // POST/PATCH/DELETE : jamais intercepté ici
    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    if (isStaticAsset(url)) {
        // Cache-first : ces ressources sont versionnées/peu changeantes.
        event.respondWith(
            caches.match(request).then((cached) => cached || fetch(request).then((response) => {
                if (response.ok) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                }
                return response;
            }))
        );
        return;
    }

    if (request.mode === 'navigate') {
        // Network-first : contenu toujours à jour en ligne, dernière version
        // connue affichée hors-ligne plutôt qu'une erreur de navigateur.
        event.respondWith(
            fetch(request)
                .then((response) => {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                    return response;
                })
                .catch(() => caches.match(request).then((cached) => cached || caches.match('/dashboard')))
        );
    }
});
