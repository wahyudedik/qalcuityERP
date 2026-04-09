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
        // Wards table (jika belum ada)
        if (!Schema::hasTable('wards')) {
            Schema::create('wards', function (Blueprint $table) {
                $table->id();
                $table->string('ward_code')->unique();
                $table->string('ward_name');
                $table->enum('ward_type', ['general', 'icu', 'nicu', 'maternity', 'pediatric', 'surgery', 'isolation', 'vip'])->default('general');
                $table->integer('floor')->default(1);
                $table->integer('capacity')->default(0);
                $table->integer('occupied_beds')->default(0);
                $table->json('facilities')->nullable();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('head_nurse_id')->nullable();
                $table->unsignedBigInteger('supervisor_doctor_id')->nullable();
                $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
                $table->boolean('is_restricted')->default(false);
                $table->boolean('has_nurse_station')->default(true);
                $table->string('phone_extension')->nullable();
                $table->string('email')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['status', 'ward_type']);
            });
        }

        // Beds table
        Schema::dropIfExists('beds'); // Drop if exists from partial migration
        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ward_id'); // FK to wards
            $table->string('bed_number'); // BED-001, BED-002
            $table->string('bed_name')->nullable();
            $table->enum('bed_type', ['standard', 'vip', 'vvip', 'icu', 'nicu', 'isolation', 'maternity', 'pediatric']);
            $table->decimal('daily_rate', 12, 2)->default(0);
            $table->enum('status', ['available', 'occupied', 'maintenance', 'reserved', 'blocked'])->default('available');
            $table->string('current_patient_id')->nullable();
            $table->string('current_admission_id')->nullable();
            $table->timestamp('last_cleaned_at')->nullable();
            $table->string('cleaned_by')->nullable();
            $table->json('facilities')->nullable(); // TV, AC, WiFi, etc.
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['ward_id', 'bed_number']);
            $table->index(['status', 'bed_type']);
        });

        // Admissions table
        Schema::dropIfExists('admissions'); // Drop if exists from partial migration
        Schema::create('admissions', function (Blueprint $table) {
            $table->id();
            $table->string('admission_number')->unique(); // ADM-YYYYMMDD-XXXX
            $table->foreignId('patient_id')->constrained()->onDelete('restrict');
            $table->foreignId('admitting_doctor_id')->constrained('doctors')->onDelete('restrict');
            $table->unsignedBigInteger('ward_id')->nullable(); // FK to wards
            $table->unsignedBigInteger('bed_id')->nullable(); // FK to beds
            $table->foreignId('referred_by_visit_id')->nullable()->constrained('patient_visits')->onDelete('set null');

            $table->enum('admission_type', ['emergency', 'elective', 'referral', 'maternity', 'surgery', 'observation']);
            $table->enum('admission_category', ['class_1', 'class_2', 'class_3', 'vip', 'vvip', 'icu']);
            $table->timestamp('admission_date');
            $table->timestamp('expected_discharge_date')->nullable();
            $table->timestamp('actual_discharge_date')->nullable();
            $table->string('discharge_diagnosis')->nullable();
            $table->text('discharge_summary')->nullable();
            $table->enum('discharge_status', ['recovered', 'improved', 'unchanged', 'worsened', 'died', 'ama'])->nullable(); // Against Medical Advice
            $table->enum('discharge_type', ['normal', 'transfer', 'referral', 'ama'])->nullable();

            $table->string('admission_diagnosis')->nullable();
            $table->string('icd10_code')->nullable();
            $table->text('chief_complaint')->nullable();
            $table->text('admission_notes')->nullable();
            $table->text('treatment_plan')->nullable();
            $table->text('special_instructions')->nullable();

            $table->enum('status', ['pending', 'active', 'discharged', 'transferred', 'ama', 'deceased'])->default('pending');
            $table->boolean('requires_care_plan')->default(false);
            $table->boolean('requires_surgery')->default(false);
            $table->boolean('is_isolation')->default(false);

            $table->decimal('estimated_cost', 15, 2)->default(0);
            $table->decimal('actual_cost', 15, 2)->default(0);
            $table->decimal('deposit_amount', 15, 2)->default(0);

            $table->string('guarantor_name')->nullable();
            $table->string('guarantor_phone')->nullable();
            $table->string('guarantor_relationship')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'admission_date']);
            $table->index(['patient_id', 'status']);
        });

        // Queue Management table
        Schema::dropIfExists('queue_managements'); // Drop if exists
        Schema::create('queue_managements', function (Blueprint $table) {
            $table->id();
            $table->string('queue_number')->unique(); // Q-YYYYMMDD-XXXX
            $table->string('token_number'); // Display token: A001, B002
            $table->enum('queue_type', ['outpatient', 'specialist', 'pharmacy', 'laboratory', 'radiology', 'billing', 'registration']);
            $table->unsignedBigInteger('outpatient_visit_id')->nullable(); // FK to outpatient_visits
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('doctor_id')->nullable(); // FK to doctors
            $table->unsignedBigInteger('department_id')->nullable(); // FK to departments
            $table->unsignedBigInteger('queue_setting_id')->nullable(); // FK to queue_settings

            $table->enum('status', ['waiting', 'called', 'serving', 'completed', 'skipped', 'cancelled', 'no_show'])->default('waiting');
            $table->integer('queue_position');
            $table->integer('estimated_wait_minutes')->default(0);
            $table->timestamp('registered_at');
            $table->timestamp('called_at')->nullable();
            $table->timestamp('serving_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('actual_wait_minutes')->nullable();
            $table->integer('service_duration_minutes')->nullable();

            $table->string('priority')->default('normal'); // normal, priority, elderly, pregnant, disability
            $table->integer('priority_position')->default(0);
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['queue_type', 'status', 'registered_at']);
            $table->index(['doctor_id', 'status']);
        });

        // Triage Assessments table
        Schema::dropIfExists('triage_assessments');
        Schema::create('triage_assessments', function (Blueprint $table) {
            $table->id();
            $table->string('triage_number')->unique(); // TRI-YYYYMMDD-XXXX
            $table->foreignId('patient_id')->constrained()->onDelete('restrict');
            $table->foreignId('patient_visit_id')->nullable()->constrained('patient_visits')->onDelete('set null');
            $table->foreignId('assessed_by')->constrained('users')->onDelete('restrict');
            $table->timestamp('assessment_date');

            // Emergency Level
            $table->enum('triage_level', ['red', 'yellow', 'green', 'black'])->comment('Red=Critical, Yellow=Urgent, Green=Non-urgent, Black=Deceased');
            $table->string('triage_level_name')->nullable(); // Emergency, Urgent, Non-urgent
            $table->integer('triage_score')->nullable(); // 1-5 scale

            // Vital Signs
            $table->decimal('temperature', 4, 1)->nullable(); // Celsius
            $table->integer('heart_rate')->nullable(); // BPM
            $table->integer('respiratory_rate')->nullable(); // Breaths/min
            $table->integer('systolic_bp')->nullable(); // mmHg
            $table->integer('diastolic_bp')->nullable(); // mmHg
            $table->integer('spo2')->nullable(); // Oxygen saturation %
            $table->integer('pain_scale')->nullable(); // 0-10 scale
            $table->integer('gcs_score')->nullable(); // Glasgow Coma Scale 3-15

            // Assessment
            $table->string('chief_complaint');
            $table->text('assessment_notes')->nullable();
            $table->text('initial_treatment')->nullable();
            $table->text('recommendations')->nullable();
            $table->boolean('requires_immediate_intervention')->default(false);
            $table->boolean('requires_resuscitation')->default(false);
            $table->boolean('requires_isolation')->default(false);

            // Disposition
            $table->enum('disposition', ['discharged', 'admitted', 'transferred', 'observation', 'surgery'])->nullable();
            $table->unsignedBigInteger('admission_id')->nullable(); // FK to admissions
            $table->foreignId('assigned_doctor_id')->nullable()->constrained('doctors')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['triage_level', 'assessment_date']);
            $table->index(['patient_id', 'assessment_date']);
        });

        // Critical Alerts table
        Schema::dropIfExists('critical_alerts');
        Schema::create('critical_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('alert_number')->unique(); // ALERT-YYYYMMDD-XXXX
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('patient_visit_id')->nullable(); // FK to patient_visits
            $table->unsignedBigInteger('admission_id')->nullable(); // FK to admissions
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');

            $table->enum('alert_type', ['critical_lab', 'critical_vitals', 'allergy', 'medication_error', 'cardiac_arrest', 'respiratory_distress', 'sepsis', 'stroke', 'trauma', 'other']);
            $table->enum('severity', ['low', 'medium', 'high', 'critical', 'life_threatening']);
            $table->string('alert_title');
            $table->text('alert_description');
            $table->text('clinical_findings')->nullable();
            $table->text('recommended_action')->nullable();
            $table->text('intervention_taken')->nullable();

            $table->enum('status', ['new', 'acknowledged', 'in_progress', 'resolved', 'false_alarm'])->default('new');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->string('resolved_by')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->integer('response_time_minutes')->nullable();

            $table->boolean('notification_sent')->default(false);
            $table->boolean('requires_escalation')->default(false);
            $table->timestamp('escalated_at')->nullable();
            $table->string('escalated_to')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['severity', 'status', 'created_at']);
            $table->index(['alert_type', 'status']);
        });

        // Pharmacy Inventory table
        Schema::dropIfExists('pharmacy_inventories');
        Schema::create('pharmacy_inventories', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique(); // PHARM-XXXX
            $table->string('item_name');
            $table->string('generic_name')->nullable();
            $table->string('brand_name')->nullable();
            $table->enum('item_type', ['medication', 'supplement', 'medical_supply', 'vaccine', 'herbal']);
            $table->enum('medication_type', ['tablet', 'capsule', 'syrup', 'injection', 'topical', 'inhaler', 'drop', 'suppository', 'powder', 'other'])->nullable();
            $table->string('drug_class')->nullable(); // Antibiotic, Analgesic, etc.
            $table->string('therapeutic_category')->nullable();

            // Stock Management
            $table->integer('stock_quantity')->default(0);
            $table->integer('minimum_stock')->default(0); // Reorder point
            $table->integer('maximum_stock')->nullable();
            $table->integer('reorder_quantity')->nullable();
            $table->string('unit_of_measure'); // Tablet, Bottle, Box, Vial, etc.
            $table->integer('stock_in_transit')->default(0);
            $table->integer('reserved_stock')->default(0); // For prescriptions
            $table->integer('available_stock')->storedAs('stock_quantity - reserved_stock');

            // Pricing
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->decimal('markup_percentage', 5, 2)->nullable();

            // Supplier
            $table->string('supplier_name')->nullable();
            $table->string('supplier_contact')->nullable();
            $table->date('last_order_date')->nullable();

            // Expiry Tracking
            $table->date('expiry_date')->nullable();
            $table->boolean('has_expiry')->default(false);
            $table->integer('expiry_alert_days')->default(90);
            $table->boolean('expiry_alert_sent')->default(false);

            // Storage
            $table->enum('storage_requirement', ['room_temp', 'refrigerated', 'frozen', 'controlled_substance'])->default('room_temp');
            $table->string('storage_location')->nullable(); // Rack, Shelf
            $table->string('batch_number')->nullable();

            // Regulation
            $table->boolean('requires_prescription')->default(true);
            $table->boolean('controlled_substance')->default(false);
            $table->string('bpom_number')->nullable(); // Indonesia FDA
            $table->string('registration_number')->nullable();

            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['item_type', 'is_active']);
            $table->index(['stock_quantity', 'minimum_stock']);
            $table->index(['expiry_date', 'has_expiry']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacy_inventories');
        Schema::dropIfExists('critical_alerts');
        Schema::dropIfExists('triage_assessments');
        Schema::dropIfExists('queue_managements');
        Schema::dropIfExists('admissions');
        Schema::dropIfExists('beds');
        Schema::dropIfExists('wards');
    }
};
