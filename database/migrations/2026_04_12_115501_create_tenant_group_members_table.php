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
        if (! Schema::hasTable('tenant_group_members')) {
            Schema::create('tenant_group_members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_group_id')->constrained('company_groups')->onDelete('cascade');
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->string('role')->default('member'); // admin, member, viewer
                $table->json('permissions')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('joined_at')->useCurrent();
                $table->timestamps();

                $table->index(['company_group_id', 'is_active']);
                $table->index(['tenant_id', 'is_active']);
                $table->unique(['company_group_id', 'tenant_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_group_members');
    }
};
