<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop existing tables if they exist
        Schema::dropIfExists('radiology_report_templates');
        Schema::dropIfExists('pacs_integrations');
        Schema::dropIfExists('radiology_results');
        Schema::dropIfExists('radiology_orders');
        Schema::dropIfExists('radiology_exams');

        // Radiology Exams Catalog
        if (! Schema::hasTable('radiology_exams')) {
            Schema::create('radiology_exams', function (Blueprint $table) {
                $table->id();
                $table->string('exam_code')->unique(); // XR-CHST, CT-HEAD, MRI-BRAIN, etc.
                $table->string('exam_name');
                $table->string('modality'); // X-Ray, CT Scan, MRI, Ultrasound, Fluoroscopy, Mammography, etc.
                $table->string('body_part'); // Chest, Head, Abdomen, etc.
                $table->string('body_region')->nullable(); // Upper, Lower, etc.
                $table->text('description')->nullable();

                // Pricing
                $table->decimal('price', 10, 2)->default(0);
                $table->decimal('cost', 10, 2)->default(0);

                // Duration
                $table->integer('duration_minutes')->default(0);
                $table->integer('preparation_time')->default(0); // minutes

                // Requirements
                $table->boolean('requires_contrast')->default(false);
                $table->string('contrast_type')->nullable(); // Iodine, Gadolinium, etc.
                $table->boolean('requires_fasting')->default(false);
                $table->text('preparation_instructions')->nullable();
                $table->text('contraindications')->nullable();

                // Technical
                $table->json('protocols')->nullable(); // JSON array of protocols
                $table->text('technical_notes')->nullable();

                // Status
                $table->boolean('is_active')->default(true);
                $table->boolean('requires_authorization')->default(false);

                $table->timestamps();

                $table->index('exam_code');
                $table->index('modality');
                $table->index('body_part');
                $table->index('is_active');
            });
        }

        // Radiology Orders
        if (! Schema::hasTable('radiology_orders')) {
            Schema::create('radiology_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained()->onDelete('cascade');
                $table->unsignedBigInteger('visit_id')->nullable(); // FK to patient_visits
                $table->foreignId('exam_id')->constrained('radiology_exams')->onDelete('restrict');
                $table->unsignedBigInteger('ordered_by'); // FK to doctors (will be created separately)
                $table->unsignedBigInteger('radiologist_id')->nullable(); // FK to doctors
                $table->foreignId('technologist_id')->nullable()->constrained('users')->onDelete('set null');

                // Order Information
                $table->string('order_number')->unique(); // RAD-ORD-YYYYMMDD-XXXX
                $table->datetime('order_date');
                $table->datetime('scheduled_date')->nullable();
                $table->datetime('started_at')->nullable();
                $table->datetime('completed_at')->nullable();
                $table->datetime('reported_at')->nullable();

                // Clinical Information
                $table->text('clinical_indication'); // Why exam is ordered
                $table->text('clinical_history')->nullable();
                $table->string('icd10_code')->nullable();

                // Priority & Status
                $table->enum('priority', ['routine', 'urgent', 'stat'])->default('routine');
                $table->enum('status', ['ordered', 'scheduled', 'in_progress', 'completed', 'reported', 'cancelled'])
                    ->default('ordered');

                // Contrast
                $table->boolean('contrast_required')->default(false);
                $table->string('contrast_type')->nullable();
                $table->decimal('contrast_volume', 8, 2)->nullable(); // ml
                $table->text('contrast_notes')->nullable();

                // Authorization
                $table->boolean('is_authorized')->default(false);
                $table->foreignId('authorized_by')->nullable()->constrained('users')->onDelete('set null');
                $table->datetime('authorized_at')->nullable();
                $table->string('authorization_number')->nullable();

                // Scheduling
                $table->string('room_number')->nullable();
                $table->string('equipment_id')->nullable();

                $table->text('special_instructions')->nullable();
                $table->text('notes')->nullable();
                $table->softDeletes();
                $table->timestamps();

                $table->index('order_number');
                $table->index('patient_id');
                $table->index('status');
                $table->index('priority');
                $table->index('scheduled_date');
                $table->index(['status', 'priority']);
            });
        }

        // Radiology Results
        if (! Schema::hasTable('radiology_results')) {
            Schema::create('radiology_results', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('radiology_orders')->onDelete('cascade');
                $table->foreignId('patient_id')->constrained()->onDelete('cascade');
                $table->unsignedBigInteger('reported_by'); // FK to doctors
                $table->unsignedBigInteger('verified_by')->nullable(); // FK to doctors

                // Report Information
                $table->string('report_number')->unique(); // RAD-RPT-YYYYMMDD-XXXX
                $table->datetime('exam_date');
                $table->datetime('reported_at');
                $table->datetime('verified_at')->nullable();

                // Report Content
                $table->text('clinical_history');
                $table->text('examination_performed');
                $table->text('technique')->nullable(); // How exam was performed
                $table->text('comparison')->nullable(); // Previous studies
                $table->text('findings'); // Detailed findings
                $table->text('impression'); // Conclusion/diagnosis
                $table->text('recommendations')->nullable();

                // Status
                $table->enum('status', ['preliminary', 'final', 'amended', 'addended', 'cancelled'])->default('preliminary');
                $table->boolean('is_critical')->default(false);
                $table->text('critical_findings')->nullable();

                // Images
                $table->json('image_urls')->nullable(); // Array of image URLs
                $table->string('dicom_study_uid')->nullable();
                $table->integer('series_count')->default(0);
                $table->integer('image_count')->default(0);

                // Signature
                $table->boolean('is_signed')->default(false);
                $table->timestamp('signed_at')->nullable();
                $table->string('digital_signature')->nullable();

                $table->text('notes')->nullable();
                $table->softDeletes();
                $table->timestamps();

                $table->index('report_number');
                $table->index('order_id');
                $table->index('status');
                $table->index('reported_at');
            });
        }

        // PACS Integration
        if (! Schema::hasTable('pacs_integrations')) {
            Schema::create('pacs_integrations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained()->onDelete('cascade');
                $table->foreignId('order_id')->nullable()->constrained('radiology_orders')->onDelete('set null');
                $table->foreignId('result_id')->nullable()->constrained('radiology_results')->onDelete('set null');

                // DICOM Information
                $table->string('study_instance_uid')->unique(); // DICOM Study Instance UID
                $table->string('accession_number')->nullable();
                $table->string('modality'); // CT, MR, CR, DX, US, etc.
                $table->string('study_description')->nullable();
                $table->date('study_date');
                $table->time('study_time')->nullable();

                // Series & Images
                $table->integer('series_count')->default(0);
                $table->integer('image_count')->default(0);
                $table->json('series_details')->nullable(); // JSON array of series info

                // PACS Storage
                $table->string('pacs_server')->nullable();
                $table->string('pacs_ae_title')->nullable(); // Application Entity Title
                $table->string('storage_path')->nullable();
                $table->bigInteger('storage_size_bytes')->default(0);

                // Retrieval
                $table->string('viewer_url')->nullable();
                $table->string('thumbnail_url')->nullable();
                $table->json('image_urls')->nullable();

                // Status
                $table->enum('status', ['pending', 'received', 'available', 'archived', 'failed'])->default('pending');
                $table->datetime('received_at')->nullable();
                $table->datetime('archived_at')->nullable();

                // Metadata
                $table->json('dicom_metadata')->nullable(); // Full DICOM metadata
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('study_instance_uid');
                $table->index('patient_id');
                $table->index('modality');
                $table->index('study_date');
                $table->index('status');
            });
        }

        // Radiology Report Templates
        if (! Schema::hasTable('radiology_report_templates')) {
            Schema::create('radiology_report_templates', function (Blueprint $table) {
                $table->id();
                $table->string('template_code')->unique();
                $table->string('template_name');
                $table->string('modality');
                $table->string('body_part');
                $table->string('exam_type')->nullable();

                // Template Content
                $table->text('clinical_history_template')->nullable();
                $table->text('technique_template')->nullable();
                $table->text('findings_template'); // With placeholders
                $table->text('impression_template');
                $table->json('template_variables')->nullable(); // Available placeholders

                // Usage
                $table->integer('usage_count')->default(0);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_default')->default(false);

                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('template_code');
                $table->index('modality');
                $table->index('body_part');
            });
        }

        // Radiology Equipment
        if (! Schema::hasTable('radiology_equipments')) {
            Schema::create('radiology_equipments', function (Blueprint $table) {
                $table->id();
                $table->string('equipment_code')->unique();
                $table->string('equipment_name');
                $table->string('modality'); // CT, MRI, X-Ray, US, etc.
                $table->string('manufacturer')->nullable();
                $table->string('model_number')->nullable();
                $table->string('serial_number')->nullable();

                // Specifications
                $table->json('specifications')->nullable(); // Technical specs
                $table->integer('year_installed')->nullable();

                // Location
                $table->string('room_number')->nullable();
                $table->string('department')->default('Radiology');

                // DICOM
                $table->string('dicom_ae_title')->nullable();
                $table->string('dicom_port')->nullable();
                $table->ipAddress('dicom_ip_address')->nullable();

                // Status
                $table->enum('status', ['operational', 'maintenance', 'calibration', 'out_of_service'])->default('operational');
                $table->boolean('is_active')->default(true);

                // Maintenance
                $table->date('last_maintenance_date')->nullable();
                $table->date('next_maintenance_date')->nullable();
                $table->text('maintenance_notes')->nullable();

                $table->timestamps();

                $table->index('equipment_code');
                $table->index('modality');
                $table->index('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radiology_equipments');
        Schema::dropIfExists('radiology_report_templates');
        Schema::dropIfExists('pacs_integrations');
        Schema::dropIfExists('radiology_results');
        Schema::dropIfExists('radiology_orders');
        Schema::dropIfExists('radiology_exams');
    }
};
