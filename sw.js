// Version: 20260811000712 (Use local scripts instead of CDN)
self.addEventListener('install', (event) => {
    // Force the new service worker to become active immediately
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    // Delete all existing caches to ensure users get the new IndexedDB update
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    return caches.delete(cacheName);
                })
            );
        }).then(() => {
            // Take control of all clients immediately
            return self.clients.claim();
        })
    );
});

self.addEventListener('fetch', (event) => {
    // Skip cross-origin requests and non-GET requests
    if (event.request.method !== 'GET' || !event.request.url.startsWith(self.location.origin)) {
        return;
    }
    
    // Always fetch from network first (Network First Strategy)
    event.respondWith(
        fetch(event.request).then((networkResponse) => {
            // Check if we received a valid response
            if (networkResponse && networkResponse.status === 200 && networkResponse.type === 'basic') {
                const responseToCache = networkResponse.clone();
                caches.open('magaies-dynamic-v1').then((cache) => {
                    cache.put(event.request, responseToCache);
                });
            }
            return networkResponse;
        }).catch(() => {
            // Fallback for offline mode
            return caches.match(event.request);
        })
    );
});
