<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Adds tenant_id to the patients table and converts global unique constraints
     * on medical_record_number and nik to per-tenant unique constraints.
     * This fixes HealthcareGenerator::seedPatients() which requires tenant_id.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('patients', 'tenant_id')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->foreignId('tenant_id')->after('id')->constrained('tenants')->onDelete('cascade');
                $table->index('tenant_id');
            });

            // Drop global unique constraints and replace with per-tenant unique constraints
            Schema::table('patients', function (Blueprint $table) {
                $table->dropUnique(['medical_record_number']);
                $table->dropUnique(['nik']);
                $table->unique(['tenant_id', 'medical_record_number']);
                $table->unique(['tenant_id', 'nik']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('patients', 'tenant_id')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->dropUnique(['tenant_id', 'medical_record_number']);
                $table->dropUnique(['tenant_id', 'nik']);
                $table->unique(['medical_record_number']);
                $table->unique(['nik']);
                $table->dropForeign(['tenant_id']);
                $table->dropIndex(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }
    }
};
