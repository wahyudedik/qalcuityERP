<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - BPOM/Regulatory Compliance Module.
     */
    public function up(): void
    {
        // 1. Product Registrations - BPOM notification tracking
        if (! Schema::hasTable('product_registrations')) {
            Schema::create('product_registrations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('formula_id')->nullable()->constrained('cosmetic_formulas')->nullOnDelete();
                $table->string('registration_number')->unique(); // NA12345678901
                $table->string('product_name');
                $table->string('product_category'); // skincare, haircare, makeup, fragrance, etc.
                $table->string('registration_type')->default('notification'); // notification, certification
                $table->string('status')->default('pending'); // pending, submitted, approved, rejected, expired
                $table->date('submission_date')->nullable();
                $table->date('approval_date')->nullable();
                $table->date('expiry_date')->nullable();
                $table->string('notified_by')->nullable(); // BPOM official name
                $table->text('notes')->nullable();
                $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'expiry_date']);
            });
        }

        // 2. Registration Documents - Uploaded certificates
        if (! Schema::hasTable('registration_documents')) {
            Schema::create('registration_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('registration_id')->constrained('product_registrations')->onDelete('cascade');
                $table->string('document_name');
                $table->string('document_type'); // certificate, formula, label, test_report, sds, other
                $table->string('file_path');
                $table->string('file_name');
                $table->integer('file_size')->nullable(); // in KB
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'document_type']);
            });
        }

        // 3. Ingredient Restrictions - Banned/restricted ingredients
        if (! Schema::hasTable('ingredient_restrictions')) {
            Schema::create('ingredient_restrictions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('ingredient_name'); // INCI name
                $table->string('cas_number')->nullable();
                $table->string('restriction_type'); // banned, restricted, limited
                $table->decimal('max_limit', 10, 2)->nullable(); // Maximum allowed percentage
                $table->text('regulation_reference')->nullable(); // BPOM regulation number
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'restriction_type']);
            });
        }

        // 4. Safety Data Sheets - SDS management
        if (! Schema::hasTable('safety_data_sheets')) {
            Schema::create('safety_data_sheets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('formula_id')->nullable()->constrained('cosmetic_formulas')->nullOnDelete();
                $table->foreignId('registration_id')->nullable()->constrained('product_registrations')->nullOnDelete();
                $table->string('sds_number')->unique(); // SDS-2026-0001
                $table->string('product_name');
                $table->string('version')->default('1.0');
                $table->date('issue_date');
                $table->date('review_date')->nullable();
                $table->json('hazard_statements')->nullable(); // Hazard codes
                $table->json('precautionary_statements')->nullable(); // Safety codes
                $table->text('first_aid_measures')->nullable();
                $table->text('fire_fighting_measures')->nullable();
                $table->text('handling_storage')->nullable();
                $table->string('file_path')->nullable();
                $table->string('status')->default('draft'); // draft, active, outdated
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'issue_date']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('safety_data_sheets');
        Schema::dropIfExists('ingredient_restrictions');
        Schema::dropIfExists('registration_documents');
        Schema::dropIfExists('product_registrations');
    }
};
