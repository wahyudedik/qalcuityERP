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
        if (!Schema::hasTable('material_deliveries')) {
            Schema::create('material_deliveries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('project_id')->constrained()->onDelete('cascade');
                $table->string('delivery_number')->unique();
                $table->foreignId('supplier_id')->nullable()->constrained()->onDelete('set null');
                $table->string('supplier_name');
                $table->string('material_name');
                $table->string('material_category')->nullable(); // cement, steel, sand, aggregate, bricks, etc
                $table->decimal('quantity_ordered', 12, 3);
                $table->decimal('quantity_delivered', 12, 3)->default(0);
                $table->string('unit');
                $table->decimal('unit_price', 12, 2)->default(0);
                $table->decimal('total_value', 15, 2)->default(0);
                $table->date('expected_date');
                $table->timestamp('actual_delivery_date')->nullable();
                $table->enum('delivery_status', ['pending', 'in_transit', 'delivered', 'partial', 'cancelled'])->default('pending');
                $table->string('po_number')->nullable();
                $table->string('do_number')->nullable(); // delivery order number
                $table->string('vehicle_number')->nullable();
                $table->string('driver_name')->nullable();
                $table->string('driver_phone')->nullable();
                $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');
                $table->enum('quality_check_status', ['passed', 'failed', 'pending'])->default('pending');
                $table->text('quality_notes')->nullable();
                $table->json('photos')->nullable(); // JSON array of delivery photos
                $table->text('remarks')->nullable();
                $table->timestamps();
    
                $table->index(['tenant_id', 'project_id']);
                $table->index(['tenant_id', 'delivery_status']);
                $table->index(['project_id', 'expected_date']);
                $table->index(['tenant_id', 'expected_date']);
                $table->index(['supplier_id', 'delivery_status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_deliveries');
    }
};
