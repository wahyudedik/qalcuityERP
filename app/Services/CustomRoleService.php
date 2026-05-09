<?php

namespace App\Services;

use App\Models\CustomRole;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomRoleService
{
    /**
     * Reserved role names — hardcoded system and tenant roles.
     */
    private const RESERVED_NAMES = [
        'super_admin',
        'admin',
        'affiliate',
        'manager',
        'staff',
        'kasir',
        'gudang',
        'housekeeping',
        'maintenance',
    ];

    /**
     * Create a new custom role for a tenant.
     */
    public function createRole(int $tenantId, string $name, ?string $description, int $createdBy): CustomRole
    {
        return DB::transaction(function () use ($tenantId, $name, $description, $createdBy) {
            $role = CustomRole::create([
                'tenant_id' => $tenantId,
                'name' => $name,
                'slug' => $this->generateUniqueSlug($tenantId, $name),
                'description' => $description,
                'is_active' => true,
                'created_by' => $createdBy,
                'updated_by' => $createdBy,
            ]);

            return $role;
        });
    }

    /**
     * Update an existing custom role.
     */
    public function updateRole(CustomRole $role, array $data, int $updatedBy): CustomRole
    {
        return DB::transaction(function () use ($role, $data, $updatedBy) {
            $updateData = array_merge(
                array_intersect_key($data, array_flip(['name', 'description', 'is_active'])),
                ['updated_by' => $updatedBy]
            );

            // Regenerate slug if name changed
            if (isset($data['name']) && $data['name'] !== $role->name) {
                $updateData['slug'] = $this->generateUniqueSlug($role->tenant_id, $data['name'], $role->id);
            }

            $role->update($updateData);

            $this->invalidateRoleCache($role);

            return $role->fresh();
        });
    }

    /**
     * Delete a custom role. Throws exception if users are still assigned.
     *
     * @throws \RuntimeException
     */
    public function deleteRole(CustomRole $role): bool
    {
        $userCount = $role->userCount();

        if ($userCount > 0) {
            throw new \RuntimeException(
                "Tidak dapat menghapus role \"{$role->name}\" karena masih digunakan oleh {$userCount} user. "
                    . 'Silakan pindahkan user ke role lain terlebih dahulu.'
            );
        }

        return DB::transaction(function () use ($role) {
            $this->invalidateRoleCache($role);

            // Permissions will cascade delete via FK, but explicit delete for cache clarity
            $role->permissions()->delete();
            $role->delete();

            return true;
        });
    }

    /**
     * Sync permissions for a custom role (delete existing + insert new) in a transaction.
     *
     * @param  array  $permissions  Array of ['module' => string, 'action' => string, 'granted' => bool]
     */
    public function syncPermissions(CustomRole $role, array $permissions): void
    {
        DB::transaction(function () use ($role, $permissions) {
            // Delete existing permissions
            $role->permissions()->delete();

            // Insert new permissions
            $records = [];
            foreach ($permissions as $perm) {
                $module = $perm['module'] ?? null;
                $action = $perm['action'] ?? null;
                $granted = $perm['granted'] ?? true;

                // Validate module and action exist
                if (! $module || ! $action) {
                    continue;
                }

                if (! isset(PermissionService::MODULES[$module])) {
                    continue;
                }

                if (! in_array($action, PermissionService::MODULES[$module])) {
                    continue;
                }

                $records[] = [
                    'tenant_id' => $role->tenant_id,
                    'custom_role_id' => $role->id,
                    'module' => $module,
                    'action' => $action,
                    'granted' => (bool) $granted,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (! empty($records)) {
                RolePermission::insert($records);
            }

            $this->invalidateRoleCache($role);
        });
    }

    /**
     * Clone an existing custom role with a new name.
     */
    public function cloneRole(CustomRole $source, string $newName, int $createdBy): CustomRole
    {
        return DB::transaction(function () use ($source, $newName, $createdBy) {
            $newRole = $this->createRole($source->tenant_id, $newName, $source->description, $createdBy);

            // Copy permissions from source
            $permissions = $source->permissions->map(fn($p) => [
                'module' => $p->module,
                'action' => $p->action,
                'granted' => $p->granted,
            ])->toArray();

            if (! empty($permissions)) {
                $this->syncPermissions($newRole, $permissions);
            }

            return $newRole;
        });
    }

    /**
     * Clone from a hardcoded role, creating a custom role with the same default permissions.
     */
    public function cloneFromHardcodedRole(int $tenantId, string $hardcodedRole, string $newName, int $createdBy): CustomRole
    {
        $defaults = PermissionService::ROLE_DEFAULTS[$hardcodedRole] ?? [];

        return DB::transaction(function () use ($tenantId, $newName, $createdBy, $defaults, $hardcodedRole) {
            $description = "Dibuat dari role \"{$hardcodedRole}\"";
            $newRole = $this->createRole($tenantId, $newName, $description, $createdBy);

            $permissions = [];

            if ($defaults === '*') {
                // Admin gets all permissions
                foreach (PermissionService::MODULES as $module => $actions) {
                    foreach ($actions as $action) {
                        $permissions[] = [
                            'module' => $module,
                            'action' => $action,
                            'granted' => true,
                        ];
                    }
                }
            } else {
                foreach ($defaults as $module => $actions) {
                    foreach ($actions as $action) {
                        $permissions[] = [
                            'module' => $module,
                            'action' => $action,
                            'granted' => true,
                        ];
                    }
                }
            }

            if (! empty($permissions)) {
                $this->syncPermissions($newRole, $permissions);
            }

            return $newRole;
        });
    }

    /**
     * Get permissions for a custom role (cached).
     *
     * @return array ['module.action' => bool]
     */
    public function getRolePermissions(CustomRole $role): array
    {
        $cacheKey = "custom_role_perms:{$role->id}";

        try {
            return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($role) {
                return $this->loadRolePermissionsFromDb($role);
            });
        } catch (\Exception $e) {
            // Graceful fallback if cache unavailable
            return $this->loadRolePermissionsFromDb($role);
        }
    }

    /**
     * Reassign users from one custom role to another. Returns count of affected users.
     */
    public function reassignUsers(CustomRole $from, CustomRole $to): int
    {
        $oldRoleValue = 'custom:' . $from->id;
        $newRoleValue = 'custom:' . $to->id;

        $affected = User::where('tenant_id', $from->tenant_id)
            ->where('role', $oldRoleValue)
            ->update(['role' => $newRoleValue]);

        // Invalidate cache for all affected users
        User::where('tenant_id', $from->tenant_id)
            ->where('role', $newRoleValue)
            ->pluck('id')
            ->each(function ($userId) {
                $this->invalidateUserPermissionCache($userId);
            });

        return $affected;
    }

    /**
     * Validate a role name for a tenant.
     * Returns true if the name is valid and available.
     */
    public function validateRoleName(int $tenantId, string $name, ?int $excludeId = null): bool
    {
        // Check length
        if (mb_strlen($name) < 3 || mb_strlen($name) > 50) {
            return false;
        }

        // Check reserved names (case-insensitive)
        $normalizedName = strtolower(trim($name));
        if (in_array($normalizedName, self::RESERVED_NAMES)) {
            return false;
        }

        // Check uniqueness within tenant
        $query = CustomRole::where('tenant_id', $tenantId)
            ->where('name', $name);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return ! $query->exists();
    }

    // ─── Private Helpers ──────────────────────────────────────────

    /**
     * Generate a unique slug for a custom role within a tenant.
     */
    private function generateUniqueSlug(int $tenantId, string $name, ?int $excludeId = null): string
    {
        $baseSlug = Str::slug($name);

        if (empty($baseSlug)) {
            $baseSlug = 'role';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (true) {
            $query = CustomRole::where('tenant_id', $tenantId)
                ->where('slug', $slug);

            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            if (! $query->exists()) {
                break;
            }

            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Invalidate all caches related to a custom role.
     */
    private function invalidateRoleCache(CustomRole $role): void
    {
        // Invalidate role permissions cache
        Cache::forget("custom_role_perms:{$role->id}");

        // Invalidate permission cache for all users with this role
        $roleValue = 'custom:' . $role->id;
        User::where('tenant_id', $role->tenant_id)
            ->where('role', $roleValue)
            ->pluck('id')
            ->each(function ($userId) {
                $this->invalidateUserPermissionCache($userId);
            });
    }

    /**
     * Invalidate user permission cache.
     */
    private function invalidateUserPermissionCache(int $userId): void
    {
        Cache::forget("user_perms_v3:{$userId}");
        Cache::forget("user_perms_v2:{$userId}");
        Cache::forget("user_overrides:{$userId}");
    }

    /**
     * Load role permissions directly from database.
     */
    private function loadRolePermissionsFromDb(CustomRole $role): array
    {
        $permissions = [];

        $role->permissions()
            ->where('granted', true)
            ->get(['module', 'action', 'granted'])
            ->each(function ($perm) use (&$permissions) {
                $permissions["{$perm->module}.{$perm->action}"] = (bool) $perm->granted;
            });

        return $permissions;
    }
}
