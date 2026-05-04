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
        Schema::dropIfExists('medical_waste_logs');
        Schema::dropIfExists('sterilization_logs');
        Schema::dropIfExists('medical_supply_request_items');
        Schema::dropIfExists('medical_supply_requests');
        Schema::dropIfExists('medical_supply_transactions');
        Schema::dropIfExists('medical_supplies');

        // Medical Supplies
        if (!Schema::hasTable('medical_supplies')) {
            Schema::create('medical_supplies', function (Blueprint $table) {
                $table->id();
                $table->string('supply_code')->unique(); // MED-SUP-XXXX
                $table->string('supply_name');
                $table->string('generic_name')->nullable();
                $table->string('brand')->nullable();
    
                // Category
                $table->string('category'); // Surgical, Pharmaceutical, Diagnostic, PPE, etc.
                $table->string('subcategory')->nullable();
                $table->string('unit_of_measure'); // pcs, box, pack, liter, etc.
    
                // Stock Management
                $table->integer('stock_quantity')->default(0);
                $table->integer('minimum_stock')->default(0); // Reorder point
                $table->integer('maximum_stock')->nullable();
                $table->integer('reorder_quantity')->nullable();
    
                // Expiry Tracking
                $table->date('expiry_date')->nullable();
                $table->boolean('has_expiry')->default(false);
                $table->integer('expiry_alert_days')->default(90); // Days before expiry to alert
                $table->boolean('expiry_alert_sent')->default(false);
    
                // Location & Storage
                $table->string('storage_location')->nullable(); // Warehouse, room, shelf
                $table->string('bin_location')->nullable();
                $table->string('storage_condition')->nullable(); // Room temp, refrigerated, frozen
    
                // Pricing
                $table->decimal('unit_cost', 10, 2)->default(0);
                $table->decimal('selling_price', 10, 2)->default(0);
    
                // Supplier
                $table->unsignedBigInteger('supplier_id')->nullable(); // FK to suppliers
                $table->string('supplier_part_number')->nullable();
    
                // Status
                $table->boolean('is_active')->default(true);
                $table->boolean('requires_prescription')->default(false);
                $table->boolean('is_controlled_substance')->default(false);
                $table->boolean('requires_sterilization')->default(false);
    
                // Documentation
                $table->text('description')->nullable();
                $table->text('specifications')->nullable();
                $table->string('msds_path')->nullable(); // Material Safety Data Sheet
                $table->string('image_path')->nullable();
    
                $table->timestamps();
    
                $table->index('supply_code');
                $table->index('category');
                $table->index('stock_quantity');
                $table->index('expiry_date');
                $table->index('is_active');
            });
        }

        // Medical Supply Transactions (Inventory Movement)
        if (!Schema::hasTable('medical_supply_transactions')) {
            Schema::create('medical_supply_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supply_id')->constrained('medical_supplies')->onDelete('cascade');
                $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
    
                // Transaction Details
                $table->string('transaction_number')->unique(); // TRX-MED-YYYYMMDD-XXXX
                $table->datetime('transaction_date');
                $table->enum('transaction_type', ['receipt', 'issue', 'return', 'adjustment', 'transfer', 'expiry', 'damage']);
    
                // Quantity
                $table->integer('quantity');
                $table->integer('previous_quantity');
                $table->integer('new_quantity');
    
                // Reference
                $table->string('reference_type')->nullable(); // purchase_order, request, etc.
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->string('reference_number')->nullable();
    
                // Destination/Source
                $table->string('source_location')->nullable();
                $table->string('destination_location')->nullable();
                $table->unsignedBigInteger('department_id')->nullable(); // FK to departments (will be created separately)
    
                // Batch & Expiry
                $table->string('batch_number')->nullable();
                $table->date('expiry_date')->nullable();
    
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->index('transaction_number');
                $table->index('supply_id');
                $table->index('transaction_type');
                $table->index('transaction_date');
            });
        }

        // Medical Supply Requests
        if (!Schema::hasTable('medical_supply_requests')) {
            Schema::create('medical_supply_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('requested_by')->constrained('users')->onDelete('restrict');
                $table->unsignedBigInteger('department_id'); // FK to departments
                $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
    
                // Request Information
                $table->string('request_number')->unique(); // REQ-MED-YYYYMMDD-XXXX
                $table->date('request_date');
                $table->date('required_date')->nullable();
                $table->datetime('approved_at')->nullable();
                $table->datetime('fulfilled_at')->nullable();
    
                // Priority
                $table->enum('urgency', ['low', 'normal', 'urgent', 'emergency'])->default('normal');
    
                // Status
                $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'partial', 'fulfilled', 'cancelled'])
                    ->default('draft');
    
                // Purpose
                $table->string('purpose')->nullable(); // Surgery, ward restock, emergency, etc.
                $table->text('justification')->nullable();
    
                $table->text('notes')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamps();
    
                $table->index('request_number');
                $table->index('status');
                $table->index('urgency');
                $table->index('request_date');
            });
        }

        // Medical Supply Request Items
        if (!Schema::hasTable('medical_supply_request_items')) {
            Schema::create('medical_supply_request_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('request_id')->constrained('medical_supply_requests')->onDelete('cascade');
                $table->foreignId('supply_id')->constrained('medical_supplies')->onDelete('restrict');
    
                // Item Details
                $table->integer('quantity_requested');
                $table->integer('quantity_approved')->default(0);
                $table->integer('quantity_fulfilled')->default(0);
    
                // Status
                $table->enum('status', ['pending', 'approved', 'rejected', 'fulfilled', 'partial'])->default('pending');
    
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->index('request_id');
                $table->index('supply_id');
            });
        }

        // Sterilization Logs
        if (!Schema::hasTable('sterilization_logs')) {
            Schema::create('sterilization_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('equipment_id')->nullable()->constrained('medical_equipments')->onDelete('set null');
                $table->unsignedBigInteger('supply_id')->nullable(); // FK to medical_supplies
                $table->foreignId('processed_by')->constrained('users')->onDelete('restrict');
                $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null');
    
                // Sterilization Details
                $table->string('sterilization_number')->unique(); // STER-YYYYMMDD-XXXX
                $table->datetime('sterilization_date');
                $table->datetime('completion_date')->nullable();
    
                // Method
                $table->enum('sterilization_method', [
                    'autoclave',
                    'ethylene_oxide',
                    'hydrogen_peroxide',
                    'steam',
                    'dry_heat',
                    'radiation',
                    'chemical'
                ]);
    
                // Parameters
                $table->decimal('temperature', 5, 2)->nullable(); // Celsius
                $table->integer('duration_minutes')->nullable();
                $table->decimal('pressure', 8, 2)->nullable(); // PSI or bar
                $table->string('chemical_indicator')->nullable();
                $table->string('biological_indicator')->nullable();
    
                // Load Information
                $table->integer('load_size')->nullable(); // Number of items
                $table->string('load_contents')->nullable(); // Description
                $table->string('sterilizer_id')->nullable(); // Machine ID
    
                // Validation
                $table->enum('validation_result', ['passed', 'failed', 'pending'])->default('pending');
                $table->text('validation_notes')->nullable();
                $table->datetime('validated_at')->nullable();
    
                // Next Sterilization
                $table->datetime('next_sterilization_due')->nullable();
    
                // Compliance
                $table->boolean('is_compliant')->default(false);
                $table->string('compliance_standard')->nullable(); // ISO, CDC, etc.
    
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->index('sterilization_number');
                $table->index('sterilization_date');
                $table->index('validation_result');
                $table->index('equipment_id');
            });
        }

        // Medical Waste Logs
        if (!Schema::hasTable('medical_waste_logs')) {
            Schema::create('medical_waste_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('generated_by')->constrained('users')->onDelete('restrict');
                $table->unsignedBigInteger('department_id'); // FK to departments
                $table->foreignId('collected_by')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('disposed_by')->nullable()->constrained('users')->onDelete('set null');
    
                // Waste Information
                $table->string('waste_log_number')->unique(); // WASTE-YYYYMMDD-XXXX
                $table->datetime('generation_date');
                $table->datetime('collection_date')->nullable();
                $table->datetime('disposal_date')->nullable();
    
                // Waste Type
                $table->enum('waste_type', [
                    'infectious',
                    'hazardous',
                    'radioactive',
                    'pharmaceutical',
                    'sharps',
                    'pathological',
                    'chemical',
                    'general'
                ]);
                $table->string('waste_description');
    
                // Quantity
                $table->decimal('weight_kg', 8, 2)->default(0);
                $table->integer('container_count')->default(0);
                $table->string('container_type')->nullable(); // Bag, box, drum, etc.
    
                // Handling
                $table->enum('handling_method', ['incineration', 'autoclave', 'landfill', 'recycling', 'chemical_treatment']);
                $table->string('disposal_facility')->nullable();
                $table->string('disposal_location')->nullable();
    
                // Compliance
                $table->string('manifest_number')->nullable();
                $table->string('transporter_name')->nullable();
                $table->string('transporter_license')->nullable();
                $table->boolean('is_compliant')->default(false);
    
                // Cost
                $table->decimal('disposal_cost', 10, 2)->default(0);
    
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->index('waste_log_number');
                $table->index('waste_type');
                $table->index('generation_date');
                $table->index('department_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_waste_logs');
        Schema::dropIfExists('sterilization_logs');
        Schema::dropIfExists('medical_supply_request_items');
        Schema::dropIfExists('medical_supply_requests');
        Schema::dropIfExists('medical_supply_transactions');
        Schema::dropIfExists('medical_supplies');
    }
};
