<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PERF-003: Add Composite Database Indexes
 *
 * Composite indexes significantly improve query performance for common filter patterns.
 *
 * Indexes added:
 * - sales_orders(tenant_id, status, created_at) - Order filtering
 * - invoices(tenant_id, due_date, status) - Invoice aging
 * - products(tenant_id, sku) - UNIQUE - Product lookup
 * - customers(tenant_id, email) - UNIQUE - Customer dedup
 * - employees(tenant_id, employee_code) - UNIQUE - Employee lookup
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Sales orders: Filter by tenant, status, and date range
        if (Schema::hasTable('sales_orders')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                // Check if index already exists
                $indexes = DB::select("SHOW INDEX FROM sales_orders WHERE Key_name = 'idx_sales_orders_tenant_status_date'");
                if (empty($indexes)) {
                    $table->index(['tenant_id', 'status', 'created_at'], 'idx_sales_orders_tenant_status_date');
                }
            });
        }

        // Invoices: Filter by tenant, due date, and status (for aging reports)
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                $indexes = DB::select("SHOW INDEX FROM invoices WHERE Key_name = 'idx_invoices_tenant_due_status'");
                if (empty($indexes)) {
                    $table->index(['tenant_id', 'due_date', 'status'], 'idx_invoices_tenant_due_status');
                }
            });
        }

        // Products: Unique SKU per tenant
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (Schema::hasColumn('products', 'sku')) {
                    $indexes = DB::select("SHOW INDEX FROM products WHERE Key_name = 'uq_products_tenant_sku'");
                    if (empty($indexes)) {
                        $table->unique(['tenant_id', 'sku'], 'uq_products_tenant_sku');
                    }
                }
            });
        }

        // Customers: Unique email per tenant
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                if (Schema::hasColumn('customers', 'email')) {
                    $indexes = DB::select("SHOW INDEX FROM customers WHERE Key_name = 'uq_customers_tenant_email'");
                    if (empty($indexes)) {
                        $table->unique(['tenant_id', 'email'], 'uq_customers_tenant_email');
                    }
                }
            });
        }

        // Employees: Skip if employee_code doesn't exist
        // (Column name may vary: code, emp_code, employee_number, etc.)
        // Manual migration needed if column exists with different name
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropIndex('idx_sales_orders_tenant_status_date');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('idx_invoices_tenant_due_status');
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'sku')) {
                $table->dropUnique('uq_products_tenant_sku');
            }
        });

        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'email')) {
                $table->dropUnique('uq_customers_tenant_email');
            }
        });

        // Employees: No index added, nothing to drop
    }
};
