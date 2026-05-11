import './bootstrap';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import logger from './logger';
import errorBoundary from './error-boundary';
import moduleLoader from './module-loader';
import helpSystem from './help-system';
import './chart-theme'; // BUG-1.9 FIX: theme-changed listener for Chart.js instances
import numberFormat from './utils/number-format'; // TASK 6.9: Indonesian number formatting
import { registerLayoutStore } from './stores/layout-store'; // TASK 3.3: Layout state management
import './custom-dialog'; // Custom dialog system (replaces native alert/confirm/prompt)

// Register Alpine plugins
Alpine.plugin(collapse);

// FIX JS-005: Wrap custom plugin dalam try-catch agar Alpine tetap start jika plugin gagal
try {
    Alpine.plugin(errorBoundary);
} catch (e) {
    console.warn('[App] error-boundary plugin failed to load', e);
}

// TASK 6.9: Register number formatting utilities globally
window.NumberFormat = numberFormat;

// TASK 6.9: Register Alpine magic helpers for number formatting
// TASK 3.3: Register layout store for responsive state management
document.addEventListener('alpine:init', () => {
    numberFormat.registerAlpineMagics();
    registerLayoutStore(Alpine);
});

// FIX JS-001: Set window.Alpine SETELAH semua plugin terdaftar, sebelum Alpine.start()
window.Alpine = Alpine;
Alpine.start();

// ═══════════════════════════════════════════════════════════
// TASK-013: Lazy Loading Modules Implementation
// ═══════════════════════════════════════════════════════════

// Load CORE modules via moduleLoader (avoid duplicate imports)
logger.info('Loading core UI/UX modules...');

// Use moduleLoader to load core modules immediately
Promise.all([
    moduleLoader.load('shortcuts'),
    moduleLoader.load('search'),
    moduleLoader.load('accessibility'),
]).then(() => {
    logger.info('Core modules loaded successfully');
}).catch(err => {
    logger.error('Failed to load core modules', err);
});

// Load non-module scripts that don't need lazy loading
import './recently-used-tracker.js'; // TASK-016: Recently used menu tracking
import './form-wizard.js'; // TASK-018: Multi-step form wizard
import './navigation-progress.js'; // Navigation progress bar (replaces blur effect)

// ═══════════════════════════════════════════════════════════
// LAZY LOADED MODULES (loaded on demand)
// ═══════════════════════════════════════════════════════════

// Chat module - load only when chat container exists
document.addEventListener('DOMContentLoaded', () => {
    const chatContainer = document.querySelector('.chat-container');

    if (chatContainer) {
        moduleLoader.load('chat')
            .then(({ initChat }) => {
                window.chatManager = initChat();
                logger.info('Chat module loaded on demand');
            })
            .catch(err => {
                logger.error('Chat module failed to load', err);
            });
    }
});

// Industry-specific modules - auto-detect and preload
document.addEventListener('DOMContentLoaded', async () => {
    const industry = document.querySelector('meta[name="industry"]')?.content;

    if (industry) {
        logger.info(`Detected industry: ${industry}, preloading modules...`);

        // Show loading indicator
        moduleLoader.showLoadingIndicator(industry);

        try {
            await moduleLoader.preloadIndustry(industry);
            logger.info(`${industry} modules preloaded successfully`);
        } catch (error) {
            logger.error(`Failed to preload ${industry} modules`, error);
        } finally {
            moduleLoader.hideLoadingIndicator(industry);
        }
    }
});

// ═══════════════════════════════════════════════════════════
// BACKGROUND PRELOADING (after page load)
// ═══════════════════════════════════════════════════════════

// Preload offline manager and notifications in background
// FIX JS-002: Tambahkan fallback untuk browser yang tidak support requestIdleCallback (Safari < 16)
const scheduleIdleTask = window.requestIdleCallback
    ? (fn, opts) => window.requestIdleCallback(fn, opts)
    : (fn) => setTimeout(fn, 200);

scheduleIdleTask(async () => {
    try {
        await moduleLoader.preload(['offline', 'notifications'], {
            delay: 0,
            useIdleCallback: false, // Already in idle callback
        });
        logger.info('Background modules preloaded during idle time');
    } catch (error) {
        logger.warn('Background preload failed', error);
    }
}, { timeout: 5000 });

// ═══════════════════════════════════════════════════════════
// SERVICE WORKER (offline support)
// ═══════════════════════════════════════════════════════════

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                logger.info('Service Worker registered', { scope: registration.scope });
            })
            .catch(error => {
                // FIX JS-003: Graceful degradation — tampilkan banner jika SW gagal
                logger.error('Service Worker registration failed', error);
                // Tidak throw error — app tetap berjalan tanpa offline support
                if (document.readyState === 'complete') {
                    console.info('[App] Offline mode tidak tersedia di browser ini.');
                }
            });
    });
}

// ═══════════════════════════════════════════════════════════
// PERFORMANCE MONITORING
// ═══════════════════════════════════════════════════════════

// Log bundle size after page load
window.addEventListener('load', async () => {
    const bundleSize = await moduleLoader.getBundleSize();

    if (bundleSize) {
        const totalMB = (bundleSize.totalSize / 1024 / 1024).toFixed(2);
        logger.info(`Initial bundle size: ${totalMB} MB (${bundleSize.scriptCount} scripts)`);

        // Warn if bundle is too large
        if (bundleSize.totalSize > 2 * 1024 * 1024) { // 2MB
            logger.warn(`Bundle size exceeds 2MB: ${totalMB} MB`);
        }
    }

    // Log module loading performance
    const metrics = moduleLoader.getPerformanceMetrics();
    logger.info('Module loading metrics:', metrics);
});

logger.info('App initialized with advanced lazy loading');

// FIX JS-019: Tidak ekspos internal modules ke window untuk mencegah manipulasi via console
// window.moduleLoader dan window.logger dihapus dari global scope
// Gunakan import langsung jika perlu akses dari modul lain

