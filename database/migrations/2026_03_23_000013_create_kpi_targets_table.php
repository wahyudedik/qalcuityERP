<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('kpi_targets')) {
            Schema::create('kpi_targets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('metric');          // e.g. revenue, orders, customers, profit_margin
                $table->string('label');           // display name
                $table->string('period');          // YYYY-MM
                $table->decimal('target', 18, 2);
                $table->decimal('actual', 18, 2)->default(0);
                $table->string('unit')->default('number'); // number, currency, percent
                $table->string('color')->default('#3b82f6');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['tenant_id', 'period']);
                $table->unique(['tenant_id', 'metric', 'period']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_targets');
    }
};
