<?php

namespace Tests\Feature\Audit;

use App\Models\ActivityLog;
use App\Models\AuditTrail;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\ErrorLog;
use App\Models\FailedJob;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Migration;
use App\Models\Notification;
use App\Models\PersonalAccessToken;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Supplier;
use App\Models\SystemSetting;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Warehouse;
use App\Traits\BelongsToTenant;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Task 24.3: Verify all tenant models use BelongsToTenant trait
 *
 * Validates: Requirements 3.1, 21.1
 *
 * This test ensures that:
 * - All tenant-scoped models use the BelongsToTenant trait
 * - Tenant isolation is enforced at the model level
 * - No model can bypass tenant filtering accidentally
 */
class ModelTenantScopeTest extends TestCase
{
    /**
     * Get all model classes from app/Models directory
     */
    protected function getAllModelClasses(): array
    {
        $modelPath = app_path('Models');
        $models = [];

        $files = File::allFiles($modelPath);

        foreach ($files as $file) {
            $className = 'App\\Models\\'.str_replace(
                ['/', '.php'],
                ['\\', ''],
                $file->getRelativePathname()
            );

            if (class_exists($className)) {
                $models[] = $className;
            }
        }

        return $models;
    }

    /**
     * Models that should NOT have tenant scope (system-wide models)
     */
    protected function getExcludedModels(): array
    {
        return [
            Tenant::class,
            User::class, // Has tenant_id but uses custom logic
            ActivityLog::class, // System-wide logging
            AuditTrail::class, // System-wide audit
            ErrorLog::class, // System-wide errors
            SystemSetting::class, // System-wide settings
            Migration::class, // Laravel migrations table
            FailedJob::class, // Laravel failed jobs
            PersonalAccessToken::class, // Laravel Sanctum
            Notification::class, // Laravel notifications (polymorphic)
        ];
    }

    /**
     * Check if a model should have tenant scope based on its table structure
     */
    protected function shouldHaveTenantScope(string $modelClass): bool
    {
        // Skip if in excluded list
        if (in_array($modelClass, $this->getExcludedModels())) {
            return false;
        }

        try {
            $model = new $modelClass;

            // Check if model has tenant_id column
            if (! $model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'tenant_id')) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            // Skip models that can't be instantiated
            return false;
        }
    }

    #[Test]
    public function all_tenant_scoped_models_use_belongs_to_tenant_trait()
    {
        $models = $this->getAllModelClasses();
        $missingTrait = [];

        foreach ($models as $modelClass) {
            if (! $this->shouldHaveTenantScope($modelClass)) {
                continue;
            }

            $traits = class_uses_recursive($modelClass);

            if (! in_array(BelongsToTenant::class, $traits)) {
                $missingTrait[] = $modelClass;
            }
        }

        $this->assertEmpty(
            $missingTrait,
            "The following models have tenant_id column but don't use BelongsToTenant trait:\n".
            implode("\n", $missingTrait)
        );
    }

    #[Test]
    public function belongs_to_tenant_trait_adds_global_scope()
    {
        // Test with Invoice model (known to use BelongsToTenant)
        $tenant1 = $this->createTenant(['name' => 'Tenant 1']);
        $tenant2 = $this->createTenant(['name' => 'Tenant 2']);

        $user1 = $this->createAdminUser($tenant1);
        $user2 = $this->createAdminUser($tenant2);

        // Create customers for both tenants
        $customer1 = $this->createCustomer($tenant1->id, ['name' => 'Customer T1']);
        $customer2 = $this->createCustomer($tenant2->id, ['name' => 'Customer T2']);

        // Login as tenant 1 user
        $this->actingAs($user1);

        // Query should only return tenant 1 customers
        $customers = Customer::all();
        $this->assertCount(1, $customers);
        $this->assertEquals($customer1->id, $customers->first()->id);
        $this->assertEquals($tenant1->id, $customers->first()->tenant_id);

        // Login as tenant 2 user
        $this->actingAs($user2);

        // Query should only return tenant 2 customers
        $customers = Customer::all();
        $this->assertCount(1, $customers);
        $this->assertEquals($customer2->id, $customers->first()->id);
        $this->assertEquals($tenant2->id, $customers->first()->tenant_id);
    }

    #[Test]
    public function belongs_to_tenant_trait_auto_sets_tenant_id_on_create()
    {
        $tenant = $this->createTenant();
        $user = $this->createAdminUser($tenant);

        $this->actingAs($user);

        // Create customer without explicitly setting tenant_id
        $customer = Customer::create([
            'name' => 'Auto Tenant Customer',
            'is_active' => true,
        ]);

        // tenant_id should be automatically set
        $this->assertEquals($tenant->id, $customer->tenant_id);
    }

    #[Test]
    public function without_tenant_scope_bypasses_filtering()
    {
        $tenant1 = $this->createTenant(['name' => 'Tenant 1']);
        $tenant2 = $this->createTenant(['name' => 'Tenant 2']);

        $user1 = $this->createAdminUser($tenant1);

        $customer1 = $this->createCustomer($tenant1->id);
        $customer2 = $this->createCustomer($tenant2->id);

        $this->actingAs($user1);

        // Normal query - only tenant 1
        $customers = Customer::all();
        $this->assertCount(1, $customers);

        // Without scope - all tenants
        $allCustomers = Customer::withoutTenantScope()->get();
        $this->assertCount(2, $allCustomers);
    }

    #[Test]
    public function for_tenant_scope_filters_by_specific_tenant()
    {
        $tenant1 = $this->createTenant(['name' => 'Tenant 1']);
        $tenant2 = $this->createTenant(['name' => 'Tenant 2']);

        $user1 = $this->createAdminUser($tenant1);

        $customer1 = $this->createCustomer($tenant1->id);
        $customer2 = $this->createCustomer($tenant2->id);

        $this->actingAs($user1);

        // Query for specific tenant
        $tenant2Customers = Customer::forTenant($tenant2->id)->get();
        $this->assertCount(1, $tenant2Customers);
        $this->assertEquals($customer2->id, $tenant2Customers->first()->id);
    }

    #[Test]
    public function super_admin_bypasses_tenant_scope()
    {
        $tenant1 = $this->createTenant(['name' => 'Tenant 1']);
        $tenant2 = $this->createTenant(['name' => 'Tenant 2']);

        // Create super admin (no tenant_id)
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@test.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $customer1 = $this->createCustomer($tenant1->id);
        $customer2 = $this->createCustomer($tenant2->id);

        $this->actingAs($superAdmin);

        // Super admin should see all customers
        $customers = Customer::all();
        $this->assertCount(2, $customers);
    }

    #[Test]
    public function key_models_have_tenant_scope()
    {
        // Verify critical models use BelongsToTenant
        $criticalModels = [
            Invoice::class,
            SalesOrder::class,
            PurchaseOrder::class,
            Customer::class,
            Supplier::class,
            Product::class,
            Employee::class,
            JournalEntry::class,
            ChartOfAccount::class,
            Warehouse::class,
            ProductStock::class,
        ];

        foreach ($criticalModels as $modelClass) {
            $traits = class_uses_recursive($modelClass);
            $this->assertContains(
                BelongsToTenant::class,
                $traits,
                "Critical model {$modelClass} must use BelongsToTenant trait"
            );
        }
    }

    #[Test]
    public function tenant_isolation_prevents_cross_tenant_access()
    {
        $tenant1 = $this->createTenant(['name' => 'Tenant 1']);
        $tenant2 = $this->createTenant(['name' => 'Tenant 2']);

        $user1 = $this->createAdminUser($tenant1);
        $user2 = $this->createAdminUser($tenant2);

        // Create invoice for tenant 1
        $customer1 = $this->createCustomer($tenant1->id);
        $this->actingAs($user1);

        $invoice1 = Invoice::create([
            'tenant_id' => $tenant1->id,
            'customer_id' => $customer1->id,
            'number' => 'INV-T1-001',
            'subtotal_amount' => 100000,
            'tax_amount' => 11000,
            'total_amount' => 111000,
            'paid_amount' => 0,
            'remaining_amount' => 111000,
            'status' => 'unpaid',
            'due_date' => today()->addDays(30),
        ]);

        // Switch to tenant 2 user
        $this->actingAs($user2);

        // Tenant 2 user should not see tenant 1 invoice
        $invoices = Invoice::all();
        $this->assertCount(0, $invoices);

        // Direct query by ID should also fail
        $foundInvoice = Invoice::find($invoice1->id);
        $this->assertNull($foundInvoice, 'Tenant 2 user should not be able to access Tenant 1 invoice');
    }
}
