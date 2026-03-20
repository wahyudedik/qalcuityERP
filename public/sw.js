const CACHE_NAME = 'qalcuity-v1';
const OFFLINE_URL = '/offline';

const PRECACHE = [
    '/',
    '/dashboard',
    '/offline',
];

// Install: precache shell
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(PRECACHE)).then(() => self.skipWaiting())
    );
});

// Activate: clean old caches
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

// Fetch: network-first, fallback to cache
self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') return;
    if (event.request.url.includes('/api/') || event.request.url.includes('/chat/send')) return;

    event.respondWith(
        fetch(event.request)
            .then(response => {
                const clone = response.clone();
                caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                return response;
            })
            .catch(() => caches.match(event.request).then(cached => cached || caches.match(OFFLINE_URL)))
    );
});

// Push notifications
self.addEventListener('push', event => {
    const data = event.data?.json() ?? { title: 'Qalcuity ERP', body: 'Ada notifikasi baru' };
    event.waitUntil(
        self.registration.showNotification(data.title, {
            body: data.body,
            icon: '/favicon.png',
            badge: '/favicon.png',
            data: data.url ?? '/dashboard',
        })
    );
});

self.addEventListener('notificationclick', event => {
    event.notification.close();
    event.waitUntil(clients.openWindow(event.notification.data));
});
