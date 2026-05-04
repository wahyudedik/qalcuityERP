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
        if (!Schema::hasTable('mrp_accuracies')) {
            Schema::create('mrp_accuracies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('work_order_id')->nullable()->constrained()->onDelete('set null');
                $table->foreignId('product_id')->constrained()->onDelete('cascade');
                $table->decimal('planned_quantity', 12, 3);
                $table->decimal('actual_quantity', 12, 3);
                $table->decimal('variance_quantity', 12, 3)->default(0);
                $table->decimal('variance_percent', 5, 2)->default(0);
                $table->decimal('planned_cost', 15, 2)->default(0);
                $table->decimal('actual_cost', 15, 2)->default(0);
                $table->decimal('cost_variance', 15, 2)->default(0);
                $table->date('tracking_date');
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->index(['tenant_id', 'tracking_date']);
                $table->index(['tenant_id', 'work_order_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mrp_accuracies');
    }
};
