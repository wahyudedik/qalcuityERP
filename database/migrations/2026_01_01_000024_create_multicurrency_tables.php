<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('code', 10); // USD, EUR, SGD, MYR
            $table->string('name');
            $table->string('symbol', 10)->nullable();
            $table->decimal('rate_to_idr', 18, 6)->default(1); // 1 unit = X IDR
            $table->boolean('is_base')->default(false); // IDR = base
            $table->boolean('is_active')->default(true);
            $table->timestamp('rate_updated_at')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('currency_rate_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('currency_code', 10);
            $table->decimal('rate_to_idr', 18, 6);
            $table->date('date');
            $table->timestamps();
            $table->index(['tenant_id', 'currency_code', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currency_rate_history');
        Schema::dropIfExists('currencies');
    }
};
