/**
 * Navigation Progress Bar
 * Menampilkan progress bar di top saat navigasi tanpa blur effect yang mengganggu
 */

class NavigationProgress {
    constructor() {
        this.bar = null;
        this.isNavigating = false;
        this.init();
    }

    init() {
        // Create progress bar element
        this.bar = document.createElement('div');
        this.bar.id = 'nav-progress-bar';
        this.bar.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 0%;
            height: 3px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6);
            z-index: 9999;
            transition: width 0.3s ease, opacity 0.3s ease;
            opacity: 0;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
        `;
        document.body.appendChild(this.bar);

        // Listen to link clicks
        this.attachListeners();
    }

    attachListeners() {
        // Intercept all internal link clicks
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');

            if (!link) return;

            const href = link.getAttribute('href');

            // Skip external links, anchors, and special hrefs
            if (!href ||
                href.startsWith('#') ||
                href.startsWith('javascript:') ||
                href.startsWith('mailto:') ||
                href.startsWith('tel:') ||
                link.target === '_blank' ||
                link.hasAttribute('download')) {
                return;
            }

            // Skip if same page
            if (href === window.location.pathname + window.location.search) {
                return;
            }

            // Start progress
            this.start();
        });

        // Handle browser back/forward
        window.addEventListener('popstate', () => {
            this.start();
        });

        // Stop progress when page loads
        window.addEventListener('load', () => {
            this.complete();
        });

        // Fallback: stop after 5 seconds
        window.addEventListener('beforeunload', () => {
            setTimeout(() => this.complete(), 5000);
        });
    }

    start() {
        if (this.isNavigating) return;

        this.isNavigating = true;
        this.bar.style.opacity = '1';
        this.bar.style.width = '0%';

        // Animate to 70% over 1 second
        setTimeout(() => {
            this.bar.style.width = '70%';
        }, 50);
    }

    complete() {
        if (!this.isNavigating) return;

        // Jump to 100%
        this.bar.style.width = '100%';

        // Fade out after 300ms
        setTimeout(() => {
            this.bar.style.opacity = '0';

            // Reset after fade
            setTimeout(() => {
                this.bar.style.width = '0%';
                this.isNavigating = false;
            }, 300);
        }, 300);
    }

    // Manual control methods
    setProgress(percent) {
        this.bar.style.opacity = '1';
        this.bar.style.width = `${Math.min(100, Math.max(0, percent))}%`;
    }

    reset() {
        this.bar.style.opacity = '0';
        this.bar.style.width = '0%';
        this.isNavigating = false;
    }
}

// Initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.navigationProgress = new NavigationProgress();
    });
} else {
    window.navigationProgress = new NavigationProgress();
}

export default NavigationProgress;
