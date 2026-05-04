<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('concrete_mix_designs')) {
            Schema::create('concrete_mix_designs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('grade', 20);                          // K-225, K-300, fc25, fc30
                $table->string('name');                                // "Beton Mutu K-300"
                $table->decimal('target_strength', 8, 2)->default(0); // MPa (fc') or kg/cm² (K)
                $table->string('strength_unit', 10)->default('K');     // K (kg/cm²) or fc (MPa)
                $table->decimal('slump_min', 5, 1)->default(8);       // cm
                $table->decimal('slump_max', 5, 1)->default(12);      // cm
                $table->decimal('water_cement_ratio', 4, 2)->default(0.50);
    
                // Komposisi per 1 m³ beton
                $table->decimal('cement_kg', 8, 2)->default(0);       // Semen (kg)
                $table->decimal('water_liter', 8, 2)->default(0);     // Air (liter)
                $table->decimal('fine_agg_kg', 8, 2)->default(0);     // Agregat halus / Pasir (kg)
                $table->decimal('coarse_agg_kg', 8, 2)->default(0);   // Agregat kasar / Kerikil/Split (kg)
                $table->decimal('admixture_liter', 8, 3)->default(0); // Admixture/Additive (liter)
    
                $table->string('cement_type', 50)->default('PCC');     // PCC, OPC, PPC
                $table->string('agg_max_size', 20)->default('20mm');   // Ukuran maks agregat kasar
                $table->boolean('is_standard')->default(false);        // template standar vs custom
                $table->boolean('is_active')->default(true);
                $table->foreignId('bom_id')->nullable()->constrained('boms')->nullOnDelete(); // linked BOM
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->index(['tenant_id', 'grade']);
                $table->index(['tenant_id', 'is_active']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('concrete_mix_designs');
    }
};
