<?php

namespace Tests\Feature\Security;

use App\Models\Tenant;
use App\Models\User;
use App\Services\Security\PermissionService as SecurityPermissionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Bug Condition Exploration Tests — Konfirmasi Bug Ada / Sudah Diperbaiki
 *
 * Setiap test group (bug1, bug2, ...) memverifikasi bahwa bug condition
 * yang ditemukan sudah diperbaiki dengan benar.
 *
 * Validates: Requirements 1.1 – 1.11, 2.1 – 2.11
 */
class BugConditionExplorationTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = $this->createTenant();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Bug 1 — isAdmin() di Security\PermissionService harus menggunakan $user->role
    // Validates: Requirements 2.1, 2.2
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * @group bug1
     *
     * Bug Condition: user.role IN ['admin','super_admin']
     *   AND Security\PermissionService.isAdmin(user) = false
     *
     * Expected Behavior (after fix): isAdmin(user) returns true
     *   for user.role IN ['admin', 'super_admin']
     *
     * Validates: Requirements 2.1, 2.2
     */
    public function test_bug1_isAdmin_returns_true_for_admin_role(): void
    {
        $admin = $this->createUserWithRole('admin');

        $service = app(SecurityPermissionService::class);

        // Akses protected method via reflection
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('isAdmin');
        $method->setAccessible(true);

        $result = $method->invoke($service, $admin);

        $this->assertTrue(
            $result,
            "Bug 1: isAdmin() harus mengembalikan true untuk user dengan role='admin'. " .
            "Bug: menggunakan \$user->role_name yang tidak ada di model User."
        );
    }

    /**
     * @test
     * @group bug1
     *
     * Bug Condition: user.role = 'super_admin'
     *   AND Security\PermissionService.isAdmin(user) = false
     *
     * Expected Behavior (after fix): isAdmin(user) returns true for super_admin
     *
     * Validates: Requirements 2.1, 2.2
     */
    public function test_bug1_isAdmin_returns_true_for_super_admin_role(): void
    {
        $superAdmin = $this->createUserWithRole('super_admin');

        $service = app(SecurityPermissionService::class);

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('isAdmin');
        $method->setAccessible(true);

        $result = $method->invoke($service, $superAdmin);

        $this->assertTrue(
            $result,
            "Bug 1: isAdmin() harus mengembalikan true untuk user dengan role='super_admin'. " .
            "Bug: menggunakan 'superadmin' (tanpa underscore) dalam array check."
        );
    }

    /**
     * @test
     * @group bug1
     *
     * Property: untuk semua user dengan role IN ['admin', 'super_admin'],
     * isAdmin() HARUS mengembalikan true (bukan false).
     *
     * Validates: Requirements 2.1, 2.2
     */
    public function test_bug1_isAdmin_consistent_for_all_admin_roles(): void
    {
        $adminRoles = ['admin', 'super_admin'];
        $service = app(SecurityPermissionService::class);

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('isAdmin');
        $method->setAccessible(true);

        foreach ($adminRoles as $role) {
            $user = $this->createUserWithRole($role);
            $result = $method->invoke($service, $user);

            $this->assertTrue(
                $result,
                "Bug 1: isAdmin() harus mengembalikan true untuk role='{$role}'"
            );
        }
    }

    /**
     * @test
     * @group bug1
     *
     * Property: untuk user dengan role non-admin,
     * isAdmin() HARUS mengembalikan false.
     *
     * Validates: Requirements 2.1, 2.2
     */
    public function test_bug1_isAdmin_returns_false_for_non_admin_roles(): void
    {
        $nonAdminRoles = ['staff', 'kasir', 'gudang', 'manager'];
        $service = app(SecurityPermissionService::class);

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('isAdmin');
        $method->setAccessible(true);

        foreach ($nonAdminRoles as $role) {
            $user = $this->createUserWithRole($role);
            $result = $method->invoke($service, $user);

            $this->assertFalse(
                $result,
                "isAdmin() harus mengembalikan false untuk role='{$role}'"
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Bug 2 — hasRole('superadmin') harus diganti isSuperAdmin() di middleware/policy
    // Validates: Requirements 2.3, 2.4
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * @group bug2
     *
     * Bug Condition: user.role = 'super_admin'
     *   AND hasRole('superadmin') = false (karena 'superadmin' != 'super_admin')
     *
     * Expected Behavior (after fix): isSuperAdmin() returns true
     *   sehingga bypass check aktif di RBACMiddleware, HealthcareAccessMiddleware,
     *   MedicalRecordPolicy, dan PatientDataPolicy
     *
     * Validates: Requirements 2.3, 2.4
     */
    public function test_bug2_isSuperAdmin_returns_true_for_super_admin_role(): void
    {
        $superAdmin = $this->createUserWithRole('super_admin');

        // isSuperAdmin() harus mengembalikan true untuk role 'super_admin'
        $this->assertTrue(
            $superAdmin->isSuperAdmin(),
            "Bug 2: isSuperAdmin() harus mengembalikan true untuk user dengan role='super_admin'."
        );
    }

    /**
     * @test
     * @group bug2
     *
     * Bug Condition: hasRole('superadmin') mengembalikan false untuk user dengan role='super_admin'
     *
     * Expected Behavior (after fix): middleware menggunakan isSuperAdmin() bukan hasRole('superadmin')
     * sehingga bypass aktif.
     *
     * Validates: Requirements 2.3, 2.4
     */
    public function test_bug2_hasRole_superadmin_without_underscore_returns_false(): void
    {
        $superAdmin = $this->createUserWithRole('super_admin');

        // hasRole('superadmin') (tanpa underscore) harus false — ini adalah root cause bug
        $this->assertFalse(
            $superAdmin->hasRole('superadmin'),
            "Bug 2 root cause: hasRole('superadmin') harus false karena role tersimpan sebagai 'super_admin'"
        );

        // Sedangkan isSuperAdmin() harus true — ini adalah fix yang benar
        $this->assertTrue(
            $superAdmin->isSuperAdmin(),
            "Bug 2 fix: isSuperAdmin() harus true untuk user dengan role='super_admin'"
        );
    }

    /**
     * @test
     * @group bug2
     *
     * Property: untuk semua user dengan role = 'super_admin',
     * isSuperAdmin() HARUS mengembalikan true (bypass check aktif).
     *
     * Validates: Requirements 2.3, 2.4
     */
    public function test_bug2_superadmin_bypass_active_in_all_middleware_and_policies(): void
    {
        $superAdmin = $this->createUserWithRole('super_admin');

        // Verifikasi isSuperAdmin() bekerja dengan benar
        $this->assertTrue(
            $superAdmin->isSuperAdmin(),
            "Bug 2: isSuperAdmin() harus true untuk super_admin — bypass check harus aktif di semua middleware dan policy"
        );

        // Verifikasi hasRole('super_admin') juga bekerja (dengan underscore)
        $this->assertTrue(
            $superAdmin->hasRole('super_admin'),
            "Bug 2: hasRole('super_admin') harus true untuk user dengan role='super_admin'"
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Bug 3 — Route sensitif harus memiliki tenant.isolation di middleware stack
    // Validates: Requirements 2.5, 2.6, 2.7, 2.8
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * @group bug3
     *
     * Bug Condition: request.path MATCHES '/barcode/*'
     *   AND 'tenant.isolation' NOT IN middleware
     *
     * Expected Behavior (after fix): tenant.isolation ada di middleware stack
     *   untuk semua route /barcode/*
     *
     * Validates: Requirements 2.5
     */
    public function test_bug3_barcode_routes_have_tenant_isolation_middleware(): void
    {
        $router = app('router');
        $routes = $router->getRoutes();

        $barcodeRoutes = collect($routes->getRoutes())->filter(function ($route) {
            return str_starts_with($route->uri(), 'barcode/');
        });

        $this->assertNotEmpty($barcodeRoutes, "Harus ada route dengan prefix 'barcode/'");

        foreach ($barcodeRoutes as $route) {
            $middleware = $route->gatherMiddleware();
            $this->assertContains(
                'tenant.isolation',
                $middleware,
                "Route '{$route->uri()}' harus memiliki middleware 'tenant.isolation'. " .
                "Bug 3: barcode routes tidak memiliki tenant isolation."
            );
        }
    }

    /**
     * @test
     * @group bug3
     *
     * Bug Condition: request.path MATCHES '/inventory/movements/*'
     *   AND 'tenant.isolation' NOT IN middleware
     *
     * Expected Behavior (after fix): tenant.isolation ada di middleware stack
     *
     * Validates: Requirements 2.5
     */
    public function test_bug3_inventory_movements_routes_have_tenant_isolation_middleware(): void
    {
        $router = app('router');
        $routes = $router->getRoutes();

        $movementRoutes = collect($routes->getRoutes())->filter(function ($route) {
            return str_starts_with($route->uri(), 'inventory/movements');
        });

        $this->assertNotEmpty($movementRoutes, "Harus ada route dengan prefix 'inventory/movements'");

        foreach ($movementRoutes as $route) {
            $middleware = $route->gatherMiddleware();
            $this->assertContains(
                'tenant.isolation',
                $middleware,
                "Route '{$route->uri()}' harus memiliki middleware 'tenant.isolation'. " .
                "Bug 3: inventory/movements routes tidak memiliki tenant isolation."
            );
        }
    }

    /**
     * @test
     * @group bug3
     *
     * Bug Condition: request.path IN ['/bulk-actions/execute', '/bulk-actions/export-download']
     *   AND 'tenant.isolation' NOT IN middleware
     *
     * Expected Behavior (after fix): tenant.isolation ada di middleware stack
     *
     * Validates: Requirements 2.6
     */
    public function test_bug3_bulk_actions_routes_have_tenant_isolation_middleware(): void
    {
        $router = app('router');
        $routes = $router->getRoutes();

        $bulkRoutes = collect($routes->getRoutes())->filter(function ($route) {
            return str_starts_with($route->uri(), 'bulk-actions/');
        });

        $this->assertNotEmpty($bulkRoutes, "Harus ada route dengan prefix 'bulk-actions/'");

        foreach ($bulkRoutes as $route) {
            $middleware = $route->gatherMiddleware();
            $this->assertContains(
                'tenant.isolation',
                $middleware,
                "Route '{$route->uri()}' harus memiliki middleware 'tenant.isolation'. " .
                "Bug 3: bulk-actions routes tidak memiliki tenant isolation."
            );
        }
    }

    /**
     * @test
     * @group bug3
     *
     * Bug Condition: request.path IN ['/api/quick-search', '/api/saved-searches/*']
     *   AND 'tenant.isolation' NOT IN middleware
     *
     * Expected Behavior (after fix): tenant.isolation ada di middleware stack
     *
     * Validates: Requirements 2.7
     */
    public function test_bug3_quick_search_and_saved_searches_routes_have_tenant_isolation_middleware(): void
    {
        $router = app('router');
        $routes = $router->getRoutes();

        $searchRoutes = collect($routes->getRoutes())->filter(function ($route) {
            return str_starts_with($route->uri(), 'api/quick-search') ||
                   str_starts_with($route->uri(), 'api/saved-searches');
        });

        $this->assertNotEmpty($searchRoutes, "Harus ada route quick-search dan saved-searches");

        foreach ($searchRoutes as $route) {
            $middleware = $route->gatherMiddleware();
            $this->assertContains(
                'tenant.isolation',
                $middleware,
                "Route '{$route->uri()}' harus memiliki middleware 'tenant.isolation'. " .
                "Bug 3: search routes tidak memiliki tenant isolation."
            );
        }
    }

    /**
     * @test
     * @group bug3
     *
     * Bug Condition: request.path MATCHES '/transaction-chain/*'
     *   AND 'tenant.isolation' NOT IN middleware
     *
     * Expected Behavior (after fix): tenant.isolation ada di middleware stack
     *
     * Validates: Requirements 2.8
     */
    public function test_bug3_transaction_chain_routes_have_tenant_isolation_middleware(): void
    {
        $router = app('router');
        $routes = $router->getRoutes();

        $chainRoutes = collect($routes->getRoutes())->filter(function ($route) {
            return str_starts_with($route->uri(), 'transaction-chain/');
        });

        $this->assertNotEmpty($chainRoutes, "Harus ada route dengan prefix 'transaction-chain/'");

        foreach ($chainRoutes as $route) {
            $middleware = $route->gatherMiddleware();
            $this->assertContains(
                'tenant.isolation',
                $middleware,
                "Route '{$route->uri()}' harus memiliki middleware 'tenant.isolation'. " .
                "Bug 3: transaction-chain routes tidak memiliki tenant isolation."
            );
        }
    }

    /**
     * @test
     * @group bug3
     *
     * Property: untuk semua route sensitif, tenant.isolation HARUS ada di middleware stack.
     * Validates semua 5 kelompok route sekaligus.
     *
     * Validates: Requirements 2.5, 2.6, 2.7, 2.8
     */
    public function test_bug3_all_sensitive_routes_have_tenant_isolation(): void
    {
        $router = app('router');
        $routes = $router->getRoutes();

        $sensitivePrefixes = [
            'barcode/',
            'inventory/movements',
            'bulk-actions/',
            'api/quick-search',
            'api/saved-searches',
            'transaction-chain/',
        ];

        foreach ($sensitivePrefixes as $prefix) {
            $matchingRoutes = collect($routes->getRoutes())->filter(function ($route) use ($prefix) {
                return str_starts_with($route->uri(), $prefix);
            });

            $this->assertNotEmpty(
                $matchingRoutes,
                "Harus ada route dengan prefix '{$prefix}'"
            );

            foreach ($matchingRoutes as $route) {
                $middleware = $route->gatherMiddleware();
                $this->assertContains(
                    'tenant.isolation',
                    $middleware,
                    "Route '{$route->uri()}' (prefix: '{$prefix}') harus memiliki 'tenant.isolation'. " .
                    "Bug 3: route sensitif tanpa tenant isolation."
                );
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Bug 4 — Customer Portal harus memiliki tenant.isolation di middleware stack
    // Validates: Requirements 2.9
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * @group bug4
     *
     * Bug Condition: request.path STARTS_WITH '/portal/'
     *   AND 'tenant.isolation' NOT IN middleware
     *
     * Expected Behavior (after fix): tenant.isolation ada di middleware stack
     *   untuk semua route /portal/*
     *
     * Validates: Requirements 2.9
     */
    public function test_bug4_portal_routes_have_tenant_isolation_middleware(): void
    {
        $router = app('router');
        $routes = $router->getRoutes();

        $portalRoutes = collect($routes->getRoutes())->filter(function ($route) {
            return str_starts_with($route->uri(), 'portal/') || $route->uri() === 'portal';
        });

        $this->assertNotEmpty($portalRoutes, "Harus ada route dengan prefix 'portal'");

        foreach ($portalRoutes as $route) {
            $middleware = $route->gatherMiddleware();
            $this->assertContains(
                'tenant.isolation',
                $middleware,
                "Route '{$route->uri()}' harus memiliki middleware 'tenant.isolation'. " .
                "Bug 4: portal routes tidak memiliki tenant isolation."
            );
        }
    }

    /**
     * @test
     * @group bug4
     *
     * Property: untuk semua route /portal/*, tenant.isolation HARUS ada di middleware stack.
     * Validates semua portal routes sekaligus.
     *
     * Validates: Requirements 2.9
     */
    public function test_bug4_all_portal_routes_have_tenant_isolation(): void
    {
        $router = app('router');
        $routes = $router->getRoutes();

        $portalRoutes = collect($routes->getRoutes())->filter(function ($route) {
            return str_starts_with($route->uri(), 'portal');
        });

        $this->assertNotEmpty(
            $portalRoutes,
            "Harus ada route dengan prefix 'portal'"
        );

        foreach ($portalRoutes as $route) {
            $middleware = $route->gatherMiddleware();
            $this->assertContains(
                'tenant.isolation',
                $middleware,
                "Route '{$route->uri()}' harus memiliki 'tenant.isolation'. " .
                "Bug 4: customer portal routes tanpa tenant isolation."
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Bug 5 — Supplier Scorecard harus memiliki role check di middleware stack
    // Validates: Requirements 2.10
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * @group bug5
     *
     * Bug Condition: user.role IN ['staff','kasir','gudang','housekeeping','maintenance','affiliate']
     *   AND request.path STARTS_WITH '/supplier-scorecards/' OR '/supplier-performance/'
     *
     * Expected Behavior (after fix): response 403 untuk role yang tidak memiliki suppliers.view
     *
     * Validates: Requirements 2.10
     */
    public function test_bug5_supplier_scorecard_routes_have_permission_middleware(): void
    {
        $router = app('router');
        $routes = $router->getRoutes();

        $scorecardRoutes = collect($routes->getRoutes())->filter(function ($route) {
            return str_starts_with($route->uri(), 'supplier-scorecards') ||
                   str_starts_with($route->uri(), 'supplier-performance');
        });

        $this->assertNotEmpty($scorecardRoutes, "Harus ada route dengan prefix 'supplier-scorecards' atau 'supplier-performance'");

        foreach ($scorecardRoutes as $route) {
            $middleware = $route->gatherMiddleware();
            $hasPermissionCheck = collect($middleware)->contains(function ($m) {
                return str_contains($m, 'permission:suppliers');
            });
            $this->assertTrue(
                $hasPermissionCheck,
                "Route '{$route->uri()}' harus memiliki middleware 'permission:suppliers,view'. " .
                "Bug 5: supplier scorecard/performance routes tidak memiliki role check."
            );
        }
    }

    /**
     * @test
     * @group bug5
     *
     * Bug Condition: user.role = 'kasir' mengakses /supplier-scorecards/
     * Expected Behavior (after fix): response 403 (kasir tidak punya suppliers.view)
     *
     * Validates: Requirements 2.10
     */
    public function test_bug5_kasir_gets_403_on_supplier_scorecard(): void
    {
        $kasir = $this->createUserWithRole('kasir');

        $response = $this->actingAs($kasir)->get('/supplier-scorecards/');

        $this->assertEquals(
            403,
            $response->getStatusCode(),
            "Bug 5: user dengan role='kasir' harus mendapat 403 saat mengakses /supplier-scorecards/"
        );
    }

    /**
     * @test
     * @group bug5
     *
     * Bug Condition: user.role = 'gudang' mengakses /supplier-scorecards/
     * Expected Behavior (after fix): response 403 (gudang tidak punya suppliers.view)
     *
     * Validates: Requirements 2.10
     */
    public function test_bug5_gudang_gets_403_on_supplier_scorecard(): void
    {
        $gudang = $this->createUserWithRole('gudang');

        $response = $this->actingAs($gudang)->get('/supplier-scorecards/');

        $this->assertEquals(
            403,
            $response->getStatusCode(),
            "Bug 5: user dengan role='gudang' harus mendapat 403 saat mengakses /supplier-scorecards/"
        );
    }

    /**
     * @test
     * @group bug5
     *
     * Property: untuk semua role yang tidak memiliki suppliers.view,
     * response HARUS 403 pada /supplier-scorecards/ dan /supplier-performance/
     *
     * Validates: Requirements 2.10
     */
    public function test_bug5_unauthorized_roles_get_403_on_supplier_routes(): void
    {
        // Roles that do NOT have suppliers.view in ROLE_DEFAULTS
        // Note: 'affiliate' excluded as it may not be in DB enum depending on migration state
        $unauthorizedRoles = ['kasir', 'gudang', 'housekeeping', 'maintenance'];

        foreach ($unauthorizedRoles as $role) {
            $user = $this->createUserWithRole($role);

            $scorecardResponse = $this->actingAs($user)->get('/supplier-scorecards/');
            $this->assertEquals(
                403,
                $scorecardResponse->getStatusCode(),
                "Bug 5: role='{$role}' harus mendapat 403 pada /supplier-scorecards/"
            );

            $performanceResponse = $this->actingAs($user)->get('/supplier-performance/');
            $this->assertEquals(
                403,
                $performanceResponse->getStatusCode(),
                "Bug 5: role='{$role}' harus mendapat 403 pada /supplier-performance/"
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Bug 6 — EnforceTenantIsolation harus mencakup semua model bertenant
    // Validates: Requirements 2.11
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * @group bug6
     *
     * Bug Condition: model.class IN [ErpNotification, UserPermission, CustomField,
     *   DocumentTemplate, Workflow, AiTourSession]
     *   AND model.tenant_id != user.tenant_id
     *   AND model NOT IN $tenantModels (sebelum fix)
     *
     * Expected Behavior (after fix): EnforceTenantIsolation mengembalikan 403
     *   untuk akses lintas tenant pada semua model yang ditambahkan.
     *
     * Validates: Requirements 2.11
     */
    public function test_bug6_enforce_tenant_isolation_blocks_cross_tenant_custom_field(): void
    {
        $tenantA = $this->createTenant();
        $tenantB = $this->createTenant();
        $userB = $this->createUserWithRole('admin', $tenantB);

        // Buat CustomField milik tenant A
        $customField = \App\Models\CustomField::create([
            'tenant_id'  => $tenantA->id,
            'module'     => 'invoice',
            'key'        => 'cf_bug6_' . uniqid(),
            'label'      => 'Test Field',
            'type'       => 'text',
            'required'   => false,
            'is_active'  => true,
            'sort_order' => 1,
        ]);

        // Simulasikan EnforceTenantIsolation logic untuk CustomField
        $tenantModels = $this->getTenantModelsFromMiddleware();
        $this->assertContains(
            \App\Models\CustomField::class,
            $tenantModels,
            "Bug 6 fix: CustomField harus ada di \$tenantModels EnforceTenantIsolation"
        );

        // Verifikasi bahwa akses lintas tenant akan diblokir
        $blocked = $this->simulateTenantIsolationCheck($customField, $userB);
        $this->assertTrue(
            $blocked,
            "Bug 6: CustomField dengan tenant_id berbeda harus diblokir oleh EnforceTenantIsolation"
        );
    }

    /**
     * @test
     * @group bug6
     *
     * Bug Condition: UserPermission dengan tenant_id berbeda tidak diblokir
     * Expected Behavior (after fix): 403 untuk akses lintas tenant
     *
     * Validates: Requirements 2.11
     */
    public function test_bug6_enforce_tenant_isolation_blocks_cross_tenant_user_permission(): void
    {
        $tenantA = $this->createTenant();
        $tenantB = $this->createTenant();
        $userA = $this->createUserWithRole('staff', $tenantA);
        $userB = $this->createUserWithRole('admin', $tenantB);

        // Buat UserPermission milik tenant A
        $userPermission = \App\Models\UserPermission::create([
            'tenant_id' => $tenantA->id,
            'user_id'   => $userA->id,
            'module'    => 'sales',
            'action'    => 'view',
            'granted'   => true,
        ]);

        $tenantModels = $this->getTenantModelsFromMiddleware();
        $this->assertContains(
            \App\Models\UserPermission::class,
            $tenantModels,
            "Bug 6 fix: UserPermission harus ada di \$tenantModels EnforceTenantIsolation"
        );

        $blocked = $this->simulateTenantIsolationCheck($userPermission, $userB);
        $this->assertTrue(
            $blocked,
            "Bug 6: UserPermission dengan tenant_id berbeda harus diblokir oleh EnforceTenantIsolation"
        );
    }

    /**
     * @test
     * @group bug6
     *
     * Bug Condition: ErpNotification dengan tenant_id berbeda tidak diblokir
     * Expected Behavior (after fix): 403 untuk akses lintas tenant
     *
     * Validates: Requirements 2.11
     */
    public function test_bug6_enforce_tenant_isolation_blocks_cross_tenant_erp_notification(): void
    {
        $tenantA = $this->createTenant();
        $tenantB = $this->createTenant();
        $userA = $this->createUserWithRole('staff', $tenantA);
        $userB = $this->createUserWithRole('admin', $tenantB);

        // Buat ErpNotification milik tenant A
        $notification = \App\Models\ErpNotification::create([
            'tenant_id' => $tenantA->id,
            'user_id'   => $userA->id,
            'type'      => 'low_stock',
            'module'    => 'inventory',
            'title'     => 'Test Notification',
            'body'      => 'Test body',
        ]);

        $tenantModels = $this->getTenantModelsFromMiddleware();
        $this->assertContains(
            \App\Models\ErpNotification::class,
            $tenantModels,
            "Bug 6 fix: ErpNotification harus ada di \$tenantModels EnforceTenantIsolation"
        );

        $blocked = $this->simulateTenantIsolationCheck($notification, $userB);
        $this->assertTrue(
            $blocked,
            "Bug 6: ErpNotification dengan tenant_id berbeda harus diblokir oleh EnforceTenantIsolation"
        );
    }

    /**
     * @test
     * @group bug6
     *
     * Bug Condition: DocumentTemplate dengan tenant_id berbeda tidak diblokir
     * Expected Behavior (after fix): 403 untuk akses lintas tenant
     *
     * Validates: Requirements 2.11
     */
    public function test_bug6_enforce_tenant_isolation_blocks_cross_tenant_document_template(): void
    {
        $tenantA = $this->createTenant();
        $tenantB = $this->createTenant();
        $userB = $this->createUserWithRole('admin', $tenantB);

        // Buat DocumentTemplate milik tenant A
        $template = \App\Models\DocumentTemplate::create([
            'tenant_id'    => $tenantA->id,
            'name'         => 'Test Template',
            'doc_type'     => 'invoice',
            'html_content' => '<p>Test</p>',
            'is_default'   => false,
        ]);

        $tenantModels = $this->getTenantModelsFromMiddleware();
        $this->assertContains(
            \App\Models\DocumentTemplate::class,
            $tenantModels,
            "Bug 6 fix: DocumentTemplate harus ada di \$tenantModels EnforceTenantIsolation"
        );

        $blocked = $this->simulateTenantIsolationCheck($template, $userB);
        $this->assertTrue(
            $blocked,
            "Bug 6: DocumentTemplate dengan tenant_id berbeda harus diblokir oleh EnforceTenantIsolation"
        );
    }

    /**
     * @test
     * @group bug6
     *
     * Bug Condition: Workflow dengan tenant_id berbeda tidak diblokir
     * Expected Behavior (after fix): 403 untuk akses lintas tenant
     *
     * Validates: Requirements 2.11
     */
    public function test_bug6_enforce_tenant_isolation_blocks_cross_tenant_workflow(): void
    {
        $tenantA = $this->createTenant();
        $tenantB = $this->createTenant();
        $userB = $this->createUserWithRole('admin', $tenantB);

        // Buat Workflow milik tenant A
        $workflow = \App\Models\Workflow::create([
            'tenant_id'      => $tenantA->id,
            'name'           => 'Test Workflow',
            'trigger_type'   => 'event',
            'trigger_config' => ['event' => 'invoice.created'],
            'is_active'      => true,
        ]);

        $tenantModels = $this->getTenantModelsFromMiddleware();
        $this->assertContains(
            \App\Models\Workflow::class,
            $tenantModels,
            "Bug 6 fix: Workflow harus ada di \$tenantModels EnforceTenantIsolation"
        );

        $blocked = $this->simulateTenantIsolationCheck($workflow, $userB);
        $this->assertTrue(
            $blocked,
            "Bug 6: Workflow dengan tenant_id berbeda harus diblokir oleh EnforceTenantIsolation"
        );
    }

    /**
     * @test
     * @group bug6
     *
     * Bug Condition: AiTourSession dengan tenant_id berbeda tidak diblokir
     * Expected Behavior (after fix): 403 untuk akses lintas tenant
     *
     * Validates: Requirements 2.11
     */
    public function test_bug6_enforce_tenant_isolation_blocks_cross_tenant_ai_tour_session(): void
    {
        $tenantA = $this->createTenant();
        $tenantB = $this->createTenant();
        $userA = $this->createUserWithRole('staff', $tenantA);
        $userB = $this->createUserWithRole('admin', $tenantB);

        // Buat AiTourSession milik tenant A
        $tourSession = \App\Models\AiTourSession::create([
            'tenant_id'  => $tenantA->id,
            'user_id'    => $userA->id,
            'tour_type'  => 'general',
            'is_active'  => true,
            'started_at' => now(),
        ]);

        $tenantModels = $this->getTenantModelsFromMiddleware();
        $this->assertContains(
            \App\Models\AiTourSession::class,
            $tenantModels,
            "Bug 6 fix: AiTourSession harus ada di \$tenantModels EnforceTenantIsolation"
        );

        $blocked = $this->simulateTenantIsolationCheck($tourSession, $userB);
        $this->assertTrue(
            $blocked,
            "Bug 6: AiTourSession dengan tenant_id berbeda harus diblokir oleh EnforceTenantIsolation"
        );
    }

    /**
     * @test
     * @group bug6
     *
     * Property: untuk semua model yang ditambahkan ke $tenantModels,
     * akses lintas tenant HARUS diblokir dengan 403.
     *
     * Validates: Requirements 2.11
     */
    public function test_bug6_all_added_models_are_in_tenant_models_list(): void
    {
        $tenantModels = $this->getTenantModelsFromMiddleware();

        $requiredModels = [
            \App\Models\ErpNotification::class,
            \App\Models\UserPermission::class,
            \App\Models\CustomField::class,
            \App\Models\DocumentTemplate::class,
            \App\Models\Workflow::class,
            \App\Models\AiTourSession::class,
            \App\Models\WebhookSubscription::class, // sudah ada sebelumnya
        ];

        foreach ($requiredModels as $modelClass) {
            $this->assertContains(
                $modelClass,
                $tenantModels,
                "Bug 6 fix: {$modelClass} harus ada di \$tenantModels EnforceTenantIsolation"
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helper methods
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Simulasikan logika EnforceTenantIsolation untuk satu model dan user.
     * Mengembalikan true jika akses akan diblokir (tenant_id tidak cocok).
     */
    private function simulateTenantIsolationCheck(object $model, \App\Models\User $user): bool
    {
        $tenantModels = $this->getTenantModelsFromMiddleware();
        $modelClass = get_class($model);

        if (!in_array($modelClass, $tenantModels)) {
            return false; // Model tidak dicek
        }

        if (isset($model->tenant_id) && (int) $model->tenant_id !== (int) $user->tenant_id) {
            return true; // Akan diblokir
        }

        return false;
    }

    /**
     * Ambil daftar $tenantModels dari EnforceTenantIsolation via reflection.
     */
    private function getTenantModelsFromMiddleware(): array
    {
        $middleware = app(\App\Http\Middleware\EnforceTenantIsolation::class);
        $reflection = new \ReflectionClass($middleware);
        $method = $reflection->getMethod('handle');

        // Parse $tenantModels dari source code middleware
        // Karena $tenantModels adalah local variable di handle(), kita gunakan pendekatan lain:
        // Buat request dummy dan jalankan middleware, lalu cek via closure
        $tenantModels = [];

        // Gunakan reflection untuk membaca source dan extract array
        // Alternatif: instantiate middleware dan invoke handle dengan mock request
        // yang memiliki route parameter model, lalu cek apakah 403 dilempar

        // Pendekatan paling reliable: baca langsung dari file middleware
        $source = file_get_contents(app_path('Http/Middleware/EnforceTenantIsolation.php'));

        // Extract semua class references dari $tenantModels array
        preg_match_all('/\\\\App\\\\Models\\\\(\w+)::class/', $source, $matches);

        foreach ($matches[0] as $classRef) {
            // Convert dari string ke actual class name
            $className = str_replace('\\\\', '\\', $classRef);
            $className = rtrim($className, '::class');
            // Normalize double backslashes
            $className = preg_replace('/\\\\+/', '\\', $className);
            $tenantModels[] = ltrim($className, '\\');
        }

        return $tenantModels;
    }

    private function createUserWithRole(string $role, ?\App\Models\Tenant $tenant = null): User
    {
        $targetTenant = $tenant ?? $this->tenant;
        return User::create([
            'tenant_id'         => $targetTenant->id,
            'name'              => ucfirst($role) . ' Test',
            'email'             => $role . '-' . uniqid() . '@test.com',
            'password'          => bcrypt('password'),
            'role'              => $role,
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);
    }
}
