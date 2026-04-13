/**
 * Hotel Module
 * 
 * Industry-specific features for hotel:
 * - Room management
 * - Reservations
 * - Housekeeping
 * - POS integration
 * 
 * Lazy loaded on demand to reduce initial bundle size.
 */

import logger from '../logger';

class HotelModule {
    constructor() {
        this.initialized = false;
        this.components = {};
    }

    async init() {
        if (this.initialized) return;

        logger.info('Hotel module initializing...');

        try {
            // Load hotel-specific components
            await this.loadComponents();

            // Initialize event listeners
            this.initEventListeners();

            this.initialized = true;
            logger.info('Hotel module initialized successfully');
        } catch (error) {
            logger.error('Hotel module initialization failed', error);
            throw error;
        }
    }

    async loadComponents() {
        // Dynamic imports for heavy components
        const [
            pos,
            printer,
        ] = await Promise.all([
            import('../offline-pos.js'),
            import('../pos-printer.js'),
        ]);

        this.components.pos = pos;
        this.components.printer = printer;
    }

    initEventListeners() {
        // Listen for hotel-specific events
        document.addEventListener('reservation:created', (e) => {
            logger.info('Reservation created', e.detail);
        });

        document.addEventListener('housekeeping:updated', (e) => {
            logger.info('Housekeeping status updated', e.detail);
        });
    }

    destroy() {
        this.initialized = false;
        this.components = {};
        logger.info('Hotel module destroyed');
    }
}

// Export singleton
const hotelModule = new HotelModule();

export default hotelModule;
export { HotelModule };
