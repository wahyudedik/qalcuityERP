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
        // Drop existing tables if they exist
        Schema::dropIfExists('queue_analytics_daily');
        Schema::dropIfExists('queue_display_logs');
        Schema::dropIfExists('queue_management');
        Schema::dropIfExists('outpatient_visits');
        Schema::dropIfExists('queue_settings');

        // Queue Settings
        if (!Schema::hasTable('queue_settings')) {
            Schema::create('queue_settings', function (Blueprint $table) {
                $table->id();
                $table->string('queue_code')->unique(); // OPD-001, LAB-001, etc.
                $table->string('queue_name'); // Outpatient, Laboratory, Pharmacy, etc.
                $table->string('queue_type'); // outpatient, laboratory, pharmacy, registration, etc.
                $table->string('location')->nullable(); // Room, Counter, etc.
    
                // Configuration
                $table->integer('prefix_number')->default(0); // Current prefix number (resets daily/monthly)
                $table->string('prefix_format')->default('daily'); // daily, monthly, yearly
                $table->integer('max_queue_per_day')->default(0); // 0 = unlimited
                $table->integer('service_time')->default(15); // Average service time in minutes
    
                // Schedule
                $table->time('start_time')->default('08:00:00');
                $table->time('end_time')->default('16:00:00');
                $table->json('working_days')->nullable();
    
                // Display Settings
                $table->boolean('show_on_display')->default(true);
                $table->boolean('play_sound')->default(true);
                $table->string('sound_file')->nullable();
                $table->integer('call_repeat')->default(3);
    
                // Status
                $table->boolean('is_active')->default(true);
                $table->timestamps();
    
                $table->index('queue_code');
                $table->index('queue_type');
            });
        }

        // Outpatient Visits
        if (!Schema::hasTable('outpatient_visits')) {
            Schema::create('outpatient_visits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained()->onDelete('cascade');
                $table->unsignedBigInteger('doctor_id')->nullable(); // FK to doctors table (will be created separately)
                $table->foreignId('queue_setting_id')->nullable()->constrained('queue_settings')->onDelete('set null');
    
                // Visit Information
                $table->string('visit_number')->unique(); // OPD-20260408-0001
                $table->date('visit_date');
                $table->time('visit_time');
    
                // Queue
                $table->string('queue_number')->nullable(); // A001, B002, etc.
                $table->integer('queue_position')->nullable();
    
                // Visit Details
                $table->enum('visit_type', ['first_visit', 'follow_up', 'referral', 'check_up', 'consultation']);
                $table->enum('visit_category', ['general', 'specialist', 'emergency', 'maternal', 'pediatric']);
                $table->string('chief_complaint')->nullable();
    
                // Status
                $table->enum('status', ['registered', 'waiting', 'called', 'in_consultation', 'completed', 'cancelled', 'no_show'])
                    ->default('registered');
    
                // Timing
                $table->datetime('registered_at')->nullable();
                $table->datetime('called_at')->nullable();
                $table->datetime('consultation_started_at')->nullable();
                $table->datetime('consultation_ended_at')->nullable();
                $table->integer('estimated_wait_minutes')->nullable();
                $table->integer('actual_wait_minutes')->nullable();
                $table->integer('consultation_duration_minutes')->nullable();
    
                // Insurance & Payment
                $table->string('payment_method')->nullable(); // self, insurance, bpjs, company
                $table->boolean('is_insurance')->default(false);
                $table->string('insurance_provider')->nullable();
                $table->string('insurance_policy_number')->nullable();
    
                // Referral
                $table->foreignId('referred_by_visit_id')->nullable()->constrained('outpatient_visits')->onDelete('set null');
                $table->string('referral_letter_number')->nullable();
    
                // Standard fields
                $table->text('notes')->nullable();
                $table->softDeletes();
                $table->timestamps();
    
                $table->index('visit_number');
                $table->index('patient_id');
                $table->index('doctor_id');
                $table->index('visit_date');
                $table->index('queue_number');
                $table->index('status');
                $table->index(['visit_date', 'status']);
                $table->index(['doctor_id', 'visit_date']);
            });
        }

        // Queue Management
        if (!Schema::hasTable('queue_management')) {
            Schema::create('queue_management', function (Blueprint $table) {
                $table->id();
                $table->foreignId('outpatient_visit_id')->constrained()->onDelete('cascade');
                $table->foreignId('queue_setting_id')->constrained('queue_settings')->onDelete('cascade');
                $table->foreignId('counter_id')->nullable()->constrained('users')->onDelete('set null'); // Staff handling
    
                // Queue Position
                $table->integer('queue_position'); // Current position
                $table->integer('total_ahead'); // How many patients ahead
                $table->integer('estimated_wait_minutes')->default(0);
    
                // Status
                $table->enum('status', ['waiting', 'called', 'serving', 'completed', 'skipped', 'cancelled'])
                    ->default('waiting');
    
                // Timing
                $table->datetime('enqueued_at');
                $table->datetime('called_at')->nullable();
                $table->datetime('serving_started_at')->nullable();
                $table->datetime('serving_ended_at')->nullable();
                $table->integer('call_count')->default(0);
                $table->datetime('last_called_at')->nullable();
    
                // Skip & Reschedule
                $table->integer('skip_count')->default(0);
                $table->datetime('last_skipped_at')->nullable();
                $table->boolean('is_rescheduled')->default(false);
                $table->foreignId('rescheduled_to_visit_id')->nullable()->constrained('outpatient_visits')->onDelete('set null');
    
                // Priority
                $table->boolean('is_priority')->default(false); // Elderly, pregnant, disability
                $table->integer('priority_level')->default(0); // 0 = normal, higher = more priority
    
                // Standard fields
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->index('outpatient_visit_id');
                $table->index('queue_setting_id');
                $table->index('queue_position');
                $table->index('status');
                $table->index(['queue_setting_id', 'status']);
            });
        }

        // Queue Display Logs
        if (!Schema::hasTable('queue_display_logs')) {
            Schema::create('queue_display_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('queue_management_id'); // FK to queue_management table
                $table->foreign('queue_management_id')->references('id')->on('queue_management')->onDelete('cascade');
                $table->foreignId('display_screen_id')->nullable()->constrained('users')->onDelete('set null');
    
                // Display Information
                $table->datetime('displayed_at');
                $table->string('display_message');
                $table->integer('call_number');
                $table->boolean('was_answered')->default(false);
                $table->datetime('answered_at')->nullable();
    
                $table->timestamps();
    
                $table->index('queue_management_id');
                $table->index('displayed_at');
            });
        }

        // Queue Analytics (Daily Summary)
        if (!Schema::hasTable('queue_analytics_daily')) {
            Schema::create('queue_analytics_daily', function (Blueprint $table) {
                $table->id();
                $table->foreignId('queue_setting_id')->constrained('queue_settings')->onDelete('cascade');
                $table->date('analytics_date');
    
                // Statistics
                $table->integer('total_registered')->default(0);
                $table->integer('total_served')->default(0);
                $table->integer('total_no_show')->default(0);
                $table->integer('total_cancelled')->default(0);
                $table->integer('total_skipped')->default(0);
                $table->integer('currently_waiting')->default(0);
    
                // Wait Times (minutes)
                $table->integer('avg_wait_time')->default(0);
                $table->integer('min_wait_time')->default(0);
                $table->integer('max_wait_time')->default(0);
                $table->integer('median_wait_time')->default(0);
    
                // Service Times (minutes)
                $table->integer('avg_service_time')->default(0);
                $table->integer('min_service_time')->default(0);
                $table->integer('max_service_time')->default(0);
    
                // Hourly breakdown (JSON)
                $table->json('hourly_distribution')->nullable();
                $table->json('doctor_distribution')->nullable();
    
                $table->timestamps();
    
                $table->unique(['queue_setting_id', 'analytics_date']);
                $table->index('analytics_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_analytics_daily');
        Schema::dropIfExists('queue_display_logs');
        Schema::dropIfExists('queue_management');
        Schema::dropIfExists('outpatient_visits');
        Schema::dropIfExists('queue_settings');
    }
};
