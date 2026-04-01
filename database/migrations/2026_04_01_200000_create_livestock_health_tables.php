<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Health records — illness, treatment, observation
        Schema::create('livestock_health_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('livestock_herd_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->enum('type', ['illness', 'treatment', 'observation', 'quarantine', 'recovery']);
            $table->string('condition');                            // nama penyakit / kondisi
            $table->integer('affected_count')->default(0);         // jumlah ternak terdampak
            $table->integer('death_count')->default(0);            // kematian akibat kondisi ini
            $table->string('symptoms')->nullable();
            $table->string('medication')->nullable();              // obat yang diberikan
            $table->decimal('medication_cost', 15, 2)->default(0);
            $table->string('administered_by')->nullable();         // dokter hewan / petugas
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['active', 'monitoring', 'resolved'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['livestock_herd_id', 'date']);
            $table->index(['tenant_id', 'type']);
        });

        // Vaccination schedule & records
        Schema::create('livestock_vaccinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('livestock_herd_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('vaccine_name');                        // ND, Gumboro, IBD, Anthrax
            $table->date('scheduled_date');
            $table->date('administered_date')->nullable();
            $table->integer('dose_age_days')->default(0);          // umur saat vaksin (hari)
            $table->string('dose_method')->nullable();             // tetes mata, suntik, air minum, spray
            $table->integer('vaccinated_count')->default(0);       // jumlah yang divaksin
            $table->decimal('cost', 15, 2)->default(0);
            $table->string('administered_by')->nullable();
            $table->string('batch_number')->nullable();            // nomor batch vaksin
            $table->enum('status', ['scheduled', 'completed', 'missed', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['livestock_herd_id', 'status']);
            $table->index(['tenant_id', 'scheduled_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('livestock_vaccinations');
        Schema::dropIfExists('livestock_health_records');
    }
};
