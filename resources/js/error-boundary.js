/**
 * Alpine.js Error Boundary Plugin
 * 
 * Provides error boundary functionality for Alpine components
 * Catches and handles errors gracefully without breaking the UI
 * 
 * Usage:
 * <div x-data x-error-boundary>
 *     <button @click="riskyOperation()">Click</button>
 *     <template x-if="$error">
 *         <div class="error">Something went wrong</div>
 *     </template>
 * </div>
 */

import logger from './logger';

export default function errorBoundary(Alpine) {
    Alpine.data('errorBoundary', () => ({
        error: null,
        errorMessage: '',
        errorDetails: null,
        hasError: false,
        retryCount: 0,
        maxRetries: 3,

        init() {
            // Override console.error to catch Alpine errors
            const originalError = console.error;
            const self = this;

            console.error = function (...args) {
                // Check if this is an Alpine error
                const message = args.join(' ');
                if (message.includes('Alpine') || message.includes('x-')) {
                    self.handleError(args[0], 'Alpine Runtime Error');
                }
                originalError.apply(console, args);
            };

            // Listen for unhandled promise rejections
            window.addEventListener('unhandledrejection', (event) => {
                self.handleError(event.reason, 'Unhandled Promise Rejection');
            });

            // Listen for global errors
            window.addEventListener('error', (event) => {
                if (event.target && event.target.closest('[x-data]')) {
                    self.handleError(event.error, 'Component Error');
                }
            });

            logger.debug('Error boundary initialized');
        },

        /**
         * Execute function with error boundary
         */
        async execute(fn, fallback = null) {
            try {
                this.clearError();
                const result = await fn();
                return result;
            } catch (error) {
                this.handleError(error, 'Execution Error');
                return fallback;
            }
        },

        /**
         * Handle error
         */
        handleError(error, context = 'Unknown') {
            this.error = error;
            this.errorMessage = error?.message || String(error);
            this.errorDetails = {
                context: context,
                timestamp: new Date().toISOString(),
                stack: error?.stack,
            };
            this.hasError = true;

            logger.error(`Error Boundary: ${context}`, error, {
                component: this.$el?.closest('[x-data]')?.tagName || 'Unknown',
            });
        },

        /**
         * Clear error state
         */
        clearError() {
            this.error = null;
            this.errorMessage = '';
            this.errorDetails = null;
            this.hasError = false;
        },

        /**
         * Retry operation
         */
        async retry(fn) {
            if (this.retryCount >= this.maxRetries) {
                logger.error('Max retries exceeded', null, {
                    retryCount: this.retryCount,
                });
                return;
            }

            this.retryCount++;
            logger.info(`Retry attempt ${this.retryCount}/${this.maxRetries}`);

            return this.execute(fn);
        },

        /**
         * Reset retry count
         */
        resetRetry() {
            this.retryCount = 0;
            this.clearError();
        },

        /**
         * Dismiss error
         */
        dismiss() {
            this.resetRetry();
            logger.debug('Error dismissed by user');
        },

        /**
         * Report error to server
         */
        async reportError() {
            if (!this.error) return;

            try {
                await fetch('/api/errors/report', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    },
                    body: JSON.stringify({
                        message: this.errorMessage,
                        context: this.errorDetails?.context,
                        stack: this.errorDetails?.stack,
                        url: window.location.href,
                        userAgent: navigator.userAgent,
                    }),
                });

                logger.info('Error reported to server');
            } catch (error) {
                logger.error('Failed to report error', error);
            }
        },
    }));

    logger.info('Alpine Error Boundary plugin loaded');
}
