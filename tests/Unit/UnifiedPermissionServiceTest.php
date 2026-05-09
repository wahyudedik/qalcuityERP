<?php

namespace Tests\Unit;

use App\Models\CustomRole;
use App\Models\RolePermission;
use App\Models\User;
use App\Models\UserPermission;
use App\Services\UnifiedPermissionService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class UnifiedPermissionServiceTest extends TestCase
{
    private $tenant;

    private UnifiedPermissionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->createTenant();
        $this->service = app(UnifiedPermissionService::class);
    }

    // ── Permission Priority ───────────────────────────────────────

    public function test_super_admin_always_has_permission(): void
    {
        $user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Super Admin',
            'email' => 'super-' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->assertTrue($this->service->check($user, 'customers', 'delete'));
        $this->assertTrue($this->service->check($user, 'accounting', 'create'));
        $this->assertTrue($this->service->check($user, 'users', 'delete'));
    }

    public function test_admin_always_has_permission(): void
    {
        $user = $this->createAdminUser($this->tenant);

        $this->assertTrue($this->service->check($user, 'customers', 'delete'));
        $this->assertTrue($this->service->check($user, 'accounting', 'create'));
        $this->assertTrue($this->service->check($user, 'users', 'delete'));
    }

    public function test_per_user_override_takes_priority_over_custom_role(): void
    {
        $role = $this->createCustomRoleWithPermissions(['customers.view', 'customers.create']);

        $user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Custom User',
            'email' => 'custom-' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'custom:' . $role->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Override: deny customers.view (even though role grants it)
        UserPermission::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $user->id,
            'module' => 'customers',
            'action' => 'view',
            'granted' => false,
        ]);

        // Override denies it
        $this->assertFalse($this->service->check($user, 'customers', 'view'));

        // Role still grants create (no override)
        $this->assertTrue($this->service->check($user, 'customers', 'create'));
    }

    public function test_per_user_override_can_grant_permission_not_in_role(): void
    {
        $role = $this->createCustomRoleWithPermissions(['customers.view']);

        $user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Custom User',
            'email' => 'custom-' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'custom:' . $role->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Override: grant accounting.view (not in role)
        UserPermission::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $user->id,
            'module' => 'accounting',
            'action' => 'view',
            'granted' => true,
        ]);

        $this->assertTrue($this->service->check($user, 'accounting', 'view'));
    }

    public function test_custom_role_permission_check(): void
    {
        $role = $this->createCustomRoleWithPermissions(['customers.view', 'sales.create']);

        $user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Custom User',
            'email' => 'custom-' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'custom:' . $role->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->assertTrue($this->service->check($user, 'customers', 'view'));
        $this->assertTrue($this->service->check($user, 'sales', 'create'));
        $this->assertFalse($this->service->check($user, 'customers', 'delete'));
        $this->assertFalse($this->service->check($user, 'accounting', 'view'));
    }

    // ── Hardcoded Role Fallback ───────────────────────────────────

    public function test_hardcoded_role_uses_role_defaults(): void
    {
        $user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Staff User',
            'email' => 'staff-' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Staff has dashboard.view
        $this->assertTrue($this->service->check($user, 'dashboard', 'view'));
        // Staff has pos.view and pos.create
        $this->assertTrue($this->service->check($user, 'pos', 'view'));
        $this->assertTrue($this->service->check($user, 'pos', 'create'));
        // Staff does NOT have accounting.create
        $this->assertFalse($this->service->check($user, 'accounting', 'create'));
    }

    public function test_kasir_role_uses_role_defaults(): void
    {
        $user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Kasir User',
            'email' => 'kasir-' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'kasir',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Kasir has pos.view and pos.create
        $this->assertTrue($this->service->check($user, 'pos', 'view'));
        $this->assertTrue($this->service->check($user, 'pos', 'create'));
        // Kasir does NOT have customers.create
        $this->assertFalse($this->service->check($user, 'customers', 'create'));
    }

    // ── Cached vs Uncached ────────────────────────────────────────

    public function test_custom_role_permission_is_cached(): void
    {
        $role = $this->createCustomRoleWithPermissions(['customers.view']);

        $user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Custom User',
            'email' => 'custom-' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'custom:' . $role->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // First call populates cache
        $this->assertTrue($this->service->check($user, 'customers', 'view'));

        // Verify cache key exists
        $this->assertNotNull(Cache::get("custom_role_perms:{$role->id}"));
    }

    // ── Cache Invalidation ────────────────────────────────────────

    public function test_invalidate_user_cache_clears_permission_cache(): void
    {
        $user = $this->createAdminUser($this->tenant);

        // Populate cache
        $this->service->getUserPermissions($user);

        // Verify cache exists
        $this->assertNotNull(Cache::get("user_perms_v3:{$user->id}"));

        // Invalidate
        $this->service->invalidateUserCache($user->id);

        // Verify cache cleared
        $this->assertNull(Cache::get("user_perms_v3:{$user->id}"));
    }

    public function test_get_user_permissions_returns_full_permission_map(): void
    {
        $role = $this->createCustomRoleWithPermissions(['customers.view', 'sales.create']);

        $user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Custom User',
            'email' => 'custom-' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'custom:' . $role->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $permissions = $this->service->getUserPermissions($user);

        $this->assertIsArray($permissions);
        $this->assertTrue($permissions['customers']['view']);
        $this->assertTrue($permissions['sales']['create']);
        $this->assertFalse($permissions['customers']['delete']);
    }

    // ── Helpers ───────────────────────────────────────────────────

    private function createCustomRoleWithPermissions(array $permissionKeys): CustomRole
    {
        $role = CustomRole::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Role ' . uniqid(),
            'slug' => 'test-role-' . uniqid(),
            'is_active' => true,
            'created_by' => null,
        ]);

        foreach ($permissionKeys as $key) {
            [$module, $action] = explode('.', $key);
            RolePermission::create([
                'tenant_id' => $this->tenant->id,
                'custom_role_id' => $role->id,
                'module' => $module,
                'action' => $action,
                'granted' => true,
            ]);
        }

        return $role;
    }
}
