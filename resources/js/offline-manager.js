/**
 * OfflineQueueManager
 * 
 * Mengelola queue operasi offline untuk berbagai modul ERP.
 * Menyimpan mutations di IndexedDB dan auto-sync ketika online kembali.
 */

import logger from './logger';

class OfflineQueueManager {
    constructor() {
        this.dbName = 'qalcuity-erp';
        this.dbVersion = 2;
        this.storeName = 'mutation_queue';
        this.isOnline = navigator.onLine;
        this.syncInProgress = false;
        this.listeners = [];

        this.init();
        this.setupEventListeners();
    }

    /**
     * Initialize IndexedDB
     */
    async init() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.dbName, this.dbVersion);

            request.onupgradeneeded = (event) => {
                const db = event.target.result;

                // Create mutation queue store
                if (!db.objectStoreNames.contains(this.storeName)) {
                    const store = db.createObjectStore(this.storeName, {
                        keyPath: 'id',
                        autoIncrement: true
                    });
                    store.createIndex('module', 'module', { unique: false });
                    store.createIndex('status', 'status', { unique: false });
                    store.createIndex('queued_at', 'queued_at', { unique: false });
                    store.createIndex('priority', 'priority', { unique: false });
                }

                // Create cached data store for offline reads
                if (!db.objectStoreNames.contains('cached_data')) {
                    const store = db.createObjectStore('cached_data', {
                        keyPath: 'key'
                    });
                    store.createIndex('module', 'module', { unique: false });
                    store.createIndex('updated_at', 'updated_at', { unique: false });
                    store.createIndex('expires_at', 'expires_at', { unique: false });
                }
            };

            request.onsuccess = (event) => {
                this.db = event.target.result;
                logger.debug('[OfflineQueue] IndexedDB initialized');
                resolve(this.db);
            };

            request.onerror = (event) => {
                logger.error('[OfflineQueue] IndexedDB error', event.target.error);
                reject(event.target.error);
            };
        });
    }

    /**
     * Setup online/offline event listeners
     */
    setupEventListeners() {
        window.addEventListener('online', () => {
            logger.info('[OfflineQueue] Connection restored');
            this.isOnline = true;
            this.notifyListeners({ type: 'ONLINE' });
            this.autoSync();
        });

        window.addEventListener('offline', () => {
            logger.warn('[OfflineQueue] Connection lost');
            this.isOnline = false;
            this.notifyListeners({ type: 'OFFLINE' });
        });
    }

    /**
     * Add mutation to queue
     * @param {Object} mutation - Mutation data
     * @param {string} mutation.url - API endpoint URL
     * @param {string} mutation.method - HTTP method (POST/PUT/PATCH/DELETE)
     * @param {Object} mutation.body - Request body
     * @param {string} mutation.module - Module name (pos, inventory, sales, etc)
     * @param {number} mutation.priority - Priority (1=highest, 5=lowest)
     * @param {Function} mutation.onSuccess - Success callback
     * @param {Function} mutation.onError - Error callback
     * @returns {Promise<number>} Queue item ID
     */
    async enqueue(mutation) {
        try {
            await this.ensureDbReady();

            const item = {
                url: mutation.url,
                method: mutation.method || 'POST',
                body: mutation.body,
                module: mutation.module || 'general',
                priority: mutation.priority || 3,
                status: 'pending',
                queued_at: new Date().toISOString(),
                retry_count: 0,
                max_retries: mutation.max_retries || 5,
                // TASK 1.4: Exponential backoff configuration
                base_delay: mutation.base_delay || 1000, // 1 second base
                max_delay: mutation.max_delay || 300000, // 5 minutes max
                next_retry_at: null,
                csrf_token: document.querySelector('meta[name="csrf-token"]')?.content,
                // BUG-OFF-001 FIX: Add timestamp for conflict detection
                offline_timestamp: new Date().toISOString(),
                local_id: mutation.local_id || null,
                user_id: mutation.user_id || null,
                user_role: mutation.user_role || null,
                on_success_callback: mutation.onSuccess ? mutation.onSuccess.toString() : null,
                on_error_callback: mutation.onError ? mutation.onError.toString() : null,
            };

            return new Promise((resolve, reject) => {
                const transaction = this.db.transaction([this.storeName], 'readwrite');
                const store = transaction.objectStore(this.storeName);
                const request = store.add(item);

                request.onsuccess = () => {
                    const id = request.result;
                    logger.debug(`[OfflineQueue] Queued mutation #${id}: ${mutation.method} ${mutation.url}`);

                    this.notifyListeners({
                        type: 'QUEUED',
                        id,
                        module: mutation.module,
                        queueLength: this.getQueueLength(),
                    });

                    resolve(id);
                };

                request.onerror = (event) => {
                    logger.error('[OfflineQueue] Failed to queue mutation', event.target.error);
                    reject(event.target.error);
                };
            });
        } catch (error) {
            logger.error('[OfflineQueue] Enqueue error', error);
            throw error;
        }
    }

    /**
     * Get all pending mutations from queue
     * @returns {Promise<Array>} Array of queued mutations
     */
    async getPendingMutations() {
        await this.ensureDbReady();

        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([this.storeName], 'readonly');
            const store = transaction.objectStore(this.storeName);
            const index = store.index('status');
            const request = index.getAll('pending');

            request.onsuccess = () => {
                // Sort by priority (ascending) then queued_at (ascending)
                const items = request.result.sort((a, b) => {
                    if (a.priority !== b.priority) {
                        return a.priority - b.priority;
                    }
                    return new Date(a.queued_at) - new Date(b.queued_at);
                });
                resolve(items);
            };

            request.onerror = (event) => {
                reject(event.target.error);
            };
        });
    }

    /**
     * Sync all pending mutations
     * @returns {Promise<Object>} Sync results
     */
    async sync() {
        if (this.syncInProgress) {
            logger.debug('[OfflineQueue] Sync already in progress');
            return { synced: 0, failed: 0 };
        }

        if (!this.isOnline) {
            logger.warn('[OfflineQueue] Cannot sync: offline');
            return { synced: 0, failed: 0 };
        }

        this.syncInProgress = true;
        this.notifyListeners({ type: 'SYNC_START' });

        try {
            const pending = await this.getPendingMutations();
            let synced = 0;
            let failed = 0;

            logger.info(`[OfflineQueue] Starting sync: ${pending.length} mutations`);

            for (const mutation of pending) {
                try {
                    const success = await this.processMutation(mutation);
                    if (success) {
                        synced++;
                    } else {
                        failed++;
                    }
                } catch (error) {
                    logger.error(`[OfflineQueue] Failed to process mutation #${mutation.id}`, error);
                    failed++;

                    // Update retry count
                    await this.updateRetryCount(mutation.id, mutation.retry_count + 1);
                }
            }

            logger.info(`[OfflineQueue] Sync complete: ${synced} synced, ${failed} failed`);

            this.notifyListeners({
                type: 'SYNC_COMPLETE',
                synced,
                failed,
                queueLength: await this.getQueueLength(),
            });

            return { synced, failed };
        } catch (error) {
            logger.error('[OfflineQueue] Sync error', error);
            this.notifyListeners({ type: 'SYNC_ERROR', error: error.message });
            return { synced: 0, failed: 0 };
        } finally {
            this.syncInProgress = false;
        }
    }

    /**
     * Process single mutation
     * @param {Object} mutation - Mutation to process
     * @returns {Promise<boolean>} Success status
     */
    async processMutation(mutation) {
        try {
            // TASK 1.4: Check if it's time to retry (exponential backoff)
            if (mutation.next_retry_at) {
                const nextRetry = new Date(mutation.next_retry_at);
                if (new Date() < nextRetry) {
                    logger.debug(`[OfflineQueue] Skipping mutation #${mutation.id} - next retry at ${nextRetry.toISOString()}`);
                    return false; // Not ready yet
                }
            }

            // BUG-OFF-002 FIX: Refresh CSRF token before sync to avoid stale token
            const csrfToken = await this.getFreshCsrfToken();

            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken, // Use fresh token, not stored one
                'X-Offline-Sync': '1',
                // TASK 1.3: Include user info for role-priority resolution
                'X-User-ID': mutation.user_id || '',
                'X-User-Role': mutation.user_role || '',
            };

            const response = await fetch(mutation.url, {
                method: mutation.method,
                headers,
                body: mutation.body ? JSON.stringify(mutation.body) : undefined,
            });

            if (response.ok) {
                // Success - remove from queue
                await this.removeFromQueue(mutation.id);

                const result = await response.json().catch(() => ({}));

                // BUG-OFF-001 FIX: Check for conflict warnings
                if (result.conflict_warning) {
                    logger.warn(`[OfflineQueue] Conflict warning: ${result.conflict_warning}`);
                    this.notifyListeners({
                        type: 'CONFLICT_WARNING',
                        id: mutation.id,
                        module: mutation.module,
                        warning: result.conflict_warning,
                        result,
                    });
                }

                this.notifyListeners({
                    type: 'MUTATION_SUCCESS',
                    id: mutation.id,
                    module: mutation.module,
                    result,
                });

                return true;
            } else if (response.status === 409) {
                // BUG-OFF-001 FIX: Conflict detected
                logger.warn(`[OfflineQueue] Conflict detected for mutation #${mutation.id}`);
                const result = await response.json().catch(() => ({}));

                await this.markAsFailed(mutation.id, `Conflict: ${result.error || 'Conflict detected'}`);

                this.notifyListeners({
                    type: 'CONFLICT_DETECTED',
                    id: mutation.id,
                    module: mutation.module,
                    conflict_id: result.conflict_id,
                    strategy: result.strategy,
                    error: result.error,
                });

                return false;
            } else if (response.status === 419) {
                // BUG-OFF-002 FIX: CSRF token expired, retry with fresh token
                logger.warn(`[OfflineQueue] CSRF token expired for mutation #${mutation.id}, retrying...`);

                // Get fresh token and retry
                const freshToken = await this.getFreshCsrfToken();

                const retryResponse = await fetch(mutation.url, {
                    method: mutation.method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': freshToken,
                        'X-Offline-Sync': '1',
                    },
                    body: mutation.body ? JSON.stringify(mutation.body) : undefined,
                });

                if (retryResponse.ok) {
                    await this.removeFromQueue(mutation.id);
                    const result = await retryResponse.json().catch(() => ({}));

                    this.notifyListeners({
                        type: 'MUTATION_SUCCESS',
                        id: mutation.id,
                        module: mutation.module,
                        result,
                        retried: true,
                        reason: 'csrf_token_expired',
                    });

                    return true;
                } else {
                    // Still failed after retry
                    logger.error(`[OfflineQueue] Mutation #${mutation.id} failed after CSRF retry`);
                    await this.markAsFailed(mutation.id, 'CSRF retry failed');
                    return false;
                }
            } else if (response.status === 422) {
                // Validation error - don't retry
                logger.warn(`[OfflineQueue] Validation error for mutation #${mutation.id}`);
                await this.markAsFailed(mutation.id, 'Validation error');
                return false;
            } else if (response.status === 401 || response.status === 403) {
                // Auth error - re-queue for later
                logger.warn(`[OfflineQueue] Auth error for mutation #${mutation.id}, will retry`);
                return false;
            } else if (response.status === 429 || response.status >= 500) {
                // TASK 1.4: Rate limit or server error - apply exponential backoff
                const retryCount = mutation.retry_count + 1;
                const delay = this.calculateBackoffDelay(retryCount, mutation.base_delay, mutation.max_delay);

                logger.warn(`[OfflineQueue] Rate limit/server error for mutation #${mutation.id}, retry ${retryCount}/${mutation.max_retries} in ${delay}ms`);

                await this.updateRetryWithBackoff(mutation.id, retryCount, delay);
                return false;
            } else {
                // Other errors - retry with exponential backoff
                const retryCount = mutation.retry_count + 1;
                const delay = this.calculateBackoffDelay(retryCount, mutation.base_delay, mutation.max_delay);

                await this.updateRetryWithBackoff(mutation.id, retryCount, delay);
                throw new Error(`HTTP ${response.status}`);
            }
        } catch (error) {
            // Check if max retries exceeded
            if (mutation.retry_count >= mutation.max_retries) {
                logger.error(`[OfflineQueue] Max retries exceeded for mutation #${mutation.id}`);
                await this.markAsFailed(mutation.id, error.message);
                return false;
            }
            throw error;
        }
    }

    /**
     * TASK 1.4: Calculate exponential backoff delay
     * @param {number} retryCount - Current retry count
     * @param {number} baseDelay - Base delay in ms
     * @param {number} maxDelay - Maximum delay in ms
     * @returns {number} Delay in milliseconds
     */
    calculateBackoffDelay(retryCount, baseDelay = 1000, maxDelay = 300000) {
        // Exponential backoff with jitter
        const exponentialDelay = baseDelay * Math.pow(2, retryCount);
        const jitter = Math.random() * 0.3 * exponentialDelay; // Add up to 30% jitter
        const delay = Math.min(exponentialDelay + jitter, maxDelay);

        return Math.round(delay);
    }

    /**
     * TASK 1.4: Update retry count with backoff delay
     * @param {number} id - Mutation ID
     * @param {number} count - New retry count
     * @param {number} delayMs - Delay in milliseconds before next retry
     */
    async updateRetryWithBackoff(id, count, delayMs) {
        await this.ensureDbReady();

        const nextRetryAt = new Date(Date.now() + delayMs);

        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([this.storeName], 'readwrite');
            const store = transaction.objectStore(this.storeName);
            const request = store.get(id);

            request.onsuccess = () => {
                const item = request.result;
                if (item) {
                    item.retry_count = count;
                    item.last_retry_at = new Date().toISOString();
                    item.next_retry_at = nextRetryAt.toISOString();
                    item.backoff_delay = delayMs;
                    store.put(item);
                }
                resolve();
            };

            request.onerror = (event) => reject(event.target.error);
        });
    }

    /**
     * Auto-sync when connection restored
     */
    async autoSync() {
        // Wait a bit to ensure stable connection
        setTimeout(async () => {
            const queueLength = await this.getQueueLength();
            if (queueLength > 0) {
                logger.info(`[OfflineQueue] Auto-sync triggered: ${queueLength} pending mutations`);
                await this.sync();
            }
        }, 2000);
    }

    /**
     * Cache data for offline access
     * @param {string} key - Cache key
     * @param {*} data - Data to cache
     * @param {string} module - Module name
     * @param {number} ttlSeconds - Time to live in seconds
     */
    async cacheData(key, data, module = 'general', ttlSeconds = 3600) {
        await this.ensureDbReady();

        const item = {
            key,
            data,
            module,
            updated_at: new Date().toISOString(),
            expires_at: new Date(Date.now() + ttlSeconds * 1000).toISOString(),
        };

        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['cached_data'], 'readwrite');
            const store = transaction.objectStore('cached_data');
            const request = store.put(item);

            request.onsuccess = () => {
                logger.debug(`[OfflineQueue] Cached data: ${key}`);
                resolve();
            };

            request.onerror = (event) => {
                reject(event.target.error);
            };
        });
    }

    /**
     * Get cached data
     * @param {string} key - Cache key
     * @returns {Promise<*|null>} Cached data or null
     */
    async getCachedData(key) {
        await this.ensureDbReady();

        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['cached_data'], 'readonly');
            const store = transaction.objectStore('cached_data');
            const request = store.get(key);

            request.onsuccess = () => {
                const item = request.result;

                if (!item) {
                    resolve(null);
                    return;
                }

                // Check if expired
                if (new Date(item.expires_at) < new Date()) {
                    logger.debug(`[OfflineQueue] Cache expired: ${key}`);
                    this.removeCachedData(key);
                    resolve(null);
                    return;
                }

                resolve(item.data);
            };

            request.onerror = (event) => {
                reject(event.target.error);
            };
        });
    }

    /**
     * Remove cached data
     * @param {string} key - Cache key
     */
    async removeCachedData(key) {
        await this.ensureDbReady();

        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['cached_data'], 'readwrite');
            const store = transaction.objectStore('cached_data');
            const request = store.delete(key);

            request.onsuccess = () => resolve();
            request.onerror = (event) => reject(event.target.error);
        });
    }

    /**
     * Get queue length
     * @returns {Promise<number>} Number of pending mutations
     */
    async getQueueLength() {
        await this.ensureDbReady();

        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([this.storeName], 'readonly');
            const store = transaction.objectStore(this.storeName);
            const index = store.index('status');
            const request = index.count('pending');

            request.onsuccess = () => resolve(request.result);
            request.onerror = (event) => reject(event.target.error);
        });
    }

    /**
     * Remove mutation from queue
     * @param {number} id - Mutation ID
     */
    async removeFromQueue(id) {
        await this.ensureDbReady();

        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([this.storeName], 'readwrite');
            const store = transaction.objectStore(this.storeName);
            const request = store.delete(id);

            request.onsuccess = () => resolve();
            request.onerror = (event) => reject(event.target.error);
        });
    }

    /**
     * Mark mutation as failed
     * @param {number} id - Mutation ID
     * @param {string} reason - Failure reason
     */
    async markAsFailed(id, reason) {
        await this.ensureDbReady();

        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([this.storeName], 'readwrite');
            const store = transaction.objectStore(this.storeName);
            const request = store.get(id);

            request.onsuccess = () => {
                const item = request.result;
                if (item) {
                    item.status = 'failed';
                    item.failed_at = new Date().toISOString();
                    item.failure_reason = reason;
                    store.put(item);
                }
                resolve();
            };

            request.onerror = (event) => reject(event.target.error);
        });
    }

    /**
     * Update retry count
     * @param {number} id - Mutation ID
     * @param {number} count - New retry count
     */
    async updateRetryCount(id, count) {
        await this.ensureDbReady();

        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([this.storeName], 'readwrite');
            const store = transaction.objectStore(this.storeName);
            const request = store.get(id);

            request.onsuccess = () => {
                const item = request.result;
                if (item) {
                    item.retry_count = count;
                    item.last_retry_at = new Date().toISOString();
                    store.put(item);
                }
                resolve();
            };

            request.onerror = (event) => reject(event.target.error);
        });
    }

    /**
     * Clear all mutations from queue
     */
    async clearQueue() {
        await this.ensureDbReady();

        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([this.storeName], 'readwrite');
            const store = transaction.objectStore(this.storeName);
            const request = store.clear();

            request.onsuccess = () => {
                logger.info('[OfflineQueue] Queue cleared');
                this.notifyListeners({ type: 'QUEUE_CLEARED' });
                resolve();
            };

            request.onerror = (event) => reject(event.target.error);
        });
    }

    /**
     * Subscribe to queue events
     * @param {Function} callback - Event callback
     * @returns {Function} Unsubscribe function
     */
    subscribe(callback) {
        this.listeners.push(callback);
        return () => {
            this.listeners = this.listeners.filter(cb => cb !== callback);
        };
    }

    /**
     * Notify all listeners
     * @param {Object} event - Event data
     */
    notifyListeners(event) {
        this.listeners.forEach(callback => {
            try {
                callback(event);
            } catch (error) {
                logger.error('[OfflineQueue] Listener error', error);
            }
        });
    }

    /**
     * Ensure database is ready
     */
    async ensureDbReady() {
        if (!this.db) {
            await this.init();
        }
    }

    /**
     * BUG-OFF-002 FIX: Get fresh CSRF token from server
     * Prevents sync failures due to expired tokens
     * 
     * @returns {Promise<string>} Fresh CSRF token
     */
    async getFreshCsrfToken() {
        try {
            // Try to get from meta tag first (might be refreshed by server)
            const metaToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (metaToken) {
                return metaToken;
            }

            // If not available, fetch a new one from server
            const response = await fetch('/api/csrf-token', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                },
            });

            if (response.ok) {
                const data = await response.json();
                const token = data.csrf_token;

                // Update meta tag for future use
                let metaTag = document.querySelector('meta[name="csrf-token"]');
                if (metaTag) {
                    metaTag.setAttribute('content', token);
                } else {
                    metaTag = document.createElement('meta');
                    metaTag.name = 'csrf-token';
                    metaTag.content = token;
                    document.head.appendChild(metaTag);
                }

                logger.debug('[OfflineQueue] CSRF token refreshed');
                return token;
            }

            // Fallback to stored token (might fail)
            logger.warn('[OfflineQueue] Could not refresh CSRF token, using stored token');
            return this.getLastStoredToken();

        } catch (error) {
            logger.error('[OfflineQueue] Error refreshing CSRF token', error);
            // Fallback to last stored token
            return this.getLastStoredToken();
        }
    }

    /**
     * Get last stored CSRF token from pending mutations
     * @returns {string|null} CSRF token or null
     */
    async getLastStoredToken() {
        try {
            const pending = await this.getPendingMutations();
            if (pending.length > 0) {
                return pending[0].csrf_token || null;
            }
        } catch (error) {
            logger.error('[OfflineQueue] Error getting stored token', error);
        }
        return null;
    }

    /**
     * Get queue statistics
     * @returns {Promise<Object>} Queue stats
     */
    async getStats() {
        await this.ensureDbReady();

        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([this.storeName], 'readonly');
            const store = transaction.objectStore(this.storeName);
            const request = store.getAll();

            request.onsuccess = () => {
                const items = request.result;
                const stats = {
                    total: items.length,
                    pending: items.filter(i => i.status === 'pending').length,
                    failed: items.filter(i => i.status === 'failed').length,
                    byModule: {},
                };

                items.forEach(item => {
                    if (!stats.byModule[item.module]) {
                        stats.byModule[item.module] = { pending: 0, failed: 0 };
                    }
                    stats.byModule[item.module][item.status]++;
                });

                resolve(stats);
            };

            request.onerror = (event) => reject(event.target.error);
        });
    }
}

// Export as global singleton
window.OfflineQueueManager = OfflineQueueManager;
