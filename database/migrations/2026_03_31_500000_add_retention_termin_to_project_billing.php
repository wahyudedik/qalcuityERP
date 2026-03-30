<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add retention config
        Schema::table('project_billing_configs', function (Blueprint $table) {
            $table->decimal('retention_pct', 5, 2)->default(0)->after('fixed_price');       // 5%, 10%
            $table->decimal('contract_value', 15, 2)->default(0)->after('retention_pct');    // nilai kontrak
            $table->unsignedSmallInteger('retention_release_days')->default(90)->after('contract_value'); // hari setelah selesai
        });

        // Extend billing_type enum to include 'termin'
        // MySQL doesn't support ALTER ENUM easily, so we use string
        Schema::table('project_billing_configs', function (Blueprint $table) {
            $table->string('billing_type', 30)->default('time_material')->change();
        });

        // Add retention tracking to project_invoices
        Schema::table('project_invoices', function (Blueprint $table) {
            $table->string('billing_type', 30)->default('time_material')->change();
            $table->decimal('gross_amount', 15, 2)->default(0)->after('total_amount');       // before retention
            $table->decimal('retention_amount', 15, 2)->default(0)->after('gross_amount');    // held back
            $table->decimal('retention_released', 15, 2)->default(0)->after('retention_amount');
            $table->boolean('retention_released_flag')->default(false)->after('retention_released');
            $table->date('retention_release_date')->nullable()->after('retention_released_flag');
            $table->unsignedSmallInteger('termin_number')->nullable()->after('retention_release_date');
            $table->decimal('progress_pct', 5, 2)->default(0)->after('termin_number');       // progress at billing time
        });

        // Add retention fields to milestones
        Schema::table('project_milestones', function (Blueprint $table) {
            $table->decimal('retention_amount', 15, 2)->default(0)->after('percentage');
            $table->decimal('billed_amount', 15, 2)->default(0)->after('retention_amount');   // amount - retention
        });
    }

    public function down(): void
    {
        Schema::table('project_milestones', function (Blueprint $table) {
            $table->dropColumn(['retention_amount', 'billed_amount']);
        });

        Schema::table('project_invoices', function (Blueprint $table) {
            $table->dropColumn([
                'gross_amount', 'retention_amount', 'retention_released',
                'retention_released_flag', 'retention_release_date',
                'termin_number', 'progress_pct',
            ]);
        });

        Schema::table('project_billing_configs', function (Blueprint $table) {
            $table->dropColumn(['retention_pct', 'contract_value', 'retention_release_days']);
        });
    }
};
