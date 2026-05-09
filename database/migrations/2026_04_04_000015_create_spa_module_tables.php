<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Spa Therapists
        if (! Schema::hasTable('spa_therapists')) {
            Schema::create('spa_therapists', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('employee_number')->unique();
                $table->string('name');
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->text('specializations')->nullable(); // JSON array
                $table->enum('status', ['available', 'busy', 'off_duty', 'on_leave'])->default('available');
                $table->decimal('hourly_rate', 10, 2)->default(0); // Commission rate
                $table->integer('rating')->default(0); // Average rating 1-5
                $table->integer('total_treatments')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'status']);
            });
        }

        // Spa Treatments/Services
        if (! Schema::hasTable('spa_treatments')) {
            Schema::create('spa_treatments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('category'); // massage, facial, body_treatment, etc.
                $table->integer('duration_minutes'); // e.g., 60, 90, 120
                $table->decimal('price', 15, 2);
                $table->decimal('cost', 15, 2)->default(0); // Cost of products used
                $table->string('image_path')->nullable();
                $table->text('benefits')->nullable(); // JSON array
                $table->boolean('requires_consultation')->default(false);
                $table->integer('preparation_time')->default(0); // Minutes before treatment
                $table->integer('cleanup_time')->default(0); // Minutes after treatment
                $table->integer('max_daily_bookings')->nullable(); // Limit per day
                $table->integer('booked_today')->default(0);
                $table->boolean('is_active')->default(true);
                $table->integer('display_order')->default(0);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'category', 'is_active']);
            });
        }

        // Spa Packages (Bundle of treatments)
        if (! Schema::hasTable('spa_packages')) {
            Schema::create('spa_packages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->text('description')->nullable();
                $table->decimal('package_price', 15, 2);
                $table->decimal('regular_price', 15, 2); // Sum of individual treatments
                $table->decimal('savings', 15, 2)->default(0);
                $table->integer('total_duration_minutes');
                $table->string('image_path')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'is_active']);
            });
        }

        // Package Items (Treatments in a package)
        if (! Schema::hasTable('spa_package_items')) {
            Schema::create('spa_package_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('package_id')->constrained('spa_packages')->cascadeOnDelete();
                $table->foreignId('treatment_id')->constrained('spa_treatments')->cascadeOnDelete();
                $table->integer('sequence_order');
                $table->integer('duration_override')->nullable(); // Override default duration
                $table->timestamps();

                $table->unique(['package_id', 'treatment_id']);
            });
        }

        // Spa Bookings/Appointments
        if (! Schema::hasTable('spa_bookings')) {
            Schema::create('spa_bookings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('booking_number')->unique();
                $table->foreignId('guest_id')->nullable()->constrained('guests')->nullOnDelete();
                $table->unsignedInteger('room_number')->nullable(); // Room number reference
                $table->foreignId('reservation_id')->nullable()->constrained('reservations')->nullOnDelete();
                $table->foreignId('therapist_id')->nullable()->constrained('spa_therapists')->nullOnDelete();
                $table->foreignId('treatment_id')->nullable()->constrained('spa_treatments')->nullOnDelete();
                $table->foreignId('package_id')->nullable()->constrained('spa_packages')->nullOnDelete();
                $table->date('booking_date');
                $table->time('start_time');
                $table->time('end_time');
                $table->integer('duration_minutes');
                $table->decimal('amount', 15, 2);
                $table->decimal('tax_amount', 15, 2)->default(0);
                $table->decimal('service_charge', 15, 2)->default(0);
                $table->decimal('total_amount', 15, 2);
                $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'])->default('pending');
                $table->text('special_requests')->nullable();
                $table->text('therapist_notes')->nullable();
                $table->text('cancellation_reason')->nullable();
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'booking_date', 'status']);
                $table->index(['tenant_id', 'therapist_id', 'booking_date']);
            });
        }

        // Booking Items (For packages with multiple treatments)
        if (! Schema::hasTable('spa_booking_items')) {
            Schema::create('spa_booking_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('booking_id')->constrained('spa_bookings')->cascadeOnDelete();
                $table->foreignId('treatment_id')->constrained('spa_treatments')->cascadeOnDelete();
                $table->integer('sequence_order');
                $table->time('scheduled_start')->nullable();
                $table->time('scheduled_end')->nullable();
                $table->enum('status', ['pending', 'in_progress', 'completed', 'skipped'])->default('pending');
                $table->timestamps();

                $table->index(['booking_id', 'sequence_order']);
            });
        }

        // Therapist Schedules/Availability
        if (! Schema::hasTable('therapist_schedules')) {
            Schema::create('therapist_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('therapist_id')->constrained('spa_therapists')->cascadeOnDelete();
                $table->date('schedule_date');
                $table->time('start_time');
                $table->time('end_time');
                $table->boolean('is_available')->default(true);
                $table->text('breaks')->nullable(); // JSON array of break times
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['therapist_id', 'schedule_date', 'start_time', 'end_time'], 'therapist_sched_unique');
                $table->index(['tenant_id', 'therapist_id', 'schedule_date']);
            });
        }

        // Therapist Time Off/Leave
        if (! Schema::hasTable('therapist_time_off')) {
            Schema::create('therapist_time_off', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('therapist_id')->constrained('spa_therapists')->cascadeOnDelete();
                $table->date('start_date');
                $table->date('end_date');
                $table->enum('type', ['vacation', 'sick_leave', 'training', 'personal'])->default('vacation');
                $table->text('reason')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'therapist_id', 'start_date']);
            });
        }

        // Spa Product Sales (Retail products sold at spa)
        if (! Schema::hasTable('spa_product_sales')) {
            Schema::create('spa_product_sales', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('booking_id')->nullable()->constrained('spa_bookings')->nullOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->integer('quantity');
                $table->decimal('unit_price', 15, 2);
                $table->decimal('total_price', 15, 2);
                $table->decimal('cost_price', 15, 2);
                $table->decimal('profit', 15, 2)->storedAs('(`total_price` - `cost_price`)');
                $table->foreignId('sold_by')->constrained('users')->cascadeOnDelete();
                $table->timestamp('sale_date')->useCurrent();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'sale_date']);
                $table->index(['tenant_id', 'product_id']);
            });
        }

        // Spa Reviews/Ratings
        if (! Schema::hasTable('spa_reviews')) {
            Schema::create('spa_reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('booking_id')->constrained('spa_bookings')->cascadeOnDelete();
                $table->foreignId('guest_id')->nullable()->constrained('guests')->nullOnDelete();
                $table->foreignId('therapist_id')->nullable()->constrained('spa_therapists')->nullOnDelete();
                $table->foreignId('treatment_id')->nullable()->constrained('spa_treatments')->nullOnDelete();
                $table->integer('rating'); // 1-5
                $table->text('comment')->nullable();
                $table->json('ratings_breakdown')->nullable(); // {cleanliness: 5, service: 4, ...}
                $table->boolean('is_published')->default(false);
                $table->timestamps();

                $table->index(['tenant_id', 'therapist_id']);
                $table->index(['tenant_id', 'treatment_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('spa_reviews');
        Schema::dropIfExists('spa_product_sales');
        Schema::dropIfExists('therapist_time_off');
        Schema::dropIfExists('therapist_schedules');
        Schema::dropIfExists('spa_booking_items');
        Schema::dropIfExists('spa_bookings');
        Schema::dropIfExists('spa_package_items');
        Schema::dropIfExists('spa_packages');
        Schema::dropIfExists('spa_treatments');
        Schema::dropIfExists('spa_therapists');
    }
};
