/**
 * â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
 * ًں›،ï¸ڈ ELSAJANA ERP 2026 - PRODUCTION PWA SERVICE WORKER (V3.0)
 * â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
 * Features:
 * - Offline-First App Shell for 1-Tap Home Screen Standalone Experience
 * - Instant Stale-While-Revalidate for CSS, JS, Fonts & Vector Assets
 * - Network-First with Offline Fallback Cache for Dashboards
 * - Zero Cache Staleness for Critical Real-Time APIs
 */

const CACHE_NAME = 'magaiees-pwa-v2027.99-11-10-merged-v1';

const PRECACHE_ASSETS = [
    './',
    './manifest.json',
    './index.php',
    './mobile-suite.html',
    './mobile-suite.php',
    './mobile-voucher.php',
    './mobile-dashboard.php',
    './magaiees_unified_dashboard.php',
    './single-cash-payment.php',
    './multi-cash-payment.php',
    './direct-sales.php',
    './purchase.php',
    './purchase-receipt.php',
    './sales-delivery.php',
    './delivery-note.php',
    './assets/logo.png',
    './assets/icon-192.png',
    './assets/apple-touch-icon.png',
    './assets/favicon.ico',
    './css/tailwind.min.css',
    './css/all.min.css',
    './css/design-system.css',
    './css/theme.css',
    './css/components.css',
    './css/magaiees-royal-buttons.css?v2027.99_royal_v2027.99',
    './css/mobile-suite.css?v2027.99_10_15_v2027.99',
    './js/tailwind.min.js',
    './js/localforage.min.js',
    './js/magaiees-royal-buttons.js?v2027.99_royal_v2027.99',
    './js/pwa-manager.js?v2027.99_10_15_v2027.99',
    './js/magaies-offline-sync.js',
    './js/mobile-suite.js?v2027.99_10_15_v2027.99',
    './js/app.js',
    './js/single-cash-payment.js',
    './js/multi-cash-payment.js',
    './js/direct-sales-v3.js',
    './js/purchase-pro-v5.js',
    './js/sales-delivery.js'
];

function isValidCacheableResponse(res, requireHtml = false) {
    if (!res || res.status !== 200) return false;
    if (res.redirected) return false;
    if (res.url && (res.url.includes('login.php') || res.url.includes('logout='))) return false;
    if (requireHtml) {
        const ct = (res.headers && res.headers.get('content-type')) ? res.headers.get('content-type').toLowerCase() : '';
        if (ct && !ct.includes('text/html')) return false;
    }
    return true;
}

async function storeInCache(cache, assetUrl, res) {
    if (!isValidCacheableResponse(res)) return;
    try {
        const fullUrl = new URL(assetUrl, self.registration.scope);
        const cleanUrl = fullUrl.origin + fullUrl.pathname;
        await cache.put(assetUrl, res.clone());
        if (cleanUrl !== fullUrl.href) {
            await cache.put(cleanUrl, res.clone());
        }
    } catch (e) {}
}

// Install Event: Fetch fresh assets from server (bypassing stale disk cache)
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(async (cache) => {
            await Promise.allSettled(
                PRECACHE_ASSETS.map(async (assetUrl) => {
                    try {
                        const res = await fetch(assetUrl, { cache: 'reload', credentials: 'same-origin' });
                        await storeInCache(cache, assetUrl, res);
                    } catch (e) {}
                })
            );
        }).then(() => self.skipWaiting())
    );
});

// Activate Event: Clear Legacy Caches & Claim Clients Immediately + Flush Any Pending Offline Outbox
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
        }).then(() => self.clients.claim()).then(() => {
            return flushIndexedDBOutboxInSW().catch(() => {});
        })
    );
});

// Helper: Fetch with timeout for fast offline fallback on cold start
function fetchWithTimeout(request, timeoutMs = 8000) {
    return new Promise((resolve, reject) => {
        if (self.navigator && self.navigator.onLine === false) {
            return reject(new Error('Device is offline'));
        }
        const timer = setTimeout(() => reject(new Error('Network timeout')), timeoutMs);
        fetch(request, { cache: 'no-cache' })
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

async function findBestCachedPage(request, url) {
    const direct =
        (await caches.match(request)) ||
        (await caches.match(request, { ignoreSearch: true })) ||
        (await caches.match(url.origin + url.pathname, { ignoreSearch: true }));
    if (direct) return direct;

    // Cross-match paired equivalents (e.g. mobile-voucher.php <-> mobile-voucher.html, mobile-dashboard.php <-> mobile-suite.html)
    const path = url.pathname;
    const scopeBase = self.registration.scope.replace(/\/$/, '');
    if (path.endsWith('mobile-voucher.php') || path.endsWith('mobile-voucher.html')) {
        const vMatch =
            (await caches.match(scopeBase + '/mobile-voucher.php', { ignoreSearch: true })) ||
            (await caches.match(scopeBase + '/mobile-voucher.html', { ignoreSearch: true }));
        if (vMatch) return vMatch;
    }

    // Only fallback to dashboard shell if the root or dashboard itself was requested
    if (path.endsWith('magaiees_unified_dashboard.php') || path.endsWith('mobile-dashboard.php')) {
        const ua = request.headers.get('user-agent') || '';
        const isMobileUA = /Android|iPhone|iPad|iPod|Mobile/i.test(ua) || path.includes('mobile');
        if (isMobileUA) {
            return (
                (await caches.match(scopeBase + '/mobile-suite.html', { ignoreSearch: true })) ||
                (await caches.match(scopeBase + '/mobile-dashboard.php', { ignoreSearch: true }))
            );
        } else {
            return await caches.match(scopeBase + '/magaiees_unified_dashboard.php', { ignoreSearch: true });
        }
    }
    return null;
}

// Fetch Event: Instant App-Shell with Version-Aware Asset Caching + Opportunistic Outbox Sync
self.addEventListener('fetch', (event) => {
    const request = event.request;

    // ظپط­طµ ط§ظ†طھظ‡ط§ط²ظٹ طµط§ظ…طھ (ظƒظ„ 5 ط«ظˆط§ظ†ظچ) ظ„ط±ظپط¹ ط£ظٹ ط­ط±ظƒط§طھ ط£ظˆظپظ„ط§ظٹظ† ظ…ط¹ظ„ظ‚ط© ظپظٹ IndexedDB ط¨ظ…ط¬ط±ط¯ ظ…ط±ظˆط± ط£ظٹ ظ†ط´ط§ط· ط´ط¨ظƒظٹ
    if (self.navigator && self.navigator.onLine !== false && !_swSyncInProgress) {
        const nowMs = Date.now();
        if (nowMs - _lastSwOpportunisticCheck > 5000 && !request.url.includes('sync_offline_batch')) {
            _lastSwOpportunisticCheck = nowMs;
            event.waitUntil(flushIndexedDBOutboxInSW().catch(() => {}));
        }
    }

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

    // Never intercept login/logout requests with cached shells
    if (url.pathname.endsWith('/login.php') || url.searchParams.has('logout')) {
        return;
    }

    // 2. Navigation Requests: Network-first (8s timeout) with exact offline cache fallback
    if (isNavigation && !url.searchParams.has('api') && !url.pathname.includes('/api/')) {
        event.respondWith(
            (async () => {
                const isExplicitlyOffline = (self.navigator && self.navigator.onLine === false);

                if (!isExplicitlyOffline) {
                    const freshResponse = await fetchWithTimeout(request, 8000)
                        .then((networkResponse) => {
                            if (isValidCacheableResponse(networkResponse, true)) {
                                const copy1 = networkResponse.clone();
                                const copy2 = networkResponse.clone();
                                caches.open(CACHE_NAME).then((cache) => {
                                    cache.put(request, copy1);
                                    cache.put(url.origin + url.pathname, copy2);
                                });
                                return networkResponse;
                            }
                            if (networkResponse && (networkResponse.redirected || (networkResponse.url && networkResponse.url.includes('login.php')))) {
                                return networkResponse;
                            }
                            return networkResponse || null;
                        })
                        .catch(() => null);

                    if (freshResponse) return freshResponse;
                }

                // Offline or network timeout: serve immediately from cache
                const cachedPage = await findBestCachedPage(request, url);
                if (cachedPage) return cachedPage;

                // Safe non-looping offline fallback screen (only shown if browser cache was completely cleared while offline)
                return new Response(
                    '<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>ظ…ظ‚ط§ظٹظٹط³ ERP - ظˆط¶ط¹ ط§ظ„ط£ظˆظپظ„ط§ظٹظ†</title></head><body style="background:#0f172a;color:#fff;font-family:Tajawal,sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;text-align:center;padding:20px;"><div style="max-width:400px;background:#1e293b;border:1px solid rgba(56,189,248,0.3);border-radius:20px;padding:24px;box-shadow:0 20px 50px rgba(0,0,0,0.5);"><div style="font-size:42px;margin-bottom:10px;">âڑ،</div><h2 style="margin:0 0 10px;font-size:18px;color:#38bdf8;">ظ†ط¸ط§ظ… ظ…ظ‚ط§ظٹظٹط³ - ظˆط¶ط¹ ط§ظ„ط£ظˆظپظ„ط§ظٹظ† ط§ظ„ظپظˆط±ظٹ</h2><p style="font-size:13px;color:#cbd5e1;line-height:1.6;margin-bottom:18px;">ط§ط®طھط± ط§ظ„ط´ط§ط´ط© ط§ظ„طھظٹ طھط±ط؛ط¨ ط¨ظپطھط­ظ‡ط§ ظپظٹ ظˆط¶ط¹ ط§ظ„ط£ظˆظپظ„ط§ظٹظ†:</p><div style="display:flex;flex-direction:column;gap:10px;"><a href="mobile-voucher.php" style="background:linear-gradient(135deg,#10b981,#059669);color:#fff;text-decoration:none;padding:12px;border-radius:12px;font-weight:bold;font-size:14px;">ًں’¸ ط³ظ†ط¯ ط­ط±ظƒط© ظ…ط§ظ„ظٹط© (ط¬ظˆط§ظ„)</a><a href="mobile-suite.html" style="background:linear-gradient(135deg,#0284c7,#0369a1);color:#fff;text-decoration:none;padding:12px;border-radius:12px;font-weight:bold;font-size:14px;">ًں“± ظ…ظ†ط¸ظˆظ…ط© ط§ظ„ط¬ظˆط§ظ„ ط§ظ„ط´ط§ظ…ظ„ط©</a><a href="magaiees_unified_dashboard.php" style="background:rgba(255,255,255,0.1);color:#e2e8f0;text-decoration:none;padding:12px;border-radius:12px;font-weight:bold;font-size:14px;">ًں’» ظ…ظ†ط¸ظˆظ…ط© ط§ظ„ظ„ط§ط¨طھظˆط¨ ط§ظ„ط´ط§ظ…ظ„ط©</a></div></div></body></html>',
                    { status: 200, headers: { 'Content-Type': 'text/html; charset=utf-8' } }
                );
            })()
        );
        return;
    }

    // 3. Static Assets (CSS, JS, Fonts, Images): Fresh Network-First for local CSS/JS when online, Cache-First for immutable media & offline
    if (
        /\.(css|js|woff2?|ttf|png|jpe?g|svg|ico|webp|json)(\?.*)?$/i.test(url.pathname) ||
        url.hostname.includes('fonts.googleapis.com') ||
        url.hostname.includes('fonts.gstatic.com') ||
        url.hostname.includes('cdnjs.cloudflare.com') ||
        url.hostname.includes('cdn.tailwindcss.com') ||
        url.hostname.includes('unpkg.com')
    ) {
        const isLocalCodeAsset = (url.origin === self.location.origin) && /\.(css|js|json)(\?.*)?$/i.test(url.pathname);
        event.respondWith(
            (async () => {
                if (self.navigator && self.navigator.onLine === false) {
                    const offlineExact = await caches.match(request);
                    if (offlineExact) return offlineExact;
                    const offlineCached = await caches.match(request, { ignoreSearch: true });
                    if (offlineCached) return offlineCached;
                    return new Response('', { status: 408 });
                }

                // For local CSS/JS/JSON when online: fetch latest first so code updates appear immediately
                if (isLocalCodeAsset) {
                    try {
                        const networkResponse = await fetchWithTimeout(request, 1500);
                        if (isValidCacheableResponse(networkResponse)) {
                            const copy = networkResponse.clone();
                            caches.open(CACHE_NAME).then((cache) => storeInCache(cache, request.url, copy));
                            return networkResponse;
                        }
                    } catch (e) {}
                }

                const exactCached = await caches.match(request);
                if (exactCached) {
                    return exactCached;
                }

                try {
                    const networkResponse = await fetchWithTimeout(request, 2000);
                    if (isValidCacheableResponse(networkResponse)) {
                        const copy = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => storeInCache(cache, request.url, copy));
                    }
                    return networkResponse;
                } catch (e) {
                    const fallbackCached = await caches.match(request, { ignoreSearch: true });
                    if (fallbackCached) return fallbackCached;
                    return new Response('', { status: 408 });
                }
            })()
        );
        return;
    }
});

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// ًںڑ€ ظ…ط­ط±ظƒ ط§ظ„ظ…ط²ط§ظ…ظ†ط© ط§ظ„ط°ط§طھظٹط© ظپظٹ ط§ظ„ط®ظ„ظپظٹط© ط¨ط¯ظˆظ† ظپطھط­ ط§ظ„طھط·ط¨ظٹظ‚ (Zero-UI Autonomous Background Sync)
// ظٹظ‚ط±ط£ ط§ظ„ط­ط±ظƒط§طھ ط§ظ„ظ…ط¹ظ„ظ‚ط© ظ…ظ† IndexedDB ظˆظٹط±ظپط¹ظ‡ط§ ظ…ط¨ط§ط´ط±ط© ط¥ظ„ظ‰ MariaDB ظپظˆط± ط¹ظˆط¯ط© ط§ظ„ط¥ظ†طھط±ظ†طھ
// ط­طھظ‰ ظ„ظˆ ظƒط§ظ† ط§ظ„ظ…ط³طھط®ط¯ظ… ظ†ط§ط¦ظ…ط§ظ‹ ط£ظˆ ط§ظ„ظ‡ط§طھظپ ظ…ظ‚ظپظ„ط§ظ‹ ظˆط§ظ„طھط·ط¨ظٹظ‚ ظ…ط؛ظ„ظ‚ط§ظ‹ ط¨ط§ظ„ظƒط§ظ…ظ„
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
let _swSyncInProgress = false;
let _lastSwOpportunisticCheck = 0;

function openSwIDB() {
    return new Promise((resolve) => {
        if (typeof indexedDB === 'undefined') return resolve(null);
        try {
            const req = indexedDB.open('MagaiesOfflineIDB_v1', 2);
            req.onupgradeneeded = (e) => {
                const db = e.target.result;
                if (!db.objectStoreNames.contains('outbox')) {
                    db.createObjectStore('outbox', { keyPath: 'id' });
                }
                if (!db.objectStoreNames.contains('meta')) {
                    db.createObjectStore('meta', { keyPath: 'key' });
                }
                if (!db.objectStoreNames.contains('synced_ack')) {
                    db.createObjectStore('synced_ack', { keyPath: 'offline_id' });
                }
            };
            req.onsuccess = (e) => resolve(e.target.result);
            req.onerror = () => resolve(null);
        } catch (err) {
            resolve(null);
        }
    });
}

async function flushIndexedDBOutboxInSW() {
    if (_swSyncInProgress) return false;
    if (self.navigator && self.navigator.onLine === false) return false;

    _swSyncInProgress = true;
    try {
        const db = await openSwIDB();
        if (!db) return false;

        const scopeBase = self.registration.scope.replace(/\/$/, '');

        // 1. ظپط­طµ ظˆط±ظپط¹ ط£ظٹ طھط¹ط¯ظٹظ„ط§طھ ظ…ط¹ظ„ظ‚ط© ظ…ظ† ط´ط§ط´ط§طھ ط§ظ„ظ†ط¸ط§ظ… ط§ظ„ط´ط§ظ…ظ„ط© (js/app.js -> cloud_push_outbox)
        const cloudOutbox = await new Promise((resolve) => {
            try {
                const tx = db.transaction(['meta'], 'readonly');
                const req = tx.objectStore('meta').get('cloud_push_outbox');
                req.onsuccess = () => resolve(req.result || null);
                req.onerror = () => resolve(null);
            } catch (e) { resolve(null); }
        });

        if (cloudOutbox && cloudOutbox.payload && cloudOutbox.auth_token) {
            try {
                const pushUrl = scopeBase + '/api_index.php/sync/push?token=' + encodeURIComponent(cloudOutbox.auth_token);
                const cRes = await fetch(pushUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + cloudOutbox.auth_token
                    },
                    credentials: 'include',
                    body: JSON.stringify(cloudOutbox.payload)
                });
                if (cRes && cRes.ok) {
                    await new Promise((resolve) => {
                        try {
                            const tx = db.transaction(['meta'], 'readwrite');
                            tx.objectStore('meta').delete('cloud_push_outbox');
                            tx.oncomplete = () => resolve(true);
                            tx.onerror = () => resolve(false);
                        } catch (e) { resolve(false); }
                    });
                    const openClients = await self.clients.matchAll({ includeUncontrolled: true });
                    openClients.forEach((c) => c.postMessage({ type: 'CLOUD_PUSH_COMPLETED_BY_SW' }));
                }
            } catch (cErr) {
                console.warn('[SW BackgroundSync] Cloud push deferred:', cErr && cErr.message ? cErr.message : cErr);
            }
        }

        // 2. ظپط­طµ ظˆط±ظپط¹ ط·ط§ط¨ظˆط± ط§ظ„ط­ط±ظƒط§طھ ط§ظ„ظ…ظˆط­ط¯ ظˆط§ظ„ط³ظ†ط¯ط§طھ ظˆط§ظ„ظ…ط¨ظٹط¹ط§طھ ظˆط§ظ„ظ…ط´طھط±ظٹط§طھ (MagaiesOffline -> outbox)
        const items = await new Promise((resolve) => {
            try {
                const tx = db.transaction(['outbox'], 'readonly');
                const req = tx.objectStore('outbox').getAll();
                req.onsuccess = () => resolve(req.result || []);
                req.onerror = () => resolve([]);
            } catch (e) { resolve([]); }
        });

        if (!Array.isArray(items) || items.length === 0) {
            // ط­طھظ‰ ظ„ظˆ ظƒط§ظ† IndexedDB ظپط§ط±ط؛ط§ظ‹طŒ ظ†ط±ط³ظ„ ط¥ط´ط§ط±ط© ظ„ط£ظٹ ظ†ط§ظپط°ط© ظ…ظپطھظˆط­ط© ط¥ظ† ظˆط¬ط¯طھ
            const openClients = await self.clients.matchAll({ includeUncontrolled: true });
            openClients.forEach((c) => c.postMessage({ type: 'TRIGGER_OFFLINE_SYNC' }));
            return true;
        }

        const meta = await new Promise((resolve) => {
            try {
                const tx = db.transaction(['meta'], 'readonly');
                const req = tx.objectStore('meta').get('sync_context');
                req.onsuccess = () => resolve(req.result || {});
                req.onerror = () => resolve({});
            } catch (e) { resolve({}); }
        });

        const syncUrl = scopeBase + '/api/mirror_sync.php?action=sync_offline_batch';

        const headers = { 'Content-Type': 'application/json' };
        if (meta && meta.auth_token) {
            headers['X-Auth-Token'] = meta.auth_token;
        }

        const res = await fetch(syncUrl, {
            method: 'POST',
            headers: headers,
            credentials: 'include',
            body: JSON.stringify({
                items: items,
                device_code: (meta && meta.device_code) ? meta.device_code : '1',
                tenant_id: (meta && meta.tenant_id) ? meta.tenant_id : '12',
                auth_token: (meta && meta.auth_token) ? meta.auth_token : '',
                source: 'service_worker_background'
            })
        });

        if (!res || !res.ok) {
            throw new Error('SW sync HTTP ' + (res ? res.status : 'failed'));
        }

        const out = await res.json();
        if (out && out.success && Array.isArray(out.synced)) {
            const outboxMap = {};
            items.forEach((it) => {
                if (it && it.offline_id) outboxMap[String(it.offline_id)] = it;
            });
            // ظ…ط³ط­ ط§ظ„ط­ط±ظƒط§طھ ط§ظ„ظ…ط±ظپظˆط¹ط© ظ…ظ† outbox ظˆطھط³ط¬ظٹظ„ظ‡ط§ ظپظٹ synced_ack ظ„طھطھط­ط¯ط« ط§ظ„ط´ط§ط´ط§طھ ظ„ط§ط­ظ‚ط§ظ‹ ط¯ظˆظ† ط¥ط¹ط§ط¯ط© ط±ظپط¹
            await new Promise((resolve) => {
                try {
                    const tx = db.transaction(['outbox', 'synced_ack'], 'readwrite');
                    const outboxStore = tx.objectStore('outbox');
                    const ackStore = tx.objectStore('synced_ack');
                    out.synced.forEach((sItem) => {
                        if (sItem && sItem.offline_id) {
                            const orig = outboxMap[String(sItem.offline_id)] || {};
                            if (orig.id !== undefined) outboxStore.delete(orig.id);
                            else outboxStore.delete(String(sItem.offline_id));
                            const p = orig.payload || sItem.record || {};
                            const party = p.customerName || p.customer || p.supplierName || p.supplier ||
                                ((p.fromAccountName || p.fromAccount) && (p.toAccountName || p.toAccount)
                                    ? `${p.fromAccountName || p.fromAccount} â‍” ${p.toAccountName || p.toAccount}`
                                    : (p.toAccountName || p.fromAccountName || ''));
                            const amount = Number(p.amountLocal || p.total || p.netTotal || p.amount || 0);
                            ackStore.put({
                                offline_id: String(sItem.offline_id),
                                real_id: String(sItem.real_id || sItem.offline_id),
                                type: sItem.type || orig.type || 'sales',
                                created_at: orig.created_at || p.createdAt || p.date || new Date().toISOString(),
                                synced_at: sItem.synced_at || new Date().toISOString(),
                                sync_mode: 'background_sw',
                                amount: amount,
                                party: String(party || ''),
                                idempotency_key: (sItem.record && sItem.record.idempotencyKey) ? sItem.record.idempotencyKey : (orig.idempotency_key || ''),
                                record: sItem.record || null
                            });
                        }
                    });
                    tx.oncomplete = () => resolve(true);
                    tx.onerror = () => resolve(false);
                } catch (e) { resolve(false); }
            });

            // ط¥ط¹ظ„ط§ظ… ط£ظٹ ظ†ط§ظپط°ط© ظ…ظپطھظˆط­ط© ط¨طھط­ط¯ظٹط« ط£ط±ظ‚ط§ظ…ظ‡ط§ ظپظˆط±ط§ظ‹
            const clients = await self.clients.matchAll({ includeUncontrolled: true });
            clients.forEach((client) => {
                client.postMessage({
                    type: 'OFFLINE_SYNC_COMPLETED_BY_SW',
                    synced: out.synced
                });
            });

            // ط¥ط¸ظ‡ط§ط± ط¥ط´ط¹ط§ط± ط¹ظ„ظ‰ ط´ط§ط´ط© ط§ظ„ظ‚ظپظ„ (ط¥ظ† ظƒط§ظ†طھ ط§ظ„طµظ„ط§ط­ظٹط© ظ…ظپط¹ظ„ط©) ظ„ط·ظ…ط£ظ†ط© ط§ظ„ظ…ط³طھط®ط¯ظ… ط£ظ† ط§ظ„ط­ط±ظƒط§طھ ط§ط±طھظپط¹طھ طھظ„ظ‚ط§ط¦ظٹط§ظ‹
            try {
                if (self.Notification && self.Notification.permission === 'granted' && out.synced.length > 0) {
                    const count = out.synced.length;
                    const first = out.synced[0];
                    const docInfo = first ? ` (#${first.real_id || first.offline_id})` : '';
                    await self.registration.showNotification('ًںں¢ ظ…ظ‚ط§ظٹظٹط³ ERP: طھظ… ط±ظپط¹ ط§ظ„ط­ط±ظƒط§طھ طھظ„ظ‚ط§ط¦ظٹط§ظ‹', {
                        body: `طھظ… ظپظٹ ط§ظ„ط®ظ„ظپظٹط© طھط±ط­ظٹظ„ ظˆط§ط¹طھظ…ط§ط¯ ${count} ط­ط±ظƒط© ط£ظˆظپظ„ط§ظٹظ† ط¥ظ„ظ‰ ظ‚ط§ط¹ط¯ط© ط§ظ„ط¨ظٹط§ظ†ط§طھ ط¨ظ†ط¬ط§ط­${docInfo} ط¯ظˆظ† ط§ظ„ط­ط§ط¬ط© ظ„ظپطھط­ ط§ظ„طھط·ط¨ظٹظ‚.`,
                        icon: './assets/icon-192.png',
                        badge: './assets/icon-192.png',
                        tag: 'magaiees-bg-sync-success',
                        renotify: false,
                        data: { url: './mobile-suite.html' }
                    });
                }
            } catch (e) {}

            return true;
        }
        return false;
    } catch (err) {
        console.warn('[SW BackgroundSync] Deferred until stable connection:', err && err.message ? err.message : err);
        throw err; // ط¥ط±ط¬ط§ط¹ ط§ظ„ط®ط·ط£ ظ„ظٹط¹ظٹط¯ ظ†ط¸ط§ظ… طھط´ط؛ظٹظ„ ط§ظ„ظ‡ط§طھظپ (OS) ط§ظ„ظ…ط­ط§ظˆظ„ط© طھظ„ظ‚ط§ط¦ظٹط§ظ‹ ط¹ظ†ط¯ ط§ط³طھظ‚ط±ط§ط± ط§ظ„ط´ط¨ظƒط©
    } finally {
        _swSyncInProgress = false;
    }
}

// Warm-cache & instant sync message handler
self.addEventListener('message', (event) => {
    if (!event.data) return;

    if (event.data.type === 'SW_SYNC_NOW') {
        event.waitUntil(flushIndexedDBOutboxInSW().catch(() => {}));
        return;
    }

    if (event.data.type === 'WARM_OFFLINE_CACHE') {
        const urls = Array.isArray(event.data.urls) ? event.data.urls : PRECACHE_ASSETS;
        const token = event.data.token || '';
        const fetchOpts = { credentials: 'same-origin' };
        if (token) {
            fetchOpts.headers = { 'X-Auth-Token': token };
        }
        event.waitUntil(
            Promise.allSettled([
                flushIndexedDBOutboxInSW().catch(() => {}),
                caches.open(CACHE_NAME).then(async (cache) => {
                    for (const u of urls) {
                        try {
                            const hit = await cache.match(u, { ignoreSearch: true });
                            if (!hit) {
                                const res = await fetch(u, fetchOpts);
                                await storeInCache(cache, u, res);
                            }
                        } catch (e) {}
                    }
                })
            ])
        );
    }
});

// 1. OS-Level Background Sync Event: ظٹط¹ظ…ظ„ طھظ„ظ‚ط§ط¦ظٹط§ظ‹ ظپظˆط± ط¹ظˆط¯ط© ط§ظ„ط¥ظ†طھط±ظ†طھ ظ„ظ„ظ‡ط§طھظپ ط­طھظ‰ ظˆط§ظ„طھط·ط¨ظٹظ‚ ظ…ط؛ظ„ظ‚ طھظ…ط§ظ…ط§ظ‹
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-offline-outbox' || event.tag.startsWith('sync-offline')) {
        event.waitUntil(flushIndexedDBOutboxInSW());
    }
});

// 2. Periodic Background Sync Event: ظپط­طµ ط¯ظˆط±ظٹ ظپظٹ ط§ظ„ط®ظ„ظپظٹط© ظ„ط±ظپط¹ ط£ظٹ ط­ط±ظƒط§طھ ظ…ط¹ظ„ظ‚ط©
self.addEventListener('periodicsync', (event) => {
    if (event.tag === 'periodic-offline-outbox' || event.tag.startsWith('periodic-offline')) {
        event.waitUntil(flushIndexedDBOutboxInSW().catch(() => {}));
    }
});

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// ًں”” Web Push Event: Triggers when notification arrives while phone is locked
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
self.addEventListener('push', (event) => {
    console.log('[SW Push] Background push event received:', event);
    let payload = {
        title: 'ًں”” ط¥ط´ط¹ط§ط± ظپظˆط±ظٹ: ظ…ظ‚ط§ظٹظٹط³ ERP',
        body: 'طھظ†ط¨ظٹظ‡ طھظ†ظپظٹط°ظٹ ط¬ط¯ظٹط¯ ظٹطھط·ظ„ط¨ ط§ظ†طھط¨ط§ظ‡ظƒ ط¹ظ„ظ‰ ط´ط§ط´ط© ط§ظ„ط¬ظˆط§ظ„',
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
            { action: 'open', title: 'ط¹ط±ط¶ ظپظٹ ظ…ظ‚ط§ظٹظٹط³ ًںڑ€' },
            { action: 'dismiss', title: 'طھط¬ط§ظ‡ظ„' }
        ]
    };

    event.waitUntil(
        Promise.allSettled([
            flushIndexedDBOutboxInSW().catch(() => {}),
            self.registration.showNotification(payload.title, options)
        ])
    );
});

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// ًں“² Notification Click Event: When user taps the lockscreen notification
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
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

