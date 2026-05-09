<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('project_tasks', 'progress_method')) {
                $table->enum('progress_method', ['status', 'volume'])->default('status')->after('status');
            }
            if (! Schema::hasColumn('project_tasks', 'target_volume')) {
                $table->decimal('target_volume', 15, 3)->default(0)->after('progress_method');
            }
            if (! Schema::hasColumn('project_tasks', 'actual_volume')) {
                $table->decimal('actual_volume', 15, 3)->default(0)->after('target_volume');
            }
            if (! Schema::hasColumn('project_tasks', 'volume_unit')) {
                $table->string('volume_unit', 30)->nullable()->after('actual_volume');
            }
        });
    }

    public function down(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->dropColumn(['progress_method', 'target_volume', 'actual_volume', 'volume_unit']);
        });
    }
};
