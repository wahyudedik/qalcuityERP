<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Fixes remaining NOT NULL constraints that prevent generators from inserting data:
     *
     * 1. doctors.user_id — HealthcareGenerator::seedDoctors() does not pass user_id.
     *    The column is NOT NULL FK to users. Making it nullable allows the generator to work.
     *
     * 2. crop_cycles.farm_plot_id — AgricultureGenerator::seedCropCycles() does not pass
     *    farm_plot_id. The column is NOT NULL FK to farm_plots. Making it nullable allows
     *    the generator to insert crop cycles without a farm plot reference.
     *
     * 3. crop_cycles.number — The original schema requires a non-null number field.
     *    The generator does not pass this field. Making it nullable allows the generator to work.
     *
     * 4. farm_plots.rent_cost — AgricultureGenerator::seedFarmPlots() passes null for
     *    non-rented plots. The column is NOT NULL with default 0. Making it nullable allows
     *    the generator to insert farm plots without a rent cost.
     */
    public function up(): void
    {
        // Fix 1: Make doctors.user_id nullable
        if (Schema::hasTable('doctors') && Schema::hasColumn('doctors', 'user_id')) {
            DB::statement('ALTER TABLE doctors MODIFY COLUMN user_id BIGINT UNSIGNED NULL');
        }

        // Fix 2: Make crop_cycles.farm_plot_id nullable
        if (Schema::hasTable('crop_cycles') && Schema::hasColumn('crop_cycles', 'farm_plot_id')) {
            DB::statement('ALTER TABLE crop_cycles MODIFY COLUMN farm_plot_id BIGINT UNSIGNED NULL');
        }

        // Fix 3: Make crop_cycles.number nullable
        if (Schema::hasTable('crop_cycles') && Schema::hasColumn('crop_cycles', 'number')) {
            DB::statement('ALTER TABLE crop_cycles MODIFY COLUMN `number` VARCHAR(30) NULL');
        }

        // Fix 4: Make farm_plots.rent_cost nullable
        if (Schema::hasTable('farm_plots') && Schema::hasColumn('farm_plots', 'rent_cost')) {
            DB::statement('ALTER TABLE farm_plots MODIFY COLUMN rent_cost DECIMAL(15,2) NULL DEFAULT 0');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert doctors.user_id to NOT NULL (will fail if any rows have NULL user_id)
        if (Schema::hasTable('doctors') && Schema::hasColumn('doctors', 'user_id')) {
            DB::statement('ALTER TABLE doctors MODIFY COLUMN user_id BIGINT UNSIGNED NOT NULL');
        }

        // Revert crop_cycles.farm_plot_id to NOT NULL
        if (Schema::hasTable('crop_cycles') && Schema::hasColumn('crop_cycles', 'farm_plot_id')) {
            DB::statement('ALTER TABLE crop_cycles MODIFY COLUMN farm_plot_id BIGINT UNSIGNED NOT NULL');
        }

        // Revert crop_cycles.number to NOT NULL
        if (Schema::hasTable('crop_cycles') && Schema::hasColumn('crop_cycles', 'number')) {
            DB::statement('ALTER TABLE crop_cycles MODIFY COLUMN `number` VARCHAR(30) NOT NULL');
        }

        // Revert farm_plots.rent_cost to NOT NULL
        if (Schema::hasTable('farm_plots') && Schema::hasColumn('farm_plots', 'rent_cost')) {
            DB::statement('ALTER TABLE farm_plots MODIFY COLUMN rent_cost DECIMAL(15,2) NOT NULL DEFAULT 0');
        }
    }
};
