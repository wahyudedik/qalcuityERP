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
        Schema::create('user_layout_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('page', 100)->index(); // notifications, room-availability, dll
            $table->json('layout_config')->nullable(); // konfigurasi kolom dan widget
            $table->json('breakpoint_config')->nullable(); // konfigurasi responsive breakpoints
            $table->timestamps();

            // Unique constraint: satu user hanya bisa punya satu preference per halaman
            $table->unique(['tenant_id', 'user_id', 'page']);

            // Index untuk performa query
            $table->index(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'page']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_layout_preferences');
    }
};
