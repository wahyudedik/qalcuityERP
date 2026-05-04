<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations - QC Laboratory Module.
     */
    public function up(): void
    {
        // 1. QC Test Templates - Standard test procedures (MUST BE FIRST)
        if (!Schema::hasTable('qc_test_templates')) {
            Schema::create('qc_test_templates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('template_name');
                $table->string('template_code')->unique(); // TMP-001
                $table->string('test_category'); // microbial, heavy_metal, preservative, etc.
                $table->json('test_parameters'); // Standard parameters for this template
                $table->json('acceptance_criteria'); // Pass/fail criteria
                $table->text('procedure')->nullable(); // Step-by-step procedure
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
    
                $table->index(['tenant_id', 'test_category']);
            });
        }

        // 2. QC Test Results - Individual test results
        if (!Schema::hasTable('qc_test_results')) {
            Schema::create('qc_test_results', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('batch_id')->nullable()->constrained('cosmetic_batch_records')->nullOnDelete();
                $table->foreignId('template_id')->nullable()->constrained('qc_test_templates')->nullOnDelete();
                $table->string('test_code')->unique(); // QC-2026-0001
                $table->string('test_name'); // Microbial, Heavy Metal, etc.
                $table->string('test_category'); // microbial, heavy_metal, preservative, patch_test, physical, chemical
                $table->string('sample_id')->nullable(); // Sample identifier
                $table->json('parameters'); // Test parameters [{name, value, limit, result}]
                $table->string('result')->default('pending'); // pending, pass, fail, inconclusive
                $table->text('observations')->nullable();
                $table->text('recommendations')->nullable();
                $table->foreignId('tested_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('test_date');
                $table->timestamp('approved_at')->nullable();
                $table->string('status')->default('draft'); // draft, completed, approved, rejected
                $table->timestamps();
                $table->softDeletes();
    
                $table->index(['tenant_id', 'test_category']);
                $table->index(['tenant_id', 'result']);
                $table->index(['tenant_id', 'test_date']);
            });
        }

        // 3. COA Certificates - Certificate of Analysis
        if (!Schema::hasTable('coa_certificates')) {
            Schema::create('coa_certificates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('batch_id')->constrained('cosmetic_batch_records')->onDelete('cascade');
                $table->string('coa_number')->unique(); // COA-2026-0001
                $table->date('issue_date');
                $table->date('expiry_date')->nullable();
                $table->json('test_results'); // Summary of all test results
                $table->text('conclusion')->nullable();
                $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('status')->default('draft'); // draft, issued, approved, revoked
                $table->timestamps();
                $table->softDeletes();
    
                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'issue_date']);
            });
        }

        // 4. OOS Investigations - Out of Specification
        if (!Schema::hasTable('oos_investigations')) {
            Schema::create('oos_investigations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('test_result_id')->nullable()->constrained('qc_test_results')->nullOnDelete();
                $table->foreignId('batch_id')->nullable()->constrained('cosmetic_batch_records')->nullOnDelete();
                $table->string('oos_number')->unique(); // OOS-2026-0001
                $table->string('oos_type'); // laboratory, manufacturing, stability, complaint
                $table->text('description');
                $table->text('root_cause')->nullable();
                $table->text('corrective_action')->nullable();
                $table->text('preventive_action')->nullable();
                $table->string('severity')->default('medium'); // low, medium, high, critical
                $table->string('status')->default('open'); // open, investigating, completed, closed
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('investigated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('discovery_date');
                $table->timestamp('completion_date')->nullable();
                $table->timestamps();
                $table->softDeletes();
    
                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'severity']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oos_investigations');
        Schema::dropIfExists('coa_certificates');
        Schema::dropIfExists('qc_test_results');
        Schema::dropIfExists('qc_test_templates');
    }
};
