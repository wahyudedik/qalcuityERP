<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Advanced Expiry Management Module.
     */
    public function up(): void
    {
        // 1. Expiry Alerts - Alert tracking for expiring products
        if (! Schema::hasTable('expiry_alerts')) {
            Schema::create('expiry_alerts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('batch_id')->constrained('cosmetic_batch_records')->onDelete('cascade');
                $table->string('alert_type'); // pao_expiry, best_before, near_expiry, expired
                $table->date('alert_date'); // When the alert was triggered
                $table->date('expiry_date'); // Actual expiry date
                $table->integer('days_until_expiry'); // Days remaining (negative if expired)
                $table->integer('alert_threshold'); // 30, 90, 180, 365 days
                $table->string('severity')->default('warning'); // info, warning, critical, expired
                $table->boolean('is_read')->default(false);
                $table->boolean('is_actioned')->default(false);
                $table->string('action_taken')->nullable(); // discounted, disposed, recalled
                $table->timestamp('actioned_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'severity']);
                $table->index(['tenant_id', 'is_read']);
                $table->index(['tenant_id', 'alert_date']);
            });
        }

        // 2. Batch Recalls - Product recall management
        if (! Schema::hasTable('batch_recalls')) {
            Schema::create('batch_recalls', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('batch_id')->constrained('cosmetic_batch_records')->onDelete('cascade');
                $table->string('recall_number')->unique(); // RCL-2026-0001
                $table->string('recall_reason'); // contamination, labeling_error, adverse_reaction, etc.
                $table->text('description');
                $table->text('affected_regions')->nullable(); // Geographic scope
                $table->string('severity'); // minor, major, critical
                $table->string('status')->default('initiated'); // initiated, in_progress, completed, cancelled
                $table->date('recall_date');
                $table->date('completion_date')->nullable();
                $table->integer('total_units')->default(0);
                $table->integer('units_returned')->default(0);
                $table->integer('units_destroyed')->default(0);
                $table->string('initiated_by')->nullable();
                $table->text('resolution_notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'severity']);
            });
        }

        // 3. Expiry Reports - Compliance reporting
        if (! Schema::hasTable('expiry_reports')) {
            Schema::create('expiry_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('report_number')->unique(); // EXP-2026-0001
                $table->string('report_type'); // monthly, quarterly, annual, ad_hoc
                $table->date('start_date');
                $table->date('end_date');
                $table->json('summary_data'); // Aggregated expiry statistics
                $table->integer('total_batches_monitored')->default(0);
                $table->integer('batches_expired')->default(0);
                $table->integer('batches_recalled')->default(0);
                $table->decimal('total_loss_value', 12, 2)->default(0);
                $table->string('generated_by')->nullable();
                $table->string('file_path')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'report_type']);
                $table->index(['tenant_id', 'start_date']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expiry_reports');
        Schema::dropIfExists('batch_recalls');
        Schema::dropIfExists('expiry_alerts');
    }
};
