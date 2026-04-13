/**
 * Construction Module
 * 
 * Industry-specific features for construction:
 * - Project management
 * - Daily site reports
 * - Material deliveries
 * - Subcontractor management
 * 
 * Lazy loaded on demand to reduce initial bundle size.
 */

import logger from '../logger';

class ConstructionModule {
    constructor() {
        this.initialized = false;
    }

    async init() {
        if (this.initialized) return;

        logger.info('Construction module initializing...');

        try {
            // Initialize event listeners
            this.initEventListeners();

            this.initialized = true;
            logger.info('Construction module initialized successfully');
        } catch (error) {
            logger.error('Construction module initialization failed', error);
            throw error;
        }
    }

    initEventListeners() {
        // Listen for construction-specific events
        document.addEventListener('project:created', (e) => {
            logger.info('Project created', e.detail);
        });

        document.addEventListener('daily-report:submitted', (e) => {
            logger.info('Daily site report submitted', e.detail);
        });
    }

    destroy() {
        this.initialized = false;
        logger.info('Construction module destroyed');
    }
}

// Export singleton
const constructionModule = new ConstructionModule();

export default constructionModule;
export { ConstructionModule };
