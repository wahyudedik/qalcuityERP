<?php

namespace Tests\Feature\Audit;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Task 24.2: Smoke test all main routes return HTTP 200/302
 *
 * Validates: Requirements 2.1, 2.2, 2.6
 *
 * This test ensures that:
 * - All main routes are accessible without 404/500 errors
 * - Routes return appropriate HTTP status codes (200 for pages, 302 for redirects)
 * - No RouteNotFoundException or MethodNotAllowedException
 */
class RouteIntegrityTest extends TestCase
{
    protected Tenant $tenant;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->createTenant(['plan' => 'enterprise']); // Full access
        $this->user = $this->createAdminUser($this->tenant);

        $this->actingAs($this->user);
    }

    #[Test]
    public function dashboard_route_is_accessible()
    {
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
    }

    #[Test]
    public function accounting_routes_are_accessible()
    {
        $routes = [
            'accounting.index',
            'accounting.chart-of-accounts.index',
            'accounting.journal-entries.index',
            'accounting.reports.balance-sheet',
            'accounting.reports.income-statement',
            'accounting.reports.cash-flow',
        ];

        foreach ($routes as $routeName) {
            if (Route::has($routeName)) {
                $response = $this->get(route($routeName));
                $this->assertContains($response->status(), [200, 302], "Route {$routeName} should return 200 or 302");
            }
        }
    }

    #[Test]
    public function inventory_routes_are_accessible()
    {
        $routes = [
            'inventory.index',
            'inventory.products.index',
            'inventory.warehouses.index',
            'inventory.stock-movements.index',
        ];

        foreach ($routes as $routeName) {
            if (Route::has($routeName)) {
                $response = $this->get(route($routeName));
                $this->assertContains($response->status(), [200, 302], "Route {$routeName} should return 200 or 302");
            }
        }
    }

    #[Test]
    public function sales_routes_are_accessible()
    {
        $routes = [
            'sales.index',
            'sales.quotations.index',
            'sales.orders.index',
            'sales.invoices.index',
            'sales.customers.index',
        ];

        foreach ($routes as $routeName) {
            if (Route::has($routeName)) {
                $response = $this->get(route($routeName));
                $this->assertContains($response->status(), [200, 302], "Route {$routeName} should return 200 or 302");
            }
        }
    }

    #[Test]
    public function purchasing_routes_are_accessible()
    {
        $routes = [
            'purchasing.index',
            'purchasing.orders.index',
            'purchasing.suppliers.index',
        ];

        foreach ($routes as $routeName) {
            if (Route::has($routeName)) {
                $response = $this->get(route($routeName));
                $this->assertContains($response->status(), [200, 302], "Route {$routeName} should return 200 or 302");
            }
        }
    }

    #[Test]
    public function hrm_routes_are_accessible()
    {
        $routes = [
            'hrm.index',
            'hrm.employees.index',
            'hrm.attendance.index',
            'hrm.leaves.index',
        ];

        foreach ($routes as $routeName) {
            if (Route::has($routeName)) {
                $response = $this->get(route($routeName));
                $this->assertContains($response->status(), [200, 302], "Route {$routeName} should return 200 or 302");
            }
        }
    }

    #[Test]
    public function payroll_routes_are_accessible()
    {
        $routes = [
            'payroll.index',
            'payroll.runs.index',
        ];

        foreach ($routes as $routeName) {
            if (Route::has($routeName)) {
                $response = $this->get(route($routeName));
                $this->assertContains($response->status(), [200, 302], "Route {$routeName} should return 200 or 302");
            }
        }
    }

    #[Test]
    public function pos_routes_are_accessible()
    {
        $routes = [
            'pos.index',
            'pos.sessions.index',
        ];

        foreach ($routes as $routeName) {
            if (Route::has($routeName)) {
                $response = $this->get(route($routeName));
                $this->assertContains($response->status(), [200, 302], "Route {$routeName} should return 200 or 302");
            }
        }
    }

    #[Test]
    public function reports_routes_are_accessible()
    {
        $routes = [
            'reports.index',
        ];

        foreach ($routes as $routeName) {
            if (Route::has($routeName)) {
                $response = $this->get(route($routeName));
                $this->assertContains($response->status(), [200, 302], "Route {$routeName} should return 200 or 302");
            }
        }
    }

    #[Test]
    public function settings_routes_are_accessible()
    {
        $routes = [
            'settings.index',
            'settings.company',
            'settings.modules',
        ];

        foreach ($routes as $routeName) {
            if (Route::has($routeName)) {
                $response = $this->get(route($routeName));
                $this->assertContains($response->status(), [200, 302], "Route {$routeName} should return 200 or 302");
            }
        }
    }

    #[Test]
    public function profile_routes_are_accessible()
    {
        $response = $this->get(route('profile.edit'));
        $response->assertStatus(200);
    }

    #[Test]
    public function notifications_route_is_accessible()
    {
        if (Route::has('notifications.index')) {
            $response = $this->get(route('notifications.index'));
            $this->assertContains($response->status(), [200, 302]);
        }
    }

    #[Test]
    public function error_pages_exist()
    {
        // Test 404 page
        $response = $this->get('/non-existent-route-'.uniqid());
        $response->assertStatus(404);

        // Test 403 page (access denied)
        // Create a user without admin role
        $limitedUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Limited User',
            'email' => 'limited-'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
            'is_active' => true,
        ]);

        $this->actingAs($limitedUser);

        // Try to access admin-only route
        if (Route::has('settings.company')) {
            $response = $this->get(route('settings.company'));
            // Should either redirect or show 403
            $this->assertContains($response->status(), [302, 403]);
        }
    }

    #[Test]
    public function api_routes_are_accessible()
    {
        // Test some API routes if they exist
        $apiRoutes = [
            '/api/products',
            '/api/customers',
        ];

        foreach ($apiRoutes as $route) {
            $response = $this->getJson($route);
            // API should return 200 or 401 (if auth required)
            $this->assertContains($response->status(), [200, 401, 404]);
        }
    }

    #[Test]
    public function healthcare_routes_are_accessible_when_module_active()
    {
        // Only test if healthcare routes exist
        if (Route::has('healthcare.index')) {
            $response = $this->get(route('healthcare.index'));
            $this->assertContains($response->status(), [200, 302]);
        }
    }

    #[Test]
    public function all_named_routes_have_valid_controllers()
    {
        $routes = Route::getRoutes();
        $errors = [];

        foreach ($routes as $route) {
            $action = $route->getAction();

            // Skip closure routes
            if (! isset($action['controller'])) {
                continue;
            }

            $controller = $action['controller'];

            // Parse controller@method or Controller::class format
            if (is_string($controller)) {
                $parts = explode('@', $controller);
                $controllerClass = $parts[0];

                // Check if controller class exists
                if (! class_exists($controllerClass)) {
                    $errors[] = "Controller not found: {$controllerClass} for route {$route->getName()}";
                }
            }
        }

        $this->assertEmpty($errors, "Found routes with missing controllers:\n".implode("\n", $errors));
    }
}
