/**
 * ═════════════════════════════════════════════════════════════════════
 * 🛡️ ELSAJANA ERP 2026 - PRODUCTION PWA SERVICE WORKER (V3.0)
 * ═════════════════════════════════════════════════════════════════════
 * Features:
 * - Offline-First App Shell for 1-Tap Home Screen Standalone Experience
 * - Instant Stale-While-Revalidate for CSS, JS, Fonts & Vector Assets
 * - Network-First with Offline Fallback Cache for Dashboards
 * - Zero Cache Staleness for Critical Real-Time APIs
 */

const CACHE_NAME = 'magaiees-pwa-v2026-10-v3';

const PRECACHE_ASSETS = [
    './manifest.json',
    './assets/icon-192.png',
    './assets/icon-512.png',
    './assets/icon-maskable-512.png',
    './assets/apple-touch-icon.png',
    './assets/icon.svg',
    './assets/favicon.ico',
    './css/design-system.css',
    './css/theme.css',
    './js/pwa-manager.js'
];

// Install Event: Pre-cache Essential Shell Assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS).catch((err) => {
                console.warn('[SW] Some precache assets failed to fetch:', err);
            });
        }).then(() => self.skipWaiting())
    );
});

// Activate Event: Clear Legacy Caches & Claim Clients Immediately
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((key) => {
                    if (key !== CACHE_NAME) {
                        console.log('[SW] Purging legacy cache:', key);
                        return caches.delete(key);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch Event: Intelligent Multi-Tier Caching Strategy
self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);

    // 1. Never cache dynamic JSON APIs or push synchronization endpoints
    if (url.pathname.includes('/api/') || url.pathname.includes('api_index') || url.pathname.includes('sync_queue') || url.searchParams.has('api_call')) {
        return;
    }

    // 2. Navigation Requests (HTML / Dashboards): Network-First with Offline Fallback
    if (request.mode === 'navigate' || request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(
            fetch(request)
                .then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const copy = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                    }
                    return networkResponse;
                })
                .catch(() => {
                    // When offline, attempt to serve the cached dashboard
                    return caches.match(request).then((cached) => {
                        if (cached) return cached;
                        // Fallback to cached mobile dashboard or universal entry
                        return caches.match('./mobile-dashboard.php').then((mobileFallback) => {
                            if (mobileFallback) return mobileFallback;
                            return caches.match('./dashboard.php');
                        });
                    });
                })
        );
        return;
    }

    // 3. Static Assets (CSS, JS, Fonts, Images): Stale-While-Revalidate
    if (/\.(css|js|woff2?|ttf|png|jpe?g|svg|ico|webp)(\?.*)?$/i.test(url.pathname)) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                const fetchPromise = fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const copy = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                    }
                    return networkResponse;
                }).catch(() => {/* Offline fallback for asset */});

                return cachedResponse || fetchPromise;
            })
        );
        return;
    }
});

// Background Sync Event for Offline Queue
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-offline-outbox') {
        event.waitUntil(
            self.clients.matchAll().then((clients) => {
                clients.forEach((client) => {
                    client.postMessage({ type: 'TRIGGER_OFFLINE_SYNC' });
                });
            })
        );
    }
});
