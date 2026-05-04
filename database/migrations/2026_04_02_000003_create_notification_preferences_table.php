<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('notification_preferences')) {
            Schema::create('notification_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('notification_type', 100);
                $table->boolean('in_app')->default(true);
                $table->boolean('email')->default(true);
                $table->boolean('push')->default(true);
                $table->timestamps();
                $table->unique(['user_id', 'notification_type']);
            });
        }

        // Also add digest columns to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'digest_frequency')) {
                $table->string('digest_frequency', 20)->default('weekly')->after('is_active'); // daily, weekly, monthly, off
                $table->string('digest_day', 10)->default('friday')->after('digest_frequency'); // monday-sunday
                $table->string('digest_time', 5)->default('17:00')->after('digest_day');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['digest_frequency', 'digest_day', 'digest_time']);
        });
    }
};
