<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Cosmetic Formula Management Tables
     */
    public function up(): void
    {
        // 1. Cosmetic Formulas - Master formula records
        if (! Schema::hasTable('cosmetic_formulas')) {
            Schema::create('cosmetic_formulas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('formula_code')->unique(); // CF-2026-0001
                $table->string('formula_name'); // e.g., "Moisturizing Cream v2"
                $table->string('product_type'); // cream, lotion, serum, lipstick, shampoo, etc.
                $table->string('brand')->nullable();
                $table->decimal('target_ph', 4, 2)->nullable(); // Target pH level
                $table->decimal('actual_ph', 4, 2)->nullable(); // Actual measured pH
                $table->integer('shelf_life_months')->nullable(); // e.g., 24 months
                $table->decimal('batch_size', 10, 2)->default(0); // Batch size in grams/ml
                $table->string('batch_unit')->default('grams'); // grams, ml, units
                $table->decimal('total_cost', 12, 2)->default(0); // Total ingredient cost
                $table->decimal('cost_per_unit', 10, 2)->nullable(); // Cost per unit
                $table->string('status')->default('draft'); // draft, testing, approved, production, discontinued
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->softDeletes();
                $table->timestamps();

                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'product_type']);
                $table->index(['tenant_id', 'formula_code']);
            });
        }

        // 2. Formula Ingredients - INCI ingredient tracking
        if (! Schema::hasTable('formula_ingredients')) {
            Schema::create('formula_ingredients', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('formula_id')->constrained('cosmetic_formulas')->onDelete('cascade');
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete(); // Link to inventory
                $table->string('inci_name'); // INCI name (International Nomenclature)
                $table->string('common_name')->nullable(); // Common/trade name
                $table->string('cas_number')->nullable(); // Chemical Abstracts Service number
                $table->decimal('quantity', 10, 3); // Amount in batch
                $table->string('unit'); // g, ml, %, drops
                $table->decimal('percentage', 5, 2)->nullable(); // Percentage of total formula
                $table->string('function')->nullable(); // emollient, preservative, active, fragrance, etc.
                $table->string('phase')->nullable(); // oil_phase, water_phase, cool_down_phase
                $table->integer('sort_order')->default(0); // Mixing order
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['formula_id', 'inci_name']);
                $table->index(['tenant_id', 'inci_name']);
                $table->index(['tenant_id', 'function']);
            });
        }

        // 3. Formula Versions - Track formula revisions
        if (! Schema::hasTable('formula_versions')) {
            Schema::create('formula_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('formula_id')->constrained('cosmetic_formulas')->onDelete('cascade');
                $table->string('version_number'); // v1.0, v1.1, v2.0
                $table->text('changes_summary')->nullable(); // What changed
                $table->text('reason_for_change')->nullable(); // Why it changed
                $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('approval_notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'formula_id']);
                $table->index(['tenant_id', 'version_number']);
            });
        }

        // 4. Stability Tests - Accelerated & real-time stability testing
        if (! Schema::hasTable('stability_tests')) {
            Schema::create('stability_tests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('formula_id')->constrained('cosmetic_formulas')->onDelete('cascade');
                $table->foreignId('batch_id')->nullable()->constrained('product_batches')->nullOnDelete();
                $table->string('test_code')->unique(); // ST-2026-0001
                $table->enum('test_type', ['accelerated', 'real_time', 'freeze_thaw', 'photostability']);
                $table->date('start_date');
                $table->date('expected_end_date')->nullable();
                $table->date('actual_end_date')->nullable();
                $table->string('storage_conditions'); // e.g., "40°C ± 2°C / 75% RH ± 5%"
                $table->decimal('initial_ph', 4, 2)->nullable();
                $table->decimal('final_ph', 4, 2)->nullable();
                $table->string('initial_appearance')->nullable();
                $table->string('final_appearance')->nullable();
                $table->decimal('initial_viscosity', 8, 2)->nullable();
                $table->decimal('final_viscosity', 8, 2)->nullable();
                $table->string('microbial_results')->nullable(); // Pass/Fail
                $table->string('color_change')->nullable(); // None, Slight, Moderate, Severe
                $table->string('odor_change')->nullable(); // None, Slight, Rancid, Other
                $table->string('separation')->nullable(); // None, Slight, Moderate, Severe
                $table->string('overall_result')->nullable(); // Pass, Fail, Inconclusive
                $table->text('observations')->nullable();
                $table->string('status')->default('in_progress'); // in_progress, completed, failed
                $table->foreignId('tested_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['tenant_id', 'formula_id']);
                $table->index(['tenant_id', 'test_type']);
                $table->index(['tenant_id', 'status']);
                $table->index(['start_date', 'expected_end_date']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stability_tests');
        Schema::dropIfExists('formula_versions');
        Schema::dropIfExists('formula_ingredients');
        Schema::dropIfExists('cosmetic_formulas');
    }
};
