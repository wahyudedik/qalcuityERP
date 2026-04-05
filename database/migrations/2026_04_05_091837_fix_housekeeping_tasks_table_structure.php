<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('housekeeping_tasks')) {
            // Drop and recreate with correct structure
            Schema::dropIfExists('housekeeping_task_assignments');
            Schema::dropIfExists('housekeeping_tasks');

            Schema::create('housekeeping_tasks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
                $table->enum('type', ['checkout_clean', 'stay_clean', 'deep_clean', 'inspection']);
                $table->enum('status', ['pending', 'assigned', 'in_progress', 'completed', 'cancelled'])->default('pending');
                $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
                $table->timestamp('scheduled_at')->nullable();
                $table->integer('estimated_duration')->nullable(); // in minutes
                $table->text('notes')->nullable();
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'status']);
                $table->index(['room_id', 'scheduled_at']);
            });

            // Recreate task assignments table
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('housekeeping_task_assignments');
        Schema::dropIfExists('housekeeping_tasks');
    }
};
