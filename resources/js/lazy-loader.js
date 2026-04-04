/**
 * Lazy Loading Helper for qalcuityERP
 * Dynamic imports with loading states and error handling
 */

/**
 * Lazy load a module with optional loading state
 * @param {Function} importFn - The dynamic import function
 * @param {Object} options - Loading options
 * @returns {Promise<any>}
 */
export async function lazyLoad(importFn, options = {}) {
    const {
        loading = null,
        error = null,
        timeout = 10000,
        retries = 3
    } = options;

    // Show loading state
    if (loading && typeof loading === 'function') {
        loading(true);
    }

    let lastError;

    // Retry logic for failed loads
    for (let attempt = 1; attempt <= retries; attempt++) {
        try {
            // Create abort controller for timeout
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), timeout);

            // Import the module
            const module = await Promise.race([
                importFn(),
                new Promise((_, reject) => {
                    controller.signal.addEventListener('abort', () => {
                        reject(new Error(`Lazy load timeout after ${timeout}ms`));
                    });
                })
            ]);

            clearTimeout(timeoutId);

            // Hide loading state
            if (loading && typeof loading === 'function') {
                loading(false);
            }

            return module;

        } catch (err) {
            lastError = err;
            console.warn(`[LazyLoad] Attempt ${attempt}/${retries} failed:`, err.message);

            // Don't retry on certain errors
            if (err.message.includes('timeout') || err.name === 'AbortError') {
                continue;
            }

            break;
        }
    }

    // Hide loading state
    if (loading && typeof loading === 'function') {
        loading(false);
    }

    // Show error state
    if (error && typeof error === 'function') {
        error(lastError);
    }

    throw lastError;
}

/**
 * Lazy load a component with automatic chunk naming
 * @param {string} chunkName - Name of the chunk to load
 * @param {Object} options - Loading options
 * @returns {Promise<any>}
 */
export function loadChunk(chunkName, options = {}) {
    return lazyLoad(() => import(`./${chunkName}`), options);
}

/**
 * Preload modules in background
 * @param {Array<Function>} importFns - Array of dynamic import functions
 */
export function preloadModules(importFns) {
    // Use requestIdleCallback if available, otherwise setTimeout
    const scheduleIdle = window.requestIdleCallback ||
        ((cb) => setTimeout(cb, 1));

    scheduleIdle(() => {
        importFns.forEach(importFn => {
            importFn().catch(err => {
                console.warn('[Preload] Failed to preload module:', err);
            });
        });
    }, { timeout: 2000 });
}

/**
 * Load CSS dynamically
 * @param {string} href - CSS file URL
 * @returns {Promise<void>}
 */
export function loadCSS(href) {
    return new Promise((resolve, reject) => {
        // Check if already loaded
        const existing = document.querySelector(`link[href="${href}"]`);
        if (existing) {
            return resolve();
        }

        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = href;
        link.onload = resolve;
        link.onerror = reject;

        document.head.appendChild(link);
    });
}

/**
 * Load script dynamically
 * @param {string} src - Script file URL
 * @param {Object} options - Script options
 * @returns {Promise<void>}
 */
export function loadScript(src, options = {}) {
    return new Promise((resolve, reject) => {
        const {
            type = 'text/javascript',
            async = true,
            defer = false,
            crossorigin = null
        } = options;

        // Check if already loaded
        const existing = document.querySelector(`script[src="${src}"]`);
        if (existing) {
            return resolve();
        }

        const script = document.createElement('script');
        script.type = type;
        script.src = src;
        script.async = async;
        script.defer = defer;

        if (crossorigin) {
            script.crossOrigin = crossorigin;
        }

        script.onload = resolve;
        script.onerror = reject;

        document.head.appendChild(script);
    });
}

/**
 * Image lazy loader with Intersection Observer
 * @param {NodeList|Array} images - Images to lazy load
 * @param {Object} options - Lazy load options
 */
export function lazyLoadImages(images, options = {}) {
    const {
        rootMargin = '50px',
        threshold = 0.01,
        placeholder = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'
    } = options;

    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;

            const img = entry.target;

            if (img.dataset.src) {
                img.src = img.dataset.src;
                img.onload = () => {
                    img.classList.add('loaded');
                    observer.unobserve(img);
                };
                img.onerror = () => {
                    console.warn('[LazyLoad] Image failed to load:', img.dataset.src);
                    observer.unobserve(img);
                };
            }
        });
    }, { rootMargin, threshold });

    images.forEach(img => {
        img.src = placeholder;
        imageObserver.observe(img);
    });

    return imageObserver;
}

/**
 * Component lazy loader factory
 * Creates a lazy-loaded component wrapper
 * @param {Function} importFn - Dynamic import function
 * @param {string} tagName - HTML tag name for the component
 * @returns {Object} Component definition
 */
export function createLazyComponent(importFn, tagName = 'div') {
    return {
        setup(props, { slots }) {
            const loaded = ref(false);
            const component = ref(null);
            const error = ref(null);

            onMounted(async () => {
                try {
                    const module = await importFn();
                    component.value = module.default || module;
                    loaded.value = true;
                } catch (err) {
                    error.value = err;
                    console.error('[LazyComponent] Failed to load:', err);
                }
            });

            return () => {
                if (!loaded.value) {
                    if (error.value) {
                        return h('div', { class: 'lazy-error' }, 'Failed to load component');
                    }
                    return h('div', { class: 'lazy-loading' }, slots.loading?.() || 'Loading...');
                }

                return h(component.value, props, slots);
            };
        }
    };
}

// Export convenience re-exports
export const lazy = lazyLoad;
export const preload = preloadModules;

console.log('[LazyLoad] Helper loaded');
