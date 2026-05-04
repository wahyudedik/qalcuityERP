<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('zero_input_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('zero_input_logs', 'user_corrected_data')) {
                $table->json('user_corrected_data')->nullable()->after('extracted_data');
            }
            if (!Schema::hasColumn('zero_input_logs', 'confidence_score')) {
                $table->float('confidence_score')->nullable()->after('user_corrected_data');
            }
            if (!Schema::hasColumn('zero_input_logs', 'was_corrected')) {
                $table->boolean('was_corrected')->default(false)->after('confidence_score');
            }
            if (!Schema::hasColumn('zero_input_logs', 'feedback')) {
                $table->string('feedback', 20)->nullable()->after('was_corrected'); // accurate|corrected|rejected
            
            }});
    }

    public function down(): void
    {
        Schema::table('zero_input_logs', function (Blueprint $table) {
            $table->dropColumn(['user_corrected_data', 'confidence_score', 'was_corrected', 'feedback']);
        });
    }
};
