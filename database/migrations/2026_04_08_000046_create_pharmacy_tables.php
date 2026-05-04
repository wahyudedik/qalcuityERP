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
        Schema::dropIfExists('pharmacy_analytics_daily');
        Schema::dropIfExists('medicine_alerts');
        Schema::dropIfExists('pharmacy_dispensing');
        Schema::dropIfExists('medicine_interactions');
        Schema::dropIfExists('medicine_suppliers');
        Schema::dropIfExists('medicine_stocks');
        Schema::dropIfExists('medicines');
        Schema::dropIfExists('medicine_categories');

        // Medicine Categories
        if (!Schema::hasTable('medicine_categories')) {
            Schema::create('medicine_categories', function (Blueprint $table) {
                $table->id();
                $table->string('category_code')->unique(); // ANT, ANA, VIT, etc.
                $table->string('category_name'); // Antibiotics, Analgesics, Vitamins, etc.
                $table->text('description')->nullable();
                $table->string('color_code')->nullable(); // For UI display
                $table->boolean('requires_prescription')->default(false);
                $table->boolean('is_controlled_substance')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
    
                $table->index('category_code');
            });
        }

        // Medicines
        if (!Schema::hasTable('medicines')) {
            Schema::create('medicines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->nullable()->constrained('medicine_categories')->onDelete('set null');
    
                // Medicine Information
                $table->string('medicine_code')->unique(); // MED-0001, etc.
                $table->string('name'); // Brand name
                $table->string('generic_name')->nullable();
                $table->string('manufacturer')->nullable();
                $table->string('brand')->nullable();
    
                // Classification
                $table->string('dosage_form'); // Tablet, capsule, syrup, injection, etc.
                $table->string('strength'); // 500mg, 250ml, etc.
                $table->string('route')->default('oral'); // oral, topical, injection, etc.
                $table->string('atc_code')->nullable(); // Anatomical Therapeutic Chemical code
    
                // Storage
                $table->enum('storage_type', ['room_temperature', 'refrigerated', 'frozen', 'controlled'])->default('room_temperature');
                $table->string('storage_instructions')->nullable();
    
                // Pricing
                $table->decimal('unit_price', 10, 2)->default(0);
                $table->decimal('purchase_price', 10, 2)->default(0);
                $table->decimal('selling_price', 10, 2)->default(0);
                $table->decimal('markup_percentage', 5, 2)->default(0);
    
                // Stock Summary
                $table->integer('total_stock')->default(0);
                $table->integer('minimum_stock')->default(0);
                $table->integer('reorder_point')->default(0);
                $table->integer('maximum_stock')->default(0);
    
                // Regulation
                $table->boolean('requires_prescription')->default(false);
                $table->boolean('is_controlled_substance')->default(false);
                $table->string('drug_classification')->nullable(); // OTC, Prescription, Controlled
    
                // Standard fields
                $table->text('description')->nullable();
                $table->text('contraindications')->nullable();
                $table->text('side_effects')->nullable();
                $table->text('dosage_instructions')->nullable();
                $table->boolean('is_active')->default(true);
                $table->softDeletes();
                $table->timestamps();
    
                $table->index('medicine_code');
                $table->index('generic_name');
                $table->index('category_id');
                $table->index('is_active');
            });
        }

        // Medicine Stock (Batch-level tracking)
        if (!Schema::hasTable('medicine_stocks')) {
            Schema::create('medicine_stocks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('medicine_id')->constrained()->onDelete('cascade');
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
    
                // Batch Information
                $table->string('batch_number');
                $table->date('manufacturing_date')->nullable();
                $table->date('expiry_date');
                $table->integer('quantity');
                $table->integer('quantity_available')->default(0);
                $table->integer('quantity_reserved')->default(0);
                $table->integer('quantity_damaged')->default(0);
    
                // Pricing
                $table->decimal('purchase_price', 10, 2)->default(0);
                $table->decimal('unit_cost', 10, 2)->default(0);
    
                // Storage
                $table->string('storage_location')->nullable(); // Warehouse, Pharmacy, Room
                $table->string('rack_number')->nullable();
                $table->string('shelf_number')->nullable();
    
                // Status
                $table->enum('status', ['available', 'reserved', 'expired', 'damaged', 'quarantine', 'returned'])->default('available');
                $table->boolean('is_expired')->default(false);
                $table->datetime('expired_at')->nullable();
    
                // Alerts
                $table->boolean('expiry_alert_sent')->default(false);
                $table->datetime('expiry_alert_sent_at')->nullable();
    
                // Standard fields
                $table->text('notes')->nullable();
                $table->softDeletes();
                $table->timestamps();
    
                $table->index('medicine_id');
                $table->index('batch_number');
                $table->index('expiry_date');
                $table->index('status');
                $table->index(['medicine_id', 'status']);
                $table->index(['expiry_date', 'is_expired']);
            });
        }

        // Medicine Suppliers
        if (!Schema::hasTable('medicine_suppliers')) {
            Schema::create('medicine_suppliers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('medicine_id')->constrained()->onDelete('cascade');
                $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
    
                // Pricing
                $table->decimal('purchase_price', 10, 2)->default(0);
                $table->decimal('minimum_order_quantity', 10, 2)->default(0);
                $table->integer('lead_time_days')->default(0); // Days to deliver
    
                // Status
                $table->boolean('is_preferred')->default(false);
                $table->boolean('is_active')->default(true);
                $table->integer('reliability_score')->default(0); // 0-100
    
                // Standard fields
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->unique(['medicine_id', 'supplier_id']);
                $table->index('is_preferred');
            });
        }

        // Medicine Interactions
        if (!Schema::hasTable('medicine_interactions')) {
            Schema::create('medicine_interactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('medicine_a_id')->constrained('medicines')->onDelete('cascade');
                $table->foreignId('medicine_b_id')->constrained('medicines')->onDelete('cascade');
    
                // Interaction Details
                $table->enum('severity', ['contraindicated', 'major', 'moderate', 'minor'])->default('moderate');
                $table->text('description');
                $table->text('mechanism')->nullable(); // How interaction occurs
                $table->text('clinical_effects')->nullable();
                $table->text('management')->nullable(); // How to manage/avoid
    
                // Recommendations
                $table->boolean('avoid_combination')->default(false);
                $table->text('alternative_suggestions')->nullable();
                $table->text('monitoring_required')->nullable();
    
                // References
                $table->string('reference_source')->nullable();
                $table->text('reference_notes')->nullable();
    
                // Standard fields
                $table->boolean('is_active')->default(true);
                $table->timestamps();
    
                $table->unique(['medicine_a_id', 'medicine_b_id']);
                $table->index('severity');
            });
        }

        // Pharmacy Dispensing
        if (!Schema::hasTable('pharmacy_dispensing')) {
            Schema::create('pharmacy_dispensing', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('prescription_id'); // FK to prescriptions (will be created separately)
                $table->foreignId('patient_id')->constrained()->onDelete('cascade');
                $table->foreignId('dispensed_by')->constrained('users')->onDelete('restrict');
                $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
    
                // Dispensing Information
                $table->string('dispensing_number')->unique();
                $table->datetime('dispense_date');
                $table->datetime('verified_at')->nullable();
    
                // Status
                $table->enum('status', ['pending', 'dispensing', 'completed', 'cancelled', 'returned'])->default('pending');
    
                // Items (JSON for flexibility)
                $table->json('dispensed_items'); // Array of dispensed medicines
    
                // Pricing
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->decimal('discount', 12, 2)->default(0);
                $table->decimal('tax', 12, 2)->default(0);
                $table->decimal('total_amount', 12, 2)->default(0);
    
                // Payment
                $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'refunded'])->default('unpaid');
                $table->datetime('paid_at')->nullable();
    
                // Patient Counseling
                $table->boolean('counseling_provided')->default(false);
                $table->text('counseling_notes')->nullable();
                $table->text('special_instructions')->nullable();
    
                // Standard fields
                $table->text('notes')->nullable();
                $table->softDeletes();
                $table->timestamps();
    
                $table->index('dispensing_number');
                $table->index('prescription_id');
                $table->index('patient_id');
                $table->index('status');
                $table->index('dispense_date');
            });
        }

        // Medicine Alerts
        if (!Schema::hasTable('medicine_alerts')) {
            Schema::create('medicine_alerts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('medicine_id')->nullable()->constrained()->onDelete('cascade');
                $table->foreignId('medicine_stock_id')->nullable()->constrained('medicine_stocks')->onDelete('cascade');
                $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
                $table->foreignId('acknowledged_by')->nullable()->constrained('users')->onDelete('set null');
    
                // Alert Information
                $table->enum('alert_type', ['low_stock', 'out_of_stock', 'expiring_soon', 'expired', 'interaction', 'recall']);
                $table->string('alert_title');
                $table->text('alert_message');
                $table->enum('priority', ['critical', 'high', 'medium', 'low'])->default('medium');
    
                // Status
                $table->enum('status', ['active', 'acknowledged', 'resolved'])->default('active');
                $table->datetime('alerted_at');
                $table->datetime('acknowledged_at')->nullable();
                $table->datetime('resolved_at')->nullable();
                $table->text('resolution_notes')->nullable();
    
                // Expiry specific
                $table->date('expiry_date')->nullable();
                $table->integer('days_until_expiry')->nullable();
    
                // Stock specific
                $table->integer('current_stock')->nullable();
                $table->integer('minimum_stock')->nullable();
    
                $table->timestamps();
    
                $table->index('alert_type');
                $table->index('priority');
                $table->index('status');
                $table->index('expiry_date');
            });
        }

        // Pharmacy Analytics Daily
        if (!Schema::hasTable('pharmacy_analytics_daily')) {
            Schema::create('pharmacy_analytics_daily', function (Blueprint $table) {
                $table->id();
                $table->date('analytics_date');
    
                // Dispensing Metrics
                $table->integer('total_prescriptions')->default(0);
                $table->integer('total_dispensed')->default(0);
                $table->integer('total_pending')->default(0);
                $table->integer('total_cancelled')->default(0);
                $table->integer('total_returned')->default(0);
    
                // Revenue
                $table->decimal('total_revenue', 12, 2)->default(0);
                $table->decimal('total_cost', 12, 2)->default(0);
                $table->decimal('total_profit', 12, 2)->default(0);
                $table->decimal('average_prescription_value', 10, 2)->default(0);
    
                // Stock Metrics
                $table->integer('total_medicines')->default(0);
                $table->integer('low_stock_count')->default(0);
                $table->integer('out_of_stock_count')->default(0);
                $table->integer('expired_count')->default(0);
                $table->integer('expiring_soon_count')->default(0);
    
                // Top medicines (JSON)
                $table->json('top_dispensed_medicines')->nullable();
                $table->json('top_revenue_medicines')->nullable();
                $table->json('category_distribution')->nullable();
    
                $table->timestamps();
    
                $table->unique('analytics_date');
                $table->index('analytics_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacy_analytics_daily');
        Schema::dropIfExists('medicine_alerts');
        Schema::dropIfExists('pharmacy_dispensing');
        Schema::dropIfExists('medicine_interactions');
        Schema::dropIfExists('medicine_suppliers');
        Schema::dropIfExists('medicine_stocks');
        Schema::dropIfExists('medicines');
        Schema::dropIfExists('medicine_categories');
    }
};
