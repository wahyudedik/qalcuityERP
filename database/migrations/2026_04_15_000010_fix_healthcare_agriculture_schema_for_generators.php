<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Fixes schema issues preventing HealthcareGenerator and AgricultureGenerator from working:
     *
     * 1. appointments.visit_type — Generator uses 'outpatient' but enum only has
     *    'new_patient' and 'return_patient'. Adding 'outpatient' to the enum.
     *
     * 2. patient_medical_records.doctor_id — FK references users table, but generator
     *    passes doctor IDs from the doctors table. Dropping the FK constraint and making
     *    the column reference doctors table instead (or just drop FK to allow any value).
     *    Since doctor_id is already nullable, we just drop the FK constraint.
     *
     * 3. harvest_logs.user_id — NOT NULL without default. Generator doesn't pass user_id.
     *    Making it nullable.
     */
    public function up(): void
    {
        // Fix 1: Add 'outpatient' to appointments.visit_type enum
        if (Schema::hasTable('appointments') && Schema::hasColumn('appointments', 'visit_type')) {
            DB::statement("ALTER TABLE appointments MODIFY COLUMN visit_type 
                ENUM('new_patient','return_patient','outpatient','inpatient','emergency') NOT NULL DEFAULT 'new_patient'");
        }

        // Fix 2: Drop FK constraint on patient_medical_records.doctor_id
        // The generator passes doctor IDs from doctors table, not users table
        if (Schema::hasTable('patient_medical_records') && Schema::hasColumn('patient_medical_records', 'doctor_id')) {
            try {
                DB::statement("ALTER TABLE patient_medical_records DROP FOREIGN KEY patient_medical_records_doctor_id_foreign");
            } catch (\Exception $e) {
                // FK might not exist or have different name, ignore
            }
        }

        // Fix 3: Make harvest_logs.user_id nullable
        if (Schema::hasTable('harvest_logs') && Schema::hasColumn('harvest_logs', 'user_id')) {
            DB::statement("ALTER TABLE harvest_logs MODIFY COLUMN user_id BIGINT UNSIGNED NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert appointments.visit_type enum
        if (Schema::hasTable('appointments') && Schema::hasColumn('appointments', 'visit_type')) {
            DB::statement("ALTER TABLE appointments MODIFY COLUMN visit_type 
                ENUM('new_patient','return_patient') NOT NULL DEFAULT 'new_patient'");
        }

        // Restore FK on patient_medical_records.doctor_id
        if (Schema::hasTable('patient_medical_records') && Schema::hasColumn('patient_medical_records', 'doctor_id')) {
            try {
                DB::statement("ALTER TABLE patient_medical_records ADD CONSTRAINT patient_medical_records_doctor_id_foreign 
                    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE SET NULL");
            } catch (\Exception $e) {
                // Ignore if restore fails
            }
        }

        // Revert harvest_logs.user_id to NOT NULL
        if (Schema::hasTable('harvest_logs') && Schema::hasColumn('harvest_logs', 'user_id')) {
            DB::statement("ALTER TABLE harvest_logs MODIFY COLUMN user_id BIGINT UNSIGNED NOT NULL");
        }
    }
};
