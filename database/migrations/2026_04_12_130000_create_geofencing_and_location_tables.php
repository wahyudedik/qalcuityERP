<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Geofencing zones table
        if (!Schema::hasTable('geofence_zones')) {
            Schema::create('geofence_zones', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('name'); // e.g., "Jakarta Operational Area"
                $table->text('description')->nullable();
                $table->enum('zone_type', ['circular', 'polygon'])->default('circular');
    
                // For circular zones
                $table->decimal('center_latitude', 10, 7)->nullable();
                $table->decimal('center_longitude', 10, 7)->nullable();
                $table->integer('radius_meters')->nullable(); // Radius for circular zones
    
                // For polygon zones (GeoJSON format)
                $table->json('polygon_coordinates')->nullable(); // [[lat, lng], [lat, lng], ...]
    
                $table->boolean('is_active')->default(true);
                $table->json('alert_settings')->nullable(); // Notification settings
                $table->timestamps();
                $table->softDeletes();
    
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->index(['tenant_id', 'is_active']);
            });
        }

        // Device geofence assignments
        if (!Schema::hasTable('device_geofence_assignments')) {
            Schema::create('device_geofence_assignments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('device_id');
                $table->unsignedBigInteger('zone_id');
                $table->enum('alert_type', ['enter', 'exit', 'both'])->default('both');
                $table->boolean('is_enabled')->default(true);
                $table->timestamps();
    
                $table->foreign('device_id')->references('id')->on('network_devices')->onDelete('cascade');
                $table->foreign('zone_id')->references('id')->on('geofence_zones')->onDelete('cascade');
                $table->unique(['device_id', 'zone_id']);
            });
        }

        // Geofence alerts log
        if (!Schema::hasTable('geofence_alerts')) {
            Schema::create('geofence_alerts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('device_id');
                $table->unsignedBigInteger('zone_id');
                $table->enum('event_type', ['enter', 'exit']);
                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);
                $table->integer('distance_from_center_meters')->nullable();
                $table->text('message')->nullable();
                $table->boolean('is_notified')->default(false);
                $table->timestamp('triggered_at');
                $table->timestamps();
    
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->foreign('device_id')->references('id')->on('network_devices')->onDelete('cascade');
                $table->foreign('zone_id')->references('id')->on('geofence_zones')->onDelete('cascade');
                $table->index(['tenant_id', 'triggered_at']);
                $table->index(['device_id', 'triggered_at']);
            });
        }

        // Location history for tracking device movement
        if (!Schema::hasTable('location_history')) {
            Schema::create('location_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('device_id');
                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);
                $table->integer('accuracy_meters')->nullable(); // GPS accuracy
                $table->integer('altitude_meters')->nullable();
                $table->decimal('speed_kmh', 5, 2)->nullable(); // Movement speed
                $table->decimal('heading_degrees', 5, 2)->nullable(); // Direction 0-360
                $table->string('source')->default('manual'); // manual, gps, api, mobile_app
                $table->json('metadata')->nullable(); // Additional data
                $table->timestamp('recorded_at');
                $table->timestamps();
    
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->foreign('device_id')->references('id')->on('network_devices')->onDelete('cascade');
                $table->index(['tenant_id', 'device_id', 'recorded_at']);
                $table->index(['device_id', 'recorded_at']);
            });
        }

        // Mobile device tracking for route history
        if (!Schema::hasTable('mobile_device_tracks')) {
            Schema::create('mobile_device_tracks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('device_id');
                $table->string('session_id')->nullable(); // Group locations by session
                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);
                $table->integer('accuracy_meters')->nullable();
                $table->decimal('battery_level')->nullable(); // Device battery %
                $table->string('network_type')->nullable(); // wifi, 4g, 5g, etc
                $table->json('route_metadata')->nullable();
                $table->timestamp('tracked_at');
                $table->timestamps();
    
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->foreign('device_id')->references('id')->on('network_devices')->onDelete('cascade');
                $table->index(['tenant_id', 'device_id', 'tracked_at']);
                $table->index(['session_id', 'tracked_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_device_tracks');
        Schema::dropIfExists('location_history');
        Schema::dropIfExists('geofence_alerts');
        Schema::dropIfExists('device_geofence_assignments');
        Schema::dropIfExists('geofence_zones');
    }
};
