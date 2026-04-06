<?php

namespace App\Services\Security;

use App\Models\Permission;
use App\Models\Role;

class PermissionService
{
    /**
     * Check if user has permission
     */
    public function hasPermission($user, string $permission): bool
    {
        // If user is admin/superadmin, grant all permissions
        if ($this->isAdmin($user)) {
            return true;
        }

        // Get user's role
        $role = $user->role ?? null;

        if (!$role) {
            return false;
        }

        // Check if role has permission
        return $role->permissions()->where('name', $permission)->exists();
    }

    /**
     * Check if user has any of the permissions
     */
    public function hasAnyPermission($user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($user, $permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all permissions
     */
    public function hasAllPermissions($user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($user, $permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Create new permission
     */
    public function createPermission(string $name, string $group, ?string $description = null): Permission
    {
        return Permission::create([
            'name' => $name,
            'group' => $group,
            'description' => $description,
            'is_system' => false,
        ]);
    }

    /**
     * Assign permission to role
     */
    public function assignPermissionToRole(int $roleId, int $permissionId): bool
    {
        try {
            $role = Role::findOrFail($roleId);
            $permission = Permission::findOrFail($permissionId);

            $role->permissions()->syncWithoutDetaching([$permissionId]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Assign permission failed', [
                'role_id' => $roleId,
                'permission_id' => $permissionId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Remove permission from role
     */
    public function removePermissionFromRole(int $roleId, int $permissionId): bool
    {
        try {
            $role = Role::findOrFail($roleId);
            $permission = Permission::findOrFail($permissionId);

            $role->permissions()->detach([$permissionId]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Remove permission failed', [
                'role_id' => $roleId,
                'permission_id' => $permissionId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Sync role permissions
     */
    public function syncRolePermissions(int $roleId, array $permissionIds): bool
    {
        try {
            $role = Role::findOrFail($roleId);
            $role->permissions()->sync($permissionIds);

            return true;
        } catch (\Exception $e) {
            \Log::error('Sync permissions failed', [
                'role_id' => $roleId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get all permissions grouped by category
     */
    public function getGroupedPermissions(): array
    {
        return Permission::all()
            ->groupBy('group')
            ->map(function ($permissions) {
                return $permissions->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'description' => $permission->description,
                    ];
                });
            })
            ->toArray();
    }

    /**
     * Get role permissions
     */
    public function getRolePermissions(int $roleId): array
    {
        $role = Role::with('permissions')->findOrFail($roleId);

        return $role->permissions->pluck('name')->toArray();
    }

    /**
     * Check if user is admin
     */
    protected function isAdmin($user): bool
    {
        return in_array($user->role_name ?? '', ['admin', 'superadmin']);
    }

    /**
     * Seed default permissions
     */
    public function seedDefaultPermissions(): void
    {
        $defaultPermissions = [
            // User Management
            ['users.view', 'users', 'View users'],
            ['users.create', 'users', 'Create users'],
            ['users.edit', 'users', 'Edit users'],
            ['users.delete', 'users', 'Delete users'],
            ['users.manage_roles', 'users', 'Manage user roles'],

            // Invoices
            ['invoices.view', 'invoices', 'View invoices'],
            ['invoices.create', 'invoices', 'Create invoices'],
            ['invoices.edit', 'invoices', 'Edit invoices'],
            ['invoices.delete', 'invoices', 'Delete invoices'],
            ['invoices.approve', 'invoices', 'Approve invoices'],

            // Products
            ['products.view', 'products', 'View products'],
            ['products.create', 'products', 'Create products'],
            ['products.edit', 'products', 'Edit products'],
            ['products.delete', 'products', 'Delete products'],

            // Reports
            ['reports.view', 'reports', 'View reports'],
            ['reports.export', 'reports', 'Export reports'],

            // Settings
            ['settings.view', 'settings', 'View settings'],
            ['settings.edit', 'settings', 'Edit settings'],

            // Security
            ['security.manage_2fa', 'security', 'Manage 2FA'],
            ['security.view_audit_logs', 'security', 'View audit logs'],
            ['security.manage_ip_whitelist', 'security', 'Manage IP whitelist'],
            ['security.view_sessions', 'security', 'View sessions'],
        ];

        foreach ($defaultPermissions as [$name, $group, $description]) {
            Permission::firstOrCreate(
                ['name' => $name],
                [
                    'group' => $group,
                    'description' => $description,
                    'is_system' => true,
                ]
            );
        }
    }
}
