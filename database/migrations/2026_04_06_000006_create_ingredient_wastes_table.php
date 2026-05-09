<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ingredient_wastes')) {
            Schema::dropIfExists('ingredient_wastes');
        }

        if (! Schema::hasTable('ingredient_wastes')) {
            Schema::create('ingredient_wastes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->unsignedBigInteger('inventory_item_id')->nullable();
                // Don't add foreign key - inventory_items table might not exist
                $table->string('item_name');
                $table->decimal('quantity_wasted', 10, 3);
                $table->string('unit', 50);
                $table->decimal('cost_per_unit', 10, 2);
                $table->decimal('total_waste_cost', 10, 2);
                $table->string('waste_type'); // spoilage, over_production, preparation_error, expired, other
                $table->text('reason')->nullable();
                $table->foreignId('wasted_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('wasted_at');
                $table->string('department')->default('kitchen'); // kitchen, bar, storage
                $table->text('preventive_action')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'wasted_at']);
                $table->index(['tenant_id', 'waste_type']);
                $table->index(['tenant_id', 'department']);
                $table->index('item_name');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredient_wastes');
    }
};
