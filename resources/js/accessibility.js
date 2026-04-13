/**
 * Accessibility (a11y) Module - WCAG 2.1 Compliance
 * Features:
 * - Screen reader announcements
 * - Focus management for modals
 * - Skip to content link
 * - Reduced motion support
 * - Keyboard navigation helpers
 * - ARIA live regions
 */

import logger from './logger';

export class AccessibilityManager {
    constructor() {
        this.announcementsElement = null;
        this.focusTrapElements = new Map();
        this.init();
    }

    init() {
        this.createAnnouncementsRegion();
        this.setupSkipLink();
        this.respectReducedMotion();
        this.setupKeyboardNavigation();

        logger.debug('[AccessibilityManager] Initialized - WCAG 2.1 AA');
    }

    /**
     * Create ARIA live region for screen reader announcements
     */
    createAnnouncementsRegion() {
        if (document.getElementById('a11y-announcements')) return;

        const region = document.createElement('div');
        region.id = 'a11y-announcements';
        region.setAttribute('aria-live', 'polite');
        region.setAttribute('aria-atomic', 'true');
        region.className = 'sr-only';
        document.body.appendChild(region);
        this.announcementsElement = region;
    }

    /**
     * Announce message to screen readers
     */
    announce(message, priority = 'polite') {
        if (!this.announcementsElement) {
            this.createAnnouncementsRegion();
        }

        // Clear previous announcement
        this.announcementsElement.textContent = '';

        // Set priority
        this.announcementsElement.setAttribute('aria-live', priority);

        // Announce message (with slight delay for screen readers to pick up)
        setTimeout(() => {
            this.announcementsElement.textContent = message;
        }, 100);

        logger.debug(`[a11y] Announced (${priority}): ${message}`);
    }

    /**
     * Setup skip to content link
     */
    setupSkipLink() {
        if (document.getElementById('skip-to-content')) return;

        const skipLink = document.createElement('a');
        skipLink.id = 'skip-to-content';
        skipLink.href = '#main-content';
        skipLink.className = 'skip-link sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[9999] focus:px-4 focus:py-2 focus:bg-blue-600 focus:text-white focus:rounded focus:shadow-lg';
        skipLink.textContent = 'Skip to main content';
        document.body.insertBefore(skipLink, document.body.firstChild);
    }

    /**
     * Respect user's reduced motion preference
     */
    respectReducedMotion() {
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

        const applyMotionPreference = (e) => {
            if (e.matches) {
                document.documentElement.classList.add('reduce-motion');
                // Disable all CSS transitions and animations
                document.documentElement.style.setProperty('--motion-reduced', 'true');
            } else {
                document.documentElement.classList.remove('reduce-motion');
                document.documentElement.style.setProperty('--motion-reduced', 'false');
            }
        };

        // Apply on load
        applyMotionPreference(prefersReducedMotion);

        // Listen for changes
        if (prefersReducedMotion.addEventListener) {
            prefersReducedMotion.addEventListener('change', applyMotionPreference);
        }
    }

    /**
     * Trap focus within element (for modals, dialogs)
     */
    trapFocus(element, options = {}) {
        const focusableSelectors = [
            'button:not([disabled])',
            '[href]',
            'input:not([disabled])',
            'select:not([disabled])',
            'textarea:not([disabled])',
            '[tabindex]:not([tabindex="-1"])',
            'audio[controls]',
            'video[controls]',
            '[contenteditable]:not([contenteditable="false"])'
        ].join(', ');

        const focusableElements = Array.from(element.querySelectorAll(focusableSelectors))
            .filter(el => el.offsetParent !== null); // Only visible elements

        if (focusableElements.length === 0) return;

        const firstFocusable = focusableElements[0];
        const lastFocusable = focusableElements[focusableElements.length - 1];

        const handleKeydown = (e) => {
            if (e.key !== 'Tab') return;

            if (e.shiftKey) {
                // Shift + Tab
                if (document.activeElement === firstFocusable) {
                    e.preventDefault();
                    lastFocusable.focus();
                    this.announce('Wrapped to last focusable element');
                }
            } else {
                // Tab
                if (document.activeElement === lastFocusable) {
                    e.preventDefault();
                    firstFocusable.focus();
                    this.announce('Wrapped to first focusable element');
                }
            }
        };

        element.addEventListener('keydown', handleKeydown);

        // Store trap info for later removal
        this.focusTrapElements.set(element, {
            handler: handleKeydown,
            firstFocusable,
            lastFocusable
        });

        // Focus first element
        if (options.autoFocus !== false) {
            firstFocusable.focus();
        }

        // Announce modal open
        const title = element.querySelector('[id*="title"], h1, h2, h3');
        if (title) {
            this.announce(`${title.textContent} dialog opened`, 'assertive');
        }

        return () => this.removeFocusTrap(element);
    }

    /**
     * Remove focus trap
     */
    removeFocusTrap(element) {
        const trap = this.focusTrapElements.get(element);
        if (trap) {
            element.removeEventListener('keydown', trap.handler);
            this.focusTrapElements.delete(element);
        }
    }

    /**
     * Setup keyboard navigation for common patterns
     */
    setupKeyboardNavigation() {
        // Arrow key navigation for menu items
        document.addEventListener('keydown', (e) => {
            const target = e.target;

            // Handle arrow keys in menus/lists
            if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
                const menu = target.closest('[role="menu"], [role="listbox"], .nav-menu, .dropdown-menu');
                if (menu) {
                    this.handleArrowNavigation(e, menu);
                }
            }
        });
    }

    /**
     * Handle arrow key navigation in menus
     */
    handleArrowNavigation(e, menu) {
        const items = Array.from(menu.querySelectorAll(
            '[role="menuitem"], [role="option"], a, button, .menu-item'
        )).filter(el => !el.disabled && el.offsetParent !== null);

        if (items.length === 0) return;

        const currentIndex = items.indexOf(document.activeElement);
        if (currentIndex === -1) return;

        let nextIndex = currentIndex;

        if (e.key === 'ArrowDown' || e.key === 'ArrowRight') {
            nextIndex = (currentIndex + 1) % items.length;
        } else if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') {
            nextIndex = (currentIndex - 1 + items.length) % items.length;
        }

        if (nextIndex !== currentIndex) {
            e.preventDefault();
            items[nextIndex].focus();
        }
    }

    /**
     * Make element accessible (add ARIA attributes)
     */
    makeAccessible(element, options = {}) {
        const {
            role,
            label,
            labelledBy,
            describedBy,
            expanded,
            controls,
            hidden
        } = options;

        if (role) element.setAttribute('role', role);
        if (label) element.setAttribute('aria-label', label);
        if (labelledBy) element.setAttribute('aria-labelledby', labelledBy);
        if (describedBy) element.setAttribute('aria-describedby', describedBy);
        if (expanded !== undefined) element.setAttribute('aria-expanded', expanded);
        if (controls) element.setAttribute('aria-controls', controls);
        if (hidden !== undefined) element.setAttribute('aria-hidden', hidden);
    }

    /**
     * Toggle expanded state
     */
    toggleExpanded(button, expanded) {
        button.setAttribute('aria-expanded', expanded.toString());
        const controlsId = button.getAttribute('aria-controls');
        if (controlsId) {
            const controlled = document.getElementById(controlsId);
            if (controlled) {
                controlled.setAttribute('aria-hidden', (!expanded).toString());
            }
        }
    }

    /**
     * Check if element has sufficient color contrast
     * Returns true if contrast ratio >= 4.5:1 (AA standard)
     */
    hasSufficientContrast(element) {
        const styles = window.getComputedStyle(element);
        const fgColor = this.parseColor(styles.color);
        const bgColor = this.parseBackgroundColor(element);

        if (!fgColor || !bgColor) return true; // Can't determine, assume OK

        const ratio = this.getContrastRatio(fgColor, bgColor);
        return ratio >= 4.5; // AA standard for normal text
    }

    /**
     * Parse color string to RGB
     */
    parseColor(colorStr) {
        const match = colorStr.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
        if (!match) return null;
        return {
            r: parseInt(match[1]),
            g: parseInt(match[2]),
            b: parseInt(match[3])
        };
    }

    /**
     * Parse background color (simplified)
     */
    parseBackgroundColor(element) {
        let current = element;
        while (current) {
            const bg = window.getComputedStyle(current).backgroundColor;
            if (bg !== 'rgba(0, 0, 0, 0)' && bg !== 'transparent') {
                return this.parseColor(bg);
            }
            current = current.parentElement;
        }
        return { r: 255, g: 255, b: 255 }; // Default white
    }

    /**
     * Calculate contrast ratio
     */
    getContrastRatio(color1, color2) {
        const l1 = this.getRelativeLuminance(color1);
        const l2 = this.getRelativeLuminance(color2);

        const lighter = Math.max(l1, l2);
        const darker = Math.min(l1, l2);

        return (lighter + 0.05) / (darker + 0.05);
    }

    /**
     * Get relative luminance
     */
    getRelativeLuminance(color) {
        const [r, g, b] = [color.r, color.g, color.b].map(c => {
            c = c / 255;
            return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
        });

        return 0.2126 * r + 0.7152 * g + 0.0722 * b;
    }

    /**
     * Add visible focus indicator to element
     */
    addFocusIndicator(element) {
        element.classList.add('focus:outline-none', 'focus:ring-2', 'focus:ring-blue-500', 'focus:ring-offset-2');
    }

    /**
     * Get accessibility report
     */
    getAccessibilityReport() {
        const report = {
            hasSkipLink: !!document.getElementById('skip-to-content'),
            hasAnnouncementsRegion: !!this.announcementsElement,
            reducedMotionEnabled: document.documentElement.classList.contains('reduce-motion'),
            activeFocusTraps: this.focusTrapElements.size,
            imagesWithoutAlt: document.querySelectorAll('img:not([alt])').length,
            buttonsWithoutLabel: 0,
            formsWithoutLabels: 0
        };

        // Count buttons without accessible names
        document.querySelectorAll('button').forEach(btn => {
            if (!btn.textContent.trim() && !btn.getAttribute('aria-label')) {
                report.buttonsWithoutLabel++;
            }
        });

        // Count form inputs without labels
        document.querySelectorAll('input, select, textarea').forEach(input => {
            const id = input.id;
            const hasLabel = id && document.querySelector(`label[for="${id}"]`);
            const hasAriaLabel = input.getAttribute('aria-label') || input.getAttribute('aria-labelledby');

            if (!hasLabel && !hasAriaLabel && input.type !== 'hidden') {
                report.formsWithoutLabels++;
            }
        });

        return report;
    }
}

// Initialize accessibility manager
let a11yManager = null;

export function initAccessibility() {
    if (!a11yManager) {
        a11yManager = new AccessibilityManager();
        window.announce = (msg) => a11yManager.announce(msg);
        window.a11y = a11yManager;
    }
    return a11yManager;
}

// Auto-initialize
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAccessibility);
} else {
    initAccessibility();
}

export default AccessibilityManager;
