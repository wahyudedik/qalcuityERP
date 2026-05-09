<?php

namespace App\Services\Telecom;

use App\Models\ErpNotification;
use App\Models\NetworkAlert;
use App\Models\NetworkDevice;
use App\Models\TelecomSubscription;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\WebhookService;
use Illuminate\Support\Facades\Log;

/**
 * Network Alert Service
 *
 * Manages network alerts, notifications, and automated responses
 * for various network events (device offline, quota exceeded, etc.)
 */
class NetworkAlertService
{
    protected NotificationService $notificationService;

    protected WebhookService $webhookService;

    public function __construct(
        NotificationService $notificationService,
        WebhookService $webhookService
    ) {
        $this->notificationService = $notificationService;
        $this->webhookService = $webhookService;
    }

    /**
     * Create alert when device goes offline
     */
    public function handleDeviceOffline(NetworkDevice $device): NetworkAlert
    {
        Log::warning('Device offline detected', [
            'device_id' => $device->id,
            'device_name' => $device->name,
            'ip_address' => $device->ip_address,
        ]);

        // Count affected subscriptions
        $affectedSubscriptions = TelecomSubscription::where('device_id', $device->id)
            ->where('status', 'active')
            ->count();

        // Determine severity
        $severity = $this->calculateSeverity($affectedSubscriptions);

        // Create alert record
        $alert = NetworkAlert::create([
            'tenant_id' => $device->tenant_id,
            'device_id' => $device->id,
            'type' => 'device_offline',
            'severity' => $severity,
            'title' => "Device Offline: {$device->name}",
            'message' => "Device {$device->name} ({$device->ip_address}) has been offline. {$affectedSubscriptions} active subscriptions affected.",
            'metadata' => [
                'device_name' => $device->name,
                'ip_address' => $device->ip_address,
                'brand' => $device->brand,
                'affected_subscriptions' => $affectedSubscriptions,
                'last_seen_at' => $device->last_seen_at?->toISOString(),
            ],
            'status' => 'active',
            'triggered_at' => now(),
        ]);

        // Send notifications
        $this->sendDeviceOfflineNotifications($device, $alert, $affectedSubscriptions);

        // Trigger webhook
        $this->webhookService->dispatch($device->tenant_id, 'telecom.device_offline', [
            'device_id' => $device->id,
            'device_name' => $device->name,
            'ip_address' => $device->ip_address,
            'brand' => $device->brand,
            'affected_subscriptions' => $affectedSubscriptions,
            'severity' => $severity,
            'alert_id' => $alert->id,
        ]);

        return $alert;
    }

    /**
     * Create alert when device comes back online
     */
    public function handleDeviceOnline(NetworkDevice $device): NetworkAlert
    {
        Log::info('Device back online', [
            'device_id' => $device->id,
            'device_name' => $device->name,
        ]);

        // Resolve any existing offline alerts for this device
        $this->resolveAlertsByType($device->id, 'device_offline');

        // Create recovery alert
        $alert = NetworkAlert::create([
            'tenant_id' => $device->tenant_id,
            'device_id' => $device->id,
            'type' => 'device_online',
            'severity' => 'info',
            'title' => "Device Back Online: {$device->name}",
            'message' => "Device {$device->name} is now back online and operational.",
            'metadata' => [
                'device_name' => $device->name,
                'ip_address' => $device->ip_address,
                'recovered_at' => now()->toISOString(),
            ],
            'status' => 'resolved',
            'triggered_at' => now(),
            'resolved_at' => now(),
        ]);

        // Send recovery notification
        $this->sendNotificationToAdmins($device->tenant_id, 'device.recovered', [
            'device_name' => $device->name,
            'ip_address' => $device->ip_address,
            'downtime_duration' => $this->calculateDowntimeDuration($device),
        ]);

        // Trigger webhook
        $this->webhookService->dispatch($device->tenant_id, 'telecom.device_online', [
            'device_id' => $device->id,
            'device_name' => $device->name,
            'alert_id' => $alert->id,
        ]);

        return $alert;
    }

    /**
     * Handle quota exceeded event
     */
    public function handleQuotaExceeded(TelecomSubscription $subscription): NetworkAlert
    {
        Log::warning('Quota exceeded', [
            'subscription_id' => $subscription->id,
            'customer_id' => $subscription->customer_id,
            'usage_bytes' => $subscription->current_usage_bytes,
            'quota_bytes' => $subscription->package->quota_bytes,
        ]);

        $customer = $subscription->customer;
        $package = $subscription->package;

        // Create alert
        $alert = NetworkAlert::create([
            'tenant_id' => $subscription->tenant_id,
            'subscription_id' => $subscription->id,
            'customer_id' => $subscription->customer_id,
            'type' => 'quota_exceeded',
            'severity' => 'high',
            'title' => "Quota Exceeded: {$customer->name}",
            'message' => "Customer {$customer->name} has exceeded their {$package->name} quota.",
            'metadata' => [
                'customer_name' => $customer->name,
                'package_name' => $package->name,
                'quota_bytes' => $package->quota_bytes,
                'used_bytes' => $subscription->current_usage_bytes,
                'usage_percentage' => round(($subscription->current_usage_bytes / $package->quota_bytes) * 100, 2),
            ],
            'status' => 'active',
            'triggered_at' => now(),
        ]);

        // Notify customer
        $this->sendNotificationToCustomer($customer, 'quota.exceeded', [
            'package_name' => $package->name,
            'usage_percentage' => 100,
            'action_required' => true,
        ]);

        // Notify admin
        $this->sendNotificationToAdmins($subscription->tenant_id, 'quota.exceeded.admin', [
            'customer_name' => $customer->name,
            'package_name' => $package->name,
            'subscription_id' => $subscription->id,
        ]);

        // Trigger webhook
        $this->webhookService->dispatch($subscription->tenant_id, 'telecom.quota_exceeded', [
            'subscription_id' => $subscription->id,
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'package_name' => $package->name,
            'quota_bytes' => $package->quota_bytes,
            'used_bytes' => $subscription->current_usage_bytes,
            'alert_id' => $alert->id,
        ]);

        return $alert;
    }

    /**
     * Handle quota warning (80% threshold)
     */
    public function handleQuotaWarning(TelecomSubscription $subscription, int $threshold = 80): NetworkAlert
    {
        $customer = $subscription->customer;
        $package = $subscription->package;

        Log::info('Quota warning', [
            'subscription_id' => $subscription->id,
            'threshold' => $threshold,
        ]);

        // Check if warning already sent in current period
        $existingWarning = NetworkAlert::where('subscription_id', $subscription->id)
            ->where('type', 'quota_warning')
            ->whereDate('triggered_at', '>=', now()->startOfMonth())
            ->first();

        if ($existingWarning) {
            return $existingWarning; // Don't send duplicate warnings
        }

        // Create warning alert
        $alert = NetworkAlert::create([
            'tenant_id' => $subscription->tenant_id,
            'subscription_id' => $subscription->id,
            'customer_id' => $subscription->customer_id,
            'type' => 'quota_warning',
            'severity' => 'medium',
            'title' => "Quota Warning: {$customer->name}",
            'message' => "Customer {$customer->name} has used {$threshold}% of their {$package->name} quota.",
            'metadata' => [
                'customer_name' => $customer->name,
                'package_name' => $package->name,
                'threshold' => $threshold,
                'quota_bytes' => $package->quota_bytes,
                'used_bytes' => $subscription->current_usage_bytes,
            ],
            'status' => 'active',
            'triggered_at' => now(),
        ]);

        // Notify customer
        $this->sendNotificationToCustomer($customer, 'quota.warning', [
            'package_name' => $package->name,
            'usage_percentage' => $threshold,
            'upgrade_available' => true,
        ]);

        // Trigger webhook
        $this->webhookService->dispatch($subscription->tenant_id, 'telecom.quota_warning', [
            'subscription_id' => $subscription->id,
            'customer_id' => $customer->id,
            'threshold' => $threshold,
            'alert_id' => $alert->id,
        ]);

        return $alert;
    }

    /**
     * Get active alerts for tenant
     */
    public function getActiveAlerts(int $tenantId, array $filters = []): array
    {
        $query = NetworkAlert::where('tenant_id', $tenantId)
            ->where('status', 'active');

        // Apply filters
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['severity'])) {
            $query->where('severity', $filters['severity']);
        }

        if (isset($filters['device_id'])) {
            $query->where('device_id', $filters['device_id']);
        }

        return $query->orderBy('severity', 'desc')
            ->orderBy('triggered_at', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Resolve alert by ID
     */
    public function resolveAlert(int $alertId, int $tenantId): bool
    {
        $alert = NetworkAlert::where('id', $alertId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (! $alert) {
            return false;
        }

        $alert->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolution_notes' => 'Manually resolved by admin',
        ]);

        Log::info('Alert resolved', ['alert_id' => $alertId]);

        return true;
    }

    /**
     * Resolve all alerts of a specific type for a device
     */
    protected function resolveAlertsByType(int $deviceId, string $type): void
    {
        NetworkAlert::where('device_id', $deviceId)
            ->where('type', $type)
            ->where('status', 'active')
            ->update([
                'status' => 'resolved',
                'resolved_at' => now(),
            ]);
    }

    /**
     * Calculate severity based on impact
     */
    protected function calculateSeverity(int $affectedSubscriptions): string
    {
        if ($affectedSubscriptions > 10) {
            return 'critical';
        } elseif ($affectedSubscriptions > 5) {
            return 'high';
        } elseif ($affectedSubscriptions > 0) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Send notifications for device offline
     */
    protected function sendDeviceOfflineNotifications(
        NetworkDevice $device,
        NetworkAlert $alert,
        int $affectedSubscriptions
    ): void {
        // Notify admins
        $this->sendNotificationToAdmins($device->tenant_id, 'device.offline', [
            'device_name' => $device->name,
            'ip_address' => $device->ip_address,
            'affected_subscriptions' => $affectedSubscriptions,
            'severity' => $alert->severity,
            'alert_id' => $alert->id,
        ]);

        // If critical severity, also notify via SMS (placeholder)
        if ($alert->severity === 'critical') {
            Log::critical("CRITICAL ALERT: {$device->name} is OFFLINE! {$affectedSubscriptions} customers affected.");
            // TODO: Implement SMS notification when SMS service is available
        }
    }

    /**
     * Calculate downtime duration
     */
    protected function calculateDowntimeDuration(NetworkDevice $device): string
    {
        if (! $device->last_seen_at) {
            return 'Unknown';
        }

        $downtime = now()->diff($device->last_seen_at);

        if ($downtime->days > 0) {
            return "{$downtime->days} days, {$downtime->h} hours";
        } elseif ($downtime->h > 0) {
            return "{$downtime->h} hours, {$downtime->i} minutes";
        }

        return "{$downtime->i} minutes";
    }

    /**
     * Helper: Send notification to customer
     */
    protected function sendNotificationToCustomer($customer, string $type, array $data): void
    {
        // Create in-app notification for customer
        ErpNotification::create([
            'tenant_id' => $customer->tenant_id,
            'user_id' => $customer->user_id ?? null,
            'type' => $type,
            'module' => 'telecom',
            'title' => $this->getNotificationTitle($type, $data),
            'body' => $this->getNotificationBody($type, $data),
            'data' => $data,
            'is_read' => false,
        ]);

        Log::info('Notification sent to customer', [
            'customer_id' => $customer->id,
            'type' => $type,
        ]);
    }

    /**
     * Helper: Send notification to admins
     */
    protected function sendNotificationToAdmins(int $tenantId, string $type, array $data): void
    {
        // Get all admin users for tenant
        $admins = User::where('tenant_id', $tenantId)
            ->whereIn('role', ['admin', 'manager'])
            ->pluck('id');

        foreach ($admins as $adminId) {
            ErpNotification::create([
                'tenant_id' => $tenantId,
                'user_id' => $adminId,
                'type' => $type,
                'module' => 'telecom',
                'title' => $this->getNotificationTitle($type, $data),
                'body' => $this->getNotificationBody($type, $data),
                'data' => $data,
                'is_read' => false,
            ]);
        }

        Log::info('Notification sent to admins', [
            'tenant_id' => $tenantId,
            'type' => $type,
            'admin_count' => $admins->count(),
        ]);
    }

    /**
     * Get notification title based on type
     */
    protected function getNotificationTitle(string $type, array $data): string
    {
        return match ($type) {
            'device.offline' => "🔴 Device Offline: {$data['device_name']}",
            'device.recovered' => "✅ Device Back Online: {$data['device_name']}",
            'quota.exceeded' => '⚠️ Quota Exceeded',
            'quota.exceeded.admin' => "🚨 Customer Quota Exceeded: {$data['customer_name']}",
            'quota.warning' => "⚡ Quota Warning: {$data['usage_percentage']}% Used",
            default => 'Telecom Alert',
        };
    }

    /**
     * Get notification body based on type
     */
    protected function getNotificationBody(string $type, array $data): string
    {
        return match ($type) {
            'device.offline' => "Device {$data['device_name']} ({$data['ip_address']}) is offline. {$data['affected_subscriptions']} subscriptions affected.",
            'device.recovered' => "Device {$data['device_name']} is back online after {$data['downtime_duration']} downtime.",
            'quota.exceeded' => "You have exceeded your {$data['package_name']} quota. Please upgrade or wait for next billing cycle.",
            'quota.exceeded.admin' => "Customer {$data['customer_name']} has exceeded their {$data['package_name']} quota (Subscription #{$data['subscription_id']}).",
            'quota.warning' => "You have used {$data['usage_percentage']}% of your {$data['package_name']} quota. Consider upgrading.",
            default => 'Network alert triggered.',
        };
    }

    /**
     * Get alert statistics
     */
    public function getAlertStatistics(int $tenantId, string $period = 'month'): array
    {
        $startDate = match ($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $alerts = NetworkAlert::where('tenant_id', $tenantId)
            ->where('triggered_at', '>=', $startDate)
            ->get();

        return [
            'total_alerts' => $alerts->count(),
            'active_alerts' => $alerts->where('status', 'active')->count(),
            'resolved_alerts' => $alerts->where('status', 'resolved')->count(),
            'by_type' => $alerts->groupBy('type')->map->count(),
            'by_severity' => $alerts->groupBy('severity')->map->count(),
            'most_affected_device' => $alerts->whereNotNull('device_id')
                ->groupBy('device_id')
                ->map->count()
                ->sortDesc()
                ->first(),
        ];
    }
}
