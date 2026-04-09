/**
 * PushNotificationManager
 * 
 * Mengelola push notifications untuk PWA.
 * Handle subscription, permission request, dan notification events.
 */
class PushNotificationManager {
    constructor() {
        this.isSupported = 'PushManager' in window && 'serviceWorker' in navigator;
        this.subscription = null;
        this.vapidPublicKey = null;
    }

    /**
     * Initialize push notifications
     * @param {string} vapidPublicKey - VAPID public key from server
     */
    async initialize(vapidPublicKey) {
        if (!this.isSupported) {
            console.warn('[PushNotification] Push notifications not supported');
            return false;
        }

        this.vapidPublicKey = vapidPublicKey;

        try {
            // Check permission
            const permission = await this.requestPermission();
            if (permission !== 'granted') {
                console.warn('[PushNotification] Permission denied');
                return false;
            }

            // Get or create subscription
            this.subscription = await this.getSubscription();

            if (this.subscription) {
                console.log('[PushNotification] Already subscribed');
                await this.sendSubscriptionToServer(this.subscription);
                return true;
            }

            return false;
        } catch (error) {
            console.error('[PushNotification] Initialization error:', error);
            return false;
        }
    }

    /**
     * Request notification permission
     * @returns {Promise<string>} Permission status
     */
    async requestPermission() {
        if (!('Notification' in window)) {
            return 'denied';
        }

        const permission = await Notification.requestPermission();
        console.log('[PushNotification] Permission:', permission);
        return permission;
    }

    /**
     * Get current subscription or create new one
     * @returns {Promise<PushSubscription|null>}
     */
    async getSubscription() {
        try {
            const registration = await navigator.serviceWorker.ready;
            let subscription = await registration.pushManager.getSubscription();

            if (!subscription && this.vapidPublicKey) {
                // Create new subscription
                subscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: this.urlBase64ToUint8Array(this.vapidPublicKey),
                });

                console.log('[PushNotification] New subscription created');
            }

            this.subscription = subscription;
            return subscription;
        } catch (error) {
            console.error('[PushNotification] Get subscription error:', error);
            return null;
        }
    }

    /**
     * Subscribe to push notifications
     * @returns {Promise<PushSubscription|null>}
     */
    async subscribe() {
        if (!this.vapidPublicKey) {
            console.error('[PushNotification] VAPID key not set');
            return null;
        }

        try {
            const permission = await this.requestPermission();
            if (permission !== 'granted') {
                return null;
            }

            const subscription = await this.getSubscription();
            if (subscription) {
                await this.sendSubscriptionToServer(subscription);
                console.log('[PushNotification] Subscribed successfully');
                return subscription;
            }

            return null;
        } catch (error) {
            console.error('[PushNotification] Subscribe error:', error);
            return null;
        }
    }

    /**
     * Unsubscribe from push notifications
     * @returns {Promise<boolean>}
     */
    async unsubscribe() {
        if (!this.subscription) {
            return true;
        }

        try {
            const success = await this.subscription.unsubscribe();
            if (success) {
                console.log('[PushNotification] Unsubscribed');
                await this.removeSubscriptionFromServer();
                this.subscription = null;
            }
            return success;
        } catch (error) {
            console.error('[PushNotification] Unsubscribe error:', error);
            return false;
        }
    }

    /**
     * Send subscription to server for storage
     * @param {PushSubscription} subscription
     */
    async sendSubscriptionToServer(subscription) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            const response = await fetch('/push/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    subscription: subscription.toJSON(),
                }),
            });

            if (response.ok) {
                console.log('[PushNotification] Subscription sent to server');
            } else {
                console.warn('[PushNotification] Failed to send subscription to server');
            }
        } catch (error) {
            console.error('[PushNotification] Send subscription error:', error);
        }
    }

    /**
     * Remove subscription from server
     */
    async removeSubscriptionFromServer() {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            const response = await fetch('/push/unsubscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    endpoint: this.subscription?.endpoint,
                }),
            });

            if (response.ok) {
                console.log('[PushNotification] Subscription removed from server');
            }
        } catch (error) {
            console.error('[PushNotification] Remove subscription error:', error);
        }
    }

    /**
     * Check if user is subscribed
     * @returns {Promise<boolean>}
     */
    async isSubscribed() {
        if (!this.subscription) {
            this.subscription = await this.getSubscription();
        }
        return this.subscription !== null;
    }

    /**
     * Get subscription status
     * @returns {Promise<Object>}
     */
    async getStatus() {
        const permission = Notification.permission;
        const subscribed = await this.isSubscribed();

        return {
            supported: this.isSupported,
            permission,
            subscribed,
        };
    }

    /**
     * Convert VAPID key from base64 to Uint8Array
     * @param {string} base64String
     * @returns {Uint8Array}
     */
    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }

        return outputArray;
    }

    /**
     * Show local notification (without push)
     * @param {Object} options
     * @param {string} options.title
     * @param {string} options.body
     * @param {string} options.icon
     * @param {string} options.url
     */
    showNotification({ title, body, icon = '/favicon.png', url = '/dashboard' }) {
        if (!('Notification' in window)) {
            console.warn('[PushNotification] Notifications not supported');
            return;
        }

        if (Notification.permission === 'granted') {
            navigator.serviceWorker.ready.then(registration => {
                registration.showNotification(title, {
                    body,
                    icon,
                    badge: icon,
                    data: { url },
                    tag: url,
                });
            });
        }
    }
}

// Export as global singleton
window.PushNotificationManager = PushNotificationManager;
