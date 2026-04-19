<?php

namespace Tests\Property;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\Tenant;
use App\Models\User;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Property-Based Tests for Tenant Data Isolation.
 *
 * Feature: erp-comprehensive-audit-fix
 * 
 * **Validates: Requirements 23.3**
 */
class TenantIsolationPropertyTest extends TestCase
{
    use TestTrait;

    /**
     * Property 1: Tenant Data Isolation
     *
     * For any query that is performed by a user from tenant A, all records
     * returned must have tenant_id equal to tenant A, and there must be no
     * records from any other tenant.
     *
     * **Validates: Requirements 23.3**
     */
    #[ErisRepeat(repeat: 100)]
    public function testTenantIsolationProperty(): void
    {
        $this
            ->forAll(
                Generators::choose(1, 10) // number of records to create per tenant
            )
            ->then(function($recordCount) {
                // Create two tenants normally (let database assign IDs)
                $tenantA = $this->createTenant();
                $tenantB = $this->createTenant();

                // Create users for each tenant
                $userA = $this->createAdminUser($tenantA);
                $userB = $this->createAdminUser($tenantB);

                // Create customers for each tenant
                $customerA = $this->createCustomer($tenantA->id);
                $customerB = $this->createCustomer($tenantB->id);

                // Create multiple records for tenant A
                for ($i = 0; $i < $recordCount; $i++) {
                    // Create sales order first
                    $soA = SalesOrder::create([
                        'tenant_id' => $tenantA->id,
                        'customer_id' => $customerA->id,
                        'user_id' => $userA->id,
                        'number' => 'SO-A-' . $i . '-' . uniqid(),
                        'status' => 'confirmed',
                        'date' => now(),
                        'subtotal' => 1000 * ($i + 1),
                        'discount' => 0,
                        'tax' => 0,
                        'total' => 1000 * ($i + 1),
                    ]);

                    Invoice::create([
                        'tenant_id' => $tenantA->id,
                        'customer_id' => $customerA->id,
                        'sales_order_id' => $soA->id,
                        'number' => 'INV-A-' . $i . '-' . uniqid(),
                        'total_amount' => 1000 * ($i + 1),
                        'paid_amount' => 0,
                        'remaining_amount' => 1000 * ($i + 1),
                        'status' => 'unpaid',
                        'due_date' => now()->addDays(30),
                    ]);

                    Product::create([
                        'tenant_id' => $tenantA->id,
                        'name' => 'Product A ' . $i,
                        'sku' => 'SKU-A-' . $i . '-' . uniqid(),
                        'unit' => 'pcs',
                        'price_sell' => 100 * ($i + 1),
                        'is_active' => true,
                    ]);
                }

                // Create multiple records for tenant B
                for ($i = 0; $i < $recordCount; $i++) {
                    // Create sales order first
                    $soB = SalesOrder::create([
                        'tenant_id' => $tenantB->id,
                        'customer_id' => $customerB->id,
                        'user_id' => $userB->id,
                        'number' => 'SO-B-' . $i . '-' . uniqid(),
                        'status' => 'confirmed',
                        'date' => now(),
                        'subtotal' => 2000 * ($i + 1),
                        'discount' => 0,
                        'tax' => 0,
                        'total' => 2000 * ($i + 1),
                    ]);

                    Invoice::create([
                        'tenant_id' => $tenantB->id,
                        'customer_id' => $customerB->id,
                        'sales_order_id' => $soB->id,
                        'number' => 'INV-B-' . $i . '-' . uniqid(),
                        'total_amount' => 2000 * ($i + 1),
                        'paid_amount' => 0,
                        'remaining_amount' => 2000 * ($i + 1),
                        'status' => 'unpaid',
                        'due_date' => now()->addDays(30),
                    ]);

                    Product::create([
                        'tenant_id' => $tenantB->id,
                        'name' => 'Product B ' . $i,
                        'sku' => 'SKU-B-' . $i . '-' . uniqid(),
                        'unit' => 'pcs',
                        'price_sell' => 200 * ($i + 1),
                        'is_active' => true,
                    ]);
                }

                // Authenticate as user from tenant A
                Auth::login($userA);

                // Query invoices - should only return tenant A's data
                $invoices = Invoice::all();
                $this->assertGreaterThan(0, $invoices->count(), 
                    "Should have invoices for tenant A");
                
                $this->assertTrue(
                    $invoices->every(fn($invoice) => $invoice->tenant_id === $tenantA->id),
                    "All invoices queried by tenant A user must belong to tenant A. " .
                    "Found invoices from other tenants: " . 
                    $invoices->pluck('tenant_id')->unique()->implode(', ')
                );

                // Query products - should only return tenant A's data
                $products = Product::all();
                $this->assertGreaterThan(0, $products->count(), 
                    "Should have products for tenant A");
                
                $this->assertTrue(
                    $products->every(fn($product) => $product->tenant_id === $tenantA->id),
                    "All products queried by tenant A user must belong to tenant A. " .
                    "Found products from other tenants: " . 
                    $products->pluck('tenant_id')->unique()->implode(', ')
                );

                // Verify no data from tenant B is accessible
                $this->assertEquals(0, $invoices->where('tenant_id', $tenantB->id)->count(),
                    "No invoices from tenant B should be accessible");
                $this->assertEquals(0, $products->where('tenant_id', $tenantB->id)->count(),
                    "No products from tenant B should be accessible");

                Auth::logout();
            });
    }
}
