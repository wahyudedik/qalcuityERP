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
        // Workflows table
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('trigger_type')->comment('event, schedule, condition');
            $table->json('trigger_config')->comment('Trigger configuration');
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->integer('execution_count')->default(0);
            $table->timestamp('last_executed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
            $table->index('trigger_type');
        });

        // Workflow Actions table
        Schema::create('workflow_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('workflow_id')->constrained()->onDelete('cascade');
            $table->string('action_type')->comment('create_po, send_notification, etc');
            $table->json('action_config')->comment('Action configuration');
            $table->integer('order')->default(0);
            $table->json('condition')->nullable()->comment('Optional condition');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['workflow_id', 'order']);
        });

        // Workflow Execution Logs table
        Schema::create('workflow_execution_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('workflow_id')->constrained()->onDelete('cascade');
            $table->string('triggered_by')->nullable()->comment('Event name or schedule');
            $table->json('context_data')->nullable();
            $table->string('status')->default('running')->comment('running, success, failed');
            $table->text('error_message')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['workflow_id', 'started_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_execution_logs');
        Schema::dropIfExists('workflow_actions');
        Schema::dropIfExists('workflows');
    }
};
