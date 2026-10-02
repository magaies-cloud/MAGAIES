/**
 * ═════════════════════════════════════════════════════════════════════
 * 🛡️ MAGAIEES ERP 2026 - PRODUCTION PWA SERVICE WORKER (V3.0)
 * ═════════════════════════════════════════════════════════════════════
 * Plug-and-Play Offline Shell for Vercel / Netlify / GitHub Pages
 * Features:
 * - Instant offline loading when launched from mobile home screen
 * - Cache essential shell files, fonts, and brand assets
 */

const CACHE_NAME = 'magaiees-static-pwa-v3';

const STATIC_ASSETS = [
    './',
    './index.html',
    './manifest.json',
    './js/pwa-manager.js',
    './assets/icon-192.png',
    './assets/icon-512.png',
    './assets/icon-maskable-512.png',
    './assets/apple-touch-icon.png',
    './assets/icon.svg',
    './assets/favicon.ico'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS).catch((e) => console.warn('[Magaiees SW] Precache error:', e));
        }).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((k) => (k !== CACHE_NAME ? caches.delete(k) : null))
            );
        }).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET') return;

    event.respondWith(
        caches.match(request).then((cached) => {
            const networkFetch = fetch(request).then((networkRes) => {
                if (networkRes && networkRes.status === 200) {
                    const copy = networkRes.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                }
                return networkRes;
            }).catch(() => {/* Offline fallback */});

            return cached || networkFetch || caches.match('./index.html');
        })
    );
});
