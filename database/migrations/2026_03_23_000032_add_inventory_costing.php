<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Costing method per tenant (opt-in, default = simple)
        Schema::table('tenants', function (Blueprint $table) {
            if (! Schema::hasColumn('tenants', 'costing_method')) {
                $table->enum('costing_method', ['simple', 'avco', 'fifo'])->default('simple')->after('business_description');
            }
        });

        // 2. Track cost_price on every stock movement (in/out)
        Schema::table('stock_movements', function (Blueprint $table) {
            if (! Schema::hasColumn('stock_movements', 'cost_price')) {
                $table->decimal('cost_price', 15, 4)->default(0)->after('quantity');
            }
            if (! Schema::hasColumn('stock_movements', 'cost_total')) {
                $table->decimal('cost_total', 15, 4)->default(0)->after('cost_price');
            }
            // to_warehouse_id may not exist yet — add if missing
            if (! Schema::hasColumn('stock_movements', 'to_warehouse_id')) {
                $table->foreignId('to_warehouse_id')->nullable()->after('warehouse_id');
            }
        });

        // 3. Track cost_price on product batches
        Schema::table('product_batches', function (Blueprint $table) {
            if (! Schema::hasColumn('product_batches', 'cost_price')) {
                $table->decimal('cost_price', 15, 4)->default(0)->after('quantity');
            }
            if (! Schema::hasColumn('product_batches', 'quantity_remaining')) {
                $table->decimal('quantity_remaining', 10, 4)->nullable()->after('cost_price');
            }
        });

        // 4. Running average cost per product per warehouse (for AVCO)
        if (! Schema::hasTable('product_avg_costs')) {
            Schema::create('product_avg_costs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
                $table->decimal('avg_cost', 15, 4)->default(0);
                $table->decimal('total_qty', 10, 4)->default(0);
                $table->decimal('total_value', 15, 4)->default(0);
                $table->timestamps();
                $table->unique(['product_id', 'warehouse_id']);
            });
        }

        // 5. COGS ledger — one row per sale/out movement
        if (! Schema::hasTable('cogs_entries')) {
            Schema::create('cogs_entries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
                $table->foreignId('stock_movement_id')->constrained()->cascadeOnDelete();
                $table->string('costing_method', 10);          // fifo / avco / simple
                $table->decimal('quantity', 10, 4);
                $table->decimal('unit_cost', 15, 4);
                $table->decimal('total_cost', 15, 4);
                $table->string('reference')->nullable();        // SO number, etc.
                $table->date('date');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cogs_entries');
        Schema::dropIfExists('product_avg_costs');

        Schema::table('product_batches', function (Blueprint $table) {
            $table->dropColumn(['cost_price', 'quantity_remaining']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn(['cost_price', 'cost_total']);
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('costing_method');
        });
    }
};
