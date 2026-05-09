<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('assets')) {
            Schema::create('assets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('asset_code')->unique();
                $table->string('name');
                $table->string('category')->nullable(); // vehicle, machine, equipment, furniture, building
                $table->string('brand')->nullable();
                $table->string('model')->nullable();
                $table->string('serial_number')->nullable();
                $table->string('location')->nullable();
                $table->date('purchase_date')->nullable();
                $table->decimal('purchase_price', 18, 2)->default(0);
                $table->decimal('current_value', 18, 2)->default(0);
                $table->decimal('salvage_value', 18, 2)->default(0);
                $table->integer('useful_life_years')->default(5);
                $table->string('depreciation_method')->default('straight_line'); // straight_line, declining_balance
                $table->string('status')->default('active'); // active, maintenance, disposed, retired
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index('tenant_id');
            });
        }

        if (! Schema::hasTable('asset_maintenances')) {
            Schema::create('asset_maintenances', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('asset_id');
                $table->string('type')->default('scheduled'); // scheduled, corrective, preventive
                $table->string('description');
                $table->date('scheduled_date')->nullable();
                $table->date('completed_date')->nullable();
                $table->decimal('cost', 18, 2)->default(0);
                $table->string('vendor')->nullable();
                $table->string('status')->default('pending'); // pending, in_progress, completed, cancelled
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index(['tenant_id', 'asset_id']);
            });
        }

        if (! Schema::hasTable('asset_depreciations')) {
            Schema::create('asset_depreciations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('asset_id');
                $table->string('period'); // YYYY-MM
                $table->decimal('depreciation_amount', 18, 2);
                $table->decimal('book_value_after', 18, 2);
                $table->timestamps();
                $table->index(['tenant_id', 'asset_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_depreciations');
        Schema::dropIfExists('asset_maintenances');
        Schema::dropIfExists('assets');
    }
};
