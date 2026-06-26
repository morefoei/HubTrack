const CACHE_NAME = 'trackhub-v1';

self.addEventListener('install', (e) => {
    // Instalasi service worker (bisa ditambahkan file untuk offline cache di masa depan)
    self.skipWaiting();
});

self.addEventListener('fetch', (e) => {
    // Aplikasi ini selalu membutuhkan internet (fetching API), jadi bypass cache untuk request dinamis
    e.respondWith(fetch(e.request).catch(() => {
        // Fallback jika offline
    }));
});
