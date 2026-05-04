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
        Schema::dropIfExists('er_analytics_daily');
        Schema::dropIfExists('er_alerts');
        Schema::dropIfExists('emergency_treatments');
        Schema::dropIfExists('triage_assessments');
        Schema::dropIfExists('emergency_cases');

        // Emergency Cases
        if (!Schema::hasTable('emergency_cases')) {
            Schema::create('emergency_cases', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained()->onDelete('cascade');
                $table->foreignId('triage_nurse_id')->nullable()->constrained('users')->onDelete('set null');
                $table->unsignedBigInteger('emergency_doctor_id')->nullable(); // FK to doctors
                $table->unsignedBigInteger('admission_id')->nullable(); // FK to admissions (will be created separately)
    
                // Case Information
                $table->string('case_number')->unique(); // ER-YYYYMMDD-XXXX
                $table->datetime('arrival_time');
                $table->datetime('triage_time')->nullable();
                $table->datetime('treatment_started_at')->nullable();
                $table->datetime('treatment_ended_at')->nullable();
                $table->datetime('disposition_time')->nullable();
    
                // Triage
                $table->enum('triage_level', ['red', 'orange', 'yellow', 'green', 'black']);
                $table->string('triage_code')->nullable(); // ESI-1, ESI-2, etc.
    
                // Case Details
                $table->text('chief_complaint');
                $table->text('mechanism_of_injury')->nullable(); // For trauma cases
                $table->string('arrival_mode')->nullable(); // walk-in, ambulance, helicopter, referral
                $table->string('brought_by')->nullable(); // police, family, ambulance crew
    
                // Status & Disposition
                $table->enum('status', [
                    'triaged',
                    'waiting',
                    'in_treatment',
                    'critical',
                    'stable',
                    'admitted',
                    'transferred',
                    'discharged',
                    'ama',
                    'deceased',
                    'referred'
                ])
                    ->default('triaged');
                $table->enum('disposition', ['discharged_home', 'admitted', 'transferred', 'ama', 'deceased', 'referred'])->nullable();
    
                // Timing Metrics
                $table->integer('door_to_triage_minutes')->nullable();
                $table->integer('door_to_doctor_minutes')->nullable();
                $table->integer('door_to_treatment_minutes')->nullable();
                $table->integer('total_er_duration_minutes')->nullable();
    
                // Alert Flags
                $table->boolean('is_critical')->default(false);
                $table->boolean('requires_isolation')->default(false);
                $table->boolean('requires_immediate_intervention')->default(false);
                $table->boolean('alert_sent')->default(false);
    
                // Standard fields
                $table->text('notes')->nullable();
                $table->softDeletes();
                $table->timestamps();
    
                $table->index('case_number');
                $table->index('patient_id');
                $table->index('triage_level');
                $table->index('status');
                $table->index('arrival_time');
                $table->index(['triage_level', 'status']);
            });
        }

        // Triage Assessments
        if (!Schema::hasTable('triage_assessments')) {
            Schema::create('triage_assessments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('case_id')->constrained('emergency_cases')->onDelete('cascade');
                $table->foreignId('assessed_by')->constrained('users')->onDelete('restrict');
    
                // Assessment Time
                $table->datetime('assessment_time');
                $table->integer('assessment_number')->default(1); // Multiple assessments allowed
    
                // Vital Signs
                $table->json('vital_signs'); // Complete vital signs JSON
                $table->decimal('temperature', 4, 1)->nullable();
                $table->integer('heart_rate')->nullable();
                $table->integer('blood_pressure_systolic')->nullable();
                $table->integer('blood_pressure_diastolic')->nullable();
                $table->integer('respiratory_rate')->nullable();
                $table->integer('oxygen_saturation')->nullable(); // SpO2
                $table->integer('pain_scale')->nullable(); // 0-10
    
                // Consciousness
                $table->string('gcs_eye')->nullable(); // Glasgow Coma Scale - Eye
                $table->string('gcs_verbal')->nullable(); // GCS - Verbal
                $table->string('gcs_motor')->nullable(); // GCS - Motor
                $table->integer('gcs_total')->nullable(); // Total GCS score
    
                // Assessment
                $table->enum('urgency_level', ['resuscitation', 'emergent', 'urgent', 'less_urgent', 'non_urgent']);
                $table->integer('esi_level')->nullable(); // Emergency Severity Index 1-5
    
                // Clinical Notes
                $table->text('nurse_notes');
                $table->text('chief_complaint_details');
                $table->text('allergies')->nullable();
                $table->text('current_medications')->nullable();
                $table->text('medical_history')->nullable();
    
                // Recommendations
                $table->text('recommended_actions')->nullable();
                $table->boolean('requires_immediate_intervention')->default(false);
                $table->boolean('requires_isolation')->default(false);
                $table->string('recommended_department')->nullable();
    
                // Standard fields
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->index('case_id');
                $table->index('urgency_level');
                $table->index('esi_level');
                $table->index('assessment_time');
            });
        }

        // Emergency Treatments
        if (!Schema::hasTable('emergency_treatments')) {
            Schema::create('emergency_treatments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('case_id')->constrained('emergency_cases')->onDelete('cascade');
                $table->foreignId('patient_id')->constrained()->onDelete('cascade');
                $table->unsignedBigInteger('treated_by'); // FK to doctors (will be created separately)
                $table->foreignId('assisted_by')->nullable()->constrained('users')->onDelete('set null');
    
                // Treatment Information
                $table->string('treatment_number')->unique();
                $table->datetime('treatment_start');
                $table->datetime('treatment_end')->nullable();
                $table->integer('duration_minutes')->nullable();
    
                // Treatment Details
                $table->text('treatment_given');
                $table->text('diagnosis')->nullable();
                $table->string('icd10_code')->nullable();
    
                // Medications
                $table->json('medications_given')->nullable(); // Array of medications
                $table->text('medication_notes')->nullable();
    
                // Procedures
                $table->json('procedures_performed')->nullable(); // Array of procedures
                $table->text('procedure_notes')->nullable();
    
                // Interventions
                $table->json('interventions')->nullable(); // IV fluids, oxygen, etc.
                $table->text('response_to_treatment')->nullable();
    
                // Outcome
                $table->enum('outcome', ['improved', 'stable', 'worsened', 'no_change', 'critical', 'deceased']);
                $table->text('outcome_notes')->nullable();
    
                // Disposition
                $table->enum('disposition', ['discharged', 'admitted', 'transferred', 'referred', 'ama', 'deceased']);
                $table->text('disposition_notes')->nullable();
                $table->string('admitted_to_ward')->nullable();
                $table->string('transferred_to')->nullable();
    
                // Follow-up
                $table->text('follow_up_instructions')->nullable();
                $table->date('follow_up_date')->nullable();
                $table->string('follow_up_with')->nullable();
    
                // Standard fields
                $table->text('notes')->nullable();
                $table->softDeletes();
                $table->timestamps();
    
                $table->index('case_id');
                $table->index('treatment_number');
                $table->index('outcome');
                $table->index('disposition');
            });
        }

        // ER Alerts
        if (!Schema::hasTable('er_alerts')) {
            Schema::create('er_alerts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('case_id')->constrained('emergency_cases')->onDelete('cascade');
                $table->foreignId('patient_id')->constrained()->onDelete('cascade');
                $table->foreignId('alerted_by')->constrained('users')->onDelete('restrict');
                $table->foreignId('acknowledged_by')->nullable()->constrained('users')->onDelete('set null');
    
                // Alert Information
                $table->string('alert_type'); // critical_patient, bed_unavailable, equipment_needed, etc.
                $table->string('alert_title');
                $table->text('alert_message');
                $table->enum('priority', ['critical', 'high', 'medium', 'low'])->default('critical');
    
                // Status
                $table->enum('status', ['active', 'acknowledged', 'resolved', 'escalated'])->default('active');
                $table->datetime('alerted_at');
                $table->datetime('acknowledged_at')->nullable();
                $table->datetime('resolved_at')->nullable();
                $table->text('resolution_notes')->nullable();
    
                // Escalation
                $table->boolean('is_escalated')->default(false);
                $table->datetime('escalated_at')->nullable();
                $table->foreignId('escalated_to')->nullable()->constrained('users')->onDelete('set null');
    
                $table->timestamps();
    
                $table->index('case_id');
                $table->index('alert_type');
                $table->index('priority');
                $table->index('status');
            });
        }

        // ER Analytics Daily
        if (!Schema::hasTable('er_analytics_daily')) {
            Schema::create('er_analytics_daily', function (Blueprint $table) {
                $table->id();
                $table->date('analytics_date');
    
                // Volume Metrics
                $table->integer('total_cases')->default(0);
                $table->integer('total_treated')->default(0);
                $table->integer('total_admitted')->default(0);
                $table->integer('total_discharged')->default(0);
                $table->integer('total_transferred')->default(0);
                $table->integer('total_deceased')->default(0);
                $table->integer('current_in_er')->default(0);
    
                // Triage Distribution
                $table->integer('triage_red')->default(0);
                $table->integer('triage_orange')->default(0);
                $table->integer('triage_yellow')->default(0);
                $table->integer('triage_green')->default(0);
                $table->integer('triage_black')->default(0);
    
                // Timing Metrics (minutes)
                $table->integer('avg_door_to_triage')->default(0);
                $table->integer('avg_door_to_doctor')->default(0);
                $table->integer('avg_door_to_treatment')->default(0);
                $table->integer('avg_total_er_duration')->default(0);
    
                // Outcome Distribution
                $table->integer('outcome_improved')->default(0);
                $table->integer('outcome_stable')->default(0);
                $table->integer('outcome_worsened')->default(0);
                $table->integer('outcome_deceased')->default(0);
    
                // Hourly distribution (JSON)
                $table->json('hourly_arrivals')->nullable();
                $table->json('triage_level_distribution')->nullable();
                $table->json('common_complaints')->nullable();
    
                $table->timestamps();
    
                $table->unique('analytics_date');
                $table->index('analytics_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('er_analytics_daily');
        Schema::dropIfExists('er_alerts');
        Schema::dropIfExists('emergency_treatments');
        Schema::dropIfExists('triage_assessments');
        Schema::dropIfExists('emergency_cases');
    }
};
