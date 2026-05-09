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
        // Weather Data Table
        if (! Schema::hasTable('weather_data')) {
            Schema::create('weather_data', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('location_name')->nullable();
                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);
                $table->float('temperature')->comment('Temperature in Celsius');
                $table->float('feels_like')->nullable();
                $table->float('humidity')->comment('Humidity percentage');
                $table->float('pressure')->nullable();
                $table->float('wind_speed')->nullable();
                $table->string('wind_direction')->nullable();
                $table->float('rainfall')->default(0)->comment('Rainfall in mm');
                $table->string('weather_condition')->nullable();
                $table->text('weather_description')->nullable();
                $table->float('visibility')->nullable();
                $table->float('uv_index')->nullable();
                $table->timestamp('forecast_date');
                $table->string('forecast_type')->default('current')->comment('current, hourly, daily');
                $table->string('data_source')->default('openweathermap');
                $table->json('raw_data')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'forecast_date']);
                $table->index(['latitude', 'longitude']);
                $table->index('forecast_type');
            });
        }

        // Crop Cycles Table
        if (! Schema::hasTable('crop_cycles')) {
            Schema::create('crop_cycles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('crop_name');
                $table->string('variety')->nullable();
                $table->decimal('area_hectares', 10, 2);
                $table->string('field_location')->nullable();
                $table->date('planting_date');
                $table->date('expected_harvest_date')->nullable();
                $table->date('actual_harvest_date')->nullable();
                $table->string('growth_stage')->default('planning')->comment('planning, planted, vegetative, flowering, fruiting, ready_to_harvest, harvested');
                $table->integer('days_to_harvest')->nullable();
                $table->float('estimated_yield_tons')->nullable();
                $table->float('actual_yield_tons')->nullable();
                $table->string('status')->default('active')->comment('active, completed, failed');
                $table->text('notes')->nullable();
                $table->json('metadata')->nullable(); // Additional data like soil type, seeds used, etc
                $table->timestamps();

                $table->index(['tenant_id', 'status']);
                $table->index('planting_date');
                $table->index('growth_stage');
            });
        }

        // Pest Detections Table
        if (! Schema::hasTable('pest_detections')) {
            Schema::create('pest_detections', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('crop_cycle_id')->nullable()->constrained('crop_cycles')->onDelete('set null');
                $table->string('image_path');
                $table->string('pest_name')->nullable();
                $table->string('disease_name')->nullable();
                $table->float('confidence_score')->nullable();
                $table->string('severity')->default('unknown')->comment('low, medium, high, critical');
                $table->boolean('pest_detected')->default(false);
                $table->boolean('disease_detected')->default(false);
                $table->json('treatment_recommendations')->nullable();
                $table->json('prevention_tips')->nullable();
                $table->text('ai_analysis')->nullable();
                $table->string('status')->default('pending')->comment('pending, reviewed, treated, resolved');
                $table->date('treatment_date')->nullable();
                $table->text('treatment_notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'status']);
                $table->index('created_at');
                $table->index('severity');
            });
        }

        // Irrigation Schedules Table
        if (! Schema::hasTable('irrigation_schedules')) {
            Schema::create('irrigation_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('crop_cycle_id')->nullable()->constrained('crop_cycles')->onDelete('set null');
                $table->string('zone_name')->nullable();
                $table->string('schedule_type')->default('automatic')->comment('automatic, manual, emergency');
                $table->time('irrigation_time');
                $table->integer('duration_minutes')->default(30);
                $table->string('frequency')->default('daily')->comment('hourly, daily, weekly, custom');
                $table->json('custom_days')->nullable(); // [1,3,5] for Mon, Wed, Fri
                $table->float('water_volume_liters')->nullable();
                $table->string('irrigation_method')->default('sprinkler')->comment('sprinkler, drip, flood, manual');
                $table->boolean('is_active')->default(true);
                $table->boolean('weather_adjusted')->default(false);
                $table->timestamp('last_irrigated_at')->nullable();
                $table->timestamp('next_irrigation_at')->nullable();
                $table->integer('total_irrigations')->default(0);
                $table->float('total_water_used_liters')->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'is_active']);
                $table->index('next_irrigation_at');
                $table->index('schedule_type');
            });
        }

        // Irrigation Logs Table
        if (! Schema::hasTable('irrigation_logs')) {
            Schema::create('irrigation_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('irrigation_schedule_id')->constrained()->onDelete('cascade');
                $table->timestamp('irrigated_at');
                $table->integer('actual_duration_minutes');
                $table->float('actual_water_used_liters')->nullable();
                $table->string('status')->default('completed')->comment('completed, failed, skipped');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'irrigated_at']);
            });
        }

        // Market Prices Table
        if (! Schema::hasTable('market_prices')) {
            Schema::create('market_prices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('commodity');
                $table->string('market_name')->nullable();
                $table->string('location')->nullable();
                $table->decimal('price_per_kg', 12, 2);
                $table->string('currency')->default('IDR');
                $table->string('unit')->default('kg');
                $table->string('quality_grade')->nullable()->comment('premium, grade_a, grade_b, standard');
                $table->date('price_date');
                $table->string('price_source')->default('manual')->comment('manual, api, scraper');
                $table->decimal('previous_price', 12, 2)->nullable();
                $table->float('price_change_percent')->nullable();
                $table->text('market_notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'commodity', 'price_date']);
                $table->index('commodity');
                $table->index('price_date');
            });
        }

        // Price Alerts Table
        if (! Schema::hasTable('price_alerts')) {
            Schema::create('price_alerts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('commodity');
                $table->decimal('target_price', 12, 2);
                $table->string('condition')->comment('above, below, equals');
                $table->boolean('is_active')->default(true);
                $table->json('notification_channels')->nullable(); // email, sms, push
                $table->timestamp('triggered_at')->nullable();
                $table->boolean('has_triggered')->default(false);
                $table->timestamps();

                $table->index(['tenant_id', 'is_active']);
                $table->index('commodity');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_alerts');
        Schema::dropIfExists('market_prices');
        Schema::dropIfExists('irrigation_logs');
        Schema::dropIfExists('irrigation_schedules');
        Schema::dropIfExists('pest_detections');
        Schema::dropIfExists('crop_cycles');
        Schema::dropIfExists('weather_data');
    }
};
