import './bootstrap';

import Alpine from 'alpinejs';

// Initialize Alpine.js
window.Alpine = Alpine;
Alpine.start();

// Register Service Worker for offline support & caching
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('[SW] Registered:', registration.scope);
            })
            .catch(error => {
                console.log('[SW] Registration failed:', error);
            });
    });
}

// Lazy load chat only when needed
document.addEventListener('DOMContentLoaded', () => {
    const chatContainer = document.querySelector('.chat-container');

    if (chatContainer) {
        // Dynamic import - creates separate chunk
        import('./chunks/chat.js')
            .then(({ initChat }) => {
                window.chatManager = initChat();
            })
            .catch(err => {
                console.error('[Chat] Failed to load:', err);
            });
    }
});

// Preload offline manager in background
if ('requestIdleCallback' in window) {
    requestIdleCallback(() => {
        import('./offline-manager.js')
            .then(module => {
                console.log('[Offline] Preloaded');
            })
            .catch(err => {
                console.warn('[Offline] Preload failed:', err);
            });
    }, { timeout: 2000 });
} else {
    setTimeout(() => {
        import('./offline-manager.js')
            .then(module => {
                console.log('[Offline] Preloaded');
            })
            .catch(err => {
                console.warn('[Offline] Preload failed:', err);
            });
    }, 2000);
}

// Initialize Push Notifications (if VAPID key is configured)
document.addEventListener('DOMContentLoaded', () => {
    const vapidKey = document.querySelector('meta[name="vapid-public-key"]')?.content;

    if (vapidKey && 'serviceWorker' in navigator) {
        import('./push-notification.js')
            .then(({ PushNotificationManager }) => {
                window.pushManager = new PushNotificationManager();

                // Auto-initialize if permission already granted
                window.pushManager.initialize(vapidKey).then(success => {
                    if (success) {
                        console.log('[PushNotification] Auto-initialized');
                    }
                });
            })
            .catch(err => {
                console.warn('[PushNotification] Failed to load:', err);
            });
    }
});

console.log('[App] Initialized with code splitting');
