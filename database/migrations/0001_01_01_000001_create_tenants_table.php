<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tenants')) {
            Schema::create('tenants', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();          // subdomain / identifier unik
                $table->string('email')->unique();          // email kontak utama tenant
                $table->string('phone')->nullable();
                $table->string('address')->nullable();
                $table->string('logo')->nullable();
                $table->enum('plan', ['trial', 'basic', 'pro', 'enterprise'])->default('trial');
                $table->boolean('is_active')->default(true);
                $table->timestamp('trial_ends_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
