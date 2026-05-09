<?php

namespace Tests\Feature;

use App\Models\CustomRole;
use App\Models\RolePermission;
use App\Models\User;
use App\Services\CustomRoleService;
use App\Services\PermissionService;
use Tests\TestCase;

class CustomRoleTest extends TestCase
{
    private $tenant;

    private $admin;

    private CustomRoleService $roleService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->createTenant();
        $this->admin = $this->createAdminUser($this->tenant);
        $this->roleService = app(CustomRoleService::class);
    }

    // ── CRUD Operations ───────────────────────────────────────────

    public function test_admin_can_view_roles_index(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('tenant.roles.index'));

        $response->assertStatus(200);
    }

    public function test_admin_can_create_custom_role(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('tenant.roles.store'), [
            'name' => 'Supervisor Gudang',
            'description' => 'Role untuk supervisor gudang',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('custom_roles', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Supervisor Gudang',
        ]);
    }

    public function test_admin_can_update_custom_role(): void
    {
        $role = $this->createCustomRole('Original Name');

        $this->actingAs($this->admin);

        $response = $this->put(route('tenant.roles.update', $role), [
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('custom_roles', [
            'id' => $role->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_admin_can_delete_custom_role_without_users(): void
    {
        $role = $this->createCustomRole('To Delete');

        $this->actingAs($this->admin);

        $response = $this->delete(route('tenant.roles.destroy', $role));

        $response->assertRedirect();

        $this->assertDatabaseMissing('custom_roles', [
            'id' => $role->id,
        ]);
    }

    public function test_rejects_duplicate_role_name_within_tenant(): void
    {
        $this->createCustomRole('Existing Role');

        $this->actingAs($this->admin);

        $response = $this->post(route('tenant.roles.store'), [
            'name' => 'Existing Role',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_rejects_reserved_role_names(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('tenant.roles.store'), [
            'name' => 'admin',
        ]);

        $response->assertSessionHasErrors('name');
    }

    // ── Tenant Isolation ──────────────────────────────────────────

    public function test_cannot_access_other_tenant_role_edit(): void
    {
        $otherTenant = $this->createTenant(['slug' => 'other-' . uniqid()]);
        $otherRole = CustomRole::withoutTenantScope()->create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Other Role',
            'slug' => 'other-role',
            'is_active' => true,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin);

        // BelongsToTenant global scope prevents finding the model
        // Laravel returns a non-success response (redirect or 404)
        $response = $this->get(route('tenant.roles.edit', $otherRole));

        $this->assertNotEquals(
            200,
            $response->getStatusCode(),
            'Should not be able to access another tenant\'s role'
        );
    }

    public function test_cannot_update_other_tenant_role(): void
    {
        $otherTenant = $this->createTenant(['slug' => 'other-' . uniqid()]);
        $otherRole = CustomRole::withoutTenantScope()->create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Other Role',
            'slug' => 'other-role',
            'is_active' => true,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin);

        $response = $this->put(route('tenant.roles.update', $otherRole), [
            'name' => 'Hacked Name',
        ]);

        $this->assertNotEquals(
            200,
            $response->getStatusCode(),
            'Should not be able to update another tenant\'s role'
        );

        // Verify the role was NOT modified
        $this->assertDatabaseHas('custom_roles', [
            'id' => $otherRole->id,
            'name' => 'Other Role',
        ]);
    }

    public function test_cannot_delete_other_tenant_role(): void
    {
        $otherTenant = $this->createTenant(['slug' => 'other-' . uniqid()]);
        $otherRole = CustomRole::withoutTenantScope()->create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Other Role',
            'slug' => 'other-role',
            'is_active' => true,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin);

        $response = $this->delete(route('tenant.roles.destroy', $otherRole));

        $this->assertNotEquals(
            200,
            $response->getStatusCode(),
            'Should not be able to delete another tenant\'s role'
        );

        // Verify the role still exists
        $this->assertDatabaseHas('custom_roles', [
            'id' => $otherRole->id,
        ]);
    }

    // ── Permission Matrix Save/Load ───────────────────────────────

    public function test_can_save_and_load_permissions(): void
    {
        $role = $this->createCustomRole('Permission Test');

        $this->actingAs($this->admin);

        // Save permissions
        $response = $this->post(route('tenant.roles.permissions.save', $role), [
            'perms' => [
                'customers.view' => '1',
                'customers.create' => '1',
                'sales.view' => '1',
            ],
        ]);

        $response->assertRedirect();

        // Verify permissions saved
        $this->assertDatabaseHas('role_permissions', [
            'custom_role_id' => $role->id,
            'module' => 'customers',
            'action' => 'view',
            'granted' => true,
        ]);

        $this->assertDatabaseHas('role_permissions', [
            'custom_role_id' => $role->id,
            'module' => 'customers',
            'action' => 'create',
            'granted' => true,
        ]);

        $this->assertDatabaseHas('role_permissions', [
            'custom_role_id' => $role->id,
            'module' => 'sales',
            'action' => 'view',
            'granted' => true,
        ]);

        // Verify non-granted permissions are not stored
        $this->assertDatabaseMissing('role_permissions', [
            'custom_role_id' => $role->id,
            'module' => 'customers',
            'action' => 'delete',
        ]);
    }

    public function test_saving_permissions_replaces_existing(): void
    {
        $role = $this->createCustomRole('Replace Test');

        $this->actingAs($this->admin);

        // Save initial permissions
        $this->post(route('tenant.roles.permissions.save', $role), [
            'perms' => [
                'customers.view' => '1',
                'customers.create' => '1',
            ],
        ]);

        // Save new permissions (should replace)
        $this->post(route('tenant.roles.permissions.save', $role), [
            'perms' => [
                'sales.view' => '1',
            ],
        ]);

        // Old permissions should be gone
        $this->assertDatabaseMissing('role_permissions', [
            'custom_role_id' => $role->id,
            'module' => 'customers',
            'action' => 'view',
        ]);

        // New permissions should exist
        $this->assertDatabaseHas('role_permissions', [
            'custom_role_id' => $role->id,
            'module' => 'sales',
            'action' => 'view',
            'granted' => true,
        ]);
    }

    // ── Role Cloning ──────────────────────────────────────────────

    public function test_can_clone_custom_role(): void
    {
        $source = $this->createCustomRole('Source Role');

        // Add permissions to source
        $this->roleService->syncPermissions($source, [
            ['module' => 'customers', 'action' => 'view', 'granted' => true],
            ['module' => 'customers', 'action' => 'create', 'granted' => true],
            ['module' => 'sales', 'action' => 'view', 'granted' => true],
        ]);

        $this->actingAs($this->admin);

        $response = $this->post(route('tenant.roles.clone', $source));

        $response->assertRedirect();

        // Verify cloned role exists
        $cloned = CustomRole::where('tenant_id', $this->tenant->id)
            ->where('name', 'like', 'Source Role (Copy%')
            ->first();

        $this->assertNotNull($cloned);

        // Verify permissions were copied
        $this->assertDatabaseHas('role_permissions', [
            'custom_role_id' => $cloned->id,
            'module' => 'customers',
            'action' => 'view',
            'granted' => true,
        ]);

        $this->assertDatabaseHas('role_permissions', [
            'custom_role_id' => $cloned->id,
            'module' => 'sales',
            'action' => 'view',
            'granted' => true,
        ]);
    }

    public function test_can_clone_from_hardcoded_role(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('tenant.roles.store'), [
            'name' => 'Custom Kasir',
            'description' => 'Cloned from kasir',
            'clone_from' => 'hardcoded:kasir',
        ]);

        $response->assertRedirect();

        $role = CustomRole::where('tenant_id', $this->tenant->id)
            ->where('name', 'Custom Kasir')
            ->first();

        $this->assertNotNull($role);

        // Kasir has pos.view and pos.create by default
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

    // ── Deletion Prevention ───────────────────────────────────────

    public function test_cannot_delete_role_with_assigned_users(): void
    {
        $role = $this->createCustomRole('Assigned Role');

        // Assign a user to this role
        User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Staff User',
            'email' => 'staff-' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'custom:' . $role->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($this->admin);

        $response = $this->delete(route('tenant.roles.destroy', $role));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Role should still exist
        $this->assertDatabaseHas('custom_roles', [
            'id' => $role->id,
        ]);
    }

    // ── Non-admin cannot access ───────────────────────────────────

    public function test_non_admin_cannot_access_role_management(): void
    {
        $staffUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Staff',
            'email' => 'staff-' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($staffUser);

        $response = $this->get(route('tenant.roles.index'));

        $response->assertStatus(403);
    }

    // ── Helper ────────────────────────────────────────────────────

    private function createCustomRole(string $name): CustomRole
    {
        return CustomRole::create([
            'tenant_id' => $this->tenant->id,
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'is_active' => true,
            'created_by' => $this->admin->id,
        ]);
    }
}
