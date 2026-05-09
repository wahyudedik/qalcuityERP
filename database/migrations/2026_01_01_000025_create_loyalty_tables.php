<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('loyalty_programs')) {
            Schema::create('loyalty_programs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('name');
                $table->decimal('points_per_idr', 10, 4)->default(0.01); // 1 poin per Rp 100
                $table->decimal('idr_per_point', 10, 4)->default(100);   // 1 poin = Rp 100 redeem
                $table->integer('min_redeem_points')->default(100);
                $table->integer('expiry_days')->default(365); // 0 = no expiry
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->index('tenant_id');
            });
        }

        if (! Schema::hasTable('loyalty_tiers')) {
            Schema::create('loyalty_tiers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('program_id');
                $table->string('name'); // Bronze, Silver, Gold, Platinum
                $table->integer('min_points')->default(0);
                $table->decimal('multiplier', 5, 2)->default(1.0); // poin multiplier
                $table->string('color')->nullable();
                $table->timestamps();
                $table->index('tenant_id');
            });
        }

        if (! Schema::hasTable('loyalty_points')) {
            Schema::create('loyalty_points', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('customer_id');
                $table->unsignedBigInteger('program_id');
                $table->integer('total_points')->default(0);
                $table->integer('lifetime_points')->default(0);
                $table->string('tier')->default('Bronze');
                $table->timestamp('tier_updated_at')->nullable();
                $table->timestamps();
                $table->unique(['tenant_id', 'customer_id', 'program_id']);
            });
        }

        if (! Schema::hasTable('loyalty_transactions')) {
            Schema::create('loyalty_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('customer_id');
                $table->unsignedBigInteger('program_id');
                $table->string('type'); // earn, redeem, expire, adjust
                $table->integer('points');
                $table->decimal('transaction_amount', 18, 2)->default(0);
                $table->string('reference')->nullable(); // SO number, etc
                $table->text('notes')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
                $table->index(['tenant_id', 'customer_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_transactions');
        Schema::dropIfExists('loyalty_points');
        Schema::dropIfExists('loyalty_tiers');
        Schema::dropIfExists('loyalty_programs');
    }
};
