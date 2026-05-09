<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('popup_ads')) {
            Schema::create('popup_ads', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('body')->nullable();
                $table->string('image_path')->nullable();        // stored in storage/app/public/popup-ads/
                $table->string('button_label', 100)->nullable(); // e.g. "Lihat Promo"
                $table->string('button_url', 500)->nullable();
                $table->enum('target', ['all', 'specific'])->default('all');
                $table->json('tenant_ids')->nullable();           // array of tenant IDs when target=specific
                $table->enum('frequency', ['once', 'daily', 'always'])->default('once');
                $table->date('starts_at')->nullable();
                $table->date('ends_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Track which users have already dismissed (for once/daily logic)
        if (! Schema::hasTable('popup_ad_views')) {
            Schema::create('popup_ad_views', function (Blueprint $table) {
                $table->id();
                $table->foreignId('popup_ad_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->timestamp('viewed_at');
                $table->index(['popup_ad_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('popup_ad_views');
        Schema::dropIfExists('popup_ads');
    }
};
