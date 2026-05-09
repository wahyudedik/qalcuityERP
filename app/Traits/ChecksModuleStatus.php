<?php

namespace App\Traits;

/**
 * Trait ChecksModuleStatus
 *
 * Provides helper methods for notifications to check if a module is active
 * for a tenant before sending notifications.
 */
trait ChecksModuleStatus
{
    /**
     * Check if the module is active for the notifiable user's tenant.
     *
     * @param  object  $notifiable  The user being notified
     * @param  string  $module  The module key (e.g., 'purchasing', 'hrm', 'pos')
     */
    protected function isModuleActiveForTenant(object $notifiable, string $module): bool
    {
        // If notifiable doesn't have a tenant, allow notification (e.g., SuperAdmin)
        if (! $notifiable->tenant) {
            return true;
        }

        return $notifiable->tenant->isModuleEnabled($module);
    }

    /**
     * Get the module key for this notification.
     * Override this method in notification classes to specify the module.
     */
    protected function getModuleKey(): ?string
    {
        return null;
    }

    /**
     * Filter channels based on module status.
     * If module is disabled, return empty array to prevent notification.
     */
    protected function filterChannelsByModuleStatus(object $notifiable, array $channels): array
    {
        $moduleKey = $this->getModuleKey();

        // If no module key specified, allow all channels
        if (! $moduleKey) {
            return $channels;
        }

        // If module is disabled, return empty array (no notification)
        if (! $this->isModuleActiveForTenant($notifiable, $moduleKey)) {
            return [];
        }

        return $channels;
    }
}
