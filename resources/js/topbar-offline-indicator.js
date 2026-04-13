/**
 * TopbarOfflineIndicator
 * 
 * TASK 1.6: Offline indicator di topbar application
 * - Shows online/offline status
 * - Shows pending sync count
 * - Quick sync button with progress
 * - Conflict notification badge
 */

import logger from './logger';

class TopbarOfflineIndicator {
    constructor(options = {}) {
        this.containerSelector = options.containerSelector || '#topbar-offline-indicator';
        this.queueManager = window.offlineQueueManager || null;
        this.syncInProgress = false;
        this.pendingCount = 0;
        this.conflictCount = 0;

        this.init();
    }

    /**
     * Initialize indicator
     */
    async init() {
        this.render();
        this.setupEventListeners();
        await this.updateStatus();

        logger.debug('[TopbarOfflineIndicator] Initialized');
    }

    /**
     * Render indicator HTML
     */
    render() {
        const container = document.querySelector(this.containerSelector);
        if (!container) {
            logger.warn('[TopbarOfflineIndicator] Container not found', { selector: this.containerSelector });
            return;
        }

        container.innerHTML = `
            <div id="topbar-offline-widget" class="flex items-center space-x-3">
                <!-- Connection Status -->
                <div id="connection-status" class="flex items-center space-x-2 px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 transition-all duration-300">
                    <div id="status-dot" class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                    <span id="status-text" class="text-sm font-medium text-gray-700 dark:text-gray-300">Online</span>
                </div>

                <!-- Pending Sync Badge -->
                <button id="pending-sync-btn" onclick="window.topbarIndicator.showSyncPanel()" 
                        class="hidden relative flex items-center space-x-2 px-3 py-1.5 rounded-lg bg-blue-100 dark:bg-blue-900/30 hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-all duration-300">
                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span id="pending-count" class="text-sm font-semibold text-blue-700 dark:text-blue-300">0</span>
                    <span class="text-xs text-blue-600 dark:text-blue-400">pending</span>
                </button>

                <!-- Conflict Badge -->
                <button id="conflict-btn" onclick="window.topbarIndicator.showConflicts()" 
                        class="hidden relative flex items-center space-x-2 px-3 py-1.5 rounded-lg bg-orange-100 dark:bg-orange-900/30 hover:bg-orange-200 dark:hover:bg-orange-900/50 transition-all duration-300">
                    <svg class="w-4 h-4 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span id="conflict-count" class="text-sm font-semibold text-orange-700 dark:text-orange-300">0</span>
                    <span class="text-xs text-orange-600 dark:text-orange-400">conflicts</span>
                </button>

                <!-- Quick Sync Button -->
                <button id="quick-sync-btn" onclick="window.topbarIndicator.quickSync()" 
                        class="hidden items-center space-x-2 px-4 py-1.5 rounded-lg bg-green-600 hover:bg-green-700 text-white font-medium text-sm transition-all duration-300 shadow-sm hover:shadow-md">
                    <svg id="sync-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span id="sync-text">Sync Now</span>
                </button>

                <!-- Progress Bar (shown during sync) -->
                <div id="sync-progress-container" class="hidden w-32">
                    <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400 mb-1">
                        <span id="progress-text">Syncing...</span>
                        <span id="progress-percent">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                        <div id="sync-progress-bar" class="bg-gradient-to-r from-blue-500 to-green-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Online/Offline events
        window.addEventListener('online', () => {
            this.handleOnline();
        });

        window.addEventListener('offline', () => {
            this.handleOffline();
        });

        // Subscribe to queue events
        if (this.queueManager) {
            this.queueManager.subscribe((event) => {
                this.handleQueueEvent(event);
            });
        }

        // Periodic status update
        setInterval(() => {
            this.updateStatus();
        }, 15000); // Every 15 seconds
    }

    /**
     * Handle online event
     */
    handleOnline() {
        const statusDot = document.getElementById('status-dot');
        const statusText = document.getElementById('status-text');
        const connectionStatus = document.getElementById('connection-status');

        statusDot.className = 'w-2 h-2 rounded-full bg-green-500 animate-pulse';
        statusText.textContent = 'Online';
        connectionStatus.className = 'flex items-center space-x-2 px-3 py-1.5 rounded-lg bg-green-100 dark:bg-green-900/30 transition-all duration-300';

        this.showNotification('Connection restored', 'success');
    }

    /**
     * Handle offline event
     */
    handleOffline() {
        const statusDot = document.getElementById('status-dot');
        const statusText = document.getElementById('status-text');
        const connectionStatus = document.getElementById('connection-status');

        statusDot.className = 'w-2 h-2 rounded-full bg-red-500';
        statusText.textContent = 'Offline';
        connectionStatus.className = 'flex items-center space-x-2 px-3 py-1.5 rounded-lg bg-red-100 dark:bg-red-900/30 transition-all duration-300';

        this.showNotification('You are offline. Changes will be queued.', 'warning');
    }

    /**
     * Handle queue events
     */
    handleQueueEvent(event) {
        switch (event.type) {
            case 'QUEUED':
            case 'SYNC_COMPLETE':
            case 'CONFLICT_DETECTED':
                this.updateStatus();
                break;

            case 'SYNC_START':
                this.showSyncProgress(0);
                break;
        }
    }

    /**
     * Update status display
     */
    async updateStatus() {
        const isOnline = navigator.onLine;

        // Update connection status
        const statusDot = document.getElementById('status-dot');
        const statusText = document.getElementById('status-text');
        const connectionStatus = document.getElementById('connection-status');

        if (!isOnline) {
            statusDot.className = 'w-2 h-2 rounded-full bg-red-500';
            statusText.textContent = 'Offline';
            connectionStatus.className = 'flex items-center space-x-2 px-3 py-1.5 rounded-lg bg-red-100 dark:bg-red-900/30 transition-all duration-300';
        } else {
            statusDot.className = 'w-2 h-2 rounded-full bg-green-500 animate-pulse';
            statusText.textContent = 'Online';
            connectionStatus.className = 'flex items-center space-x-2 px-3 py-1.5 rounded-lg bg-green-100 dark:bg-green-900/30 transition-all duration-300';
        }

        // Update pending count
        if (this.queueManager) {
            this.pendingCount = await this.queueManager.getQueueLength();
            const pendingBtn = document.getElementById('pending-sync-btn');
            const pendingCountEl = document.getElementById('pending-count');

            if (this.pendingCount > 0) {
                pendingBtn.classList.remove('hidden');
                pendingBtn.classList.add('flex');
                pendingCountEl.textContent = this.pendingCount;

                // Show quick sync button
                const quickSyncBtn = document.getElementById('quick-sync-btn');
                quickSyncBtn.classList.remove('hidden');
                quickSyncBtn.classList.add('flex');
            } else {
                pendingBtn.classList.add('hidden');
                pendingBtn.classList.remove('flex');

                const quickSyncBtn = document.getElementById('quick-sync-btn');
                quickSyncBtn.classList.add('hidden');
                quickSyncBtn.classList.remove('flex');
            }
        }

        // Update conflict count (from API)
        await this.updateConflictCount();
    }

    /**
     * Update conflict count from API
     */
    async updateConflictCount() {
        // Hanya fetch jika online — tidak perlu cek konflik saat offline
        if (!navigator.onLine) return;

        try {
            const response = await fetch('/api/offline/conflicts', {
                credentials: 'same-origin', // Include cookies for session auth
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            });

            // Jika 401/403, user belum login atau session expired — skip silently
            if (response.status === 401 || response.status === 403) {
                return;
            }

            if (response.ok) {
                const data = await response.json();
                this.conflictCount = data.statistics?.pending_conflicts || 0;

                const conflictBtn = document.getElementById('conflict-btn');
                const conflictCountEl = document.getElementById('conflict-count');

                if (this.conflictCount > 0) {
                    conflictBtn.classList.remove('hidden');
                    conflictBtn.classList.add('flex');
                    conflictCountEl.textContent = this.conflictCount;
                } else {
                    conflictBtn.classList.add('hidden');
                    conflictBtn.classList.remove('flex');
                }
            }
        } catch (error) {
            logger.error('[TopbarOfflineIndicator] Failed to update conflict count', error);
        }
    }

    /**
     * Show sync progress
     */
    showSyncProgress(percent) {
        const progressContainer = document.getElementById('sync-progress-container');
        const progressBar = document.getElementById('sync-progress-bar');
        const progressText = document.getElementById('progress-text');
        const progressPercent = document.getElementById('progress-percent');
        const quickSyncBtn = document.getElementById('quick-sync-btn');

        progressContainer.classList.remove('hidden');
        quickSyncBtn.classList.add('hidden');

        progressBar.style.width = `${percent}%`;
        progressPercent.textContent = `${Math.round(percent)}%`;
        progressText.textContent = percent < 100 ? 'Syncing...' : 'Complete!';
    }

    /**
     * Hide sync progress
     */
    hideSyncProgress() {
        const progressContainer = document.getElementById('sync-progress-container');
        const quickSyncBtn = document.getElementById('quick-sync-btn');

        setTimeout(() => {
            progressContainer.classList.add('hidden');
            quickSyncBtn.classList.remove('hidden');
        }, 1000);
    }

    /**
     * TASK 1.5: Quick sync with progress indicator
     */
    async quickSync() {
        if (this.syncInProgress || !this.queueManager) {
            return;
        }

        this.syncInProgress = true;

        // Update UI
        const syncIcon = document.getElementById('sync-icon');
        const syncText = document.getElementById('sync-text');
        syncIcon.classList.add('animate-spin');
        syncText.textContent = 'Syncing...';

        this.showSyncProgress(0);

        try {
            // Simulate progress (since we don't have real-time progress from backend)
            const totalItems = this.pendingCount;
            let syncedItems = 0;

            const progressInterval = setInterval(() => {
                if (syncedItems < totalItems) {
                    const progress = ((syncedItems + 0.5) / totalItems) * 100;
                    this.showSyncProgress(Math.min(progress, 95));
                }
            }, 500);

            // Perform sync
            const result = await this.queueManager.sync();

            clearInterval(progressInterval);
            syncedItems = result.synced || 0;

            // Complete
            this.showSyncProgress(100);

            if (result.failed > 0) {
                this.showNotification(`Sync complete: ${result.synced} succeeded, ${result.failed} failed`, 'warning');
            } else {
                this.showNotification(`Sync complete: ${result.synced} items synchronized`, 'success');
            }

            // Update status
            await this.updateStatus();

        } catch (error) {
            logger.error('[TopbarOfflineIndicator] Sync failed', error);
            this.showNotification('Sync failed. Please try again.', 'error');
        } finally {
            this.syncInProgress = false;

            // Reset UI
            syncIcon.classList.remove('animate-spin');
            syncText.textContent = 'Sync Now';

            this.hideSyncProgress();
        }
    }

    /**
     * Show sync panel (dropdown or modal)
     */
    showSyncPanel() {
        if (window.offlineStatusIndicator) {
            window.offlineStatusIndicator.show();
        }
    }

    /**
     * Show conflicts panel
     */
    showConflicts() {
        if (window.conflictResolutionUI) {
            window.conflictResolutionUI.show();
        }
    }

    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        if (window.showToast) {
            window.showToast(message, type);
        } else if (window.Alpine?.store?.notifications) {
            window.Alpine.store('notifications').add({
                message,
                type,
            });
        } else {
            // Fallback to simple alert
            logger.debug(`[Notification] ${type}: ${message}`);
        }
    }
}

// Export as global singleton
window.TopbarOfflineIndicator = TopbarOfflineIndicator;

// Auto-initialize when DOM ready
document.addEventListener('DOMContentLoaded', () => {
    window.topbarIndicator = new TopbarOfflineIndicator({
        containerSelector: '#topbar-offline-indicator',
    });
});
