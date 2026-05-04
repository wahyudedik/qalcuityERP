<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Warehouse zones (Zona Penyimpanan: Dry, Cold, Hazmat, Staging, etc)
        if (!Schema::hasTable('warehouse_zones')) {
            Schema::create('warehouse_zones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('code', 10);
                $table->string('name');
                $table->string('type', 20)->default('general'); // general, cold, hazmat, staging, returns
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['warehouse_id', 'code']);
            });
        }

        // Bin locations (Rak/Lokasi: Z01-A01-R01-B01)
        if (!Schema::hasTable('warehouse_bins')) {
            Schema::create('warehouse_bins', function (Blueprint $table) {
                $table->id();
                $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
                $table->foreignId('zone_id')->nullable()->constrained('warehouse_zones')->nullOnDelete();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('code', 30);           // Z01-A01-R01-B01
                $table->string('aisle', 10)->nullable();
                $table->string('rack', 10)->nullable();
                $table->string('shelf', 10)->nullable();
                $table->unsignedInteger('max_capacity')->default(0); // 0 = unlimited
                $table->string('bin_type', 20)->default('storage'); // storage, picking, staging, returns
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['warehouse_id', 'code']);
            });
        }

        // Bin-level stock (stok per lokasi rak)
        if (!Schema::hasTable('bin_stocks')) {
            Schema::create('bin_stocks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('bin_id')->constrained('warehouse_bins')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->decimal('quantity', 12, 3)->default(0);
                $table->timestamps();
                $table->unique(['bin_id', 'product_id']);
            });
        }

        // Putaway rules (aturan penempatan barang masuk)
        if (!Schema::hasTable('putaway_rules')) {
            Schema::create('putaway_rules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
                $table->string('product_category')->nullable(); // match product.category
                $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('zone_id')->nullable()->constrained('warehouse_zones')->nullOnDelete();
                $table->foreignId('bin_id')->nullable()->constrained('warehouse_bins')->nullOnDelete();
                $table->unsignedSmallInteger('priority')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Picking lists (daftar ambil barang untuk SO/DO)
        if (!Schema::hasTable('picking_lists')) {
            Schema::create('picking_lists', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
                $table->string('number', 30);
                $table->string('reference_type', 30)->nullable(); // sales_order, delivery_order
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->unique(['tenant_id', 'number']);
            });
        }

        // Picking list items
        if (!Schema::hasTable('picking_list_items')) {
            Schema::create('picking_list_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('picking_list_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('bin_id')->nullable()->constrained('warehouse_bins')->nullOnDelete();
                $table->decimal('quantity_requested', 12, 3);
                $table->decimal('quantity_picked', 12, 3)->default(0);
                $table->enum('status', ['pending', 'picked', 'short'])->default('pending');
                $table->timestamps();
            });
        }

        // Stock opname per bin
        if (!Schema::hasTable('stock_opname_sessions')) {
            Schema::create('stock_opname_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
                $table->string('number', 30);
                $table->date('opname_date');
                $table->enum('status', ['draft', 'in_progress', 'completed'])->default('draft');
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('stock_opname_items')) {
            Schema::create('stock_opname_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('session_id')->constrained('stock_opname_sessions')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('bin_id')->nullable()->constrained('warehouse_bins')->nullOnDelete();
                $table->decimal('system_qty', 12, 3);
                $table->decimal('actual_qty', 12, 3)->nullable();
                $table->decimal('difference', 12, 3)->nullable();
                $table->string('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opname_items');
        Schema::dropIfExists('stock_opname_sessions');
        Schema::dropIfExists('picking_list_items');
        Schema::dropIfExists('picking_lists');
        Schema::dropIfExists('putaway_rules');
        Schema::dropIfExists('bin_stocks');
        Schema::dropIfExists('warehouse_bins');
        Schema::dropIfExists('warehouse_zones');
    }
};
