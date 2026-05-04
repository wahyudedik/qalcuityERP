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
        // Drop table if exists to fix previous failed migration
        Schema::dropIfExists('patient_medical_records');

        if (!Schema::hasTable('patient_medical_records')) {
            Schema::create('patient_medical_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained()->onDelete('cascade');
                $table->foreignId('doctor_id')->nullable()->constrained('users')->onDelete('set null');
                $table->unsignedBigInteger('visit_id')->nullable(); // FK to patient_visits
    
                // Record Details
                $table->string('record_type'); // consultation, examination, procedure, observation
                $table->text('chief_complaint')->nullable(); // Keluhan utama
                $table->text('history_of_present_illness')->nullable(); // Riwayat penyakit sekarang
                $table->text('past_medical_history')->nullable(); // Riwayat penyakit dahulu
                $table->text('family_history')->nullable(); // Riwayat penyakit keluarga
                $table->text('social_history')->nullable(); // Riwayat sosial (merokok, alkohol, dll)
    
                // Physical Examination
                $table->json('vital_signs')->nullable(); // {bp_sysolic, bp_diastolic, heart_rate, temperature, respiratory_rate, spo2, weight, height, bmi}
                $table->text('physical_examination')->nullable(); // Hasil pemeriksaan fisik
                $table->text('examination_findings')->nullable(); // Temuan pemeriksaan
    
                // Diagnosis & Treatment
                $table->text('diagnosis')->nullable(); // Diagnosa
                $table->text('differential_diagnosis')->nullable(); // Diagnosa banding
                $table->text('treatment_plan')->nullable(); // Rencana perawatan
                $table->text('medications_prescribed')->nullable(); // Obat yang diresepkan
                $table->text('procedures_performed')->nullable(); // Tindakan yang dilakukan
                $table->text('doctor_notes')->nullable(); // Catatan dokter
                $table->text('patient_instructions')->nullable(); // Instruksi untuk pasien
    
                // Follow-up
                $table->date('follow_up_date')->nullable();
                $table->text('follow_up_instructions')->nullable();
    
                // Status
                $table->enum('status', ['draft', 'completed', 'amended'])->default('draft');
                $table->boolean('is_emergency')->default(false);
                $table->boolean('requires_follow_up')->default(false);
    
                // Digital Signature
                $table->string('doctor_signature')->nullable();
                $table->timestamp('signed_at')->nullable();
    
                // Standard fields
                $table->text('notes')->nullable();
                $table->softDeletes();
                $table->timestamps();
    
                // Indexes
                $table->index('patient_id');
                $table->index('doctor_id');
                $table->index('record_type');
                $table->index('status');
                $table->index('follow_up_date');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_medical_records');
    }
};
