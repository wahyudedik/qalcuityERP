<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('supplier_performances')) {
            Schema::create('supplier_performances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
                $table->foreignId('purchase_order_id')->nullable()->constrained()->onDelete('set null');
                $table->date('evaluation_date');
                $table->date('period_start')->nullable();
                $table->date('period_end')->nullable();
                $table->date('expected_delivery_date')->nullable();
                $table->date('actual_delivery_date')->nullable();
                $table->decimal('lead_time_days', 8, 2)->default(0);
                $table->decimal('expected_lead_time_days', 8, 2)->nullable();
                $table->decimal('lead_time_variance_days', 8, 2)->default(0);
                $table->boolean('on_time_delivery')->default(false);
                $table->decimal('quantity_ordered', 12, 3)->default(0);
                $table->decimal('quantity_received', 12, 3)->default(0);
                $table->decimal('quantity_rejected', 12, 3)->default(0);
                $table->decimal('quality_rate', 5, 2)->default(0);
                $table->decimal('delivery_score', 5, 2)->default(0);
                $table->decimal('quality_score', 5, 2)->default(0);
                $table->decimal('cost_score', 5, 2)->default(0);
                $table->decimal('responsiveness_score', 5, 2)->default(0);
                $table->decimal('overall_score', 5, 2)->default(0);
                $table->string('rating_grade', 3)->default('NA');
                $table->decimal('total_po_value', 15, 2)->default(0);
                $table->decimal('actual_po_value', 15, 2)->default(0);
                $table->decimal('cost_variance', 15, 2)->default(0);
                $table->text('defect_notes')->nullable();
                $table->text('delivery_notes')->nullable();
                $table->foreignId('evaluated_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();

                $table->index(['tenant_id', 'supplier_id']);
                $table->index(['tenant_id', 'evaluation_date']);
                $table->index(['tenant_id', 'rating_grade']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_performances');
    }
};
