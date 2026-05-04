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
        // 1. Action Logs (for undo/rollback)
        if (!Schema::hasTable('action_logs')) {
            Schema::create('action_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('action_type'); // create, update, delete, bulk_operation
                $table->string('model_type'); // App\Models\Invoice, etc
                $table->unsignedBigInteger('model_id')->nullable();
                $table->json('before_state')->nullable(); // Data before change
                $table->json('after_state')->nullable(); // Data after change
                $table->json('metadata')->nullable(); // Additional context
                $table->boolean('can_undo')->default(true);
                $table->boolean('undone')->default(false);
                $table->timestamp('undone_at')->nullable();
                $table->foreignId('undone_by_user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('expires_at')->nullable(); // Auto-expire old logs
                $table->timestamps();
    
                $table->index(['tenant_id', 'user_id']);
                $table->index(['tenant_id', 'action_type']);
                $table->index(['tenant_id', 'model_type', 'model_id']);
                $table->index('created_at');
            });
        }

        // 2. Automated Backups
        if (!Schema::hasTable('automated_backups')) {
            Schema::create('automated_backups', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('backup_type'); // daily, weekly, monthly, manual, pre_change
                $table->string('status'); // pending, processing, completed, failed
                $table->string('file_path')->nullable(); // Path to backup file
                $table->string('file_size_mb')->nullable();
                $table->json('tables_included')->nullable(); // Which tables backed up
                $table->integer('records_count')->default(0);
                $table->text('error_message')->nullable();
                $table->timestamp('started_at');
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('expires_at')->nullable(); // Auto-delete old backups
                $table->timestamps();
    
                $table->index(['tenant_id', 'backup_type']);
                $table->index(['tenant_id', 'status']);
                $table->index('created_at');
            });
        }

        // 3. Restore Points (Before major changes)
        if (!Schema::hasTable('restore_points')) {
            Schema::create('restore_points', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('name'); // e.g., "Before Price Update"
                $table->text('description')->nullable();
                $table->string('trigger_event'); // manual, before_migration, before_bulk_update
                $table->json('affected_models')->nullable(); // Models that will be affected
                $table->json('snapshot_data')->nullable(); // Snapshot of critical data
                $table->boolean('is_active')->default(true);
                $table->boolean('used')->default(false);
                $table->timestamp('used_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
    
                $table->index(['tenant_id', 'is_active']);
                $table->index('created_at');
            });
        }

        // 4. Conflict Resolution (Multi-user edits)
        if (!Schema::hasTable('edit_conflicts')) {
            Schema::create('edit_conflicts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('model_type'); // App\Models\Invoice, etc
                $table->unsignedBigInteger('model_id');
                $table->foreignId('original_user_id')->constrained('users')->onDelete('cascade'); // First editor
                $table->foreignId('conflicting_user_id')->constrained('users')->onDelete('cascade'); // Second editor
                $table->json('original_data'); // Data when first user started editing
                $table->json('first_user_changes'); // Changes by first user
                $table->json('second_user_changes'); // Changes by second user
                $table->string('resolution_strategy')->default('manual'); // manual, first_wins, last_wins, merge
                $table->string('status')->default('pending'); // pending, resolved, discarded
                $table->json('resolved_data')->nullable(); // Final resolved data
                $table->foreignId('resolved_by_user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->text('resolution_notes')->nullable();
                $table->timestamp('detected_at');
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
    
                $table->index(['tenant_id', 'status']);
                $table->index(['model_type', 'model_id']);
            });
        }

        // 5. Error Logs with Actionable Solutions
        if (!Schema::hasTable('error_logs_enhanced')) {
            Schema::create('error_logs_enhanced', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('set null');
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
                $table->string('error_type'); // validation, database, api, permission, etc
                $table->string('error_code')->nullable(); // Unique error code
                $table->text('error_message');
                $table->text('stack_trace')->nullable();
                $table->string('file')->nullable();
                $table->integer('line')->nullable();
                $table->json('context')->nullable(); // Request data, user info, etc
                $table->json('suggested_solutions')->nullable(); // Actionable solutions
                $table->string('severity')->default('error'); // info, warning, error, critical
                $table->boolean('resolved')->default(false);
                $table->text('resolution_notes')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
    
                $table->index(['tenant_id', 'error_type']);
                $table->index(['tenant_id', 'severity']);
                $table->index('created_at');
            });
        }

        // 6. Recovery Queue (Failed operations to retry)
        if (!Schema::hasTable('recovery_queue')) {
            Schema::create('recovery_queue', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('operation_type'); // invoice_creation, payment_processing, etc
                $table->json('operation_data'); // Original operation data
                $table->string('failure_reason');
                $table->integer('retry_count')->default(0);
                $table->integer('max_retries')->default(3);
                $table->string('status')->default('pending'); // pending, retrying, completed, failed
                $table->json('last_error')->nullable();
                $table->timestamp('next_retry_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
    
                $table->index(['tenant_id', 'status']);
                $table->index('next_retry_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recovery_queue');
        Schema::dropIfExists('error_logs_enhanced');
        Schema::dropIfExists('edit_conflicts');
        Schema::dropIfExists('restore_points');
        Schema::dropIfExists('automated_backups');
        Schema::dropIfExists('action_logs');
    }
};
