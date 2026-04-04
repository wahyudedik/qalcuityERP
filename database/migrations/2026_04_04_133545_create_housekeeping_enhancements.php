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
        // Create linen_inventories table
        Schema::create('linen_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('item_name');
            $table->string('item_code')->unique();
            $table->string('category');
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->string('material')->nullable();
            $table->integer('par_level')->default(3);
            $table->integer('total_quantity')->default(0);
            $table->integer('available_quantity')->default(0);
            $table->integer('in_use_quantity')->default(0);
            $table->integer('soiled_quantity')->default(0);
            $table->integer('damaged_quantity')->default(0);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->date('last_purchase_date')->nullable();
            $table->text('supplier_info')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'category']);
        });

        // Create linen_movements table
        Schema::create('linen_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('linen_inventory_id')->constrained('linen_inventories')->cascadeOnDelete();
            $table->enum('movement_type', ['add', 'remove', 'transfer', 'damage', 'laundry_out', 'laundry_in']);
            $table->integer('quantity');
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->foreignId('from_location')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('to_location')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'movement_type']);
            $table->index(['linen_inventory_id', 'created_at']);
        });

        // Create room_inspection_checklists table
        Schema::create('room_inspection_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('inspected_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('inspected_at');
            $table->json('checklist_items');
            $table->integer('overall_score')->nullable();
            $table->text('general_notes')->nullable();
            $table->json('photos')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'inspected_at']);
            $table->index(['room_id', 'inspected_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_inspection_checklists');
        Schema::dropIfExists('linen_movements');
        Schema::dropIfExists('linen_inventories');
    }
};
