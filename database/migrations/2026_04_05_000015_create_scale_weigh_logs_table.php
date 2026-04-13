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
        Schema::create('scale_weigh_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('scale_id')->constrained('smart_scales')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('weight', 12, 4);
            $table->string('unit')->default('g');
            $table->decimal('tare_weight', 12, 4)->default(0);
            $table->decimal('net_weight', 12, 4);
            $table->string('reference_type')->nullable()->comment('goods_receipt, stock_opname, production');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('weighed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('weigh_time');
            $table->text('raw_data')->nullable();
            $table->string('status')->default('pending')->comment('pending, processed, error');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('weigh_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scale_weigh_logs');
    }
};
