<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('harvest_logs')) {
            Schema::create('harvest_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('farm_plot_id')->constrained()->cascadeOnDelete();
                $table->foreignId('crop_cycle_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('number', 30);                          // HRV-A1-20260331-01
                $table->date('harvest_date');
                $table->string('crop_name');
                $table->decimal('total_qty', 12, 3)->default(0);       // total semua grade
                $table->string('unit', 20)->default('kg');
                $table->decimal('reject_qty', 12, 3)->default(0);      // sortiran/reject
                $table->decimal('moisture_pct', 5, 2)->nullable();      // kadar air (%)
                $table->string('storage_location')->nullable();         // gudang tujuan
                $table->decimal('labor_cost', 15, 2)->default(0);       // upah panen
                $table->decimal('transport_cost', 15, 2)->default(0);   // biaya angkut
                $table->string('weather')->nullable();                  // cuaca saat panen
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'harvest_date']);
                $table->index(['farm_plot_id', 'harvest_date']);
                $table->index(['crop_cycle_id']);
            });
        }

        // Grade breakdown per harvest log
        if (! Schema::hasTable('harvest_log_grades')) {
            Schema::create('harvest_log_grades', function (Blueprint $table) {
                $table->id();
                $table->foreignId('harvest_log_id')->constrained()->cascadeOnDelete();
                $table->string('grade', 30);                           // A, B, C, Premium, Standar, BS
                $table->decimal('quantity', 12, 3);
                $table->string('unit', 20)->default('kg');
                $table->decimal('price_per_unit', 15, 2)->default(0);  // harga jual per grade
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // Workers who participated in harvest
        if (! Schema::hasTable('harvest_log_workers')) {
            Schema::create('harvest_log_workers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('harvest_log_id')->constrained()->cascadeOnDelete();
                $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
                $table->string('worker_name');                         // nama (bisa non-employee)
                $table->decimal('quantity_picked', 12, 3)->default(0); // jumlah yang dipetik pekerja ini
                $table->string('unit', 20)->default('kg');
                $table->decimal('wage', 15, 2)->default(0);            // upah pekerja ini
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('harvest_log_workers');
        Schema::dropIfExists('harvest_log_grades');
        Schema::dropIfExists('harvest_logs');
    }
};
