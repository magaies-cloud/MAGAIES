// Version: 20260815060700 (Performance: Cache-first for static assets)
self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.filter(n => n !== 'magaies-static-v2').map(n => caches.delete(n))
            );
        }).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET' || !event.request.url.startsWith(self.location.origin)) {
        return;
    }

    const url = new URL(event.request.url);
    
    // Skip PHP and API requests - let browser handle normally
    if (url.pathname.endsWith('.php') || url.pathname.includes('/api/') || url.pathname.includes('api_index')) {
        return;
    }
    
    // Static assets (JS, CSS, fonts, images): Cache-First for maximum speed
    if (/\.(js|css|woff2?|ttf|png|jpg|jpeg|gif|svg|ico|webp)(\?.*)?$/i.test(url.pathname)) {
        event.respondWith(
            caches.match(event.request).then((cached) => {
                if (cached) return cached; // Instant from cache!
                return fetch(event.request).then((response) => {
                    if (response && response.status === 200 && response.type === 'basic') {
                        const clone = response.clone();
                        caches.open('magaies-static-v2').then(c => c.put(event.request, clone));
                    }
                    return response;
                });
            }).catch(() => caches.match(event.request))
        );
        return;
    }
    
    // Everything else: just pass through to browser
    return;
});
