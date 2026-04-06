/**
 * Fisheries Module - Frontend Service Layer
 * Handles all API communication for fisheries features
 */

import axios from 'axios';

// Create Axios instance with CSRF token
const fisheriesApi = axios.create({
    baseURL: '/fisheries',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
    },
});

// Add auth interceptor
fisheriesApi.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);

/**
 * Cold Chain Management Service
 */
export const coldChainService = {
    // Get all cold storage units
    getStorageUnits(params = {}) {
        return fisheriesApi.get('/cold-chain/storage', { params });
    },

    // Get single storage unit details
    getStorageUnit(id) {
        return fisheriesApi.get(`/cold-chain/storage/${id}`);
    },

    // Log temperature reading
    logTemperature(storageId, data) {
        return fisheriesApi.post(`/cold-chain/storage/${storageId}/temperature`, data);
    },

    // Get temperature history
    getTemperatureHistory(storageId, params = {}) {
        return fisheriesApi.get(`/cold-chain/storage/${storageId}/history`, { params });
    },

    // Get active alerts
    getAlerts() {
        return fisheriesApi.get('/cold-chain/alerts');
    },

    // Acknowledge alert
    acknowledgeAlert(alertId) {
        return fisheriesApi.post(`/cold-chain/alerts/${alertId}/acknowledge`);
    },

    // Generate compliance report
    generateComplianceReport(params = {}) {
        return fisheriesApi.get('/cold-chain/compliance-report', { params });
    },
};

/**
 * Fishing Operations Service
 */
export const fishingService = {
    // Get all fishing trips
    getTrips(params = {}) {
        return fisheriesApi.get('/operations/trips', { params });
    },

    // Get single trip details
    getTrip(id) {
        return fisheriesApi.get(`/operations/trips/${id}`);
    },

    // Plan new trip
    planTrip(data) {
        return fisheriesApi.post('/operations/trips/plan', data);
    },

    // Record catch
    recordCatch(tripId, data) {
        return fisheriesApi.post(`/operations/trips/${tripId}/catch`, data);
    },

    // Update trip status
    updateTripStatus(tripId, status) {
        return fisheriesApi.post(`/operations/trips/${tripId}/status`, { status });
    },

    // Depart trip
    departTrip(tripId) {
        return fishingService.updateTripStatus(tripId, 'departed');
    },

    // Complete trip
    completeTrip(tripId) {
        return fishingService.updateTripStatus(tripId, 'completed');
    },

    // Get trip analytics
    getTripAnalytics(tripId) {
        return fisheriesApi.get(`/operations/trips/${tripId}/analytics`);
    },

    // Get vessels list
    getVessels() {
        return fisheriesApi.get('/operations/vessels');
    },

    // Get fishing zones
    getFishingZones() {
        return fisheriesApi.get('/operations/fishing-zones');
    },
};

/**
 * Aquaculture Management Service
 */
export const aquacultureService = {
    // Get all ponds
    getPonds(params = {}) {
        return fisheriesApi.get('/aquaculture/ponds', { params });
    },

    // Get single pond details
    getPond(id) {
        return fisheriesApi.get(`/aquaculture/ponds/${id}`);
    },

    // Create new pond
    createPond(data) {
        return fisheriesApi.post('/aquaculture/ponds', data);
    },

    // Log water quality
    logWaterQuality(pondId, data) {
        return fisheriesApi.post(`/aquaculture/ponds/${pondId}/water-quality`, data);
    },

    // Get water quality history
    getWaterQualityHistory(pondId, params = {}) {
        return fisheriesApi.get(`/aquaculture/ponds/${pondId}/water-quality/history`, { params });
    },

    // Log feeding
    logFeeding(pondId, data) {
        return fisheriesApi.post(`/aquaculture/ponds/${pondId}/feeding`, data);
    },

    // Get feeding history
    getFeedingHistory(pondId, params = {}) {
        return fisheriesApi.get(`/aquaculture/ponds/${pondId}/feeding/history`, { params });
    },

    // Calculate FCR
    calculateFCR(pondId, params = {}) {
        return fisheriesApi.get(`/aquaculture/ponds/${pondId}/fcr`, { params });
    },

    // Get pond dashboard
    getPondDashboard(pondId) {
        return fisheriesApi.get(`/aquaculture/ponds/${pondId}/dashboard`);
    },
};

/**
 * Species & Grading Service
 */
export const speciesService = {
    // Get all species
    getSpecies(params = {}) {
        return fisheriesApi.get('/species', { params });
    },

    // Get single species
    getSpeciesById(id) {
        return fisheriesApi.get(`/species/${id}`);
    },

    // Create species
    createSpecies(data) {
        return fisheriesApi.post('/species', data);
    },

    // Get quality grades
    getGrades() {
        return fisheriesApi.get('/species/grades');
    },

    // Create grade
    createGrade(data) {
        return fisheriesApi.post('/species/grades', data);
    },

    // Assess freshness
    assessFreshness(data) {
        return fisheriesApi.post('/species/freshness-assessment', data);
    },

    // Calculate market value
    calculateMarketValue(speciesId, weight, gradeId = null) {
        return fisheriesApi.post('/species/market-value', {
            species_id: speciesId,
            weight,
            grade_id: gradeId,
        });
    },
};

/**
 * Export Documentation Service
 */
export const exportService = {
    // Get permits
    getPermits(params = {}) {
        return fisheriesApi.get('/export/permits', { params });
    },

    // Create permit
    createPermit(data) {
        return fisheriesApi.post('/export/permits', data);
    },

    // Get health certificates
    getCertificates(params = {}) {
        return fisheriesApi.get('/export/certificates', { params });
    },

    // Create certificate
    createCertificate(data) {
        return fisheriesApi.post('/export/certificates', data);
    },

    // Get customs declarations
    getCustomsDeclarations(params = {}) {
        return fisheriesApi.get('/export/customs-declarations', { params });
    },

    // Create customs declaration
    createCustomsDeclaration(data) {
        return fisheriesApi.post('/export/customs-declarations', data);
    },

    // Get shipments
    getShipments(params = {}) {
        return fisheriesApi.get('/export/shipments', { params });
    },

    // Create shipment
    createShipment(data) {
        return fisheriesApi.post('/export/shipments', data);
    },

    // Track shipment
    trackShipment(shipmentId) {
        return fisheriesApi.get(`/export/shipments/${shipmentId}/tracking`);
    },

    // Check export readiness
    checkExportReadiness(permitId, certificateId, customsId) {
        return fisheriesApi.post('/export/readiness-check', {
            permit_id: permitId,
            certificate_id: certificateId,
            customs_id: customsId,
        });
    },
};

/**
 * WebSocket Integration for Real-time Updates
 */
export const fisheriesWebSocket = {
    channel: null,

    // Initialize Echo connection
    init() {
        if (window.Echo) {
            this.channel = window.Echo.private(`fisheries.${window.tenantId}`);
        }
    },

    // Subscribe to temperature updates
    subscribeToTemperatureUpdates(storageUnitId, callback) {
        if (!this.channel) this.init();

        this.channel.listen('.temperature.updated', (event) => {
            if (event.storage_unit_id === storageUnitId) {
                callback(event);
            }
        });
    },

    // Subscribe to alerts
    subscribeToAlerts(callback) {
        if (!this.channel) this.init();

        this.channel.listen('.alert.created', (event) => {
            callback(event);
        });
    },

    // Unsubscribe from all
    unsubscribe() {
        if (this.channel) {
            this.channel.stopListening('.temperature.updated');
            this.channel.stopListening('.alert.created');
        }
    },
};

// Export all services
export default {
    coldChain: coldChainService,
    fishing: fishingService,
    aquaculture: aquacultureService,
    species: speciesService,
    export: exportService,
    websocket: fisheriesWebSocket,
};
