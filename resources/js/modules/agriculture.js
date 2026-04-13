/**
 * Agriculture Module
 * 
 * Industry-specific features for agriculture:
 * - Farm management
 * - Crop cycles
 * - Harvest logs
 * - Weather integration
 * 
 * Lazy loaded on demand to reduce initial bundle size.
 */

import logger from '../logger';

class AgricultureModule {
    constructor() {
        this.initialized = false;
    }

    async init() {
        if (this.initialized) return;

        logger.info('Agriculture module initializing...');

        try {
            // Initialize event listeners
            this.initEventListeners();

            this.initialized = true;
            logger.info('Agriculture module initialized successfully');
        } catch (error) {
            logger.error('Agriculture module initialization failed', error);
            throw error;
        }
    }

    initEventListeners() {
        // Listen for agriculture-specific events
        document.addEventListener('crop-cycle:started', (e) => {
            logger.info('Crop cycle started', e.detail);
        });

        document.addEventListener('harvest:logged', (e) => {
            logger.info('Harvest logged', e.detail);
        });
    }

    destroy() {
        this.initialized = false;
        logger.info('Agriculture module destroyed');
    }
}

// Export singleton
const agricultureModule = new AgricultureModule();

export default agricultureModule;
export { AgricultureModule };
