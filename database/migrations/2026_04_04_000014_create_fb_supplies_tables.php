<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // F&B Supplies/Ingredients
        if (!Schema::hasTable('fb_supplies'))
            Schema::create('fb_supplies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('name'); // e.g., "Chicken Breast", "Rice", "Tomato Sauce"
                $table->string('unit')->default('pcs'); // pcs, kg, liter, box, etc.
                $table->decimal('current_stock', 10, 2)->default(0);
                $table->decimal('minimum_stock', 10, 2)->default(0); // Reorder point
                $table->decimal('cost_per_unit', 15, 2)->default(0);
                $table->unsignedBigInteger('category_id')->nullable(); // product category reference
                $table->string('supplier_name')->nullable();
                $table->date('last_restocked_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'is_active']);
            });

        // F&B Supply Transactions (Stock In/Out)
        if (!Schema::hasTable('fb_supply_transactions'))
            Schema::create('fb_supply_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('supply_id')->constrained('fb_supplies')->cascadeOnDelete();
                $table->enum('transaction_type', ['purchase', 'usage', 'adjustment', 'waste']);
                $table->decimal('quantity', 10, 2);
                $table->decimal('unit_cost', 15, 2)->nullable();
                $table->decimal('total_cost', 15, 2)->nullable();
                $table->text('reference')->nullable(); // PO number, order number, etc.
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->timestamp('transaction_date')->useCurrent();
                $table->timestamps();

                $table->index(['tenant_id', 'supply_id', 'transaction_date'], 'fb_supply_tx_tenant_supply_date_idx');
            });

        // Recipe Ingredients for F&B (Link menu items to supplies)
        if (!Schema::hasTable('fb_recipe_ingredients'))
            Schema::create('fb_recipe_ingredients', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('menu_item_id')->constrained('menu_items')->cascadeOnDelete();
                $table->foreignId('supply_id')->constrained('fb_supplies')->cascadeOnDelete();
                $table->decimal('quantity_required', 10, 2); // Amount needed per serving
                $table->string('unit')->default('pcs');
                $table->timestamps();

                $table->unique(['menu_item_id', 'supply_id']);
                $table->index(['tenant_id', 'menu_item_id']);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('fb_recipe_ingredients');
        Schema::dropIfExists('fb_supply_transactions');
        Schema::dropIfExists('fb_supplies');
    }
};
