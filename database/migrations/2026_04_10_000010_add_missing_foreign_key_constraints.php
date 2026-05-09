<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add missing foreign key constraints identified during audit.
     * Only adds FKs where they are genuinely missing.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // ==========================================
        // 1. SURGERY TABLES - Missing FKs
        // ==========================================

        if (Schema::hasTable('surgery_schedules')) {
            Schema::table('surgery_schedules', function (Blueprint $table) {
                $existingFks = collect(Schema::getForeignKeys('surgery_schedules'))
                    ->flatMap(fn ($fk) => $fk['columns'] ?? []);

                // surgeon_id -> doctors.id
                if (Schema::hasColumn('surgery_schedules', 'surgeon_id') && ! $existingFks->contains('surgeon_id')) {
                    try {
                        $table->foreign('surgeon_id')->references('id')->on('doctors')->onDelete('restrict');
                    } catch (Exception $e) {
                        Log::warning('Failed to add FK for surgery_schedules.surgeon_id: '.$e->getMessage());
                    }
                }

                // operating_room_id -> operating_rooms.id
                if (Schema::hasColumn('surgery_schedules', 'operating_room_id') && ! $existingFks->contains('operating_room_id')) {
                    try {
                        $table->foreign('operating_room_id')->references('id')->on('operating_rooms')->onDelete('restrict');
                    } catch (Exception $e) {
                        Log::warning('Failed to add FK for surgery_schedules.operating_room_id: '.$e->getMessage());
                    }
                }

                // anesthesiologist_id -> doctors.id
                if (Schema::hasColumn('surgery_schedules', 'anesthesiologist_id') && ! $existingFks->contains('anesthesiologist_id')) {
                    try {
                        $table->foreign('anesthesiologist_id')->references('id')->on('doctors')->onDelete('set null');
                    } catch (Exception $e) {
                        Log::warning('Failed to add FK for surgery_schedules.anesthesiologist_id: '.$e->getMessage());
                    }
                }
            });
        }

        if (Schema::hasTable('surgery_teams')) {
            Schema::table('surgery_teams', function (Blueprint $table) {
                $existingFks = collect(Schema::getForeignKeys('surgery_teams'))
                    ->flatMap(fn ($fk) => $fk['columns'] ?? []);

                // Handle different versions of surgery_teams table
                if (Schema::hasColumn('surgery_teams', 'staff_id') && ! $existingFks->contains('staff_id')) {
                    try {
                        $table->foreign('staff_id')->references('id')->on('users')->onDelete('restrict');
                    } catch (Exception $e) {
                        Log::warning('Failed to add FK for surgery_teams.staff_id: '.$e->getMessage());
                    }
                }

                if (Schema::hasColumn('surgery_teams', 'surgery_schedule_id') && ! $existingFks->contains('surgery_schedule_id')) {
                    try {
                        $table->foreign('surgery_schedule_id')->references('id')->on('surgery_schedules')->onDelete('cascade');
                    } catch (Exception $e) {
                        Log::warning('Failed to add FK for surgery_teams.surgery_schedule_id: '.$e->getMessage());
                    }
                }

                if (Schema::hasColumn('surgery_teams', 'doctor_id') && ! $existingFks->contains('doctor_id')) {
                    try {
                        $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('restrict');
                    } catch (Exception $e) {
                        Log::warning('Failed to add FK for surgery_teams.doctor_id: '.$e->getMessage());
                    }
                }
            });
        }

        // ==========================================
        // 2. LABORATORY TABLES - Missing FKs
        // ==========================================

        if (Schema::hasTable('lab_samples')) {
            Schema::table('lab_samples', function (Blueprint $table) {
                $existingFks = collect(Schema::getForeignKeys('lab_samples'))
                    ->flatMap(fn ($fk) => $fk['columns'] ?? []);

                // lab_order_id -> lab_orders.id
                if (Schema::hasColumn('lab_samples', 'lab_order_id') && ! $existingFks->contains('lab_order_id')) {
                    try {
                        $table->foreign('lab_order_id')->references('id')->on('lab_orders')->onDelete('cascade');
                    } catch (Exception $e) {
                        Log::warning('Failed to add FK for lab_samples.lab_order_id: '.$e->getMessage());
                    }
                }
            });
        }

        if (Schema::hasTable('lab_results')) {
            Schema::table('lab_results', function (Blueprint $table) {
                $existingFks = collect(Schema::getForeignKeys('lab_results'))
                    ->flatMap(fn ($fk) => $fk['columns'] ?? []);

                // Only add FK for columns that exist and don't have FK yet
                $fkMappings = [
                    'lab_order_id' => ['table' => 'lab_orders', 'onDelete' => 'cascade'],
                    'test_id' => ['table' => 'lab_test_catalogs', 'onDelete' => 'restrict'],
                    'performed_by' => ['table' => 'users', 'onDelete' => 'set null'],
                    'verified_by' => ['table' => 'users', 'onDelete' => 'set null'],
                ];

                foreach ($fkMappings as $column => $ref) {
                    if (Schema::hasColumn('lab_results', $column) && ! $existingFks->contains($column)) {
                        try {
                            $table->foreign($column)->references('id')->on($ref['table'])->onDelete($ref['onDelete']);
                        } catch (Exception $e) {
                            Log::warning("Failed to add FK for lab_results.{$column}: ".$e->getMessage());
                        }
                    }
                }
            });
        }

        if (Schema::hasTable('lab_result_details')) {
            Schema::table('lab_result_details', function (Blueprint $table) {
                $existingFks = collect(Schema::getForeignKeys('lab_result_details'))
                    ->flatMap(fn ($fk) => $fk['columns'] ?? []);

                if (Schema::hasColumn('lab_result_details', 'lab_result_id') && ! $existingFks->contains('lab_result_id')) {
                    try {
                        $table->foreign('lab_result_id')->references('id')->on('lab_results')->onDelete('cascade');
                    } catch (Exception $e) {
                        Log::warning('Failed to add FK for lab_result_details.lab_result_id: '.$e->getMessage());
                    }
                }
            });
        }

        // ==========================================
        // 3. RADIOLOGY TABLES - Missing FKs
        // ==========================================

        if (Schema::hasTable('radiology_orders')) {
            Schema::table('radiology_orders', function (Blueprint $table) {
                $existingFks = collect(Schema::getForeignKeys('radiology_orders'))
                    ->flatMap(fn ($fk) => $fk['columns'] ?? []);

                $fkMappings = [
                    'ordered_by' => ['table' => 'doctors', 'onDelete' => 'restrict'],
                    'radiologist_id' => ['table' => 'doctors', 'onDelete' => 'set null'],
                    'visit_id' => ['table' => 'patient_visits', 'onDelete' => 'set null'],
                ];

                foreach ($fkMappings as $column => $ref) {
                    if (Schema::hasColumn('radiology_orders', $column) && ! $existingFks->contains($column)) {
                        try {
                            $table->foreign($column)->references('id')->on($ref['table'])->onDelete($ref['onDelete']);
                        } catch (Exception $e) {
                            Log::warning("Failed to add FK for radiology_orders.{$column}: ".$e->getMessage());
                        }
                    }
                }
            });
        }

        if (Schema::hasTable('radiology_results')) {
            Schema::table('radiology_results', function (Blueprint $table) {
                $existingFks = collect(Schema::getForeignKeys('radiology_results'))
                    ->flatMap(fn ($fk) => $fk['columns'] ?? []);

                $fkMappings = [
                    'reported_by' => ['table' => 'doctors', 'onDelete' => 'restrict'],
                    'verified_by' => ['table' => 'doctors', 'onDelete' => 'set null'],
                ];

                foreach ($fkMappings as $column => $ref) {
                    if (Schema::hasColumn('radiology_results', $column) && ! $existingFks->contains($column)) {
                        try {
                            $table->foreign($column)->references('id')->on($ref['table'])->onDelete($ref['onDelete']);
                        } catch (Exception $e) {
                            Log::warning("Failed to add FK for radiology_results.{$column}: ".$e->getMessage());
                        }
                    }
                }
            });
        }

        if (Schema::hasTable('radiology_images')) {
            Schema::table('radiology_images', function (Blueprint $table) {
                $existingFks = collect(Schema::getForeignKeys('radiology_images'))
                    ->flatMap(fn ($fk) => $fk['columns'] ?? []);

                if (Schema::hasColumn('radiology_images', 'radiology_exam_id') && ! $existingFks->contains('radiology_exam_id')) {
                    try {
                        $table->foreign('radiology_exam_id')->references('id')->on('radiology_exams')->onDelete('restrict');
                    } catch (Exception $e) {
                        Log::warning('Failed to add FK for radiology_images.radiology_exam_id: '.$e->getMessage());
                    }
                }
            });
        }

        if (Schema::hasTable('pacs_studies')) {
            Schema::table('pacs_studies', function (Blueprint $table) {
                $existingFks = collect(Schema::getForeignKeys('pacs_studies'))
                    ->flatMap(fn ($fk) => $fk['columns'] ?? []);

                $fkMappings = [
                    'radiology_exam_id' => ['table' => 'radiology_exams', 'onDelete' => 'restrict'],
                    'referring_doctor_id' => ['table' => 'doctors', 'onDelete' => 'set null'],
                    'radiologist_id' => ['table' => 'doctors', 'onDelete' => 'set null'],
                    'patient_visit_id' => ['table' => 'patient_visits', 'onDelete' => 'set null'],
                ];

                foreach ($fkMappings as $column => $ref) {
                    if (Schema::hasColumn('pacs_studies', $column) && ! $existingFks->contains($column)) {
                        try {
                            $table->foreign($column)->references('id')->on($ref['table'])->onDelete($ref['onDelete']);
                        } catch (Exception $e) {
                            Log::warning("Failed to add FK for pacs_studies.{$column}: ".$e->getMessage());
                        }
                    }
                }
            });
        }

        // ==========================================
        // 4. TELECONSULTATION TABLES - Missing FKs
        // ==========================================

        if (Schema::hasTable('teleconsultations')) {
            Schema::table('teleconsultations', function (Blueprint $table) {
                $existingFks = collect(Schema::getForeignKeys('teleconsultations'))
                    ->flatMap(fn ($fk) => $fk['columns'] ?? []);

                if (Schema::hasColumn('teleconsultations', 'doctor_id') && ! $existingFks->contains('doctor_id')) {
                    try {
                        $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('restrict');
                    } catch (Exception $e) {
                        Log::warning('Failed to add FK for teleconsultations.doctor_id: '.$e->getMessage());
                    }
                }

                if (Schema::hasColumn('teleconsultations', 'visit_id') && ! $existingFks->contains('visit_id')) {
                    try {
                        $table->foreign('visit_id')->references('id')->on('patient_visits')->onDelete('set null');
                    } catch (Exception $e) {
                        Log::warning('Failed to add FK for teleconsultations.visit_id: '.$e->getMessage());
                    }
                }
            });
        }

        // Helper function to add FK if missing
        $addFkIfMissing = function ($table, $column, $refTable, $onDelete = 'restrict') {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                return;
            }

            $existingFks = collect(Schema::getForeignKeys($table))->flatMap(fn ($fk) => $fk['columns'] ?? []);

            if (! $existingFks->contains($column)) {
                try {
                    Schema::table($table, function (Blueprint $table) use ($column, $refTable, $onDelete) {
                        $table->foreign($column)->references('id')->on($refTable)->onDelete($onDelete);
                    });
                } catch (Exception $e) {
                    Log::warning("Failed to add FK for {$table}.{$column}: ".$e->getMessage());
                }
            }
        };

        // telemedicine_prescriptions
        $addFkIfMissing('telemedicine_prescriptions', 'teleconsultation_id', 'teleconsultations', 'cascade');
        $addFkIfMissing('telemedicine_prescriptions', 'doctor_id', 'doctors', 'restrict');
        $addFkIfMissing('telemedicine_prescriptions', 'pharmacy_id', 'pharmacies', 'set null');

        // teleconsultation_recordings
        $addFkIfMissing('teleconsultation_recordings', 'teleconsultation_id', 'teleconsultations', 'cascade');
        $addFkIfMissing('teleconsultation_recordings', 'doctor_id', 'doctors', 'set null');

        // teleconsultation_payments
        $addFkIfMissing('teleconsultation_payments', 'teleconsultation_id', 'teleconsultations', 'cascade');

        // teleconsultation_feedbacks
        $addFkIfMissing('teleconsultation_feedbacks', 'teleconsultation_id', 'teleconsultations', 'cascade');
        $addFkIfMissing('teleconsultation_feedbacks', 'doctor_id', 'doctors', 'restrict');

        // ==========================================
        // 5. MEDICAL EQUIPMENT TABLES
        // ==========================================

        $addFkIfMissing('medical_equipment', 'department_id', 'departments', 'set null');
        $addFkIfMissing('equipment_maintenance_logs', 'equipment_id', 'medical_equipment', 'cascade');
        $addFkIfMissing('equipment_maintenance_logs', 'technician_id', 'users', 'set null');
        $addFkIfMissing('or_utilization_logs', 'operating_room_id', 'operating_rooms', 'cascade');
        $addFkIfMissing('or_utilization_logs', 'surgery_schedule_id', 'surgery_schedules', 'set null');

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        $dropFk = function ($table, $columns) {
            if (! Schema::hasTable($table)) {
                return;
            }

            Schema::table($table, function (Blueprint $table) use ($columns) {
                foreach ($columns as $column) {
                    try {
                        $table->dropForeign([$column]);
                    } catch (Exception $e) {
                        // Ignore if FK doesn't exist
                    }
                }
            });
        };

        // Surgery tables
        $dropFk('surgery_schedules', ['surgeon_id', 'operating_room_id', 'anesthesiologist_id']);
        $dropFk('surgery_teams', ['staff_id', 'surgery_schedule_id', 'doctor_id']);

        // Lab tables
        $dropFk('lab_samples', ['lab_order_id']);
        $dropFk('lab_results', ['lab_order_id', 'test_id', 'performed_by', 'verified_by']);
        $dropFk('lab_result_details', ['lab_result_id']);

        // Radiology tables
        $dropFk('radiology_orders', ['ordered_by', 'radiologist_id', 'visit_id']);
        $dropFk('radiology_results', ['reported_by', 'verified_by']);
        $dropFk('radiology_images', ['radiology_exam_id']);
        $dropFk('pacs_studies', ['radiology_exam_id', 'referring_doctor_id', 'radiologist_id', 'patient_visit_id']);

        // Teleconsultation tables
        $dropFk('teleconsultations', ['doctor_id', 'visit_id']);
        $dropFk('telemedicine_prescriptions', ['teleconsultation_id', 'doctor_id', 'pharmacy_id']);
        $dropFk('teleconsultation_recordings', ['teleconsultation_id', 'doctor_id']);
        $dropFk('teleconsultation_payments', ['teleconsultation_id']);
        $dropFk('teleconsultation_feedbacks', ['teleconsultation_id', 'doctor_id']);

        // Equipment tables
        $dropFk('medical_equipment', ['department_id']);
        $dropFk('equipment_maintenance_logs', ['equipment_id', 'technician_id']);
        $dropFk('or_utilization_logs', ['operating_room_id', 'surgery_schedule_id']);

        Schema::enableForeignKeyConstraints();
    }
};
