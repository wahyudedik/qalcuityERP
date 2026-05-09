<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('subscription_plans')) {
            Schema::create('subscription_plans', function (Blueprint $table) {
                $table->id();
                $table->string('name');                          // Nama plan: Basic, Pro, Enterprise
                $table->string('slug')->unique();                // basic, pro, enterprise
                $table->decimal('price_monthly', 12, 2)->default(0);
                $table->decimal('price_yearly', 12, 2)->default(0);
                $table->integer('max_users')->default(5);        // -1 = unlimited
                $table->integer('max_ai_messages')->default(100); // per bulan, -1 = unlimited
                $table->integer('trial_days')->default(14);
                $table->json('features')->nullable();            // array fitur yang tersedia
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // Tambah kolom plan_expires_at ke tenants
        Schema::table('tenants', function (Blueprint $table) {
            if (! Schema::hasColumn('tenants', 'plan_expires_at')) {
                $table->timestamp('plan_expires_at')->nullable()->after('trial_ends_at');
            }
            if (! Schema::hasColumn('tenants', 'subscription_plan_id')) {
                $table->unsignedBigInteger('subscription_plan_id')->nullable()->after('plan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['plan_expires_at', 'subscription_plan_id']);
        });
        Schema::dropIfExists('subscription_plans');
    }
};
