<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop tables from previous partial migration safely
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        if (Schema::hasTable('lab_samples')) {
            Schema::dropIfExists('lab_samples');
        }
        if (Schema::hasTable('lab_results')) {
            Schema::dropIfExists('lab_results');
        }
        if (Schema::hasTable('lab_result_details')) {
            Schema::dropIfExists('lab_result_details');
        }
        if (Schema::hasTable('radiology_images')) {
            Schema::dropIfExists('radiology_images');
        }
        if (Schema::hasTable('pacs_studies')) {
            Schema::dropIfExists('pacs_studies');
        }
        if (Schema::hasTable('insurance_claims')) {
            Schema::dropIfExists('insurance_claims');
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Lab Samples table
        if (!Schema::hasTable('lab_samples')) {
            Schema::create('lab_samples', function (Blueprint $table) {
                $table->id();
                $table->string('sample_number')->unique(); // SAMPLE-YYYYMMDD-XXXX
                $table->unsignedBigInteger('lab_order_id'); // FK to lab_orders
                $table->foreignId('patient_id')->constrained()->onDelete('restrict');
                $table->unsignedBigInteger('patient_visit_id')->nullable(); // FK to patient_visits
                $table->foreignId('collected_by')->constrained('users')->onDelete('restrict');
                $table->timestamp('collection_date');
                $table->string('sample_type'); // Blood, Urine, Stool, Sputum, Swab, etc.
                $table->string('container_type')->nullable(); // Vacutainer, Cup, Tube, etc.
                $table->string('container_color')->nullable(); // Red, Purple, Green, etc.
                $table->integer('volume')->nullable(); // ml
                $table->enum('collection_method', ['venipuncture', 'finger_stick', 'capillary', 'catheter', 'midstream', 'clean_catch', 'swab', 'other'])->default('venipuncture');
                $table->string('collection_site')->nullable();
                $table->boolean('requires_centrifuge')->default(false);
                $table->boolean('is_stat')->default(false);
                $table->enum('status', ['collected', 'in_transit', 'received', 'processing', 'completed', 'rejected'])->default('collected');
                $table->timestamp('received_at')->nullable();
                $table->string('rejected_reason')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
    
                $table->index(['status', 'collection_date']);
                $table->index(['sample_type', 'status']);
            });
        }

        // Lab Results table
        if (!Schema::hasTable('lab_results')) {
            Schema::create('lab_results', function (Blueprint $table) {
                $table->id();
                $table->string('result_number')->unique(); // LAB-RESULT-YYYYMMDD-XXXX
                $table->unsignedBigInteger('lab_order_id'); // FK to lab_orders
                $table->unsignedBigInteger('sample_id')->nullable(); // FK to lab_samples
                $table->foreignId('patient_id')->constrained()->onDelete('restrict');
                $table->unsignedBigInteger('patient_visit_id')->nullable(); // FK to patient_visits
                $table->unsignedBigInteger('test_id'); // FK to lab_test_catalogs
                $table->foreignId('performed_by')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('result_date');
                $table->timestamp('verified_at')->nullable();
    
                $table->string('result_value')->nullable();
                $table->string('result_unit')->nullable();
                $table->decimal('numeric_value', 10, 2)->nullable();
                $table->string('reference_range')->nullable();
                $table->string('abnormal_flag')->nullable(); // L (Low), H (High), LL (Critical Low), HH (Critical High), N (Normal)
                $table->boolean('is_critical')->default(false);
                $table->boolean('is_abnormal')->default(false);
                $table->enum('status', ['pending', 'preliminary', 'final', 'corrected', 'cancelled'])->default('pending');
    
                $table->text('interpretation')->nullable();
                $table->text('clinical_notes')->nullable();
                $table->text('comments')->nullable();
    
                $table->timestamps();
                $table->softDeletes();
    
                $table->index(['status', 'result_date']);
                $table->index(['is_critical', 'status']);
                $table->index(['patient_id', 'result_date']);
            });
        }

        // Lab Result Details table (for multi-parameter tests)
        if (!Schema::hasTable('lab_result_details')) {
            Schema::create('lab_result_details', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lab_result_id'); // FK to lab_results
                $table->string('parameter_name');
                $table->string('parameter_code')->nullable();
                $table->string('result_value')->nullable();
                $table->string('result_unit')->nullable();
                $table->decimal('numeric_value', 10, 2)->nullable();
                $table->string('reference_range')->nullable();
                $table->string('reference_low')->nullable();
                $table->string('reference_high')->nullable();
                $table->string('abnormal_flag')->nullable();
                $table->boolean('is_abnormal')->default(false);
                $table->text('interpretation')->nullable();
                $table->timestamps();
    
                $table->index(['lab_result_id', 'parameter_name']);
            });
        }

        // Radiology Images table
        if (!Schema::hasTable('radiology_images')) {
            Schema::create('radiology_images', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('radiology_exam_id'); // FK to radiology_exams
                $table->string('image_number')->unique(); // IMG-YYYYMMDD-XXXX
                $table->string('series_number')->nullable();
                $table->string('instance_number')->nullable();
                $table->string('sop_instance_uid')->nullable(); // DICOM UID
                $table->string('modality'); // XRAY, CT, MRI, US, etc.
                $table->string('file_path');
                $table->string('file_name');
                $table->string('file_type')->default('dicom'); // dicom, jpg, png, pdf
                $table->bigInteger('file_size')->nullable(); // bytes
                $table->string('thumbnail_path')->nullable();
                $table->integer('width')->nullable();
                $table->integer('height')->nullable();
                $table->integer('bits_allocated')->nullable();
                $table->string('patient_position')->nullable();
                $table->string('view_position')->nullable(); // AP, PA, Lateral, etc.
                $table->json('dicom_metadata')->nullable(); // Full DICOM metadata
                $table->timestamp('image_date');
                $table->timestamps();
    
                $table->index(['radiology_exam_id', 'series_number']);
                $table->index(['modality', 'image_date']);
            });
        }

        // PACS Studies table
        if (!Schema::hasTable('pacs_studies')) {
            Schema::create('pacs_studies', function (Blueprint $table) {
                $table->id();
                $table->string('study_instance_uid')->unique(); // DICOM Study UID
                $table->string('study_number')->unique(); // STUDY-YYYYMMDD-XXXX
                $table->unsignedBigInteger('radiology_exam_id'); // FK to radiology_exams
                $table->foreignId('patient_id')->constrained()->onDelete('restrict');
                $table->unsignedBigInteger('patient_visit_id')->nullable(); // FK to patient_visits
                $table->foreignId('referring_doctor_id')->nullable()->constrained('doctors')->onDelete('set null');
                $table->foreignId('radiologist_id')->nullable()->constrained('doctors')->onDelete('set null');
                $table->timestamp('study_date');
                $table->string('study_description')->nullable();
                $table->string('accession_number')->nullable();
                $table->integer('total_series')->default(0);
                $table->integer('total_images')->default(0);
                $table->bigInteger('total_size')->nullable(); // bytes
                $table->string('modality');
                $table->string('body_part')->nullable();
                $table->enum('status', ['scheduled', 'in_progress', 'interpreted', 'reported', 'completed'])->default('scheduled');
                $table->timestamp('interpreted_at')->nullable();
                $table->timestamp('reported_at')->nullable();
                $table->timestamps();
    
                $table->index(['status', 'study_date']);
                $table->index(['patient_id', 'study_date']);
                $table->index(['modality', 'status']);
            });
        }

        // Insurance Claims table
        if (!Schema::hasTable('insurance_claims')) {
            Schema::create('insurance_claims', function (Blueprint $table) {
                $table->id();
                $table->string('claim_number')->unique(); // CLAIM-YYYYMMDD-XXXX
                $table->unsignedBigInteger('medical_bill_id'); // FK to medical_bills
                $table->foreignId('patient_id')->constrained()->onDelete('restrict');
                $table->unsignedBigInteger('patient_visit_id')->nullable(); // FK to patient_visits
                $table->unsignedBigInteger('admission_id')->nullable(); // FK to admissions
                $table->unsignedBigInteger('insurance_provider_id')->nullable(); // FK to insurance_providers
                $table->string('policy_number');
                $table->string('group_number')->nullable();
                $table->string('member_id');
                $table->string('authorization_number')->nullable();
                $table->timestamp('submission_date')->nullable();
                $table->timestamp('processing_date')->nullable();
                $table->timestamp('adjudication_date')->nullable();
                $table->timestamp('payment_date')->nullable();
    
                $table->decimal('total_billed', 15, 2)->default(0);
                $table->decimal('allowed_amount', 15, 2)->nullable();
                $table->decimal('denied_amount', 15, 2)->default(0);
                $table->decimal('deductible_amount', 15, 2)->default(0);
                $table->decimal('copay_amount', 15, 2)->default(0);
                $table->decimal('coinsurance_amount', 15, 2)->default(0);
                $table->decimal('insurance_payment', 15, 2)->default(0);
                $table->decimal('patient_responsibility', 15, 2)->default(0);
                $table->decimal('write_off_amount', 15, 2)->default(0);
    
                $table->enum('status', ['draft', 'submitted', 'received', 'in_review', 'approved', 'denied', 'partially_paid', 'paid', 'appealed', 'cancelled'])->default('draft');
                $table->enum('claim_type', ['institutional', 'professional', 'dental', 'pharmacy'])->default('institutional');
                $table->string('primary_diagnosis_code')->nullable(); // ICD-10
                $table->string('secondary_diagnosis_code')->nullable();
                $table->text('claim_notes')->nullable();
                $table->text('denial_reason')->nullable();
                $table->text('appeal_notes')->nullable();
                $table->timestamp('appeal_date')->nullable();
    
                $table->timestamps();
                $table->softDeletes();
    
                $table->index(['status', 'submission_date']);
                $table->index(['patient_id', 'status']);
                $table->index(['insurance_provider_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        if (Schema::hasTable('insurance_claims')) {
            Schema::dropIfExists('insurance_claims');
        }
        if (Schema::hasTable('pacs_studies')) {
            Schema::dropIfExists('pacs_studies');
        }
        if (Schema::hasTable('radiology_images')) {
            Schema::dropIfExists('radiology_images');
        }
        if (Schema::hasTable('lab_result_details')) {
            Schema::dropIfExists('lab_result_details');
        }
        if (Schema::hasTable('lab_results')) {
            Schema::dropIfExists('lab_results');
        }
        if (Schema::hasTable('lab_samples')) {
            Schema::dropIfExists('lab_samples');
        }

        // Drop tables from previous partial migration if exists
        if (Schema::hasTable('lab_orders')) {
            Schema::dropIfExists('lab_orders');
        }
        if (Schema::hasTable('lab_test_results')) {
            Schema::dropIfExists('lab_test_results');
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
