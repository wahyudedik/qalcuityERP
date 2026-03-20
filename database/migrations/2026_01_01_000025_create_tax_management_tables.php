<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('name'); // PPN 11%, PPh 23, PPh Final 0.5%
            $table->string('code'); // PPN, PPH23, PPH_FINAL
            $table->string('type'); // ppn, pph21, pph23, pph_final
            $table->decimal('rate', 8, 4); // percentage
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('tenant_id');
        });

        Schema::create('tax_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('tax_code');
            $table->string('type'); // ppn_out, ppn_in, pph21, pph23, pph_final
            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('party_name')->nullable(); // customer/supplier name
            $table->string('npwp')->nullable();
            $table->decimal('base_amount', 18, 2);
            $table->decimal('tax_amount', 18, 2);
            $table->decimal('rate', 8, 4);
            $table->date('transaction_date');
            $table->string('period'); // YYYY-MM
            $table->string('status')->default('recorded'); // recorded, reported, paid
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'period', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_records');
        Schema::dropIfExists('tax_rates');
    }
};
