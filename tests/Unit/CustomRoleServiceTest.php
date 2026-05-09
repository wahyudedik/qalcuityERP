<?php

namespace Tests\Unit;

use App\Models\CustomRole;
use App\Models\RolePermission;
use App\Services\CustomRoleService;
use App\Services\PermissionService;
use Tests\TestCase;

class CustomRoleServiceTest extends TestCase
{
    private $tenant;

    private $admin;

    private CustomRoleService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->createTenant();
        $this->admin = $this->createAdminUser($this->tenant);
        $this->service = app(CustomRoleService::class);
    }

    // ── createRole with slug generation ───────────────────────────

    public function test_create_role_generates_slug_from_name(): void
    {
        $role = $this->service->createRole(
            $this->tenant->id,
            'Supervisor Gudang',
            'Deskripsi role',
            $this->admin->id
        );

        $this->assertEquals('supervisor-gudang', $role->slug);
        $this->assertEquals('Supervisor Gudang', $role->name);
        $this->assertEquals($this->tenant->id, $role->tenant_id);
        $this->assertEquals($this->admin->id, $role->created_by);
        $this->assertTrue($role->is_active);
    }

    public function test_create_role_generates_unique_slug_on_collision(): void
    {
        // Create first role
        $this->service->createRole($this->tenant->id, 'Manager Toko', null, $this->admin->id);

        // Create second role with same name (different tenant would allow, but same tenant slug collision)
        $otherTenant = $this->createTenant(['slug' => 'other-' . uniqid()]);
        $otherAdmin = $this->createAdminUser($otherTenant);

        $role2 = $this->service->createRole($otherTenant->id, 'Manager Toko', null, $otherAdmin->id);

        // Same slug is fine for different tenants
        $this->assertEquals('manager-toko', $role2->slug);

        // Same tenant, same name slug should get suffix
        $role3 = $this->service->createRole($this->tenant->id, 'Manager Toko', 'Another one', $this->admin->id);

        $this->assertStringStartsWith('manager-toko', $role3->slug);
        $this->assertNotEquals('manager-toko', $role3->slug);
    }

    // ── validateRoleName ──────────────────────────────────────────

    public function test_validate_role_name_rejects_hardcoded_names(): void
    {
        $this->assertFalse($this->service->validateRoleName($this->tenant->id, 'admin'));
        $this->assertFalse($this->service->validateRoleName($this->tenant->id, 'super_admin'));
        $this->assertFalse($this->service->validateRoleName($this->tenant->id, 'staff'));
        $this->assertFalse($this->service->validateRoleName($this->tenant->id, 'kasir'));
        $this->assertFalse($this->service->validateRoleName($this->tenant->id, 'gudang'));
        $this->assertFalse($this->service->validateRoleName($this->tenant->id, 'manager'));
    }

    public function test_validate_role_name_rejects_hardcoded_names_case_insensitive(): void
    {
        $this->assertFalse($this->service->validateRoleName($this->tenant->id, 'Admin'));
        $this->assertFalse($this->service->validateRoleName($this->tenant->id, 'STAFF'));
        $this->assertFalse($this->service->validateRoleName($this->tenant->id, 'Kasir'));
    }

    public function test_validate_role_name_rejects_duplicates_within_tenant(): void
    {
        $this->service->createRole($this->tenant->id, 'Existing Role', null, $this->admin->id);

        $this->assertFalse($this->service->validateRoleName($this->tenant->id, 'Existing Role'));
    }

    public function test_validate_role_name_allows_same_name_when_excluding_self(): void
    {
        $role = $this->service->createRole($this->tenant->id, 'My Role', null, $this->admin->id);

        // Should be valid when excluding the role's own ID (for updates)
        $this->assertTrue($this->service->validateRoleName($this->tenant->id, 'My Role', $role->id));
    }

    public function test_validate_role_name_rejects_too_short(): void
    {
        $this->assertFalse($this->service->validateRoleName($this->tenant->id, 'ab'));
    }

    public function test_validate_role_name_rejects_too_long(): void
    {
        $longName = str_repeat('a', 51);
        $this->assertFalse($this->service->validateRoleName($this->tenant->id, $longName));
    }

    public function test_validate_role_name_accepts_valid_name(): void
    {
        $this->assertTrue($this->service->validateRoleName($this->tenant->id, 'Supervisor Gudang'));
        $this->assertTrue($this->service->validateRoleName($this->tenant->id, 'Kepala Divisi IT'));
    }

    // ── syncPermissions ───────────────────────────────────────────

    public function test_sync_permissions_stores_valid_permissions(): void
    {
        $role = $this->service->createRole($this->tenant->id, 'Sync Test', null, $this->admin->id);

        $this->service->syncPermissions($role, [
            ['module' => 'customers', 'action' => 'view', 'granted' => true],
            ['module' => 'customers', 'action' => 'create', 'granted' => true],
            ['module' => 'sales', 'action' => 'view', 'granted' => true],
        ]);

        $this->assertEquals(3, $role->permissions()->count());

        $this->assertDatabaseHas('role_permissions', [
            'custom_role_id' => $role->id,
            'module' => 'customers',
            'action' => 'view',
            'granted' => true,
        ]);
    }

    public function test_sync_permissions_replaces_existing(): void
    {
        $role = $this->service->createRole($this->tenant->id, 'Replace Test', null, $this->admin->id);

        // Initial sync
        $this->service->syncPermissions($role, [
            ['module' => 'customers', 'action' => 'view', 'granted' => true],
            ['module' => 'customers', 'action' => 'create', 'granted' => true],
        ]);

        $this->assertEquals(2, $role->permissions()->count());

        // Replace with new set
        $this->service->syncPermissions($role, [
            ['module' => 'sales', 'action' => 'view', 'granted' => true],
        ]);

        $this->assertEquals(1, $role->permissions()->count());

        $this->assertDatabaseMissing('role_permissions', [
            'custom_role_id' => $role->id,
            'module' => 'customers',
        ]);

        $this->assertDatabaseHas('role_permissions', [
            'custom_role_id' => $role->id,
            'module' => 'sales',
            'action' => 'view',
        ]);
    }

    public function test_sync_permissions_skips_invalid_modules(): void
    {
        $role = $this->service->createRole($this->tenant->id, 'Invalid Module', null, $this->admin->id);

        $this->service->syncPermissions($role, [
            ['module' => 'nonexistent_module', 'action' => 'view', 'granted' => true],
            ['module' => 'customers', 'action' => 'view', 'granted' => true],
        ]);

        // Only valid permission should be stored
        $this->assertEquals(1, $role->permissions()->count());
    }

    public function test_sync_permissions_skips_invalid_actions(): void
    {
        $role = $this->service->createRole($this->tenant->id, 'Invalid Action', null, $this->admin->id);

        $this->service->syncPermissions($role, [
            ['module' => 'customers', 'action' => 'nonexistent_action', 'granted' => true],
            ['module' => 'customers', 'action' => 'view', 'granted' => true],
        ]);

        $this->assertEquals(1, $role->permissions()->count());
    }

    // ── cloneRole ─────────────────────────────────────────────────

    public function test_clone_role_copies_permissions(): void
    {
        $source = $this->service->createRole($this->tenant->id, 'Source', null, $this->admin->id);

        $this->service->syncPermissions($source, [
            ['module' => 'customers', 'action' => 'view', 'granted' => true],
            ['module' => 'customers', 'action' => 'create', 'granted' => true],
            ['module' => 'sales', 'action' => 'view', 'granted' => true],
        ]);

        $cloned = $this->service->cloneRole($source, 'Cloned Role', $this->admin->id);

        $this->assertEquals('Cloned Role', $cloned->name);
        $this->assertEquals($this->tenant->id, $cloned->tenant_id);
        $this->assertEquals(3, $cloned->permissions()->count());

        $this->assertDatabaseHas('role_permissions', [
            'custom_role_id' => $cloned->id,
            'module' => 'customers',
            'action' => 'view',
            'granted' => true,
        ]);
    }

    // ── cloneFromHardcodedRole ────────────────────────────────────

    public function test_clone_from_hardcoded_role_copies_defaults(): void
    {
        $role = $this->service->cloneFromHardcodedRole(
            $this->tenant->id,
            'kasir',
            'Custom Kasir',
            $this->admin->id
        );

        $this->assertEquals('Custom Kasir', $role->name);
        $this->assertEquals($this->tenant->id, $role->tenant_id);

        // Kasir defaults include pos.view and pos.create
        $this->assertDatabaseHas('role_permissions', [
            'custom_role_id' => $role->id,
            'module' => 'pos',
            'action' => 'view',
            'granted' => true,
        ]);

        $this->assertDatabaseHas('role_permissions', [
            'custom_role_id' => $role->id,
            'module' => 'pos',
            'action' => 'create',
            'granted' => true,
        ]);
    }

    public function test_clone_from_admin_role_grants_all_permissions(): void
    {
        $role = $this->service->cloneFromHardcodedRole(
            $this->tenant->id,
            'admin',
            'Full Access Role',
            $this->admin->id
        );

        // Admin has '*' which means all modules/actions
        $totalExpected = 0;
        foreach (PermissionService::MODULES as $module => $actions) {
            $totalExpected += count($actions);
        }

        $this->assertEquals($totalExpected, $role->permissions()->count());
    }

    public function test_clone_from_nonexistent_hardcoded_role_creates_empty(): void
    {
        $role = $this->service->cloneFromHardcodedRole(
            $this->tenant->id,
            'nonexistent_role',
            'Empty Role',
            $this->admin->id
        );

        $this->assertEquals('Empty Role', $role->name);
        $this->assertEquals(0, $role->permissions()->count());
    }
}
