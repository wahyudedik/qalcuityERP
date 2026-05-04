<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('offline_sync_conflicts')) {
            Schema::create('offline_sync_conflicts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->string('entity_type'); // inventory, sale, customer, pos
                $table->unsignedBigInteger('entity_id');
                $table->string('local_id')->nullable(); // Local transaction ID
                $table->timestamp('offline_timestamp'); // When changes were made offline
    
                // Server state (at sync time)
                $table->json('server_state');
    
                // Local state (offline changes)
                $table->json('local_state');
    
                // Conflict metadata
                $table->integer('offline_changes')->default(0); // Number of changes made while offline
                $table->string('status')->default('pending'); // pending, resolved, discarded
                $table->string('resolution_strategy')->nullable(); // local_wins, server_wins, merge, skip
                $table->timestamp('detected_at');
                $table->timestamp('resolved_at')->nullable();
                $table->foreignId('resolved_by')->nullable()->constrained('users');
                $table->timestamps();
    
                // Indexes
                $table->index(['tenant_id', 'status']);
                $table->index(['entity_type', 'entity_id']);
                $table->index('local_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('offline_sync_conflicts');
    }
};
