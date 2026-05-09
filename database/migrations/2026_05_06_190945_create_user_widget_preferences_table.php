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
        Schema::create('user_widget_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('page', 100); // halaman tempat widget berada, e.g. 'notifications', 'reports'
            $table->string('widget_type', 100); // tipe widget, e.g. 'statistics', 'chart', 'quick-actions'
            $table->json('widget_config')->nullable(); // konfigurasi spesifik widget
            $table->unsignedSmallInteger('position')->default(0); // urutan widget dalam halaman
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Index untuk query berdasarkan tenant, user, dan halaman
            $table->index(['tenant_id', 'user_id', 'page']);
            $table->index(['tenant_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_widget_preferences');
    }
};
