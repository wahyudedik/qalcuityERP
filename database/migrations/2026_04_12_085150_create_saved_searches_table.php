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
        if (!Schema::hasTable('saved_searches')) {
            Schema::create('saved_searches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->string('query');
                $table->string('type')->default('all');
                $table->json('filters')->nullable();
                $table->string('module')->nullable();
                $table->integer('use_count')->default(0);
                $table->timestamp('last_used_at')->nullable();
                $table->boolean('is_public')->default(false);
                $table->timestamps();
    
                $table->index(['user_id', 'is_public']);
                $table->index(['query', 'type']);
                $table->index('last_used_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_searches');
    }
};
