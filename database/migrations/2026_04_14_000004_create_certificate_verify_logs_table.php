<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_verify_logs', function (Blueprint $table) {
            $table->id();
            $table->string('certificate_number', 50)->index();
            $table->string('ip_address', 45);
            $table->enum('result', ['valid', 'invalid', 'not_found', 'revoked']);
            $table->timestamp('verified_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_verify_logs');
    }
};
