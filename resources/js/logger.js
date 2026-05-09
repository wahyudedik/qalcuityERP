/**
 * Logger - Production-ready logging wrapper
 *
 * Features:
 * - Disables console.log in production
 * - Structured logging with levels
 * - Optional remote error reporting
 * - Performance monitoring
 *
 * Usage:
 *   import logger from './logger';
 *   logger.info('Message');
 *   logger.error('Error', error);
 *   logger.warn('Warning', data);
 */

class Logger {
    constructor() {
        this.isProduction = import.meta.env.PROD || false;
        this.isDevelopment = import.meta.env.DEV || false;
        this.logLevel = this.isProduction ? 'error' : 'warn';
        this.remoteLoggingEnabled = false;
        this.remoteEndpoint = null;

        // Log levels (numeric priority)
        this.levels = {
            debug: 0,
            info: 1,
            warn: 2,
            error: 3,
        };
    }

    /**
     * Configure logger
     */
    configure(options = {}) {
        if (options.logLevel) {
            this.logLevel = options.logLevel;
        }
        if (options.remoteLogging) {
            this.remoteLoggingEnabled = options.remoteLogging.enabled;
            this.remoteEndpoint = options.remoteLogging.endpoint;
        }
    }

    /**
     * Check if should log based on level
     */
    shouldLog(level) {
        return this.levels[level] >= this.levels[this.logLevel];
    }

    /**
     * Format log message
     */
    formatMessage(level, message, data = null) {
        const timestamp = new Date().toISOString();
        const prefix = `[${timestamp}] [${level.toUpperCase()}]`;

        if (data) {
            return {
                message: `${prefix} ${message}`,
                data: data,
                timestamp: timestamp,
                level: level,
            };
        }

        return {
            message: `${prefix} ${message}`,
            timestamp: timestamp,
            level: level,
        };
    }

    /**
     * Send log to remote server (production only)
     */
    async sendRemoteLog(logData) {
        if (!this.remoteLoggingEnabled || !this.remoteEndpoint) {
            return;
        }

        try {
            await navigator.sendBeacon(this.remoteEndpoint, JSON.stringify(logData));
        } catch (error) {
            // Fallback to fetch if sendBeacon fails
            try {
                await fetch(this.remoteEndpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(logData),
                    keepalive: true,
                });
            } catch (fetchError) {
                // Silent fail - don't create infinite loop
            }
        }
    }

    /**
     * Debug log (development only)
     */
    debug(message, data = null) {
        if (!this.shouldLog('debug')) return;

        const formatted = this.formatMessage('debug', message, data);

        if (this.isDevelopment) {
            console.debug(formatted.message, data || '');
        }
    }

    /**
     * Info log
     */
    info(message, data = null) {
        if (!this.shouldLog('info')) return;

        const formatted = this.formatMessage('info', message, data);

        if (this.isDevelopment) {
            console.log(formatted.message, data || '');
        }
    }

    /**
     * Warning log
     */
    warn(message, data = null) {
        if (!this.shouldLog('warn')) return;

        const formatted = this.formatMessage('warn', message, data);

        console.warn(formatted.message, data || '');

        // Send to remote in production
        if (this.isProduction && this.remoteLoggingEnabled) {
            this.sendRemoteLog(formatted);
        }
    }

    /**
     * Error log (always logged)
     */
    error(message, error = null, context = null) {
        if (!this.shouldLog('error')) return;

        const errorData = {
            message: error?.message || message,
            stack: error?.stack,
            context: context,
        };

        const formatted = this.formatMessage('error', message, errorData);

        console.error(formatted.message, error || '');

        // Send to remote in production
        if (this.isProduction && this.remoteLoggingEnabled) {
            this.sendRemoteLog(formatted);
        }
    }

    /**
     * Performance timing
     */
    performance(label, callback) {
        if (!this.isDevelopment) {
            return callback();
        }

        const start = performance.now();
        const result = callback();
        const end = performance.now();

        this.debug(`[Performance] ${label}`, {
            duration: `${(end - start).toFixed(2)}ms`,
        });

        return result;
    }

    /**
     * Group logs (development only)
     */
    group(label) {
        if (this.isDevelopment && console.group) {
            console.group(label);
        }
    }

    /**
     * End group
     */
    groupEnd() {
        if (this.isDevelopment && console.groupEnd) {
            console.groupEnd();
        }
    }

    /**
     * Table log (development only)
     */
    table(data) {
        if (this.isDevelopment && console.table) {
            console.table(data);
        }
    }

    /**
     * Assert (always active)
     */
    assert(condition, message) {
        if (!condition) {
            this.error('Assertion failed', new Error(message));
        }
    }

    /**
     * Clear console (development only)
     */
    clear() {
        if (this.isDevelopment && console.clear) {
            console.clear();
        }
    }

    /**
     * Trace (development only)
     */
    trace(message) {
        if (this.isDevelopment && console.trace) {
            console.trace(message);
        }
    }
}

// Create singleton instance
const logger = new Logger();

// Export as default
export default logger;

// Also export for named imports
export { Logger };
