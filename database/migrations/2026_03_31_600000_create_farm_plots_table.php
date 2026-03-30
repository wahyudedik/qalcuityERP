<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farm_plots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30);                            // A1, B2, Blok-01
            $table->string('name');                                 // "Blok A1 — Sawah Utara"
            $table->decimal('area_size', 10, 3)->default(0);       // luas
            $table->string('area_unit', 10)->default('ha');         // ha, m2, are
            $table->string('location')->nullable();                 // alamat/koordinat
            $table->string('soil_type', 50)->nullable();            // tanah liat, berpasir, dll
            $table->string('irrigation_type', 50)->nullable();      // irigasi, tadah hujan, sprinkler
            $table->string('ownership', 30)->default('owned');      // owned, rented, shared
            $table->decimal('rent_cost', 15, 2)->default(0);        // biaya sewa per musim
            $table->string('current_crop')->nullable();             // tanaman saat ini
            $table->enum('status', [
                'idle',           // kosong/bera
                'preparing',      // persiapan lahan
                'planted',        // sudah ditanam
                'growing',        // masa pertumbuhan
                'ready_harvest',  // siap panen
                'harvesting',     // sedang dipanen
                'post_harvest',   // pasca panen
            ])->default('idle');
            $table->date('planted_at')->nullable();
            $table->date('expected_harvest')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
            $table->index(['tenant_id', 'status']);
        });

        // Activity log per plot (input pupuk, pestisida, pengairan, dll)
        Schema::create('farm_plot_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_plot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('activity_type', 30);  // planting, fertilizing, spraying, watering, weeding, harvesting, other
            $table->date('date');
            $table->string('description');
            $table->string('input_product')->nullable();           // nama produk input (pupuk, pestisida)
            $table->decimal('input_quantity', 10, 3)->default(0);  // jumlah input
            $table->string('input_unit', 20)->nullable();          // kg, liter, sak
            $table->decimal('cost', 15, 2)->default(0);            // biaya aktivitas
            $table->decimal('harvest_qty', 12, 3)->default(0);     // khusus harvesting
            $table->string('harvest_unit', 20)->nullable();
            $table->string('harvest_grade', 30)->nullable();       // A, B, C / premium, standar
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['farm_plot_id', 'date']);
            $table->index(['farm_plot_id', 'activity_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farm_plot_activities');
        Schema::dropIfExists('farm_plots');
    }
};
