<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds columns required by AgricultureGenerator::seedCropCycles() that are
     * missing from the first crop_cycles schema (2026_03_31_000008).
     * The second migration (2026_04_06_000010) is guarded with hasTable() and
     * gets skipped when the first migration already created the table.
     */
    public function up(): void
    {
        Schema::table('crop_cycles', function (Blueprint $table) {
            if (!Schema::hasColumn('crop_cycles', 'variety')) {
                $table->string('variety')->nullable();
            }
            if (!Schema::hasColumn('crop_cycles', 'area_hectares')) {
                $table->decimal('area_hectares', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('crop_cycles', 'field_location')) {
                $table->string('field_location')->nullable();
            }
            if (!Schema::hasColumn('crop_cycles', 'growth_stage')) {
                $table->string('growth_stage')->nullable();
            }
            if (!Schema::hasColumn('crop_cycles', 'estimated_yield_tons')) {
                $table->float('estimated_yield_tons')->nullable();
            }
            if (!Schema::hasColumn('crop_cycles', 'actual_yield_tons')) {
                $table->float('actual_yield_tons')->nullable();
            }
            if (!Schema::hasColumn('crop_cycles', 'status')) {
                $table->string('status')->default('active');
            }
            if (!Schema::hasColumn('crop_cycles', 'planting_date')) {
                $table->date('planting_date')->nullable();
            }
            if (!Schema::hasColumn('crop_cycles', 'expected_harvest_date')) {
                $table->date('expected_harvest_date')->nullable();
            }
            if (!Schema::hasColumn('crop_cycles', 'actual_harvest_date')) {
                $table->date('actual_harvest_date')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crop_cycles', function (Blueprint $table) {
            $columns = [
                'variety',
                'area_hectares',
                'field_location',
                'growth_stage',
                'estimated_yield_tons',
                'actual_yield_tons',
                'status',
                'planting_date',
                'expected_harvest_date',
                'actual_harvest_date',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('crop_cycles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
