<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * BUG-SALES-002: Add indexes for efficient stock deduction queries
     * This optimizes the pessimistic locking query in POS checkout:
     * - WHERE product_id = ? AND quantity > 0 ORDER BY quantity DESC
     */
    public function up(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            // Composite index for stock deduction query
            // Covers: WHERE product_id = ? AND quantity > 0 ORDER BY quantity DESC
            if (! $this->hasIndex('product_stocks', 'idx_product_stocks_product_quantity')) {
                $table->index(['product_id', 'quantity'], 'idx_product_stocks_product_quantity');
            }

            // Index for stock movements lookup
            if (! $this->hasIndex('product_stocks', 'idx_product_stocks_warehouse')) {
                $table->index('warehouse_id', 'idx_product_stocks_warehouse');
            }
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            // Composite index for stock movement queries
            if (! $this->hasIndex('stock_movements', 'idx_stock_movements_product_type')) {
                $table->index(['product_id', 'type', 'created_at'], 'idx_stock_movements_product_type');
            }

            // Index for reference lookup
            if (! $this->hasIndex('stock_movements', 'idx_stock_movements_reference')) {
                $table->index('reference', 'idx_stock_movements_reference');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropIndex('idx_product_stocks_product_quantity');
            $table->dropIndex('idx_product_stocks_warehouse');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex('idx_stock_movements_product_type');
            $table->dropIndex('idx_stock_movements_reference');
        });
    }

    /**
     * Check if index exists
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = '{$indexName}'");

        return ! empty($indexes);
    }
};
