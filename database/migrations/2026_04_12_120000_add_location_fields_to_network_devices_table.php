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
        Schema::table('network_devices', function (Blueprint $table) {
            // Add location name field
            if (! Schema::hasColumn('network_devices', 'location')) {
                $table->string('location')->nullable()->after('notes')
                    ->comment('Location name (e.g., "Tower A - Jakarta Selatan")');
            }

            // Add GPS coordinates
            if (! Schema::hasColumn('network_devices', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('location')
                    ->comment('Latitude coordinate (-90 to 90)');
            }
            if (! Schema::hasColumn('network_devices', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude')
                    ->comment('Longitude coordinate (-180 to 180)');
            }

            // Add coverage radius in meters
            if (! Schema::hasColumn('network_devices', 'coverage_radius')) {
                $table->integer('coverage_radius')->nullable()->after('longitude')
                    ->comment('Coverage radius in meters (1-50000)');
            }

            // Add indexes for performance
            $table->index(['latitude', 'longitude'], 'idx_coordinates');
            $table->index('coverage_radius', 'idx_coverage_radius');

            // Composite index for spatial queries (devices with coordinates)
            $table->index(['tenant_id', 'latitude', 'longitude'], 'idx_tenant_coordinates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('network_devices', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('idx_tenant_coordinates');
            $table->dropIndex('idx_coverage_radius');
            $table->dropIndex('idx_coordinates');

            // Then drop columns
            $table->dropColumn(['location', 'latitude', 'longitude', 'coverage_radius']);
        });
    }
};
