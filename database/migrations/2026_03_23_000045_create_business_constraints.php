<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_constraints', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('key', 60);          // e.g. no_sell_below_cost, max_discount_pct, min_cash_balance, confirm_above_amount
            $table->string('label', 100);
            $table->string('value_type', 20)->default('boolean'); // boolean, percentage, amount, integer
            $table->string('value', 100)->default('false');       // stored as string, cast on read
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'key']);
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_constraints');
    }
};
