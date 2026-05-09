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
        // Drop table if exists to fix previous failed migration
        Schema::dropIfExists('appointments');

        if (! Schema::hasTable('appointments')) {
            Schema::create('appointments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained()->onDelete('cascade');
                $table->unsignedBigInteger('doctor_id'); // FK to doctors
                $table->unsignedBigInteger('department_id')->nullable();
                $table->unsignedBigInteger('schedule_id')->nullable(); // FK to medical_staff_schedules
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

                // Appointment Information
                $table->string('appointment_number')->unique();
                $table->date('appointment_date');
                $table->time('appointment_time');
                $table->integer('estimated_duration')->default(30); // minutes

                // Appointment Type
                $table->enum('appointment_type', ['consultation', 'follow_up', 'check_up', 'procedure', 'telemedicine', 'emergency']);
                $table->enum('visit_type', ['new_patient', 'return_patient'])->default('return_patient');

                // Status
                $table->enum('status', ['scheduled', 'confirmed', 'checked_in', 'in_progress', 'completed', 'cancelled', 'no_show', 'rescheduled'])->default('scheduled');

                // Appointment Details
                $table->text('reason_for_visit')->nullable();
                $table->text('symptoms')->nullable();
                $table->text('special_requests')->nullable();
                $table->boolean('is_urgent')->default(false);

                // Reminders
                $table->boolean('reminder_sent_24h')->default(false);
                $table->boolean('reminder_sent_1h')->default(false);
                $table->timestamp('last_reminder_sent_at')->nullable();

                // Check-in
                $table->timestamp('checked_in_at')->nullable();
                $table->timestamp('consultation_started_at')->nullable();
                $table->timestamp('consultation_ended_at')->nullable();

                // Cancellation/Reschedule
                $table->text('cancellation_reason')->nullable();
                $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('cancelled_at')->nullable();
                $table->foreignId('rescheduled_to_id')->nullable()->constrained('appointments')->onDelete('set null');

                // Notification
                $table->string('notification_method')->nullable(); // sms, whatsapp, email
                $table->text('notification_message')->nullable();

                // Outcome
                $table->foreignId('visit_id')->nullable()->constrained('patient_visits')->onDelete('set null');
                $table->integer('satisfaction_rating')->nullable()->min(1)->max(5);
                $table->text('patient_feedback')->nullable();

                // Standard fields
                $table->text('notes')->nullable();
                $table->softDeletes();
                $table->timestamps();

                // Indexes
                $table->index('patient_id');
                $table->index('doctor_id');
                $table->index('appointment_date');
                $table->index('appointment_time');
                $table->index('status');
                $table->index('appointment_type');
                $table->index(['appointment_date', 'doctor_id']);
                $table->index(['appointment_date', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
