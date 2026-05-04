<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Template shift (Pagi, Siang, Malam, dll)
        if (!Schema::hasTable('work_shifts')) {
            Schema::create('work_shifts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('name');                        // e.g. "Shift Pagi"
                $table->string('color', 7)->default('#3b82f6'); // hex color untuk kalender
                $table->time('start_time');                    // e.g. 08:00
                $table->time('end_time');                      // e.g. 16:00
                $table->unsignedSmallInteger('break_minutes')->default(60);
                $table->boolean('crosses_midnight')->default(false); // shift malam yg melewati tengah malam
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
    
                $table->index(['tenant_id', 'is_active']);
            });
        }

        // Jadwal shift per karyawan per hari
        if (!Schema::hasTable('shift_schedules')) {
            Schema::create('shift_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
                $table->foreignId('work_shift_id')->constrained()->cascadeOnDelete();
                $table->date('date');
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->unique(['employee_id', 'date']);
                $table->index(['tenant_id', 'date']);
                $table->index(['tenant_id', 'employee_id']);
            });
        }

        // Tambah kolom shift ke tabel attendances
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'shift_id')) {
                $table->foreignId('shift_id')->nullable()->after('employee_id')
                      ->constrained('work_shifts')->nullOnDelete();
            }
            $table->unsignedSmallInteger('work_minutes')->nullable()->after('check_out');
            if (!Schema::hasColumn('attendances', 'overtime_minutes')) {
                $table->smallInteger('overtime_minutes')->nullable()->after('work_minutes'); // bisa negatif (pulang cepat)
                $table->enum('status', ['present', 'absent', 'late', 'leave', 'sick', 'holiday'])->default('present')->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
            $table->dropColumn(['shift_id', 'work_minutes', 'overtime_minutes']);
        });
        Schema::dropIfExists('shift_schedules');
        Schema::dropIfExists('work_shifts');
    }
};
