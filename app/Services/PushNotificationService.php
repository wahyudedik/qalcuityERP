<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    /**
     * Send push notification to user
     */
    public function sendToUser(int $userId, string $title, string $body, array $data = []): bool
    {
        $user = User::find($userId);

        if (! $user || ! $user->push_subscription) {
            return false;
        }

        return $this->sendPushNotification(
            $user->push_subscription,
            $title,
            $body,
            $data
        );
    }

    /**
     * Send push notification to multiple users
     */
    public function sendToUsers(array $userIds, string $title, string $body, array $data = []): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($userIds as $userId) {
            try {
                $success = $this->sendToUser($userId, $title, $body, $data);

                if ($success) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                }
            } catch (\Throwable $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ];

                Log::error('Push notification failed: '.$e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Send push notification to tenant users
     */
    public function sendToTenant(int $tenantId, string $title, string $body, array $data = []): array
    {
        $users = User::where('tenant_id', $tenantId)
            ->whereNotNull('push_subscription')
            ->pluck('id')
            ->toArray();

        return $this->sendToUsers($users, $title, $body, $data);
    }

    /**
     * Send transactional notification
     */
    public function sendTransactionNotification(string $type, int $userId, array $context = []): bool
    {
        $notification = $this->getTransactionTemplate($type, $context);

        if (! $notification) {
            return false;
        }

        return $this->sendToUser(
            $userId,
            $notification['title'],
            $notification['body'],
            $notification['data']
        );
    }

    /**
     * Subscribe user to push notifications
     */
    public function subscribeUser(int $userId, array $subscription): bool
    {
        $user = User::find($userId);

        if (! $user) {
            return false;
        }

        $user->update([
            'push_subscription' => json_encode($subscription),
        ]);

        return true;
    }

    /**
     * Unsubscribe user from push notifications
     */
    public function unsubscribeUser(int $userId): bool
    {
        $user = User::find($userId);

        if (! $user) {
            return false;
        }

        $user->update([
            'push_subscription' => null,
        ]);

        return true;
    }

    /**
     * Get notification templates for common events
     */
    protected function getTransactionTemplate(string $type, array $context = []): ?array
    {
        return match ($type) {
            'invoice_created' => [
                'title' => 'Invoice Baru Dibuat',
                'body' => "Invoice #{$context['invoice_number']} sebesar Rp ".number_format($context['amount'], 0, ',', '.'),
                'data' => [
                    'type' => 'invoice',
                    'id' => $context['invoice_id'],
                    'url' => "/invoices/{$context['invoice_id']}",
                ],
            ],

            'payment_received' => [
                'title' => 'Pembayaran Diterima',
                'body' => 'Pembayaran Rp '.number_format($context['amount'], 0, ',', '.')." untuk invoice #{$context['invoice_number']}",
                'data' => [
                    'type' => 'payment',
                    'id' => $context['payment_id'],
                    'url' => "/payments/{$context['payment_id']}",
                ],
            ],

            'low_stock' => [
                'title' => 'Stok Menipis',
                'body' => "Produk '{$context['product_name']}' tersisa {$context['quantity']} unit",
                'data' => [
                    'type' => 'inventory',
                    'id' => $context['product_id'],
                    'url' => "/inventory/products/{$context['product_id']}",
                ],
            ],

            'order_completed' => [
                'title' => 'Order Selesai',
                'body' => "Order #{$context['order_number']} telah selesai diproses",
                'data' => [
                    'type' => 'order',
                    'id' => $context['order_id'],
                    'url' => "/orders/{$context['order_id']}",
                ],
            ],

            'task_assigned' => [
                'title' => 'Tugas Baru',
                'body' => "Anda mendapat tugas: {$context['task_title']}",
                'data' => [
                    'type' => 'task',
                    'id' => $context['task_id'],
                    'url' => "/tasks/{$context['task_id']}",
                ],
            ],

            default => null
        };
    }

    /**
     * Send actual push notification via Web Push
     */
    protected function sendPushNotification(string $subscription, string $title, string $body, array $data = []): bool
    {
        try {
            $subscriptionData = json_decode($subscription, true);

            if (! $subscriptionData) {
                return false;
            }

            // Use web-push library or custom implementation
            // For now, log the notification (implement actual sending with Minishlink/web-push)

            Log::info('Push notification sent', [
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'endpoint' => $subscriptionData['endpoint'] ?? null,
            ]);

            // TODO: Implement actual Web Push sending
            // $webPush = new WebPush([...]);
            // $webPush->sendOneNotification(...);

            return true;

        } catch (\Throwable $e) {
            Log::error('Push notification error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Check if push notifications are configured
     */
    public function isConfigured(): bool
    {
        return config('services.web_push.vapid_public_key') !== null
            && config('services.web_push.vapid_private_key') !== null;
    }

    /**
     * Get VAPID public key for client subscription
     */
    public function getVapidPublicKey(): ?string
    {
        return config('services.web_push.vapid_public_key');
    }
}
