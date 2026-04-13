/**
 * Healthcare Module
 * 
 * Industry-specific features for healthcare:
 * - Patient management
 * - Appointments
 * - Medical records
 * - Telemedicine
 * 
 * Lazy loaded on demand to reduce initial bundle size.
 */

import logger from '../logger';

class HealthcareModule {
    constructor() {
        this.initialized = false;
        this.components = {};
    }

    async init() {
        if (this.initialized) return;

        logger.info('Healthcare module initializing...');

        try {
            // Load healthcare-specific components
            await this.loadComponents();

            // Initialize event listeners
            this.initEventListeners();

            this.initialized = true;
            logger.info('Healthcare module initialized successfully');
        } catch (error) {
            logger.error('Healthcare module initialization failed', error);
            throw error;
        }
    }

    async loadComponents() {
        // Dynamic imports for heavy components
        const [
            chat,
        ] = await Promise.all([
            import('../chunks/chat.js'),
        ]);

        this.components.chat = chat;
    }

    initEventListeners() {
        // Listen for healthcare-specific events
        document.addEventListener('appointment:booked', (e) => {
            logger.info('Appointment booked', e.detail);
        });

        document.addEventListener('patient:admitted', (e) => {
            logger.info('Patient admitted', e.detail);
        });
    }

    destroy() {
        this.initialized = false;
        this.components = {};
        logger.info('Healthcare module destroyed');
    }
}

// Export singleton
const healthcareModule = new HealthcareModule();

export default healthcareModule;
export { HealthcareModule };
