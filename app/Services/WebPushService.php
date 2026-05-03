<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

/**
 * WebPushService — Send push notifications to subscribed browsers.
 *
 * Uses the Web Push protocol with VAPID authentication via Minishlink library.
 *
 * Setup: Generate VAPID keys with `php artisan vapid:generate`
 * and add to .env: VAPID_PUBLIC_KEY, VAPID_PRIVATE_KEY
 */
class WebPushService
{
    /**
     * Send push notification to a specific user.
     */
    public function sendToUser(int $userId, string $title, string $body, ?string $url = null, ?string $tag = null): int
    {
        $subscriptions = PushSubscription::where('user_id', $userId)->get();
        return $this->sendToSubscriptions($subscriptions, $title, $body, $url, $tag);
    }

    /**
     * Send push notification to all users of a tenant.
     */
    public function sendToTenant(int $tenantId, string $title, string $body, ?string $url = null, ?string $tag = null): int
    {
        $subscriptions = PushSubscription::where('tenant_id', $tenantId)->get();
        return $this->sendToSubscriptions($subscriptions, $title, $body, $url, $tag);
    }

    /**
     * Send push notification to specific role(s) within a tenant.
     */
    public function sendToRole(int $tenantId, array $roles, string $title, string $body, ?string $url = null): int
    {
        $userIds = User::where('tenant_id', $tenantId)->whereIn('role', $roles)->pluck('id');
        $subscriptions = PushSubscription::whereIn('user_id', $userIds)->get();
        return $this->sendToSubscriptions($subscriptions, $title, $body, $url);
    }

    /**
     * Send to a collection of subscriptions using Minishlink WebPush.
     */
    private function sendToSubscriptions($subscriptions, string $title, string $body, ?string $url = null, ?string $tag = null): int
    {
        if ($subscriptions->isEmpty()) {
            return 0;
        }

        $sent = 0;
        $payload = json_encode([
            'title' => $title,
            'body' => $body,
            'url' => $url ?? '/dashboard',
            'tag' => $tag ?? 'erp-' . now()->timestamp,
            'icon' => '/logo.png',
            'badge' => '/logo.png',
        ]);

        $webPush = $this->getWebPushInstance();

        if (!$webPush) {
            Log::warning('WebPush: VAPID keys not configured');
            return 0;
        }

        foreach ($subscriptions as $sub) {
            try {
                $subscription = Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'publicKey' => $sub->p256dh,
                    'authToken' => $sub->auth,
                ]);

                $webPush->sendOneNotification(
                    $subscription,
                    $payload,
                    ['TTL' => 86400]
                );

                $sent++;
            } catch (\Throwable $e) {
                // Check if subscription is expired (410 Gone or 404 Not Found)
                if (str_contains($e->getMessage(), '410') || str_contains($e->getMessage(), '404')) {
                    $sub->delete();
                    Log::info("WebPush: removed expired subscription #{$sub->id}");
                } else {
                    Log::warning("WebPush failed for sub #{$sub->id}: " . $e->getMessage());
                }
            }
        }

        // Flush all pending notifications
        foreach ($webPush->flush() as $report) {
            if (!$report->isSuccess()) {
                Log::warning("WebPush failed: " . $report->getReason());
            }
        }

        return $sent;
    }

    /**
     * Get WebPush instance with VAPID configuration.
     */
    private function getWebPushInstance(): ?WebPush
    {
        [$publicKey, $privateKey] = $this->resolveVapidKeyPair();

        if (!$publicKey || !$privateKey) {
            return null;
        }

        return new WebPush([
            'VAPID' => [
                'subject' => config('app.url'),
                'publicKey' => $publicKey,
                'privateKey' => $privateKey,
            ],
        ]);
    }

    /**
     * Get VAPID public key for client-side subscription.
     */
    public static function vapidPublicKey(): ?string
    {
        [$publicKey] = self::resolveVapidKeyPairStatic();
        return $publicKey;
    }

    /**
     * Check if WebPush is properly configured.
     */
    public function isConfigured(): bool
    {
        [$publicKey, $privateKey] = $this->resolveVapidKeyPair();
        return !empty($publicKey) && !empty($privateKey);
    }

    /**
     * @return array{0:string|null,1:string|null}
     */
    private function resolveVapidKeyPair(): array
    {
        return self::resolveVapidKeyPairStatic();
    }

    /**
     * @return array{0:string|null,1:string|null}
     */
    private static function resolveVapidKeyPairStatic(): array
    {
        $isDevelopment = app()->environment(['local', 'development', 'testing']);
        $envNamespace = $isDevelopment ? 'development' : 'production';

        $publicKey = config("services.vapid.{$envNamespace}.public_key");
        $privateKey = config("services.vapid.{$envNamespace}.private_key");

        if (empty($publicKey) || empty($privateKey)) {
            $publicKey = config('services.vapid.public_key');
            $privateKey = config('services.vapid.private_key');
        }

        return [$publicKey ?: null, $privateKey ?: null];
    }
}
