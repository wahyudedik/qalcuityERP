/**
 * ConflictResolutionUI
 * 
 * TASK 1.2 & 1.3: UI Component untuk menampilkan dan resolve conflicts
 * - Menampilkan daftar conflicts
 * - Field-level comparison (diff viewer)
 * - Auto-resolve strategies (last-write-wins, role-priority)
 * - Manual resolution interface
 */

import logger from './logger';

class ConflictResolutionUI {
    constructor(options = {}) {
        this.apiBaseUrl = options.apiBaseUrl || '/api/offline';
        this.visible = false;
        this.conflicts = [];
        this.statistics = {};
        this.currentConflict = null;
        this.autoResolveStrategy = options.defaultStrategy || 'merge';

        this.init();
    }

    /**
     * Initialize component
     */
    async init() {
        this.createModalElement();
        this.setupEventListeners();

        logger.debug('[ConflictResolution] UI initialized');
    }

    /**
     * Create modal DOM element
     */
    createModalElement() {
        this.modal = document.createElement('div');
        this.modal.id = 'conflict-resolution-modal';
        this.modal.className = 'fixed inset-0 z-[100] hidden overflow-y-auto';
        this.modal.innerHTML = `
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="window.conflictResolutionUI.hide()"></div>
            
            <!-- Modal Container -->
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-5xl w-full max-h-[90vh] overflow-hidden">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-orange-500 to-red-500 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="bg-white/20 rounded-lg p-2">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-white">Sync Conflicts</h2>
                                    <p class="text-sm text-white/80">Resolve data conflicts from offline sync</p>
                                </div>
                            </div>
                            <button onclick="window.conflictResolutionUI.hide()" class="text-white/80 hover:text-white transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Statistics Bar -->
                    <div id="conflict-stats" class="bg-gray-50 dark:bg-gray-700 px-6 py-3 border-b border-gray-200 dark:border-gray-600">
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center space-x-4">
                                <span class="text-gray-600 dark:text-gray-300">Total: <strong id="stat-total" class="text-gray-900 dark:text-white">0</strong></span>
                                <span class="text-orange-600 dark:text-orange-400">Pending: <strong id="stat-pending" class="text-orange-700 dark:text-orange-300">0</strong></span>
                                <span class="text-green-600 dark:text-green-400">Resolved: <strong id="stat-resolved" class="text-green-700 dark:text-green-300">0</strong></span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <label class="text-gray-600 dark:text-gray-300 text-xs">Auto-resolve:</label>
                                <select id="auto-resolve-strategy" onchange="window.conflictResolutionUI.updateAutoStrategy(this.value)" 
                                        class="text-xs border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    <option value="merge">Smart Merge</option>
                                    <option value="local_wins">Local Wins</option>
                                    <option value="server_wins">Server Wins</option>
                                    <option value="role_priority">Role Priority</option>
                                    <option value="last_write">Last Write Wins</option>
                                </select>
                                <button onclick="window.conflictResolutionUI.autoResolveAll()" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1 rounded transition-colors">
                                    Auto-Resolve All
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Content Area -->
                    <div class="overflow-y-auto" style="max-height: calc(90vh - 200px);">
                        <!-- Conflict List -->
                        <div id="conflict-list" class="divide-y divide-gray-200 dark:divide-gray-700">
                            <!-- Conflicts will be rendered here -->
                        </div>

                        <!-- Conflict Detail View -->
                        <div id="conflict-detail" class="hidden p-6">
                            <!-- Detail view will be rendered here -->
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 border-t border-gray-200 dark:border-gray-600">
                        <div class="flex items-center justify-between">
                            <button onclick="window.conflictResolutionUI.loadConflicts()" 
                                    class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white text-sm flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                <span>Refresh</span>
                            </button>
                            <button onclick="window.conflictResolutionUI.hide()" 
                                    class="bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-800 dark:text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(this.modal);
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Listen for conflict events from offline manager
        if (window.offlineQueueManager) {
            window.offlineQueueManager.subscribe((event) => {
                if (event.type === 'CONFLICT_DETECTED' || event.type === 'CONFLICT_WARNING') {
                    this.loadConflicts();
                }
            });
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.visible) {
                this.hide();
            }
        });
    }

    /**
     * Load conflicts from server
     */
    async loadConflicts() {
        try {
            const response = await fetch(`${this.apiBaseUrl}/conflicts`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            });

            if (response.ok) {
                const data = await response.json();
                this.conflicts = data.conflicts || [];
                this.statistics = data.statistics || {};
                this.renderConflictList();
                this.updateStatistics();
            }
        } catch (error) {
            logger.error('[ConflictResolution] Failed to load conflicts', error);
        }
    }

    /**
     * Update statistics display
     */
    updateStatistics() {
        document.getElementById('stat-total').textContent = this.statistics.total_conflicts || 0;
        document.getElementById('stat-pending').textContent = this.statistics.pending_conflicts || 0;
        document.getElementById('stat-resolved').textContent = this.statistics.resolved_conflicts || 0;
    }

    /**
     * Render conflict list
     */
    renderConflictList() {
        const listEl = document.getElementById('conflict-list');
        const detailEl = document.getElementById('conflict-detail');

        listEl.classList.remove('hidden');
        detailEl.classList.add('hidden');

        if (this.conflicts.length === 0) {
            listEl.innerHTML = `
                <div class="p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Conflicts</h3>
                    <p class="text-gray-500 dark:text-gray-400">All data is synchronized successfully</p>
                </div>
            `;
            return;
        }

        listEl.innerHTML = this.conflicts.map(conflict => `
            <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer" 
                 onclick="window.conflictResolutionUI.showConflictDetail(${conflict.id})">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="text-xs font-medium px-2 py-1 rounded bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-200 uppercase">
                                ${conflict.entity_type}
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                ID: ${conflict.entity_id}
                            </span>
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-300 mb-1">
                            Detected: ${new Date(conflict.detected_at).toLocaleString()}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            Strategy: <span class="font-medium text-blue-600 dark:text-blue-400">${conflict.status}</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </div>
        `).join('');
    }

    /**
     * Show conflict detail view
     */
    async showConflictDetail(conflictId) {
        const conflict = this.conflicts.find(c => c.id === conflictId);
        if (!conflict) return;

        this.currentConflict = conflict;

        const listEl = document.getElementById('conflict-list');
        const detailEl = document.getElementById('conflict-detail');

        listEl.classList.add('hidden');
        detailEl.classList.remove('hidden');

        // Render field-level diff
        detailEl.innerHTML = this.renderConflictDetail(conflict);
    }

    /**
     * Render conflict detail with field-level comparison
     */
    renderConflictDetail(conflict) {
        const serverState = conflict.server_state || {};
        const localState = conflict.local_state || {};

        // Get all unique fields
        const allFields = [...new Set([...Object.keys(serverState), ...Object.keys(localState)])];

        return `
            <div class="space-y-6">
                <!-- Header -->
                <div class="flex items-center justify-between">
                    <button onclick="window.conflictResolutionUI.renderConflictList()" class="text-blue-600 hover:text-blue-700 text-sm flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        <span>Back to list</span>
                    </button>
                    <span class="text-xs font-medium px-3 py-1 rounded bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-200 uppercase">
                        ${conflict.entity_type}
                    </span>
                </div>

                <!-- Info -->
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600 dark:text-gray-400">Entity ID:</span>
                            <span class="ml-2 font-medium text-gray-900 dark:text-white">${conflict.entity_id}</span>
                        </div>
                        <div>
                            <span class="text-gray-600 dark:text-gray-400">Detected:</span>
                            <span class="ml-2 font-medium text-gray-900 dark:text-white">${new Date(conflict.detected_at).toLocaleString()}</span>
                        </div>
                    </div>
                </div>

                <!-- Field Comparison -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Field Comparison</h3>
                    <div class="space-y-3">
                        ${allFields.map(field => {
            const serverValue = serverState[field];
            const localValue = localState[field];
            const hasDiff = JSON.stringify(serverValue) !== JSON.stringify(localValue);

            return `
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden ${hasDiff ? 'border-l-4 border-l-orange-500' : ''}">
                                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-2 font-medium text-sm text-gray-700 dark:text-gray-300">
                                        ${field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                        ${hasDiff ? '<span class="ml-2 text-orange-600 text-xs">⚠️ CHANGED</span>' : '<span class="ml-2 text-green-600 text-xs">✓ SAME</span>'}
                                    </div>
                                    <div class="grid grid-cols-2 divide-x divide-gray-200 dark:divide-gray-700">
                                        <div class="p-4">
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Server Value</div>
                                            <div class="text-sm font-mono ${hasDiff ? 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 p-2 rounded' : 'text-gray-900 dark:text-white'}">
                                                ${serverValue !== null && serverValue !== undefined ? JSON.stringify(serverValue) : '<span class="text-gray-400 italic">null</span>'}
                                            </div>
                                        </div>
                                        <div class="p-4">
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Local Value (Offline)</div>
                                            <div class="text-sm font-mono ${hasDiff ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 p-2 rounded' : 'text-gray-900 dark:text-white'}">
                                                ${localValue !== null && localValue !== undefined ? JSON.stringify(localValue) : '<span class="text-gray-400 italic">null</span>'}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
        }).join('')}
                    </div>
                </div>

                <!-- Resolution Actions -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Resolve Conflict</h3>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <button onclick="window.conflictResolutionUI.resolveConflict(${conflict.id}, 'local_wins')" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg text-sm font-medium transition-colors">
                            <div class="font-semibold">Use Local Value</div>
                            <div class="text-xs text-blue-100 mt-1">Keep offline changes</div>
                        </button>
                        
                        <button onclick="window.conflictResolutionUI.resolveConflict(${conflict.id}, 'server_wins')" 
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-lg text-sm font-medium transition-colors">
                            <div class="font-semibold">Use Server Value</div>
                            <div class="text-xs text-red-100 mt-1">Discard offline changes</div>
                        </button>
                        
                        <button onclick="window.conflictResolutionUI.resolveConflict(${conflict.id}, 'merge')" 
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg text-sm font-medium transition-colors">
                            <div class="font-semibold">Smart Merge</div>
                            <div class="text-xs text-green-100 mt-1">Combine both changes</div>
                        </button>
                        
                        <button onclick="window.conflictResolutionUI.resolveConflict(${conflict.id}, 'skip')" 
                                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-3 rounded-lg text-sm font-medium transition-colors">
                            <div class="font-semibold">Skip</div>
                            <div class="text-xs text-gray-100 mt-1">Ignore this conflict</div>
                        </button>
                    </div>

                    <!-- Auto-resolve with role priority -->
                    <div class="pt-4 border-t border-gray-200 dark:border-gray-600">
                        <button onclick="window.conflictResolutionUI.resolveWithRolePriority(${conflict.id})" 
                                class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-3 rounded-lg text-sm font-medium transition-colors">
                            <div class="font-semibold">🎯 Auto-Resolve by Role Priority</div>
                            <div class="text-xs text-purple-100 mt-1">Manager > Supervisor > Staff (auto-detect)</div>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Resolve single conflict
     */
    async resolveConflict(conflictId, strategy) {
        try {
            const response = await fetch(`${this.apiBaseUrl}/conflicts/${conflictId}/resolve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({ strategy }),
            });

            if (response.ok) {
                const result = await response.json();

                // Show success notification
                this.showNotification(`Conflict resolved: ${strategy}`, 'success');

                // Reload conflicts
                await this.loadConflicts();
            } else {
                throw new Error('Failed to resolve conflict');
            }
        } catch (error) {
            logger.error('[ConflictResolution] Resolution failed', error);
            this.showNotification('Failed to resolve conflict', 'error');
        }
    }

    /**
     * TASK 1.3: Resolve with role-based priority
     */
    async resolveWithRolePriority(conflictId) {
        // Get user role and apply priority
        const userRole = document.querySelector('meta[name="user-role"]')?.content || 'staff';

        const rolePriority = {
            'super_admin': 5,
            'admin': 4,
            'manager': 3,
            'supervisor': 2,
            'staff': 1
        };

        const currentPriority = rolePriority[userRole] || 1;

        // If current user has high priority, use local wins
        if (currentPriority >= 3) {
            await this.resolveConflict(conflictId, 'local_wins');
        } else {
            // Low priority user, ask server
            await this.resolveConflict(conflictId, 'server_wins');
        }
    }

    /**
     * Auto-resolve all pending conflicts
     */
    async autoResolveAll() {
        if (!confirm('Auto-resolve all pending conflicts with current strategy?')) {
            return;
        }

        try {
            const response = await fetch(`${this.apiBaseUrl}/conflicts/auto-resolve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            });

            if (response.ok) {
                const result = await response.json();
                this.showNotification(`Auto-resolved: ${result.result.resolved} conflicts`, 'success');
                await this.loadConflicts();
            }
        } catch (error) {
            logger.error('[ConflictResolution] Auto-resolve failed', error);
            this.showNotification('Auto-resolve failed', 'error');
        }
    }

    /**
     * Update auto-resolve strategy
     */
    updateAutoStrategy(strategy) {
        this.autoResolveStrategy = strategy;
        logger.debug(`[ConflictResolution] Auto-strategy updated: ${strategy}`);
    }

    /**
     * Show modal
     */
    async show() {
        this.modal.classList.remove('hidden');
        this.visible = true;
        await this.loadConflicts();
    }

    /**
     * Hide modal
     */
    hide() {
        this.modal.classList.add('hidden');
        this.visible = false;
        this.currentConflict = null;
    }

    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        // Use existing notification system or create simple toast
        if (window.showToast) {
            window.showToast(message, type);
        } else {
            alert(message);
        }
    }
}

// Export as global singleton
window.ConflictResolutionUI = ConflictResolutionUI;

// Auto-initialize
document.addEventListener('DOMContentLoaded', () => {
    window.conflictResolutionUI = new ConflictResolutionUI({
        apiBaseUrl: '/api/offline',
        defaultStrategy: 'merge',
    });
});
