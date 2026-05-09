/**
 * POS-specific lightweight entry point.
 * Hanya load Alpine.js dan axios — tanpa module-loader, help-system, dll.
 * Ini membuat halaman POS jauh lebih ringan dan cepat.
 */

import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

Alpine.plugin(collapse);
window.Alpine = Alpine;
Alpine.start();

// Service Worker registration (untuk offline POS support)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            // Graceful degradation — app tetap jalan tanpa offline support
        });
    });
}
