<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop existing tables if they exist
        Schema::dropIfExists('lab_qc_logs');
        Schema::dropIfExists('lab_equipment_calibrations');
        Schema::dropIfExists('lab_reports');
        Schema::dropIfExists('lab_result_details');
        Schema::dropIfExists('lab_samples');
        Schema::dropIfExists('lab_test_catalogs');
        Schema::dropIfExists('lab_equipments');

        // Lab Test Catalog
        Schema::create('lab_test_catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('test_code')->unique(); // CBC, LFT, RFT, etc.
            $table->string('test_name');
            $table->string('category'); // Hematology, Chemistry, Microbiology, etc.
            $table->string('subcategory')->nullable();
            $table->text('description')->nullable();

            // Pricing
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('cost', 10, 2)->default(0);

            // Turnaround
            $table->integer('turnaround_time')->default(0); // hours
            $table->boolean('is_stat_available')->default(false);
            $table->integer('stat_turnaround_time')->nullable(); // hours

            // Sample Requirements
            $table->string('sample_type'); // Blood, Urine, Stool, etc.
            $table->string('container_type')->nullable(); // EDTA tube, plain tube, etc.
            $table->integer('minimum_volume')->nullable(); // ml
            $table->text('collection_instructions')->nullable();

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_fasting')->default(false);
            $table->boolean('is_package')->default(false);

            $table->timestamps();

            $table->index('test_code');
            $table->index('category');
            $table->index('is_active');
        });

        // Lab Equipments
        Schema::create('lab_equipments', function (Blueprint $table) {
            $table->id();
            $table->string('equipment_code')->unique();
            $table->string('equipment_name');
            $table->string('manufacturer')->nullable();
            $table->string('model_number')->nullable();
            $table->string('serial_number')->nullable();

            // Location
            $table->string('location')->nullable(); // Lab room, department
            $table->string('department')->nullable();

            // Calibration
            $table->date('installation_date')->nullable();
            $table->date('last_calibration_date')->nullable();
            $table->date('next_calibration_date')->nullable();
            $table->integer('calibration_interval_months')->default(12);

            // Maintenance
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->text('maintenance_notes')->nullable();

            // Status
            $table->enum('status', ['operational', 'under_maintenance', 'calibration', 'out_of_service', 'decommissioned'])
                ->default('operational');
            $table->boolean('requires_calibration')->default(true);
            $table->boolean('is_active')->default(true);

            // Documentation
            $table->text('specifications')->nullable();
            $table->string('manual_path')->nullable();

            $table->timestamps();

            $table->index('equipment_code');
            $table->index('status');
            $table->index('next_calibration_date');
        });

        // Equipment Calibrations
        Schema::create('lab_equipment_calibrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('lab_equipments')->onDelete('cascade');
            $table->foreignId('performed_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');

            // Calibration Details
            $table->date('calibration_date');
            $table->text('calibration_procedure')->nullable();
            $table->text('calibration_results')->nullable(); // JSON
            $table->enum('calibration_status', ['passed', 'failed', 'adjusted'])->default('passed');

            // Standards Used
            $table->string('standard_reference')->nullable();
            $table->text('standards_used')->nullable(); // JSON

            // Next Calibration
            $table->date('next_calibration_date');

            // Certification
            $table->string('certificate_number')->nullable();
            $table->string('certificate_path')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('equipment_id');
            $table->index('calibration_date');
            $table->index('calibration_status');
        });

        // Lab Samples
        Schema::create('lab_samples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_order_id')->unsignedBigInteger(); // FK to lab_orders (will be created separately)
            $table->foreignId('collected_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('tested_by')->nullable()->constrained('users')->onDelete('set null');

            // Sample Information
            $table->string('sample_number')->unique(); // SAMPLE-YYYYMMDD-XXXX
            $table->string('sample_type'); // Blood, Urine, Stool, Sputum, etc.
            $table->string('container_type')->nullable();

            // Collection
            $table->datetime('collection_time');
            $table->string('collection_site')->nullable();
            $table->text('collection_notes')->nullable();

            // Receipt
            $table->datetime('received_at')->nullable();
            $table->enum('sample_condition', ['acceptable', 'hemolyzed', 'clotted', 'insufficient', 'contaminated', 'rejected'])
                ->default('acceptable');
            $table->text('rejection_reason')->nullable();

            // Processing
            $table->datetime('processing_started_at')->nullable();
            $table->datetime('processing_completed_at')->nullable();
            $table->string('processing_method')->nullable();

            // Status
            $table->enum('status', ['collected', 'received', 'in_progress', 'completed', 'rejected', 'discarded'])
                ->default('collected');

            // Storage
            $table->string('storage_location')->nullable();
            $table->datetime('storage_temperature')->nullable();
            $table->date('retention_until')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('sample_number');
            $table->index('lab_order_id');
            $table->index('status');
            $table->index('collection_time');
        });

        // Lab Result Details
        Schema::create('lab_result_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sample_id')->constrained('lab_samples')->onDelete('cascade');
            $table->foreignId('test_id')->nullable()->constrained('lab_test_catalogs')->onDelete('set null');
            $table->foreignId('tested_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');

            // Test Information
            $table->string('parameter_name'); // Glucose, Hemoglobin, etc.
            $table->string('parameter_code')->nullable(); // GLU, HGB, etc.
            $table->string('test_method')->nullable();

            // Results
            $table->string('result_value');
            $table->string('unit')->nullable(); // mg/dL, g/dL, etc.
            $table->string('reference_range_min')->nullable();
            $table->string('reference_range_max')->nullable();
            $table->string('reference_range_display')->nullable(); // "70-100 mg/dL"

            // Flag
            $table->enum('flag', ['normal', 'low', 'high', 'critical_low', 'critical_high', 'abnormal'])->default('normal');
            $table->boolean('is_critical')->default(false);
            $table->boolean('is_abnormal')->default(false);

            // Validation
            $table->enum('validation_status', ['preliminary', 'verified', 'amended', 'cancelled'])->default('preliminary');
            $table->datetime('verified_at')->nullable();

            // Interpretation
            $table->text('interpretation')->nullable();
            $table->text('clinical_notes')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('sample_id');
            $table->index('parameter_name');
            $table->index('flag');
            $table->index('validation_status');
        });

        // Lab Reports
        Schema::create('lab_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_order_id')->unsignedBigInteger(); // FK to lab_orders
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('generated_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');

            // Report Information
            $table->string('report_number')->unique(); // LAB-RPT-YYYYMMDD-XXXX
            $table->datetime('generated_at');
            $table->datetime('approved_at')->nullable();

            // Status
            $table->enum('status', ['draft', 'preliminary', 'final', 'amended', 'cancelled'])->default('draft');

            // Report Content
            $table->json('test_results')->nullable(); // JSON array of results
            $table->text('overall_interpretation')->nullable();
            $table->text('clinical_correlation')->nullable();
            $table->text('recommendations')->nullable();

            // Documents
            $table->string('pdf_path')->nullable();
            $table->string('digital_signature_path')->nullable();
            $table->boolean('is_signed')->default(false);

            // Delivery
            $table->enum('delivery_method', ['print', 'email', 'portal', 'pickup'])->nullable();
            $table->datetime('delivered_at')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('report_number');
            $table->index('lab_order_id');
            $table->index('status');
            $table->index('generated_at');
        });

        // Lab QC (Quality Control) Logs
        Schema::create('lab_qc_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->nullable()->constrained('lab_equipments')->onDelete('set null');
            $table->foreignId('test_id')->nullable()->constrained('lab_test_catalogs')->onDelete('set null');
            $table->foreignId('performed_by')->constrained('users')->onDelete('restrict');

            // QC Information
            $table->string('qc_lot_number')->nullable();
            $table->date('qc_date');

            // Control Results
            $table->json('control_results'); // JSON array of control measurements
            $table->string('control_level'); // Low, Normal, High
            $table->string('target_value');
            $table->string('acceptable_range_min');
            $table->string('acceptable_range_max');

            // QC Status
            $table->enum('qc_status', ['in_control', 'out_of_control', 'warning', 'repeat'])->default('in_control');
            $table->text('corrective_actions')->nullable();

            // Westgard Rules
            $table->json('westgard_rules_violated')->nullable();
            $table->text('rule_interpretation')->nullable();

            // Approval
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->datetime('reviewed_at')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('qc_date');
            $table->index('qc_status');
            $table->index('equipment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_qc_logs');
        Schema::dropIfExists('lab_reports');
        Schema::dropIfExists('lab_result_details');
        Schema::dropIfExists('lab_samples');
        Schema::dropIfExists('lab_equipment_calibrations');
        Schema::dropIfExists('lab_equipments');
        Schema::dropIfExists('lab_test_catalogs');
    }
};
