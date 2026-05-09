<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('triage_assessments', 'assessment_time') && ! Schema::hasColumn('triage_assessments', 'assessment_date')) {
            Schema::table('triage_assessments', function (Blueprint $table) {
                $table->renameColumn('assessment_time', 'assessment_date');
            });
        }

        if (! Schema::hasColumn('triage_assessments', 'deleted_at')) {
            Schema::table('triage_assessments', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (! Schema::hasColumn('emergency_cases', 'tenant_id')) {
            Schema::table('emergency_cases', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
                $table->index('tenant_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('emergency_cases', 'tenant_id')) {
            Schema::table('emergency_cases', function (Blueprint $table) {
                $table->dropIndex(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }

        if (Schema::hasColumn('triage_assessments', 'deleted_at')) {
            Schema::table('triage_assessments', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('triage_assessments', 'assessment_date') && ! Schema::hasColumn('triage_assessments', 'assessment_time')) {
            Schema::table('triage_assessments', function (Blueprint $table) {
                $table->renameColumn('assessment_date', 'assessment_time');
            });
        }
    }
};
