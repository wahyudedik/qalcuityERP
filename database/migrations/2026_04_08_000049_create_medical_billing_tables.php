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
        Schema::dropIfExists('payment_plans');
        Schema::dropIfExists('copayments');
        Schema::dropIfExists('insurance_adjudications');
        Schema::dropIfExists('insurance_claims');
        Schema::dropIfExists('bill_items');
        Schema::dropIfExists('medical_bills');

        // Medical Bills
        if (!Schema::hasTable('medical_bills')) {
            Schema::create('medical_bills', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained()->onDelete('cascade');
                $table->unsignedBigInteger('visit_id')->nullable(); // FK to patient_visits
                $table->unsignedBigInteger('admission_id')->nullable(); // FK to admissions
    
                // Bill Information
                $table->string('bill_number')->unique(); // BILL-YYYYMMDD-XXXX
                $table->date('bill_date');
                $table->date('due_date')->nullable();
    
                // Amounts
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->decimal('discount_amount', 12, 2)->default(0);
                $table->decimal('discount_percentage', 5, 2)->default(0);
                $table->decimal('tax_amount', 12, 2)->default(0);
                $table->decimal('total_amount', 12, 2)->default(0);
    
                // Insurance
                $table->boolean('has_insurance')->default(false);
                $table->unsignedBigInteger('insurance_provider_id')->nullable(); // FK to insurance_providers
                $table->string('policy_number')->nullable();
                $table->string('group_number')->nullable();
                $table->decimal('insurance_coverage', 12, 2)->default(0);
                $table->decimal('insurance_deductible', 12, 2)->default(0);
                $table->decimal('patient_payable', 12, 2)->default(0);
    
                // Payment Status
                $table->decimal('amount_paid', 12, 2)->default(0);
                $table->decimal('balance_due', 12, 2)->default(0);
                $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'overdue', 'written_off', 'refunded'])
                    ->default('unpaid');
                $table->enum('billing_status', ['draft', 'finalized', 'submitted', 'approved', 'rejected', 'cancelled'])
                    ->default('draft');
    
                // Financial Class
                $table->enum('financial_class', ['self_pay', 'insurance', 'government', 'corporate', 'charity'])
                    ->default('self_pay');
    
                // Dates
                $table->datetime('finalized_at')->nullable();
                $table->datetime('paid_at')->nullable();
    
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->index('bill_number');
                $table->index('patient_id');
                $table->index('payment_status');
                $table->index('billing_status');
                $table->index('bill_date');
                $table->index('due_date');
            });
        }

        // Bill Items
        if (!Schema::hasTable('bill_items')) {
            Schema::create('bill_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('bill_id')->constrained('medical_bills')->onDelete('cascade');
    
                // Item Information
                $table->string('item_type'); // consultation, procedure, medicine, lab, radiology, room, etc.
                $table->unsignedBigInteger('item_id')->nullable(); // Reference to original item
                $table->string('item_code')->nullable();
                $table->string('item_name');
                $table->text('description')->nullable();
    
                // Quantity & Pricing
                $table->integer('quantity')->default(1);
                $table->decimal('unit_price', 10, 2)->default(0);
                $table->decimal('discount_percentage', 5, 2)->default(0);
                $table->decimal('discount_amount', 10, 2)->default(0);
                $table->decimal('total', 12, 2)->default(0);
    
                // Insurance Coverage
                $table->boolean('is_covered_by_insurance')->default(false);
                $table->decimal('insurance_amount', 10, 2)->default(0);
                $table->decimal('patient_amount', 10, 2)->default(0);
    
                // Category
                $table->string('category')->nullable(); // Pharmacy, Laboratory, Radiology, etc.
    
                $table->timestamps();
    
                $table->index('bill_id');
                $table->index('item_type');
                $table->index('category');
            });
        }

        // Insurance Claims
        if (!Schema::hasTable('insurance_claims')) {
            Schema::create('insurance_claims', function (Blueprint $table) {
                $table->id();
                $table->foreignId('bill_id')->constrained('medical_bills')->onDelete('cascade');
                $table->foreignId('patient_id')->constrained()->onDelete('cascade');
                $table->unsignedBigInteger('insurance_provider_id'); // FK to insurance_providers
    
                // Claim Information
                $table->string('claim_number')->unique(); // CLAIM-YYYYMMDD-XXXX
                $table->date('claim_date');
                $table->datetime('submitted_date')->nullable();
                $table->datetime('processed_date')->nullable();
    
                // Amounts
                $table->decimal('billed_amount', 12, 2)->default(0);
                $table->decimal('claim_amount', 12, 2)->default(0);
                $table->decimal('approved_amount', 12, 2)->default(0);
                $table->decimal('rejected_amount', 12, 2)->default(0);
                $table->decimal('paid_amount', 12, 2)->default(0);
    
                // Status
                $table->enum('status', ['draft', 'submitted', 'received', 'under_review', 'approved', 'partially_approved', 'rejected', 'paid', 'appealed'])
                    ->default('draft');
    
                // Insurance Details
                $table->string('policy_number')->nullable();
                $table->string('group_number')->nullable();
                $table->string('authorization_number')->nullable();
                $table->date('service_date_from');
                $table->date('service_date_to')->nullable();
    
                // Diagnosis & Procedures
                $table->json('diagnosis_codes')->nullable(); // ICD-10 codes
                $table->json('procedure_codes')->nullable(); // CPT/ICD-9-CM codes
    
                // Submission
                $table->enum('submission_method', ['electronic', 'paper', 'portal'])->default('electronic');
                $table->string('clearinghouse')->nullable();
                $table->text('submission_response')->nullable(); // JSON response
    
                // Rejection
                $table->text('rejection_reason')->nullable();
                $table->json('rejection_details')->nullable();
                $table->boolean('is_appealed')->default(false);
                $table->datetime('appeal_date')->nullable();
                $table->text('appeal_notes')->nullable();
    
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->index('claim_number');
                $table->index('bill_id');
                $table->index('status');
                $table->index('submitted_date');
                $table->index('insurance_provider_id');
            });
        }

        // Insurance Adjudications
        if (!Schema::hasTable('insurance_adjudications')) {
            Schema::create('insurance_adjudications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('claim_id')->constrained('insurance_claims')->onDelete('cascade');
    
                // Adjudication Details
                $table->datetime('adjudication_date');
                $table->decimal('billed_amount', 12, 2)->default(0);
                $table->decimal('allowed_amount', 12, 2)->default(0);
                $table->decimal('deductible_amount', 12, 2)->default(0);
                $table->decimal('copay_amount', 12, 2)->default(0);
                $table->decimal('coinsurance_amount', 12, 2)->default(0);
                $table->decimal('approved_amount', 12, 2)->default(0);
                $table->decimal('rejected_amount', 12, 2)->default(0);
                $table->decimal('paid_amount', 12, 2)->default(0);
    
                // Breakdown
                $table->json('line_items')->nullable(); // Per-item adjudication
                $table->text('adjustment_reason')->nullable();
    
                // Rejection
                $table->boolean('has_rejection')->default(false);
                $table->text('rejection_reason')->nullable();
                $table->json('rejection_codes')->nullable(); // CARC codes
                $table->text('rejection_notes')->nullable();
    
                // Remittance
                $table->string('remittance_number')->nullable(); // EOB/EON number
                $table->date('remittance_date')->nullable();
                $table->string('check_number')->nullable();
                $table->date('payment_date')->nullable();
    
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->index('claim_id');
                $table->index('adjudication_date');
            });
        }

        // Copayments
        if (!Schema::hasTable('copayments')) {
            Schema::create('copayments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('bill_id')->constrained('medical_bills')->onDelete('cascade');
                $table->foreignId('patient_id')->constrained()->onDelete('cascade');
                $table->foreignId('collected_by')->constrained('users')->onDelete('restrict');
    
                // Copay Information
                $table->string('copay_number')->unique(); // COPAY-YYYYMMDD-XXXX
                $table->date('copay_date');
                $table->decimal('copay_amount', 10, 2);
                $table->decimal('collected_amount', 10, 2);
    
                // Payment Method
                $table->enum('payment_method', ['cash', 'credit_card', 'debit_card', 'bank_transfer', 'ewallet', 'check'])
                    ->default('cash');
                $table->string('transaction_reference')->nullable();
    
                // Status
                $table->enum('status', ['pending', 'collected', 'refunded', 'void'])->default('collected');
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->index('copay_number');
                $table->index('bill_id');
                $table->index('copay_date');
            });
        }

        // Payment Plans
        if (!Schema::hasTable('payment_plans')) {
            Schema::create('payment_plans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('bill_id')->constrained('medical_bills')->onDelete('cascade');
                $table->foreignId('patient_id')->constrained()->onDelete('cascade');
    
                // Plan Information
                $table->string('plan_number')->unique(); // PLAN-YYYYMMDD-XXXX
                $table->date('plan_date');
                $table->decimal('total_amount', 12, 2);
                $table->decimal('down_payment', 12, 2)->default(0);
                $table->decimal('remaining_balance', 12, 2);
                $table->integer('installment_count');
                $table->decimal('installment_amount', 12, 2);
    
                // Schedule
                $table->date('first_payment_date');
                $table->enum('frequency', ['weekly', 'biweekly', 'monthly'])->default('monthly');
                $table->json('payment_schedule'); // Array of payment dates and amounts
    
                // Status
                $table->enum('status', ['active', 'completed', 'defaulted', 'cancelled'])->default('active');
                $table->date('completion_date')->nullable();
    
                // Tracking
                $table->decimal('total_paid', 12, 2)->default(0);
                $table->integer('payments_made')->default(0);
                $table->date('last_payment_date')->nullable();
                $table->date('next_payment_date')->nullable();
    
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->index('plan_number');
                $table->index('bill_id');
                $table->index('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_plans');
        Schema::dropIfExists('copayments');
        Schema::dropIfExists('insurance_adjudications');
        Schema::dropIfExists('insurance_claims');
        Schema::dropIfExists('bill_items');
        Schema::dropIfExists('medical_bills');
    }
};
