<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('fb_payments')) {
            Schema::create('fb_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('fb_order_id');
                $table->string('payment_number')->unique(); // e.g., DIN-20240407-001-P1
                $table->decimal('amount', 15, 2);
                $table->string('payment_method'); // cash, card, qris, room_charge, etc.
                $table->string('status')->default('completed'); // pending, completed, failed, refunded
                $table->timestamp('paid_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('fb_order_id')->references('id')->on('fb_orders')->cascadeOnDelete();

                $table->index(['tenant_id', 'fb_order_id']);
                $table->index(['tenant_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fb_payments');
    }
};
