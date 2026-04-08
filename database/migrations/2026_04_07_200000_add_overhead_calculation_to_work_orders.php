<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * BUG-MFG-003 FIX: Add overhead rate configuration and tracking
     */
    public function up(): void
    {
        // Add overhead configuration to work_orders
        Schema::table('work_orders', function (Blueprint $table) {
            // Overhead calculation method
            $table->string('overhead_method')->default('manual')->after('overhead_cost')
                ->comment('manual, work_center, percentage_of_labor, percentage_of_material');

            // Overhead rate configuration
            $table->decimal('overhead_rate', 10, 4)->default(0)->after('overhead_method')
                ->comment('Rate for overhead calculation (per hour or percentage)');

            // Auto-calculated overhead from operations
            $table->decimal('calculated_overhead', 15, 2)->nullable()->after('overhead_rate')
                ->comment('Auto-calculated overhead from work center operations');

            // Total operation hours for overhead calculation
            $table->decimal('total_operation_hours', 10, 2)->default(0)->after('calculated_overhead');
        });

        // Add overhead rate default to work_centers
        Schema::table('work_centers', function (Blueprint $table) {
            $table->decimal('overhead_rate_per_hour', 12, 2)->default(0)->after('cost_per_hour')
                ->comment('Overhead cost per hour (electricity, maintenance, depreciation, etc.)');

            $table->decimal('monthly_fixed_overhead', 15, 2)->default(0)->after('overhead_rate_per_hour')
                ->comment('Fixed monthly overhead (rent, insurance, etc.)');
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn([
                'overhead_method',
                'overhead_rate',
                'calculated_overhead',
                'total_operation_hours',
            ]);
        });

        Schema::table('work_centers', function (Blueprint $table) {
            $table->dropColumn([
                'overhead_rate_per_hour',
                'monthly_fixed_overhead',
            ]);
        });
    }
};
