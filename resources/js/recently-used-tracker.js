/**
 * RecentlyUsedTracker - Track and manage recently used menu items
 * 
 * TASK-016: Implements recently used menu tracking to improve navigation UX
 * by showing frequently accessed menu items at the top of the sidebar panel.
 * 
 * @version 1.0.0
 */

class RecentlyUsedTracker {
    constructor(maxItems = 10) {
        this.maxItems = maxItems;
        this.storageKey = 'recently_used_menu';
    }

    /**
     * Track menu item access
     * 
     * @param {string} group - Menu group identifier
     * @param {string} label - Menu item label
     * @param {string} route - Route name or URL
     */
    track(group, label, route) {
        try {
            const items = this.getRecentlyUsed();

            // Remove if already exists (to re-add at top)
            const filtered = items.filter(item => item.route !== route);

            // Add to beginning
            const newItem = {
                group,
                label,
                route,
                accessedAt: new Date().toISOString(),
                count: this._getAccessCount(route, items) + 1
            };

            filtered.unshift(newItem);

            // Keep only maxItems
            const trimmed = filtered.slice(0, this.maxItems);

            localStorage.setItem(this.storageKey, JSON.stringify(trimmed));
        } catch (e) {
            console.warn('RecentlyUsedTracker: Failed to track', e);
        }
    }

    /**
     * Get recently used items
     * 
     * @returns {Array} Recently used menu items
     */
    getRecentlyUsed() {
        try {
            const data = localStorage.getItem(this.storageKey);
            return data ? JSON.parse(data) : [];
        } catch (e) {
            console.warn('RecentlyUsedTracker: Failed to get items', e);
            return [];
        }
    }

    /**
     * Get access count for a route
     * 
     * @param {string} route - Route identifier
     * @param {Array} items - Items array to search
     * @returns {number} Access count
     */
    _getAccessCount(route, items) {
        const item = items.find(i => i.route === route);
        return item ? item.count : 0;
    }

    /**
     * Clear recently used items
     */
    clear() {
        localStorage.removeItem(this.storageKey);
    }

    /**
     * Check if recently used section should be shown
     * 
     * @returns {boolean} Show recently used
     */
    shouldShow() {
        return this.getRecentlyUsed().length > 0;
    }
}

// Create singleton
const recentlyUsed = new RecentlyUsedTracker();

// Make globally available
window.recentlyUsed = recentlyUsed;

// Auto-track menu clicks
document.addEventListener('click', (e) => {
    const menuLink = e.target.closest('a.panel-link, a[href]');
    if (menuLink) {
        const group = menuLink.dataset.group || '';
        const label = menuLink.textContent.trim();
        const route = menuLink.getAttribute('href');

        if (group && label && route) {
            recentlyUsed.track(group, label, route);
        }
    }
});

export default recentlyUsed;
export { RecentlyUsedTracker };
