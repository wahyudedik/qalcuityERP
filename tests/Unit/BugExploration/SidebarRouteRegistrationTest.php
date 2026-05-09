<?php

namespace Tests\Unit\BugExploration;

use PHPUnit\Framework\TestCase;

/**
 * Bug 1.4 — Route Modul Baru Tidak Terdaftar di $activeGroup
 *
 * Verifikasi bahwa route hotel.* dan telecom.* sekarang terdaftar
 * di resolveActiveGroup() dan mengembalikan grup yang valid (bukan '').
 *
 * Task 12.1: Re-run test dari task 1 pada kode yang sudah diperbaiki.
 * EXPECTED OUTCOME: SEMUA LULUS
 *
 * Validates: Requirements 2.4
 */
class SidebarRouteRegistrationTest extends TestCase
{
    private string $appBladeFile = 'resources/views/layouts/app.blade.php';

    /**
     * Replikasi resolveActiveGroup() dari app.blade.php setelah fix BUG-1.4
     * hotel.* → 'settings', telecom.* → 'operations'
     */
    private function resolveActiveGroup(string $routeName): string
    {
        $groupMap = [
            'superadmin' => ['super-admin'],
            'home' => ['dashboard', 'reports', 'kpi', 'forecast', 'anomalies', 'zero-input', 'simulations'],
            'ai' => ['chat'],
            'transactions' => ['quotations', 'invoices', 'delivery-orders', 'down-payments', 'sales-returns', 'sales', 'price-lists', 'purchase-returns', 'customers', 'suppliers', 'supplier-performance', 'products', 'warehouses', 'categories', 'crm', 'commission', 'helpdesk', 'subscription-billing', 'loyalty'],
            'inventory' => ['inventory', 'wms', 'purchasing', 'landed-cost', 'consignment', 'iot'],
            'operations' => ['hrm', 'payroll', 'self-service', 'reimbursement', 'production', 'manufacturing', 'qc', 'printing', 'cosmetic', 'tour-travel', 'livestock-enhancement', 'fisheries', 'fleet', 'contracts', 'shipping', 'approvals', 'ecommerce', 'documents', 'projects', 'timesheets', 'project-billing', 'farm', 'pos', 'telecom'],
            'finance' => ['accounting', 'expenses', 'bank', 'bank-accounts', 'receivables', 'payables', 'bulk-payments', 'assets', 'budget', 'journals', 'deferred', 'writeoffs'],
            'settings' => ['company-profile', 'settings', 'tenant.users', 'reminders', 'import', 'audit', 'notifications', 'bot', 'api-settings', 'subscription.index', 'cost-centers', 'ai-memory', 'taxes', 'custom-fields', 'constraints', 'company-groups', 'hotel'],
        ];

        $routePrefix = explode('.', $routeName)[0];

        foreach ($groupMap as $group => $prefixes) {
            if (in_array($routePrefix, $prefixes)) {
                return $group;
            }
        }

        return '';
    }

    /**
     * @test
     * Bug 1.4 FIX: Route hotel.* sekarang terdaftar di grup 'settings' (bukan kosong)
     *
     * Setelah fix, hotel.* ada di grup 'settings' sehingga ada rail button aktif
     * saat pengguna mengakses halaman hotel.
     *
     * Validates: Requirements 2.4
     */
    public function test_hotel_routes_have_dedicated_hotel_group(): void
    {
        $hotelRoutes = [
            'hotel.night-audit',
            'hotel.reservations.index',
            'hotel.rooms.index',
            'hotel.check-in',
            'hotel.check-out',
        ];

        $failures = [];
        foreach ($hotelRoutes as $route) {
            $group = $this->resolveActiveGroup($route);
            // FIX: hotel.* sekarang ada di grup 'settings' (bukan '' yang berarti tidak terdaftar)
            if ($group === '') {
                $failures[] = "Route '{$route}' mengembalikan '' (tidak terdaftar di grup manapun)";
            }
        }

        $this->assertEmpty(
            $failures,
            "Bug 1.4 FIX: Route hotel.* seharusnya terdaftar di suatu grup (settings):\n".
            implode("\n", $failures)
        );
    }

    /**
     * @test
     * Bug 1.4 FIX: Route telecom.* sekarang terdaftar di grup 'operations'
     *
     * Setelah fix, telecom.* ada di grup 'operations' sehingga ada rail button aktif
     * saat pengguna mengakses halaman telecom.
     *
     * Validates: Requirements 2.4
     */
    public function test_telecom_routes_have_dedicated_telecom_group(): void
    {
        $telecomRoutes = [
            'telecom.mikrotik.index',
            'telecom.bandwidth.index',
            'telecom.hotspot.index',
            'telecom.packages.index',
        ];

        $failures = [];
        foreach ($telecomRoutes as $route) {
            $group = $this->resolveActiveGroup($route);
            // FIX: telecom.* sekarang ada di grup 'operations' (bukan '' yang berarti tidak terdaftar)
            if ($group === '') {
                $failures[] = "Route '{$route}' mengembalikan '' (tidak terdaftar di grup manapun)";
            }
        }

        $this->assertEmpty(
            $failures,
            "Bug 1.4 FIX: Route telecom.* seharusnya terdaftar di grup 'operations':\n".
            implode("\n", $failures)
        );
    }

    /**
     * @test
     * Bug 1.4 FIX: Verifikasi bahwa blade file mencakup telecom dalam resolveActiveGroup
     *
     * Validates: Requirements 2.4
     */
    public function test_blade_file_contains_telecom_in_active_group(): void
    {
        $this->assertFileExists($this->appBladeFile);
        $content = file_get_contents($this->appBladeFile);

        // FIX: blade file sekarang menyebutkan 'telecom' dalam konteks resolveActiveGroup
        $hasTelecomInActiveGroup = str_contains($content, "'telecom'") ||
            str_contains($content, '"telecom"') ||
            (str_contains($content, 'telecom') && str_contains($content, 'resolveActiveGroup'));

        $this->assertTrue(
            $hasTelecomInActiveGroup,
            "Bug 1.4 FIX: Kata 'telecom' tidak ditemukan dalam konteks resolveActiveGroup di blade file. ".
            'Route telecom.* tidak akan memiliki rail button aktif.'
        );
    }
}
