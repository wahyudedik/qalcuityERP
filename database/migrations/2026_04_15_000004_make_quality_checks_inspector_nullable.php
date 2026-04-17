<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Makes quality_checks.inspector_id nullable so ManufacturingGenerator
     * can insert quality check records without providing an inspector_id.
     * The foreign key constraint to the users table is preserved.
     */
    public function up(): void
    {
        if (Schema::hasTable('quality_checks') && Schema::hasColumn('quality_checks', 'inspector_id')) {
            DB::statement("ALTER TABLE quality_checks MODIFY COLUMN inspector_id BIGINT UNSIGNED NULL");
        }
    }

    /**
     * Reverse the migrations.
     *
     * Reverts inspector_id back to NOT NULL.
     * Note: this will fail if any existing rows have NULL inspector_id.
     */
    public function down(): void
    {
        if (Schema::hasTable('quality_checks') && Schema::hasColumn('quality_checks', 'inspector_id')) {
            DB::statement("ALTER TABLE quality_checks MODIFY COLUMN inspector_id BIGINT UNSIGNED NOT NULL");
        }
    }
};
