/**
 * Chart Theme Manager — Light Mode Only
 * Applies consistent light mode colors to all Chart.js instances.
 *
 * Usage: import this file once in app.js or include it in pages that use Chart.js.
 * Charts must be registered via window.registerChart(chart) to receive theme colors.
 */

// Light mode colors (fixed)
const LIGHT_COLORS = {
    text: '#1e293b',
    muted: '#64748b',
    grid: 'rgba(0,0,0,0.06)',
};

// Registry of active Chart.js instances
const chartRegistry = new Set();

/**
 * Register a Chart.js instance and apply light mode colors.
 * Call this after creating each chart: window.registerChart(myChart)
 */
window.registerChart = function (chart) {
    if (chart && typeof chart.update === 'function') {
        chartRegistry.add(chart);
        applyChartTheme(chart);
    }
};

/**
 * Unregister a chart (call when destroying a chart).
 */
window.unregisterChart = function (chart) {
    chartRegistry.delete(chart);
};

/**
 * Apply light mode colors to a single Chart.js instance.
 */
function applyChartTheme(chart) {
    try {
        // Update legend label colors
        if (chart.options?.plugins?.legend?.labels) {
            chart.options.plugins.legend.labels.color = LIGHT_COLORS.text;
        }

        // Update scale tick and grid colors
        const scales = chart.options?.scales || {};
        Object.values(scales).forEach(scale => {
            if (scale.ticks) {
                scale.ticks.color = LIGHT_COLORS.muted;
            }
            if (scale.grid) {
                scale.grid.color = LIGHT_COLORS.grid;
            }
        });

        chart.update('none'); // 'none' = no animation for theme switch
    } catch (e) {
        // Chart may have been destroyed — remove from registry
        chartRegistry.delete(chart);
    }
}
