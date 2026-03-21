// ─── Qalcuity ERP Service Worker ─────────────────────────────────────────────
// Strategy:
//   Static assets (JS/CSS/fonts) → Cache-first (long TTL)
//   Pages (HTML)                 → Network-first, fallback cache
//   API / POST                   → Network-only (skip SW)
//   POS checkout offline         → Queue in IndexedDB, sync when online

const SW_VERSION = 'qalcuity-v3';
const STATIC_CACHE = SW_VERSION + '-static';
const PAGE_CACHE = SW_VERSION + '-pages';
const OFFLINE_URL = '/offline';

// Assets yang di-precache saat install
const PRECACHE_STATIC = [
    '/offline',
    '/favicon.png',
    '/logo.png',
];

// ─── Install ──────────────────────────────────────────────────────────────────
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => cache.addAll(PRECACHE_STATIC))
            .then(() => self.skipWaiting())
    );
});

// ─── Activate ─────────────────────────────────────────────────────────────────
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys()
            .then(keys => Promise.all(
                keys
                    .filter(k => k !== STATIC_CACHE && k !== PAGE_CACHE)
                    .map(k => caches.delete(k))
            ))
            .then(() => self.clients.claim())
    );
});

// ─── Fetch ────────────────────────────────────────────────────────────────────
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET and cross-origin
    if (request.method !== 'GET') return;
    if (url.origin !== self.location.origin) return;

    // Skip API, chat, auth routes — always network
    const skipPaths = ['/chat/send', '/api/', '/sanctum/', '/login', '/logout', '/register'];
    if (skipPaths.some(p => url.pathname.startsWith(p))) return;

    // Static assets (build/, fonts, images) → Cache-first
    if (
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/storage/') ||
        /\.(js|css|woff2?|ttf|eot|svg|png|jpg|jpeg|webp|ico|gif)$/.test(url.pathname)
    ) {
        event.respondWith(cacheFirst(request, STATIC_CACHE));
        return;
    }

    // HTML pages → Network-first, fallback to cache, then offline page
    event.respondWith(networkFirstPage(request));
});

// ─── Cache-first strategy ─────────────────────────────────────────────────────
async function cacheFirst(request, cacheName) {
    const cached = await caches.match(request);
    if (cached) return cached;

    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(cacheName);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        return new Response('Asset not available offline', { status: 503 });
    }
}

// ─── Network-first for pages ──────────────────────────────────────────────────
async function networkFirstPage(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(PAGE_CACHE);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        const cached = await caches.match(request);
        if (cached) return cached;

        // Return offline page for navigation requests
        if (request.mode === 'navigate') {
            const offlinePage = await caches.match(OFFLINE_URL);
            return offlinePage || new Response('<h1>Offline</h1>', {
                headers: { 'Content-Type': 'text/html' }
            });
        }

        return new Response('Offline', { status: 503 });
    }
}

// ─── Background Sync — POS offline queue ─────────────────────────────────────
self.addEventListener('sync', event => {
    if (event.tag === 'pos-checkout-sync') {
        event.waitUntil(syncPosQueue());
    }
});

async function syncPosQueue() {
    const db = await openDb();
    const tx = db.transaction('pos_queue', 'readwrite');
    const store = tx.objectStore('pos_queue');
    const items = await storeGetAll(store);

    for (const item of items) {
        try {
            const res = await fetch('/pos/checkout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': item.csrf,
                },
                body: JSON.stringify(item.payload),
            });

            if (res.ok) {
                const delTx = db.transaction('pos_queue', 'readwrite');
                delTx.objectStore('pos_queue').delete(item.id);
                await txComplete(delTx);

                // Notify all clients
                const clients = await self.clients.matchAll();
                clients.forEach(c => c.postMessage({
                    type: 'POS_SYNC_SUCCESS',
                    orderId: item.id,
                }));
            }
        } catch {
            // Will retry on next sync
        }
    }
}

// ─── Push Notifications ───────────────────────────────────────────────────────
self.addEventListener('push', event => {
    const data = event.data?.json() ?? { title: 'Qalcuity ERP', body: 'Ada notifikasi baru' };
    event.waitUntil(
        self.registration.showNotification(data.title, {
            body: data.body,
            icon: '/favicon.png',
            badge: '/favicon.png',
            tag: data.tag ?? 'erp-notification',
            data: { url: data.url ?? '/dashboard' },
        })
    );
});

self.addEventListener('notificationclick', event => {
    event.notification.close();
    event.waitUntil(
        clients.matchAll({ type: 'window' }).then(windowClients => {
            const target = event.notification.data.url;
            const existing = windowClients.find(c => c.url === target && 'focus' in c);
            if (existing) return existing.focus();
            return clients.openWindow(target);
        })
    );
});

// ─── IndexedDB helpers ────────────────────────────────────────────────────────
function openDb() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open('qalcuity-pos', 1);
        req.onupgradeneeded = e => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains('pos_queue')) {
                const store = db.createObjectStore('pos_queue', { keyPath: 'id', autoIncrement: true });
                store.createIndex('queued_at', 'queued_at');
            }
        };
        req.onsuccess = e => resolve(e.target.result);
        req.onerror = e => reject(e.target.error);
    });
}

function storeGetAll(store) {
    return new Promise((resolve, reject) => {
        const req = store.getAll();
        req.onsuccess = e => resolve(e.target.result);
        req.onerror = e => reject(e.target.error);
    });
}

function txComplete(tx) {
    return new Promise((resolve, reject) => {
        tx.oncomplete = resolve;
        tx.onerror = e => reject(e.target.error);
    });
}
