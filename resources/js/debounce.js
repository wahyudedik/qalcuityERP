/**
 * Debounce Utility
 * 
 * Delays function execution until after a specified wait time
 * has elapsed since the last call. Useful for search inputs,
 * window resize events, and other high-frequency events.
 * 
 * Usage:
 * const debouncedSearch = debounce(searchFunction, 300);
 * input.addEventListener('input', debouncedSearch);
 * 
 * Or with Alpine.js:
 * Alpine.data('searchComponent', () => ({
 *     init() {
 *         this.debouncedSearch = debounce(this.search, 300);
 *         this.$watch('query', () => this.debouncedSearch());
 *     }
 * }));
 */

/**
 * Create a debounced function.
 * 
 * @param {Function} func Function to debounce
 * @param {number} wait Wait time in milliseconds
 * @param {boolean} immediate Execute on leading edge
 * @returns {Function} Debounced function
 */
export function debounce(func, wait = 300, immediate = false) {
    let timeout;

    return function executedFunction(...args) {
        const context = this;

        const later = () => {
            timeout = null;
            if (!immediate) {
                func.apply(context, args);
            }
        };

        const callNow = immediate && !timeout;

        clearTimeout(timeout);
        timeout = setTimeout(later, wait);

        if (callNow) {
            func.apply(context, args);
        }
    };
}

/**
 * Create a throttled function (executes at most once per wait period).
 * 
 * @param {Function} func Function to throttle
 * @param {number} limit Time limit in milliseconds
 * @returns {Function} Throttled function
 */
export function throttle(func, limit = 300) {
    let inThrottle;

    return function executedFunction(...args) {
        const context = this;

        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

/**
 * Initialize debounce for search inputs automatically.
 * Looks for inputs with data-debounce attribute.
 * 
 * Usage in HTML:
 * <input type="search" data-debounce="300" data-action="search">
 */
export function initAutoDebounce() {
    const searchInputs = document.querySelectorAll('input[data-debounce]');

    searchInputs.forEach(input => {
        const waitTime = parseInt(input.dataset.debounce) || 300;
        const eventName = input.dataset.event || 'input';

        const debouncedHandler = debounce((event) => {
            // Dispatch custom event with debounced value
            input.dispatchEvent(new CustomEvent('debounced-input', {
                detail: { value: event.target.value }
            }));
        }, waitTime);

        input.addEventListener(eventName, debouncedHandler);
    });
}

/**
 * Alpine.js plugin for debounce.
 * 
 * Usage:
 * <div x-data="{ query: '' }" x-debounce:300ms="search">
 *     <input x-model="query">
 * </div>
 */
export function debouncePlugin(Alpine) {
    Alpine.directive('debounce', (el, { expression, modifiers }, { evaluate }) => {
        const wait = modifiers.length > 0 ? parseInt(modifiers[0]) : 300;

        let timeout;
        const handler = (event) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                evaluate(expression);
            }, wait);
        };

        el.addEventListener('input', handler);
    });
}
