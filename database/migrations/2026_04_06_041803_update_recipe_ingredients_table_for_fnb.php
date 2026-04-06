<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Update recipe_ingredients table for F&B
        Schema::table('recipe_ingredients', function (Blueprint $table) {
            if (!Schema::hasColumn('recipe_ingredients', 'recipe_id')) {
                $table->foreignId('recipe_id')->nullable()->constrained('recipes')->onDelete('cascade');
            }
            if (!Schema::hasColumn('recipe_ingredients', 'inventory_item_id')) {
                $table->unsignedBigInteger('inventory_item_id')->nullable()->after('recipe_id');
                // Don't add foreign key constraint - inventory_items might not exist
            }
            if (!Schema::hasColumn('recipe_ingredients', 'ingredient_name')) {
                $table->string('ingredient_name')->after('recipe_id');
            }
            if (!Schema::hasColumn('recipe_ingredients', 'quantity')) {
                $table->decimal('quantity', 10, 3)->default(0)->after('ingredient_name');
            }
            if (!Schema::hasColumn('recipe_ingredients', 'cost_per_unit')) {
                $table->decimal('cost_per_unit', 10, 2)->default(0)->after('unit');
            }
            if (!Schema::hasColumn('recipe_ingredients', 'notes')) {
                $table->text('notes')->nullable()->after('cost_per_unit');
            }

            // Remove old columns if exist
            if (Schema::hasColumn('recipe_ingredients', 'menu_item_id')) {
                $table->dropForeign(['menu_item_id']);
                $table->dropColumn('menu_item_id');
            }
            if (Schema::hasColumn('recipe_ingredients', 'supply_id')) {
                $table->dropForeign(['supply_id']);
                $table->dropColumn('supply_id');
            }
            if (Schema::hasColumn('recipe_ingredients', 'quantity_required')) {
                $table->dropColumn('quantity_required');
            }
        });
    }

    public function down(): void
    {
        Schema::table('recipe_ingredients', function (Blueprint $table) {
            if (Schema::hasColumn('recipe_ingredients', 'recipe_id')) {
                $table->dropForeign(['recipe_id']);
                $table->dropColumn('recipe_id');
            }
            if (Schema::hasColumn('recipe_ingredients', 'inventory_item_id')) {
                $table->dropForeign(['inventory_item_id']);
                $table->dropColumn('inventory_item_id');
            }
            if (Schema::hasColumn('recipe_ingredients', 'ingredient_name')) {
                $table->dropColumn('ingredient_name');
            }
            if (Schema::hasColumn('recipe_ingredients', 'quantity')) {
                $table->dropColumn('quantity');
            }
            if (Schema::hasColumn('recipe_ingredients', 'cost_per_unit')) {
                $table->dropColumn('cost_per_unit');
            }
            if (Schema::hasColumn('recipe_ingredients', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
