<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mix_design_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('mix_design_id')->constrained('concrete_mix_designs')->onDelete('cascade');
            $table->integer('version_number');
            $table->string('grade');
            $table->string('name');
            $table->decimal('target_strength', 8, 2);
            $table->string('strength_unit')->default('K');
            $table->decimal('slump_min', 5, 1)->nullable();
            $table->decimal('slump_max', 5, 1)->nullable();
            $table->decimal('water_cement_ratio', 5, 2);
            $table->decimal('cement_kg', 10, 2);
            $table->decimal('water_liter', 10, 2);
            $table->decimal('fine_agg_kg', 10, 2);
            $table->decimal('coarse_agg_kg', 10, 2);
            $table->decimal('admixture_liter', 10, 3)->default(0);
            $table->string('cement_type')->nullable();
            $table->string('agg_max_size')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->text('change_reason');
            $table->foreignId('changed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->json('snapshot_data')->nullable();
            $table->timestamps();

            $table->unique(['mix_design_id', 'version_number']);
            $table->index(['tenant_id', 'mix_design_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mix_design_versions');
    }
};
