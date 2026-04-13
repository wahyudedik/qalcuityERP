<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('teleconsultation_payments')) {
            Schema::create('teleconsultation_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('consultation_id')->constrained('teleconsultations')->onDelete('cascade');
                $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
                $table->string('payment_number')->unique();
                $table->decimal('amount', 15, 2);
                $table->decimal('discount', 15, 2)->default(0);
                $table->decimal('total_amount', 15, 2);
                $table->string('payment_method')->default('ewallet');
                $table->enum('status', ['pending', 'success', 'failed', 'refunded'])->default('pending');
                $table->string('gateway')->nullable();
                $table->string('gateway_transaction_id')->nullable();
                $table->string('snap_token')->nullable();
                $table->json('gateway_response')->nullable();
                $table->text('payment_instructions')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('refunded_at')->nullable();
                $table->string('refund_reason')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['consultation_id', 'status']);
                $table->index(['patient_id', 'status']);
                $table->index('payment_number');
                $table->index(['status', 'paid_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teleconsultation_payments');
    }
};
