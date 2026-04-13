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
        if (!Schema::hasTable('telemedicine_prescriptions')) {
            Schema::create('telemedicine_prescriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('consultation_id')->constrained('teleconsultations')->onDelete('cascade');
                $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
                $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('pharmacy_id')->nullable()->constrained('pharmacies')->onDelete('set null');
                $table->string('prescription_number')->unique();
                $table->date('prescription_date');
                $table->date('valid_until');
                $table->json('prescription_data');
                $table->string('diagnosis')->nullable();
                $table->string('icd10_code')->nullable();
                $table->text('instructions')->nullable();
                $table->text('special_notes')->nullable();
                $table->enum('status', ['active', 'completed', 'cancelled', 'expired'])->default('active');
                $table->boolean('sent_to_pharmacy')->default(false);
                $table->timestamp('sent_at')->nullable();
                $table->enum('pharmacy_status', ['pending', 'confirmed', 'preparing', 'ready', 'dispensed'])->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['consultation_id', 'status']);
                $table->index(['patient_id', 'prescription_date']);
                $table->index(['doctor_id', 'prescription_date']);
                $table->index('prescription_number');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telemedicine_prescriptions');
    }
};
