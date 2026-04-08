<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * BUG-MFG-002 FIX: Create material_reservations table
     */
    public function up(): void
    {
        if (!Schema::hasTable('material_reservations')) {
            Schema::create('material_reservations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('work_order_id');
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('warehouse_id');
                $table->decimal('quantity_required', 12, 3);
                $table->decimal('quantity_reserved', 12, 3)->default(0);
                $table->decimal('quantity_consumed', 12, 3)->default(0);
                $table->string('status')->default('reserved'); // reserved, consumed, released, cancelled
                $table->timestamp('reserved_at')->nullable();
                $table->timestamp('consumed_at')->nullable();
                $table->timestamp('released_at')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('work_order_id')->references('id')->on('work_orders')->cascadeOnDelete();
                $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
                $table->foreign('warehouse_id')->references('id')->on('warehouses')->cascadeOnDelete();

                // Unique constraint: one reservation per WO per product per warehouse
                $table->unique(['work_order_id', 'product_id', 'warehouse_id'], 'unique_wo_product_warehouse');

                // Indexes for performance
                $table->index(['tenant_id', 'product_id', 'warehouse_id', 'status'], 'idx_product_warehouse_status');
                $table->index(['tenant_id', 'work_order_id', 'status'], 'idx_wo_status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('material_reservations');
    }
};
