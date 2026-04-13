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
        // Print Jobs Queue
        if (!Schema::hasTable('print_jobs')) {
            Schema::create('print_jobs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('job_number')->unique();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->string('job_name');
                $table->text('description')->nullable();
                $table->string('product_type'); // business_card, flyer, brochure, banner, book, etc
                $table->string('status')->default('queued'); // queued, prepress, platemaking, on_press, finishing, quality_check, completed, cancelled
                $table->string('priority')->default('normal'); // low, normal, high, urgent
                $table->date('due_date')->nullable();
                $table->integer('quantity')->default(0);
                $table->string('paper_type')->nullable();
                $table->decimal('paper_size_width', 8, 2)->nullable(); // mm
                $table->decimal('paper_size_height', 8, 2)->nullable(); // mm
                $table->integer('colors_front')->default(4); // CMYK
                $table->integer('colors_back')->default(0);
                $table->string('finishing_type')->nullable(); // laminating, binding, cutting, folding
                $table->json('specifications')->nullable(); // detailed specs
                $table->string('file_path')->nullable(); // original file
                $table->string('proof_path')->nullable(); // proof file
                $table->boolean('proof_approved')->default(false);
                $table->timestamp('proof_approved_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->decimal('estimated_cost', 12, 2)->default(0);
                $table->decimal('actual_cost', 12, 2)->default(0);
                $table->decimal('quoted_price', 12, 2)->default(0);
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->foreignId('assigned_operator')->nullable()->constrained('users')->nullOnDelete();
                $table->text('special_instructions')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'due_date']);
                $table->index('job_number');
                $table->index('priority');
            });
        }

        // Pre-Press Workflows
        if (!Schema::hasTable('prepress_workflows')) {
            Schema::create('prepress_workflows', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('print_job_id')->constrained('print_jobs')->onDelete('cascade');
                $table->string('workflow_stage'); // file_check, color_correction, imposition, proofing
                $table->string('status')->default('pending'); // pending, in_progress, completed, rejected
                $table->text('instructions')->nullable();
                $table->json('adjustments')->nullable(); // color adjustments, corrections
                $table->string('imposition_layout')->nullable(); // layout type
                $table->integer('pages_per_sheet')->nullable();
                $table->string('proof_type')->nullable(); // digital, hard_copy
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('rejection_reason')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'print_job_id']);
                $table->index('workflow_stage');
            });
        }

        // Printing Plates
        if (!Schema::hasTable('printing_plates')) {
            Schema::create('printing_plates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('print_job_id')->constrained('print_jobs')->onDelete('cascade');
                $table->string('plate_number');
                $table->string('color_channel'); // C, M, Y, K, spot_color
                $table->string('plate_type')->default('aluminum'); // aluminum, polymer, thermal
                $table->string('size')->nullable(); // plate size
                $table->integer('screen_lpi')->nullable(); // lines per inch
                $table->string('status')->default('available'); // available, mounted, in_use, cleaned, retired
                $table->integer('usage_count')->default(0);
                $table->integer('max_usage')->default(50000); // max impressions
                $table->timestamp('created_at_plate')->nullable();
                $table->timestamp('mounted_at')->nullable();
                $table->timestamp('dismounted_at')->nullable();
                $table->timestamp('cleaned_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'print_job_id']);
                $table->index('plate_number');
                $table->index('status');
            });
        }

        // Press Runs (Real-time tracking)
        if (!Schema::hasTable('press_runs')) {
            Schema::create('press_runs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('print_job_id')->constrained('print_jobs')->onDelete('cascade');
                $table->string('press_machine')->nullable(); // machine name/number
                $table->timestamp('run_start')->nullable();
                $table->timestamp('run_end')->nullable();
                $table->integer('target_quantity')->default(0);
                $table->integer('produced_quantity')->default(0);
                $table->integer('waste_quantity')->default(0);
                $table->decimal('production_speed', 8, 2)->default(0); // sheets per hour
                $table->string('current_status')->default('setup'); // setup, running, paused, stopped, completed
                $table->integer('ink_levels_c')->nullable(); // percentage
                $table->integer('ink_levels_m')->nullable();
                $table->integer('ink_levels_y')->nullable();
                $table->integer('ink_levels_k')->nullable();
                $table->decimal('registration_accuracy', 5, 2)->nullable(); // mm
                $table->string('quality_status')->default('checking'); // checking, ok, needs_adjustment
                $table->json('quality_checks')->nullable(); // periodic quality check results
                $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('run_notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'print_job_id']);
                $table->index('current_status');
                $table->index('run_start');
            });
        }

        // Finishing Operations
        if (!Schema::hasTable('finishing_operations')) {
            Schema::create('finishing_operations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('print_job_id')->constrained('print_jobs')->onDelete('cascade');
                $table->string('operation_type'); // cutting, folding, binding, laminating, embossing, die_cutting
                $table->string('status')->default('pending'); // pending, in_progress, completed, failed
                $table->integer('sequence_order')->default(0); // order of operations
                $table->json('operation_specs')->nullable(); // specific settings for operation
                $table->integer('target_quantity')->default(0);
                $table->integer('completed_quantity')->default(0);
                $table->integer('waste_quantity')->default(0);
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('machine_used')->nullable();
                $table->text('quality_notes')->nullable();
                $table->text('issues')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'print_job_id']);
                $table->index('operation_type');
                $table->index('status');
            });
        }

        // Print Job Estimates (Advanced estimating engine)
        if (!Schema::hasTable('print_estimates')) {
            Schema::create('print_estimates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('print_job_id')->nullable()->constrained('print_jobs')->nullOnDelete();
                $table->string('estimate_number')->unique();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->string('product_type');
                $table->integer('quantity');
                $table->string('paper_type');
                $table->decimal('paper_size_width', 8, 2);
                $table->decimal('paper_size_height', 8, 2);
                $table->integer('colors_front')->default(4);
                $table->integer('colors_back')->default(0);
                $table->string('finishing_options')->nullable(); // comma-separated
                $table->decimal('paper_cost', 12, 2)->default(0);
                $table->decimal('plate_cost', 12, 2)->default(0);
                $table->decimal('ink_cost', 12, 2)->default(0);
                $table->decimal('labor_cost', 12, 2)->default(0);
                $table->decimal('machine_cost', 12, 2)->default(0);
                $table->decimal('finishing_cost', 12, 2)->default(0);
                $table->decimal('overhead_cost', 12, 2)->default(0);
                $table->decimal('total_cost', 12, 2)->default(0);
                $table->decimal('markup_percentage', 5, 2)->default(30);
                $table->decimal('quoted_price', 12, 2)->default(0);
                $table->decimal('profit_margin', 12, 2)->default(0);
                $table->string('status')->default('draft'); // draft, sent, accepted, rejected, expired
                $table->date('valid_until')->nullable();
                $table->json('cost_breakdown')->nullable(); // detailed breakdown
                $table->text('terms_and_conditions')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'status']);
                $table->index('estimate_number');
                $table->index('created_at');
            });
        }

        // Web-to-Print Orders
        if (!Schema::hasTable('web_to_print_orders')) {
            Schema::create('web_to_print_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('order_number')->unique();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->string('customer_email')->nullable();
                $table->string('customer_name')->nullable();
                $table->string('product_template')->nullable(); // template used
                $table->json('customization_data')->nullable(); // customer customizations
                $table->string('uploaded_file_path')->nullable();
                $table->integer('quantity')->default(0);
                $table->decimal('unit_price', 12, 2)->default(0);
                $table->decimal('total_price', 12, 2)->default(0);
                $table->string('payment_status')->default('pending'); // pending, paid, refunded
                $table->string('fulfillment_status')->default('pending'); // pending, in_production, shipped, delivered
                $table->foreignId('print_job_id')->nullable()->constrained('print_jobs')->nullOnDelete();
                $table->string('shipping_address')->nullable();
                $table->string('tracking_number')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('shipped_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->text('special_instructions')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'fulfillment_status']);
                $table->index('order_number');
                $table->index('payment_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('web_to_print_orders');
        Schema::dropIfExists('print_estimates');
        Schema::dropIfExists('finishing_operations');
        Schema::dropIfExists('press_runs');
        Schema::dropIfExists('printing_plates');
        Schema::dropIfExists('prepress_workflows');
        Schema::dropIfExists('print_jobs');
    }
};
