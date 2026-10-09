'use strict';

const cachePrefix = 'agallery-pwa-' + encodeURIComponent(new URL(self.registration.scope).pathname) + '-';
const cacheName = cachePrefix + 'v1';
const offlineUrl = new URL('offline.html', self.registration.scope).href;
const staticUrls = ['offline.html', 'pwa-icon-192.png', 'pwa-icon-512.png', 'pwa-icon-maskable-512.png', 'pwa-icon-180.png']
    .map(path => new URL(path, self.registration.scope).href);

self.addEventListener('install', event => {
    event.waitUntil(caches.open(cacheName).then(cache => cache.addAll(staticUrls)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', event => {
    event.waitUntil(caches.keys().then(keys => Promise.all(
        keys.filter(key => key.startsWith(cachePrefix) && key !== cacheName).map(key => caches.delete(key))
    )).then(() => self.clients.claim()));
});

self.addEventListener('fetch', event => {
    const request = event.request;
    const url = new URL(request.url);
    if (request.method !== 'GET' || url.origin !== self.location.origin) {
        return;
    }
    if (request.mode === 'navigate') {
        event.respondWith(fetch(request, { cache: 'no-store' }).catch(async () => {
            const cache = await caches.open(cacheName);
            const offline = await cache.match(offlineUrl);
            if (!offline) return Response.error();
            const html = (await offline.text()).replace('<head>', '<head><base href="' + self.registration.scope + '">');
            return new Response(html, {
                headers: { 'Content-Type': 'text/html; charset=utf-8', 'Cache-Control': 'no-store' },
            });
        }));
        return;
    }
    if (staticUrls.includes(url.href)) {
        event.respondWith(caches.open(cacheName).then(async cache => await cache.match(request) || fetch(request)));
    }
});
