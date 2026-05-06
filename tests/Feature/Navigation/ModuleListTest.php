<?php

namespace Tests\Feature\Navigation;

use App\Models\User;
use PHPUnit\Framework\TestCase;

/**
 * Smoke test — MODULE_LIST generation logic per user type.
 *
 * This test verifies that the Blade conditional logic in app.blade.php
 * produces the correct MODULE_LIST for each user type, by directly
 * testing the User model role-check methods and the expected module sets.
 *
 * Validates: Requirements 2.4, 2.5, 2.6
 * Design doc: MODULE_LIST section
 *
 * User type → expected modules:
 *   SuperAdmin          → home, superadmin                                    (2 modules)
 *   Affiliate           → home                                                (1 module)
 *   Kasir               → home, ai, transactions, inventory                   (4 modules)
 *   Gudang              → home, ai, transactions, inventory                   (4 modules)
 *   Tenant biasa (admin/manager/staff) → home, ai, transactions, inventory,
 *                          operations, finance, settings                      (7 modules)
 */
class ModuleListTest extends TestCase
{
    // ── Helper: simulate the MODULE_LIST Blade logic ──────────────

    /**
     * Replicate the PHP/Blade conditional logic from app.blade.php:
     *
     *   @if ($user?->isSuperAdmin())
     *       home, superadmin
     *   @elseif ($user?->isAffiliate())
     *       home
     *   @else
     *       home, ai, transactions, inventory
     *       @if (!$user?->isKasir() && !$user?->isGudang())
     *           operations, finance, settings
     *       @endif
     *   @endif
     *
     * @return string[] List of module keys
     */
    private function resolveModuleList(?User $user): array
    {
        if ($user?->isSuperAdmin()) {
            return ['home', 'superadmin'];
        }

        if ($user?->isAffiliate()) {
            return ['home'];
        }

        // Tenant user (else branch)
        $modules = ['home', 'ai', 'transactions', 'inventory'];

        if (!$user?->isKasir() && !$user?->isGudang()) {
            $modules[] = 'operations';
            $modules[] = 'finance';
            $modules[] = 'settings';
        }

        return $modules;
    }

    /**
     * Build a User instance with the given role without touching the database.
     * We use a plain User object and set the role attribute directly.
     */
    private function makeUser(string $role): User
    {
        $user = new User();
        $user->role = $role;
        return $user;
    }

    // ── Tests: User model role-check methods ─────────────────────

    /**
     * @test
     * Verify isSuperAdmin() returns true only for 'super_admin' role.
     */
    public function test_is_super_admin_method(): void
    {
        $superAdmin = $this->makeUser('super_admin');
        $this->assertTrue($superAdmin->isSuperAdmin(), "super_admin role should return true for isSuperAdmin()");

        foreach (['admin', 'manager', 'staff', 'kasir', 'gudang', 'affiliate'] as $role) {
            $user = $this->makeUser($role);
            $this->assertFalse($user->isSuperAdmin(), "Role '{$role}' should NOT be isSuperAdmin()");
        }
    }

    /**
     * @test
     * Verify isAffiliate() returns true only for 'affiliate' role.
     */
    public function test_is_affiliate_method(): void
    {
        $affiliate = $this->makeUser('affiliate');
        $this->assertTrue($affiliate->isAffiliate(), "affiliate role should return true for isAffiliate()");

        foreach (['admin', 'manager', 'staff', 'kasir', 'gudang', 'super_admin'] as $role) {
            $user = $this->makeUser($role);
            $this->assertFalse($user->isAffiliate(), "Role '{$role}' should NOT be isAffiliate()");
        }
    }

    /**
     * @test
     * Verify isKasir() returns true only for 'kasir' role.
     */
    public function test_is_kasir_method(): void
    {
        $kasir = $this->makeUser('kasir');
        $this->assertTrue($kasir->isKasir(), "kasir role should return true for isKasir()");

        foreach (['admin', 'manager', 'staff', 'gudang', 'super_admin', 'affiliate'] as $role) {
            $user = $this->makeUser($role);
            $this->assertFalse($user->isKasir(), "Role '{$role}' should NOT be isKasir()");
        }
    }

    /**
     * @test
     * Verify isGudang() returns true only for 'gudang' role.
     */
    public function test_is_gudang_method(): void
    {
        $gudang = $this->makeUser('gudang');
        $this->assertTrue($gudang->isGudang(), "gudang role should return true for isGudang()");

        foreach (['admin', 'manager', 'staff', 'kasir', 'super_admin', 'affiliate'] as $role) {
            $user = $this->makeUser($role);
            $this->assertFalse($user->isGudang(), "Role '{$role}' should NOT be isGudang()");
        }
    }

    // ── Tests: MODULE_LIST per user type ─────────────────────────

    /**
     * @test
     * Req 2.5: SuperAdmin sees home and superadmin only (2 modules).
     */
    public function test_super_admin_sees_home_and_superadmin_only(): void
    {
        $user = $this->makeUser('super_admin');
        $modules = $this->resolveModuleList($user);

        $this->assertCount(2, $modules, "SuperAdmin should have exactly 2 modules");
        $this->assertContains('home', $modules, "SuperAdmin must have 'home' module");
        $this->assertContains('superadmin', $modules, "SuperAdmin must have 'superadmin' module");

        // Must NOT have tenant modules
        foreach (['ai', 'transactions', 'inventory', 'operations', 'finance', 'settings'] as $mod) {
            $this->assertNotContains($mod, $modules, "SuperAdmin must NOT have '{$mod}' module");
        }
    }

    /**
     * @test
     * Req 2.6: Affiliate sees home only (1 module).
     */
    public function test_affiliate_sees_home_only(): void
    {
        $user = $this->makeUser('affiliate');
        $modules = $this->resolveModuleList($user);

        $this->assertCount(1, $modules, "Affiliate should have exactly 1 module");
        $this->assertContains('home', $modules, "Affiliate must have 'home' module");

        // Must NOT have any other modules
        foreach (['ai', 'transactions', 'inventory', 'operations', 'finance', 'settings', 'superadmin'] as $mod) {
            $this->assertNotContains($mod, $modules, "Affiliate must NOT have '{$mod}' module");
        }
    }

    /**
     * @test
     * Req 2.4: Tenant biasa (admin role) sees 7 modules:
     * home, ai, transactions, inventory, operations, finance, settings.
     */
    public function test_tenant_admin_sees_7_modules(): void
    {
        $user = $this->makeUser('admin');
        $modules = $this->resolveModuleList($user);

        $expected = ['home', 'ai', 'transactions', 'inventory', 'operations', 'finance', 'settings'];

        $this->assertCount(7, $modules, "Tenant admin should have exactly 7 modules");

        foreach ($expected as $mod) {
            $this->assertContains($mod, $modules, "Tenant admin must have '{$mod}' module");
        }

        $this->assertNotContains('superadmin', $modules, "Tenant admin must NOT have 'superadmin' module");
    }

    /**
     * @test
     * Req 2.4: Tenant manager sees 7 modules (same as admin).
     */
    public function test_tenant_manager_sees_7_modules(): void
    {
        $user = $this->makeUser('manager');
        $modules = $this->resolveModuleList($user);

        $expected = ['home', 'ai', 'transactions', 'inventory', 'operations', 'finance', 'settings'];

        $this->assertCount(7, $modules, "Tenant manager should have exactly 7 modules");

        foreach ($expected as $mod) {
            $this->assertContains($mod, $modules, "Tenant manager must have '{$mod}' module");
        }
    }

    /**
     * @test
     * Req 2.4: Tenant staff sees 7 modules (same as admin).
     */
    public function test_tenant_staff_sees_7_modules(): void
    {
        $user = $this->makeUser('staff');
        $modules = $this->resolveModuleList($user);

        $expected = ['home', 'ai', 'transactions', 'inventory', 'operations', 'finance', 'settings'];

        $this->assertCount(7, $modules, "Tenant staff should have exactly 7 modules");

        foreach ($expected as $mod) {
            $this->assertContains($mod, $modules, "Tenant staff must have '{$mod}' module");
        }
    }

    /**
     * @test
     * Design doc: Kasir sees only home, ai, transactions, inventory (4 modules).
     */
    public function test_kasir_sees_4_modules_without_operations_finance_settings(): void
    {
        $user = $this->makeUser('kasir');
        $modules = $this->resolveModuleList($user);

        $this->assertCount(4, $modules, "Kasir should have exactly 4 modules");

        $expected = ['home', 'ai', 'transactions', 'inventory'];
        foreach ($expected as $mod) {
            $this->assertContains($mod, $modules, "Kasir must have '{$mod}' module");
        }

        // Must NOT have the restricted modules
        foreach (['operations', 'finance', 'settings', 'superadmin'] as $mod) {
            $this->assertNotContains($mod, $modules, "Kasir must NOT have '{$mod}' module");
        }
    }

    /**
     * @test
     * Design doc: Gudang sees only home, ai, transactions, inventory (4 modules).
     */
    public function test_gudang_sees_4_modules_without_operations_finance_settings(): void
    {
        $user = $this->makeUser('gudang');
        $modules = $this->resolveModuleList($user);

        $this->assertCount(4, $modules, "Gudang should have exactly 4 modules");

        $expected = ['home', 'ai', 'transactions', 'inventory'];
        foreach ($expected as $mod) {
            $this->assertContains($mod, $modules, "Gudang must have '{$mod}' module");
        }

        // Must NOT have the restricted modules
        foreach (['operations', 'finance', 'settings', 'superadmin'] as $mod) {
            $this->assertNotContains($mod, $modules, "Gudang must NOT have '{$mod}' module");
        }
    }

    /**
     * @test
     * Verify null user (unauthenticated) falls into the else branch.
     * null?->isKasir() = null (falsy), null?->isGudang() = null (falsy)
     * So !null && !null = true → full 7 modules are returned.
     *
     * Note: In practice, app.blade.php is only rendered for authenticated users,
     * but this verifies the null-safe operator behavior is correct.
     */
    public function test_null_user_falls_into_else_branch_with_full_tenant_modules(): void
    {
        $modules = $this->resolveModuleList(null);

        $this->assertCount(7, $modules, "Null user (null-safe operators) should produce 7 modules");
        $this->assertContains('operations', $modules);
        $this->assertContains('finance', $modules);
        $this->assertContains('settings', $modules);
    }

    // ── Tests: MODULE_LIST structure verification ─────────────────

    /**
     * @test
     * Verify the app.blade.php file contains the MODULE_LIST generation block
     * with the correct conditional structure.
     */
    public function test_app_blade_contains_module_list_with_correct_conditionals(): void
    {
        $projectRoot = $this->resolveProjectRoot();
        $appBladePath = $projectRoot . '/resources/views/layouts/app.blade.php';

        $this->assertFileExists($appBladePath, 'app.blade.php must exist');

        $content = file_get_contents($appBladePath);

        // Verify MODULE_LIST constant declaration
        $this->assertStringContainsString(
            'const MODULE_LIST = [',
            $content,
            'app.blade.php must declare MODULE_LIST constant'
        );

        // Verify SuperAdmin conditional
        $this->assertStringContainsString(
            "@if (\$user?->isSuperAdmin())",
            $content,
            'MODULE_LIST must check isSuperAdmin()'
        );

        // Verify Affiliate conditional
        $this->assertStringContainsString(
            "@elseif (\$user?->isAffiliate())",
            $content,
            'MODULE_LIST must check isAffiliate()'
        );

        // Verify Kasir/Gudang restriction
        $this->assertStringContainsString(
            "isKasir()",
            $content,
            'MODULE_LIST must check isKasir()'
        );
        $this->assertStringContainsString(
            "isGudang()",
            $content,
            'MODULE_LIST must check isGudang()'
        );

        // Verify all 8 module keys are present
        $expectedModuleKeys = ['home', 'superadmin', 'ai', 'transactions', 'inventory', 'operations', 'finance', 'settings'];
        foreach ($expectedModuleKeys as $key) {
            $this->assertStringContainsString(
                "key: '{$key}'",
                $content,
                "MODULE_LIST must include module key '{$key}'"
            );
        }
    }

    /**
     * @test
     * Verify the User model has all required role-check methods for MODULE_LIST.
     */
    public function test_user_model_has_all_required_role_methods(): void
    {
        $user = new User();

        $this->assertTrue(method_exists($user, 'isSuperAdmin'), 'User must have isSuperAdmin() method');
        $this->assertTrue(method_exists($user, 'isAffiliate'), 'User must have isAffiliate() method');
        $this->assertTrue(method_exists($user, 'isKasir'), 'User must have isKasir() method');
        $this->assertTrue(method_exists($user, 'isGudang'), 'User must have isGudang() method');
    }

    /**
     * @test
     * Verify all role constants are defined on the User model.
     */
    public function test_user_model_has_all_role_constants(): void
    {
        $this->assertSame('super_admin', User::ROLE_SUPER_ADMIN);
        $this->assertSame('admin', User::ROLE_ADMIN);
        $this->assertSame('manager', User::ROLE_MANAGER);
        $this->assertSame('staff', User::ROLE_STAFF);
        $this->assertSame('kasir', User::ROLE_KASIR);
        $this->assertSame('gudang', User::ROLE_GUDANG);
        $this->assertSame('affiliate', User::ROLE_AFFILIATE);
    }

    // ── Helper ────────────────────────────────────────────────────

    private function resolveProjectRoot(): string
    {
        $cwd = getcwd();
        if (is_dir($cwd . '/resources/views')) {
            return $cwd;
        }

        $dir = __DIR__;
        for ($i = 0; $i < 5; $i++) {
            $dir = dirname($dir);
            if (is_dir($dir . '/resources/views')) {
                return $dir;
            }
        }

        return $cwd;
    }
}
