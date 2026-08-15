// Version: 20260815044300 (Fix PHP interception bug)
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

    // Skip dynamic PHP and API requests entirely - let the browser handle them normally
    const url = new URL(event.request.url);
    if (url.pathname.endsWith('.php') || url.pathname.includes('/api/')) {
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
        }).catch(async (error) => {
            // Fallback for offline mode
            const cachedResponse = await caches.match(event.request);
            if (cachedResponse) {
                return cachedResponse;
            }
            // Must return a valid Response to avoid TypeError: Failed to convert value to 'Response'
            return new Response("Offline Mode: Resource not available in cache.", {
                status: 503,
                statusText: "Service Unavailable"
            });
        })
    );
});
