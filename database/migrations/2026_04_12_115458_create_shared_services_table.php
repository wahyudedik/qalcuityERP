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
        if (!Schema::hasTable('shared_services')) {
            Schema::create('shared_services', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_group_id')->constrained('company_groups')->onDelete('cascade');
                $table->string('name');
                $table->string('code')->unique();
                $table->text('description')->nullable();
                $table->string('service_type'); // accounting, hr, inventory, etc
                $table->json('configuration')->nullable();
                $table->boolean('is_active')->default(true);
                $table->string('cost_center')->nullable();
                $table->timestamps();

                $table->index(['company_group_id', 'is_active']);
                $table->index(['service_type', 'is_active']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shared_services');
    }
};
