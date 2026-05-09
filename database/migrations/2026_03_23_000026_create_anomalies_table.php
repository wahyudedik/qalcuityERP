<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('anomaly_alerts')) {
            Schema::create('anomaly_alerts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('type');       // unusual_transaction, stock_mismatch, unbalanced_journal, duplicate_transaction, fraud_pattern, price_anomaly
                $table->string('severity');   // critical, warning, info
                $table->string('title');
                $table->text('description');
                $table->json('data')->nullable();
                $table->string('status')->default('open'); // open, acknowledged, resolved
                $table->unsignedBigInteger('acknowledged_by')->nullable();
                $table->timestamp('acknowledged_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'status', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('anomaly_alerts');
    }
};
