<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('accounting_periods')) {
            Schema::create('accounting_periods', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('name', 50); // e.g. "Maret 2026"
                $table->date('start_date');
                $table->date('end_date');
                $table->enum('status', ['open', 'closed', 'locked'])->default('open');
                $table->unsignedBigInteger('closed_by')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'start_date', 'end_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_periods');
    }
};
