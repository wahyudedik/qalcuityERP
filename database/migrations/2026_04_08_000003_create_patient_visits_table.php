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
        if (! Schema::hasTable('patient_visits')) {
            Schema::create('patient_visits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained()->onDelete('cascade');
                $table->foreignId('doctor_id')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('registered_by')->nullable()->constrained('users')->onDelete('set null');

                // Visit Information
                $table->string('visit_number')->unique(); // No. Kunjungan
                $table->enum('visit_type', ['outpatient', 'inpatient', 'emergency', 'telemedicine', 'home_care']);
                $table->date('visit_date');
                $table->time('visit_time');

                // Visit Details
                $table->text('chief_complaint')->nullable(); // Keluhan utama
                $table->string('visit_reason')->nullable(); // Alasan kunjungan
                $table->enum('visit_status', ['registered', 'waiting', 'in_consultation', 'completed', 'referred', 'cancelled'])->default('registered');

                // Queue Information (for outpatient)
                $table->integer('queue_number')->nullable();
                $table->timestamp('queue_called_at')->nullable();
                $table->timestamp('consultation_started_at')->nullable();
                $table->timestamp('consultation_ended_at')->nullable();

                // Referral Information
                $table->boolean('is_referral')->default(false);
                $table->string('referral_from')->nullable(); // Doctor/hospital name
                $table->string('referral_to')->nullable(); // Referred to specialist/hospital
                $table->text('referral_reason')->nullable();

                // Department/Unit
                $table->string('department')->nullable(); // Poliklinik/Unit
                $table->string('room_number')->nullable();

                // Diagnosis Summary
                $table->text('primary_diagnosis')->nullable();
                $table->text('secondary_diagnosis')->nullable();
                $table->string('icd10_code')->nullable();

                // Visit Outcome
                $table->enum('outcome', ['treated', 'referred', 'admitted', 'discharged', 'left_ama', 'deceased'])->nullable();
                $table->text('treatment_summary')->nullable();
                $table->text('follow_up_instructions')->nullable();
                $table->date('next_visit_date')->nullable();

                // Billing
                $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'insurance_pending', 'waived'])->default('unpaid');
                $table->decimal('consultation_fee', 12, 2)->default(0);
                $table->decimal('total_charges', 12, 2)->default(0);

                // Satisfaction
                $table->integer('satisfaction_rating')->nullable()->min(1)->max(5);
                $table->text('patient_feedback')->nullable();

                // Standard fields
                $table->text('notes')->nullable();
                $table->softDeletes();
                $table->timestamps();

                // Indexes
                $table->index('patient_id');
                $table->index('doctor_id');
                $table->index('visit_number');
                $table->index('visit_date');
                $table->index('visit_type');
                $table->index('visit_status');
                $table->index('department');
                $table->index(['visit_date', 'visit_status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_visits');
    }
};
