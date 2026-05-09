<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds tenant_id to the doctors table and converts global unique constraints
     * on doctor_number and license_number to per-tenant unique constraints.
     * This fixes HealthcareGenerator::seedDoctors() which requires tenant_id.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('doctors', 'tenant_id')) {
            Schema::table('doctors', function (Blueprint $table) {
                if (! Schema::hasColumn('doctors', 'tenant_id')) {
                    $table->foreignId('tenant_id')->after('id')->constrained('tenants')->onDelete('cascade');
                }
                $table->index('tenant_id');
            });

            // Drop global unique constraints and replace with per-tenant unique constraints
            Schema::table('doctors', function (Blueprint $table) {
                $table->dropUnique(['doctor_number']);
                $table->dropUnique(['license_number']);
                $table->unique(['tenant_id', 'doctor_number']);
                $table->unique(['tenant_id', 'license_number']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('doctors', 'tenant_id')) {
            Schema::table('doctors', function (Blueprint $table) {
                $table->dropUnique(['tenant_id', 'doctor_number']);
                $table->dropUnique(['tenant_id', 'license_number']);
                $table->unique(['doctor_number']);
                $table->unique(['license_number']);
                $table->dropForeign(['tenant_id']);
                $table->dropIndex(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }
    }
};
