const CACHE_NAME = 'pos-cache-v3';
const MASTER_API_PATTERNS = ['/products', '/customers', '/pricing', '/categories', '/warehouses'];

const offlineResponse = (request) => {
    if (request.headers.get('accept')?.includes('text/html')) {
        return new Response('<!doctype html><title>Offline</title><p>This page is unavailable offline.</p>', {
            status: 503,
            headers: { 'Content-Type': 'text/html; charset=utf-8' },
        });
    }

    return new Response(JSON.stringify({ offline: true }), {
        status: 503,
        headers: { 'Content-Type': 'application/json' },
    });
};

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        Promise.all([
            self.clients.claim(),
            caches.keys().then((keys) => Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)))),
        ]).catch(() => undefined)
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    if (request.method !== 'GET') {
        return;
    }

    // Cache-first for master data API
    if (MASTER_API_PATTERNS.some((p) => url.pathname.includes(p))) {
        event.respondWith(
            caches.open(CACHE_NAME).then((cache) =>
                fetch(request)
                    .then((response) => {
                        if (response && response.status >= 200 && response.status < 300) {
                            cache.put(request, response.clone());
                        }
                        return response;
                    })
                        .catch(() => cache.match(request).then((cached) => cached || offlineResponse(request)))
                    ).catch(() => offlineResponse(request))
        );
        return;
    }

    // Network-first for transaction API
    if (url.pathname.includes('/transactions/')) {
        event.respondWith(
            fetch(event.request).catch(() => new Response(JSON.stringify({ offline: true }), {
                status: 503,
                headers: { 'Content-Type': 'application/json' },
            }))
        );
        return;
    }

    // Cache-first for static assets
    event.respondWith(
        caches.match(event.request)
            .then((cached) => cached || fetch(event.request))
            .catch(() => offlineResponse(event.request))
    );
});
