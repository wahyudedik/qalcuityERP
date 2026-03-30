/**
 * Qalcuity ERP — Offline Manager
 *
 * Provides offline mutation queueing, sync status, and cached data
 * management for ALL modules (not just POS).
 *
 * Usage in Blade:
 *   await window.ErpOffline.queue('invoices', '/invoices', 'POST', payload);
 *   const count = await window.ErpOffline.pendingCount();
 */

const DB_NAME = 'qalcuity-erp';
const DB_VERSION = 1;
let _db = null;

// ─── IndexedDB bootstrap ─────────────────────────────────────────────────────
function getDb() {
    if (_db) return Promise.resolve(_db);
    return new Promise((resolve, reject) => {
        const req = indexedDB.open(DB_NAME, DB_VERSION);
        req.onupgradeneeded = e => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains('mutation_queue')) {
                const s = db.createObjectStore('mutation_queue', { keyPath: 'id', autoIncrement: true });
                s.createIndex('module', 'module');
                s.createIndex('queued_at', 'queued_at');
            }
            if (!db.objectStoreNames.contains('cached_data')) {
                const s = db.createObjectStore('cached_data', { keyPath: 'key' });
                s.createIndex('module', 'module');
                s.createIndex('updated_at', 'updated_at');
            }
        };
        req.onsuccess = e => { _db = e.target.result; resolve(_db); };
        req.onerror = e => reject(e.target.error);
    });
}

// ─── Queue a mutation for offline sync ────────────────────────────────────────
async function queueMutation(module, url, method, body) {
    const db = await getDb();
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    return new Promise((resolve, reject) => {
        const tx = db.transaction('mutation_queue', 'readwrite');
        const store = tx.objectStore('mutation_queue');
        const record = { module, url, method, body, csrf, queued_at: Date.now() };
        const req = store.add(record);
        req.onsuccess = () => {
            resolve(req.result);
            updateBadge();
        };
        req.onerror = e => reject(e.target.error);
    });
}

// ─── Get pending mutation count ───────────────────────────────────────────────
async function pendingCount(module) {
    try {
        const db = await getDb();
        return new Promise(resolve => {
            const tx = db.transaction('mutation_queue', 'readonly');
            if (module) {
                const idx = tx.objectStore('mutation_queue').index('module');
                const req = idx.count(IDBKeyRange.only(module));
                req.onsuccess = () => resolve(req.result);
                req.onerror = () => resolve(0);
            } else {
                const req = tx.objectStore('mutation_queue').count();
                req.onsuccess = () => resolve(req.result);
                req.onerror = () => resolve(0);
            }
        });
    } catch { return 0; }
}

// ─── Get all pending items (optionally by module) ─────────────────────────────
async function pendingItems(module) {
    const db = await getDb();
    return new Promise((resolve, reject) => {
        const tx = db.transaction('mutation_queue', 'readonly');
        let req;
        if (module) {
            const idx = tx.objectStore('mutation_queue').index('module');
            req = idx.getAll(IDBKeyRange.only(module));
        } else {
            req = tx.objectStore('mutation_queue').getAll();
        }
        req.onsuccess = () => resolve(req.result);
        req.onerror = e => reject(e.target.error);
    });
}

// ─── Cache data locally (for read-heavy pages like dashboard) ─────────────────
async function cacheData(key, module, data) {
    const db = await getDb();
    return new Promise((resolve, reject) => {
        const tx = db.transaction('cached_data', 'readwrite');
        tx.objectStore('cached_data').put({
            key, module, data, updated_at: Date.now(),
        });
        tx.oncomplete = resolve;
        tx.onerror = e => reject(e.target.error);
    });
}

async function getCachedData(key) {
    try {
        const db = await getDb();
        return new Promise(resolve => {
            const tx = db.transaction('cached_data', 'readonly');
            const req = tx.objectStore('cached_data').get(key);
            req.onsuccess = () => resolve(req.result?.data ?? null);
            req.onerror = () => resolve(null);
        });
    } catch { return null; }
}

// ─── Offline-aware fetch wrapper ──────────────────────────────────────────────
async function offlineFetch(url, options = {}) {
    const method = (options.method || 'GET').toUpperCase();
    const module = options.offlineModule || 'general';

    // GET requests — try network, fall back to cache
    if (method === 'GET') {
        try {
            const res = await fetch(url, options);
            if (res.ok) {
                const data = await res.clone().json().catch(() => null);
                if (data) {
                    cacheData(`get:${url}`, module, data);
                }
            }
            return res;
        } catch {
            const cached = await getCachedData(`get:${url}`);
            if (cached) {
                return new Response(JSON.stringify(cached), {
                    status: 200,
                    headers: { 'Content-Type': 'application/json', 'X-Offline-Cache': '1' },
                });
            }
            throw new Error('Offline dan tidak ada data cache');
        }
    }

    // Mutation requests — try network, queue if offline
    try {
        const res = await fetch(url, options);
        return res;
    } catch {
        const body = options.body ? JSON.parse(options.body) : null;
        const id = await queueMutation(module, url, method, body);

        // Request background sync
        if ('serviceWorker' in navigator && 'SyncManager' in window) {
            const reg = await navigator.serviceWorker.ready;
            await reg.sync.register('erp-mutation-sync').catch(() => { });
        }

        return new Response(JSON.stringify({
            offline: true,
            queued: true,
            queue_id: id,
            message: 'Disimpan offline. Akan disinkronisasi saat online.',
        }), {
            status: 202,
            headers: { 'Content-Type': 'application/json', 'X-Offline-Queued': '1' },
        });
    }
}

// ─── Manual sync flush (when coming back online) ─────────────────────────────
async function flushQueue() {
    const items = await pendingItems();
    let synced = 0;
    const db = await getDb();

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
                await new Promise((resolve, reject) => {
                    const tx = db.transaction('mutation_queue', 'readwrite');
                    tx.objectStore('mutation_queue').delete(item.id);
                    tx.oncomplete = resolve;
                    tx.onerror = e => reject(e.target.error);
                });
                synced++;
            }
        } catch { /* will retry */ }
    }

    updateBadge();
    return synced;
}

// ─── UI badge update ──────────────────────────────────────────────────────────
async function updateBadge() {
    const count = await pendingCount();
    const badge = document.getElementById('offline-sync-badge');
    if (badge) {
        badge.textContent = count;
        badge.classList.toggle('hidden', count === 0);
    }
    // Also update the offline indicator
    const indicator = document.getElementById('offline-indicator');
    if (indicator) {
        indicator.dataset.pending = count;
    }
}

// ─── Online/Offline event handling ────────────────────────────────────────────
function initOfflineDetection() {
    const indicator = document.getElementById('offline-indicator');

    function setStatus(online) {
        if (indicator) {
            indicator.classList.toggle('hidden', online);
        }
        document.body.dataset.online = online ? '1' : '0';

        if (online) {
            flushQueue().then(synced => {
                if (synced > 0) {
                    showOfflineToast(`${synced} perubahan offline berhasil disinkronisasi`, 'success');
                }
            });
        }
    }

    window.addEventListener('online', () => setStatus(true));
    window.addEventListener('offline', () => setStatus(false));
    setStatus(navigator.onLine);

    // Listen for SW sync messages
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.addEventListener('message', e => {
            if (e.data?.type === 'ERP_SYNC_COMPLETE') {
                showOfflineToast(`${e.data.synced} perubahan berhasil disinkronisasi`, 'success');
                updateBadge();
            }
            if (e.data?.type === 'ERP_SYNC_RESULT' && !e.data.success) {
                showOfflineToast(`Gagal sinkronisasi: ${e.data.module}`, 'error');
            }
            if (e.data?.type === 'POS_SYNC_SUCCESS') {
                showOfflineToast('Transaksi POS offline berhasil disinkronisasi', 'success');
            }
        });
    }

    updateBadge();
}

function showOfflineToast(message, type = 'info') {
    // Use existing toast system if available, otherwise create one
    if (typeof window.showToast === 'function') {
        window.showToast(message, type);
        return;
    }

    const colors = {
        success: 'bg-green-500/90',
        error: 'bg-red-500/90',
        warning: 'bg-amber-500/90',
        info: 'bg-blue-500/90',
    };

    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 z-[9999] px-4 py-3 rounded-xl text-white text-sm font-medium shadow-lg ${colors[type] || colors.info} transition-all duration-300 translate-y-2 opacity-0`;
    toast.textContent = message;
    document.body.appendChild(toast);

    requestAnimationFrame(() => {
        toast.classList.remove('translate-y-2', 'opacity-0');
    });

    setTimeout(() => {
        toast.classList.add('translate-y-2', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// ─── Export to window ─────────────────────────────────────────────────────────
window.ErpOffline = {
    queue: queueMutation,
    pendingCount,
    pendingItems,
    cacheData,
    getCachedData,
    fetch: offlineFetch,
    flush: flushQueue,
    updateBadge,
    init: initOfflineDetection,
};

// Auto-init when DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initOfflineDetection);
} else {
    initOfflineDetection();
}
