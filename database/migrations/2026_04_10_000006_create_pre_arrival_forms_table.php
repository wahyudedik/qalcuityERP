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
        if (! Schema::hasTable('pre_arrival_forms')) {
            Schema::create('pre_arrival_forms', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->foreignId('reservation_id')->constrained('reservations')->onDelete('cascade');
                $table->foreignId('guest_id')->constrained('guests')->onDelete('cascade');

                // Guest Details
                $table->string('id_number')->nullable();
                $table->string('id_type')->nullable(); // passport, ktp, sim
                $table->date('id_expiry')->nullable();
                $table->string('nationality')->nullable();
                $table->date('date_of_birth')->nullable();
                $table->string('gender')->nullable();

                // Contact Information
                $table->string('emergency_contact_name')->nullable();
                $table->string('emergency_contact_phone')->nullable();
                $table->string('emergency_contact_relationship')->nullable();

                // Preferences
                $table->string('room_preference')->nullable(); // high_floor, low_floor, near_elevator, etc
                $table->string('bed_preference')->nullable(); // twin, king, queen
                $table->json('special_requests')->nullable();
                $table->text('dietary_requirements')->nullable();
                $table->json('amenities_requested')->nullable();

                // Arrival Details
                $table->time('estimated_arrival_time')->nullable();
                $table->string('transportation_method')->nullable(); // taxi, airport_shuttle, private_car
                $table->string('flight_number')->nullable();
                $table->string('airport_pickup_required')->default(false);

                // Consent & Agreements
                $table->boolean('terms_accepted')->default(false);
                $table->boolean('marketing_consent')->default(false);
                $table->boolean('data_processing_consent')->default(false);

                // Status
                $table->string('status')->default('pending'); // pending, completed, verified
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');

                $table->timestamps();

                $table->index(['tenant_id', 'status']);
                $table->index(['reservation_id']);
                $table->index(['submitted_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_arrival_forms');
    }
};
