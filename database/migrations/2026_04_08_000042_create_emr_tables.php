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
        // ICD-10 Codes Reference Table
        if (! Schema::hasTable('icd10_codes')) {
            Schema::create('icd10_codes', function (Blueprint $table) {
                $table->id();
                $table->string('code', 10)->unique(); // A00.0, B99.9, etc.
                $table->string('description');
                $table->string('category')->nullable(); // Chapter/Category
                $table->string('subcategory')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('code');
                $table->index('category');
            });
        }

        // Diagnoses
        if (! Schema::hasTable('diagnoses')) {
            Schema::create('diagnoses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('visit_id')->constrained('patient_visits')->onDelete('cascade');
                $table->foreignId('doctor_id')->nullable()->constrained('doctors')->onDelete('set null');

                // Diagnosis Information
                $table->string('icd10_code', 10);
                $table->string('icd10_description');
                $table->text('diagnosis_notes')->nullable();

                // Type
                $table->enum('diagnosis_type', ['primary', 'secondary', 'differential', 'working'])->default('working');
                $table->enum('status', ['provisional', 'confirmed', 'ruled_out', 'chronic'])->default('provisional');

                // Priority
                $table->integer('priority')->default(0); // For sorting

                // Standard fields
                $table->text('notes')->nullable();
                $table->softDeletes();
                $table->timestamps();

                $table->index('visit_id');
                $table->index('icd10_code');
                $table->index('diagnosis_type');
                $table->index('status');
            });
        }

        // Prescriptions
        if (! Schema::hasTable('prescriptions')) {
            Schema::create('prescriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('visit_id')->constrained('patient_visits')->onDelete('cascade');
                $table->foreignId('patient_id')->constrained()->onDelete('cascade');
                $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');

                // Prescription Information
                $table->string('prescription_number')->unique();
                $table->date('prescription_date');
                $table->text('diagnosis_summary')->nullable();
                $table->text('special_instructions')->nullable();

                // Status
                $table->enum('status', ['draft', 'active', 'completed', 'cancelled', 'expired'])->default('draft');
                $table->date('valid_until')->nullable();

                // Dispensing
                $table->boolean('is_dispensed')->default(false);
                $table->timestamp('dispensed_at')->nullable();
                $table->foreignId('dispensed_by')->nullable()->constrained('users')->onDelete('set null');
                $table->string('pharmacy_location')->nullable();

                // Standard fields
                $table->text('notes')->nullable();
                $table->softDeletes();
                $table->timestamps();

                $table->index('visit_id');
                $table->index('patient_id');
                $table->index('doctor_id');
                $table->index('prescription_number');
                $table->index('status');
            });
        }

        // Prescription Items
        if (! Schema::hasTable('prescription_items')) {
            Schema::create('prescription_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('prescription_id')->constrained()->onDelete('cascade');

                // Medicine Information
                $table->string('medicine_name');
                $table->string('generic_name')->nullable();
                $table->string('brand_name')->nullable();
                $table->string('medicine_type')->nullable(); // tablet, capsule, syrup, injection, etc.
                $table->string('strength')->nullable(); // 500mg, 250ml, etc.

                // Dosage Instructions
                $table->string('dosage'); // 500mg, 1 tablet, etc.
                $table->string('frequency'); // 3x sehari, setiap 8 jam, etc.
                $table->string('route')->default('oral'); // oral, topical, injection, etc.
                $table->integer('duration_days')->nullable();
                $table->text('special_instructions')->nullable(); // sebelum makan, setelah makan, etc.

                // Quantity
                $table->integer('quantity');
                $table->integer('quantity_dispensed')->default(0);

                // Status
                $table->boolean('is_dispensed')->default(false);

                // Standard fields
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('prescription_id');
                $table->index('medicine_name');
            });
        }

        // Medical Procedures
        if (! Schema::hasTable('medical_procedures')) {
            Schema::create('medical_procedures', function (Blueprint $table) {
                $table->id();
                $table->foreignId('visit_id')->constrained('patient_visits')->onDelete('cascade');
                $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
                $table->foreignId('performed_by')->nullable()->constrained('users')->onDelete('set null');

                // Procedure Information
                $table->string('procedure_code')->nullable(); // CPT/ICD-10-PCS code
                $table->string('procedure_name');
                $table->text('procedure_description')->nullable();
                $table->text('indication')->nullable(); // Why procedure is done

                // Execution
                $table->datetime('procedure_date')->nullable();
                $table->integer('duration_minutes')->nullable();
                $table->string('anesthesia_type')->nullable(); // local, general, regional, none
                $table->text('findings')->nullable();
                $table->text('complications')->nullable();

                // Status
                $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled', 'complicated'])->default('planned');
                $table->enum('urgency', ['elective', 'urgent', 'emergency'])->default('elective');

                // Outcome
                $table->text('outcome')->nullable();
                $table->text('post_procedure_instructions')->nullable();

                // Standard fields
                $table->text('notes')->nullable();
                $table->softDeletes();
                $table->timestamps();

                $table->index('visit_id');
                $table->index('procedure_code');
                $table->index('status');
            });
        }

        // Lab Orders
        if (! Schema::hasTable('lab_orders')) {
            Schema::create('lab_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('visit_id')->constrained('patient_visits')->onDelete('cascade');
                $table->foreignId('patient_id')->constrained()->onDelete('cascade');
                $table->foreignId('ordered_by')->constrained('doctors')->onDelete('cascade');
                $table->foreignId('performed_by')->nullable()->constrained('users')->onDelete('set null');

                // Order Information
                $table->string('order_number')->unique();
                $table->string('test_type'); // Blood Test, Urine Test, X-Ray, etc.
                $table->text('test_description')->nullable();
                $table->json('test_parameters')->nullable(); // ["glucose", "cholesterol", etc.]

                // Priority & Status
                $table->enum('priority', ['routine', 'urgent', 'stat'])->default('routine');
                $table->enum('status', ['ordered', 'collected', 'in_progress', 'completed', 'cancelled', 'rejected'])->default('ordered');

                // Sample Collection
                $table->string('sample_type')->nullable(); // blood, urine, tissue, etc.
                $table->datetime('collection_time')->nullable();
                $table->string('collected_by')->nullable();

                // Results
                $table->datetime('completed_at')->nullable();
                $table->foreignId('verified_by')->nullable()->constrained('doctors')->onDelete('set null');
                $table->datetime('verified_at')->nullable();

                // Standard fields
                $table->text('clinical_notes')->nullable();
                $table->text('notes')->nullable();
                $table->softDeletes();
                $table->timestamps();

                $table->index('visit_id');
                $table->index('patient_id');
                $table->index('order_number');
                $table->index('status');
                $table->index('priority');
            });
        }

        // Lab Results
        if (! Schema::hasTable('lab_results')) {
            Schema::create('lab_results', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lab_order_id')->constrained()->onDelete('cascade');

                // Test Parameter
                $table->string('parameter_name'); // Glucose, Hemoglobin, etc.
                $table->string('parameter_code')->nullable(); // GLU, HGB, etc.

                // Results
                $table->string('result_value');
                $table->string('unit')->nullable(); // mg/dL, g/dL, etc.
                $table->string('reference_range')->nullable(); // 70-100 mg/dL
                $table->string('reference_min')->nullable();
                $table->string('reference_max')->nullable();

                // Flag
                $table->enum('flag', ['normal', 'low', 'high', 'critical', 'abnormal'])->default('normal');
                $table->boolean('is_abnormal')->default(false);

                // Interpretation
                $table->text('interpretation')->nullable();

                // Standard fields
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('lab_order_id');
                $table->index('parameter_name');
                $table->index('flag');
            });
        }

        // EMR Documents
        if (! Schema::hasTable('emr_documents')) {
            Schema::create('emr_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained()->onDelete('cascade');
                $table->foreignId('doctor_id')->nullable()->constrained('doctors')->onDelete('set null');
                $table->foreignId('visit_id')->nullable()->constrained('patient_visits')->onDelete('set null');

                // Document Information
                $table->string('document_number')->unique();
                $table->string('document_type'); // consultation, procedure, lab, radiology, discharge, etc.
                $table->string('title');
                $table->text('content'); // JSON or HTML content
                $table->json('metadata')->nullable(); // Additional structured data

                // Status
                $table->enum('status', ['draft', 'finalized', 'amended', 'archived'])->default('draft');
                $table->boolean('is_signed')->default(false);
                $table->timestamp('signed_at')->nullable();
                $table->string('digital_signature')->nullable();

                // Versioning
                $table->integer('version')->default(1);
                $table->foreignId('parent_id')->nullable()->constrained('emr_documents')->onDelete('set null');

                // Standard fields
                $table->text('notes')->nullable();
                $table->softDeletes();
                $table->timestamps();

                $table->index('patient_id');
                $table->index('document_type');
                $table->index('status');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        if (Schema::hasTable('emr_documents')) {
            Schema::dropIfExists('emr_documents');
        }
        if (Schema::hasTable('lab_results')) {
            Schema::dropIfExists('lab_results');
        }
        if (Schema::hasTable('lab_orders')) {
            Schema::dropIfExists('lab_orders');
        }
        if (Schema::hasTable('medical_procedures')) {
            Schema::dropIfExists('medical_procedures');
        }
        if (Schema::hasTable('prescription_items')) {
            Schema::dropIfExists('prescription_items');
        }
        if (Schema::hasTable('prescriptions')) {
            Schema::dropIfExists('prescriptions');
        }
        if (Schema::hasTable('diagnoses')) {
            Schema::dropIfExists('diagnoses');
        }
        if (Schema::hasTable('icd10_codes')) {
            Schema::dropIfExists('icd10_codes');
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
