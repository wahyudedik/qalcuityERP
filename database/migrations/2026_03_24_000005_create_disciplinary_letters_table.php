<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('disciplinary_letters')) {
            Schema::create('disciplinary_letters', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('employee_id');
                $table->enum('level', ['sp1', 'sp2', 'sp3', 'memo', 'termination'])->default('sp1');
                $table->string('letter_number')->nullable();   // nomor surat
                $table->date('issued_date');
                $table->date('valid_until')->nullable();       // masa berlaku SP
                $table->string('violation_type');              // jenis pelanggaran
                $table->text('violation_description');         // uraian pelanggaran
                $table->text('corrective_action');             // tindakan perbaikan yang diminta
                $table->text('consequences')->nullable();      // konsekuensi jika tidak diperbaiki
                $table->enum('status', ['draft', 'issued', 'acknowledged', 'expired'])->default('draft');
                $table->timestamp('acknowledged_at')->nullable();
                $table->text('employee_response')->nullable(); // tanggapan karyawan
                $table->unsignedBigInteger('issued_by');       // user yang menerbitkan
                $table->unsignedBigInteger('witnessed_by')->nullable(); // saksi
                $table->string('source')->nullable();          // 'manual' | 'ai_anomaly'
                $table->json('ai_context')->nullable();        // data anomali dari AI
                $table->timestamps();
    
                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                $table->foreign('issued_by')->references('id')->on('users')->restrictOnDelete();
                $table->foreign('witnessed_by')->references('id')->on('users')->nullOnDelete();
                $table->index(['tenant_id', 'employee_id']);
                $table->index(['tenant_id', 'status', 'issued_date'], 'dl_status_date_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('disciplinary_letters');
    }
};
