<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('achievements')) {
            Schema::create('achievements', function (Blueprint $table) {
                $table->id();
                $table->string('key', 50)->unique();
                $table->string('name');
                $table->string('description');
                $table->string('icon', 10)->default('🏆');
                $table->string('category', 30);
                $table->string('color', 20)->default('amber');
                $table->integer('points')->default(10);
                $table->string('requirement_type', 20);
                $table->string('requirement_model')->nullable();
                $table->string('requirement_action')->nullable();
                $table->integer('requirement_value')->default(1);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('user_achievements')) {
            Schema::create('user_achievements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('achievement_id')->constrained()->cascadeOnDelete();
                $table->integer('current_progress')->default(0);
                $table->timestamp('earned_at')->nullable();
                $table->timestamps();
                $table->unique(['user_id', 'achievement_id']);
                $table->index(['tenant_id', 'user_id']);
            });
        }

        if (!Schema::hasTable('user_points_log')) {
            Schema::create('user_points_log', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->integer('points');
                $table->string('reason');
                $table->timestamps();
                $table->index(['tenant_id', 'user_id']);
            });
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'gamification_points')) {
                $table->integer('gamification_points')->default(0)->after('digest_time');
            }
            if (!Schema::hasColumn('users', 'gamification_level')) {
                $table->integer('gamification_level')->default(1)->after('gamification_points');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['gamification_points', 'gamification_level']);
        });
        Schema::dropIfExists('user_points_log');
        Schema::dropIfExists('user_achievements');
        Schema::dropIfExists('achievements');
    }
};
