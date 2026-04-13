<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add guest preferences table
        Schema::create('guest_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->constrained('guests')->cascadeOnDelete();
            $table->string('category'); // e.g., 'room', 'amenity', 'dietary', 'communication'
            $table->string('preference_key'); // e.g., 'high_floor', 'extra_pillow', 'vegetarian'
            $table->text('preference_value')->nullable(); // e.g., 'yes', '3rd floor', 'queen size'
            $table->integer('priority')->default(1); // 1=low, 2=medium, 3=high
            $table->boolean('is_auto_applied')->default(true); // Automatically apply to future bookings
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'guest_id']);
            $table->index(['category', 'preference_key']);
        });

        // Add group bookings table
        Schema::create('group_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organizer_guest_id')->constrained('guests')->cascadeOnDelete();
            $table->string('group_name');
            $table->string('group_code')->unique();
            $table->enum('type', ['corporate', 'family', 'tour', 'event', 'government', 'other']);
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_rooms')->default(1);
            $table->integer('total_guests')->default(1);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'refunded'])->default('unpaid');
            $table->enum('status', ['pending', 'confirmed', 'active', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->json('benefits')->nullable(); // Special benefits for the group
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });

        // Add reservation upgrades/downgrades table
        Schema::create('reservation_room_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reservation_id')->constrained('reservations')->cascadeOnDelete();
            $table->foreignId('from_room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->foreignId('to_room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained('room_types')->cascadeOnDelete();
            $table->enum('change_type', ['upgrade', 'downgrade', 'same_category']);
            $table->date('effective_date');
            $table->decimal('rate_difference', 15, 2)->default(0); // Positive for upgrade, negative for downgrade
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['reservation_id', 'effective_date']);
        });

        // Add early check-in / late check-out requests table
        Schema::create('early_late_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reservation_id')->constrained('reservations')->cascadeOnDelete();
            $table->foreignId('guest_id')->constrained('guests')->cascadeOnDelete();
            $table->enum('request_type', ['early_checkin', 'late_checkout']);
            $table->timestamp('requested_time');
            $table->time('standard_time'); // Standard check-in/out time for comparison
            $table->integer('extra_hours')->default(0);
            $table->decimal('extra_charge', 15, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['reservation_id', 'request_type']);
            $table->index(['status', 'requested_time']);
        });

        // Add walk-in reservations tracking
        Schema::create('walk_in_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reservation_id')->constrained('reservations')->cascadeOnDelete();
            $table->foreignId('guest_id')->nullable()->constrained('guests')->nullOnDelete();
            $table->string('walk_in_number')->unique();
            $table->timestamp('arrival_time');
            $table->enum('source', ['phone', 'email', 'website', 'ota', 'referral', 'street_walk']);
            $table->boolean('is_new_guest')->default(false);
            $table->text('special_circumstances')->nullable();
            $table->foreignId('handled_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'arrival_time']);
            $table->index(['walk_in_number']);
        });

        // Extend guests table with additional fields
        Schema::table('guests', function (Blueprint $table) {
            $table->string('guest_code')->unique()->after('id');
            $table->json('preferences')->nullable()->after('notes'); // Quick access JSON preferences
            $table->integer('loyalty_points')->default(0)->after('total_stays');
            $table->date('membership_since')->nullable()->after('loyalty_points');
            $table->string('preferred_language')->nullable()->after('nationality');
            $table->string('communication_preference')->nullable()->after('preferred_language'); // email, sms, whatsapp
        });

        // Extend reservations table with group and special request fields
        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('group_booking_id')->nullable()->constrained('group_bookings')->nullOnDelete()->after('guest_id');
            $table->boolean('is_walk_in')->default(false)->after('source');
            $table->boolean('is_vip')->default(false)->after('is_walk_in');
            $table->timestamp('actual_check_in_at')->nullable()->after('check_in_date');
            $table->timestamp('actual_check_out_at')->nullable()->after('check_out_date');
            $table->time('expected_arrival_time')->nullable()->after('adults');
            $table->string('purpose_of_stay')->nullable()->after('special_requests'); // business, leisure, honeymoon, etc.
        });

        // Add room type features for better preference matching
        Schema::table('room_types', function (Blueprint $table) {
            $table->json('features')->nullable()->after('amenities'); // e.g., ['city_view', 'balcony', 'bathtub']
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('walk_in_reservations');
        Schema::dropIfExists('early_late_requests');
        Schema::dropIfExists('reservation_room_changes');
        Schema::dropIfExists('group_bookings');
        Schema::dropIfExists('guest_preferences');

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['group_booking_id']);
            $table->dropColumn([
                'group_booking_id',
                'is_walk_in',
                'is_vip',
                'actual_check_in_at',
                'actual_check_out_at',
                'expected_arrival_time',
                'purpose_of_stay'
            ]);
        });

        Schema::table('guests', function (Blueprint $table) {
            $table->dropUnique(['guest_code']);
            $table->dropColumn([
                'guest_code',
                'preferences',
                'loyalty_points',
                'membership_since',
                'preferred_language',
                'communication_preference'
            ]);
        });

        Schema::table('room_types', function (Blueprint $table) {
            $table->dropColumn(['features']);
        });
    }
};
