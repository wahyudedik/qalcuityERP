<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('writeoffs')) {
            Schema::create('writeoffs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('requested_by');
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->string('number', 30)->unique();
                $table->enum('type', ['receivable', 'payable']); // piutang atau hutang
                $table->string('reference_type');   // App\Models\Invoice, App\Models\Payable
                $table->unsignedBigInteger('reference_id');
                $table->string('reference_number');
                $table->decimal('original_amount', 20, 2);
                $table->decimal('writeoff_amount', 20, 2);
                $table->string('reason');
                $table->enum('status', ['pending', 'approved', 'rejected', 'posted'])->default('pending');
                $table->string('rejection_reason')->nullable();
                $table->unsignedBigInteger('journal_entry_id')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('posted_at')->nullable();
                $table->timestamps();
    
                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'type']);
                $table->index(['reference_type', 'reference_id']);
                $table->foreign('journal_entry_id')->references('id')->on('journal_entries')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('writeoffs');
    }
};
