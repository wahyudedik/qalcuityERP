/**
 * OfflinePOSManager
 * 
 * Mengelola transaksi POS offline dengan local storage dan auto-sync.
 * Mendukung full offline operation untuk kasir lapangan.
 */
class OfflinePOSManager {
    constructor() {
        this.queueManager = new window.OfflineQueueManager();
        this.storageKey = 'pos_offline_transactions';
        this.productsCacheKey = 'pos_products_cache';
        this.customersCacheKey = 'pos_customers_cache';
        this.listeners = [];

        this.init();
    }

    /**
     * Initialize
     */
    async init() {
        console.log('[OfflinePOS] Initialized');

        // Load cached products and customers
        await this.loadCachedData();

        // Subscribe to queue events
        this.queueManager.subscribe((event) => {
            this.handleQueueEvent(event);
        });
    }

    /**
     * Process checkout - online or offline
     * @param {Object} orderData - Order data
     * @returns {Promise<Object>} Result
     */
    async checkout(orderData) {
        if (navigator.onLine) {
            return await this.checkoutOnline(orderData);
        } else {
            return await this.checkoutOffline(orderData);
        }
    }

    /**
     * Online checkout
     * @param {Object} orderData - Order data
     * @returns {Promise<Object>} Server response
     */
    async checkoutOnline(orderData) {
        try {
            const response = await fetch('/pos/checkout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                },
                body: JSON.stringify(orderData),
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const result = await response.json();

            // Update local cache
            await this.updateLocalInventory(orderData.items);

            return {
                success: true,
                mode: 'online',
                data: result,
            };
        } catch (error) {
            console.error('[OfflinePOS] Online checkout failed:', error);
            // Fallback to offline
            return await this.checkoutOffline(orderData);
        }
    }

    /**
     * Offline checkout
     * @param {Object} orderData - Order data
     * @returns {Promise<Object>} Local transaction result
     */
    async checkoutOffline(orderData) {
        try {
            // Generate local transaction ID
            const localId = 'OFF-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);

            const transaction = {
                id: localId,
                ...orderData,
                status: 'pending_sync',
                created_at: new Date().toISOString(),
                synced: false,
            };

            // Save to local storage
            await this.saveOfflineTransaction(transaction);

            // Queue for sync
            await this.queueManager.enqueue({
                url: '/pos/checkout',
                method: 'POST',
                body: orderData,
                module: 'pos',
                priority: 1, // High priority for POS
                max_retries: 10,
            });

            // Update local inventory immediately
            await this.updateLocalInventory(orderData.items);

            console.log(`[OfflinePOS] Offline transaction saved: ${localId}`);

            return {
                success: true,
                mode: 'offline',
                local_id: localId,
                message: 'Transaksi disimpan offline. Akan disinkronkan saat online.',
                transaction,
            };
        } catch (error) {
            console.error('[OfflinePOS] Offline checkout error:', error);
            throw error;
        }
    }

    /**
     * Save offline transaction to localStorage
     * @param {Object} transaction - Transaction data
     */
    async saveOfflineTransaction(transaction) {
        const transactions = await this.getOfflineTransactions();
        transactions.push(transaction);
        localStorage.setItem(this.storageKey, JSON.stringify(transactions));
    }

    /**
     * Get all offline transactions
     * @returns {Promise<Array>} Array of transactions
     */
    async getOfflineTransactions() {
        const data = localStorage.getItem(this.storageKey);
        return data ? JSON.parse(data) : [];
    }

    /**
     * Mark transaction as synced
     * @param {string} localId - Local transaction ID
     */
    async markAsSynced(localId) {
        const transactions = await this.getOfflineTransactions();
        const index = transactions.findIndex(t => t.id === localId);

        if (index !== -1) {
            transactions[index].synced = true;
            transactions[index].synced_at = new Date().toISOString();
            localStorage.setItem(this.storageKey, JSON.stringify(transactions));
        }
    }

    /**
     * Remove synced transactions older than 7 days
     */
    async cleanupOldTransactions() {
        const transactions = await this.getOfflineTransactions();
        const sevenDaysAgo = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000);

        const filtered = transactions.filter(t => {
            if (t.synced) {
                return new Date(t.synced_at || t.created_at) > sevenDaysAgo;
            }
            return true; // Keep unsynced transactions
        });

        localStorage.setItem(this.storageKey, JSON.stringify(filtered));
    }

    /**
     * Update local inventory after transaction
     * @param {Array} items - Order items
     */
    async updateLocalInventory(items) {
        const cachedProducts = await this.getCachedProducts();

        if (!cachedProducts) return;

        items.forEach(item => {
            const product = cachedProducts.find(p => p.id === item.product_id);
            if (product) {
                product.stock = Math.max(0, product.stock - item.quantity);
            }
        });

        await this.cacheProducts(cachedProducts);
    }

    /**
     * Cache products for offline access
     * @param {Array} products - Products array
     */
    async cacheProducts(products) {
        await this.queueManager.cacheData(
            this.productsCacheKey,
            products,
            'pos',
            86400 // 24 hours TTL
        );
    }

    /**
     * Get cached products
     * @returns {Promise<Array|null>} Products or null
     */
    async getCachedProducts() {
        return await this.queueManager.getCachedData(this.productsCacheKey);
    }

    /**
     * Cache customers for offline access
     * @param {Array} customers - Customers array
     */
    async cacheCustomers(customers) {
        await this.queueManager.cacheData(
            this.customersCacheKey,
            customers,
            'pos',
            86400 // 24 hours TTL
        );
    }

    /**
     * Get cached customers
     * @returns {Promise<Array|null>} Customers or null
     */
    async getCachedCustomers() {
        return await this.queueManager.getCachedData(this.customersCacheKey);
    }

    /**
     * Load cached data on initialization
     */
    async loadCachedData() {
        try {
            const products = await this.getCachedProducts();
            const customers = await this.getCachedCustomers();

            console.log('[OfflinePOS] Cached data loaded:', {
                products: products ? products.length : 0,
                customers: customers ? customers.length : 0,
            });
        } catch (error) {
            console.error('[OfflinePOS] Failed to load cached data:', error);
        }
    }

    /**
     * Handle queue events
     * @param {Object} event - Event data
     */
    handleQueueEvent(event) {
        switch (event.type) {
            case 'MUTATION_SUCCESS':
                if (event.module === 'pos') {
                    this.markAsSynced(event.result.local_id);
                }
                break;

            case 'SYNC_COMPLETE':
                this.notifyListeners({
                    type: 'POS_SYNC_COMPLETE',
                    synced: event.synced,
                    failed: event.failed,
                });
                break;
        }
    }

    /**
     * Get offline transaction count
     * @returns {Promise<number>} Count
     */
    async getPendingTransactionCount() {
        const transactions = await this.getOfflineTransactions();
        return transactions.filter(t => !t.synced).length;
    }

    /**
     * Subscribe to POS events
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
     * Notify listeners
     * @param {Object} event - Event data
     */
    notifyListeners(event) {
        this.listeners.forEach(callback => {
            try {
                callback(event);
            } catch (error) {
                console.error('[OfflinePOS] Listener error:', error);
            }
        });
    }

    /**
     * Get statistics
     * @returns {Promise<Object>} Stats
     */
    async getStats() {
        const transactions = await this.getOfflineTransactions();
        const pendingCount = transactions.filter(t => !t.synced).length;
        const syncedCount = transactions.filter(t => t.synced).length;

        return {
            total: transactions.length,
            pending: pendingCount,
            synced: syncedCount,
            queue_stats: await this.queueManager.getStats(),
        };
    }
}

// Export as global singleton
window.OfflinePOSManager = OfflinePOSManager;
