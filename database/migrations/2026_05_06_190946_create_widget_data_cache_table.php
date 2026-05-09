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
        Schema::create('widget_data_cache', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('widget_type', 100); // tipe widget yang di-cache
            $table->string('cache_key', 255); // identifier unik cache
            $table->json('data'); // data widget yang di-cache
            $table->timestamp('expires_at')->nullable(); // waktu kadaluarsa cache
            $table->timestamps();

            // Unique constraint: satu cache_key per tenant
            $table->unique(['tenant_id', 'cache_key']);

            // Index untuk cleanup query berdasarkan waktu kadaluarsa
            $table->index('expires_at');
            $table->index(['tenant_id', 'widget_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('widget_data_cache');
    }
};
