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
        if (!Schema::hasTable('voucher_codes')) {
            Schema::create('voucher_codes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('package_id');
                $table->unsignedBigInteger('generated_by')->nullable(); // User who generated
    
                // Voucher details
                $table->string('code')->unique(); // The actual voucher code
                $table->string('batch_number')->nullable(); // For batch generation
                $table->enum('status', ['unused', 'used', 'expired', 'revoked'])->default('unused');
    
                // Validity
                $table->timestamp('valid_from')->nullable();
                $table->timestamp('valid_until')->nullable();
                $table->integer('validity_hours')->default(24); // Hours from first use
    
                // Usage tracking
                $table->timestamp('first_used_at')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->unsignedBigInteger('used_by_customer_id')->nullable();
                $table->string('used_by_username')->nullable();
                $table->integer('usage_count')->default(0);
                $table->integer('max_usage')->default(1); // Max times can be used
    
                // Bandwidth (inherited from package, but can override)
                $table->integer('download_speed_mbps')->nullable();
                $table->integer('upload_speed_mbps')->nullable();
                $table->bigInteger('quota_bytes')->default(0);
    
                // Pricing (if sold separately)
                $table->decimal('sale_price', 15, 2)->nullable();
                $table->timestamp('sold_at')->nullable();
                $table->unsignedBigInteger('sold_to_customer_id')->nullable();
    
                $table->timestamps();
    
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->foreign('package_id')->references('id')->on('internet_packages')->onDelete('cascade');
                $table->foreign('generated_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('used_by_customer_id')->references('id')->on('customers')->onDelete('set null');
                $table->foreign('sold_to_customer_id')->references('id')->on('customers')->onDelete('set null');
    
                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'batch_number']);
                $table->index(['code']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_codes');
    }
};
