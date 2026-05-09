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
        Schema::table('onboarding_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('onboarding_profiles', 'skipped')) {
                $table->boolean('skipped')->default(false)->after('completed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('onboarding_profiles', function (Blueprint $table) {
            $table->dropColumn('skipped');
        });
    }
};
