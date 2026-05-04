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
        // ==========================================
        // COLD CHAIN MANAGEMENT
        // ==========================================

        if (!Schema::hasTable('cold_storage_units')) {
            Schema::create('cold_storage_units', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->string('unit_code')->unique();
                $table->string('name');
                $table->string('type')->default('warehouse'); // warehouse, transport, display
                $table->decimal('capacity', 10, 2)->comment('Capacity in cubic meters');
                $table->decimal('current_temperature', 5, 2)->nullable()->comment('Current temperature in Celsius');
                $table->decimal('min_temperature', 5, 2)->default(-18)->comment('Minimum safe temperature');
                $table->decimal('max_temperature', 5, 2)->default(-15)->comment('Maximum safe temperature');
                $table->string('location')->nullable();
                $table->string('sensor_id')->nullable()->comment('IoT sensor identifier');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['tenant_id', 'is_active']);
            });
        }

        if (!Schema::hasTable('temperature_logs')) {
            Schema::create('temperature_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->foreignId('cold_storage_unit_id')->constrained('cold_storage_units')->onDelete('cascade');
                $table->foreignId('product_batch_id')->nullable()->constrained('product_batches')->onDelete('set null');
                $table->decimal('temperature', 5, 2)->comment('Recorded temperature in Celsius');
                $table->decimal('humidity', 5, 2)->nullable()->comment('Relative humidity percentage');
                $table->string('sensor_id')->nullable();
                $table->string('recorded_by')->nullable()->comment('manual or auto (sensor)');
                $table->timestamp('recorded_at');
                $table->timestamps();

                $table->index(['tenant_id', 'recorded_at']);
                $table->index(['cold_storage_unit_id', 'recorded_at']);
            });
        }

        if (!Schema::hasTable('cold_chain_alerts')) {
            Schema::create('cold_chain_alerts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->foreignId('cold_storage_unit_id')->constrained('cold_storage_units')->onDelete('cascade');
                $table->foreignId('temperature_log_id')->nullable()->constrained('temperature_logs')->onDelete('set null');
                $table->string('alert_type')->comment('threshold_breach, sensor_failure, power_outage');
                $table->string('severity')->default('warning'); // warning, critical, emergency
                $table->text('message');
                $table->decimal('recorded_temperature', 5, 2)->nullable();
                $table->decimal('threshold_min', 5, 2)->nullable();
                $table->decimal('threshold_max', 5, 2)->nullable();
                $table->boolean('is_acknowledged')->default(false);
                $table->foreignId('acknowledged_by_user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('acknowledged_at')->nullable();
                $table->text('resolution_notes')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'is_acknowledged']);
                $table->index(['severity', 'created_at']);
            });
        }

        if (!Schema::hasTable('refrigerated_transports')) {
            Schema::create('refrigerated_transports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->string('vehicle_number')->unique();
                $table->string('vehicle_type')->default('truck'); // truck, van, container
                $table->decimal('capacity', 10, 2)->comment('Capacity in cubic meters');
                $table->decimal('min_temperature', 5, 2)->default(-18);
                $table->decimal('max_temperature', 5, 2)->default(-15);
                $table->string('sensor_id')->nullable();
                $table->string('driver_name')->nullable();
                $table->string('driver_phone')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['tenant_id', 'is_active']);
            });
        }

        // ==========================================
        // FISH SPECIES & QUALITY GRADING
        // ==========================================

        if (!Schema::hasTable('fish_species')) {
            Schema::create('fish_species', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->string('species_code')->unique();
                $table->string('common_name');
                $table->string('scientific_name')->nullable();
                $table->string('category')->default('marine'); // marine, freshwater, anadromous
                $table->string('family')->nullable();
                $table->decimal('avg_weight', 10, 2)->nullable()->comment('Average weight in kg');
                $table->decimal('max_weight', 10, 2)->nullable()->comment('Maximum weight in kg');
                $table->decimal('market_price_per_kg', 10, 2)->nullable();
                $table->string('preferred_habitat')->nullable();
                $table->json('characteristics')->nullable()->comment('Color, shape, features');
                $table->text('description')->nullable();
                $table->boolean('is_endangered')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['tenant_id', 'is_active']);
                $table->index('category');
            });
        }

        if (!Schema::hasTable('quality_grades')) {
            Schema::create('quality_grades', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->string('grade_code')->unique();
                $table->string('grade_name');
                $table->integer('rank')->comment('Higher is better');
                $table->text('criteria')->nullable()->comment('Quality criteria description');
                $table->decimal('min_freshness_score', 3, 2)->nullable();
                $table->decimal('price_multiplier', 4, 2)->default(1.00);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['tenant_id', 'is_active']);
            });
        }

        // ==========================================
        // FISHING TRIP & CATCH LOGGING
        // ==========================================

        if (!Schema::hasTable('fishing_vessels')) {
            Schema::create('fishing_vessels', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->string('vessel_name');
                $table->string('registration_number')->unique();
                $table->string('vessel_type')->default('fishing_boat'); // fishing_boat, trawler, longliner
                $table->decimal('gross_tonnage', 10, 2)->nullable();
                $table->integer('crew_capacity')->default(0);
                $table->decimal('fuel_capacity', 10, 2)->nullable()->comment('In liters');
                $table->decimal('storage_capacity', 10, 2)->nullable()->comment('Fish storage in kg');
                $table->string('home_port')->nullable();
                $table->date('license_expiry_date')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['tenant_id', 'is_active']);
            });
        }

        if (!Schema::hasTable('fishing_zones')) {
            Schema::create('fishing_zones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->string('zone_code')->unique();
                $table->string('zone_name');
                $table->text('coordinates')->nullable()->comment('JSON polygon coordinates');
                $table->decimal('area_size', 10, 2)->nullable()->comment('Area in square kilometers');
                $table->string('water_type')->default('sea'); // sea, river, lake
                $table->json('allowed_species')->nullable()->comment('Array of species IDs');
                $table->decimal('quota_limit', 12, 2)->nullable()->comment('Quota in kg per season');
                $table->date('season_start')->nullable();
                $table->date('season_end')->nullable();
                $table->text('regulations')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['tenant_id', 'is_active']);
            });
        }

        if (!Schema::hasTable('fishing_trips')) {
            Schema::create('fishing_trips', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->foreignId('vessel_id')->constrained('fishing_vessels')->onDelete('cascade');
                $table->foreignId('captain_id')->constrained('employees')->onDelete('cascade');
                $table->foreignId('fishing_zone_id')->nullable()->constrained('fishing_zones')->onDelete('set null');
                $table->string('trip_number')->unique();
                $table->timestamp('departure_time');
                $table->timestamp('return_time')->nullable();
                $table->string('status')->default('planned'); // planned, departed, fishing, returning, completed, cancelled
                $table->decimal('fuel_consumed', 10, 2)->nullable()->comment('In liters');
                $table->decimal('total_catch_weight', 12, 2)->default(0)->comment('Total catch in kg');
                $table->decimal('latitude', 10, 8)->nullable()->comment('Last known position');
                $table->decimal('longitude', 11, 8)->nullable()->comment('Last known position');
                $table->text('weather_conditions')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'status']);
                $table->index(['vessel_id', 'departure_time']);
            });
        }

        if (!Schema::hasTable('fishing_trip_crew')) {
            Schema::create('fishing_trip_crew', function (Blueprint $table) {
                $table->id();
                $table->foreignId('fishing_trip_id')->constrained('fishing_trips')->onDelete('cascade');
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->string('role')->default('crew'); // captain, first_mate, crew, engineer
                $table->timestamps();

                $table->unique(['fishing_trip_id', 'employee_id']);
            });
        }

        if (!Schema::hasTable('catch_logs')) {
            Schema::create('catch_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->foreignId('fishing_trip_id')->constrained('fishing_trips')->onDelete('cascade');
                $table->foreignId('species_id')->constrained('fish_species')->onDelete('cascade');
                $table->foreignId('grade_id')->nullable()->constrained('quality_grades')->onDelete('set null');
                $table->decimal('quantity', 10, 2)->comment('Number of fish');
                $table->decimal('total_weight', 12, 2)->comment('Total weight in kg');
                $table->decimal('average_weight', 10, 2)->nullable()->comment('Average weight per fish in kg');
                $table->decimal('freshness_score', 3, 2)->nullable()->comment('Score 0-10');
                $table->timestamp('caught_at');
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->string('catch_method')->nullable()->comment('net, line, trap, etc.');
                $table->decimal('depth', 8, 2)->nullable()->comment('Depth in meters');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'caught_at']);
                $table->index(['fishing_trip_id', 'species_id']);
            });
        }

        // ==========================================
        // FISH SPECIES & QUALITY GRADING (Already created above)
        // ==========================================
        // fish_species and quality_grades tables are already created earlier
        // to avoid foreign key reference errors in catch_logs

        if (!Schema::hasTable('freshness_assessments')) {
            Schema::create('freshness_assessments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->foreignId('catch_log_id')->constrained('catch_logs')->onDelete('cascade');
                $table->decimal('overall_score', 3, 2)->comment('Overall freshness score 0-10');
                $table->decimal('eye_clarity', 3, 2)->nullable();
                $table->decimal('gill_color', 3, 2)->nullable();
                $table->decimal('skin_firmness', 3, 2)->nullable();
                $table->decimal('odor_score', 3, 2)->nullable();
                $table->string('assessed_by_type')->default('visual'); // visual, chemical, electronic
                $table->foreignId('assessor_id')->nullable()->constrained('employees')->onDelete('set null');
                $table->timestamp('assessed_at');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'assessed_at']);
            });
        }

        // ==========================================
        // AQUACULTURE ENHANCEMENTS
        // ==========================================

        if (!Schema::hasTable('aquaculture_ponds')) {
            Schema::create('aquaculture_ponds', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->string('pond_code')->unique();
                $table->string('pond_name');
                $table->decimal('surface_area', 10, 2)->comment('Area in square meters');
                $table->decimal('depth', 8, 2)->comment('Average depth in meters');
                $table->decimal('volume', 12, 2)->comment('Volume in cubic meters');
                $table->string('pond_type')->default('earthen'); // earthen, concrete, tarpaulin, cage
                $table->string('water_source')->default('natural'); // natural, well, pumped
                $table->decimal('current_stock', 12, 2)->default(0)->comment('Current stock count');
                $table->decimal('carrying_capacity', 12, 2)->comment('Maximum carrying capacity');
                $table->foreignId('current_species_id')->nullable()->constrained('fish_species')->onDelete('set null');
                $table->date('stocking_date')->nullable();
                $table->date('expected_harvest_date')->nullable();
                $table->string('status')->default('empty'); // empty, stocked, growing, ready_harvest, maintenance
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['tenant_id', 'status']);
            });
        }

        if (!Schema::hasTable('water_quality_logs')) {
            Schema::create('water_quality_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->foreignId('pond_id')->nullable()->constrained('aquaculture_ponds')->onDelete('cascade');
                $table->foreignId('fishing_zone_id')->nullable()->constrained('fishing_zones')->onDelete('cascade');
                $table->decimal('ph_level', 4, 2)->nullable()->comment('pH level (0-14)');
                $table->decimal('dissolved_oxygen', 5, 2)->nullable()->comment('DO in mg/L');
                $table->decimal('temperature', 5, 2)->nullable()->comment('Water temperature in Celsius');
                $table->decimal('salinity', 5, 2)->nullable()->comment('Salinity in ppt');
                $table->decimal('ammonia', 5, 2)->nullable()->comment('Ammonia in mg/L');
                $table->decimal('nitrite', 5, 2)->nullable()->comment('Nitrite in mg/L');
                $table->decimal('nitrate', 5, 2)->nullable()->comment('Nitrate in mg/L');
                $table->decimal('turbidity', 5, 2)->nullable()->comment('Turbidity in NTU');
                $table->string('measurement_method')->default('manual'); // manual, sensor, lab_test
                $table->foreignId('measured_by_user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('measured_at');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'measured_at']);
                $table->index(['pond_id', 'measured_at']);
            });
        }

        if (!Schema::hasTable('feeding_schedules')) {
            Schema::create('feeding_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->foreignId('pond_id')->constrained('aquaculture_ponds')->onDelete('cascade');
                $table->foreignId('feed_product_id')->constrained('products')->onDelete('cascade');
                $table->date('schedule_date');
                $table->time('feeding_time');
                $table->decimal('planned_quantity', 10, 2)->comment('Planned feed quantity in kg');
                $table->decimal('actual_quantity', 10, 2)->nullable()->comment('Actual feed quantity used in kg');
                $table->string('status')->default('scheduled'); // scheduled, completed, skipped
                $table->foreignId('fed_by_user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('completed_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'schedule_date']);
                $table->index(['pond_id', 'schedule_date']);
            });
        }

        if (!Schema::hasTable('mortality_logs')) {
            Schema::create('mortality_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->foreignId('pond_id')->nullable()->constrained('aquaculture_ponds')->onDelete('cascade');
                $table->foreignId('fishing_trip_id')->nullable()->constrained('fishing_trips')->onDelete('cascade');
                $table->integer('count')->comment('Number of deaths');
                $table->decimal('total_weight', 10, 2)->nullable()->comment('Total weight in kg');
                $table->string('cause_of_death')->nullable()->comment('disease, predation, oxygen_depletion, etc.');
                $table->text('symptoms')->nullable();
                $table->text('action_taken')->nullable();
                $table->foreignId('reported_by_user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('reported_at');
                $table->timestamps();
    
                $table->index(['tenant_id', 'reported_at']);
                $table->index(['pond_id', 'reported_at']);
            });
        }

        // ==========================================
        // EXPORT DOCUMENTATION
        // ==========================================

        if (!Schema::hasTable('export_permits')) {
            Schema::create('export_permits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->string('permit_number')->unique();
                $table->string('permit_type')->default('general'); // general, species_specific, seasonal
                $table->string('destination_country');
                $table->text('destination_address')->nullable();
                $table->date('issue_date');
                $table->date('expiry_date');
                $table->string('issuing_authority');
                $table->decimal('authorized_quantity', 12, 2)->nullable()->comment('Authorized export quantity in kg');
                $table->json('authorized_species')->nullable()->comment('Array of authorized species IDs');
                $table->string('status')->default('active'); // active, expired, revoked, suspended
                $table->text('conditions')->nullable();
                $table->string('document_path')->nullable();
                $table->timestamps();
    
                $table->index(['tenant_id', 'status']);
                $table->index(['expiry_date']);
            });
        }

        if (!Schema::hasTable('health_certificates')) {
            Schema::create('health_certificates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->string('certificate_number')->unique();
                $table->foreignId('product_batch_id')->nullable()->constrained('product_batches')->onDelete('set null');
                $table->foreignId('catch_log_id')->nullable()->constrained('catch_logs')->onDelete('set null');
                $table->string('certificate_type')->default('health'); // health, sanitary, veterinary
                $table->date('inspection_date');
                $table->date('issue_date');
                $table->date('expiry_date');
                $table->string('issued_by');
                $table->string('issuing_authority');
                $table->text('inspection_results')->nullable();
                $table->text('certifications')->nullable();
                $table->string('status')->default('valid'); // valid, expired, revoked
                $table->string('document_path')->nullable();
                $table->timestamps();
    
                $table->index(['tenant_id', 'status']);
                $table->index(['expiry_date']);
            });
        }

        if (!Schema::hasTable('customs_declarations')) {
            Schema::create('customs_declarations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->string('declaration_number')->unique();
                $table->foreignId('shipment_id')->nullable()->constrained('shipments')->onDelete('set null');
                $table->foreignId('export_permit_id')->nullable()->constrained('export_permits')->onDelete('set null');
                $table->string('hs_code')->comment('Harmonized System code');
                $table->string('country_of_origin');
                $table->string('destination_country');
                $table->decimal('declared_value', 15, 2)->comment('Declared value in IDR');
                $table->string('currency')->default('IDR');
                $table->decimal('total_weight', 12, 2)->comment('Total weight in kg');
                $table->integer('package_count')->default(0);
                $table->string('package_type')->nullable();
                $table->text('goods_description');
                $table->date('declaration_date');
                $table->string('status')->default('draft'); // draft, submitted, approved, rejected, cleared
                $table->string('customs_office')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamp('cleared_at')->nullable();
                $table->string('document_path')->nullable();
                $table->timestamps();
    
                $table->index(['tenant_id', 'status']);
                $table->index(['declaration_date']);
            });
        }

        if (!Schema::hasTable('export_shipments')) {
            Schema::create('export_shipments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->string('shipment_number')->unique();
                $table->foreignId('customs_declaration_id')->nullable()->constrained('customs_declarations')->onDelete('set null');
                $table->foreignId('transport_id')->nullable()->constrained('refrigerated_transports')->onDelete('set null');
                $table->date('shipment_date');
                $table->date('estimated_arrival')->nullable();
                $table->date('actual_arrival')->nullable();
                $table->string('origin_port');
                $table->string('destination_port');
                $table->string('shipping_method')->default('sea'); // sea, air, land
                $table->string('carrier_name')->nullable();
                $table->string('tracking_number')->nullable();
                $table->decimal('total_value', 15, 2)->nullable();
                $table->string('incoterm')->nullable()->comment('FOB, CIF, etc.');
                $table->string('status')->default('preparing'); // preparing, in_transit, arrived, delivered, cancelled
                $table->text('shipping_documents')->nullable()->comment('JSON array of document paths');
                $table->timestamps();
    
                $table->index(['tenant_id', 'status']);
                $table->index(['shipment_date']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('export_shipments');
        Schema::dropIfExists('customs_declarations');
        Schema::dropIfExists('health_certificates');
        Schema::dropIfExists('export_permits');
        Schema::dropIfExists('mortality_logs');
        Schema::dropIfExists('feeding_schedules');
        Schema::dropIfExists('water_quality_logs');
        Schema::dropIfExists('aquaculture_ponds');
        Schema::dropIfExists('freshness_assessments');
        Schema::dropIfExists('quality_grades');
        Schema::dropIfExists('fish_species');
        Schema::dropIfExists('catch_logs');
        Schema::dropIfExists('fishing_trip_crew');
        Schema::dropIfExists('fishing_trips');
        Schema::dropIfExists('fishing_zones');
        Schema::dropIfExists('fishing_vessels');
        Schema::dropIfExists('refrigerated_transports');
        Schema::dropIfExists('cold_chain_alerts');
        Schema::dropIfExists('temperature_logs');
        Schema::dropIfExists('cold_storage_units');
    }
};
