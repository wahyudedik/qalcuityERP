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
        // Dairy Module - Milk Production Records
        if (!Schema::hasTable('dairy_milk_records')) {
            Schema::create('dairy_milk_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('livestock_herd_id')->nullable()->constrained('livestock_herds')->nullOnDelete();
                $table->string('animal_id')->nullable(); // Individual animal ID for dairy cows
                $table->date('record_date');
                $table->enum('milking_session', ['morning', 'afternoon', 'evening']);
                $table->decimal('milk_volume_liters', 8, 2)->default(0);
                $table->decimal('fat_percentage', 5, 2)->nullable(); // Milk fat %
                $table->decimal('protein_percentage', 5, 2)->nullable(); // Protein %
                $table->decimal('lactose_percentage', 5, 2)->nullable(); // Lactose %
                $table->integer('somatic_cell_count')->nullable(); // SCC - milk quality indicator
                $table->string('quality_grade')->nullable(); // A, B, C grade
                $table->text('notes')->nullable();
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['tenant_id', 'record_date']);
                $table->index('livestock_herd_id');
            });
        }

        // Dairy Module - Milking Parlor Sessions
        if (!Schema::hasTable('dairy_milking_sessions')) {
            Schema::create('dairy_milking_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('session_code')->unique(); // e.g., MS-20260407-001
                $table->date('session_date');
                $table->enum('session_type', ['morning', 'afternoon', 'evening']);
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->integer('total_animals_milked')->default(0);
                $table->decimal('total_milk_volume', 10, 2)->default(0);
                $table->decimal('average_milk_per_animal', 8, 2)->nullable();
                $table->string('operator_name')->nullable();
                $table->text('equipment_notes')->nullable();
                $table->text('issues')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['tenant_id', 'session_date']);
            });
        }

        // Poultry Module - Egg Production Records
        if (!Schema::hasTable('poultry_egg_production')) {
            Schema::create('poultry_egg_production', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('livestock_herd_id')->constrained('livestock_herds')->onDelete('cascade');
                $table->date('record_date');
                $table->integer('eggs_collected')->default(0);
                $table->integer('eggs_broken')->default(0);
                $table->integer('eggs_double_yolk')->default(0);
                $table->integer('eggs_small')->default(0); // Below standard size
                $table->integer('eggs_medium')->default(0);
                $table->integer('eggs_large')->default(0);
                $table->integer('eggs_extra_large')->default(0);
                $table->decimal('total_weight_kg', 8, 2)->nullable();
                $table->decimal('laying_rate_percentage', 5, 2)->nullable(); // % of hens laying
                $table->decimal('feed_consumed_kg', 8, 2)->nullable();
                $table->decimal('feed_conversion_ratio', 6, 3)->nullable(); // FCR
                $table->text('notes')->nullable();
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['tenant_id', 'record_date']);
                $table->index('livestock_herd_id');
                $table->unique(['livestock_herd_id', 'record_date']);
            });
        }

        // Poultry Module - Daily Flock Performance
        if (!Schema::hasTable('poultry_flock_performance')) {
            Schema::create('poultry_flock_performance', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('livestock_herd_id')->constrained('livestock_herds')->onDelete('cascade');
                $table->date('record_date');
                $table->integer('birds_alive')->default(0);
                $table->integer('mortality_count')->default(0);
                $table->decimal('mortality_rate_percentage', 5, 2)->nullable();
                $table->decimal('average_weight_kg', 6, 2)->nullable();
                $table->decimal('feed_consumed_kg', 8, 2)->default(0);
                $table->decimal('water_consumed_liters', 8, 2)->nullable();
                $table->decimal('average_daily_gain', 6, 3)->nullable(); // ADG
                $table->decimal('feed_conversion_ratio', 6, 3)->nullable(); // FCR
                $table->string('health_status')->default('healthy'); // healthy, sick, quarantine
                $table->text('observations')->nullable();
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['tenant_id', 'record_date']);
                $table->index('livestock_herd_id');
            });
        }

        // Breeding & Genetics - Breeding Records
        if (!Schema::hasTable('breeding_records')) {
            Schema::create('breeding_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('livestock_herd_id')->nullable()->constrained('livestock_herds')->nullOnDelete();
                $table->string('dam_id')->nullable(); // Mother/female ID
                $table->string('sire_id')->nullable(); // Father/male ID
                $table->date('mating_date');
                $table->enum('mating_type', ['natural', 'artificial_insemination', 'embryo_transfer']);
                $table->date('expected_due_date')->nullable();
                $table->date('actual_birth_date')->nullable();
                $table->integer('offspring_count')->nullable();
                $table->integer('live_births')->nullable();
                $table->integer('stillbirths')->nullable();
                $table->decimal('birth_weight_avg_kg', 6, 2)->nullable();
                $table->string('genetics_line')->nullable(); // Genetic line/breed
                $table->text('genetic_traits')->nullable(); // JSON array of traits
                $table->string('status')->default('pending'); // pending, pregnant, born, failed
                $table->text('notes')->nullable();
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['tenant_id', 'status']);
                $table->index('mating_date');
            });
        }

        // Breeding & Genetics - Animal Pedigree
        if (!Schema::hasTable('animal_pedigrees')) {
            Schema::create('animal_pedigrees', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('animal_id'); // Unique animal identifier
                $table->string('animal_name')->nullable();
                $table->string('breed');
                $table->date('birth_date');
                $table->enum('gender', ['male', 'female']);
                $table->string('dam_id')->nullable(); // Mother
                $table->string('sire_id')->nullable(); // Father
                $table->string('genetic_line')->nullable();
                $table->json('genetic_markers')->nullable(); // DNA markers, traits
                $table->decimal('birth_weight_kg', 6, 2)->nullable();
                $table->text('performance_data')->nullable(); // Growth rates, production data
                $table->string('registration_number')->nullable(); // Breed registry number
                $table->boolean('is_active')->default(true);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['tenant_id', 'animal_id']);
                $table->index(['dam_id', 'sire_id']);
            });
        }

        // Manure & Waste Management
        if (!Schema::hasTable('waste_management_logs')) {
            Schema::create('waste_management_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('livestock_herd_id')->nullable()->constrained('livestock_herds')->nullOnDelete();
                $table->date('collection_date');
                $table->enum('waste_type', ['manure_solid', 'manure_liquid', 'urine', 'bedding', 'mortality', 'other']);
                $table->decimal('quantity_kg', 10, 2)->default(0);
                $table->decimal('volume_liters', 10, 2)->nullable();
                $table->enum('disposal_method', ['composting', 'biogas', 'field_application', 'sale', 'disposal', 'storage']);
                $table->string('storage_location')->nullable();
                $table->date('processing_date')->nullable();
                $table->decimal('processed_quantity_kg', 10, 2)->nullable();
                $table->string('end_product')->nullable(); // e.g., compost, biogas
                $table->decimal('revenue_amount', 12, 2)->nullable(); // If sold
                $table->text('environmental_impact')->nullable(); // Notes on environmental considerations
                $table->text('notes')->nullable();
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['tenant_id', 'collection_date']);
                $table->index('waste_type');
            });
        }

        // Composting Management
        if (!Schema::hasTable('composting_batches')) {
            Schema::create('composting_batches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('batch_code')->unique(); // e.g., COMP-2026-001
                $table->date('start_date');
                $table->date('expected_end_date')->nullable();
                $table->date('actual_end_date')->nullable();
                $table->decimal('initial_weight_kg', 10, 2)->default(0);
                $table->decimal('current_weight_kg', 10, 2)->nullable();
                $table->decimal('final_weight_kg', 10, 2)->nullable();
                $table->decimal('moisture_percentage', 5, 2)->nullable();
                $table->decimal('temperature_celsius', 5, 2)->nullable();
                $table->decimal('ph_level', 4, 2)->nullable();
                $table->string('status')->default('active'); // active, curing, completed
                $table->decimal('quality_score', 4, 2)->nullable(); // 1-10 scale
                $table->text('ingredients')->nullable(); // JSON array
                $table->text('turning_schedule')->nullable(); // Turning/compost mixing schedule
                $table->text('notes')->nullable();
                $table->foreignId('managed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['tenant_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('composting_batches');
        Schema::dropIfExists('waste_management_logs');
        Schema::dropIfExists('animal_pedigrees');
        Schema::dropIfExists('breeding_records');
        Schema::dropIfExists('poultry_flock_performance');
        Schema::dropIfExists('poultry_egg_production');
        Schema::dropIfExists('dairy_milking_sessions');
        Schema::dropIfExists('dairy_milk_records');
    }
};
