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
        if (Schema::hasTable('teleconsultation_recordings')) {
            Schema::dropIfExists('teleconsultation_recordings');
        }
        if (Schema::hasTable('telemedicine_prescriptions')) {
            Schema::dropIfExists('telemedicine_prescriptions');
        }
        if (Schema::hasTable('teleconsultation_payments')) {
            Schema::dropIfExists('teleconsultation_payments');
        }
        if (Schema::hasTable('teleconsultation_feedbacks')) {
            Schema::dropIfExists('teleconsultation_feedbacks');
        }
        if (Schema::hasTable('surgery_schedules')) {
            Schema::dropIfExists('surgery_schedules');
        }
        if (Schema::hasTable('surgery_teams')) {
            Schema::dropIfExists('surgery_teams');
        }
        if (Schema::hasTable('medical_equipment')) {
            Schema::dropIfExists('medical_equipment');
        }
        if (Schema::hasTable('equipment_maintenance_logs')) {
            Schema::dropIfExists('equipment_maintenance_logs');
        }
        if (Schema::hasTable('or_utilization_logs')) {
            Schema::dropIfExists('or_utilization_logs');
        }
        if (Schema::hasTable('medical_supply_transactions')) {
            Schema::dropIfExists('medical_supply_transactions');
        }
        if (Schema::hasTable('medical_supply_requests')) {
            Schema::dropIfExists('medical_supply_requests');
        }
        if (Schema::hasTable('medical_supply_request_items')) {
            Schema::dropIfExists('medical_supply_request_items');
        }
        if (Schema::hasTable('sterilization_logs')) {
            Schema::dropIfExists('sterilization_logs');
        }
        if (Schema::hasTable('medical_waste_logs')) {
            Schema::dropIfExists('medical_waste_logs');
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Teleconsultation Recordings table
        if (!Schema::hasTable('teleconsultation_recordings')) {
            Schema::create('teleconsultation_recordings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('teleconsultation_id'); // FK to teleconsultations
                $table->foreignId('patient_id')->constrained()->onDelete('restrict');
                $table->unsignedBigInteger('doctor_id')->nullable(); // FK to doctors
                $table->string('recording_url');
                $table->string('file_path')->nullable();
                $table->bigInteger('file_size')->nullable(); // bytes
                $table->integer('duration_seconds')->nullable();
                $table->string('recording_format')->default('mp4');
                $table->timestamp('recording_started_at');
                $table->timestamp('recording_ended_at')->nullable();
                $table->boolean('is_encrypted')->default(true);
                $table->integer('retention_days')->default(90);
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
    
                $table->index(['teleconsultation_id', 'created_at']);
            });
        }

        // Telemedicine Prescriptions table
        if (!Schema::hasTable('telemedicine_prescriptions')) {
            Schema::create('telemedicine_prescriptions', function (Blueprint $table) {
                $table->id();
                $table->string('prescription_number')->unique(); // TELE-RX-YYYYMMDD-XXXX
                $table->unsignedBigInteger('teleconsultation_id'); // FK to teleconsultations
                $table->foreignId('patient_id')->constrained()->onDelete('restrict');
                $table->unsignedBigInteger('doctor_id'); // FK to doctors
                $table->unsignedBigInteger('pharmacy_id')->nullable(); // FK to pharmacies
                $table->text('prescription_details'); // JSON or structured text
                $table->text('diagnosis')->nullable();
                $table->text('instructions')->nullable();
                $table->text('special_instructions')->nullable();
                $table->integer('validity_days')->default(30);
                $table->timestamp('valid_until')->nullable();
                $table->enum('status', ['draft', 'sent', 'dispensed', 'expired', 'cancelled'])->default('draft');
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('dispensed_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
    
                $table->index(['status', 'created_at']);
                $table->index(['patient_id', 'created_at']);
            });
        }

        // Teleconsultation Payments table
        if (!Schema::hasTable('teleconsultation_payments')) {
            Schema::create('teleconsultation_payments', function (Blueprint $table) {
                $table->id();
                $table->string('payment_number')->unique(); // PAY-TELE-YYYYMMDD-XXXX
                $table->unsignedBigInteger('teleconsultation_id'); // FK to teleconsultations
                $table->foreignId('patient_id')->constrained()->onDelete('restrict');
                $table->decimal('consultation_fee', 12, 2);
                $table->decimal('platform_fee', 12, 2)->default(0);
                $table->decimal('discount_amount', 12, 2)->default(0);
                $table->decimal('total_amount', 12, 2);
                $table->decimal('amount_paid', 12, 2)->default(0);
                $table->decimal('balance_due', 12, 2)->default(0);
                $table->enum('payment_method', ['credit_card', 'debit_card', 'bank_transfer', 'ewallet', 'insurance', 'cash'])->nullable();
                $table->string('payment_gateway')->nullable();
                $table->string('transaction_id')->nullable();
                $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded', 'partially_refunded'])->default('pending');
                $table->timestamp('paid_at')->nullable();
                $table->text('payment_notes')->nullable();
                $table->timestamps();
    
                $table->index(['payment_status', 'created_at']);
            });
        }

        // Teleconsultation Feedback table
        if (!Schema::hasTable('teleconsultation_feedbacks')) {
            Schema::create('teleconsultation_feedbacks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('teleconsultation_id'); // FK to teleconsultations
                $table->foreignId('patient_id')->constrained()->onDelete('restrict');
                $table->unsignedBigInteger('doctor_id'); // FK to doctors
                $table->integer('overall_rating'); // 1-5
                $table->integer('doctor_rating')->nullable(); // 1-5
                $table->integer('video_quality_rating')->nullable(); // 1-5
                $table->integer('audio_quality_rating')->nullable(); // 1-5
                $table->integer('app_ease_rating')->nullable(); // 1-5
                $table->text('comments')->nullable();
                $table->text('suggestions')->nullable();
                $table->boolean('would_recommend')->default(true);
                $table->enum('consultation_outcome', ['resolved', 'partially_resolved', 'not_resolved', 'needs_followup'])->nullable();
                $table->timestamps();
    
                $table->unique('teleconsultation_id'); // One feedback per consultation
                $table->index(['overall_rating', 'created_at']);
            });
        }

        // Surgery Schedules table
        if (!Schema::hasTable('surgery_schedules')) {
            Schema::create('surgery_schedules', function (Blueprint $table) {
                $table->id();
                $table->string('schedule_number')->unique(); // SURG-YYYYMMDD-XXXX
                $table->foreignId('patient_id')->constrained()->onDelete('restrict');
                $table->unsignedBigInteger('admission_id')->nullable(); // FK to admissions
                $table->unsignedBigInteger('primary_surgeon_id'); // FK to doctors
                $table->unsignedBigInteger('assistant_surgeon_id')->nullable(); // FK to doctors
                $table->unsignedBigInteger('anesthesiologist_id')->nullable(); // FK to doctors
                $table->unsignedBigInteger('operating_room_id')->nullable(); // FK to medical_equipment (OR)
                $table->date('surgery_date');
                $table->time('scheduled_start_time');
                $table->time('scheduled_end_time')->nullable();
                $table->time('actual_start_time')->nullable();
                $table->time('actual_end_time')->nullable();
                $table->integer('estimated_duration_minutes')->nullable();
                $table->integer('actual_duration_minutes')->nullable();
                $table->string('surgery_name');
                $table->text('surgery_description')->nullable();
                $table->string('icd9_code')->nullable(); // Procedure code
                $table->string('icd10_code')->nullable(); // Diagnosis code
                $table->enum('urgency', ['elective', 'urgent', 'emergency'])->default('elective');
                $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled', 'postponed'])->default('scheduled');
                $table->text('pre_op_diagnosis')->nullable();
                $table->text('post_op_diagnosis')->nullable();
                $table->text('surgery_notes')->nullable();
                $table->text('complications')->nullable();
                $table->timestamps();
                $table->softDeletes();
    
                $table->index(['status', 'surgery_date']);
                $table->index(['primary_surgeon_id', 'surgery_date']);
            });
        }

        // Surgery Teams table
        if (!Schema::hasTable('surgery_teams')) {
            Schema::create('surgery_teams', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('surgery_schedule_id'); // FK to surgery_schedules
                $table->unsignedBigInteger('doctor_id'); // FK to doctors
                $table->enum('role', ['primary_surgeon', 'assistant_surgeon', 'anesthesiologist', 'nurse', 'technician', 'observer']);
                $table->text('responsibilities')->nullable();
                $table->timestamp('check_in_time')->nullable();
                $table->timestamp('check_out_time')->nullable();
                $table->timestamps();
    
                $table->unique(['surgery_schedule_id', 'doctor_id', 'role']);
            });
        }

        // Medical Equipment table
        if (!Schema::hasTable('medical_equipment')) {
            Schema::create('medical_equipment', function (Blueprint $table) {
                $table->id();
                $table->string('equipment_code')->unique(); // EQP-XXXX
                $table->string('equipment_name');
                $table->enum('equipment_type', ['diagnostic', 'therapeutic', 'surgical', 'monitoring', 'life_support', 'laboratory', 'radiology', 'other']);
                $table->string('category')->nullable();
                $table->string('manufacturer')->nullable();
                $table->string('model_number')->nullable();
                $table->string('serial_number')->nullable();
                $table->date('purchase_date')->nullable();
                $table->decimal('purchase_cost', 15, 2)->nullable();
                $table->date('warranty_expiry')->nullable();
                $table->enum('status', ['available', 'in_use', 'maintenance', 'out_of_order', 'retired'])->default('available');
                $table->string('location')->nullable(); // Room, Ward, Department
                $table->unsignedBigInteger('department_id')->nullable(); // FK to departments
                $table->date('next_maintenance_date')->nullable();
                $table->date('last_maintenance_date')->nullable();
                $table->date('calibration_date')->nullable();
                $table->date('next_calibration_date')->nullable();
                $table->boolean('requires_calibration')->default(false);
                $table->integer('maintenance_interval_days')->nullable();
                $table->text('specifications')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
    
                $table->index(['status', 'equipment_type']);
                $table->index(['next_maintenance_date', 'status']);
            });
        }

        // Equipment Maintenance Logs table
        if (!Schema::hasTable('equipment_maintenance_logs')) {
            Schema::create('equipment_maintenance_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('equipment_id'); // FK to medical_equipment
                $table->enum('maintenance_type', ['preventive', 'corrective', 'calibration', 'inspection', 'repair']);
                $table->date('maintenance_date');
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->unsignedBigInteger('technician_id')->nullable(); // FK to users
                $table->text('work_performed');
                $table->text('parts_replaced')->nullable();
                $table->decimal('parts_cost', 12, 2)->default(0);
                $table->decimal('labor_cost', 12, 2)->default(0);
                $table->decimal('total_cost', 12, 2)->default(0);
                $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
                $table->text('findings')->nullable();
                $table->text('recommendations')->nullable();
                $table->date('next_maintenance_date')->nullable();
                $table->timestamps();
    
                $table->index(['equipment_id', 'maintenance_date']);
                $table->index(['status', 'maintenance_date']);
            });
        }

        // OR Utilization Logs table
        if (!Schema::hasTable('or_utilization_logs')) {
            Schema::create('or_utilization_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('operating_room_id'); // FK to medical_equipment (OR)
                $table->unsignedBigInteger('surgery_schedule_id')->nullable(); // FK to surgery_schedules
                $table->date('log_date');
                $table->time('room_available_from');
                $table->time('room_available_until');
                $table->time('surgery_start')->nullable();
                $table->time('surgery_end')->nullable();
                $table->integer('setup_time_minutes')->nullable();
                $table->integer('cleaning_time_minutes')->nullable();
                $table->integer('idle_time_minutes')->nullable();
                $table->integer('total_available_minutes');
                $table->integer('total_used_minutes')->default(0);
                $table->decimal('utilization_percentage', 5, 2)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->index(['operating_room_id', 'log_date']);
            });
        }

        // Medical Supply Transactions table
        if (!Schema::hasTable('medical_supply_transactions')) {
            Schema::create('medical_supply_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('transaction_number')->unique(); // TRX-MED-YYYYMMDD-XXXX
                $table->foreignId('supply_id')->constrained('medical_supplies')->onDelete('restrict');
                $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
                $table->date('transaction_date');
                $table->enum('transaction_type', ['receipt', 'issue', 'return', 'adjustment', 'transfer', 'expiry', 'damage']);
                $table->integer('quantity');
                $table->integer('previous_quantity');
                $table->integer('new_quantity');
                $table->string('reference_number')->nullable(); // PO, Request, etc.
                $table->unsignedBigInteger('from_department_id')->nullable(); // FK to departments
                $table->unsignedBigInteger('to_department_id')->nullable(); // FK to departments
                $table->string('batch_number')->nullable();
                $table->date('expiry_date')->nullable();
                $table->decimal('unit_cost', 12, 2)->nullable();
                $table->decimal('total_cost', 12, 2)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->index(['supply_id', 'transaction_date']);
                $table->index(['transaction_type', 'transaction_date'], 'med_supply_trx_type_date_idx');
            });
        }

        // Medical Supply Requests table
        if (!Schema::hasTable('medical_supply_requests')) {
            Schema::create('medical_supply_requests', function (Blueprint $table) {
                $table->id();
                $table->string('request_number')->unique(); // REQ-MED-YYYYMMDD-XXXX
                $table->foreignId('requested_by')->constrained('users')->onDelete('restrict');
                $table->unsignedBigInteger('department_id')->nullable(); // FK to departments
                $table->enum('urgency', ['low', 'normal', 'urgent', 'critical'])->default('normal');
                $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'fulfilled', 'cancelled'])->default('draft');
                $table->date('request_date');
                $table->date('required_by_date')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable(); // FK to users
                $table->timestamp('approved_at')->nullable();
                $table->text('approval_notes')->nullable();
                $table->unsignedBigInteger('fulfilled_by')->nullable(); // FK to users
                $table->timestamp('fulfilled_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
    
                $table->index(['status', 'request_date']);
                $table->index(['urgency', 'status']);
            });
        }

        // Medical Supply Request Items table
        if (!Schema::hasTable('medical_supply_request_items')) {
            Schema::create('medical_supply_request_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('request_id')->constrained('medical_supply_requests')->onDelete('cascade');
                $table->foreignId('supply_id')->constrained('medical_supplies')->onDelete('restrict');
                $table->integer('requested_quantity');
                $table->integer('approved_quantity')->nullable();
                $table->integer('fulfilled_quantity')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->index(['request_id', 'supply_id']);
            });
        }

        // Sterilization Logs table
        if (!Schema::hasTable('sterilization_logs')) {
            Schema::create('sterilization_logs', function (Blueprint $table) {
                $table->id();
                $table->string('sterilization_number')->unique(); // STER-YYYYMMDD-XXXX
                $table->unsignedBigInteger('equipment_id')->nullable(); // FK to medical_equipment
                $table->string('equipment_name')->nullable(); // If not in equipment table
                $table->enum('sterilization_method', ['autoclave', 'ethylene_oxide', 'hydrogen_peroxide', 'steam', 'dry_heat', 'chemical', 'radiation', 'uv']);
                $table->date('sterilization_date');
                $table->time('start_time');
                $table->time('end_time')->nullable();
                $table->integer('duration_minutes')->nullable();
                $table->decimal('temperature', 5, 2)->nullable(); // Celsius
                $table->decimal('pressure', 5, 2)->nullable(); // PSI or bar
                $table->string('cycle_number')->nullable();
                $table->string('batch_number')->nullable();
                $table->string('load_description')->nullable();
                $table->integer('items_count')->nullable();
                $table->unsignedBigInteger('performed_by')->constrained('users')->onDelete('restrict');
                $table->unsignedBigInteger('validated_by')->nullable(); // FK to users
                $table->enum('status', ['in_progress', 'completed', 'failed', 'validated'])->default('in_progress');
                $table->text('biological_indicator_result')->nullable();
                $table->text('chemical_indicator_result')->nullable();
                $table->boolean('passed_validation')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->index(['sterilization_date', 'status']);
                $table->index(['equipment_id', 'sterilization_date']);
            });
        }

        // Medical Waste Logs table
        if (!Schema::hasTable('medical_waste_logs')) {
            Schema::create('medical_waste_logs', function (Blueprint $table) {
                $table->id();
                $table->string('waste_number')->unique(); // WASTE-YYYYMMDD-XXXX
                $table->enum('waste_type', ['infectious', 'pathological', 'sharps', 'pharmaceutical', 'chemical', 'radioactive', 'general', 'recyclable']);
                $table->string('waste_description');
                $table->decimal('weight_kg', 8, 2);
                $table->integer('container_count')->default(1);
                $table->string('container_type')->nullable(); // Bag, Box, Drum, etc.
                $table->string('color_code')->nullable(); // Red, Yellow, Black, etc.
                $table->unsignedBigInteger('generated_by_department')->nullable(); // FK to departments
                $table->foreignId('recorded_by')->constrained('users')->onDelete('restrict');
                $table->date('generation_date');
                $table->string('storage_location')->nullable();
                $table->date('disposal_date')->nullable();
                $table->string('disposal_method')->nullable(); // Incineration, Autoclave, Landfill, etc.
                $table->string('disposal_contractor')->nullable();
                $table->string('manifest_number')->nullable();
                $table->boolean('is_compliant')->default(true);
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->index(['waste_type', 'generation_date']);
                $table->index(['generation_date', 'is_compliant']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        if (Schema::hasTable('medical_waste_logs')) {
            Schema::dropIfExists('medical_waste_logs');
        }
        if (Schema::hasTable('sterilization_logs')) {
            Schema::dropIfExists('sterilization_logs');
        }
        if (Schema::hasTable('medical_supply_request_items')) {
            Schema::dropIfExists('medical_supply_request_items');
        }
        if (Schema::hasTable('medical_supply_requests')) {
            Schema::dropIfExists('medical_supply_requests');
        }
        if (Schema::hasTable('medical_supply_transactions')) {
            Schema::dropIfExists('medical_supply_transactions');
        }
        if (Schema::hasTable('or_utilization_logs')) {
            Schema::dropIfExists('or_utilization_logs');
        }
        if (Schema::hasTable('equipment_maintenance_logs')) {
            Schema::dropIfExists('equipment_maintenance_logs');
        }
        if (Schema::hasTable('medical_equipment')) {
            Schema::dropIfExists('medical_equipment');
        }
        if (Schema::hasTable('surgery_teams')) {
            Schema::dropIfExists('surgery_teams');
        }
        if (Schema::hasTable('surgery_schedules')) {
            Schema::dropIfExists('surgery_schedules');
        }
        if (Schema::hasTable('teleconsultation_feedbacks')) {
            Schema::dropIfExists('teleconsultation_feedbacks');
        }
        if (Schema::hasTable('teleconsultation_payments')) {
            Schema::dropIfExists('teleconsultation_payments');
        }
        if (Schema::hasTable('telemedicine_prescriptions')) {
            Schema::dropIfExists('telemedicine_prescriptions');
        }
        if (Schema::hasTable('teleconsultation_recordings')) {
            Schema::dropIfExists('teleconsultation_recordings');
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
