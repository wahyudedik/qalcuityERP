<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            // Payment fields for POS
            $table->string('payment_type')->nullable()->after('status'); // cash, qris, card, transfer
            $table->string('payment_method')->nullable()->after('payment_type'); // cash, gopay, ovo, dana, etc.
            $table->decimal('paid_amount', 15, 2)->default(0)->after('total');
            $table->decimal('change_amount', 15, 2)->default(0)->after('paid_amount');
            $table->string('payment_reference')->nullable()->after('change_amount'); // Transaction number from gateway
            $table->timestamp('completed_at')->nullable()->after('payment_reference');
            $table->timestamp('stock_deducted_at')->nullable()->after('completed_at');

            // Source tracking
            $table->string('source')->default('manual')->after('user_id'); // pos, online, manual

            // Indexes for performance
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'payment_type']);
            $table->index(['tenant_id', 'created_at']);
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
