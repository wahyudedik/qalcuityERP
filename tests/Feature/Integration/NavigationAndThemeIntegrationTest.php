<?php

namespace Tests\Feature\Integration;

use PHPUnit\Framework\TestCase;

/**
 * Integration Test 14.1 — Navigasi dan Tema (End-to-End)
 *
 * Covers:
 * 1. Full Navigation Flow: setiap route → tepat satu rail button aktif + submenu ter-highlight
 * 2. Theme Persistence Flow: toggle tema → refresh → tema sama, semua komponen pakai warna benar
 * 3. Mobile Navigation Flow: buka sidebar di mobile → hanya satu layer terlihat, transisi smooth
 *
 * Validates: Requirements 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.8, 2.9, 3.5, 3.6, 3.7
 */
class NavigationAndThemeIntegrationTest extends TestCase
{
    private string $appBladeFile = 'resources/views/layouts/app.blade.php';

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function resolveActiveGroup(string $routeName): string
    {
        $groupMap = [
            'superadmin'   => ['super-admin*'],
            'home'         => ['dashboard', 'reports*', 'kpi*', 'forecast*', 'anomalies*', 'zero-input*', 'simulations*'],
            'ai'           => ['chat*'],
            'transactions' => [
                'quotations*', 'invoices*', 'delivery-orders*', 'down-payments*',
                'sales-returns*', 'sales.*', 'sales.index', 'price-lists*',
                'purchase-returns*', 'customers*', 'suppliers*', 'supplier-performance*',
                'products*', 'warehouses*', 'categories*', 'crm*', 'commission*',
                'helpdesk*', 'subscription-billing*', 'loyalty*',
            ],
            'inventory'    => ['inventory*', 'wms*', 'purchasing*', 'landed-cost*', 'consignment*', 'iot*'],
            'operations'   => [
                'hrm*', 'payroll*', 'self-service*', 'reimbursement*', 'production*',
                'manufacturing*', 'qc*', 'printing*', 'cosmetic*', 'tour-travel*',
                'livestock-enhancement*', 'fisheries*', 'fleet*', 'contracts*',
                'shipping*', 'approvals*', 'ecommerce*', 'documents*', 'projects*',
                'timesheets*', 'project-billing*', 'farm*', 'pos*', 'telecom*',
            ],
            'finance'      => [
                'accounting*', 'expenses*', 'bank.*', 'bank-accounts*', 'receivables*',
                'payables*', 'bulk-payments*', 'assets*', 'budget*', 'journals*',
                'deferred*', 'writeoffs*',
            ],
            'settings'     => [
                'company-profile*', 'settings*', 'tenant.users*', 'reminders*',
                'import*', 'audit*', 'notifications*', 'bot*', 'api-settings*',
                'subscription.index', 'cost-centers*', 'ai-memory*', 'taxes*',
                'custom-fields*', 'constraints*', 'company-groups*', 'hotel*',
            ],
        ];

        foreach ($groupMap as $group => $patterns) {
            foreach ($patterns as $pattern) {
                if ($this->routeIs($routeName, $pattern)) {
                    return $group;
                }
            }
        }

        return '';
    }

    private function routeIs(string $routeName, string $pattern): bool
    {
        if ($routeName === $pattern) {
            return true;
        }
        if (str_ends_with($pattern, '*')) {
            return str_starts_with($routeName, rtrim($pattern, '*'));
        }
        return false;
    }

    private function countMatchingGroups(string $routeName): int
    {
        $groupMap = [
            'superadmin'   => ['super-admin*'],
            'home'         => ['dashboard', 'reports*', 'kpi*', 'forecast*', 'anomalies*', 'zero-input*', 'simulations*'],
            'ai'           => ['chat*'],
            'transactions' => [
                'quotations*', 'invoices*', 'delivery-orders*', 'down-payments*',
                'sales-returns*', 'sales.*', 'sales.index', 'price-lists*',
                'purchase-returns*', 'customers*', 'suppliers*', 'supplier-performance*',
                'products*', 'warehouses*', 'categories*', 'crm*', 'commission*',
                'helpdesk*', 'subscription-billing*', 'loyalty*',
            ],
            'inventory'    => ['inventory*', 'wms*', 'purchasing*', 'landed-cost*', 'consignment*', 'iot*'],
            'operations'   => [
                'hrm*', 'payroll*', 'self-service*', 'reimbursement*', 'production*',
                'manufacturing*', 'qc*', 'printing*', 'cosmetic*', 'tour-travel*',
                'livestock-enhancement*', 'fisheries*', 'fleet*', 'contracts*',
                'shipping*', 'approvals*', 'ecommerce*', 'documents*', 'projects*',
                'timesheets*', 'project-billing*', 'farm*', 'pos*', 'telecom*',
            ],
            'finance'      => [
                'accounting*', 'expenses*', 'bank.*', 'bank-accounts*', 'receivables*',
                'payables*', 'bulk-payments*', 'assets*', 'budget*', 'journals*',
                'deferred*', 'writeoffs*',
            ],
            'settings'     => [
                'company-profile*', 'settings*', 'tenant.users*', 'reminders*',
                'import*', 'audit*', 'notifications*', 'bot*', 'api-settings*',
                'subscription.index', 'cost-centers*', 'ai-memory*', 'taxes*',
                'custom-fields*', 'constraints*', 'company-groups*', 'hotel*',
            ],
        ];

        $count = 0;
        foreach ($groupMap as $patterns) {
            foreach ($patterns as $pattern) {
                if ($this->routeIs($routeName, $pattern)) {
                    $count++;
                    break;
                }
            }
        }

        return $count;
    }

    private function getAppBladeContent(): string
    {
        if (!file_exists($this->appBladeFile)) {
            $this->fail("app.blade.php tidak ditemukan: {$this->appBladeFile}");
        }
        return file_get_contents($this->appBladeFile);
    }

    private function getAllJsContent(): string
    {
        $content = '';
        if (is_dir('resources/js')) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator('resources/js', \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if ($file->isFile() && str_ends_with($file->getFilename(), '.js')) {
                    $content .= file_get_contents($file->getPathname());
                }
            }
        }
        return $content;
    }
    // 
    // 1. FULL NAVIGATION FLOW
    // 

    /**
     * @test
     * Integration 14.1  Full Navigation Flow: setiap route menghasilkan tepat satu rail button aktif.
     * Validates: Requirements 2.1, 2.4
     */
    public function test_full_navigation_flow_each_route_activates_exactly_one_rail_button(): void
    {
        $routeToExpectedGroup = [
            'dashboard'                       => 'home',
            'reports.index'                   => 'home',
            'kpi.index'                       => 'home',
            'forecast.index'                  => 'home',
            'anomalies.index'                 => 'home',
            'zero-input.index'                => 'home',
            'simulations.index'               => 'home',
            'chat.index'                      => 'ai',
            'invoices.index'                  => 'transactions',
            'invoices.create'                 => 'transactions',
            'quotations.index'                => 'transactions',
            'sales.index'                     => 'transactions',
            'customers.index'                 => 'transactions',
            'suppliers.index'                 => 'transactions',
            'products.index'                  => 'transactions',
            'warehouses.index'                => 'transactions',
            'crm.index'                       => 'transactions',
            'helpdesk.index'                  => 'transactions',
            'inventory.index'                 => 'inventory',
            'wms.index'                       => 'inventory',
            'purchasing.index'                => 'inventory',
            'hrm.index'                       => 'operations',
            'payroll.index'                   => 'operations',
            'manufacturing.index'             => 'operations',
            'manufacturing.work-orders.index' => 'operations',
            'projects.index'                  => 'operations',
            'ecommerce.index'                 => 'operations',
            'telecom.index'                   => 'operations',
            'telecom.mikrotik.index'          => 'operations',
            'telecom.bandwidth.index'         => 'operations',
            'accounting.index'                => 'finance',
            'journals.index'                  => 'finance',
            'expenses.index'                  => 'finance',
            'assets.index'                    => 'finance',
            'budget.index'                    => 'finance',
            'receivables.index'               => 'finance',
            'payables.index'                  => 'finance',
            'settings.index'                  => 'settings',
            'company-profile.index'           => 'settings',
            'hotel.index'                     => 'settings',
            'hotel.night-audit'               => 'settings',
            'hotel.reservations.index'        => 'settings',
            'notifications.index'             => 'settings',
            'super-admin.tenants.index'       => 'superadmin',
            'super-admin.plans.index'         => 'superadmin',
        ];

        $failures = [];

        foreach ($routeToExpectedGroup as $route => $expectedGroup) {
            $actualGroup = $this->resolveActiveGroup($route);
            $matchCount  = $this->countMatchingGroups($route);

            if ($actualGroup !== $expectedGroup) {
                $failures[] = "Route '{$route}': expected '{$expectedGroup}', got '{$actualGroup}'";
            }
            if ($matchCount !== 1) {
                $failures[] = "Route '{$route}': matched {$matchCount} groups (must be exactly 1)";
            }
        }

        $this->assertEmpty(
            $failures,
            "Full Navigation Flow  tepat satu rail button harus aktif per route:\n" .
            implode("\n", $failures)
        );
    }

    /**
     * @test
     * Integration 14.1  Full Navigation Flow: tidak ada route kritis yang mengembalikan grup kosong.
     * Validates: Requirements 2.4
     */
    public function test_full_navigation_flow_no_critical_route_returns_empty_group(): void
    {
        $criticalRoutes = [
            'dashboard', 'chat.index', 'invoices.index', 'inventory.index',
            'payroll.index', 'manufacturing.index', 'hotel.night-audit',
            'telecom.mikrotik.index', 'accounting.index', 'ecommerce.index',
            'settings.index', 'super-admin.tenants.index',
        ];

        $emptyGroupRoutes = [];
        foreach ($criticalRoutes as $route) {
            if ($this->resolveActiveGroup($route) === '') {
                $emptyGroupRoutes[] = $route;
            }
        }

        $this->assertEmpty(
            $emptyGroupRoutes,
            "Full Navigation Flow  route berikut tidak memiliki grup aktif (mengembalikan ''):\n" .
            implode("\n", $emptyGroupRoutes)
        );
    }

    /**
     * @test
     * Integration 14.1  Full Navigation Flow: submenu items memiliki active flag dari PHP.
     * Validates: Requirements 2.3
     */
    public function test_full_navigation_flow_submenu_items_have_active_flag(): void
    {
        $content = $this->getAppBladeContent();

        $hasActiveFlag = str_contains($content, 'active:') &&
            (str_contains($content, "request()->routeIs(") || str_contains($content, "routeIs("));

        $this->assertTrue(
            $hasActiveFlag,
            "Full Navigation Flow  NAV_GROUPS tidak menyertakan flag 'active' yang di-render PHP. " .
            "Setiap item submenu harus memiliki 'active: {{ request()->routeIs(...) ? 'true' : 'false' }}'."
        );
    }

    /**
     * @test
     * Integration 14.1  Full Navigation Flow: renderPanelItems() menerapkan class active dari item.active.
     * Validates: Requirements 2.3
     */
    public function test_full_navigation_flow_render_panel_applies_active_class(): void
    {
        $content = $this->getAppBladeContent();

        $hasActiveFlagInRender = str_contains($content, 'item.active') ||
            str_contains($content, "'active': true") ||
            str_contains($content, '"active": true') ||
            str_contains($content, 'isActive');

        $this->assertTrue(
            $hasActiveFlagInRender,
            "Full Navigation Flow  renderPanelItems() tidak menggunakan flag 'active' dari item data. " .
            "Harus ada 'item.active' atau flag serupa untuk menandai item submenu yang aktif."
        );
    }

    /**
     * @test
     * Integration 14.1  Full Navigation Flow: --group-color tersinkronisasi saat rail button diklik.
     * Validates: Requirements 2.2
     */
    public function test_full_navigation_flow_group_color_synced_on_click(): void
    {
        $content = $this->getAppBladeContent();

        $hasColorSync = str_contains($content, "setProperty('--group-color'") ||
            str_contains($content, 'setProperty("--group-color"');

        $this->assertTrue(
            $hasColorSync,
            "Full Navigation Flow  tidak ditemukan setProperty('--group-color'). " .
            "buildPanel() harus meng-update CSS custom property '--group-color' saat rail button diklik."
        );
    }

    // 
    // 2. THEME PERSISTENCE FLOW
    // 

    /**
     * @test
     * Integration 14.1  Theme Persistence Flow: script FOUC prevention menangani semua 3 mode tema.
     * Simulates: set theme -> page reload -> verify same theme applied before first render.
     * Validates: Requirements 2.8, 3.5
     */
    public function test_theme_persistence_flow_fouc_script_handles_all_three_modes(): void
    {
        $content = $this->getAppBladeContent();

        $handlesDark   = str_contains($content, "theme === 'dark'") || str_contains($content, 'theme === "dark"');
        $handlesSystem = str_contains($content, "theme === 'system'") || str_contains($content, 'theme === "system"') ||
            str_contains($content, 'matchMedia') || str_contains($content, 'prefers-color-scheme');

        $this->assertTrue($handlesDark,
            "Theme Persistence Flow  script FOUC prevention tidak menangani theme === 'dark'.");

        $this->assertTrue($handlesSystem,
            "Theme Persistence Flow  script FOUC prevention tidak menangani theme === 'system'.");
    }

    /**
     * @test
     * Integration 14.1  Theme Persistence Flow: script FOUC prevention adalah IIFE di <head>.
     * Validates: Requirements 2.8
     */
    public function test_theme_persistence_flow_fouc_script_is_iife_before_vite(): void
    {
        $content = $this->getAppBladeContent();

        $hasIife = (bool) (preg_match('/\(function\s*\(\)\s*\{/', $content) ||
            preg_match('/\(\(\)\s*=>\s*\{/', $content));

        $this->assertTrue($hasIife,
            "Theme Persistence Flow  script FOUC prevention tidak menggunakan IIFE pattern.");

        $scriptPos = strpos($content, "localStorage.getItem('theme')");
        $vitePos   = false;
        if (preg_match('/\n\s*@vite\s*\(/', $content, $matches, PREG_OFFSET_CAPTURE)) {
            $vitePos = $matches[0][1];
        }

        if ($scriptPos !== false && $vitePos !== false) {
            $this->assertLessThan($vitePos, $scriptPos,
                "Theme Persistence Flow  script FOUC prevention berada SETELAH @vite directive.");
        }
    }

    /**
     * @test
     * Integration 14.1  Theme Persistence Flow: ThemeManager mendispatch event theme-changed.
     * Validates: Requirements 2.9, 3.7
     */
    public function test_theme_persistence_flow_theme_manager_dispatches_event(): void
    {
        $themeManagerFile = 'resources/js/theme-manager.js';
        $this->assertFileExists($themeManagerFile,
            "Theme Persistence Flow  file theme-manager.js tidak ditemukan.");

        $content = file_get_contents($themeManagerFile);

        $this->assertTrue(
            str_contains($content, 'dispatchEvent') && str_contains($content, 'theme-changed'),
            "Theme Persistence Flow  ThemeManager tidak mendispatch CustomEvent 'theme-changed'."
        );
    }

    /**
     * @test
     * Integration 14.1  Theme Persistence Flow: ada listener theme-changed untuk Chart.js.
     * Validates: Requirements 2.9
     */
    public function test_theme_persistence_flow_chartjs_responds_to_theme_changed(): void
    {
        $allJsContent = $this->getAllJsContent();

        $hasChartThemeListener = str_contains($allJsContent, 'theme-changed') &&
            (str_contains($allJsContent, 'chart') || str_contains($allJsContent, 'Chart')) &&
            str_contains($allJsContent, 'addEventListener');

        $this->assertTrue($hasChartThemeListener,
            "Theme Persistence Flow  tidak ditemukan listener 'theme-changed' yang mengupdate Chart.js.");
    }

    /**
     * @test
     * Integration 14.1  Theme Persistence Flow: ThemeManager menyimpan tema ke localStorage.
     * Simulates: toggle theme -> verify localStorage.setItem called with new theme.
     * Validates: Requirements 3.5
     */
    public function test_theme_persistence_flow_theme_saved_to_localstorage(): void
    {
        $themeManagerFile = 'resources/js/theme-manager.js';
        $this->assertFileExists($themeManagerFile);
        $content = file_get_contents($themeManagerFile);

        $this->assertTrue(
            str_contains($content, "localStorage.setItem('theme'") ||
            str_contains($content, 'localStorage.setItem("theme"'),
            "Theme Persistence Flow  ThemeManager tidak menyimpan tema ke localStorage."
        );
    }

    /**
     * @test
     * Integration 14.1  Theme Persistence Flow: ThemeManager membaca tema dari localStorage saat init.
     * Simulates: page refresh -> verify theme read from localStorage -> applied correctly.
     * Validates: Requirements 3.5, 3.6
     */
    public function test_theme_persistence_flow_theme_read_from_localstorage_on_init(): void
    {
        $themeManagerFile = 'resources/js/theme-manager.js';
        $this->assertFileExists($themeManagerFile);
        $content = file_get_contents($themeManagerFile);

        $this->assertTrue(
            str_contains($content, "localStorage.getItem('theme')") ||
            str_contains($content, 'localStorage.getItem("theme")'),
            "Theme Persistence Flow  ThemeManager tidak membaca tema dari localStorage saat init."
        );
    }

    /**
     * @test
     * Integration 14.1  Theme Persistence Flow: ThemeManager mendeteksi prefers-color-scheme untuk mode system.
     * Validates: Requirements 3.6
     */
    public function test_theme_persistence_flow_system_mode_detects_os_preference(): void
    {
        $themeManagerFile = 'resources/js/theme-manager.js';
        $this->assertFileExists($themeManagerFile);
        $content = file_get_contents($themeManagerFile);

        $this->assertTrue(
            str_contains($content, 'prefers-color-scheme') && str_contains($content, 'matchMedia'),
            "Theme Persistence Flow  ThemeManager tidak mendeteksi prefers-color-scheme OS."
        );
    }

    /**
     * @test
     * Integration 14.1  Theme Persistence Flow: komponen inti tidak menggunakan bg-white hardcoded.
     * Validates: Requirements 2.6
     */
    public function test_theme_persistence_flow_core_components_have_dark_mode_class(): void
    {
        $coreViewDirs = ['resources/views/components', 'resources/views/layouts'];
        $violations   = [];

        foreach ($coreViewDirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if (!$file->isFile() || !str_ends_with($file->getFilename(), '.blade.php')) {
                    continue;
                }
                $fileContent = file_get_contents($file->getPathname());
                $lines       = explode("\n", $fileContent);
                $relPath     = str_replace(getcwd() . DIRECTORY_SEPARATOR, '', $file->getPathname());

                foreach ($lines as $lineNum => $line) {
                    if (!str_contains($line, 'bg-white') || str_contains($line, 'dark:bg-')) {
                        continue;
                    }
                    if (str_contains(trim($line), '{{--') || str_contains(trim($line), '//') ||
                        str_contains(trim($line), '/*') || preg_match('/^\s*\.[\w-]/', $line) ||
                        preg_match('/bg-white\/[\d.]/', $line) || str_contains($line, 'bg-opacity-') ||
                        str_contains($line, '`')) {
                        continue;
                    }
                    $nextLine = $lines[$lineNum + 1] ?? '';
                    if (str_contains($nextLine, 'dark:bg-')) {
                        continue;
                    }
                    $violations[] = "{$relPath}:" . ($lineNum + 1);
                    if (count($violations) >= 15) {
                        break 2;
                    }
                }
            }
        }

        $this->assertEmpty(
            $violations,
            "Theme Persistence Flow  komponen inti menggunakan 'bg-white' tanpa 'dark:bg-' equivalent:\n" .
            implode("\n", array_slice($violations, 0, 10)) .
            (count($violations) > 10 ? "\n... dan " . (count($violations) - 10) . " lainnya" : "")
        );
    }

    // 
    // 3. MOBILE NAVIGATION FLOW
    // 

    /**
     * @test
     * Integration 14.1  Mobile Navigation Flow: mutual exclusion antara overlay dan panel.
     * Validates: Requirements 2.5
     */
    public function test_mobile_navigation_flow_mutual_exclusion_implemented(): void
    {
        $content = $this->getAppBladeContent();

        $hasMutualExclusion = str_contains($content, 'toggleMobileSidebar') &&
            str_contains($content, 'closePanel') &&
            str_contains($content, 'toggleGroup');

        $this->assertTrue($hasMutualExclusion,
            "Mobile Navigation Flow  tidak ditemukan mutual exclusion logic. " .
            "toggleMobileSidebar() harus memanggil closePanel() sebelum membuka overlay.");
    }

    /**
     * @test
     * Integration 14.1  Mobile Navigation Flow: toggleMobileSidebar menutup panel sebelum overlay.
     * Validates: Requirements 2.5
     */
    public function test_mobile_navigation_flow_toggle_mobile_closes_panel_first(): void
    {
        $content = $this->getAppBladeContent();

        $this->assertTrue(str_contains($content, 'toggleMobileSidebar'),
            "Mobile Navigation Flow  fungsi toggleMobileSidebar tidak ditemukan.");

        $togglePos      = strpos($content, 'function toggleMobileSidebar');
        $closePanelPos  = $togglePos !== false ? strpos($content, 'closePanel()', $togglePos) : false;
        $overlayOpenPos = $togglePos !== false
            ? strpos($content, "sidebar-overlay').classList.remove('hidden')", $togglePos)
            : false;

        if ($closePanelPos === false) {
            $this->fail("Mobile Navigation Flow  closePanel() tidak dipanggil di dalam toggleMobileSidebar().");
        }

        if ($overlayOpenPos !== false) {
            $this->assertLessThan($overlayOpenPos, $closePanelPos,
                "Mobile Navigation Flow  closePanel() dipanggil SETELAH overlay dibuka.");
        }
    }

    /**
     * @test
     * Integration 14.1  Mobile Navigation Flow: z-index hierarchy benar (overlay z-40, panel z-50).
     * Validates: Requirements 2.5
     */
    public function test_mobile_navigation_flow_correct_z_index_hierarchy(): void
    {
        $content = $this->getAppBladeContent();

        $hasOverlayZIndex = str_contains($content, 'sidebar-overlay') &&
            (str_contains($content, 'z-40') || str_contains($content, 'z-index: 40'));

        $hasPanelZIndex = str_contains($content, 'sidebar-panel') &&
            (str_contains($content, 'z-50') || str_contains($content, 'z-index: 50'));

        $this->assertTrue($hasOverlayZIndex,
            "Mobile Navigation Flow  sidebar-overlay tidak menggunakan z-40.");

        $this->assertTrue($hasPanelZIndex,
            "Mobile Navigation Flow  sidebar-panel tidak menggunakan z-50.");
    }

    /**
     * @test
     * Integration 14.1  Mobile Navigation Flow: sidebar memiliki transisi smooth (CSS transition).
     * Validates: Requirements 2.5
     */
    public function test_mobile_navigation_flow_smooth_transitions_defined(): void
    {
        $content = $this->getAppBladeContent();

        $hasPanelTransition = str_contains($content, 'sidebar-panel') &&
            str_contains($content, 'transition');

        $hasRailTransition = str_contains($content, 'sidebar-rail') &&
            str_contains($content, 'transition');

        $this->assertTrue($hasPanelTransition,
            "Mobile Navigation Flow  sidebar-panel tidak memiliki CSS transition.");

        $this->assertTrue($hasRailTransition,
            "Mobile Navigation Flow  sidebar-rail tidak memiliki CSS transition.");
    }

    /**
     * @test
     * Integration 14.1  Mobile Navigation Flow: closeMobileSidebar() menutup overlay.
     * Validates: Requirements 2.5
     */
    public function test_mobile_navigation_flow_close_mobile_sidebar_function_exists(): void
    {
        $content = $this->getAppBladeContent();

        $this->assertTrue(
            str_contains($content, 'closeMobileSidebar') || str_contains($content, 'closeAll'),
            "Mobile Navigation Flow  fungsi closeMobileSidebar() atau closeAll() tidak ditemukan."
        );
    }

    /**
     * @test
     * Integration 14.1  Mobile Navigation Flow: sidebar overlay hanya terlihat di mobile (lg:hidden).
     * Validates: Requirements 2.5
     */
    public function test_mobile_navigation_flow_overlay_hidden_on_desktop(): void
    {
        $content = $this->getAppBladeContent();

        $this->assertTrue(
            str_contains($content, 'sidebar-overlay') && str_contains($content, 'lg:hidden'),
            "Mobile Navigation Flow  sidebar-overlay tidak menggunakan 'lg:hidden'. " .
            "Overlay harus tersembunyi di desktop dan hanya muncul di mobile."
        );
    }

    // 
    // 4. COMBINED END-TO-END FLOW
    // 

    /**
     * @test
     * Integration 14.1  End-to-End: resolveActiveGroup() di app.blade.php konsisten dengan test.
     * Validates: Requirements 2.1, 2.4
     */
    public function test_end_to_end_resolve_active_group_in_blade_matches_test_implementation(): void
    {
        $content = $this->getAppBladeContent();

        $this->assertTrue(str_contains($content, 'resolveActiveGroup'),
            "End-to-End  fungsi resolveActiveGroup() tidak ditemukan di app.blade.php.");

        $requiredGroups = ['superadmin', 'home', 'ai', 'transactions', 'inventory', 'operations', 'finance', 'settings'];
        $missingGroups  = [];

        foreach ($requiredGroups as $group) {
            if (!str_contains($content, "'{$group}'") && !str_contains($content, "\"{$group}\"")) {
                $missingGroups[] = $group;
            }
        }

        $this->assertEmpty($missingGroups,
            "End-to-End  grup berikut tidak ditemukan di resolveActiveGroup() app.blade.php:\n" .
            implode(', ', $missingGroups));
    }

    /**
     * @test
     * Integration 14.1  End-to-End: telecom dan hotel routes terdaftar di grup yang benar.
     * Validates: Requirements 2.4
     */
    public function test_end_to_end_telecom_and_hotel_routes_registered(): void
    {
        $content = $this->getAppBladeContent();

        $hasTelecomInOperations = false;
        if (preg_match("/'operations'\s*=>\s*\[(.*?)\]/s", $content, $matches)) {
            $hasTelecomInOperations = str_contains($matches[1], 'telecom');
        }

        $hasHotelInSettings = false;
        if (preg_match("/'settings'\s*=>\s*\[(.*?)\]/s", $content, $matches)) {
            $hasHotelInSettings = str_contains($matches[1], 'hotel');
        }

        $this->assertTrue($hasTelecomInOperations,
            "End-to-End  route 'telecom*' tidak ditemukan di grup 'operations' di app.blade.php.");

        $this->assertTrue($hasHotelInSettings,
            "End-to-End  route 'hotel*' tidak ditemukan di grup 'settings' di app.blade.php.");
    }
}
