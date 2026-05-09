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
        if (! Schema::hasTable('product_certificates')) {
            Schema::create('product_certificates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->string('certificate_number', 50)->unique();
                $table->string('certificate_hash', 64);
                $table->enum('status', ['active', 'revoked'])->default('active');
                $table->foreignId('issued_by')->constrained('users');
                $table->timestamp('issued_at');
                $table->timestamp('expires_at')->nullable();
                $table->foreignId('revoked_by')->nullable()->constrained('users');
                $table->timestamp('revoked_at')->nullable();
                $table->string('revoke_reason')->nullable();
                $table->timestamps();

                $table->index(['product_id', 'status']);
                $table->index(['tenant_id', 'certificate_number']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_certificates');
    }
};
