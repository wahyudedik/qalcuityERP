/**
 * Keyboard Shortcuts Manager
 * Centralized keyboard shortcuts system with context awareness
 * Features:
 * - Global shortcuts registry
 * - Context-aware shortcuts
 * - Help modal display
 * - Input field protection
 * - Customizable shortcuts
 */

export class KeyboardShortcuts {
    constructor() {
        this.shortcuts = new Map();
        this.enabled = true;
        this.sequenceBuffer = '';
        this.sequenceTimeout = null;
        this.modalVisible = false;

        this.init();
    }

    /**
     * Register a keyboard shortcut
     * @param {string} combination - Key combination (e.g., 'ctrl+k', 'g d')
     * @param {Function} handler - Callback function
     * @param {Object} options - Additional options
     */
    register(combination, handler, options = {}) {
        const key = combination.toLowerCase();
        this.shortcuts.set(key, {
            handler,
            description: options.description || '',
            category: options.category || 'general',
            keys: this.formatKeys(combination)
        });
    }

    /**
     * Register multiple shortcuts at once
     */
    registerMultiple(shortcutsMap) {
        Object.entries(shortcutsMap).forEach(([combo, config]) => {
            this.register(combo, config.handler, {
                description: config.description,
                category: config.category
            });
        });
    }

    /**
     * Unregister a shortcut
     */
    unregister(combination) {
        this.shortcuts.delete(combination.toLowerCase());
    }

    /**
     * Enable/disable shortcuts
     */
    setEnabled(enabled) {
        this.enabled = enabled;
    }

    /**
     * Show shortcuts help modal
     */
    showHelp() {
        if (this.modalVisible) {
            this.hideHelp();
            return;
        }

        const modal = this.createHelpModal();
        document.body.appendChild(modal);
        this.modalVisible = true;

        // Trap focus in modal
        this.trapFocus(modal);
    }

    /**
     * Hide shortcuts help modal
     */
    hideHelp() {
        const modal = document.getElementById('shortcuts-help-modal');
        if (modal) {
            modal.remove();
            this.modalVisible = false;
        }
    }

    /**
     * Initialize keyboard shortcuts
     */
    init() {
        document.addEventListener('keydown', (e) => {
            if (!this.enabled) return;

            // Don't trigger when typing in inputs (except ctrl/alt shortcuts)
            if (this.isInputFocused() && !e.ctrlKey && !e.altKey) return;

            // Check for sequence shortcuts (e.g., 'g d')
            if (this.isSequence(e)) {
                this.handleSequence(e);
                return;
            }

            // Check for direct shortcuts
            const combination = this.getCombination(e);
            const shortcut = this.shortcuts.get(combination);

            if (shortcut) {
                e.preventDefault();
                shortcut.handler(e);
            }
        });

        console.log('[KeyboardShortcuts] Initialized');
    }

    /**
     * Check if key combination is a sequence
     */
    isSequence(e) {
        return !e.ctrlKey && !e.altKey && !e.metaKey &&
            (e.key === 'g' || this.sequenceBuffer.length > 0);
    }

    /**
     * Handle sequence shortcuts (e.g., 'g d' for go to dashboard)
     */
    handleSequence(e) {
        if (this.sequenceTimeout) {
            clearTimeout(this.sequenceTimeout);
        }

        this.sequenceBuffer += (this.sequenceBuffer ? ' ' : '') + e.key.toLowerCase();

        this.sequenceTimeout = setTimeout(() => {
            this.sequenceBuffer = '';
        }, 1000);

        const shortcut = this.shortcuts.get(this.sequenceBuffer);
        if (shortcut) {
            e.preventDefault();
            shortcut.handler(e);
            this.sequenceBuffer = '';
        }
    }

    /**
     * Get key combination string from event
     */
    getCombination(e) {
        const parts = [];

        if (e.ctrlKey || e.metaKey) parts.push('ctrl');
        if (e.altKey) parts.push('alt');
        if (e.shiftKey) parts.push('shift');

        const key = e.key.toLowerCase();
        if (!['control', 'alt', 'shift', 'meta'].includes(key)) {
            parts.push(key);
        }

        return parts.join('+');
    }

    /**
     * Format keys for display
     */
    formatKeys(combination) {
        return combination.split(' ').map(part => {
            return part.split('+').map(key => {
                const keyMap = {
                    'ctrl': '⌘',
                    'alt': '⌥',
                    'shift': '⇧',
                    'escape': 'Esc',
                    'enter': '↵',
                    'backspace': '⌫',
                    'delete': 'Del',
                    'arrowup': '↑',
                    'arrowdown': '↓',
                    'arrowleft': '←',
                    'arrowright': '→'
                };
                return keyMap[key] || key.charAt(0).toUpperCase() + key.slice(1);
            }).join(' + ');
        }).join(', ');
    }

    /**
     * Check if input element is focused
     */
    isInputFocused() {
        const active = document.activeElement;
        return active && (
            active.tagName === 'INPUT' ||
            active.tagName === 'TEXTAREA' ||
            active.isContentEditable ||
            active.tagName === 'SELECT'
        );
    }

    /**
     * Create help modal
     */
    createHelpModal() {
        const modal = document.createElement('div');
        modal.id = 'shortcuts-help-modal';
        modal.className = 'fixed inset-0 z-[9999] flex items-start justify-center pt-20 px-4';

        // Group shortcuts by category
        const categories = {};
        this.shortcuts.forEach((shortcut, key) => {
            if (!categories[shortcut.category]) {
                categories[shortcut.category] = [];
            }
            categories[shortcut.category].push({
                keys: shortcut.keys,
                description: shortcut.description
            });
        });

        let shortcutsHTML = '';
        Object.entries(categories).forEach(([category, items]) => {
            shortcutsHTML += `
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-3">
                        ${category}
                    </h3>
                    <div class="space-y-2">
                        ${items.map(item => `
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700 dark:text-gray-300">${item.description}</span>
                                <div class="flex gap-2 ml-4">
                                    ${item.keys.split(', ').map(k => `
                                        <kbd class="px-2 py-1 text-xs font-mono bg-gray-100 dark:bg-gray-700 rounded border border-gray-300 dark:border-gray-600">
                                            ${k}
                                        </kbd>
                                    `).join('')}
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        });

        modal.innerHTML = `
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" id="shortcuts-backdrop"></div>
            
            <!-- Modal Content -->
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-2xl w-full max-h-[70vh] overflow-hidden" role="dialog" aria-modal="true" aria-labelledby="shortcuts-title">
                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 id="shortcuts-title" class="text-xl font-bold text-gray-900 dark:text-white">
                        <i class="fas fa-keyboard mr-2"></i>
                        Keyboard Shortcuts
                    </h2>
                    <button id="close-shortcuts" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors" aria-label="Close shortcuts help">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <!-- Content -->
                <div class="p-6 overflow-y-auto max-h-[calc(70vh-140px)]">
                    ${shortcutsHTML}
                </div>
                
                <!-- Footer -->
                <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        <i class="fas fa-info-circle mr-1"></i>
                        Press <kbd class="px-2 py-0.5 bg-gray-200 dark:bg-gray-700 rounded">Ctrl + /</kbd> to show/hide this help
                    </p>
                </div>
            </div>
        `;

        // Event listeners
        setTimeout(() => {
            document.getElementById('close-shortcuts')?.addEventListener('click', () => this.hideHelp());
            document.getElementById('shortcuts-backdrop')?.addEventListener('click', () => this.hideHelp());
        }, 0);

        return modal;
    }

    /**
     * Trap focus within modal
     */
    trapFocus(element) {
        const focusable = element.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        element.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.hideHelp();
                return;
            }

            if (e.key === 'Tab') {
                if (e.shiftKey && document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                } else if (!e.shiftKey && document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            }
        });

        // Focus first element
        first?.focus();
    }

    /**
     * Get all registered shortcuts
     */
    getAllShortcuts() {
        const result = {};
        this.shortcuts.forEach((shortcut, key) => {
            result[key] = shortcut;
        });
        return result;
    }
}

// Initialize shortcuts manager
let shortcutsManager = null;

export function initKeyboardShortcuts() {
    if (!shortcutsManager) {
        shortcutsManager = new KeyboardShortcuts();
    }
    return shortcutsManager;
}

// Register default shortcuts
export function registerDefaultShortcuts(manager) {
    const shortcuts = {
        // Navigation
        'g d': {
            handler: () => window.location.href = '/dashboard',
            description: 'Go to Dashboard',
            category: 'Navigation'
        },
        'g i': {
            handler: () => window.location.href = '/invoices',
            description: 'Go to Invoices',
            category: 'Navigation'
        },
        'g p': {
            handler: () => window.location.href = '/products',
            description: 'Go to Products',
            category: 'Navigation'
        },
        'g c': {
            handler: () => window.location.href = '/customers',
            description: 'Go to Customers',
            category: 'Navigation'
        },

        // Actions
        'ctrl+k': {
            handler: () => window.dispatchEvent(new CustomEvent('open-quick-search')),
            description: 'Quick Search',
            category: 'Actions'
        },
        'ctrl+/': {
            handler: () => manager.showHelp(),
            description: 'Show Shortcuts Help',
            category: 'Actions'
        },
        '?': {
            handler: () => manager.showHelp(),
            description: 'Show Shortcuts Help',
            category: 'Actions'
        },
        'escape': {
            handler: () => {
                // Close any open modals
                const modals = document.querySelectorAll('[x-show="true"], .modal.show');
                modals.forEach(modal => {
                    if (modal._x_dataStack) {
                        modal._x_dataStack[0] && (modal._x_dataStack[0].isOpen = false);
                    }
                });
            },
            description: 'Close Modal/Dialog',
            category: 'Actions'
        },
    };

    manager.registerMultiple(shortcuts);
}

// Auto-initialize
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        const manager = initKeyboardShortcuts();
        registerDefaultShortcuts(manager);
    });
} else {
    const manager = initKeyboardShortcuts();
    registerDefaultShortcuts(manager);
}

export default KeyboardShortcuts;
