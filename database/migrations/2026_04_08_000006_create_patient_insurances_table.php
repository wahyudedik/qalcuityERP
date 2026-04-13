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
        Schema::create('patient_insurances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');

            // Insurance Information
            $table->string('insurance_provider'); // BPJS, Prudential, Allianz, etc.
            $table->string('insurance_type'); // national, private, corporate, self_pay
            $table->string('policy_number');
            $table->string('group_number')->nullable();
            $table->string('member_id')->nullable();
            $table->string('plan_name')->nullable(); // Plan type/name
            $table->string('plan_class')->nullable(); // Class 1, 2, 3, VIP, VVIP

            // Coverage Details
            $table->decimal('coverage_limit', 12, 2)->nullable(); // Maximum coverage amount
            $table->decimal('deductible', 12, 2)->default(0); // Amount patient pays before insurance
            $table->decimal('copay_percentage', 5, 2)->default(0); // Patient's percentage share
            $table->json('covered_services')->nullable(); // ["consultation", "lab", "radiology", "pharmacy"]
            $table->json('excluded_services')->nullable(); // Services not covered

            // Validity
            $table->date('effective_date');
            $table->date('expiry_date');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_primary')->default(false); // Primary or secondary insurance

            // Employer/Group Information
            $table->string('employer_name')->nullable();
            $table->string('employer_contact')->nullable();
            $table->string('group_admin_name')->nullable();
            $table->string('group_admin_contact')->nullable();

            // Claims Information
            $table->integer('total_claims')->default(0);
            $table->decimal('total_claimed_amount', 12, 2)->default(0);
            $table->decimal('total_approved_amount', 12, 2)->default(0);
            $table->date('last_claim_date')->nullable();

            // Document
            $table->string('insurance_card_path')->nullable();
            $table->string('policy_document_path')->nullable();

            // Standard fields
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('patient_id');
            $table->index('insurance_provider');
            $table->index('policy_number');
            $table->index('is_active');
            $table->index(['effective_date', 'expiry_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_insurances');
    }
};
