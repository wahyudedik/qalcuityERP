<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations - Batch Production Records (BMR)
     */
    public function up(): void
    {
        // 1. Cosmetic Batch Records - Manufacturing execution records
        if (!Schema::hasTable('cosmetic_batch_records')) {
            Schema::create('cosmetic_batch_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('formula_id')->constrained('cosmetic_formulas')->onDelete('cascade');
                $table->string('batch_number')->unique(); // BMR-2026-0001
                $table->date('production_date');
                $table->date('expiry_date')->nullable();
                $table->decimal('planned_quantity', 10, 2); // Target production
                $table->decimal('actual_quantity', 10, 2)->nullable(); // Actual produced
                $table->decimal('yield_percentage', 5, 2)->nullable(); // Actual/Planned * 100
                $table->string('status')->default('draft'); // draft, in_progress, qc_pending, released, rejected, on_hold
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('produced_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('qc_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('qc_completed_at')->nullable();
                $table->text('production_notes')->nullable();
                $table->text('qc_notes')->nullable();
                $table->softDeletes();
                $table->timestamps();

                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'formula_id']);
                $table->index(['tenant_id', 'batch_number']);
                $table->index(['production_date', 'expiry_date']);
            });
        }

        // 2. Batch Quality Checks - In-process QC checks
        if (!Schema::hasTable('batch_quality_checks')) {
            Schema::create('batch_quality_checks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('batch_id')->constrained('cosmetic_batch_records')->onDelete('cascade');
                $table->string('check_point'); // mixing, filling, packaging, final
                $table->string('parameter'); // pH, viscosity, appearance, weight, seal
                $table->decimal('target_value', 10, 2)->nullable();
                $table->decimal('actual_value', 10, 2)->nullable();
                $table->decimal('lower_limit', 10, 2)->nullable();
                $table->decimal('upper_limit', 10, 2)->nullable();
                $table->string('result')->default('pending'); // pending, pass, fail
                $table->text('observations')->nullable();
                $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('checked_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'batch_id']);
                $table->index(['tenant_id', 'result']);
                $table->index(['tenant_id', 'check_point']);
            });
        }

        // 3. Batch Rework Logs - Rework tracking
        if (!Schema::hasTable('batch_rework_logs')) {
            Schema::create('batch_rework_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('batch_id')->constrained('cosmetic_batch_records')->onDelete('cascade');
                $table->string('rework_code')->unique(); // RW-2026-0001
                $table->text('reason'); // Why rework is needed
                $table->text('rework_action'); // What was done
                $table->decimal('quantity_before', 10, 2);
                $table->decimal('quantity_after', 10, 2)->nullable();
                $table->decimal('loss_quantity', 10, 2)->nullable();
                $table->string('status')->default('in_progress'); // in_progress, completed, failed
                $table->foreignId('initiated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('completed_at')->nullable();
                $table->text('final_notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'batch_id']);
                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'rework_code']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_rework_logs');
        Schema::dropIfExists('batch_quality_checks');
        Schema::dropIfExists('cosmetic_batch_records');
    }
};
