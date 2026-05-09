<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add indexes to improve search performance across modules
     */
    public function up(): void
    {
        $this->addIndexIfNotExists('products', ['tenant_id', 'name'], 'products_tenant_id_name_index');
        $this->addIndexIfNotExists('products', ['tenant_id', 'sku'], 'products_tenant_id_sku_index');
        $this->addIndexIfNotExists('products', ['tenant_id', 'barcode'], 'products_tenant_id_barcode_index');
        $this->addIndexIfNotExists('products', ['tenant_id', 'is_active'], 'products_tenant_id_is_active_index');

        $this->addIndexIfNotExists('invoices', ['tenant_id', 'number'], 'invoices_tenant_id_number_index');
        $this->addIndexIfNotExists('invoices', ['tenant_id', 'status'], 'invoices_tenant_id_status_index');
        $this->addIndexIfNotExists('invoices', ['customer_id'], 'invoices_customer_id_index');

        $this->addIndexIfNotExists('customers', ['tenant_id', 'name'], 'customers_tenant_id_name_index');
        $this->addIndexIfNotExists('customers', ['tenant_id', 'email'], 'customers_tenant_id_email_index');
        $this->addIndexIfNotExists('customers', ['tenant_id', 'phone'], 'customers_tenant_id_phone_index');

        $this->addIndexIfNotExists('sales_orders', ['tenant_id', 'number'], 'sales_orders_tenant_id_number_index');
        $this->addIndexIfNotExists('sales_orders', ['tenant_id', 'status'], 'sales_orders_tenant_id_status_index');
        $this->addIndexIfNotExists('sales_orders', ['customer_id'], 'sales_orders_customer_id_index');

        $this->addIndexIfNotExists('journal_entries', ['tenant_id', 'reference'], 'journal_entries_tenant_id_reference_index');
        $this->addIndexIfNotExists('journal_entries', ['tenant_id', 'date'], 'journal_entries_tenant_id_date_index');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'name']);
            $table->dropIndex(['tenant_id', 'sku']);
            $table->dropIndex(['tenant_id', 'barcode']);
            $table->dropIndex(['tenant_id', 'is_active']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'number']);
            $table->dropIndex(['tenant_id', 'status']);
            $table->dropIndex(['customer_id']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'name']);
            $table->dropIndex(['tenant_id', 'email']);
            $table->dropIndex(['tenant_id', 'phone']);
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'number']);
            $table->dropIndex(['tenant_id', 'status']);
            $table->dropIndex(['customer_id']);
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'reference']);
            $table->dropIndex(['tenant_id', 'date']);
        });
    }

    /**
     * Add index if it doesn't exist
     */
    private function addIndexIfNotExists(string $table, array $columns, string $indexName): void
    {
        $exists = DB::select('
            SELECT COUNT(*) as count 
            FROM information_schema.statistics 
            WHERE table_schema = DATABASE() 
            AND table_name = ? 
            AND index_name = ?
        ', [$table, $indexName]);

        if ($exists[0]->count == 0) {
            Schema::table($table, function (Blueprint $table) use ($columns) {
                $table->index($columns);
            });
        }
    }
};
