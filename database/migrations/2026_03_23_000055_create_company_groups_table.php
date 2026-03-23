<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Grup perusahaan (holding)
        Schema::create('company_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_user_id'); // user yang membuat grup
            $table->string('name');
            $table->string('currency_code')->default('IDR');
            $table->timestamps();
        });

        // Anggota grup (tenant yang tergabung)
        Schema::create('company_group_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_group_id');
            $table->unsignedBigInteger('tenant_id');
            $table->string('role')->default('member'); // owner, member
            $table->timestamps();

            $table->unique(['company_group_id', 'tenant_id']);
        });

        // Transaksi intercompany
        Schema::create('intercompany_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_group_id');
            $table->unsignedBigInteger('from_tenant_id');
            $table->unsignedBigInteger('to_tenant_id');
            $table->string('type');         // sale, loan, expense_allocation
            $table->string('reference');
            $table->decimal('amount', 18, 2);
            $table->string('currency_code')->default('IDR');
            $table->text('description')->nullable();
            $table->string('status')->default('pending'); // pending, posted, cancelled
            $table->date('date');
            $table->timestamps();

            $table->index(['company_group_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intercompany_transactions');
        Schema::dropIfExists('company_group_members');
        Schema::dropIfExists('company_groups');
    }
};
