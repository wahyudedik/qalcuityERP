<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * TASK-015: Create help topics table for contextual help system
     */
    public function up(): void
    {
        Schema::create('help_topics', function (Blueprint $table) {
            $table->id();
            $table->string('topic_key')->unique(); // e.g., 'customer-selection'
            $table->string('module'); // e.g., 'sales', 'inventory', 'hrm'
            $table->string('page'); // e.g., 'invoices.create', 'products.index'
            $table->string('field')->nullable(); // e.g., 'customer_id', 'product_name'
            $table->string('title');
            $table->text('content');
            $table->text('tips')->nullable(); // JSON array of tips
            $table->string('video_url')->nullable();
            $table->string('image_url')->nullable();
            $table->string('documentation_url')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable(); // Additional data
            $table->timestamps();

            $table->index(['module', 'page']);
            $table->index(['topic_key', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('help_topics');
    }
};
