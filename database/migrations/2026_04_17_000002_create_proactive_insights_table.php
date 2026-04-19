<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('proactive_insights', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('condition_type');        // low_stock | overdue_ar | budget_exceeded | contract_expiry | unpaid_invoice
            $table->string('urgency');               // low | medium | high | critical
            $table->string('title');
            $table->text('description');
            $table->text('business_impact');
            $table->json('recommendations');
            $table->json('condition_data');
            $table->string('condition_hash');
            $table->timestamp('suppressed_until')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proactive_insights');
    }
};
