<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Room Types
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();
            $table->integer('base_occupancy')->default(2);
            $table->integer('max_occupancy')->default(2);
            $table->decimal('base_rate', 15, 2);
            $table->json('amenities')->nullable();
            $table->json('images')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Rooms
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained('room_types')->cascadeOnDelete();
            $table->string('number');
            $table->string('floor')->nullable();
            $table->string('building')->nullable();
            $table->enum('status', ['available', 'occupied', 'maintenance', 'cleaning', 'blocked'])->default('available');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['tenant_id', 'number']);
        });

        // 3. Room Rates
        Schema::create('room_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained('room_types')->cascadeOnDelete();
            $table->string('name');
            $table->enum('rate_type', ['standard', 'weekend', 'seasonal', 'promo', 'dynamic']);
            $table->decimal('amount', 15, 2);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->json('day_of_week')->nullable();
            $table->integer('min_stay')->default(1);
            $table->integer('max_stay')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Guests
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->enum('id_type', ['ktp', 'passport', 'sim'])->nullable();
            $table->string('id_number')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable()->default('Indonesia');
            $table->string('nationality')->nullable()->default('Indonesian');
            $table->date('date_of_birth')->nullable();
            $table->enum('vip_level', ['regular', 'silver', 'gold', 'platinum'])->default('regular');
            $table->text('notes')->nullable();
            $table->integer('total_stays')->default(0);
            $table->timestamp('last_stay_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 5. Reservations
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->constrained('guests')->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained('room_types')->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->string('reservation_number')->unique();
            $table->enum('status', ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'no_show'])->default('pending');
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->integer('adults')->default(1);
            $table->integer('children')->default(0);
            $table->integer('nights');
            $table->decimal('rate_per_night', 15, 2);
            $table->decimal('total_amount', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2);
            $table->enum('source', ['direct', 'website', 'traveloka', 'booking_com', 'agoda', 'tiket_com', 'other'])->default('direct');
            $table->text('special_requests')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // 6. Reservation Rooms
        Schema::create('reservation_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('reservations')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->decimal('rate_per_night', 15, 2);
            $table->enum('status', ['reserved', 'checked_in', 'checked_out', 'cancelled'])->default('reserved');
            $table->timestamps();
        });

        // 7. Check-In / Check-Outs
        Schema::create('check_in_outs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reservation_id')->constrained('reservations')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('guest_id')->constrained('guests')->cascadeOnDelete();
            $table->enum('type', ['check_in', 'check_out']);
            $table->timestamp('processed_at');
            $table->foreignId('processed_by')->constrained('users')->cascadeOnDelete();
            $table->string('key_card_number')->nullable();
            $table->decimal('deposit_amount', 15, 2)->default(0);
            $table->string('deposit_method')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 8. Housekeeping Tasks
        Schema::create('housekeeping_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['checkout_clean', 'stay_clean', 'deep_clean', 'inspection', 'turndown']);
            $table->enum('status', ['pending', 'in_progress', 'completed', 'inspected'])->default('pending');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('inspected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 9. Room Maintenance
        Schema::create('room_maintenance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['reported', 'in_progress', 'completed'])->default('reported');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('cost', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // 10. Channel Manager Configs
        Schema::create('channel_manager_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->enum('channel', ['traveloka', 'booking_com', 'agoda', 'tiket_com']);
            $table->text('api_key')->nullable();
            $table->text('api_secret')->nullable();
            $table->string('property_id')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamp('last_synced_at')->nullable();
            $table->json('sync_settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['tenant_id', 'channel']);
        });

        // 11. Channel Manager Logs
        Schema::create('channel_manager_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('channel');
            $table->enum('action', ['push_rate', 'push_availability', 'pull_reservation', 'sync']);
            $table->enum('status', ['success', 'failed']);
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        // 12. Hotel Settings
        Schema::create('hotel_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained()->cascadeOnDelete();
            $table->time('check_in_time')->default('14:00');
            $table->time('check_out_time')->default('12:00');
            $table->string('currency')->default('IDR');
            $table->decimal('tax_rate', 5, 2)->default(11.00);
            $table->boolean('deposit_required')->default(false);
            $table->decimal('default_deposit_amount', 15, 2)->default(0);
            $table->boolean('overbooking_allowed')->default(false);
            $table->boolean('auto_assign_room')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_settings');
        Schema::dropIfExists('channel_manager_logs');
        Schema::dropIfExists('channel_manager_configs');
        Schema::dropIfExists('room_maintenance');
        Schema::dropIfExists('housekeeping_tasks');
        Schema::dropIfExists('check_in_outs');
        Schema::dropIfExists('reservation_rooms');
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('guests');
        Schema::dropIfExists('room_rates');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('room_types');
    }
};
