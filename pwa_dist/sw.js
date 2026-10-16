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

const CACHE_NAME = 'magaiees-pwa-v2026-10-16-offline-v23';

const PRECACHE_ASSETS = [
    './',
    './manifest.json',
    './mobile-suite.html',
    './mobile_erp_global_suite.html',
    './mobile-voucher.php',
    './mobile-voucher.html',
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
    './css/mobile-suite.css?v=2026_10_15_v17',
    './js/tailwind.min.js',
    './js/localforage.min.js',
    './js/pwa-manager.js?v=2026_10_15_v16',
    './js/magaies-offline-sync.js',
    './js/mobile-suite.js?v=2026_10_15_v16',
    './js/app.js',
    './js/single-cash-payment.js',
    './js/multi-cash-payment.js',
    './js/direct-sales-v3.js',
    './js/purchase-pro-v5.js',
    './js/sales-delivery.js'
];

function isValidCacheableResponse(res) {
    if (!res || res.status !== 200) return false;
    if (res.redirected) return false;
    if (res.url && (res.url.includes('login.php') || res.url.includes('logout='))) return false;
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
function fetchWithTimeout(request, timeoutMs = 1800) {
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

    const ua = request.headers.get('user-agent') || '';
    const isMobileUA = /Android|iPhone|iPad|iPod|Mobile/i.test(ua) || path.includes('mobile');

    if (isMobileUA) {
        return (
            (await caches.match(scopeBase + '/mobile-suite.html', { ignoreSearch: true })) ||
            (await caches.match(scopeBase + '/mobile-dashboard.php', { ignoreSearch: true })) ||
            (await caches.match(scopeBase + '/mobile_erp_global_suite.html', { ignoreSearch: true })) ||
            (await caches.match(scopeBase + '/mobile-voucher.php', { ignoreSearch: true })) ||
            (await caches.match(scopeBase + '/mobile-voucher.html', { ignoreSearch: true }))
        );
    } else {
        return (
            (await caches.match(scopeBase + '/magaiees_unified_dashboard.php', { ignoreSearch: true })) ||
            (await caches.match(scopeBase + '/single-cash-payment.php', { ignoreSearch: true })) ||
            (await caches.match(scopeBase + '/direct-sales.php', { ignoreSearch: true })) ||
            (await caches.match(scopeBase + '/purchase.php', { ignoreSearch: true })) ||
            (await caches.match(scopeBase + '/mobile-suite.html', { ignoreSearch: true }))
        );
    }
}

// Fetch Event: Instant App-Shell with Version-Aware Asset Caching + Opportunistic Outbox Sync
self.addEventListener('fetch', (event) => {
    const request = event.request;

    // فحص انتهازي صامت (كل 5 ثوانٍ) لرفع أي حركات أوفلاين معلقة في IndexedDB بمجرد مرور أي نشاط شبكي
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

    // 2. Navigation Requests: Instant 0ms Cache when offline, or Fast Network (1.8s) with instant offline cache fallback
    if (isNavigation && !url.searchParams.has('api') && !url.pathname.includes('/api/')) {
        event.respondWith(
            (async () => {
                const isExplicitlyOffline = (self.navigator && self.navigator.onLine === false);

                if (!isExplicitlyOffline) {
                    const freshResponse = await fetchWithTimeout(request, 1800)
                        .then((networkResponse) => {
                            if (isValidCacheableResponse(networkResponse)) {
                                const copy1 = networkResponse.clone();
                                const copy2 = networkResponse.clone();
                                caches.open(CACHE_NAME).then((cache) => {
                                    cache.put(request, copy1);
                                    cache.put(url.origin + url.pathname, copy2);
                                });
                                return networkResponse;
                            }
                            // If server redirected to login.php because session cookie expired while reconnecting, serve cached app if available
                            if (networkResponse && (networkResponse.redirected || (networkResponse.url && networkResponse.url.includes('login.php')))) {
                                return networkResponse;
                            }
                            return null;
                        })
                        .catch(() => null);

                    if (freshResponse) return freshResponse;
                }

                // Offline or network timeout: serve immediately from cache
                const cachedPage = await findBestCachedPage(request, url);
                if (cachedPage) return cachedPage;

                // Safe non-looping offline fallback screen (only shown if browser cache was completely cleared while offline)
                return new Response(
                    '<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>مقاييس ERP - وضع الأوفلاين</title></head><body style="background:#0f172a;color:#fff;font-family:Tajawal,sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;text-align:center;padding:20px;"><div style="max-width:400px;background:#1e293b;border:1px solid rgba(56,189,248,0.3);border-radius:20px;padding:24px;box-shadow:0 20px 50px rgba(0,0,0,0.5);"><div style="font-size:42px;margin-bottom:10px;">⚡</div><h2 style="margin:0 0 10px;font-size:18px;color:#38bdf8;">نظام مقاييس - وضع الأوفلاين الفوري</h2><p style="font-size:13px;color:#cbd5e1;line-height:1.6;margin-bottom:18px;">اختر الشاشة التي ترغب بفتحها في وضع الأوفلاين:</p><div style="display:flex;flex-direction:column;gap:10px;"><a href="mobile-voucher.php" style="background:linear-gradient(135deg,#10b981,#059669);color:#fff;text-decoration:none;padding:12px;border-radius:12px;font-weight:bold;font-size:14px;">💸 سند حركة مالية (جوال)</a><a href="mobile-suite.html" style="background:linear-gradient(135deg,#0284c7,#0369a1);color:#fff;text-decoration:none;padding:12px;border-radius:12px;font-weight:bold;font-size:14px;">📱 منظومة الجوال الشاملة</a><a href="magaiees_unified_dashboard.php" style="background:rgba(255,255,255,0.1);color:#e2e8f0;text-decoration:none;padding:12px;border-radius:12px;font-weight:bold;font-size:14px;">💻 منظومة اللابتوب الشاملة</a></div></div></body></html>',
                    { status: 200, headers: { 'Content-Type': 'text/html; charset=utf-8' } }
                );
            })()
        );
        return;
    }

    // 3. Static Assets (CSS, JS, Fonts, Images): Exact URL Cache-First, Offline Fallback
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
                const exactCached = await caches.match(request);
                if (exactCached) {
                    return exactCached;
                }

                if (self.navigator && self.navigator.onLine === false) {
                    const offlineCached = await caches.match(request, { ignoreSearch: true });
                    if (offlineCached) return offlineCached;
                    return new Response('', { status: 408 });
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

// ────────────────────────────────────────────────────────────────────
// 🚀 محرك المزامنة الذاتية في الخلفية بدون فتح التطبيق (Zero-UI Autonomous Background Sync)
// يقرأ الحركات المعلقة من IndexedDB ويرفعها مباشرة إلى MariaDB فور عودة الإنترنت
// حتى لو كان المستخدم نائماً أو الهاتف مقفلاً والتطبيق مغلقاً بالكامل
// ────────────────────────────────────────────────────────────────────
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

        const items = await new Promise((resolve) => {
            try {
                const tx = db.transaction(['outbox'], 'readonly');
                const req = tx.objectStore('outbox').getAll();
                req.onsuccess = () => resolve(req.result || []);
                req.onerror = () => resolve([]);
            } catch (e) { resolve([]); }
        });

        if (!Array.isArray(items) || items.length === 0) {
            // حتى لو كان IndexedDB فارغاً، نرسل إشارة لأي نافذة مفتوحة إن وجدت
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

        const scopeBase = self.registration.scope.replace(/\/$/, '');
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
            // مسح الحركات المرفوعة من outbox وتسجيلها في synced_ack لتتحدث الشاشات لاحقاً دون إعادة رفع
            await new Promise((resolve) => {
                try {
                    const tx = db.transaction(['outbox', 'synced_ack'], 'readwrite');
                    const outboxStore = tx.objectStore('outbox');
                    const ackStore = tx.objectStore('synced_ack');
                    outboxStore.clear();
                    out.synced.forEach((sItem) => {
                        if (sItem && sItem.offline_id) {
                            ackStore.put({
                                offline_id: String(sItem.offline_id),
                                real_id: String(sItem.real_id || sItem.offline_id),
                                type: sItem.type || 'sales',
                                synced_at: sItem.synced_at || new Date().toISOString(),
                                idempotency_key: (sItem.record && sItem.record.idempotencyKey) ? sItem.record.idempotencyKey : '',
                                record: sItem.record || null
                            });
                        }
                    });
                    tx.oncomplete = () => resolve(true);
                    tx.onerror = () => resolve(false);
                } catch (e) { resolve(false); }
            });

            // إعلام أي نافذة مفتوحة بتحديث أرقامها فوراً
            const clients = await self.clients.matchAll({ includeUncontrolled: true });
            clients.forEach((client) => {
                client.postMessage({
                    type: 'OFFLINE_SYNC_COMPLETED_BY_SW',
                    synced: out.synced
                });
            });

            // إظهار إشعار على شاشة القفل (إن كانت الصلاحية مفعلة) لطمأنة المستخدم أن الحركات ارتفعت تلقائياً
            try {
                if (self.Notification && self.Notification.permission === 'granted' && out.synced.length > 0) {
                    const count = out.synced.length;
                    const first = out.synced[0];
                    const docInfo = first ? ` (#${first.real_id || first.offline_id})` : '';
                    await self.registration.showNotification('🟢 مقاييس ERP: تم رفع الحركات تلقائياً', {
                        body: `تم في الخلفية ترحيل واعتماد ${count} حركة أوفلاين إلى قاعدة البيانات بنجاح${docInfo} دون الحاجة لفتح التطبيق.`,
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
        throw err; // إرجاع الخطأ ليعيد نظام تشغيل الهاتف (OS) المحاولة تلقائياً عند استقرار الشبكة
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

// 1. OS-Level Background Sync Event: يعمل تلقائياً فور عودة الإنترنت للهاتف حتى والتطبيق مغلق تماماً
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-offline-outbox' || event.tag.startsWith('sync-offline')) {
        event.waitUntil(flushIndexedDBOutboxInSW());
    }
});

// 2. Periodic Background Sync Event: فحص دوري في الخلفية لرفع أي حركات معلقة
self.addEventListener('periodicsync', (event) => {
    if (event.tag === 'periodic-offline-outbox' || event.tag.startsWith('periodic-offline')) {
        event.waitUntil(flushIndexedDBOutboxInSW().catch(() => {}));
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
        Promise.allSettled([
            flushIndexedDBOutboxInSW().catch(() => {}),
            self.registration.showNotification(payload.title, options)
        ])
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
