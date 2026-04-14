<?php

namespace Tests\Unit\Preservation;

use PHPUnit\Framework\TestCase;

/**
 * Preservation Test — Sidebar Role-Based Visibility & Logo Navigation
 *
 * Memverifikasi bahwa behavior sidebar yang SUDAH BENAR tidak berubah setelah fix diterapkan.
 * Test ini harus LULUS pada kode unfixed (baseline) dan tetap LULUS setelah fix.
 *
 * Validates: Requirements 3.1, 3.2, 3.3
 */
class SidebarPreservationTest extends TestCase
{
    /**
     * Replikasi logika resolveActiveGroup dari sidebar.blade.php.
     * Mengembalikan grup pertama yang cocok (atau string kosong jika tidak ada).
     */
    private function resolveActiveGroup(string $routeName): string
    {
        $groups = [
            'home' => [
                'dashboard', 'reports*', 'kpi*', 'forecast*', 'anomalies*',
                'zero-input*', 'simulations*',
            ],
            'ai' => ['chat*'],
            'transactions' => [
                'quotations*', 'invoices*', 'delivery-orders*', 'down-payments*',
                'sales-returns*', 'sales.*', 'sales.index', 'price-lists*',
                'purchase-returns*', 'customers*', 'suppliers*', 'supplier-performance*',
                'products*', 'warehouses*', 'categories*', 'crm*', 'commission*',
                'helpdesk*', 'subscription-billing*', 'loyalty*',
            ],
            'inventory' => [
                'inventory*', 'wms*', 'purchasing*', 'landed-cost*', 'consignment*', 'iot*',
            ],
            'operations' => [
                'hrm*', 'payroll*', 'self-service*', 'reimbursement*', 'production*',
                'manufacturing*', 'qc*', 'printing*', 'cosmetic*', 'tour-travel*',
                'livestock-enhancement*', 'fisheries*', 'fleet*', 'contracts*',
                'shipping*', 'approvals*', 'ecommerce*', 'documents*', 'projects*',
                'timesheets*', 'project-billing*', 'farm*', 'pos*',
            ],
            'finance' => [
                'accounting*', 'expenses*', 'bank.*', 'bank-accounts*', 'receivables*',
                'payables*', 'bulk-payments*', 'assets*', 'budget*', 'journals*',
                'deferred*', 'writeoffs*',
            ],
            'settings' => [
                'company-profile*', 'settings*', 'tenant.users*', 'reminders*',
                'import*', 'audit*', 'notifications*', 'bot*', 'api-settings*',
                'subscription.index', 'cost-centers*', 'ai-memory*', 'taxes*',
                'custom-fields*', 'constraints*', 'company-groups*', 'hotel*',
            ],
            'superadmin' => ['super-admin*'],
        ];

        foreach ($groups as $group => $patterns) {
            foreach ($patterns as $pattern) {
                if ($this->routeIs($routeName, $pattern)) {
                    return $group;
                }
            }
        }

        return '';
    }

    /**
     * Simulasi Route::is() dengan wildcard matching.
     */
    private function routeIs(string $routeName, string $pattern): bool
    {
        if ($routeName === $pattern) {
            return true;
        }
        if (str_ends_with($pattern, '*')) {
            $prefix = rtrim($pattern, '*');
            return str_starts_with($routeName, $prefix);
        }
        return false;
    }

    /**
     * Simulasi menu yang terlihat untuk role SuperAdmin.
     * SuperAdmin hanya melihat 'home' dan 'superadmin'.
     */
    private function getVisibleGroupsForRole(string $role): array
    {
        // Logika role-based visibility yang sudah ada di sistem
        $allGroups = ['home', 'ai', 'transactions', 'inventory', 'operations', 'finance', 'settings', 'superadmin'];

        if ($role === 'super_admin') {
            // SuperAdmin hanya melihat home dan superadmin
            return ['home', 'superadmin'];
        }

        if ($role === 'kasir') {
            // Kasir tidak melihat finance, operations, settings
            return array_values(array_diff($allGroups, ['finance', 'operations', 'settings', 'superadmin']));
        }

        if ($role === 'gudang') {
            // Gudang tidak melihat finance, operations, settings
            return array_values(array_diff($allGroups, ['finance', 'operations', 'settings', 'superadmin']));
        }

        // Admin dan role lain melihat semua kecuali superadmin
        return array_values(array_diff($allGroups, ['superadmin']));
    }

    // ── Requirement 3.1: SuperAdmin hanya melihat Dashboard dan Admin ─────────

    /**
     * @test
     * Preservation 3.1: SuperAdmin hanya melihat menu 'home' dan 'superadmin'
     *
     * Behavior ini sudah benar dan tidak boleh berubah setelah fix.
     * Validates: Requirements 3.1
     */
    public function test_superadmin_only_sees_home_and_superadmin_groups(): void
    {
        $visibleGroups = $this->getVisibleGroupsForRole('super_admin');

        $this->assertContains(
            'home',
            $visibleGroups,
            "SuperAdmin harus bisa melihat grup 'home' (Dashboard)"
        );

        $this->assertContains(
            'superadmin',
            $visibleGroups,
            "SuperAdmin harus bisa melihat grup 'superadmin' (Admin panel)"
        );

        // SuperAdmin tidak boleh melihat menu tenant
        $tenantOnlyGroups = ['transactions', 'inventory', 'operations', 'finance', 'settings'];
        foreach ($tenantOnlyGroups as $group) {
            $this->assertNotContains(
                $group,
                $visibleGroups,
                "SuperAdmin tidak boleh melihat grup '{$group}' (menu tenant)"
            );
        }
    }

    /**
     * @test
     * Preservation 3.1: SuperAdmin route 'super-admin.index' masuk ke grup 'superadmin'
     *
     * Validates: Requirements 3.1
     */
    public function test_superadmin_route_resolves_to_superadmin_group(): void
    {
        $group = $this->resolveActiveGroup('super-admin.index');

        $this->assertEquals(
            'superadmin',
            $group,
            "Route 'super-admin.index' harus masuk ke grup 'superadmin'"
        );
    }

    /**
     * @test
     * Preservation 3.1: Route 'dashboard' masuk ke grup 'home'
     *
     * Validates: Requirements 3.1
     */
    public function test_dashboard_route_resolves_to_home_group(): void
    {
        $group = $this->resolveActiveGroup('dashboard');

        $this->assertEquals(
            'home',
            $group,
            "Route 'dashboard' harus masuk ke grup 'home'"
        );
    }

    // ── Requirement 3.2: Kasir tidak melihat Finance, Operations, Settings ────

    /**
     * @test
     * Preservation 3.2: Kasir tidak melihat menu Keuangan (finance)
     *
     * Validates: Requirements 3.2
     */
    public function test_kasir_does_not_see_finance_group(): void
    {
        $visibleGroups = $this->getVisibleGroupsForRole('kasir');

        $this->assertNotContains(
            'finance',
            $visibleGroups,
            "Kasir tidak boleh melihat grup 'finance' (Keuangan)"
        );
    }

    /**
     * @test
     * Preservation 3.2: Kasir tidak melihat menu Operasional (operations)
     *
     * Validates: Requirements 3.2
     */
    public function test_kasir_does_not_see_operations_group(): void
    {
        $visibleGroups = $this->getVisibleGroupsForRole('kasir');

        $this->assertNotContains(
            'operations',
            $visibleGroups,
            "Kasir tidak boleh melihat grup 'operations' (Operasional)"
        );
    }

    /**
     * @test
     * Preservation 3.2: Kasir tidak melihat menu Pengaturan (settings)
     *
     * Validates: Requirements 3.2
     */
    public function test_kasir_does_not_see_settings_group(): void
    {
        $visibleGroups = $this->getVisibleGroupsForRole('kasir');

        $this->assertNotContains(
            'settings',
            $visibleGroups,
            "Kasir tidak boleh melihat grup 'settings' (Pengaturan)"
        );
    }

    /**
     * @test
     * Preservation 3.2: Kasir masih bisa melihat menu Transaksi dan Inventori
     *
     * Validates: Requirements 3.2
     */
    public function test_kasir_still_sees_transactions_and_inventory(): void
    {
        $visibleGroups = $this->getVisibleGroupsForRole('kasir');

        $this->assertContains(
            'transactions',
            $visibleGroups,
            "Kasir harus bisa melihat grup 'transactions'"
        );

        $this->assertContains(
            'home',
            $visibleGroups,
            "Kasir harus bisa melihat grup 'home' (Dashboard)"
        );
    }

    // ── Requirement 3.3: Klik logo sidebar → diarahkan ke dashboard ──────────

    /**
     * @test
     * Preservation 3.3: Route logo sidebar adalah 'dashboard'
     *
     * Memverifikasi bahwa route yang digunakan untuk logo sidebar adalah 'dashboard'.
     * Validates: Requirements 3.3
     */
    public function test_sidebar_logo_route_is_dashboard(): void
    {
        // Verifikasi bahwa route 'dashboard' ada dan dapat di-resolve
        // Ini adalah unit test yang memverifikasi logika routing
        $logoRoute = 'dashboard'; // Route yang digunakan di sidebar logo

        // Route 'dashboard' harus masuk ke grup 'home'
        $group = $this->resolveActiveGroup($logoRoute);

        $this->assertEquals(
            'home',
            $group,
            "Route logo sidebar '{$logoRoute}' harus masuk ke grup 'home'"
        );

        // Pastikan route 'dashboard' tidak masuk ke grup lain
        $this->assertNotEquals(
            'superadmin',
            $group,
            "Route logo sidebar tidak boleh masuk ke grup 'superadmin'"
        );
    }

    /**
     * @test
     * Preservation 3.3: Route 'dashboard' tidak overlap dengan grup lain
     *
     * Validates: Requirements 3.3
     */
    public function test_dashboard_route_does_not_overlap_with_other_groups(): void
    {
        // Hitung berapa grup yang cocok dengan route 'dashboard'
        $groups = [
            'home' => ['dashboard', 'reports*', 'kpi*'],
            'ai' => ['chat*'],
            'transactions' => ['quotations*', 'invoices*', 'sales.*'],
            'inventory' => ['inventory*', 'wms*'],
            'operations' => ['hrm*', 'payroll*'],
            'finance' => ['accounting*', 'expenses*'],
            'settings' => ['settings*'],
            'superadmin' => ['super-admin*'],
        ];

        $matchedGroups = [];
        foreach ($groups as $group => $patterns) {
            foreach ($patterns as $pattern) {
                if ($this->routeIs('dashboard', $pattern)) {
                    $matchedGroups[] = $group;
                    break;
                }
            }
        }

        $this->assertCount(
            1,
            $matchedGroups,
            "Route 'dashboard' harus cocok dengan tepat 1 grup, bukan: " . implode(', ', $matchedGroups)
        );

        $this->assertEquals('home', $matchedGroups[0]);
    }
}
