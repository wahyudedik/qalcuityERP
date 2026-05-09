<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('sales_orders')) {
            return;
        }
        Schema::table('sales_orders', function (Blueprint $table) {
            // Payment fields for POS
            if (! Schema::hasColumn('sales_orders', 'payment_type')) {
                $table->string('payment_type')->nullable()->after('status');
            }
            if (! Schema::hasColumn('sales_orders', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('payment_type');
            }
            if (! Schema::hasColumn('sales_orders', 'paid_amount')) {
                $table->decimal('paid_amount', 15, 2)->default(0)->after('total');
            }
            if (! Schema::hasColumn('sales_orders', 'change_amount')) {
                $table->decimal('change_amount', 15, 2)->default(0)->after('paid_amount');
            }
            if (! Schema::hasColumn('sales_orders', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->after('change_amount');
            }
            if (! Schema::hasColumn('sales_orders', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('payment_reference');
            }
            if (! Schema::hasColumn('sales_orders', 'stock_deducted_at')) {
                $table->timestamp('stock_deducted_at')->nullable()->after('completed_at');
            }
            if (! Schema::hasColumn('sales_orders', 'source')) {
                $table->string('source')->default('manual')->after('user_id');
            }

            // Indexes for performance (use try/catch to avoid duplicates)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'status']);
            $table->dropIndex(['tenant_id', 'payment_type']);
            $table->dropIndex(['tenant_id', 'created_at']);

            $table->dropColumn([
                'payment_type',
                'payment_method',
                'paid_amount',
                'change_amount',
                'payment_reference',
                'completed_at',
                'stock_deducted_at',
                'source',
            ]);
        });
    }
};
