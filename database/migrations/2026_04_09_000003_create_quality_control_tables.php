<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Quality Check Standards/Templates
        if (! Schema::hasTable('quality_check_standards')) {
            Schema::create('quality_check_standards', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->string('name');
                $table->string('code')->unique();
                $table->text('description')->nullable();
                $table->string('stage'); // incoming, in_process, final
                $table->json('parameters'); // [{name, min_value, max_value, unit, critical}]
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('tenant_id');
                $table->index('stage');
            });
        }

        // Quality Checks/Inspections
        if (! Schema::hasTable('quality_checks')) {
            Schema::create('quality_checks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->foreignId('work_order_id')->nullable()->constrained('work_orders')->onDelete('set null');
                $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
                $table->foreignId('standard_id')->nullable()->constrained('quality_check_standards')->onDelete('set null');
                $table->foreignId('inspector_id')->constrained('users')->onDelete('restrict');

                $table->string('check_number')->unique();
                $table->string('stage'); // incoming, in_process, final
                $table->decimal('sample_size', 10, 2);
                $table->decimal('sample_passed', 10, 2)->nullable();
                $table->decimal('sample_failed', 10, 2)->nullable();

                $table->enum('status', ['pending', 'in_progress', 'passed', 'failed', 'conditional_pass'])->default('pending');
                $table->text('notes')->nullable();
                $table->json('results')->nullable(); // [{parameter, value, passed}]
                $table->text('corrective_action')->nullable();

                $table->timestamp('inspected_at')->nullable();
                $table->timestamps();

                $table->index('tenant_id');
                $table->index('work_order_id');
                $table->index('status');
                $table->index('stage');
                $table->index('inspected_at');
            });
        }

        // Defect Records
        if (! Schema::hasTable('defect_records')) {
            Schema::create('defect_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->foreignId('quality_check_id')->constrained('quality_checks')->onDelete('cascade');
                $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
                $table->foreignId('work_order_id')->nullable()->constrained('work_orders')->onDelete('set null');

                $table->string('defect_code');
                $table->string('defect_type'); // cosmetic, functional, dimensional, material, other
                $table->string('severity'); // minor, major, critical
                $table->integer('quantity_defected');
                $table->text('description');
                $table->string('root_cause')->nullable();
                $table->text('corrective_action')->nullable();
                $table->text('preventive_action')->nullable();
                $table->string('disposition'); // scrap, rework, return_to_vendor, use_as_is
                $table->decimal('cost_impact', 15, 2)->default(0);

                $table->foreignId('reported_by')->constrained('users')->onDelete('restrict');
                $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();

                $table->index('tenant_id');
                $table->index('quality_check_id');
                $table->index('severity');
                $table->index('defect_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        if (Schema::hasTable('defect_records')) {
            Schema::dropIfExists('defect_records');
        }
        if (Schema::hasTable('quality_checks')) {
            Schema::dropIfExists('quality_checks');
        }
        if (Schema::hasTable('quality_check_standards')) {
            Schema::dropIfExists('quality_check_standards');
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
