/**
 * Service Worker for qalcuityERP
 * Advanced caching strategies for optimal performance
 */

const CACHE_NAME = 'qalcuity-erp-v2';
const STATIC_CACHE = 'static-v2';
const DYNAMIC_CACHE = 'dynamic-v2';
const OFFLINE_PAGE = '/offline.html';

// Assets to cache immediately on install
const STATIC_ASSETS = [
    '/',
    '/offline.html',
    '/manifest.json',
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
    console.log('[SW] Installing service worker...');
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => {
                console.log('[SW] Caching static assets');
                return cache.addAll(STATIC_ASSETS);
            })
            .then(() => {
                console.log('[SW] Installation complete, skipping waiting');
                return self.skipWaiting();
            })
            .catch((error) => {
                console.error('[SW] Installation failed:', error);
            })
    );
});

// Activate event - clean old caches
self.addEventListener('activate', (event) => {
    console.log('[SW] Activating service worker...');
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((cacheName) => {
                        // Delete old caches that don't match current version
                        if (![STATIC_CACHE, DYNAMIC_CACHE].includes(cacheName)) {
                            console.log('[SW] Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                console.log('[SW] Activation complete, claiming clients');
                return self.clients.claim();
            })
    );
});

// Routes yang TIDAK boleh di-cache sama sekali (auth, session-sensitive)
const NO_CACHE_PATHS = [
    '/login', '/logout', '/register',
    '/forgot-password', '/reset-password',
    '/two-factor', '/verify-email',
    '/auth/google', '/dashboard',
    '/clear-cookies-temp',
];

function isNoCachePath(url) {
    return NO_CACHE_PATHS.some(path => url.pathname.startsWith(path));
}

// Cek apakah response mengandung Set-Cookie (jangan di-cache)
function hasCookieHeader(response) {
    return response.headers.has('set-cookie') ||
        response.headers.has('Set-Cookie');
}

// Fetch event - serve from cache with network fallback
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip cross-origin requests
    if (url.origin !== location.origin) {
        return;
    }

    // JANGAN cache auth pages dan session-sensitive routes
    if (isNoCachePath(url)) {
        event.respondWith(fetch(request));
        return;
    }

    // Handle different request types with different strategies
    if (request.method === 'GET') {
        if (isImageRequest(request)) {
            // Images: Cache first, network fallback
            event.respondWith(cacheFirstStrategy(request));
        } else if (isCSSRequest(request)) {
            // CSS: Stale while revalidate
            event.respondWith(staleWhileRevalidateStrategy(request));
        } else if (isJSRequest(request)) {
            // JavaScript: Cache first (versioned files)
            event.respondWith(cacheFirstStrategy(request));
        } else if (isHTMLRequest(request)) {
            // HTML: Network only — jangan cache HTML karena mengandung CSRF token & Set-Cookie
            event.respondWith(networkOnlyWithOfflineFallback(request));
        } else if (isAPIRequest(request)) {
            // API calls: Network first, cache fallback for offline
            event.respondWith(networkFirstStrategy(request, { timeout: 5000 }));
        } else {
            // Default: Stale while revalidate
            event.respondWith(staleWhileRevalidateStrategy(request));
        }
    }
});

// Network Only with Offline Fallback
// Untuk HTML pages — tidak pernah cache, tapi tampilkan offline page jika gagal
async function networkOnlyWithOfflineFallback(request) {
    try {
        const response = await fetch(request);
        return response;
    } catch (error) {
        console.log('[SW] Network failed for HTML, serving offline page:', request.url);
        if (request.mode === 'navigate') {
            return caches.match(OFFLINE_PAGE);
        }
        throw error;
    }
}

// Cache First Strategy
// Best for: Static assets, versioned files, images
async function cacheFirstStrategy(request) {
    const cachedResponse = await caches.match(request);

    if (cachedResponse) {
        console.log('[SW] Cache hit:', request.url);

        // Update cache in background (stale while revalidate)
        fetch(request).then(async (response) => {
            if (response && response.status === 200) {
                const cache = await caches.open(DYNAMIC_CACHE);
                cache.put(request, response.clone());
            }
        }).catch(() => {
            // Network failed, but we have cache - no problem
        });

        return cachedResponse;
    }

    // Not in cache, fetch from network
    try {
        const networkResponse = await fetch(request);

        if (networkResponse && networkResponse.status === 200) {
            const cache = await caches.open(DYNAMIC_CACHE);
            cache.put(request, networkResponse.clone());
        }

        return networkResponse;
    } catch (error) {
        console.error('[SW] Fetch failed:', request.url, error);

        // Return offline page for navigation requests
        if (request.mode === 'navigate') {
            return caches.match(OFFLINE_PAGE);
        }

        throw error;
    }
}

// Network First Strategy
// Best for: HTML pages, API calls, dynamic content
async function networkFirstStrategy(request, options = {}) {
    const { timeout = 5000 } = options;

    try {
        // Try network with timeout
        const networkResponse = await Promise.race([
            fetch(request),
            new Promise((_, reject) =>
                setTimeout(() => reject(new Error('Network timeout')), timeout)
            )
        ]);

        if (networkResponse && networkResponse.status === 200) {
            // Clone and store in cache
            const responseClone = networkResponse.clone();
            const cache = await caches.open(DYNAMIC_CACHE);

            // Don't cache non-success responses, non-GET requests, or responses with Set-Cookie
            if (request.method === 'GET' && !hasCookieHeader(networkResponse)) {
                cache.put(request, responseClone);
            }
        }

        return networkResponse;
    } catch (error) {
        console.log('[SW] Network failed, trying cache:', request.url);

        // Network failed, try cache
        const cachedResponse = await caches.match(request);

        if (cachedResponse) {
            console.log('[SW] Serving from cache:', request.url);
            return cachedResponse;
        }

        // Nothing in cache, return offline page for navigation
        if (request.mode === 'navigate') {
            return caches.match(OFFLINE_PAGE);
        }

        throw error;
    }
}

// Stale While Revalidate Strategy
// Best for: CSS, fonts, frequently updated resources
async function staleWhileRevalidateStrategy(request) {
    const cache = await caches.open(DYNAMIC_CACHE);
    const cachedResponse = await cache.match(request);

    // Always return cached version immediately if available
    if (cachedResponse) {
        console.log('[SW] Serving stale:', request.url);

        // Update cache in background
        fetch(request).then((response) => {
            if (response && response.status === 200) {
                cache.put(request, response.clone());
                console.log('[SW] Updated cache:', request.url);
            }
        }).catch(() => {
            // Background update failed, but user got cached content
        });

        return cachedResponse;
    }

    // No cache, fetch from network
    try {
        const networkResponse = await fetch(request);

        if (networkResponse && networkResponse.status === 200) {
            cache.put(request, networkResponse.clone());
        }

        return networkResponse;
    } catch (error) {
        console.error('[SW] Fetch failed:', request.url, error);
        throw error;
    }
}

// Helper functions to identify request types
function isImageRequest(request) {
    return request.destination === 'image' ||
        /\.(png|jpg|jpeg|gif|svg|webp|ico)$/i.test(new URL(request.url).pathname);
}

function isCSSRequest(request) {
    return request.destination === 'style' ||
        /\.css$/i.test(new URL(request.url).pathname);
}

function isJSRequest(request) {
    return request.destination === 'script' ||
        /\.js$/i.test(new URL(request.url).pathname);
}

function isHTMLRequest(request) {
    return request.destination === 'document' ||
        request.headers.get('accept')?.includes('text/html');
}

function isAPIRequest(request) {
    const url = new URL(request.url);
    return url.pathname.startsWith('/api/') ||
        url.pathname.includes('api');
}

// Message handling for cache management
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }

    if (event.data && event.data.type === 'CLEAR_CACHE') {
        event.waitUntil(
            caches.keys().then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((cacheName) => caches.delete(cacheName))
                );
            }).then(() => {
                event.ports[0].postMessage({ result: true });
            })
        );
    }

    // Trigger manual POS sync
    if (event.data && event.data.type === 'POS_SYNC') {
        event.waitUntil(syncPosQueue());
    }
});

// Background sync for offline actions
self.addEventListener('sync', (event) => {
    console.log('[SW] Sync event:', event.tag);

    if (event.tag === 'sync-data') {
        event.waitUntil(syncData());
    }

    if (event.tag === 'pos-checkout-sync') {
        event.waitUntil(syncPosQueue());
    }
});

async function syncData() {
    // Get pending offline actions from IndexedDB
    // This would integrate with your offline-manager.js
    console.log('[SW] Syncing offline data...');
}

async function syncPosQueue() {
    console.log('[SW] Syncing POS offline queue...');

    try {
        // Open IndexedDB
        const db = await openPosDb();
        const items = await getAllPosQueueItems(db);

        if (items.length === 0) {
            console.log('[SW] POS queue empty, nothing to sync');
            return;
        }

        console.log(`[SW] Found ${items.length} POS transactions to sync`);

        // Get CSRF token from a page client
        const clients = await self.clients.matchAll({ type: 'window' });
        let csrfToken = null;
        for (const client of clients) {
            // Ask the client for the CSRF token
            const channel = new MessageChannel();
            const tokenPromise = new Promise(resolve => {
                channel.port1.onmessage = e => resolve(e.data?.csrf_token || null);
                setTimeout(() => resolve(null), 2000);
            });
            client.postMessage({ type: 'GET_CSRF_TOKEN' }, [channel.port2]);
            csrfToken = await tokenPromise;
            if (csrfToken) break;
        }

        if (!csrfToken) {
            console.warn('[SW] Could not get CSRF token, deferring sync');
            return;
        }

        // Build transactions array for batch sync
        const transactions = items.map(item => ({
            offline_id: String(item.id),
            ...item.payload,
        }));

        const response = await fetch('/pos/sync-offline', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Offline-Sync': '1',
            },
            body: JSON.stringify({ transactions }),
        });

        if (!response.ok) {
            console.error('[SW] POS sync failed with status:', response.status);
            return;
        }

        const result = await response.json();
        console.log('[SW] POS sync result:', result);

        // Remove successfully synced items from IndexedDB
        const successIds = (result.results || [])
            .filter(r => r.success)
            .map(r => r.offline_id);

        for (const offlineId of successIds) {
            const item = items.find(i => String(i.id) === offlineId);
            if (item) {
                await deletePosQueueItem(db, item.id);
            }
        }

        // Notify all open clients
        const syncedCount = result.synced || 0;
        const failedCount = result.failed || 0;
        for (const client of clients) {
            client.postMessage({
                type: 'POS_SYNC_SUCCESS',
                synced: syncedCount,
                failed: failedCount,
                message: result.message,
            });
        }

    } catch (error) {
        console.error('[SW] POS sync error:', error);
    }
}

function openPosDb() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open('qalcuity-pos', 1);
        req.onsuccess = e => resolve(e.target.result);
        req.onerror = e => reject(e.target.error);
        req.onupgradeneeded = e => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains('pos_queue')) {
                const store = db.createObjectStore('pos_queue', { keyPath: 'id', autoIncrement: true });
                store.createIndex('queued_at', 'queued_at');
            }
        };
    });
}

function getAllPosQueueItems(db) {
    return new Promise((resolve, reject) => {
        const tx = db.transaction('pos_queue', 'readonly');
        const req = tx.objectStore('pos_queue').getAll();
        req.onsuccess = () => resolve(req.result || []);
        req.onerror = e => reject(e.target.error);
    });
}

function deletePosQueueItem(db, id) {
    return new Promise((resolve, reject) => {
        const tx = db.transaction('pos_queue', 'readwrite');
        const req = tx.objectStore('pos_queue').delete(id);
        req.onsuccess = () => resolve();
        req.onerror = e => reject(e.target.error);
    });
}

// Push notification handling
self.addEventListener('push', (event) => {
    console.log('[SW] Push received:', event);

    if (event.data) {
        const data = event.data.json();
        const options = {
            body: data.body || 'New notification',
            icon: '/favicon.png',
            badge: '/favicon.png',
            vibrate: [200, 100, 200],
            data: data.data || {},
            actions: data.actions || []
        };

        event.waitUntil(
            self.registration.showNotification(data.title || 'Qalcuity ERP', options)
        );
    }
});

// Notification click handling
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    event.waitUntil(
        clients.openWindow(event.notification.data.url || '/')
    );
});

console.log('[SW] Service Worker loaded');
