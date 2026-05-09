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
        if (! Schema::hasTable('patients')) {
            Schema::create('patients', function (Blueprint $table) {
                $table->id();
                $table->string('medical_record_number')->unique(); // No. Rekam Medis
                $table->string('nik', 16)->nullable()->unique(); // National ID
                $table->string('full_name');
                $table->string('short_name')->nullable(); // Nama panggilan
                $table->date('birth_date');
                $table->string('birth_place')->nullable();
                $table->enum('gender', ['male', 'female']);
                $table->string('blood_type')->nullable(); // A, B, AB, O
                $table->string('religion')->nullable();
                $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
                $table->string('occupation')->nullable();
                $table->string('nationality')->default('Indonesian');

                // Contact Information
                $table->string('phone_primary');
                $table->string('phone_secondary')->nullable();
                $table->string('email')->nullable();

                // Address
                $table->text('address_street')->nullable();
                $table->string('address_rt')->nullable();
                $table->string('address_rw')->nullable();
                $table->string('address_kelurahan')->nullable();
                $table->string('address_kecamatan')->nullable();
                $table->string('address_city')->nullable();
                $table->string('address_province')->nullable();
                $table->string('address_postal_code')->nullable();

                // Emergency Contact
                $table->string('emergency_contact_name')->nullable();
                $table->string('emergency_contact_phone')->nullable();
                $table->string('emergency_contact_relation')->nullable(); // spouse, parent, sibling, etc.

                // Insurance Information
                $table->string('insurance_provider')->nullable(); // BPJS, Private Insurance
                $table->string('insurance_policy_number')->nullable();
                $table->string('insurance_group_number')->nullable();
                $table->date('insurance_valid_until')->nullable();
                $table->string('insurance_class')->nullable(); // Class 1, 2, 3 for BPJS

                // Medical Info Summary
                $table->json('known_allergies')->nullable(); // ["Penicillin", "Peanuts", "Latex"]
                $table->json('chronic_diseases')->nullable(); // ["Diabetes", "Hypertension"]
                $table->json('current_medications')->nullable(); // Ongoing medications
                $table->text('medical_notes')->nullable(); // Additional medical notes

                // Status
                $table->enum('status', ['active', 'inactive', 'deceased'])->default('active');
                $table->boolean('is_blacklisted')->default(false); // For problematic patients
                $table->text('blacklist_reason')->nullable();

                // Photo & Documents
                $table->string('photo_path')->nullable();
                $table->string('id_card_path')->nullable();
                $table->string('insurance_card_path')->nullable();

                // QR Code for quick identification
                $table->string('qr_code')->nullable()->unique();

                // Audit & Tracking
                $table->timestamp('last_visit_date')->nullable();
                $table->integer('total_visits')->default(0);
                $table->integer('total_admissions')->default(0);

                // Standard Laravel fields
                $table->foreignId('registered_by')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('primary_doctor_id')->nullable()->constrained('users')->onDelete('set null');
                $table->text('notes')->nullable();
                $table->softDeletes();
                $table->timestamps();

                // Indexes for performance
                $table->index('medical_record_number');
                $table->index('nik');
                $table->index('full_name');
                $table->index('phone_primary');
                $table->index('status');
                $table->index('birth_date');
                $table->index(['gender', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
