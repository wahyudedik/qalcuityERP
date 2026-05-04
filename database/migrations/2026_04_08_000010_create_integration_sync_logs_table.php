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
        if (!Schema::hasTable('integration_sync_logs')) {
            Schema::create('integration_sync_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('integration_id')->constrained()->onDelete('cascade');
                $table->string('sync_type'); // products, orders, inventory
                $table->string('direction'); // push (ERP→Marketplace), pull (Marketplace→ERP)
                $table->string('status'); // success, failed, partial
                $table->integer('records_processed')->default(0);
                $table->integer('records_failed')->default(0);
                $table->text('error_message')->nullable();
                $table->integer('duration_seconds')->nullable();
                $table->json('details')->nullable(); // Additional sync details
                $table->timestamps();
    
                $table->index(['tenant_id', 'integration_id']);
                $table->index(['tenant_id', 'sync_type']);
                $table->index(['status', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_sync_logs');
    }
};
