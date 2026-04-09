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
        // Drop existing tables if they exist (in correct order)
        Schema::dropIfExists('message_attachments');
        Schema::dropIfExists('patient_messages');
        Schema::dropIfExists('medical_certificate_requests');
        Schema::dropIfExists('health_education_content');
        Schema::dropIfExists('patient_portal_logs');

        // Secure Messaging (Patient-Doctor Communication)
        Schema::create('patient_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('doctor_id')->nullable(); // FK to doctors
            $table->foreignId('sender_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('recipient_id')->constrained('users')->onDelete('restrict');

            // Message Details
            $table->string('message_number')->unique(); // MSG-YYYYMMDD-XXXXXX
            $table->unsignedBigInteger('parent_id')->nullable(); // For threaded conversations
            $table->string('conversation_id')->nullable(); // Group messages
            $table->string('subject');
            $table->text('message_body');
            $table->json('attachments')->nullable(); // File paths

            // Message Type
            $table->enum('message_type', ['consultation', 'follow_up', 'question', 'prescription_query', 'lab_query', 'general'])->default('general');
            $table->enum('priority', ['low', 'normal', 'urgent', 'critical'])->default('normal');

            // Status
            $table->boolean('is_read')->default(false);
            $table->datetime('read_at')->nullable();
            $table->boolean('is_replied')->default(false);
            $table->datetime('replied_at')->nullable();
            $table->boolean('is_archived')->default(false);

            // Reference
            $table->unsignedBigInteger('appointment_id')->nullable(); // FK to appointments
            $table->unsignedBigInteger('prescription_id')->nullable(); // FK to prescriptions
            $table->unsignedBigInteger('lab_result_id')->nullable(); // FK to lab_results

            $table->timestamps();
            $table->softDeletes();

            $table->index('message_number');
            $table->index('patient_id');
            $table->index('doctor_id');
            $table->index('conversation_id');
            $table->index('is_read');
            $table->index('message_type');
            $table->index('created_at');
        });

        // Message Attachments
        Schema::create('message_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('patient_messages')->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable(); // image, pdf, document, etc.
            $table->integer('file_size')->nullable(); // bytes
            $table->string('mime_type')->nullable();
            $table->timestamps();

            $table->index('message_id');
        });

        // Medical Certificate Requests
        Schema::create('medical_certificate_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('doctor_id')->nullable(); // FK to doctors
            $table->unsignedBigInteger('department_id')->nullable(); // FK to departments

            // Request Details
            $table->string('request_number')->unique(); // CERT-YYYYMMDD-XXXX
            $table->date('request_date');
            $table->enum('certificate_type', ['sick_leave', 'fit_to_work', 'medical_report', 'health_certificate', 'vaccination', 'other']);
            $table->string('purpose'); // Work, school, travel, etc.
            $table->text('description')->nullable();

            // Date Range
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('days_requested')->nullable();

            // Reference
            $table->unsignedBigInteger('visit_id')->nullable(); // FK to patient_visits
            $table->unsignedBigInteger('admission_id')->nullable(); // FK to admissions

            // Status
            $table->enum('status', ['pending', 'approved', 'rejected', 'issued', 'cancelled'])->default('pending');
            $table->datetime('approved_at')->nullable();
            $table->datetime('issued_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('doctor_notes')->nullable();

            // Certificate
            $table->string('certificate_number')->nullable();
            $table->string('certificate_file_path')->nullable();
            $table->string('certificate_qr_code')->nullable(); // For verification

            $table->timestamps();

            $table->index('request_number');
            $table->index('patient_id');
            $table->index('status');
            $table->index('certificate_type');
            $table->index('request_date');
        });

        // Health Education Content
        Schema::create('health_education_content', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->unsignedBigInteger('department_id')->nullable(); // FK to departments

            // Content Details
            $table->string('content_number')->unique(); // HEALTH-XXXX
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary');
            $table->longText('content');
            $table->json('content_sections')->nullable(); // Structured content

            // Categorization
            $table->string('category'); // disease, wellness, nutrition, exercise, mental_health, etc.
            $table->json('tags')->nullable();
            $table->string('target_audience')->nullable(); // patients, caregivers, general
            $table->string('language')->default('id'); // id, en

            // Media
            $table->string('featured_image')->nullable();
            $table->json('attachments')->nullable(); // PDFs, videos, etc.

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();

            // Status
            $table->boolean('is_published')->default(false);
            $table->datetime('published_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->integer('reading_time_minutes')->nullable();

            // Analytics
            $table->integer('view_count')->default(0);
            $table->integer('helpful_count')->default(0);
            $table->integer('not_helpful_count')->default(0);

            // Scheduling
            $table->datetime('publish_start')->nullable();
            $table->datetime('publish_end')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('content_number');
            $table->index('slug');
            $table->index('category');
            $table->index('is_published');
            $table->index('published_at');
            $table->index('view_count');
        });

        // Patient Portal Activity Logs
        Schema::create('patient_portal_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');

            // Activity
            $table->string('activity_type'); // login, view_records, book_appointment, view_lab, pay_bill, etc.
            $table->string('activity_description');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            // Reference
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_type')->nullable();

            $table->timestamps();

            $table->index('patient_id');
            $table->index('activity_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_portal_logs');
        Schema::dropIfExists('health_education_content');
        Schema::dropIfExists('medical_certificate_requests');
        Schema::dropIfExists('message_attachments'); // Drop child first
        Schema::dropIfExists('patient_messages'); // Then drop parent
    }
};
