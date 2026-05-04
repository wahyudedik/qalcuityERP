<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('ai_memories')) {
            Schema::create('ai_memories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('user_id');
                $table->string('key');        // preferred_payment_method, default_warehouse, frequent_customer, skipped_steps, etc.
                $table->json('value');        // nilai preferensi
                $table->unsignedInteger('frequency')->default(1); // berapa kali pola ini muncul
                $table->timestamp('last_seen_at')->nullable();
                $table->timestamps();
    
                $table->unique(['tenant_id', 'user_id', 'key']);
                $table->index(['tenant_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_memories');
    }
};
