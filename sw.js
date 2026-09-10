// Version: 20260910202500 (Financial OCR Left-Column Top Toolbar Icons Only)
self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map(n => caches.delete(n))
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
    
    // Static assets (JS, CSS, fonts, images): Network-First to always get latest
    if (/\.(js|css|woff2?|ttf|png|jpg|jpeg|gif|svg|ico|webp)(\?.*)?$/i.test(url.pathname)) {
        event.respondWith(
            fetch(event.request).then((response) => {
                if (response && response.status === 200 && response.type === 'basic') {
                    const clone = response.clone();
                    caches.open('magaies-static-v5').then(c => c.put(event.request, clone));
                }
                return response;
            }).catch(() => caches.match(event.request).then(cached => cached || new Response('', { status: 404, statusText: 'Not Found' })))
        );
        return;
    }
    
    // Everything else: just pass through to browser
    return;
});
