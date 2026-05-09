<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rate Plans - Different pricing strategies
        if (! Schema::hasTable('rate_plans')) {
            Schema::create('rate_plans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('room_type_id')->constrained('room_types')->cascadeOnDelete();
                $table->string('name'); // e.g., "Standard Rate", "Non-Refundable", "Package Deal"
                $table->string('code')->unique(); // e.g., "STD", "NR", "PKG"
                $table->text('description')->nullable();
                $table->enum('type', ['standard', 'non_refundable', 'package', 'corporate', 'promotional']);
                $table->decimal('base_rate', 15, 2); // Base price per night
                $table->integer('min_stay')->default(1); // Minimum nights
                $table->integer('max_stay')->nullable(); // Maximum nights
                $table->integer('advance_booking_days')->nullable(); // Days in advance required
                $table->boolean('is_refundable')->default(true);
                $table->integer('cancellation_hours')->default(24); // Hours before check-in
                $table->boolean('includes_breakfast')->default(false);
                $table->json('inclusions')->nullable(); // Array of included items
                $table->boolean('is_active')->default(true);
                $table->date('valid_from')->nullable();
                $table->date('valid_to')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'room_type_id', 'is_active']);
            });
        }

        // Dynamic Pricing Rules
        if (! Schema::hasTable('dynamic_pricing_rules')) {
            Schema::create('dynamic_pricing_rules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('rate_plan_id')->nullable()->constrained('rate_plans')->nullOnDelete();
                $table->string('name');
                $table->text('description')->nullable();
                $table->enum('rule_type', [
                    'occupancy_based',
                    'seasonal',
                    'day_of_week',
                    'length_of_stay',
                    'advance_booking',
                    'competitor_based',
                    'event_based',
                ]);
                $table->json('conditions'); // Rule conditions
                $table->enum('adjustment_type', ['percentage', 'fixed_amount']);
                $table->decimal('adjustment_value', 10, 2); // Can be negative for discounts
                $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
                $table->boolean('is_active')->default(true);
                $table->date('valid_from')->nullable();
                $table->date('valid_to')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'rule_type', 'is_active']);
            });
        }

        // Occupancy Forecasts
        if (! Schema::hasTable('occupancy_forecasts')) {
            Schema::create('occupancy_forecasts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('room_type_id')->nullable()->constrained('room_types')->nullOnDelete();
                $table->date('forecast_date');
                $table->integer('total_rooms');
                $table->integer('projected_booked');
                $table->integer('projected_available');
                $table->decimal('projected_occupancy_rate', 5, 2); // Percentage
                $table->decimal('projected_adr', 15, 2); // Average Daily Rate
                $table->decimal('projected_revpar', 15, 2); // Revenue Per Available Room
                $table->decimal('confidence_level', 5, 2)->default(0); // AI confidence percentage
                $table->json('factors')->nullable(); // Factors affecting forecast
                $table->timestamps();

                $table->unique(['tenant_id', 'forecast_date', 'room_type_id']);
                $table->index(['tenant_id', 'forecast_date']);
            });
        }

        // Competitor Rates Tracking
        if (! Schema::hasTable('competitor_rates')) {
            Schema::create('competitor_rates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('competitor_name');
                $table->string('source'); // e.g., "Booking.com", "Agoda", "Manual"
                $table->date('rate_date');
                $table->decimal('rate', 15, 2);
                $table->string('room_type')->nullable(); // Their room type name
                $table->json('amenities')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['tenant_id', 'competitor_name', 'rate_date']);
            });
        }

        // Special Events affecting demand
        if (! Schema::hasTable('special_events')) {
            Schema::create('special_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->text('description')->nullable();
                $table->date('start_date');
                $table->date('end_date');
                $table->enum('impact_level', ['low', 'medium', 'high', 'very_high']);
                $table->decimal('expected_demand_increase', 5, 2)->default(0); // Percentage
                $table->boolean('affects_pricing')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'start_date', 'end_date']);
            });
        }

        // Revenue Analytics Snapshots (daily aggregation)
        if (! Schema::hasTable('revenue_snapshots')) {
            Schema::create('revenue_snapshots', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->date('snapshot_date');
                $table->integer('total_rooms');
                $table->integer('occupied_rooms');
                $table->decimal('occupancy_rate', 5, 2);
                $table->decimal('adr', 15, 2); // Average Daily Rate
                $table->decimal('revpar', 15, 2); // Revenue Per Available Room
                $table->decimal('total_revenue', 15, 2);
                $table->integer('total_reservations');
                $table->integer('new_bookings_today');
                $table->integer('cancellations_today');
                $table->json('breakdown_by_room_type')->nullable();
                $table->json('breakdown_by_channel')->nullable();
                $table->timestamps();

                $table->unique(['tenant_id', 'snapshot_date']);
                $table->index(['tenant_id', 'snapshot_date']);
            });
        }

        // Pricing Recommendations (AI-generated)
        if (! Schema::hasTable('pricing_recommendations')) {
            Schema::create('pricing_recommendations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('room_type_id')->constrained('room_types')->cascadeOnDelete();
                $table->date('recommendation_date');
                $table->decimal('current_rate', 15, 2);
                $table->decimal('recommended_rate', 15, 2);
                $table->decimal('suggested_change_percentage', 5, 2);
                $table->text('reasoning'); // AI explanation
                $table->json('supporting_data')->nullable(); // Market data, occupancy, etc.
                $table->enum('status', ['pending', 'applied', 'rejected'])->default('pending');
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'recommendation_date', 'status'], 'pricing_rec_tenant_date_status_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_recommendations');
        Schema::dropIfExists('revenue_snapshots');
        Schema::dropIfExists('special_events');
        Schema::dropIfExists('competitor_rates');
        Schema::dropIfExists('occupancy_forecasts');
        Schema::dropIfExists('dynamic_pricing_rules');
        Schema::dropIfExists('rate_plans');
    }
};
