<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations - Packaging & Labeling Module.
     */
    public function up(): void
    {
        // 1. Packaging Materials - Primary & secondary packaging specs
        Schema::create('packaging_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('formula_id')->nullable()->constrained('cosmetic_formulas')->nullOnDelete();
            $table->string('material_name'); // Bottle 50ml, Tube 30g, etc.
            $table->string('material_type'); // primary, secondary, tertiary
            $table->string('material_category'); // bottle, tube, jar, box, carton, label, cap, pump
            $table->string('sku')->unique()->nullable(); // PKG-2026-0001
            $table->string('supplier_name')->nullable();
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->string('dimensions')->nullable(); // 50ml, 30g, 10x5cm
            $table->string('color')->nullable();
            $table->string('material_composition')->nullable(); // PET, HDPE, Glass, etc.
            $table->boolean('is_recyclable')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'material_type']);
            $table->index(['tenant_id', 'material_category']);
        });

        // 2. Label Versions - Label design tracking
        Schema::create('label_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('formula_id')->nullable()->constrained('cosmetic_formulas')->nullOnDelete();
            $table->foreignId('registration_id')->nullable()->constrained('product_registrations')->nullOnDelete();
            $table->string('label_code')->unique(); // LBL-2026-0001
            $table->string('version_number'); // v1.0, v2.0, etc.
            $table->string('label_type'); // primary, secondary, insert, outer
            $table->string('design_file_path')->nullable();
            $table->text('label_content')->nullable(); // Ingredients, warnings, usage
            $table->string('barcode')->nullable();
            $table->string('qr_code')->nullable();
            $table->date('effective_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('status')->default('draft'); // draft, in_review, approved, active, archived
            $table->string('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'label_type']);
        });

        // 3. Label Compliance Checks - Regulatory compliance verification
        Schema::create('label_compliance_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('label_id')->constrained('label_versions')->onDelete('cascade');
            $table->string('check_name'); // Ingredient list, Net weight, BPOM number, etc.
            $table->string('check_category'); // mandatory, optional, regulatory
            $table->text('requirement'); // What must be included
            $table->boolean('is_compliant')->nullable(); // null = not checked, true = pass, false = fail
            $table->text('findings')->nullable();
            $table->string('checked_by')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'is_compliant']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('label_compliance_checks');
        Schema::dropIfExists('label_versions');
        Schema::dropIfExists('packaging_materials');
    }
};
