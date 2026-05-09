<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Program pelatihan (katalog)
        if (! Schema::hasTable('training_programs')) {
            Schema::create('training_programs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('name');
                $table->string('category')->nullable();       // teknis, soft-skill, keselamatan, ISO, dll
                $table->text('description')->nullable();
                $table->string('provider')->nullable();       // vendor/lembaga
                $table->unsignedSmallInteger('duration_hours')->default(0);
                $table->decimal('cost', 18, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->index(['tenant_id', 'category']);
            });
        }

        // Sesi pelatihan (jadwal)
        if (! Schema::hasTable('training_sessions')) {
            Schema::create('training_sessions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('training_program_id');
                $table->date('start_date');
                $table->date('end_date');
                $table->string('location')->nullable();
                $table->string('trainer')->nullable();
                $table->unsignedSmallInteger('max_participants')->default(0); // 0 = unlimited
                $table->enum('status', ['scheduled', 'ongoing', 'completed', 'cancelled'])->default('scheduled');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('training_program_id')->references('id')->on('training_programs')->cascadeOnDelete();
                $table->index(['tenant_id', 'start_date']);
            });
        }

        // Peserta pelatihan
        if (! Schema::hasTable('training_participants')) {
            Schema::create('training_participants', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('training_session_id');
                $table->unsignedBigInteger('employee_id');
                $table->enum('status', ['registered', 'attended', 'passed', 'failed', 'absent'])->default('registered');
                $table->unsignedTinyInteger('score')->nullable();   // 0-100
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('training_session_id')->references('id')->on('training_sessions')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->unique(['training_session_id', 'employee_id'], 'tp_session_emp_unique');
                $table->index(['tenant_id', 'employee_id']);
            });
        }

        // Sertifikasi karyawan
        if (! Schema::hasTable('employee_certifications')) {
            Schema::create('employee_certifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('employee_id');
                $table->string('name');                         // nama sertifikat
                $table->string('issuer')->nullable();           // lembaga penerbit
                $table->string('certificate_number')->nullable();
                $table->date('issued_date');
                $table->date('expiry_date')->nullable();        // null = tidak expired
                $table->enum('status', ['active', 'expired', 'revoked'])->default('active');
                $table->string('file_path')->nullable();        // upload scan sertifikat
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->index(['tenant_id', 'employee_id']);
                $table->index(['tenant_id', 'expiry_date', 'status'], 'ec_expiry_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('training_participants');
        Schema::dropIfExists('training_sessions');
        Schema::dropIfExists('training_programs');
        Schema::dropIfExists('employee_certifications');
    }
};
