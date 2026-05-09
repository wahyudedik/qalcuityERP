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
        Schema::dropIfExists('or_utilization_logs');
        Schema::dropIfExists('equipment_maintenance_logs');
        Schema::dropIfExists('surgery_teams');
        Schema::dropIfExists('surgery_schedules');
        Schema::dropIfExists('medical_equipments');
        Schema::dropIfExists('operating_rooms');

        // Operating Rooms
        if (! Schema::hasTable('operating_rooms')) {
            Schema::create('operating_rooms', function (Blueprint $table) {
                $table->id();
                $table->string('room_number')->unique(); // OR-001, OR-002, etc.
                $table->string('room_name');
                $table->enum('type', ['general', 'cardiac', 'orthopedic', 'neurological', 'pediatric', 'emergency', 'hybrid']);
                $table->integer('capacity')->default(1); // Number of patients

                // Equipment & Facilities
                $table->json('equipment')->nullable(); // Array of available equipment
                $table->json('specializations')->nullable(); // Array of specialties
                $table->boolean('has_laminar_flow')->default(false);
                $table->boolean('has_hybrid_imaging')->default(false);

                // Availability Schedule
                $table->json('availability_schedule')->nullable(); // JSON schedule
                $table->time('start_time')->default('07:00:00');
                $table->time('end_time')->default('17:00:00');
                $table->boolean('is_available_247')->default(false);

                // Status
                $table->enum('status', ['available', 'occupied', 'cleaning', 'maintenance', 'closed'])->default('available');
                $table->boolean('is_active')->default(true);

                // Location
                $table->string('floor')->nullable();
                $table->string('wing')->nullable();
                $table->string('department')->default('Surgery');

                // Notes
                $table->text('specifications')->nullable();
                $table->text('notes')->nullable();

                $table->timestamps();

                $table->index('room_number');
                $table->index('type');
                $table->index('status');
                $table->index('is_active');
            });
        }

        // Medical Equipment
        if (! Schema::hasTable('medical_equipments')) {
            Schema::create('medical_equipments', function (Blueprint $table) {
                $table->id();
                $table->string('equipment_code')->unique(); // EQ-XXXX
                $table->string('equipment_name');
                $table->string('category'); // Surgical, Diagnostic, Monitoring, etc.
                $table->string('subcategory')->nullable();

                // Details
                $table->string('manufacturer')->nullable();
                $table->string('model_number')->nullable();
                $table->string('serial_number')->nullable();

                // Location & Assignment
                $table->unsignedBigInteger('operating_room_id')->nullable(); // FK to operating_rooms
                $table->string('location')->nullable(); // Room, department
                $table->string('department')->nullable();

                // Status & Condition
                $table->enum('status', ['available', 'in_use', 'maintenance', 'repair', 'retired', 'missing'])->default('available');
                $table->enum('condition', ['excellent', 'good', 'fair', 'poor', 'critical'])->default('good');

                // Maintenance
                $table->date('purchase_date')->nullable();
                $table->date('installation_date')->nullable();
                $table->date('last_maintenance')->nullable();
                $table->date('next_maintenance')->nullable();
                $table->integer('maintenance_interval_days')->default(90);
                $table->text('maintenance_notes')->nullable();

                // Financial
                $table->decimal('purchase_price', 12, 2)->nullable();
                $table->date('warranty_until')->nullable();

                // Compliance
                $table->string('certification_number')->nullable();
                $table->date('certification_expiry')->nullable();
                $table->boolean('requires_calibration')->default(false);
                $table->date('last_calibration')->nullable();
                $table->date('next_calibration')->nullable();

                $table->boolean('is_active')->default(true);
                $table->text('specifications')->nullable();
                $table->text('notes')->nullable();

                $table->timestamps();

                $table->index('equipment_code');
                $table->index('category');
                $table->index('status');
                $table->index('next_maintenance');
            });
        }

        // Surgery Schedules
        if (! Schema::hasTable('surgery_schedules')) {
            Schema::create('surgery_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained()->onDelete('cascade');
                $table->unsignedBigInteger('surgeon_id'); // FK to doctors
                $table->unsignedBigInteger('operating_room_id'); // FK to operating_rooms
                $table->unsignedBigInteger('admission_id')->nullable(); // FK to admissions (will be created separately)

                // Surgery Information
                $table->string('surgery_number')->unique(); // SUR-YYYYMMDD-XXXX
                $table->date('scheduled_date');
                $table->time('scheduled_start_time');
                $table->time('scheduled_end_time');
                $table->time('actual_start_time')->nullable();
                $table->time('actual_end_time')->nullable();
                $table->integer('estimated_duration')->default(0); // minutes
                $table->integer('actual_duration')->nullable(); // minutes

                // Procedure Details
                $table->string('procedure_name');
                $table->string('procedure_code')->nullable(); // CPT/ICD-9-CM
                $table->text('procedure_description')->nullable();
                $table->string('surgery_type')->nullable(); // Elective, Emergency, Urgent
                $table->string('icd10_code')->nullable();
                $table->text('pre_operative_diagnosis')->nullable();
                $table->text('post_operative_diagnosis')->nullable();

                // Team & Anesthesia
                $table->unsignedBigInteger('anesthesiologist_id')->nullable(); // FK to doctors
                $table->string('anesthesia_type')->nullable(); // General, Regional, Local
                $table->text('anesthesia_notes')->nullable();

                // Status
                $table->enum('status', ['scheduled', 'pre_op', 'in_progress', 'completed', 'cancelled', 'postponed'])
                    ->default('scheduled');

                // Priority
                $table->enum('priority', ['elective', 'urgent', 'emergency'])->default('elective');

                // Clinical Notes
                $table->text('preoperative_notes')->nullable();
                $table->text('intraoperative_notes')->nullable();
                $table->text('postoperative_notes')->nullable();
                $table->text('complications')->nullable();
                $table->text('surgeon_notes')->nullable();

                // Outcomes
                $table->enum('outcome', ['successful', 'partial', 'failed', 'converted'])->nullable();
                $table->integer('blood_loss_ml')->nullable();
                $table->text('implants_used')->nullable(); // JSON

                // Cancellation/Postponement
                $table->text('cancellation_reason')->nullable();
                $table->text('postponement_reason')->nullable();
                $table->datetime('rescheduled_to')->nullable();

                $table->timestamps();

                $table->index('surgery_number');
                $table->index('patient_id');
                $table->index('scheduled_date');
                $table->index('status');
                $table->index(['scheduled_date', 'operating_room_id']);
                $table->index(['scheduled_date', 'surgeon_id']);
            });
        }

        // Surgery Teams
        if (! Schema::hasTable('surgery_teams')) {
            Schema::create('surgery_teams', function (Blueprint $table) {
                $table->id();
                $table->foreignId('surgery_id')->constrained('surgery_schedules')->onDelete('cascade');
                $table->unsignedBigInteger('staff_id'); // FK to users/doctors
                $table->string('staff_type')->default('staff'); // doctor, nurse, technician

                // Role in Surgery
                $table->enum('role', [
                    'lead_surgeon',
                    'assistant_surgeon',
                    'anesthesiologist',
                    'scrub_nurse',
                    'circulating_nurse',
                    'perfusionist',
                    'radiologist',
                    'surgical_technician',
                    'observer',
                ]);

                // Assignment
                $table->time('check_in_time')->nullable();
                $table->time('check_out_time')->nullable();

                // Performance
                $table->text('notes')->nullable();
                $table->text('performance_notes')->nullable();
                $table->tinyInteger('performance_rating')->nullable()->unsigned(); // 1-5

                $table->timestamps();

                $table->index('surgery_id');
                $table->index('staff_id');
                $table->index('role');
                $table->unique(['surgery_id', 'staff_id', 'role']);
            });
        }

        // Equipment Maintenance Logs
        if (! Schema::hasTable('equipment_maintenance_logs')) {
            Schema::create('equipment_maintenance_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('equipment_id')->constrained('medical_equipments')->onDelete('cascade');
                $table->foreignId('performed_by')->constrained('users')->onDelete('restrict');

                // Maintenance Details
                $table->date('maintenance_date');
                $table->enum('maintenance_type', ['preventive', 'corrective', 'calibration', 'inspection', 'repair']);
                $table->text('description');
                $table->text('work_performed')->nullable();

                // Findings & Actions
                $table->text('findings')->nullable();
                $table->text('actions_taken')->nullable();
                $table->text('parts_replaced')->nullable(); // JSON

                // Status & Cost
                $table->enum('status', ['completed', 'in_progress', 'pending', 'failed'])->default('completed');
                $table->decimal('cost', 10, 2)->default(0);
                $table->integer('downtime_hours')->default(0);

                // Next Maintenance
                $table->date('next_maintenance_date')->nullable();

                // Documentation
                $table->string('work_order_number')->nullable();
                $table->string('technician_name')->nullable();
                $table->text('recommendations')->nullable();

                $table->timestamps();

                $table->index('equipment_id');
                $table->index('maintenance_date');
                $table->index('maintenance_type');
            });
        }

        // OR Utilization Logs
        if (! Schema::hasTable('or_utilization_logs')) {
            Schema::create('or_utilization_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('operating_room_id')->constrained('operating_rooms')->onDelete('cascade');
                $table->foreignId('surgery_id')->nullable()->constrained('surgery_schedules')->onDelete('set null');

                // Time Tracking
                $table->date('log_date');
                $table->time('start_time');
                $table->time('end_time');
                $table->integer('duration_minutes');

                // Utilization Type
                $table->enum('utilization_type', ['surgery', 'cleaning', 'maintenance', 'setup', 'breakdown', 'idle']);
                $table->string('case_number')->nullable();

                // Efficiency Metrics
                $table->integer('turnover_time')->nullable(); // minutes between cases
                $table->integer('setup_time')->nullable(); // minutes
                $table->integer('cleaning_time')->nullable(); // minutes

                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('operating_room_id');
                $table->index('log_date');
                $table->index('utilization_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('or_utilization_logs');
        Schema::dropIfExists('equipment_maintenance_logs');
        Schema::dropIfExists('surgery_teams');
        Schema::dropIfExists('surgery_schedules');
        Schema::dropIfExists('medical_equipments');
        Schema::dropIfExists('operating_rooms');
    }
};
