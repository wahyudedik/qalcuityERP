/**
 * Alpine.js Layout Store
 *
 * Mengelola state management untuk responsive layout system:
 * - Breakpoint detection dengan debounced resize handling
 * - Sidebar visibility dan collapse state
 * - Widget state management (loading, error, success per widget)
 * - Layout persistence ke localStorage
 *
 * @see Requirements 6 (Responsive Layout Universal)
 * @see Requirements 7 (Sistem Widget Management)
 * @see Requirements 8 (Performance dan Loading Optimization)
 * @see Design Document: Responsive System - Alpine.js Store
 */

import { debounce } from '../debounce';

const STORAGE_KEY = 'qalcuity_layout_preferences';
const WIDGET_STATE_KEY = 'qalcuity_widget_states';

/**
 * Register the layout store with Alpine.js.
 * Should be called during alpine:init event.
 *
 * @param {object} Alpine - Alpine.js instance
 */
export function registerLayoutStore(Alpine) {
    Alpine.store('layout', {
        // ─── Breakpoint State ────────────────────────────────────
        breakpoint: 'desktop',

        // ─── Sidebar State ───────────────────────────────────────
        sidebarVisible: true,
        sidebarCollapsed: false,

        // ─── Widget States ───────────────────────────────────────
        // Map of widgetId -> { status: 'idle'|'loading'|'error'|'success', errorMessage: null }
        widgets: {},

        // ─── Internal ────────────────────────────────────────────
        _resizeHandler: null,
        _initialized: false,

        /**
         * Initialize the layout store.
         * Restores persisted state and sets up resize listener.
         */
        init() {
            if (this._initialized) return;
            this._initialized = true;

            // Restore persisted preferences
            this._restoreFromStorage();

            // Set initial breakpoint
            this.updateBreakpoint();

            // Debounced resize handler (150ms) for performance
            this._resizeHandler = debounce(() => {
                this.updateBreakpoint();
            }, 150);

            window.addEventListener('resize', this._resizeHandler);
        },

        // ─── Breakpoint Detection ────────────────────────────────

        /**
         * Update the current breakpoint based on window width.
         * Breakpoints follow Tailwind CSS conventions:
         * - mobile: < 768px
         * - tablet: 768px - 1023px
         * - desktop: >= 1024px
         */
        updateBreakpoint() {
            const width = window.innerWidth;
            let newBreakpoint;

            if (width < 768) {
                newBreakpoint = 'mobile';
            } else if (width < 1024) {
                newBreakpoint = 'tablet';
            } else {
                newBreakpoint = 'desktop';
            }

            if (this.breakpoint !== newBreakpoint) {
                const previousBreakpoint = this.breakpoint;
                this.breakpoint = newBreakpoint;
                this._onBreakpointChange(previousBreakpoint, newBreakpoint);
            }
        },

        /**
         * Check if current breakpoint matches the given value.
         * @param {string} bp - Breakpoint to check ('mobile', 'tablet', 'desktop')
         * @returns {boolean}
         */
        is(bp) {
            return this.breakpoint === bp;
        },

        /**
         * Check if current breakpoint is at or above the given value.
         * @param {string} bp - Minimum breakpoint ('mobile', 'tablet', 'desktop')
         * @returns {boolean}
         */
        isAtLeast(bp) {
            const order = { mobile: 0, tablet: 1, desktop: 2 };
            return (order[this.breakpoint] || 0) >= (order[bp] || 0);
        },

        // ─── Sidebar Management ──────────────────────────────────

        /**
         * Toggle sidebar visibility (show/hide).
         */
        toggleSidebar() {
            this.sidebarVisible = !this.sidebarVisible;
            this._persistToStorage();
        },

        /**
         * Show the sidebar.
         */
        showSidebar() {
            this.sidebarVisible = true;
            this._persistToStorage();
        },

        /**
         * Hide the sidebar.
         */
        hideSidebar() {
            this.sidebarVisible = false;
            this._persistToStorage();
        },

        /**
         * Toggle sidebar collapsed state (full width vs compact).
         */
        toggleSidebarCollapse() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            this._persistToStorage();
        },

        /**
         * Collapse the sidebar to compact mode.
         */
        collapseSidebar() {
            this.sidebarCollapsed = true;
            this._persistToStorage();
        },

        /**
         * Expand the sidebar to full width.
         */
        expandSidebar() {
            this.sidebarCollapsed = false;
            this._persistToStorage();
        },

        // ─── Widget State Management ─────────────────────────────

        /**
         * Register a widget for state tracking.
         * @param {string} widgetId - Unique widget identifier
         */
        registerWidget(widgetId) {
            if (!this.widgets[widgetId]) {
                this.widgets[widgetId] = {
                    status: 'idle',
                    errorMessage: null,
                    lastUpdated: null,
                };
            }
        },

        /**
         * Set widget to loading state.
         * @param {string} widgetId - Widget identifier
         */
        setWidgetLoading(widgetId) {
            this._ensureWidget(widgetId);
            this.widgets[widgetId].status = 'loading';
            this.widgets[widgetId].errorMessage = null;
        },

        /**
         * Set widget to success state.
         * @param {string} widgetId - Widget identifier
         */
        setWidgetSuccess(widgetId) {
            this._ensureWidget(widgetId);
            this.widgets[widgetId].status = 'success';
            this.widgets[widgetId].errorMessage = null;
            this.widgets[widgetId].lastUpdated = Date.now();
        },

        /**
         * Set widget to error state.
         * @param {string} widgetId - Widget identifier
         * @param {string|null} message - Error message
         */
        setWidgetError(widgetId, message = null) {
            this._ensureWidget(widgetId);
            this.widgets[widgetId].status = 'error';
            this.widgets[widgetId].errorMessage = message || 'Widget gagal dimuat';
        },

        /**
         * Reset widget to idle state.
         * @param {string} widgetId - Widget identifier
         */
        resetWidget(widgetId) {
            this._ensureWidget(widgetId);
            this.widgets[widgetId].status = 'idle';
            this.widgets[widgetId].errorMessage = null;
        },

        /**
         * Get the current state of a widget.
         * @param {string} widgetId - Widget identifier
         * @returns {{ status: string, errorMessage: string|null, lastUpdated: number|null }}
         */
        getWidgetState(widgetId) {
            return this.widgets[widgetId] || { status: 'idle', errorMessage: null, lastUpdated: null };
        },

        /**
         * Check if a widget is currently loading.
         * @param {string} widgetId - Widget identifier
         * @returns {boolean}
         */
        isWidgetLoading(widgetId) {
            return this.widgets[widgetId]?.status === 'loading';
        },

        /**
         * Check if a widget has an error.
         * @param {string} widgetId - Widget identifier
         * @returns {boolean}
         */
        isWidgetError(widgetId) {
            return this.widgets[widgetId]?.status === 'error';
        },

        /**
         * Remove a widget from state tracking.
         * @param {string} widgetId - Widget identifier
         */
        unregisterWidget(widgetId) {
            delete this.widgets[widgetId];
        },

        // ─── Persistence (localStorage) ─────────────────────────

        /**
         * Persist layout preferences to localStorage.
         * @private
         */
        _persistToStorage() {
            try {
                const data = {
                    sidebarVisible: this.sidebarVisible,
                    sidebarCollapsed: this.sidebarCollapsed,
                    timestamp: Date.now(),
                };
                localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
            } catch (e) {
                // localStorage may be unavailable (private browsing, quota exceeded)
                console.warn('[LayoutStore] Failed to persist preferences:', e.message);
            }
        },

        /**
         * Restore layout preferences from localStorage.
         * @private
         */
        _restoreFromStorage() {
            try {
                const raw = localStorage.getItem(STORAGE_KEY);
                if (!raw) return;

                const data = JSON.parse(raw);

                // Only restore if data is less than 30 days old
                const maxAge = 30 * 24 * 60 * 60 * 1000;
                if (data.timestamp && (Date.now() - data.timestamp) > maxAge) {
                    localStorage.removeItem(STORAGE_KEY);
                    return;
                }

                if (typeof data.sidebarVisible === 'boolean') {
                    this.sidebarVisible = data.sidebarVisible;
                }
                if (typeof data.sidebarCollapsed === 'boolean') {
                    this.sidebarCollapsed = data.sidebarCollapsed;
                }
            } catch (e) {
                console.warn('[LayoutStore] Failed to restore preferences:', e.message);
            }
        },

        // ─── Internal Helpers ────────────────────────────────────

        /**
         * Handle breakpoint changes with automatic sidebar adjustments.
         * @param {string} previous - Previous breakpoint
         * @param {string} current - New breakpoint
         * @private
         */
        _onBreakpointChange(previous, current) {
            // Auto-hide sidebar on mobile for better space utilization
            if (current === 'mobile') {
                this.sidebarVisible = false;
            } else if (previous === 'mobile' && current !== 'mobile') {
                // Restore sidebar when leaving mobile
                this._restoreSidebarForBreakpoint();
            }

            // Auto-collapse sidebar on tablet
            if (current === 'tablet') {
                this.sidebarCollapsed = true;
            } else if (current === 'desktop' && previous === 'tablet') {
                this.sidebarCollapsed = false;
            }
        },

        /**
         * Restore sidebar state from storage when transitioning from mobile.
         * @private
         */
        _restoreSidebarForBreakpoint() {
            try {
                const raw = localStorage.getItem(STORAGE_KEY);
                if (raw) {
                    const data = JSON.parse(raw);
                    if (typeof data.sidebarVisible === 'boolean') {
                        this.sidebarVisible = data.sidebarVisible;
                    } else {
                        this.sidebarVisible = true;
                    }
                } else {
                    this.sidebarVisible = true;
                }
            } catch (e) {
                this.sidebarVisible = true;
            }
        },

        /**
         * Ensure a widget entry exists in the widgets map.
         * @param {string} widgetId
         * @private
         */
        _ensureWidget(widgetId) {
            if (!this.widgets[widgetId]) {
                this.widgets[widgetId] = {
                    status: 'idle',
                    errorMessage: null,
                    lastUpdated: null,
                };
            }
        },
    });
}

export default registerLayoutStore;
