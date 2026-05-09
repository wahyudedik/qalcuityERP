<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('crop_cycles')) {
            Schema::create('crop_cycles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('farm_plot_id')->constrained()->cascadeOnDelete();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('number', 30);                          // CC-A1-2026-01
                $table->string('crop_name');                            // Padi IR64, Jagung Hibrida
                $table->string('crop_variety')->nullable();             // varietas
                $table->string('season')->nullable();                   // Musim Tanam 1, MT2, Gadu, Rendeng

                // Phases with planned & actual dates
                $table->date('plan_prep_start')->nullable();            // rencana mulai olah tanah
                $table->date('plan_plant_date')->nullable();            // rencana tanam
                $table->date('plan_harvest_date')->nullable();          // rencana panen
                $table->date('actual_prep_start')->nullable();
                $table->date('actual_plant_date')->nullable();
                $table->date('actual_harvest_date')->nullable();
                $table->date('actual_end_date')->nullable();            // selesai pasca-panen

                $table->enum('phase', [
                    'planning',       // direncanakan
                    'land_prep',      // persiapan lahan
                    'planting',       // penanaman
                    'vegetative',     // pertumbuhan vegetatif
                    'generative',     // pertumbuhan generatif (berbunga/berbuah)
                    'harvest',        // panen
                    'post_harvest',   // pasca panen
                    'completed',      // selesai
                    'cancelled',
                ])->default('planning');

                // Targets & actuals
                $table->decimal('target_yield_qty', 12, 3)->default(0);  // target panen (kg/ton)
                $table->string('target_yield_unit', 20)->default('kg');
                $table->decimal('actual_yield_qty', 12, 3)->default(0);
                $table->decimal('estimated_budget', 15, 2)->default(0);
                $table->decimal('actual_cost', 15, 2)->default(0);

                // Seed/input info
                $table->decimal('seed_quantity', 10, 3)->default(0);
                $table->string('seed_unit', 20)->nullable();
                $table->string('seed_source')->nullable();              // asal bibit

                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'phase']);
                $table->index(['farm_plot_id', 'phase']);
            });
        }

        // Link activities to crop cycle
        Schema::table('farm_plot_activities', function (Blueprint $table) {
            if (! Schema::hasColumn('farm_plot_activities', 'crop_cycle_id')) {
                $table->foreignId('crop_cycle_id')->nullable()->after('farm_plot_id')
                    ->constrained('crop_cycles')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('farm_plot_activities', function (Blueprint $table) {
            $table->dropForeign(['crop_cycle_id']);
            $table->dropColumn('crop_cycle_id');
        });
        Schema::dropIfExists('crop_cycles');
    }
};
