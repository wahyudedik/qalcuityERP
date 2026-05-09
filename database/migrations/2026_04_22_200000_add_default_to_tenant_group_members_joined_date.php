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
        Schema::table('tenant_group_members', function (Blueprint $table) {
            // Add default value to joined_date column if it exists
            if (Schema::hasColumn('tenant_group_members', 'joined_date')) {
                $table->date('joined_date')->default(now()->toDateString())->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenant_group_members', function (Blueprint $table) {
            if (Schema::hasColumn('tenant_group_members', 'joined_date')) {
                $table->date('joined_date')->nullable()->change();
            }
        });
    }
};
