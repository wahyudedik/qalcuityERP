<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sales_orders')) {
            return;
        }

        // Update payment_type to include card and split
        DB::statement("ALTER TABLE sales_orders MODIFY COLUMN payment_type ENUM('cash','credit','transfer','qris','card','bank_transfer','split') NULL DEFAULT NULL");

        Schema::table('sales_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('sales_orders', 'split_payments')) {
                $table->json('split_payments')->nullable()->after('payment_reference')
                    ->comment('Array of {method, amount} for split payment');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            if (Schema::hasColumn('sales_orders', 'split_payments')) {
                $table->dropColumn('split_payments');
            }
        });

        DB::statement("ALTER TABLE sales_orders MODIFY COLUMN payment_type ENUM('cash','credit','transfer','qris') NULL DEFAULT NULL");
    }
};
