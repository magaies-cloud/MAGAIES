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

const CACHE_NAME = 'magaiees-pwa-v2026-10-15-fast2g-v12';

const PRECACHE_ASSETS = [
    './',
    './manifest.json',
    './mobile-suite.html',
    './mobile-voucher.php',
    './mobile-dashboard.php',
    './assets/logo.png',
    './assets/icon-192.png',
    './assets/apple-touch-icon.png',
    './assets/favicon.ico',
    './css/tailwind.min.css',
    './css/all.min.css',
    './css/mobile-suite.css',
    './js/pwa-manager.js',
    './js/magaies-offline-sync.js',
    './js/mobile-suite.js'
];

// Install Event: Only fetch assets not already cached
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(async (cache) => {
            await Promise.allSettled(
                PRECACHE_ASSETS.map(async (assetUrl) => {
                    try {
                        const existing = await cache.match(assetUrl, { ignoreSearch: true });
                        if (existing) return;
                        const res = await fetch(assetUrl, { credentials: 'same-origin' });
                        if (res && res.status === 200) {
                            await cache.put(assetUrl, res);
                        }
                    } catch (e) {}
                })
            );
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
                        return caches.delete(key);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Helper: Fetch with timeout for fast offline fallback on cold start
function fetchWithTimeout(request, timeoutMs = 2500) {
    return new Promise((resolve, reject) => {
        const timer = setTimeout(() => reject(new Error('Network timeout')), timeoutMs);
        fetch(request)
            .then((response) => {
                clearTimeout(timer);
                resolve(response);
            })
            .catch((err) => {
                clearTimeout(timer);
                reject(err);
            });
    });
}

// Fetch Event: Instant App-Shell Cache-First (Stale-While-Revalidate) for 2G Speed
self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    const isNavigation = request.mode === 'navigate' || (request.headers.get('accept') || '').includes('text/html');

    // 1. Dynamic JSON APIs & background pings (non-navigation): bypass SW cache so offline queue catches network errors
    const isApiCall =
        url.pathname.includes('/api/') ||
        url.pathname.includes('api_index') ||
        url.pathname.includes('sync_queue') ||
        url.searchParams.has('api_call') ||
        url.searchParams.has('api') ||
        url.searchParams.has('action') ||
        url.searchParams.get('live') === '1';

    if (isApiCall && !isNavigation) {
        return;
    }

    // 2. Navigation Requests: Serve Instantly from Cache if available (0.05s on 2G) and revalidate in background
    if (isNavigation && !url.searchParams.has('api') && !url.pathname.includes('/api/')) {
        event.respondWith(
            (async () => {
                const cached =
                    (await caches.match(request)) ||
                    (await caches.match(request, { ignoreSearch: true })) ||
                    (await caches.match(url.origin + url.pathname, { ignoreSearch: true }));

                const networkFetch = fetchWithTimeout(request, 4000)
                    .then((networkResponse) => {
                        if (networkResponse && networkResponse.status === 200) {
                            const copy1 = networkResponse.clone();
                            const copy2 = networkResponse.clone();
                            caches.open(CACHE_NAME).then((cache) => {
                                cache.put(request, copy1);
                                cache.put(url.origin + url.pathname, copy2);
                            });
                        }
                        return networkResponse;
                    })
                    .catch(() => null);

                // If we already have the screen cached, return it IMMEDIATELY for 2G speed while updating cache in background
                if (cached) {
                    event.waitUntil(networkFetch);
                    return cached;
                }

                const freshResponse = await networkFetch;
                if (freshResponse) return freshResponse;

                // Offline Fallback based on device type
                const ua = request.headers.get('user-agent') || '';
                const isMobileUA = /Android|iPhone|iPad|iPod|Mobile/i.test(ua) || url.pathname.includes('mobile');

                let fallback = null;
                if (isMobileUA) {
                    fallback =
                        (await caches.match('./mobile-suite.html', { ignoreSearch: true })) ||
                        (await caches.match('./mobile-dashboard.php', { ignoreSearch: true }));
                } else {
                    fallback =
                        (await caches.match('./magaiees_unified_dashboard.php', { ignoreSearch: true })) ||
                        (await caches.match('./mobile-suite.html', { ignoreSearch: true }));
                }

                if (fallback) return fallback;

                return new Response(
                    '<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>مقاييس ERP - وضع الأوفلاين</title></head><body style="background:#0f172a;color:#fff;font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;text-align:center;"><div><h2>⚡ جاري فتح النظام في وضع الأوفلاين...</h2><script>window.location.replace("mobile-suite.html");</script></div></body></html>',
                    { status: 200, headers: { 'Content-Type': 'text/html; charset=utf-8' } }
                );
            })()
        );
        return;
    }

    // 3. Static Assets (CSS, JS, Fonts, Images): Pure Cache-First (only fetch over network if missing from cache)
    if (
        /\.(css|js|woff2?|ttf|png|jpe?g|svg|ico|webp|json)(\?.*)?$/i.test(url.pathname) ||
        url.hostname.includes('fonts.googleapis.com') ||
        url.hostname.includes('fonts.gstatic.com') ||
        url.hostname.includes('cdnjs.cloudflare.com') ||
        url.hostname.includes('cdn.tailwindcss.com') ||
        url.hostname.includes('unpkg.com')
    ) {
        event.respondWith(
            (async () => {
                const cachedResponse =
                    (await caches.match(request)) ||
                    (await caches.match(request, { ignoreSearch: true }));

                if (cachedResponse) {
                    return cachedResponse;
                }

                try {
                    const networkResponse = await fetch(request);
                    if (networkResponse && networkResponse.status === 200) {
                        const copy = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                    }
                    return networkResponse;
                } catch (e) {
                    return new Response('', { status: 408 });
                }
            })()
        );
        return;
    }
});

// Warm-cache message handler: ONLY fetch assets that are NOT already in cache
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'WARM_OFFLINE_CACHE') {
        const urls = Array.isArray(event.data.urls) ? event.data.urls : PRECACHE_ASSETS;
        caches.open(CACHE_NAME).then(async (cache) => {
            for (const u of urls) {
                try {
                    const hit = await cache.match(u, { ignoreSearch: true });
                    if (!hit) {
                        const res = await fetch(u, { credentials: 'same-origin' });
                        if (res && res.status === 200) await cache.put(u, res);
                    }
                } catch (e) {}
            }
        });
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

// ────────────────────────────────────────────────────────────────────
// 🔔 Web Push Event: Triggers when notification arrives while phone is locked
// ────────────────────────────────────────────────────────────────────
self.addEventListener('push', (event) => {
    console.log('[SW Push] Background push event received:', event);
    let payload = {
        title: '🔔 إشعار فوري: مقاييس ERP',
        body: 'تنبيه تنفيذي جديد يتطلب انتباهك على شاشة الجوال',
        icon: './assets/icon-192.png',
        badge: './assets/icon-192.png',
        tag: 'magaiees-alert-' + Date.now(),
        data: { url: './mobile-dashboard.php?tab=approvals' }
    };

    if (event.data) {
        try {
            const json = event.data.json();
            payload = Object.assign(payload, json);
        } catch (e) {
            payload.body = event.data.text() || payload.body;
        }
    }

    const options = {
        body: payload.body,
        icon: payload.icon || './assets/icon-192.png',
        badge: payload.badge || './assets/icon-192.png',
        tag: payload.tag || 'magaiees-push',
        renotify: true,
        requireInteraction: true,
        vibrate: [300, 100, 300, 100, 400],
        data: payload.data || { url: './mobile-dashboard.php' },
        actions: [
            { action: 'open', title: 'عرض في مقاييس 🚀' },
            { action: 'dismiss', title: 'تجاهل' }
        ]
    };

    event.waitUntil(
        self.registration.showNotification(payload.title, options)
    );
});

// ────────────────────────────────────────────────────────────────────
// 📲 Notification Click Event: When user taps the lockscreen notification
// ────────────────────────────────────────────────────────────────────
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    if (event.action === 'dismiss') return;

    const targetUrl = event.notification.data?.url || './mobile-dashboard.php';

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            for (let client of windowClients) {
                if (client.url.includes('mobile-dashboard') || client.url.includes('magaiees')) {
                    return client.focus().then(() => {
                        if (typeof client.navigate === 'function') {
                            return client.navigate(targetUrl);
                        }
                    });
                }
            }
            return self.clients.openWindow(targetUrl);
        })
    );
});
