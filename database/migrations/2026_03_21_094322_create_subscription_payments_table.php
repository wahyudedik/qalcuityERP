<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('subscription_plans')->cascadeOnDelete();
            $table->string('order_id')->unique();
            $table->decimal('amount', 12, 2);
            $table->enum('billing', ['monthly', 'yearly']);
            $table->enum('gateway', ['midtrans', 'xendit']);
            $table->enum('status', ['pending', 'paid', 'failed'])->default('pending');
            $table->string('gateway_token')->nullable();
            $table->string('gateway_url')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }
};
