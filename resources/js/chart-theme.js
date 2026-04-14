/**
 * Chart Theme Manager — BUG-1.9 FIX
 * Listens for 'theme-changed' events and updates all Chart.js instances
 * to use appropriate colors for dark/light mode.
 *
 * Usage: import this file once in app.js or include it in pages that use Chart.js.
 * Charts must be registered via window.registerChart(chart) to receive updates.
 */

// Registry of active Chart.js instances
const chartRegistry = new Set();

/**
 * Register a Chart.js instance for theme-aware updates.
 * Call this after creating each chart: window.registerChart(myChart)
 */
window.registerChart = function (chart) {
    if (chart && typeof chart.update === 'function') {
        chartRegistry.add(chart);
    }
};

/**
 * Unregister a chart (call when destroying a chart).
 */
window.unregisterChart = function (chart) {
    chartRegistry.delete(chart);
};

/**
 * Apply dark/light colors to a single Chart.js instance.
 */
function applyChartTheme(chart, isDark) {
    const textColor = isDark ? '#e2e8f0' : '#1e293b';
    const mutedColor = isDark ? '#94a3b8' : '#64748b';
    const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';

    try {
        // Update legend label colors
        if (chart.options?.plugins?.legend?.labels) {
            chart.options.plugins.legend.labels.color = textColor;
        }

        // Update scale tick and grid colors
        const scales = chart.options?.scales || {};
        Object.values(scales).forEach(scale => {
            if (scale.ticks) {
                scale.ticks.color = mutedColor;
            }
            if (scale.grid) {
                scale.grid.color = gridColor;
            }
        });

        chart.update('none'); // 'none' = no animation for theme switch
    } catch (e) {
        // Chart may have been destroyed — remove from registry
        chartRegistry.delete(chart);
    }
}

/**
 * Listen for theme-changed events dispatched by ThemeManager.
 */
window.addEventListener('theme-changed', function (event) {
    const isDark = event.detail?.isDark ?? false;

    chartRegistry.forEach(chart => {
        applyChartTheme(chart, isDark);
    });
});

/**
 * Apply current theme to all registered charts on page load.
 * Runs after DOMContentLoaded to ensure charts are initialized first.
 */
document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.classList.contains('dark');
    // Small delay to allow charts to initialize
    setTimeout(() => {
        chartRegistry.forEach(chart => {
            applyChartTheme(chart, isDark);
        });
    }, 100);
});
