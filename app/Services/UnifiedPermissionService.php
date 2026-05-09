<?php

namespace App\Services;

use App\Models\CustomRole;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Support\Facades\Cache;

class UnifiedPermissionService
{
    /**
     * Cache TTL in minutes.
     */
    private const CACHE_TTL_MINUTES = 10;

    /**
     * Check if a user has a specific permission.
     * Priority: super_admin → admin → per-user override → custom role → hardcoded role
     */
    public function check(User $user, string $module, string $action): bool
    {
        // super_admin bypasses everything
        if ($user->isSuperAdmin()) {
            return true;
        }

        // admin gets everything within their tenant
        if ($user->isAdmin()) {
            return true;
        }

        // Check per-user override
        $override = $this->getUserOverride($user, $module, $action);
        if ($override !== null) {
            return $override;
        }

        // Custom role check
        if ($this->isCustomRole($user)) {
            return $this->checkCustomRolePermission($user, $module, $action);
        }

        // Hardcoded role fallback
        return $this->checkHardcodedRolePermission($user, $module, $action);
    }

    /**
     * Get all permissions for a user (merged: role + overrides).
     * Returns ['module' => ['action' => bool]]
     */
    public function getUserPermissions(User $user): array
    {
        $cacheKey = "user_perms_v3:{$user->id}";

        try {
            return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($user) {
                return $this->buildUserPermissions($user);
            });
        } catch (\Exception $e) {
            // Graceful fallback if cache unavailable
            return $this->buildUserPermissions($user);
        }
    }

    /**
     * Invalidate all cached permissions for a user.
     */
    public function invalidateUserCache(int $userId): void
    {
        Cache::forget("user_perms_v3:{$userId}");
        Cache::forget("user_perms_v2:{$userId}");
        Cache::forget("user_overrides:{$userId}");
    }

    // ─── Private Helpers ──────────────────────────────────────────

    /**
     * Check if user has a custom role.
     */
    private function isCustomRole(User $user): bool
    {
        return str_starts_with($user->role, 'custom:');
    }

    /**
     * Extract custom role ID from user role field.
     */
    private function getCustomRoleId(User $user): ?int
    {
        if (! $this->isCustomRole($user)) {
            return null;
        }

        return (int) substr($user->role, strlen('custom:'));
    }

    /**
     * Check permission against custom role's role_permissions table.
     */
    private function checkCustomRolePermission(User $user, string $module, string $action): bool
    {
        $roleId = $this->getCustomRoleId($user);

        if (! $roleId) {
            return false;
        }

        // Use CustomRoleService to get cached permissions
        $permissions = $this->getCustomRolePermissionsCached($roleId);

        $key = "{$module}.{$action}";

        return $permissions[$key] ?? false;
    }

    /**
     * Check permission against hardcoded role defaults.
     */
    private function checkHardcodedRolePermission(User $user, string $module, string $action): bool
    {
        $defaults = PermissionService::ROLE_DEFAULTS[$user->role] ?? [];

        if ($defaults === '*') {
            return true;
        }

        return in_array($action, $defaults[$module] ?? []);
    }

    /**
     * Get per-user override (null = no override set).
     */
    private function getUserOverride(User $user, string $module, string $action): ?bool
    {
        $overrides = $this->getUserOverridesCached($user);

        foreach ($overrides as $override) {
            if ($override['module'] === $module && $override['action'] === $action) {
                return $override['granted'];
            }
        }

        return null;
    }

    /**
     * Get cached user overrides.
     * Caches under both `user_overrides:{id}` (primary) and `user_perms_v2:{id}` (backward compat).
     */
    private function getUserOverridesCached(User $user): array
    {
        $primaryKey = "user_overrides:{$user->id}";
        $legacyKey = "user_perms_v2:{$user->id}";

        try {
            return Cache::remember($primaryKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($user, $legacyKey) {
                $overrides = UserPermission::where('user_id', $user->id)
                    ->get()
                    ->map(fn($p) => [
                        'module' => $p->module,
                        'action' => $p->action,
                        'granted' => (bool) $p->granted,
                    ])
                    ->toArray();

                // Also cache under legacy key for backward compatibility
                Cache::put($legacyKey, $overrides, now()->addMinutes(self::CACHE_TTL_MINUTES));

                return $overrides;
            });
        } catch (\Exception $e) {
            // Graceful fallback if cache unavailable
            return UserPermission::where('user_id', $user->id)
                ->get()
                ->map(fn($p) => [
                    'module' => $p->module,
                    'action' => $p->action,
                    'granted' => (bool) $p->granted,
                ])
                ->toArray();
        }
    }

    /**
     * Get cached custom role permissions by role ID.
     */
    private function getCustomRolePermissionsCached(int $roleId): array
    {
        $cacheKey = "custom_role_perms:{$roleId}";

        try {
            return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($roleId) {
                return $this->loadCustomRolePermissions($roleId);
            });
        } catch (\Exception $e) {
            // Graceful fallback if cache unavailable
            return $this->loadCustomRolePermissions($roleId);
        }
    }

    /**
     * Load custom role permissions from database.
     */
    private function loadCustomRolePermissions(int $roleId): array
    {
        $role = CustomRole::find($roleId);

        if (! $role || ! $role->is_active) {
            return [];
        }

        $permissions = [];

        $role->permissions()
            ->where('granted', true)
            ->get(['module', 'action', 'granted'])
            ->each(function ($perm) use (&$permissions) {
                $permissions["{$perm->module}.{$perm->action}"] = true;
            });

        return $permissions;
    }

    /**
     * Build the full permission map for a user (uncached).
     */
    private function buildUserPermissions(User $user): array
    {
        $result = [];

        foreach (PermissionService::MODULES as $module => $actions) {
            foreach ($actions as $action) {
                $result[$module][$action] = $this->check($user, $module, $action);
            }
        }

        return $result;
    }
}
