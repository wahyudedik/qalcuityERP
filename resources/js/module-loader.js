/**
 * ModuleLoader - Advanced Lazy Loading for ERP Modules
 * 
 * TASK-013: Implements dynamic module loading with:
 * - On-demand module loading
 * - Preloading strategies
 * - Loading indicators
 * - Error handling & retry logic
 * - Performance monitoring
 * - Bundle size tracking
 * 
 * @version 2.0.0
 * @author QalcuityERP Team
 */

import logger from './logger';

class ModuleLoader {
    constructor() {
        this.loadedModules = new Set();
        this.loadingModules = new Map();
        this.failedModules = new Map();
        this.maxRetries = 3;
        this.performanceMetrics = {};

        // Module definitions with import functions
        this.modules = {
            // Core modules (load immediately)
            theme: () => import('./theme-manager.js'),
            shortcuts: () => import('./keyboard-shortcuts.js'),
            search: () => import('./quick-search.js'),
            accessibility: () => import('./accessibility.js'),

            // Feature modules (load on demand)
            chat: () => import('./chunks/chat.js'),
            offline: () => import('./offline-manager.js'),
            notifications: () => import('./push-notification.js'),

            // Industry-specific modules (lazy load)
            manufacturing: () => import('./modules/manufacturing.js'),
            healthcare: () => import('./modules/healthcare.js'),
            hotel: () => import('./modules/hotel.js'),
            construction: () => import('./modules/construction.js'),
            agriculture: () => import('./modules/agriculture.js'),
            fisheries: () => import('./fisheries-service.js'),

            // Utility modules (preload in background)
            pos: () => import('./offline-pos.js'),
            printer: () => import('./pos-printer.js'),
            virtualScroll: () => import('./virtual-scroll.js'),
            lazyLoad: () => import('./lazy-loader.js'),
            conflictResolution: () => import('./conflict-resolution.js'),
        };

        // Module dependencies
        this.dependencies = {
            manufacturing: ['virtualScroll'],
            healthcare: ['chat'],
            hotel: ['pos', 'printer'],
            construction: [],
            agriculture: [],
            fisheries: [],
        };

        // Module priorities (1 = highest)
        this.priorities = {
            theme: 1,
            shortcuts: 1,
            search: 1,
            accessibility: 1,
            chat: 2,
            offline: 2,
            notifications: 2,
            pos: 3,
            printer: 3,
            manufacturing: 3,
            healthcare: 3,
            hotel: 3,
            construction: 3,
            agriculture: 3,
            fisheries: 3,
        };
    }

    /**
     * Load a module dynamically
     * 
     * @param {string} moduleName - Name of module to load
     * @param {Object} options - Loading options
     * @returns {Promise} Module exports
     */
    async load(moduleName, options = {}) {
        const {
            priority = false,
            preload = false,
            retry = true,
            timeout = 10000,
            onLoad = null,
            onError = null,
        } = options;

        // Check if already loaded
        if (this.loadedModules.has(moduleName)) {
            logger.debug(`Module already loaded: ${moduleName}`);
            return this.getLoadedModule(moduleName);
        }

        // Check if currently loading
        if (this.loadingModules.has(moduleName)) {
            logger.debug(`Module already loading: ${moduleName}`);
            return this.loadingModules.get(moduleName);
        }

        // Check module exists
        if (!this.modules[moduleName]) {
            const error = new Error(`Module not found: ${moduleName}`);
            logger.error(error.message);
            if (onError) onError(error);
            throw error;
        }

        // Load dependencies first
        await this.loadDependencies(moduleName);

        // Create loading promise
        const loadPromise = this.loadModule(moduleName, {
            retry,
            timeout,
            onLoad,
            onError,
        });

        this.loadingModules.set(moduleName, loadPromise);

        try {
            const module = await loadPromise;
            this.loadedModules.add(moduleName);
            this.loadingModules.delete(moduleName);

            logger.info(`Module loaded successfully: ${moduleName}`);
            this.trackPerformance(moduleName, 'load', true);

            return module;
        } catch (error) {
            this.loadingModules.delete(moduleName);
            this.failedModules.set(moduleName, {
                error,
                timestamp: Date.now(),
                retries: options.retryCount || 0,
            });

            logger.error(`Module failed to load: ${moduleName}`, error);
            this.trackPerformance(moduleName, 'load', false);

            if (onError) onError(error);
            throw error;
        }
    }

    /**
     * Load module with retry logic
     */
    async loadModule(moduleName, options) {
        const { retry, timeout, onLoad, onError } = options;
        let lastError;

        for (let attempt = 1; attempt <= this.maxRetries; attempt++) {
            try {
                const startTime = performance.now();

                // Create timeout promise
                const timeoutPromise = new Promise((_, reject) => {
                    setTimeout(() => reject(new Error(`Timeout: ${timeout}ms`)), timeout);
                });

                // Race between module load and timeout
                const module = await Promise.race([
                    this.modules[moduleName](),
                    timeoutPromise,
                ]);

                const loadTime = performance.now() - startTime;
                this.performanceMetrics[moduleName] = {
                    loadTime,
                    attempts: attempt,
                    success: true,
                };

                // Call onLoad callback
                if (onLoad && typeof onLoad === 'function') {
                    onLoad(module, loadTime);
                }

                return module;
            } catch (error) {
                lastError = error;
                options.retryCount = attempt;

                if (attempt < this.maxRetries && retry) {
                    const delay = Math.min(1000 * Math.pow(2, attempt - 1), 5000);
                    logger.warn(`Retrying module load: ${moduleName} (attempt ${attempt}/${this.maxRetries}, delay: ${delay}ms)`);
                    await this.sleep(delay);
                }
            }
        }

        throw lastError;
    }

    /**
     * Load module dependencies
     */
    async loadDependencies(moduleName) {
        const deps = this.dependencies[moduleName] || [];

        for (const dep of deps) {
            if (!this.loadedModules.has(dep)) {
                logger.debug(`Loading dependency: ${dep} for ${moduleName}`);
                await this.load(dep, { retry: false });
            }
        }
    }

    /**
     * Preload modules in background
     * 
     * @param {Array<string>} moduleNames - Modules to preload
     * @param {Object} options - Preload options
     */
    async preload(moduleNames, options = {}) {
        const {
            priority = false,
            delay = 0,
            useIdleCallback = true,
        } = options;

        const loadFn = async () => {
            // Sort by priority
            const sorted = moduleNames.sort((a, b) => {
                return (this.priorities[a] || 99) - (this.priorities[b] || 99);
            });

            for (const moduleName of sorted) {
                if (!this.loadedModules.has(moduleName) && !this.loadingModules.has(moduleName)) {
                    try {
                        await this.load(moduleName, {
                            priority: false,
                            retry: true,
                            timeout: 15000,
                        });
                    } catch (error) {
                        logger.warn(`Preload failed for ${moduleName}:`, error.message);
                    }
                }
            }
        };

        if (delay > 0) {
            setTimeout(loadFn, delay);
        } else if (useIdleCallback && 'requestIdleCallback' in window) {
            requestIdleCallback(loadFn, { timeout: 5000 });
        } else {
            loadFn();
        }
    }

    /**
     * Preload all modules for specific industry
     */
    async preloadIndustry(industry) {
        const industryModules = {
            manufacturing: ['manufacturing', 'virtualScroll'],
            healthcare: ['healthcare', 'chat'],
            hotel: ['hotel', 'pos', 'printer'],
            construction: ['construction'],
            agriculture: ['agriculture'],
            fisheries: ['fisheries'],
        };

        const modules = industryModules[industry] || [];
        if (modules.length > 0) {
            logger.info(`Preloading ${industry} modules:`, modules);
            await this.preload(modules, { delay: 2000 });
        }
    }

    /**
     * Get loaded module
     */
    getLoadedModule(moduleName) {
        // This is a placeholder - in real implementation,
        // you'd store module exports in a Map
        return null;
    }

    /**
     * Unload a module (free memory)
     */
    unload(moduleName) {
        if (this.loadedModules.has(moduleName)) {
            this.loadedModules.delete(moduleName);
            logger.info(`Module unloaded: ${moduleName}`);
        }
    }

    /**
     * Get module loading status
     */
    getStatus(moduleName) {
        if (this.loadedModules.has(moduleName)) {
            return 'loaded';
        }
        if (this.loadingModules.has(moduleName)) {
            return 'loading';
        }
        if (this.failedModules.has(moduleName)) {
            return 'failed';
        }
        return 'not_loaded';
    }

    /**
     * Get all loaded modules
     */
    getLoadedModules() {
        return Array.from(this.loadedModules);
    }

    /**
     * Get performance metrics
     */
    getPerformanceMetrics() {
        return this.performanceMetrics;
    }

    /**
     * Get bundle size estimate
     */
    async getBundleSize() {
        if ('performance' in window && 'getEntriesByType' in performance) {
            const scripts = performance.getEntriesByType('resource')
                .filter(entry => entry.initiatorType === 'script');

            return {
                totalSize: scripts.reduce((sum, s) => sum + s.transferSize, 0),
                decodedSize: scripts.reduce((sum, s) => sum + s.decodedBodySize, 0),
                scriptCount: scripts.length,
                scripts: scripts.map(s => ({
                    name: s.name.split('/').pop(),
                    size: s.transferSize,
                    duration: s.duration,
                })),
            };
        }
        return null;
    }

    /**
     * Track performance metrics
     */
    trackPerformance(moduleName, operation, success) {
        if (!this.performanceMetrics[moduleName]) {
            this.performanceMetrics[moduleName] = {};
        }

        this.performanceMetrics[moduleName][operation] = {
            success,
            timestamp: Date.now(),
        };
    }

    /**
     * Sleep utility
     */
    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    /**
     * Show loading indicator
     */
    showLoadingIndicator(moduleName) {
        const indicator = document.createElement('div');
        indicator.id = `module-loading-${moduleName}`;
        indicator.className = 'module-loading-indicator';
        indicator.innerHTML = `
            <div class="loading-spinner"></div>
            <span>Loading ${moduleName}...</span>
        `;
        indicator.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 20px 30px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 9999;
        `;
        document.body.appendChild(indicator);
    }

    /**
     * Hide loading indicator
     */
    hideLoadingIndicator(moduleName) {
        const indicator = document.getElementById(`module-loading-${moduleName}`);
        if (indicator) {
            indicator.remove();
        }
    }
}

// Create singleton instance
const moduleLoader = new ModuleLoader();

// Make available globally
window.moduleLoader = moduleLoader;

// Auto-detect and load industry-specific modules
document.addEventListener('DOMContentLoaded', async () => {
    const industryModule = document.querySelector('meta[name="industry-module"]')?.content;

    if (industryModule) {
        logger.info(`Auto-loading industry module: ${industryModule}`);
        await moduleLoader.preloadIndustry(industryModule);
    }
});

// Listen for module load events
document.addEventListener('module:load', async (e) => {
    const { moduleName, options = {} } = e.detail;

    try {
        const module = await moduleLoader.load(moduleName, options);
        document.dispatchEvent(new CustomEvent('module:loaded', {
            detail: { moduleName, module },
        }));
    } catch (error) {
        document.dispatchEvent(new CustomEvent('module:error', {
            detail: { moduleName, error },
        }));
    }
});

export default moduleLoader;
export { ModuleLoader };
