<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * TASK-2.19: Add QC checkpoint integration to work orders
     */
    public function up(): void
    {
        // Add QC fields to work_orders (with column existence check)
        Schema::table('work_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('work_orders', 'quality_status')) {
                $table->string('quality_status')->default('pending')->after('notes');
            }
            if (! Schema::hasColumn('work_orders', 'quality_inspection_required')) {
                $table->timestamp('quality_inspection_required')->nullable()->after('quality_status');
            }
            if (! Schema::hasColumn('work_orders', 'quality_passed_at')) {
                $table->timestamp('quality_passed_at')->nullable()->after('quality_inspection_required');
            }
            if (! Schema::hasColumn('work_orders', 'quality_failed_at')) {
                $table->timestamp('quality_failed_at')->nullable()->after('quality_passed_at');
            }
            if (! Schema::hasColumn('work_orders', 'quality_grade')) {
                $table->string('quality_grade')->nullable()->after('quality_failed_at');
            }
            if (! Schema::hasColumn('work_orders', 'quality_notes')) {
                $table->text('quality_notes')->nullable()->after('quality_grade');
            }
            if (! Schema::hasColumn('work_orders', 'quality_score')) {
                $table->decimal('quality_score', 5, 2)->nullable()->after('quality_notes');
            }
        });

        // Create QC test templates (with existence check)
        if (! Schema::hasTable('qc_test_templates')) {
            Schema::create('qc_test_templates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->string('name');
                $table->string('product_type')->nullable();
                $table->string('stage');
                $table->json('test_parameters');
                $table->integer('sample_size_formula')->default(1);
                $table->decimal('acceptance_quality_limit', 5, 2)->default(2.5);
                $table->boolean('is_active')->default(true);
                $table->text('instructions')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'product_type', 'stage']);
            });
        }

        // Create QC inspection records (with existence check)
        if (! Schema::hasTable('qc_inspections')) {
            Schema::create('qc_inspections', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->foreignId('work_order_id')->constrained('work_orders')->onDelete('cascade');
                $table->foreignId('template_id')->nullable()->constrained('qc_test_templates')->nullOnDelete();
                $table->foreignId('inspector_id')->constrained('users')->onDelete('restrict');
                $table->string('inspection_number')->unique();
                $table->string('stage');
                $table->integer('sample_size');
                $table->integer('sample_passed')->default(0);
                $table->integer('sample_failed')->default(0);
                $table->string('status')->default('pending');
                $table->json('test_results')->nullable();
                $table->decimal('pass_rate', 5, 2)->nullable();
                $table->string('grade')->nullable();
                $table->text('defects_found')->nullable();
                $table->text('corrective_action')->nullable();
                $table->text('inspector_notes')->nullable();
                $table->timestamp('inspected_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'work_order_id', 'stage', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qc_inspections');
        Schema::dropIfExists('qc_test_templates');

        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn([
                'quality_status',
                'quality_inspection_required',
                'quality_passed_at',
                'quality_failed_at',
                'quality_grade',
                'quality_notes',
                'quality_score',
            ]);
        });
    }
};
