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
        // Create housekeeping_supplies table
        if (!Schema::hasTable('housekeeping_supplies')) {
            Schema::create('housekeeping_supplies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('item_name');
                $table->string('item_code')->unique();
                $table->string('category');
                $table->string('brand')->nullable();
                $table->string('unit_of_measure')->default('pcs');
                $table->integer('quantity_on_hand')->default(0);
                $table->integer('reorder_point')->default(10);
                $table->integer('reorder_quantity')->default(50);
                $table->decimal('unit_cost', 15, 2)->default(0);
                $table->date('last_order_date')->nullable();
                $table->text('supplier_info')->nullable();
                $table->string('storage_location')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'category']);
                $table->index(['tenant_id', 'is_active']);
            });
        }

        // Create housekeeping_tasks table
        if (!Schema::hasTable('housekeeping_tasks')) {
            Schema::create('housekeeping_tasks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
                $table->enum('task_type', ['checkout_cleaning', 'stayover_service', 'deep_cleaning', 'inspection', 'maintenance']);
                $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
                $table->enum('status', ['pending', 'assigned', 'in_progress', 'completed', 'cancelled'])->default('pending');
                $table->text('notes')->nullable();
                $table->json('checklist_items')->nullable();
                $table->integer('estimated_duration')->nullable(); // in minutes
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'status']);
                $table->index(['room_id', 'scheduled_at']);
            });
        }

        // Create housekeeping_task_assignments table
        if (!Schema::hasTable('housekeeping_task_assignments')) {
            Schema::create('housekeeping_task_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('task_id')->constrained('housekeeping_tasks')->cascadeOnDelete();
                $table->foreignId('assigned_to')->constrained('users')->cascadeOnDelete();
                $table->timestamp('assigned_at');
                $table->timestamp('accepted_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->text('completion_notes')->nullable();
                $table->json('photos')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'assigned_to']);
                $table->index(['task_id', 'assigned_at']);
            });
        }

        // Create maintenance_requests table
        if (!Schema::hasTable('maintenance_requests')) {
            Schema::create('maintenance_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
                $table->string('request_number')->unique();
                $table->string('title');
                $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
                $table->enum('status', ['reported', 'open', 'in_progress', 'waiting_parts', 'completed', 'cancelled'])->default('open');
                $table->string('category'); // Electrical, Plumbing, HVAC, etc.
                $table->text('description');
                $table->text('resolution_notes')->nullable();
                $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete();
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('assigned_at')->nullable();
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->decimal('cost', 15, 2)->default(0);
                $table->json('photos')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'status']);
                $table->index(['room_id', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
        Schema::dropIfExists('housekeeping_task_assignments');
        Schema::dropIfExists('housekeeping_tasks');
        Schema::dropIfExists('housekeeping_supplies');
    }
};
