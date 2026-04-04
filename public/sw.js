// ─── Qalcuity ERP Service Worker ─────────────────────────────────────────────
// Strategy:
//   Static assets (JS/CSS/fonts) → Cache-first (long TTL)
//   Pages (HTML)                 → Network-first, fallback cache
//   API / POST                   → Network-only (skip SW) unless offline-queued
//   Module data (JSON reads)     → Stale-while-revalidate
//   Offline mutations            → Queue in IndexedDB, sync when online

const SW_VERSION = 'qalcuity-v5';
const STATIC_CACHE = SW_VERSION + '-static';
const PAGE_CACHE = SW_VERSION + '-pages';
const DATA_CACHE = SW_VERSION + '-data';
const OFFLINE_URL = '/offline';

// ─── Precache on install ──────────────────────────────────────────────────────
const PRECACHE_STATIC = [
    '/offline',
    '/favicon.png',
    '/logo.png',
];

// ─── Mobile/Field-mode pages that get extra aggressive caching ─────────────────
const MOBILE_PAGES = [
    '/mobile',
    '/mobile/picking',
    '/mobile/opname',
    '/mobile/farm-activity',
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => cache.addAll(PRECACHE_STATIC))
            .then(() => self.skipWaiting())
    );
});

// ─── Activate — clean old caches ──────────────────────────────────────────────
self.addEventListener('activate', event => {
    const keep = [STATIC_CACHE, PAGE_CACHE, DATA_CACHE];
    event.waitUntil(
        caches.keys()
            .then(keys => Promise.all(keys.filter(k => !keep.includes(k)).map(k => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

// ─── Routes that should NEVER be cached ───────────────────────────────────────
const SKIP_PATHS = ['/chat/send', '/sanctum/', '/login', '/logout', '/register', '/broadcasting/'];
const SKIP_PREFIXES_POST = ['/api/'];

// ─── Module page paths eligible for offline caching ───────────────────────────
const MODULE_PAGES = [
    '/dashboard', '/inventory', '/invoices', '/expenses', '/customers',
    '/suppliers', '/products', '/warehouses', '/sales', '/purchasing',
    '/quotations', '/delivery-orders', '/hrm', '/payroll', '/assets',
    '/budget', '/accounting', '/bank-accounts', '/receivables',
    '/projects', '/contracts', '/crm', '/helpdesk', '/pos',
    '/reports', '/fleet', '/manufacturing', '/production',
    '/mobile',
];

// ─── Fetch handler ────────────────────────────────────────────────────────────
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip cross-origin
    if (url.origin !== self.location.origin) return;

    // Skip auth/realtime routes
    if (SKIP_PATHS.some(p => url.pathname.startsWith(p))) return;

    // Non-GET: only intercept if we need to queue offline mutations
    if (request.method !== 'GET') {
        // POST/PUT/PATCH/DELETE — let them through normally when online
        // Offline queueing is handled client-side via offline-manager.js
        return;
    }

    // Static assets → Cache-first
    if (
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/storage/') ||
        /\.(js|css|woff2?|ttf|eot|svg|png|jpg|jpeg|webp|ico|gif)$/.test(url.pathname)
    ) {
        event.respondWith(cacheFirst(request, STATIC_CACHE));
        return;
    }

    // JSON API data requests (Accept: application/json) → Stale-while-revalidate
    if (request.headers.get('Accept')?.includes('application/json') ||
        url.pathname.startsWith('/api/')) {
        event.respondWith(staleWhileRevalidate(request, DATA_CACHE));
        return;
    }

    // HTML pages → Network-first with module-aware caching
    // Mobile pages use stale-while-revalidate for resilient offline access
    if (MOBILE_PAGES.some(p => url.pathname === p || url.pathname.startsWith(p + '/'))) {
        event.respondWith(networkFirstPage(request, true));
        return;
    }
    event.respondWith(networkFirstPage(request));
});

// ─── Cache-first (static assets) ──────────────────────────────────────────────
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

// ─── Stale-while-revalidate (data/JSON) ───────────────────────────────────────
async function staleWhileRevalidate(request, cacheName) {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);

    const fetchPromise = fetch(request).then(response => {
        if (response.ok) {
            cache.put(request, response.clone());
        }
        return response;
    }).catch(() => null);

    // Return cached immediately if available, otherwise wait for network
    if (cached) {
        // Fire-and-forget the revalidation
        fetchPromise;
        return cached;
    }

    const networkResponse = await fetchPromise;
    if (networkResponse) return networkResponse;

    // No cache, no network — return offline JSON
    return new Response(JSON.stringify({
        offline: true,
        message: 'Data tidak tersedia offline. Silakan periksa koneksi internet.',
    }), {
        status: 503,
        headers: { 'Content-Type': 'application/json' },
    });
}

// ─── Network-first (HTML pages) ──────────────────────────────────────────────
async function networkFirstPage(request, mobilePriority = false) {
    const cache = await caches.open(PAGE_CACHE);

    // Mobile-priority: race network vs cache — return whichever wins first
    if (mobilePriority) {
        const cached = await cache.match(request);
        try {
            const response = await fetch(request);
            if (response.ok && response.headers.get('Content-Type')?.includes('text/html')) {
                cache.put(request, response.clone()); // update cache in background
            }
            return response;
        } catch {
            if (cached) return cached;
            const offlinePage = await caches.match(OFFLINE_URL);
            return offlinePage || new Response('<h1>Offline — Mode Lapangan</h1><p>Buka halaman ini saat online untuk menyimpan ke cache.</p>', {
                headers: { 'Content-Type': 'text/html' },
            });
        }
    }

    try {
        const response = await fetch(request);
        if (response.ok && response.headers.get('Content-Type')?.includes('text/html')) {
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        const cached = await cache.match(request);
        if (cached) return cached;

        if (request.mode === 'navigate') {
            const offlinePage = await caches.match(OFFLINE_URL);
            return offlinePage || new Response('<h1>Offline</h1>', {
                headers: { 'Content-Type': 'text/html' },
            });
        }

        return new Response('Offline', { status: 503 });
    }
}

// ─── Background Sync — universal offline queue ───────────────────────────────
self.addEventListener('sync', event => {
    if (event.tag === 'pos-checkout-sync') {
        event.waitUntil(syncQueue('pos_queue', '/pos/checkout'));
    }
    if (event.tag === 'erp-mutation-sync') {
        event.waitUntil(syncMutationQueue());
    }
});

// Sync POS-specific queue (backward compat)
async function syncQueue(storeName, endpoint) {
    const db = await openDb('qalcuity-pos', 1, storeName);
    const items = await dbGetAll(db, storeName);

    for (const item of items) {
        try {
            const res = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': item.csrf },
                body: JSON.stringify(item.payload),
            });
            if (res.ok) {
                await dbDelete(db, storeName, item.id);
                notifyClients({ type: 'POS_SYNC_SUCCESS', orderId: item.id });
            }
        } catch { /* retry next sync */ }
    }
}

// Sync generic ERP mutation queue
async function syncMutationQueue() {
    const db = await openErpDb();
    const items = await dbGetAll(db, 'mutation_queue');
    let synced = 0;

    for (const item of items) {
        try {
            const res = await fetch(item.url, {
                method: item.method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': item.csrf,
                    'X-Offline-Sync': '1',
                },
                body: item.body ? JSON.stringify(item.body) : undefined,
            });

            if (res.ok || res.status === 422) {
                // 422 = validation error, don't retry
                const result = await res.json().catch(() => ({}));
                await dbDelete(db, 'mutation_queue', item.id);
                synced++;
                notifyClients({
                    type: 'ERP_SYNC_RESULT',
                    id: item.id,
                    module: item.module,
                    success: res.ok,
                    status: res.status,
                    result,
                });
            }
        } catch { /* retry next sync */ }
    }

    if (synced > 0) {
        notifyClients({ type: 'ERP_SYNC_COMPLETE', synced });
    }
}

async function notifyClients(data) {
    const clients = await self.clients.matchAll();
    clients.forEach(c => c.postMessage(data));
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
function openDb(name, version, storeName) {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open(name, version);
        req.onupgradeneeded = e => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains(storeName)) {
                db.createObjectStore(storeName, { keyPath: 'id', autoIncrement: true });
            }
        };
        req.onsuccess = e => resolve(e.target.result);
        req.onerror = e => reject(e.target.error);
    });
}

function openErpDb() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open('qalcuity-erp', 1);
        req.onupgradeneeded = e => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains('mutation_queue')) {
                const store = db.createObjectStore('mutation_queue', { keyPath: 'id', autoIncrement: true });
                store.createIndex('module', 'module');
                store.createIndex('queued_at', 'queued_at');
            }
            if (!db.objectStoreNames.contains('cached_data')) {
                const store = db.createObjectStore('cached_data', { keyPath: 'key' });
                store.createIndex('module', 'module');
                store.createIndex('updated_at', 'updated_at');
            }
        };
        req.onsuccess = e => resolve(e.target.result);
        req.onerror = e => reject(e.target.error);
    });
}

function dbGetAll(db, storeName) {
    return new Promise((resolve, reject) => {
        const tx = db.transaction(storeName, 'readonly');
        const req = tx.objectStore(storeName).getAll();
        req.onsuccess = () => resolve(req.result);
        req.onerror = e => reject(e.target.error);
    });
}

function dbDelete(db, storeName, key) {
    return new Promise((resolve, reject) => {
        const tx = db.transaction(storeName, 'readwrite');
        tx.objectStore(storeName).delete(key);
        tx.oncomplete = resolve;
        tx.onerror = e => reject(e.target.error);
    });
}
