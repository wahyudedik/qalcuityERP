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
        // End-of-Day Audit Batches
        Schema::create('night_audit_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('batch_number')->unique(); // e.g., "NA-20260403"
            $table->date('audit_date'); // The business date being audited
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('auditor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'failed'])->default('pending');
            $table->text('notes')->nullable();
            $table->json('summary_data')->nullable(); // Store summary statistics
            $table->integer('total_rooms')->default(0);
            $table->integer('occupied_rooms')->default(0);
            $table->decimal('occupancy_rate', 5, 2)->default(0); // Percentage
            $table->decimal('total_room_revenue', 15, 2)->default(0);
            $table->decimal('total_fb_revenue', 15, 2)->default(0);
            $table->decimal('total_other_revenue', 15, 2)->default(0);
            $table->decimal('total_revenue', 15, 2)->default(0);
            $table->decimal('adr', 15, 2)->default(0); // Average Daily Rate
            $table->decimal('revpar', 15, 2)->default(0); // Revenue Per Available Room
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'audit_date']);
            $table->index(['tenant_id', 'status']);
        });

        // Automated Revenue Postings
        Schema::create('revenue_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('audit_batch_id')->nullable()->constrained('night_audit_batches')->nullOnDelete();
            $table->string('posting_reference')->unique(); // e.g., "RP-20260403-001"
            $table->date('posting_date');
            $table->foreignId('reservation_id')->nullable()->constrained('reservations')->nullOnDelete();
            $table->integer('room_number')->nullable();
            $table->foreignId('guest_id')->nullable()->constrained('guests')->nullOnDelete();
            $table->enum('revenue_type', [
                'room_charge',
                'room_tax',
                'minibar',
                'restaurant',
                'room_service',
                'laundry',
                'telephone',
                'parking',
                'spa',
                'other'
            ]);
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->enum('status', ['pending', 'posted', 'voided'])->default('pending');
            $table->boolean('auto_generated')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'posting_date']);
            $table->index(['tenant_id', 'revenue_type']);
            $table->index(['tenant_id', 'status']);
        });

        // Daily Occupancy Statistics
        Schema::create('daily_occupancy_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->date('stat_date');
            $table->integer('total_rooms')->default(0);
            $table->integer('available_rooms')->default(0);
            $table->integer('occupied_rooms')->default(0);
            $table->integer('out_of_order_rooms')->default(0);
            $table->decimal('occupancy_percentage', 5, 2)->default(0);
            $table->integer('check_ins')->default(0);
            $table->integer('check_outs')->default(0);
            $table->integer('stay_over')->default(0); // Guests staying another night
            $table->integer('no_shows')->default(0);
            $table->integer('cancellations')->default(0);
            $table->decimal('average_length_of_stay', 5, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'stat_date']);
        });

        // ADR (Average Daily Rate) Tracking
        Schema::create('daily_rate_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->date('stat_date');
            $table->decimal('adr', 15, 2)->default(0); // Average Daily Rate
            $table->decimal('revpar', 15, 2)->default(0); // Revenue Per Available Room
            $table->decimal('total_room_revenue', 15, 2)->default(0);
            $table->decimal('total_available_rooms', 10, 2)->default(0); // Can be fractional for partial days
            $table->integer('rooms_sold')->default(0);
            $table->decimal('average_rate_sold', 15, 2)->default(0);
            $table->json('rate_breakdown')->nullable(); // Breakdown by room type
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'stat_date']);
        });

        // Audit Log for Night Audit Operations
        Schema::create('night_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('audit_batch_id')->nullable()->constrained('night_audit_batches')->nullOnDelete();
            $table->string('operation'); // e.g., "post_room_charges", "calculate_statistics"
            $table->text('description');
            $table->enum('status', ['success', 'failed', 'warning'])->default('success');
            $table->json('details')->nullable();
            $table->foreignId('performed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('performed_at')->useCurrent();
            $table->timestamps();

            $table->index(['tenant_id', 'performed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('night_audit_logs');
        Schema::dropIfExists('daily_rate_stats');
        Schema::dropIfExists('daily_occupancy_stats');
        Schema::dropIfExists('revenue_postings');
        Schema::dropIfExists('night_audit_batches');
    }
};
