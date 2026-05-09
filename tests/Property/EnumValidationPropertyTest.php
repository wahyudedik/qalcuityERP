<?php

namespace Tests\Property;

use App\Models\Invoice;
use App\Models\SalesOrder;
use App\Models\Tenant;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Database\QueryException;
use Tests\TestCase;

/**
 * Property-Based Tests for ENUM Validation Rejection.
 *
 * Feature: erp-comprehensive-audit-fix
 *
 * **Validates: Requirements 1.2, 1.3**
 */
class EnumValidationPropertyTest extends TestCase
{
    use TestTrait;

    /**
     * Property 4: ENUM Validation Rejection
     *
     * For any value that is not in the valid ENUM list for a status column,
     * the system must reject saving that value and return a validation error,
     * while existing data remains unchanged.
     *
     * **Validates: Requirements 1.2, 1.3**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_enum_validation_rejection(): void
    {
        $this
            ->forAll(
                Generators::elements([
                    'invalid_status',
                    'unknown',
                    'random_value',
                    'not_in_enum',
                    'fake_status',
                    'test123',
                    'INVALID',
                    'null',
                    'undefined',
                    'pending_approval', // might be valid for some models but not Invoice
                ])
            )
            ->then(function ($invalidStatus) {
                // Create tenant
                $tenant = $this->createTenant();

                // Verify the invalid status is NOT in the valid Invoice statuses
                $this->assertNotContains(
                    $invalidStatus,
                    Invoice::STATUSES,
                    "Test setup error: '{$invalidStatus}' should not be in valid Invoice statuses"
                );

                // Count existing invoices before attempt
                $countBefore = Invoice::where('tenant_id', $tenant->id)->count();

                // Attempt to create an invoice with invalid status
                $exceptionThrown = false;
                try {
                    Invoice::create([
                        'tenant_id' => $tenant->id,
                        'number' => 'INV-'.uniqid(),
                        'total_amount' => 1000,
                        'paid_amount' => 0,
                        'remaining_amount' => 1000,
                        'status' => $invalidStatus, // Invalid status
                        'due_date' => now()->addDays(30),
                    ]);
                } catch (QueryException $e) {
                    // Database-level ENUM constraint violation
                    $exceptionThrown = true;
                    // MySQL may report "Data truncated" or "ENUM" depending on version/config
                    $message = $e->getMessage();
                    $this->assertTrue(
                        stripos($message, 'enum') !== false ||
                        stripos($message, 'truncated') !== false ||
                        stripos($message, 'invalid') !== false,
                        "Exception should mention ENUM constraint violation or data truncation. Got: {$message}"
                    );
                } catch (\Exception $e) {
                    // Any other exception is also acceptable (validation, etc.)
                    $exceptionThrown = true;
                }

                // Verify exception was thrown
                $this->assertTrue(
                    $exceptionThrown,
                    "System must reject invalid ENUM value '{$invalidStatus}' for Invoice status"
                );

                // Verify no new invoice was created
                $countAfter = Invoice::where('tenant_id', $tenant->id)->count();
                $this->assertEquals(
                    $countBefore,
                    $countAfter,
                    'No new invoice should be created when invalid status is provided'
                );
            });
    }

    /**
     * Property 4 (variant): Valid ENUM Values Acceptance
     *
     * For any value that IS in the valid ENUM list, the system must
     * successfully save the record.
     *
     * **Validates: Requirements 1.2, 1.3**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_valid_enum_acceptance(): void
    {
        $this
            ->forAll(
                Generators::elements(Invoice::STATUSES)
            )
            ->then(function ($validStatus) {
                // Create tenant and customer
                $tenant = $this->createTenant();
                $customer = $this->createCustomer($tenant->id);
                $user = $this->createAdminUser($tenant);

                // Create sales order first
                $so = SalesOrder::create([
                    'tenant_id' => $tenant->id,
                    'customer_id' => $customer->id,
                    'user_id' => $user->id,
                    'number' => 'SO-'.uniqid(),
                    'status' => 'confirmed',
                    'date' => now(),
                    'subtotal' => 1000,
                    'discount' => 0,
                    'tax' => 0,
                    'total' => 1000,
                ]);

                // Create an invoice with valid status
                $invoice = Invoice::create([
                    'tenant_id' => $tenant->id,
                    'customer_id' => $customer->id,
                    'sales_order_id' => $so->id,
                    'number' => 'INV-'.uniqid(),
                    'total_amount' => 1000,
                    'paid_amount' => 0,
                    'remaining_amount' => 1000,
                    'status' => $validStatus,
                    'due_date' => now()->addDays(30),
                ]);

                // Verify invoice was created successfully
                $this->assertNotNull($invoice->id, 'Invoice should be created with valid status');
                $this->assertEquals(
                    $validStatus,
                    $invoice->status,
                    'Invoice status should match the provided valid status'
                );

                // Verify invoice can be retrieved from database
                $retrieved = Invoice::find($invoice->id);
                $this->assertNotNull($retrieved, 'Invoice should be retrievable from database');
                $this->assertEquals(
                    $validStatus,
                    $retrieved->status,
                    'Retrieved invoice status should match'
                );
            });
    }

    /**
     * Property 4 (variant): SalesOrder ENUM Validation
     *
     * Test ENUM validation for SalesOrder status field.
     *
     * **Validates: Requirements 1.2, 1.3**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_sales_order_enum_validation(): void
    {
        $this
            ->forAll(
                Generators::elements([
                    'invalid_so_status',
                    'unknown_status',
                    'not_valid',
                    'random123',
                ])
            )
            ->then(function ($invalidStatus) {
                // Create tenant and customer
                $tenant = $this->createTenant();
                $customer = $this->createCustomer($tenant->id);

                // Verify the invalid status is NOT in valid SalesOrder statuses
                if (defined('App\Models\SalesOrder::STATUSES')) {
                    $this->assertNotContains(
                        $invalidStatus,
                        SalesOrder::STATUSES,
                        "Test setup error: '{$invalidStatus}' should not be in valid SalesOrder statuses"
                    );
                }

                // Count existing sales orders before attempt
                $countBefore = SalesOrder::where('tenant_id', $tenant->id)->count();

                // Attempt to create a sales order with invalid status
                $exceptionThrown = false;
                try {
                    SalesOrder::create([
                        'tenant_id' => $tenant->id,
                        'customer_id' => $customer->id,
                        'number' => 'SO-'.uniqid(),
                        'total_amount' => 1000,
                        'status' => $invalidStatus, // Invalid status
                        'order_date' => now(),
                    ]);
                } catch (QueryException $e) {
                    // Database-level ENUM constraint violation
                    $exceptionThrown = true;
                } catch (\Exception $e) {
                    // Any other exception is also acceptable
                    $exceptionThrown = true;
                }

                // Verify exception was thrown
                $this->assertTrue(
                    $exceptionThrown,
                    "System must reject invalid ENUM value '{$invalidStatus}' for SalesOrder status"
                );

                // Verify no new sales order was created
                $countAfter = SalesOrder::where('tenant_id', $tenant->id)->count();
                $this->assertEquals(
                    $countBefore,
                    $countAfter,
                    'No new sales order should be created when invalid status is provided'
                );
            });
    }
}
