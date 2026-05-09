<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Note: This migration is a no-op because all tables (wards, beds, admissions,
     * bed_transfers, discharges, ward_rounds) were already created by migration:
     * 2026_04_08_1700001_create_inpatient_outpatient_er_pharmacy_tables
     */
    public function up(): void
    {
        // All tables already exist - nothing to do
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op - tables managed by other migration
    }
};
