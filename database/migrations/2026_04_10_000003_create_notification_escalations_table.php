<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Create notification escalations table for automatic escalation
     * when notifications are not read within specified time.
     */
    public function up(): void
    {
        Schema::create('notification_escalations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('notification_id')->constrained('erp_notifications')->cascadeOnDelete();

            // Escalation details
            $table->foreignId('from_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('to_user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('escalation_level')->default(1); // 1 = first escalation, 2 = second, etc.
            $table->string('reason')->nullable(); // Why it was escalated

            // Timing
            $table->integer('minutes_until_escalation')->default(30); // How long before escalation
            $table->timestamp('escalated_at');
            $table->timestamp('read_at')->nullable(); // When the escalated user read it

            $table->timestamps();

            // Indexes for performance
            $table->index(['tenant_id', 'notification_id']);
            $table->index(['to_user_id', 'read_at']);
            $table->index(['escalation_level', 'escalated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_escalations');
    }
};
