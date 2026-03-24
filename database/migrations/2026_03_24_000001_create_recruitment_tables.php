<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Lowongan kerja
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('department')->nullable();
            $table->string('location')->nullable();
            $table->enum('type', ['full_time', 'part_time', 'contract', 'internship'])->default('full_time');
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->unsignedInteger('salary_min')->nullable();
            $table->unsignedInteger('salary_max')->nullable();
            $table->unsignedSmallInteger('quota')->default(1);
            $table->date('deadline')->nullable();
            $table->enum('status', ['draft', 'open', 'closed'])->default('open');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        // Lamaran masuk
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_posting_id')->constrained()->cascadeOnDelete();
            $table->string('applicant_name');
            $table->string('applicant_email')->nullable();
            $table->string('applicant_phone')->nullable();
            $table->text('cover_letter')->nullable();
            $table->string('resume_path')->nullable();
            $table->enum('stage', [
                'applied',       // baru masuk
                'screening',     // sedang diseleksi
                'interview',     // dijadwalkan interview
                'offer',         // offer letter dikirim
                'hired',         // diterima → jadi karyawan
                'rejected',      // ditolak
            ])->default('applied');
            $table->text('notes')->nullable();           // catatan internal HRD
            $table->date('interview_date')->nullable();
            $table->string('interview_location')->nullable();
            $table->unsignedInteger('offered_salary')->nullable();
            $table->date('expected_join_date')->nullable();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete(); // setelah hired
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'job_posting_id', 'stage']);
        });

        // Template checklist onboarding per tenant
        Schema::create('onboarding_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');                      // e.g. "Onboarding Staff Umum"
            $table->string('department')->nullable();    // null = berlaku semua dept
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Item tugas dalam template checklist
        Schema::create('onboarding_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('onboarding_checklist_id')->constrained()->cascadeOnDelete();
            $table->string('task');
            $table->string('category')->nullable();      // e.g. "Administrasi", "IT", "Fasilitas"
            $table->unsignedTinyInteger('due_day')->default(1); // hari ke-N setelah join
            $table->boolean('required')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Progress onboarding per karyawan baru
        Schema::create('employee_onboardings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_application_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['in_progress', 'completed'])->default('in_progress');
            $table->date('start_date');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['employee_id']);
        });

        // Progress per task onboarding karyawan
        Schema::create('employee_onboarding_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_onboarding_id')->constrained()->cascadeOnDelete();
            $table->string('task');
            $table->string('category')->nullable();
            $table->unsignedTinyInteger('due_day')->default(1);
            $table->boolean('required')->default(true);
            $table->boolean('is_done')->default(false);
            $table->timestamp('done_at')->nullable();
            $table->foreignId('done_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_onboarding_tasks');
        Schema::dropIfExists('employee_onboardings');
        Schema::dropIfExists('onboarding_checklist_items');
        Schema::dropIfExists('onboarding_checklists');
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('job_postings');
    }
};
