/**
 * OfflineStatusIndicator
 * 
 * Menampilkan indikator status koneksi dan queue sync di UI.
 * Auto-show ketika offline atau ada pending sync.
 */
class OfflineStatusIndicator {
    constructor(options = {}) {
        this.position = options.position || 'bottom-right';
        this.autoHide = options.autoHide !== false;
        this.hideDelay = options.hideDelay || 5000;
        this.visible = false;
        this.queueManager = new window.OfflineQueueManager();
        this.posManager = options.enablePOS ? new window.OfflinePOSManager() : null;

        this.init();
    }

    /**
     * Initialize indicator
     */
    async init() {
        this.createIndicatorElement();
        this.setupEventListeners();
        await this.updateStatus();

        console.log('[OfflineStatus] Indicator initialized');
    }

    /**
     * Create indicator DOM element
     */
    createIndicatorElement() {
        this.element = document.createElement('div');
        this.element.id = 'offline-status-indicator';
        this.element.className = 'fixed z-50 transition-all duration-300 transform translate-y-full opacity-0';

        // Position classes
        const positionClasses = {
            'top-left': 'top-4 left-4',
            'top-right': 'top-4 right-4',
            'bottom-left': 'bottom-4 left-4',
            'bottom-right': 'bottom-4 right-4',
        };

        this.element.classList.add(...(positionClasses[this.position] || positionClasses['bottom-right']).split(' '));

        this.element.innerHTML = `
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-4 min-w-[320px]">
                <!-- Header -->
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-2">
                        <span id="status-icon" class="text-xl">📡</span>
                        <span id="status-title" class="font-semibold text-gray-900 dark:text-gray-100">Checking connection...</span>
                    </div>
                    <button onclick="window.offlineStatusIndicator.hide()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Status Message -->
                <div id="status-message" class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                    Memeriksa status koneksi...
                </div>

                <!-- Queue Info (shown when has pending) -->
                <div id="queue-info" class="hidden bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 mb-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-blue-800 dark:text-blue-200">Pending Sync:</span>
                        <span id="pending-count" class="font-bold text-blue-900 dark:text-blue-100">0</span>
                    </div>
                    <div class="mt-2 w-full bg-blue-200 dark:bg-blue-800 rounded-full h-2">
                        <div id="sync-progress" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-2">
                    <button id="sync-btn" onclick="window.offlineStatusIndicator.triggerSync()" 
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors hidden">
                        🔄 Sync Now
                    </button>
                    <button onclick="window.offlineStatusIndicator.showDetails()" 
                            class="flex-1 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        📊 Details
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(this.element);
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Online/Offline events
        window.addEventListener('online', () => this.handleOnline());
        window.addEventListener('offline', () => this.handleOffline());

        // Subscribe to queue events
        this.queueManager.subscribe((event) => {
            this.handleQueueEvent(event);
        });

        // Periodic status update
        setInterval(() => this.updateStatus(), 30000); // Every 30 seconds
    }

    /**
     * Handle online event
     */
    async handleOnline() {
        this.show({
            icon: '✅',
            title: 'Connection Restored',
            message: 'Koneksi internet kembali. Sinkronisasi data...',
            type: 'success',
        });

        // Auto-trigger sync
        setTimeout(() => {
            this.triggerSync();
        }, 1000);
    }

    /**
     * Handle offline event
     */
    handleOffline() {
        this.show({
            icon: '⚠️',
            title: 'Offline Mode',
            message: 'Tidak ada koneksi internet. Data akan disinkronkan saat online.',
            type: 'warning',
        });
    }

    /**
     * Handle queue events
     * @param {Object} event - Event data
     */
    async handleQueueEvent(event) {
        switch (event.type) {
            case 'SYNC_START':
                this.show({
                    icon: '🔄',
                    title: 'Syncing...',
                    message: 'Menyinkronkan data dengan server...',
                    type: 'info',
                    showProgress: true,
                });
                break;

            case 'SYNC_COMPLETE':
                if (event.failed > 0) {
                    this.show({
                        icon: '⚠️',
                        title: 'Sync Partial',
                        message: `${event.synced} berhasil, ${event.failed} gagal`,
                        type: 'warning',
                    });
                } else {
                    this.show({
                        icon: '✅',
                        title: 'Sync Complete',
                        message: `${event.synced} data berhasil disinkronkan`,
                        type: 'success',
                    });
                }

                setTimeout(() => this.hide(), this.hideDelay);
                break;

            case 'SYNC_ERROR':
                this.show({
                    icon: '❌',
                    title: 'Sync Failed',
                    message: event.error || 'Terjadi kesalahan saat sinkronisasi',
                    type: 'error',
                });
                break;

            case 'QUEUED':
                await this.updateStatus();
                break;
        }
    }

    /**
     * Update status display
     */
    async updateStatus() {
        const isOnline = navigator.onLine;
        const queueLength = await this.queueManager.getQueueLength();
        const posPendingCount = this.posManager ? await this.posManager.getPendingTransactionCount() : 0;
        const totalPending = queueLength + posPendingCount;

        // Update status icon and title
        const iconEl = document.getElementById('status-icon');
        const titleEl = document.getElementById('status-title');
        const messageEl = document.getElementById('status-message');
        const queueInfoEl = document.getElementById('queue-info');
        const pendingCountEl = document.getElementById('pending-count');
        const syncBtnEl = document.getElementById('sync-btn');

        if (!isOnline) {
            iconEl.textContent = '⚠️';
            titleEl.textContent = 'Offline Mode';
            messageEl.textContent = 'Tidak ada koneksi internet';
        } else if (totalPending > 0) {
            iconEl.textContent = '🔄';
            titleEl.textContent = 'Pending Sync';
            messageEl.textContent = `${totalPending} item menunggu sinkronisasi`;

            queueInfoEl.classList.remove('hidden');
            pendingCountEl.textContent = totalPending;
            syncBtnEl.classList.remove('hidden');

            // Show progress bar animation
            const progressEl = document.getElementById('sync-progress');
            progressEl.style.width = '100%';
            setTimeout(() => {
                progressEl.style.width = '0%';
            }, 1000);
        } else {
            iconEl.textContent = '✅';
            titleEl.textContent = 'Online';
            messageEl.textContent = 'Semua data tersinkronisasi';

            queueInfoEl.classList.add('hidden');
            syncBtnEl.classList.add('hidden');
        }

        // Auto-show if there are pending items
        if (totalPending > 0 && !this.visible) {
            this.show({
                icon: iconEl.textContent,
                title: titleEl.textContent,
                message: messageEl.textContent,
                type: 'info',
            });
        }
    }

    /**
     * Show indicator
     * @param {Object} options - Display options
     */
    show(options = {}) {
        const iconEl = document.getElementById('status-icon');
        const titleEl = document.getElementById('status-title');
        const messageEl = document.getElementById('status-message');

        if (options.icon) iconEl.textContent = options.icon;
        if (options.title) titleEl.textContent = options.title;
        if (options.message) messageEl.textContent = options.message;

        // Show with animation
        this.element.classList.remove('translate-y-full', 'opacity-0');
        this.element.classList.add('translate-y-0', 'opacity-100');
        this.visible = true;

        // Auto-hide after delay
        if (this.autoHide && options.type === 'success') {
            setTimeout(() => this.hide(), this.hideDelay);
        }
    }

    /**
     * Hide indicator
     */
    hide() {
        this.element.classList.add('translate-y-full', 'opacity-0');
        this.element.classList.remove('translate-y-0', 'opacity-100');
        this.visible = false;
    }

    /**
     * Trigger manual sync
     */
    async triggerSync() {
        this.show({
            icon: '🔄',
            title: 'Manual Sync',
            message: 'Memulai sinkronisasi...',
            type: 'info',
        });

        try {
            const result = await this.queueManager.sync();

            if (this.posManager) {
                // POS will sync automatically via queue
            }
        } catch (error) {
            console.error('[OfflineStatus] Sync error:', error);
        }
    }

    /**
     * Show detailed statistics
     */
    async showDetails() {
        const stats = await this.queueManager.getStats();
        const posStats = this.posManager ? await this.posManager.getStats() : null;

        const detailsHtml = `
            <div class="space-y-4">
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Queue Statistics</h4>
                    <dl class="grid grid-cols-2 gap-2 text-sm">
                        <dt class="text-gray-600 dark:text-gray-400">Total:</dt>
                        <dd class="font-medium">${stats.total}</dd>
                        <dt class="text-gray-600 dark:text-gray-400">Pending:</dt>
                        <dd class="font-medium text-blue-600">${stats.pending}</dd>
                        <dt class="text-gray-600 dark:text-gray-400">Failed:</dt>
                        <dd class="font-medium text-red-600">${stats.failed}</dd>
                    </dl>
                </div>
                
                ${posStats ? `
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">POS Transactions</h4>
                    <dl class="grid grid-cols-2 gap-2 text-sm">
                        <dt class="text-gray-600 dark:text-gray-400">Total:</dt>
                        <dd class="font-medium">${posStats.total}</dd>
                        <dt class="text-gray-600 dark:text-gray-400">Pending:</dt>
                        <dd class="font-medium text-orange-600">${posStats.pending}</dd>
                        <dt class="text-gray-600 dark:text-gray-400">Synced:</dt>
                        <dd class="font-medium text-green-600">${posStats.synced}</dd>
                    </dl>
                </div>
                ` : ''}
                
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">By Module</h4>
                    <ul class="space-y-1 text-sm">
                        ${Object.entries(stats.byModule).map(([module, counts]) => `
                            <li class="flex justify-between">
                                <span class="capitalize">${module}</span>
                                <span class="font-medium">${counts.pending || 0} pending</span>
                            </li>
                        `).join('')}
                    </ul>
                </div>
            </div>
        `;

        // Create modal or use existing notification system
        alert(detailsHtml.replace(/<[^>]*>/g, '\n')); // Simple fallback
    }
}

// Export as global singleton
window.OfflineStatusIndicator = OfflineStatusIndicator;

// Auto-initialize when DOM ready
document.addEventListener('DOMContentLoaded', () => {
    window.offlineStatusIndicator = new OfflineStatusIndicator({
        position: 'bottom-right',
        enablePOS: true,
    });
});
