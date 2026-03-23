<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('simulations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('scenario_type'); // price_increase, new_branch, stock_out, cost_reduction, demand_change
            $table->json('parameters');      // input skenario
            $table->json('results')->nullable(); // output kalkulasi
            $table->text('ai_narrative')->nullable(); // narasi AI
            $table->string('status')->default('draft'); // draft, calculated
            $table->timestamps();

            $table->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulations');
    }
};
