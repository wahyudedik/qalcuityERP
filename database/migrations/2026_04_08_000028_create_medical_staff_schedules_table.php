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
        if (!Schema::hasTable('medical_staff_schedules')) {
            Schema::create('medical_staff_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
    
                // Schedule Details
                $table->date('schedule_date');
                $table->time('start_time');
                $table->time('end_time');
                $table->integer('slot_duration')->default(30); // minutes per slot
                $table->integer('max_appointments')->default(0); // 0 = unlimited
                $table->integer('booked_appointments')->default(0);
    
                // Location
                $table->string('location')->nullable(); // Clinic, Hospital, Room
                $table->string('location_details')->nullable(); // Room number, floor
    
                // Status
                $table->enum('status', ['available', 'booked', 'blocked', 'cancelled'])->default('available');
                $table->boolean('is_available')->default(true);
                $table->boolean('allow_overbooking')->default(false);
    
                // Type
                $table->enum('schedule_type', ['regular', 'overtime', 'on_call', 'telemedicine', 'home_visit'])->default('regular');
    
                // Blocking reasons
                $table->string('block_reason')->nullable(); // vacation, meeting, leave, etc.
                $table->text('block_notes')->nullable();
    
                // Statistics
                $table->integer('no_show_count')->default(0);
                $table->decimal('utilization_rate', 5, 2)->default(0); // percentage
    
                // Standard fields
                $table->text('notes')->nullable();
                $table->softDeletes();
                $table->timestamps();
    
                // Indexes
                $table->index('doctor_id');
                $table->index('schedule_date');
                $table->index('status');
                $table->index('is_available');
                $table->index(['doctor_id', 'schedule_date']);
                $table->index(['schedule_date', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_staff_schedules');
    }
};
