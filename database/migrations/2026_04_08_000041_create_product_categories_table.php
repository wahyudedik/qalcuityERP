<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_categories')) {
            Schema::create('product_categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->text('description')->nullable();
                $table->foreignId('parent_id')->nullable()->constrained('product_categories')->onDelete('set null');
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
                $table->softDeletes();

                // Indexes for performance
                $table->index(['tenant_id', 'name']);
                $table->index('parent_id');

                // Unique constraint per tenant
                $table->unique(['tenant_id', 'name'], 'unique_category_per_tenant');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};
