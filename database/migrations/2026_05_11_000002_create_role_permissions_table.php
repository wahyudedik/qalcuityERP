<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('custom_role_id')->constrained('custom_roles')->cascadeOnDelete();
            $table->string('module', 50);
            $table->string('action', 20);
            $table->boolean('granted')->default(true);
            $table->timestamps();

            $table->unique(['custom_role_id', 'module', 'action']);
            $table->index(['tenant_id', 'custom_role_id']);
            $table->index(['module', 'action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
