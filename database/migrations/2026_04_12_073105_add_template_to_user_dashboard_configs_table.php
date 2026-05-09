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
        Schema::table('user_dashboard_configs', function (Blueprint $table) {
            // Template yang sedang digunakan
            if (! Schema::hasColumn('user_dashboard_configs', 'template_name')) {
                $table->string('template_name')->nullable()->after('widgets');
            }

            // Saved templates (user bisa punya multiple saved layouts)
            if (! Schema::hasColumn('user_dashboard_configs', 'saved_templates')) {
                $table->json('saved_templates')->nullable()->after('template_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_dashboard_configs', function (Blueprint $table) {
            $table->dropColumn(['template_name', 'saved_templates']);
        });
    }
};
