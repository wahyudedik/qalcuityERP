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
        if (! Schema::hasTable('fingerprint_attendance_logs')) {
            Schema::create('fingerprint_attendance_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('device_id')->constrained('fingerprint_devices')->cascadeOnDelete();
                $table->string('employee_uid'); // UID dari perangkat fingerprint
                $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete(); // Relasi ke tabel employees
                $table->timestamp('scan_time'); // Waktu scan di perangkat
                $table->enum('scan_type', ['check_in', 'check_out', 'break_in', 'break_out'])->default('check_in');
                $table->boolean('is_processed')->default(false); // Apakah sudah diproses ke attendance
                $table->timestamp('processed_at')->nullable(); // Waktu pemrosesan
                $table->string('raw_data')->nullable(); // Data mentah dari perangkat (JSON)
                $table->text('error_message')->nullable(); // Pesan error jika ada
                $table->timestamps();

                $table->index(['tenant_id', 'employee_uid']);
                $table->index(['tenant_id', 'scan_time']);
                $table->index(['device_id', 'scan_time']);
                $table->index(['employee_id', 'scan_time']);
                $table->unique(['device_id', 'employee_uid', 'scan_time'], 'unique_scan_per_device');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fingerprint_attendance_logs');
    }
};
