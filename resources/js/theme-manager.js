/**
 * Theme Manager - Enhanced dark mode with system preference detection
 * Features:
 * - Light/Dark/System themes
 * - System preference detection (prefers-color-scheme)
 * - Smooth transition animations
 * - Visual feedback with icon animation
 * - Persistence with localStorage
 */

export class ThemeManager {
    constructor() {
        this.themes = ['light', 'dark', 'system'];
        this.currentTheme = localStorage.getItem('theme') || 'system';
        this.iconElement = null;
        this.init();
    }

    init() {
        // Apply theme immediately
        this.applyTheme(this.currentTheme);

        // Listen for system preference changes
        this.setupSystemPreferenceListener();

        // Setup UI toggle button
        this.setupToggleButton();
    }

    /**
     * Apply theme to document
     */
    applyTheme(theme) {
        if (!this.themes.includes(theme)) {
            theme = 'system';
        }

        this.currentTheme = theme;
        localStorage.setItem('theme', theme);

        // Determine if should be dark
        const isDark = this.shouldBeDark(theme);

        // Apply to HTML element
        const htmlRoot = document.getElementById('html-root') || document.documentElement;
        htmlRoot.classList.toggle('dark', isDark);

        // Update meta theme-color
        this.updateMetaThemeColor(isDark);

        // Update icon if exists
        this.updateIcon(isDark);

        // Dispatch custom event for other components
        window.dispatchEvent(new CustomEvent('theme-changed', {
            detail: { theme, isDark }
        }));

        console.log(`[ThemeManager] Applied theme: ${theme} (${isDark ? 'dark' : 'light'})`);
    }

    /**
     * Toggle between light and dark
     */
    toggle() {
        const isDark = this.getCurrentMode();
        this.applyTheme(isDark ? 'light' : 'dark');
    }

    /**
     * Cycle through all themes (light -> dark -> system)
     */
    cycle() {
        const currentIndex = this.themes.indexOf(this.currentTheme);
        const nextIndex = (currentIndex + 1) % this.themes.length;
        this.applyTheme(this.themes[nextIndex]);
    }

    /**
     * Get current visual mode (true = dark, false = light)
     */
    getCurrentMode() {
        const htmlRoot = document.getElementById('html-root') || document.documentElement;
        return htmlRoot.classList.contains('dark');
    }

    /**
     * Check if theme should render as dark
     */
    shouldBeDark(theme) {
        if (theme === 'dark') return true;
        if (theme === 'light') return false;
        if (theme === 'system') {
            return window.matchMedia('(prefers-color-scheme: dark)').matches;
        }
        return false;
    }

    /**
     * Update meta theme-color for mobile browsers
     */
    updateMetaThemeColor(isDark) {
        let metaThemeColor = document.querySelector('meta[name="theme-color"]');
        if (!metaThemeColor) {
            metaThemeColor = document.createElement('meta');
            metaThemeColor.name = 'theme-color';
            document.head.appendChild(metaThemeColor);
        }
        metaThemeColor.content = isDark ? '#0f172a' : '#ffffff';
    }

    /**
     * Update toggle button icon
     */
    updateIcon(isDark) {
        if (!this.iconElement) {
            this.iconElement = document.querySelector('#theme-toggle i') ||
                document.querySelector('#theme-toggle svg');
        }

        if (this.iconElement) {
            if (this.iconElement.tagName === 'I') {
                // Font Awesome icon
                this.iconElement.className = isDark ? 'fas fa-moon' : 'fas fa-sun';
            }
        }

        // Update button title
        const toggleBtn = document.getElementById('theme-toggle');
        if (toggleBtn) {
            const themeLabel = this.currentTheme === 'system' ? 'System' :
                (this.currentTheme === 'dark' ? 'Dark' : 'Light');
            toggleBtn.title = `Theme: ${themeLabel} (click to toggle)`;
        }
    }

    /**
     * Listen for system preference changes
     */
    setupSystemPreferenceListener() {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

        const handleChange = (e) => {
            if (this.currentTheme === 'system') {
                this.applyTheme('system');
            }
        };

        // Modern browsers
        if (mediaQuery.addEventListener) {
            mediaQuery.addEventListener('change', handleChange);
        } else if (mediaQuery.addListener) {
            // Deprecated but needed for older browsers
            mediaQuery.addListener(handleChange);
        }
    }

    /**
     * Setup toggle button event listener
     */
    setupToggleButton() {
        const toggleBtn = document.getElementById('theme-toggle');
        if (toggleBtn) {
            // Remove old event listeners by cloning
            const newBtn = toggleBtn.cloneNode(true);
            toggleBtn.parentNode.replaceChild(newBtn, toggleBtn);

            newBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.cycle();
            });
        }
    }

    /**
     * Get theme info for display
     */
    getThemeInfo() {
        const isDark = this.getCurrentMode();
        return {
            theme: this.currentTheme,
            isDark: isDark,
            label: this.currentTheme === 'system' ?
                `System (${isDark ? 'Dark' : 'Light'})` :
                (isDark ? 'Dark' : 'Light')
        };
    }
}

// Initialize theme manager when DOM is ready
let themeManager = null;

export function initThemeManager() {
    if (!themeManager) {
        themeManager = new ThemeManager();
    }
    return themeManager;
}

// Auto-initialize
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initThemeManager);
} else {
    initThemeManager();
}

export default ThemeManager;
