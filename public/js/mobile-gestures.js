/**
 * Mobile Touch Gestures & Interactions
 * Native-like gestures for Qalcuity ERP PWA
 */

class MobileGestures {
    constructor() {
        this.touchStartX = 0;
        this.touchStartY = 0;
        this.touchEndX = 0;
        this.touchEndY = 0;
        this.swipeThreshold = 50;

        this.init();
    }

    init() {
        this.setupPullToRefresh();
        this.setupSwipeGestures();
        this.setupTouchFeedback();
        this.setupLongPress();
        this.setupDoubleTap();
    }

    /**
     * Pull-to-refresh implementation
     */
    setupPullToRefresh() {
        let pullStartY = 0;
        let isPulling = false;
        const pullIndicator = document.getElementById('pull-indicator');

        if (!pullIndicator) return;

        document.addEventListener('touchstart', (e) => {
            if (window.scrollY === 0) {
                pullStartY = e.touches[0].clientY;
                isPulling = true;
            }
        }, { passive: true });

        document.addEventListener('touchmove', (e) => {
            if (!isPulling) return;

            const pullDistance = e.touches[0].clientY - pullStartY;

            if (pullDistance > 0 && pullDistance < 150) {
                pullIndicator.style.opacity = pullDistance / 150;
                pullIndicator.style.transform = `translateY(${pullDistance}px)`;
            }
        }, { passive: true });

        document.addEventListener('touchend', (e) => {
            if (!isPulling) return;

            const pullDistance = e.changedTouches[0].clientY - pullStartY;
            isPulling = false;

            if (pullDistance > 100) {
                // Trigger refresh
                pullIndicator.classList.add('refreshing');
                this.triggerRefresh();
            } else {
                pullIndicator.style.opacity = '0';
                pullIndicator.style.transform = 'translateY(0)';
            }
        });
    }

    /**
     * Swipe gestures (left/right/up/down)
     */
    setupSwipeGestures() {
        document.addEventListener('touchstart', (e) => {
            this.touchStartX = e.changedTouches[0].screenX;
            this.touchStartY = e.changedTouches[0].screenY;
        }, { passive: true });

        document.addEventListener('touchend', (e) => {
            this.touchEndX = e.changedTouches[0].screenX;
            this.touchEndY = e.changedTouches[0].screenY;

            this.handleSwipe();
        }, { passive: true });
    }

    handleSwipe() {
        const deltaX = this.touchEndX - this.touchStartX;
        const deltaY = this.touchEndY - this.touchStartY;

        const absDeltaX = Math.abs(deltaX);
        const absDeltaY = Math.abs(deltaY);

        // Determine swipe direction
        if (absDeltaX > this.swipeThreshold || absDeltaY > this.swipeThreshold) {
            if (absDeltaX > absDeltaY) {
                // Horizontal swipe
                if (deltaX > 0) {
                    this.onSwipeRight();
                } else {
                    this.onSwipeLeft();
                }
            } else {
                // Vertical swipe
                if (deltaY > 0) {
                    this.onSwipeDown();
                } else {
                    this.onSwipeUp();
                }
            }
        }
    }

    onSwipeLeft() {
        // Default: navigate back or close modal
        const modal = document.querySelector('.modal.open');
        if (modal) {
            modal.classList.remove('open');
        }
    }

    onSwipeRight() {
        // Default: open sidebar or go back
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.classList.toggle('open');
        }
    }

    onSwipeUp() {
        // Scroll to top or load more
        if (window.scrollY > 500) {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    onSwipeDown() {
        // Refresh or show notifications
        if (window.scrollY === 0) {
            this.triggerRefresh();
        }
    }

    /**
     * Touch feedback (ripple effect)
     */
    setupTouchFeedback() {
        document.addEventListener('touchstart', (e) => {
            const target = e.target.closest('button, a, .touch-target');
            if (!target) return;

            target.classList.add('touch-active');

            setTimeout(() => {
                target.classList.remove('touch-active');
            }, 150);
        }, { passive: true });
    }

    /**
     * Long press detection
     */
    setupLongPress() {
        let longPressTimer;
        const longPressDuration = 800; // ms

        document.addEventListener('touchstart', (e) => {
            const target = e.target.closest('[data-long-press]');
            if (!target) return;

            longPressTimer = setTimeout(() => {
                target.dispatchEvent(new CustomEvent('longpress', {
                    bubbles: true,
                    detail: { element: target }
                }));

                // Haptic feedback if available
                if (navigator.vibrate) {
                    navigator.vibrate(50);
                }
            }, longPressDuration);
        }, { passive: true });

        document.addEventListener('touchend', () => {
            clearTimeout(longPressTimer);
        });

        document.addEventListener('touchmove', () => {
            clearTimeout(longPressTimer);
        });
    }

    /**
     * Double tap detection
     */
    setupDoubleTap() {
        let lastTap = 0;
        const doubleTapDelay = 300; // ms

        document.addEventListener('touchend', (e) => {
            const currentTime = new Date().getTime();
            const tapLength = currentTime - lastTap;

            if (tapLength < doubleTapDelay && tapLength > 0) {
                // Double tap detected
                const target = e.target.closest('[data-double-tap]');
                if (target) {
                    target.dispatchEvent(new CustomEvent('doubletap', {
                        bubbles: true,
                        detail: { element: target }
                    }));
                }
            }

            lastTap = currentTime;
        });
    }

    /**
     * Trigger page refresh
     */
    async triggerRefresh() {
        const pullIndicator = document.getElementById('pull-indicator');

        try {
            // Show loading state
            if (pullIndicator) {
                pullIndicator.classList.add('refreshing');
            }

            // Reload current page data via AJAX or full reload
            if (window.location.pathname.includes('/dashboard')) {
                await this.refreshDashboard();
            } else {
                window.location.reload();
            }
        } catch (error) {
            console.error('Refresh failed:', error);
        } finally {
            if (pullIndicator) {
                pullIndicator.classList.remove('refreshing');
                pullIndicator.style.opacity = '0';
                pullIndicator.style.transform = 'translateY(0)';
            }
        }
    }

    async refreshDashboard() {
        // Fetch fresh dashboard data
        const response = await fetch('/dashboard/data', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (response.ok) {
            const data = await response.json();
            this.updateDashboardData(data);
        }
    }

    updateDashboardData(data) {
        // Update dashboard widgets with new data
        Object.keys(data).forEach(key => {
            const element = document.getElementById(`widget-${key}`);
            if (element) {
                element.textContent = data[key];
            }
        });
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    if ('ontouchstart' in window || navigator.maxTouchPoints > 0) {
        window.mobileGestures = new MobileGestures();
    }
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MobileGestures;
}
