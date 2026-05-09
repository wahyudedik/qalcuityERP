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
        if (! Schema::hasTable('checkout_receipts')) {
            Schema::create('checkout_receipts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->foreignId('reservation_id')->constrained('reservations')->onDelete('cascade');
                $table->string('receipt_number')->unique();
                $table->decimal('grand_total', 10, 2);
                $table->decimal('amount_paid', 10, 2);
                $table->decimal('change_amount', 10, 2)->default(0);
                $table->string('payment_method'); // cash, credit_card, debit_card, transfer, qris
                $table->string('payment_status')->default('paid'); // paid, partially_paid, unpaid
                $table->string('transaction_reference')->nullable();
                $table->json('payment_details')->nullable();
                $table->text('notes')->nullable();
                $table->string('pdf_path')->nullable();
                $table->timestamp('paid_at');
                $table->foreignId('processed_by')->constrained('users')->onDelete('restrict');
                $table->timestamps();

                $table->index(['tenant_id', 'payment_status']);
                $table->index(['tenant_id', 'paid_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkout_receipts');
    }
};
