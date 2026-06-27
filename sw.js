// Version: 20260627042900 (Use local scripts instead of CDN)
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
    // Always fetch from network to prevent serving stale files after the massive architecture update
    event.respondWith(
        fetch(event.request).catch(() => {
            // Fallback for offline mode if needed, but primarily we want network first
            return caches.match(event.request);
        })
    );
});
