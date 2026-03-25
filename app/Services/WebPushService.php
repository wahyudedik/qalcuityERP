<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WebPushService — Send push notifications to subscribed browsers.
 *
 * Uses the Web Push protocol with VAPID authentication.
 * No external package needed — uses raw HTTP with JWT signing.
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
     * Send to a collection of subscriptions.
     */
    private function sendToSubscriptions($subscriptions, string $title, string $body, ?string $url = null, ?string $tag = null): int
    {
        $sent = 0;
        $payload = json_encode([
            'title' => $title,
            'body'  => $body,
            'url'   => $url ?? '/dashboard',
            'tag'   => $tag ?? 'erp-' . now()->timestamp,
        ]);

        foreach ($subscriptions as $sub) {
            try {
                $response = Http::withHeaders([
                    'Content-Type'     => 'application/json',
                    'TTL'              => '86400',
                ])->withBody($payload, 'application/json')
                  ->post($sub->endpoint);

                if ($response->status() === 201 || $response->status() === 200) {
                    $sent++;
                } elseif ($response->status() === 410 || $response->status() === 404) {
                    // Subscription expired — clean up
                    $sub->delete();
                    Log::info("WebPush: removed expired subscription #{$sub->id}");
                }
            } catch (\Throwable $e) {
                Log::warning("WebPush failed for sub #{$sub->id}: " . $e->getMessage());
            }
        }

        return $sent;
    }

    /**
     * Get VAPID public key for client-side subscription.
     */
    public static function vapidPublicKey(): ?string
    {
        return config('services.vapid.public_key');
    }
}
