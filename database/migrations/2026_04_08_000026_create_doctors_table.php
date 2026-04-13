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
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Link to users table

            // Professional Information
            $table->string('doctor_number')->unique(); // DR-XXXXX
            $table->string('license_number')->unique(); // STR (Surat Tanda Registrasi)
            $table->string('sip_number')->nullable(); // Surat Izin Praktik
            $table->string('specialization'); // General Practice, Cardiology, etc.
            $table->string('sub_specialization')->nullable(); // More specific specialization

            // Practice Information
            $table->json('practice_locations')->nullable(); // ["Clinic A", "Hospital B"]
            $table->json('practice_days')->nullable(); // ["monday", "wednesday", "friday"]
            $table->time('practice_start_time')->nullable();
            $table->time('practice_end_time')->nullable();

            // Financial
            $table->decimal('consultation_fee', 10, 2)->default(0);
            $table->decimal('follow_up_fee', 10, 2)->nullable();
            $table->decimal('home_visit_fee', 10, 2)->nullable();
            $table->decimal('telemedicine_fee', 10, 2)->nullable();

            // Qualifications
            $table->string('medical_school')->nullable();
            $table->string('graduation_year')->nullable();
            $table->text('certifications')->nullable(); // JSON array
            $table->text('education_history')->nullable(); // JSON array
            $table->text('professional_memberships')->nullable(); // JSON array

            // Experience
            $table->integer('years_of_experience')->default(0);
            $table->text('biography')->nullable();
            $table->text('languages_spoken')->nullable(); // JSON array

            // Status & Availability
            $table->enum('status', ['active', 'inactive', 'on_leave', 'suspended'])->default('active');
            $table->boolean('accepting_patients')->default(true);
            $table->boolean('available_for_telemedicine')->default(false);
            $table->boolean('available_for_home_visit')->default(false);
            $table->boolean('available_for_emergency')->default(false);

            // Statistics
            $table->integer('total_consultations')->default(0);
            $table->integer('total_patients')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('total_reviews')->default(0);

            // Bank Information (for payments)
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_name')->nullable();

            // Photo & Documents
            $table->string('photo_path')->nullable();
            $table->string('license_document_path')->nullable();
            $table->string('sip_document_path')->nullable();

            // Standard fields
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('doctor_number');
            $table->index('license_number');
            $table->index('specialization');
            $table->index('status');
            $table->index('accepting_patients');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
