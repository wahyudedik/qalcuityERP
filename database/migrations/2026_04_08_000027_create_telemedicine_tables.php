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
        Schema::dropIfExists('teleconsultation_feedbacks');
        Schema::dropIfExists('teleconsultation_payments');
        Schema::dropIfExists('telemedicine_prescriptions');
        Schema::dropIfExists('teleconsultation_recordings');
        Schema::dropIfExists('teleconsultations');

        // Teleconsultations
        if (! Schema::hasTable('teleconsultations')) {
            Schema::create('teleconsultations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained()->onDelete('cascade');
                $table->unsignedBigInteger('doctor_id'); // FK to doctors
                $table->unsignedBigInteger('visit_id')->nullable(); // FK to patient_visits

                // Consultation Information
                $table->string('consultation_number')->unique(); // TEL-YYYYMMDD-XXXX
                $table->date('consultation_date');
                $table->datetime('scheduled_time');
                $table->datetime('actual_start_time')->nullable();
                $table->datetime('actual_end_time')->nullable();
                $table->integer('scheduled_duration')->default(30); // minutes
                $table->integer('actual_duration')->nullable(); // minutes

                // Platform & Type
                $table->enum('platform', ['video', 'voice', 'chat'])->default('video');
                $table->enum('consultation_type', ['new', 'follow_up', 'emergency', 'second_opinion'])
                    ->default('new');

                // Meeting Details
                $table->string('meeting_id')->nullable(); // WebRTC/Zoom/Meet ID
                $table->string('meeting_url')->nullable();
                $table->string('meeting_password')->nullable();
                $table->text('meeting_details')->nullable(); // JSON

                // Status
                $table->enum('status', ['scheduled', 'waiting', 'in_progress', 'completed', 'cancelled', 'no_show', 'rescheduled'])
                    ->default('scheduled');

                // Clinical
                $table->text('chief_complaint')->nullable();
                $table->text('medical_history')->nullable();
                $table->text('diagnosis')->nullable();
                $table->string('icd10_code')->nullable();
                $table->text('treatment_plan')->nullable();
                $table->text('doctor_notes')->nullable();

                // Pricing
                $table->decimal('consultation_fee', 10, 2)->default(0);
                $table->decimal('discount', 10, 2)->default(0);
                $table->decimal('total_amount', 10, 2)->default(0);

                // Payment
                $table->enum('payment_status', ['unpaid', 'pending', 'paid', 'refunded'])->default('unpaid');
                $table->datetime('paid_at')->nullable();

                // Cancellation
                $table->text('cancellation_reason')->nullable();
                $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null');
                $table->datetime('cancelled_at')->nullable();

                // Rescheduling
                $table->foreignId('rescheduled_to')->nullable()->constrained('teleconsultations')->onDelete('set null');
                $table->text('reschedule_reason')->nullable();

                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('consultation_number');
                $table->index('patient_id');
                $table->index('doctor_id');
                $table->index('status');
                $table->index('scheduled_time');
                $table->index('consultation_date');
            });
        }

        // Teleconsultation Recordings
        if (! Schema::hasTable('teleconsultation_recordings')) {
            Schema::create('teleconsultation_recordings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('consultation_id')->constrained('teleconsultations')->onDelete('cascade');

                // Recording Information
                $table->string('recording_url');
                $table->string('thumbnail_url')->nullable();
                $table->integer('duration')->default(0); // seconds
                $table->bigInteger('storage_size')->default(0); // bytes

                // Storage
                $table->string('storage_provider')->default('local'); // local, s3, gcs
                $table->string('storage_path')->nullable();
                $table->string('cloud_url')->nullable();

                // Access Control
                $table->boolean('is_encrypted')->default(true);
                $table->datetime('expires_at')->nullable();
                $table->integer('access_count')->default(0);
                $table->integer('max_access')->nullable(); // null = unlimited

                // Status
                $table->enum('status', ['processing', 'available', 'archived', 'deleted'])->default('processing');

                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('consultation_id');
                $table->index('status');
                $table->index('expires_at');
            });
        }

        // Telemedicine Prescriptions
        if (! Schema::hasTable('telemedicine_prescriptions')) {
            Schema::create('telemedicine_prescriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('consultation_id')->constrained('teleconsultations')->onDelete('cascade');
                $table->foreignId('patient_id')->constrained()->onDelete('cascade');
                $table->unsignedBigInteger('doctor_id'); // FK to doctors
                $table->unsignedBigInteger('pharmacy_id')->nullable(); // FK to pharmacies (will be created separately)

                // Prescription Information
                $table->string('prescription_number')->unique(); // RX-TEL-YYYYMMDD-XXXX
                $table->date('prescription_date');
                $table->date('valid_until')->nullable();

                // Prescription Data
                $table->json('prescription_data'); // Array of medicines
                $table->text('diagnosis')->nullable();
                $table->string('icd10_code')->nullable();
                $table->text('instructions')->nullable();
                $table->text('special_notes')->nullable();

                // Pharmacy
                $table->boolean('sent_to_pharmacy')->default(false);
                $table->datetime('sent_at')->nullable();
                $table->enum('pharmacy_status', ['pending', 'confirmed', 'ready', 'dispensed', 'cancelled'])->nullable();
                $table->datetime('pharmacy_confirmed_at')->nullable();

                // Delivery
                $table->enum('fulfillment_method', ['pickup', 'delivery', 'courier'])->default('pickup');
                $table->string('delivery_address')->nullable();
                $table->string('delivery_phone')->nullable();
                $table->datetime('delivery_date')->nullable();
                $table->enum('delivery_status', ['pending', 'in_transit', 'delivered', 'failed'])->nullable();

                // Status
                $table->enum('status', ['draft', 'active', 'completed', 'cancelled', 'expired'])->default('active');

                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('prescription_number');
                $table->index('consultation_id');
                $table->index('patient_id');
                $table->index('status');
            });
        }

        // Teleconsultation Payments
        if (! Schema::hasTable('teleconsultation_payments')) {
            Schema::create('teleconsultation_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('consultation_id')->constrained('teleconsultations')->onDelete('cascade');
                $table->foreignId('patient_id')->constrained()->onDelete('cascade');

                // Payment Information
                $table->string('payment_number')->unique(); // PAY-TEL-YYYYMMDD-XXXX
                $table->decimal('amount', 10, 2);
                $table->decimal('discount', 10, 2)->default(0);
                $table->decimal('total_amount', 10, 2);

                // Payment Method
                $table->enum('payment_method', ['cash', 'credit_card', 'debit_card', 'bank_transfer', 'ewallet', 'insurance'])
                    ->default('ewallet');
                $table->string('payment_gateway')->nullable(); // midtrans, xendit, etc.
                $table->string('gateway_transaction_id')->nullable();
                $table->string('gateway_response')->nullable(); // JSON

                // Status
                $table->enum('status', ['pending', 'processing', 'success', 'failed', 'refunded', 'cancelled'])
                    ->default('pending');
                $table->datetime('paid_at')->nullable();
                $table->datetime('refunded_at')->nullable();

                // Insurance
                $table->boolean('is_insurance_claim')->default(false);
                $table->unsignedBigInteger('insurance_claim_id')->nullable();

                // Receipt
                $table->string('receipt_url')->nullable();
                $table->boolean('receipt_sent')->default(false);
                $table->datetime('receipt_sent_at')->nullable();

                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('payment_number');
                $table->index('consultation_id');
                $table->index('status');
                $table->index('payment_method');
            });
        }

        // Teleconsultation Feedback
        if (! Schema::hasTable('teleconsultation_feedbacks')) {
            Schema::create('teleconsultation_feedbacks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('consultation_id')->constrained('teleconsultations')->onDelete('cascade');
                $table->foreignId('patient_id')->constrained()->onDelete('cascade');
                $table->unsignedBigInteger('doctor_id'); // FK to doctors

                // Rating
                $table->tinyInteger('rating')->unsigned(); // 1-5
                $table->tinyInteger('video_quality')->nullable()->unsigned(); // 1-5
                $table->tinyInteger('audio_quality')->nullable()->unsigned(); // 1-5
                $table->tinyInteger('doctor_rating')->unsigned(); // 1-5
                $table->tinyInteger('platform_rating')->nullable()->unsigned(); // 1-5

                // Feedback
                $table->text('feedback')->nullable();
                $table->text('positive_feedback')->nullable();
                $table->text('negative_feedback')->nullable();
                $table->text('suggestions')->nullable();

                // Tags
                $table->json('feedback_tags')->nullable(); // ['professional', 'clear', 'helpful', etc.]

                // Status
                $table->boolean('is_anonymous')->default(false);
                $table->boolean('is_public')->default(false);
                $table->boolean('is_responded')->default(false);
                $table->text('doctor_response')->nullable();
                $table->datetime('responded_at')->nullable();

                // Follow-up
                $table->boolean('would_recommend')->default(true);
                $table->boolean('would_use_again')->default(true);
                $table->boolean('needs_followup')->default(false);
                $table->text('followup_notes')->nullable();

                $table->timestamps();

                $table->index('consultation_id');
                $table->index('doctor_id');
                $table->index('rating');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teleconsultation_feedbacks');
        Schema::dropIfExists('teleconsultation_payments');
        Schema::dropIfExists('telemedicine_prescriptions');
        Schema::dropIfExists('teleconsultation_recordings');
        Schema::dropIfExists('teleconsultations');
    }
};
